<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SalesDelivery  extends ci {
    function __construct() {
//         $ci = get_instance();
        $this->customer_trans_model = module_model_load('customer_trans');
//         $this->bank_model = $this->model('bank_account',true);
//         $this->contact_model = $this->model('crm',true);
//         $this->sys_model = $this->model('config',true);
//         $this->sale_order_model = $this->model('sale_order',true);
    }

    var $tran_no = 0;
    var $tran_type = ST_CUSTDELIVERY;

    function details(){
        global $use_popup_windows;



        $js = NULL;
        if ($use_popup_windows)
            $js .= get_js_open_window(900, 600);
        page(_("View Sales Dispatch"), true, false, "", $js);

        $this->tran_no = get_instance()->uri->segment(4);
        $tran = $this->customer_trans_model->get_customer_trans($this->tran_no, $this->tran_type);

        $data = array(
            'tran_no'=>$this->tran_no,
            'tran'=>$tran
        );
        module_view('delivery/dispatch',$data,true);

        end_page(true, false, false, $this->tran_type, $this->tran_no);
    }
}