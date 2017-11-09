<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchases {
    function __construct() {
        $this->ci = get_instance();
    }

    function index(){
        $this->dashboard = $this->ci->module_control_load('dashboard',null,true);


        if( $this->ci->uri->segment(2)=='dashboard' ){
            return $this->dashboard();
        }

    }

    private function dashboard(){

        $graphs = array();
        $graphs[] = (object)array('widget'=>'purchase_invoice_overdue','description'=>'Overdue invoices');
        $graphs[] = (object)array('widget'=>'dailysales','description'=>'Daily Sales','graph_type'=>'LineChart','data_filter'=>'dm.payment_terms = -1');
        $graphs[] = (object)array('widget'=>'suppliers','description'=>'Top 10 Suppliers','graph_type'=>'Table');
        $graphs[] = (object)array('widget'=>'suppliers','description'=>'Top 10 Suppliers','graph_type'=>'ColumnChart');

        $graphs[] = (object)array('widget'=>'weeklypurchase','description'=>'Weekly Purchase','graph_type'=>'LineChart');
        $graphs[] = (object)array('widget'=>'weeklypurchase','description'=>'Lowest weeks Purchase','graph_type'=>'Table');

        $this->dashboard->display($graphs,'Purchase Dashboard');
    }
}