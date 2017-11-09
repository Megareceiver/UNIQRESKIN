<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sales {
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
        $graphs[] = (object)array('widget'=>'customers','description'=>'Top 10 Customers','graph_type'=>'Table');
        $graphs[] = (object)array('widget'=>'sales_invoice_overdue','description'=>'Overdue invoices');
        $graphs[] = (object)array('widget'=>'customers','description'=>'customers','graph_type'=>'ColumnChart');
        $graphs[] = (object)array('widget'=>'weeklysales','description'=>'Weekly Sales','graph_type'=>'LineChart');
        $graphs[] = (object)array('widget'=>'weeklysales','description'=>'Lowest weeks sales','graph_type'=>'Table');


        $this->dashboard->display($graphs,'Sales Dashboard');
    }
}