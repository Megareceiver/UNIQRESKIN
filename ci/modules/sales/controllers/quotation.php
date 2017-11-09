<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SalesQuotation {
    function __construct() {
        $ci = get_instance();
        $this->order_control = $ci->module_control_load('sales','order',true);
    }

    function index(){
        page("Sales Quotations");


        $this->items();
        end_page();
    }

    function items(){
        $fillter_fields = $this->order_control->fillter;
        $this->order_control->fillter_view($fillter_fields);

    }

}