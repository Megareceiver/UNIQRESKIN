<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaintenanceTransaction {
    function __construct() {
        global $ci, $Ajax;
        $this->ci = $ci;
        $this->model = module_model_load('transaction');
    }

    function index(){
        die('index maintenance transaction');
    }

    function sale() {
        page( "Sale Transactions - Report");

        $page = input_val('page');
        if( !$page ){
            $page = 1;
        }
        $data = $this->model->sale_check($page);
        $data['page'] = $page;

        module_view('transactions_check',$data);
        end_page();

    }
}