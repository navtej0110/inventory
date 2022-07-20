<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

	public function dashboard()
	{      
        // 3.1 Count of all active and verified users.
        $thisQuery = $this->db->query("SELECT count(*) as active_verified_users from `users` WHERE `status` = 1 AND `verified` = 1")->result(); 
        $data['active_verified_users'] = $thisQuery[0]->active_verified_users;

        // 3.2  Count of active and verified users who have attached active products.
        $active_verified_attached_users = $this->db->query("SELECT u.user_id FROM `users` u INNER JOIN `user_product_list`up ON (up.user_id = u.user_id) INNER JOIN `products` p ON (p.id = up.product_id) WHERE  u.`status` = 1 AND u.`verified` = 1     AND p.`status` = 'active' GROUP BY u.user_id")->num_rows(); 
        $data['active_verified_attached_users'] = $active_verified_attached_users;

        // 3.3. Count of all active products (just from products table).
        $thisQuery = $this->db->query("SELECT count(id) as active_products FROM `products` WHERE `status` = 'active'")->result(); 
        $data['active_products'] = $thisQuery[0]->active_products;

        // 3.4. Count of active products which don't belong to any user.
        $thisQuery = $this->db->query("SELECT count(id) as active_products_without_user  FROM `products` WHERE `status` = 'active' AND `id` NOT  IN (SELECT `product_id` FROM `user_product_list`)")->result(); 
        $data['active_products_without_user'] = $thisQuery[0]->active_products_without_user;

        // 3.5. Amount of all active attached products (if user1 has 3 prod1 and 2 prod2 which are active, user2 has 7 prod2 and 4 prod3, prod3 is inactive, then the amount of active attached products will be 3 + 2 + 7 = 12).
        $thisQuery = $this->db->query(
            "SELECT sum(up.quantity) as active_attached_products
            FROM `products` p
            INNER JOIN `user_product_list` up ON (p.id = up.product_id)
            WHERE p.`status` = 'active';"
        )->result(); 
        $data['active_attached_products'] = $thisQuery[0]->active_attached_products;

        // 3.6. Summarized price of all active attached products (from the previous subpoint if prod1 price is 100$, prod2 price is 120$, prod3 price is 200$, the summarized price will be 3 x 100 + 9 x 120 = 1380).
        $thisQuery = $this->db->query(
            "SELECT sum(up.quantity * up.product_price) as active_attached_products_price
            FROM `products` p
            INNER JOIN `user_product_list` up ON (p.id = up.product_id)
            WHERE p.`status` = 'active';"
        )->result(); 
        $data['active_attached_products_price'] = $thisQuery[0]->active_attached_products_price;

        // 3.7. Summarized prices of all active products per user. For example - John Summer - 85$, Lennon Green - 107$.
        $data['active_attached_products_price_per_user'] = $this->db->query(
            "SELECT u.user_name, sum(up.quantity * up.product_price) as price
            FROM `products` p
            INNER JOIN `user_product_list` up ON (p.id = up.product_id)
            INNER JOIN `users` u ON (u.user_id = up.user_id)
            WHERE p.`status` = 'active'
            GROUP BY u.user_id;"
        )->result();

        $data['conversion_rates'] = $this->get_exchange_rates('EUR','USD,RON');

		$this->load->view('inventory/dashboard', $data);
	}

    public function get_exchange_rates($base,$symbols) {
        $curl = curl_init();

        // API key is placed here for Testing. It can be moved to more secure place like .env file
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.apilayer.com/exchangerates_data/latest?symbols=$symbols&base=$base",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: text/plain",
                "apikey: gMWALyRxVCDkzGmvorlBY0OFQXaSAbuw"
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response_obj = json_decode($response);
        
        return $response_obj->success ? $response_obj->rates : false;
    }

    public function sync_db() {
        
        $this->db->query("CREATE DATABASE `inventory`");
        $this->db->query("USE `inventory`");

        // Create Users Table
        $this->db->query(
            "CREATE TABLE `users` (
                `user_id` int NOT NULL AUTO_INCREMENT,
                `user_name` varchar(255) NOT NULL,
                `status` tinyint(1) DEFAULT '1',
                `verified` tinyint(1) DEFAULT '0',
                PRIMARY KEY (`user_id`)
            )"
        );

        // Create Products Table
        $this->db->query(
            "CREATE TABLE `products` (
                `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `description` VARCHAR(255) NOT NULL,
                `image` VARCHAR(255) NOT NULL,
                `status` ENUM('active','inactive') DEFAULT 'active'
            )"
        );

        // Create User/Products Mapping
        $this->db->query(
            "CREATE TABLE `user_product_list` (
                `user_product_id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `user_id` INT(11) NOT NULL,
                `product_id` INT(11) NOT NULL,
                `quantity` INT(11) NOT NULL,
                `product_price` FLOAT(18,2) NOT NULL
            )"
        );

        // Create Dummy Users
        $this->db->query(
            "INSERT INTO users (`user_name`, `status`, `verified`)
            VALUES ('John', 1, 1),
            ('Tim', 0, 1),
            ('Steve', 0, 0),
            ('Rory', 1, 0)"
        );

        // Create Dummy Products
        $this->db->query(
            "INSERT INTO products (`title`, `description`, `image`, `status`)
            VALUES ('iPhone X', 'Product 1 Description', 'Product1Image.jpg', 'active'),
            ('iPhone 11', 'Product 2 Description', 'Product2Image.jpg', 'inactive'),
            ('iPhone 12', 'Product 3 Description', 'Product3Image.jpg', 'active'),
            ('iPhone 13', 'Product 4 Description', 'Product4Image.jpg', 'inactive')"
        );

        // Create User/Products
        $this->db->query(
            "INSERT INTO user_product_list (`user_id`, `product_id`, `quantity`, `product_price`)
            VALUES (1, 1, 10, 100),
            (1, 3, 20, 200),
            (2, 1, 10, 300),
            (2, 2, 30, 100),
            (3, 3, 20, 200)"
        );

        echo 'DB Synced'; die();
    }
}
