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

		$this->load->view('inventory/dashboard', $data);
	}
}
