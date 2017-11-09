<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bab_deb {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->gl_model = $this->ci->model('Gl_trans',true);
        $this->common_model = $this->ci->model('common',true);
        $this->gl_trans = $this->ci->model('gl_trans',true);
    }

    function index(){
        $js = get_js_date_picker();
        $page_security = 'SA_GLSETUP';
        page(_($help_context = "Bad Debts Processing Dashboad"),false, false, "", $js);
        global $Ajax;
        $Ajax->activate('_page_body');

        $type='customer';

        if( $this->ci->input->post('type')=='supplier' ){
            $type='supplier';
        } else if ( $this->ci->input->get('type')=='supplier' ) {
            $type='supplier';
        }

        start_form();
        if( $type=='supplier' ){
            $this->supplier_view();
        } else {
            $this->customer_view();
        }
        hidden('type',$type);

        end_form();
        end_page();
    }

    private function customer_view(){
        $this->customer_model = $this->ci->model('cutomer',true);
        $this->customer_trans_model = $this->ci->model('Customer_trans',true);

        if( $this->ci->input->post('process') ){
            $this->customer_process( $this->ci->input->post('process') );
        }else if ($this->ci->input->post('paid') ) {
            $this->customer_process( $this->ci->input->post('paid'),$step = 2 );
        }

        if( $this->ci->input->post('datebaddeb')=='M'){
            $pieces = explode("-", $data['date']);
            $data['date'] = $pieces[1].'/'.$pieces[2];}
        else{
            $data['date'] =Today();
        }

        $data['threshold'] = 180;
        $data['page'] = 1;
        $data['page_last'] = 1;
        $data['limit'] = page_padding_limit;
        $data['debtor'] = null;
        $data['datebaddeb']=$this->ci->input->post('datebaddeb');
        $data['date'] = null;

         if( $this->ci->input->post('date') ){
            $data['date'] = $this->ci->input->post('date');
            $data['threshold'] = $this->ci->input->post('threshold');
            $data['debtor'] = $this->ci->input->post('debtor');
        }

        $page_submit = array('first','pre','next','last');
        foreach ($page_submit AS $na){
            if(  $this->ci->input->post($na) ){
                $data['page'] = $this->ci->input->post($na);
            }
        }
        if(  $data['datebaddeb']=='M' ){
            $data['datebaddeb']= $this->ci->input->post('datebaddeb');
            if( is_date($data['date']) ) {
                $date_item = array(date('m'),date('Y'));
            } else {
                $date_item = explode("-", $data['date']);
            }

            $date = new DateTime("1-".$date_item[0].'-'.$date_item[1]);
            $last_day = $date->modify('-1 day');
            $data['date'] = $last_day->format( 'm-Y' );
        } elseif ($data['datebaddeb']=='D' && !is_date($data['date'])){
            $data['date'] = Today();
        }


        $items = $this->customer_model->bab_deb_load($data['date'],$data['threshold'], $data['debtor'], $data['page']);
        $data['items'] = $items['items'];
        $data['total'] = $items['total'];

        if( $data['total'] > 0 && round($data['total']/$data['limit']) > 1){
            $data['page_last'] = round($data['total']/$data['limit']);
        }

        if(  $data['datebaddeb']=='M' ){
            $data['date'] = date('m-Y',strtotime($data['date']));
        } else {
            $data['date'] = Today();
        }

        if(  !isset($data['datebaddeb']) || empty($data['datebaddeb']) ){
            $data['datebaddeb'] = 'D';
        }
        $this->ci->view('bad-deb/debtor',$data);

    }

    private function customer_process($trans_no=0,$step=1){
        $invoice = $this->customer_trans_model->get_customer_tran(ST_SALESINVOICE,$trans_no);
        $invoice_gl = $this->gl_model->search_transaction(ST_SALESINVOICE,$trans_no,array('openning'=>''));
        if( !$invoice_gl || count($invoice_gl) <1 || !isset($_SESSION['SysPrefs']->prefs['baddeb_sale_reverse']) || !isset($_SESSION['SysPrefs']->prefs['baddeb_sale_tax_reverse'])){
            return;
        }
        $data_baddebt = array('type'=>ST_SALESINVOICE,'type_no'=>$invoice->trans_no,'step'=>$step);
        $exist_baddebt = $this->common_model->get_row($data_baddebt,'bad_debts');

        if( !$exist_baddebt ) {
            $this->ci->db->insert('bad_debts',$data_baddebt);
            $baddebt_id = $this->ci->db->insert_id();
        } else {
            return;
        }

        if( $invoice_gl && !empty($invoice_gl) ){
            $gl_trans = new gl_trans();
            $gl_trans->set_value( array( 'type'=>ST_BADDEB, 'type_no'=>$baddebt_id, 'tran_date'=>Today() ) );
        }
        $dec = user_amount_dec();

        $posting = array();
        $negative = 0;
        if( $step==1 ){
            $negative = -1;
        } elseif ($step==2) {
            $negative = 1;
        }
        foreach ($invoice_gl AS $gl){
            $gl_amount = round2(abs($gl->amount),$dec);
            if( $gl_amount == round2(abs($invoice->ov_amount),$dec) ){

                $gl_trans->add_trans($gl->account,$negative*floatval($gl->amount) );
                $gl_trans->add_trans($_SESSION['SysPrefs']->prefs['baddeb_sale_reverse'],(-1)*$negative*floatval($gl->amount));
            } else if ( $gl_amount== round2(abs($invoice->ov_gst),$dec) ) {
                $gl_trans->add_trans($gl->account,$negative*floatval($gl->amount));
                $gl_trans->add_trans($_SESSION['SysPrefs']->prefs['baddeb_sale_tax_reverse'],(-1)*$negative*floatval($gl->amount));
            }
        }

        foreach ($gl_trans->trans AS $trans){
            $this->gl_trans->add_gl_trans($trans);
        }
    }

    private function supplier_view(){
        $this->supplier_model = $this->ci->model('supplier',true);
        $this->supplier_trans_model = $this->ci->model('supplier_trans',true);

        if( $this->ci->input->post('process') ){
            $this->supplier_process( $this->ci->input->post('process') );
        }else if ($this->ci->input->post('paid') ) {
            $this->supplier_process( $this->ci->input->post('paid'),$step = 2 );
        }
        $data['page_last'] = 1;
        $data['limit'] = page_padding_limit;
        $data['date'] =Today();
        if( $this->ci->input->post('datebaddeb')=='M'){
          //  bug($data['date']);die;
            $pieces = explode("-", $data['date']);
                $data['date'] = $pieces[1].'/'.$pieces[2];}
                else{$data['date'] =Today();}
        $data['threshold'] = 180;
        $data['page'] = 1;
        $data['page_last'] = 1;
        $data['limit'] = page_padding_limit;
        $data['supplier'] = null;
        $data['datebaddeb']=$this->ci->input->post('datebaddeb');

        if( $this->ci->input->post('date') ){
            $data['date']=$this->ci->input->post('date');
            $data['threshold'] = $this->ci->input->post('threshold');
            $data['supplier'] = $this->ci->input->post('supplier');

        }



        $page_submit = array('first','pre','next','last');
        foreach ($page_submit AS $na){
            if(  $this->ci->input->post($na) ){
                $data['page'] = $this->ci->input->post($na);
            }
        }

        if(  $data['datebaddeb']=='M' ){
            $data['datebaddeb']= $this->ci->input->post('datebaddeb');
            if( is_date($data['date']) ) {
                $date_item = array(date('m'),date('Y'));
            } else {
                $date_item = explode("-", $data['date']);
            }

            $date = new DateTime("1-".$date_item[0].'-'.$date_item[1]);
            $last_day = $date->modify('-1 day');
            $data['date'] = $last_day->format( 'm-Y' );
        } elseif ($data['datebaddeb']=='D' && !is_date($data['date'])){
            $data['date'] = Today();
        }
//            if( $this->ci->input->post('datebaddeb')=='M' && strlen($data['date'])>7){
//                 $pieces = explode("-", $data['date']);
//                 $data['date'] = $pieces[1].'/'.$pieces[2];
//            }
// //              if( $this->ci->input->post('datebaddeb')=='D' && strlen($data['date'])<8){
//                   $pieces = explode("/", $data['date']);
//                   $datenow=Today();
//                   $day=explode("-",$datenow);
//                   $data['date'] = $day[0].'-'.$pieces[0].'-'.$pieces[1];
//              }
//        bug($data['date']);die;
        $items = $this->supplier_model->bab_deb_load($data['date'],$data['threshold'], $data['supplier'], $data['page']);
        $data['items'] = $items['items'];
        $data['total'] = $items['total'];

        if( $data['total'] > 0 && round($data['total']/$data['limit']) > 1){
            $data['page_last'] = round($data['total']/$data['limit']);
        }
        if(  !isset($data['datebaddeb']) || empty($data['datebaddeb']) ){
            $data['datebaddeb'] = 'D';
        }
        $this->ci->view('bad-deb/supplier',$data);
    }

    private function supplier_process($trans_no=0,$step=1){
        $invoice = $this->supplier_trans_model->get_tran(ST_SUPPINVOICE,$trans_no);
        $invoice_gl = $this->gl_model->search_transaction(ST_SUPPINVOICE,$trans_no,array('openning'=>''));

        if( !$invoice_gl || count($invoice_gl) <1 || !isset($_SESSION['SysPrefs']->prefs['baddeb_purchase_reverse']) || !isset($_SESSION['SysPrefs']->prefs['baddeb_purchase_tax_reverse'])){
            return;
        }

        $data_baddebt = array('type'=>ST_SUPPINVOICE,'type_no'=>$invoice->trans_no,'step'=>$step);
        $exist_baddebt = $this->common_model->get_row($data_baddebt,'bad_debts');

        if( !$exist_baddebt ) {
            $this->ci->db->insert('bad_debts',$data_baddebt);
            $baddebt_id = $this->ci->db->insert_id();
        } else {
            return;
        }

        if( $invoice_gl && !empty($invoice_gl) ){
            $gl_trans = new gl_trans();
            $gl_trans->set_value( array( 'type'=>ST_BADDEB, 'type_no'=>$baddebt_id, 'tran_date'=>Today() ) );
        }
        $dec = user_amount_dec();

        $posting = array();
        $negative = 0;
        if( $step==1 ){
            $negative = -1;
        } elseif ($step==2) {
            $negative = 1;
        }
        foreach ($invoice_gl AS $gl){
            $gl_amount = round2(abs($gl->amount),$dec);
            if( $gl_amount == round2(abs($invoice->ov_amount),$dec) ){

                $gl_trans->add_trans($gl->account,$negative*floatval($gl->amount) );
                $gl_trans->add_trans($_SESSION['SysPrefs']->prefs['baddeb_purchase_reverse'],(-1)*$negative*floatval($gl->amount));
            } else if ( $gl_amount== round2(abs($invoice->ov_gst),$dec) ) {
                $gl_trans->add_trans($gl->account,$negative*floatval($gl->amount));
                $gl_trans->add_trans($_SESSION['SysPrefs']->prefs['baddeb_purchase_tax_reverse'],(-1)*$negative*floatval($gl->amount));
            }
        }

        foreach ($gl_trans->trans AS $trans){
            $this->gl_trans->add_gl_trans($trans);
        }
    }


}