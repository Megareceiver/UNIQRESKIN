<?php
class GlInquiryTax  {
    function __construct() {
//         $this->check_input_get();
//         $this->check_submit();
        $this->tax_model = module_model_load('tax','gst');
    }
    function index(){

    }

    function view(){
        $table = array(
            'type_name'=>array('3','Type'),
            'desc'=>array('3','Description'),
            'collectible'=>array('2','Amount'),
            'net_input'=>array('2','Outputs/Inputs'),
        );

        $date_from = input_post('date_from');
        $date_to = input_post('date_to');
        if( !$date_from && !$date_to ){
            $row = get_company_prefs();
            $date = Today();
            $date_to = add_months($date, -$row['tax_last']);
            $date_to = end_month($date_to);
            $date_from = begin_month($date_to);
            $date_from = add_months($date_from, -$row['tax_prd'] + 1);

        }

        $items_tax_trans_detail = $this->tax_model->get_summary($date_from, $date_to);
//         module_view();
        box_start();
        module_view('inquiry/tax',array('table'=>$table,'items'=>$items_tax_trans_detail,'from'=>$date_from,'to'=>$date_to),true,false,'gl');

        box_end();
    }

    private function check_input_get(){

    }

    private function check_submit(){

    }
}