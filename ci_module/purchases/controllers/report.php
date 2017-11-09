<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PurchasesReport {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->db = $ci->db;

        if( !isset($ci->pdf) ){
            $ci->load_library('reporting');
        }
        $this->tcpdf = $ci->pdf->tcpdf;
        $this->pdf = $ci->pdf;

        $this->model = module_model_load( 'report','supplier' );
        $this->trans_model = module_model_load( 'transaction','supplier' );
    }

    function supplier_balances(){

        $from =    '1-1-2015';
        $to =      '31-12-2015';
        $fromsupp = input_val('PARAM_2');


        $show_balance = input_val('PARAM_3');
        $currency = input_val('PARAM_4');

        $no_zeros = _('No');

        if ($show_balance){
            unset($this->supplier_balances_table['outstanding']);
            $this->supplier_balances_table['balance'] = array('title'=>'Balance','w'=>12,'class'=>'textright');
        }


        $this->db->select('supplier_id, supp_name AS name, curr_code');
        if ($fromsupp != ALL_TEXT){
            $this->db->where('supplier_id',intval($fromsupp));
        }
        $suppliers = $this->db->order_by('supp_name')->get('suppliers')->result();

        if( $suppliers && count($suppliers) > 0 ){
            foreach ($suppliers AS $k=>$supp){
                $suppliers[$k]->ob = $this->trans_model->get_open_balance($supp->supplier_id, $from);

                $suppliers[$k]->transactions = $this->trans_model->get_transactions($supp->supplier_id, $from, $to);
            }
        }
        page('Supplier Balances');
        module_view('report/supplier_balance',array('suppliers'=>$suppliers));
        end_page();
    }
}