<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SalesOrder {
    function __construct() {
        add_document_ready_js("$('select[name=stock_search_select], select[name=customer]').chosen();");

    }

    var $fillter = array(
        'trans_no'=>array('title'=>'#'),
        'ref'=>array('title'=>'Ref'),
        'date_from'=>array('title'=>'From','type'=>'date'),
        'date_to'=>array('title'=>'To','type'=>'date'),
        'stock_location'=>array('title'=>'Locations'),
        'stock_search'=>array('title'=>'Item','type'=>'stock_product_select'),
        'customer'=>array('title'=>'Select a Customer','type'=>'customer'),
    );

    function index(){
        page("Sales Orders");


        $this->items();
        end_page();
    }

    function items(){



    }

    function fillter_view($fields=array()){
        $ci = get_instance();
        $ci->temp_view('fillter/order',array('fields'=>$fields));
    }

}