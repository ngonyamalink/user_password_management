<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class User_db extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    //to get user with username and password, record user failed login attempt , blocks user on third failed attempt, clears failed attemps on every successful login
    public function get_user($username, $password) {

        if ($this->get_failed_attempts($username) >= 3) {
            die("You have exceeded log in attempts. Contact the administrator.");
        }

        $sql = "select * from user where (username='$username' and password='$password')";
        $query = $this->db->query($sql);
        $data = !empty($query) ? $query->row_array() : false;

        if ($data) {
            $this->reset_failed_attempts($username);
            return $data;
        } else {
            $this->increment_failed_attempts($username);
            return false;
        }
    }

    public function increment_failed_attempts($username) {
        $sql = "update user set failed_attemps= (failed_attemps+1) where username='$username'";
        $this->db->query($sql);
    }

    public function reset_failed_attempts($username) {
        $sql = "update user set failed_attemps=0 where username='$username'";
        $this->db->query($sql);
    }

    public function get_failed_attempts($username) {
        $sql = "select failed_attemps from user where (username='$username')";
        $query = $this->db->query($sql);
        $data = !empty($query) ? $query->row_array() : false;

        if ($data) {
            return $data['failed_attemps'];
        } else {
            die("something went wrong, contact the administrator");
        }
    }

    //run once a day - prferably via cron job
    public function decrement_expiry_days() {
        $sql = "update user set expiry_days_left= (expiry_days_left-1)";
        $this->db->query($sql);
    }

    //call after user changes password
    public function restore_expiry_days($username) {
        $sql = "update user set expiry_days_left=30 where username='$username'";
        $this->db->query($sql);
    }

    public function get_all_users() {
        $sql = "select username,expiry_days_left from user where (expiry_days_left<=10)";
        $query = $this->db->query($sql);
        return !empty($query) ? $query->result_array() : false;
    }

}
