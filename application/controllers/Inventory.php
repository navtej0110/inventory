<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

	public function dashboard()
	{      
        $thisQuery = $this->db->query("SELECT count(*) as active_verified_users from `users` WHERE `status` = 1 AND `verified` = 1")->result(); 
        $data['active_verified_users'] = $thisQuery[0]->active_verified_users;
		$this->load->view('inventory/dashboard', $data);
	}
}
