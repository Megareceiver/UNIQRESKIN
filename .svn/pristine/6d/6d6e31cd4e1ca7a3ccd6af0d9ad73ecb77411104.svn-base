<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Days_rule {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->sale_model = $this->ci->model('sale',true);
        $this->common_model = $this->ci->model('common',true);
    }

    function index(){
        $js = get_js_date_picker();
        $page_security = 'SA_GLSETUP';
        page(_($help_context = "Days Rules"),false, false, "", $js);
        global $Ajax;

        $Ajax->activate('_page_body');

        start_form();
        $this->delivery_daysRule();
        end_form();
        end_page();
    }

    function delivery_daysRule(){
        $data['page_last'] = 1;
        $data['limit'] = page_padding_limit;
        $data['date'] = input_val('date');
        if( empty($data['date']) ){
            $data['date'] = Today();
        }

        $data['threshold'] = 21;
        $data['page'] = 1;
        $data['page_last'] = 1;

        $items = $this->sale_model->delivery_daysRule($data['date'],null,$data['threshold'],$data['page']);
        $data['items'] = $items['items'];
        $data['total'] = $items['total'];

        $this->ci->view('sale/days_rules',$data);

    }
}
