<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class passwordexpirery extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model("user_db");
        $this->load->library('email');
        $this->load->library('session');
    }

    public function run() {

        //decrement days left
        $this->user_db->decrement_expiry_days();

        //get all users with at most 10 an send them password expiry reminder
        $data = $this->user_db->get_all_users();


        $cnt = 0;

        foreach ($data as $user) {
            $cnt ++;

            $this->email->from('email@example.com', 'Identification');
            $this->email->to($user['username']);
            $this->email->subject('Password Expiry');
            $this->email->message("Your password expires in " . $user['expiry_days_left'] . " days. Please change your password");

            //Send mail
            if ($this->email->send()) {
                $this->session->set_flashdata("email_sent", "Congragulation Email Send Successfully.");
            } else {
                $this->session->set_flashdata("email_sent", "You have encountered an error");
            }

            if ($cnt == 10) {
                sleep(5);
                $cnt = 0;
            }
        }
    }

}
