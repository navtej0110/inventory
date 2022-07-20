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

		$this->load->view('inventory/dashboard', $data);
	}
}
