<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Products {
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
        $graphs[] = (object)array('widget'=>'items','description'=>'Top 10 Items','graph_type'=>'Table');
        $graphs[] = (object)array('widget'=>'items','description'=>'Top 10 Items','graph_type'=>'PieChart');
        $this->dashboard->display($graphs,'Products Dashboard');
    }
}