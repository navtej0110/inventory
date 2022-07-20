<?php 
    class Users_Products extends CI_Model
    {
        function __construct(){
            parent::__construct();
        }

        public function active_verified_users(){
            $query = $this->db->get_where('users', array('status'=>1,'verified'=>1));
            return $query;
        }
    }