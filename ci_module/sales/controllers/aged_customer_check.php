<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SalesAgedCustomerCheck {

    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->db = $ci->db;
        $page_security = 'SA_FISCALYEARS';
        $this->debtor_model = module_model_load('debtor');
        $this->opening_trans_model = module_model_load('trans','opening');
        $this->gl_model = module_model_load('gl','gl');

        $this->ci->smarty->assign('typeCredit',array(ST_CUSTCREDIT,ST_CUSTPAYMENT,ST_BANKDEPOSIT));
        $this->ci->smarty->setTemplateDir(MODULEPATH.'/sales/views/');

    }

    function index() {
        $today = Today();
        $gl_tran_model = module_model_load('trans','gl');
        $page = array(
            'title'=>_('Aged Customer Check'),
            'check'=>true,
            'total_only'=>true,
            'zero_line'=>false,
            'balance_show'=>false,
            'trans'=>array(),
            'date_begin' => input_val('date_begin'),
            'date_end' => input_val('date_end'),
        );
        if( !$page['date_begin'] ){
           $page['date_begin'] = date('Y',strtotime($today)).'-1-1';
           $page['date_begin'] = '2015-1-1';
        }
        if( !$page['date_end'] ){
            $page['date_end'] = Today();
            $page['date_end'] = '2015-08-31';
        }

        $debtor_no= $this->ci->uri->segment(3);

        if( $debtor_no ){

            $page['total_only'] = false;
        } else {
            $page['gl_opening'] = $gl_tran_model->get_gl_trans_from_to(NULL, $page['date_begin'], 1200 );
        }

        $debtors = $this->debtor_model->get_debtors($debtor_no,$this->limit,true);


        foreach ($debtors AS $k=>$deb){
            $page['trans'][$k] = array(
                'debtor'=>$deb,
                'agedReports'=>$this->gl_model->get_receivable(1200, $deb->debtor_no, $page['date_end'],true,$page['total_only']),
//                 'balanceReports'=>$this->debtor_model->get_transactions($deb->debtor_no, $page['date_begin'],$page['date_end']),

//                 'agedReports_opening'=>$this->gl_model->get_receivable(1200, $deb->debtor_no, $page['date_end'],array(ST_OPENING_CUSTOMER)),
//                 'opening'=>$this->debtor_model->opening_balance($deb->debtor_no,$page['date_begin']),
            );
            if( $page['total_only'] ){
                $page['trans'][$k]['aged_old'] = get_customer_details($deb->debtor_no, $page['date_end'], false);
                $page['trans'][$k]['gl_balance'] = $gl_tran_model->get_gl_trans_from_to($page['date_begin'], $page['date_end'], 1200 , 0, 0, 2,$deb->debtor_no );
            }

        }



        page('Customer Analyses | '.$page['title']);
        global $Ajax;
        if(in_ajax()) {
            $Ajax->activate('_page_body');
        }
        start_form();
        module_view('aged_customer/fillter',array('date_begin'=>$page['date_begin'],'date_end'=>$page['date_end']));
        end_form();


        $this->ci->smarty->assign('aged_total',0);
        $this->ci->smarty->assign('aged_old_total',0);
        $this->ci->smarty->assign('balance_total',0);
        $this->ci->smarty->assign('allocation_total',0);
        $this->ci->smarty->assign('gl_total',0);
        $this->ci->smarty->assign('gl_balance_total',0);
        if( $page['total_only'] ){
            module_view('aged_customer/debtor_aged_and_balance_overview',$page);
        } else {
            module_view('aged_customer/debtor_aged_and_balance',$page);
        }

        end_page();

    }

    function gl_trans(){
        $gl_model = module_model_load('gl','gl');

        $debtors = $this->debtor_model->get_debtors();
//         bug($debtors);die;

        $gl_receivable = $gl_model->get_receivable(1200);

        page('Customer Analyses | GL trans wrong posting');
        module_view('report/gl_receivable_gl_trans',array('trans'=>$gl_receivable,'debtors'=> $this->debtor_model->get_debtors(),'title'=>_('GL Transactions Checking')));
        end_page();
    }

    function analysis(){
        $page = array(
            'title'=>_('GL Transactions Checking'),
            'check'=>true,
            'total_only'=>true,
            'date_begin' => '2015-1-1',
            'date_end' => '2015-8-31',
            'debtor_inclue_ob'=>true,
        );
        $debtor_no= $this->ci->uri->segment(4);
        $get_debtors_where = array();

        $page['total_only'] = ($debtor_no) ? false : true;

        if( $debtor_no ){
            $get_debtors_where['debtor_no'] = $debtor_no;
            $page['total_only'] = false;
        }
        $debtors = $this->debtor_model->get_debtors($get_debtors_where);


        $trans = array();
        $debtor_selected = array();
        foreach ($debtors AS $k=>$deb){
            $trans[$k] = array(
                'debtor'=>$deb,
                'opening'=>$this->opening_trans_model->balance_total(ST_OPENING_CUSTOMER,$deb->debtor_no),
                'trans'=>$this->gl_model->get_receivable(1200, $deb->debtor_no, $page['date_end'],$page['debtor_inclue_ob'])
            );
            $debtor_selected[] = $deb->debtor_no;
        }
        $page['trans'] = $trans;
        if( !$debtor_no && !$page['debtor_inclue_ob'] ){
            $page['gl_openning'] = $this->gl_model->opening(1200,ST_OPENING,$page['date_begin']);

            $page['customer_openning'] = $opening_trans_model->balance_total(ST_OPENING_CUSTOMER);

        }

        page('Customer Analyses | GL trans posting');
        module_view('aged_customer/debtor_group',$page);
        end_page();
    }

    function customer_balance(){
        $page = array(
            'title'=>_('GL Transactions Checking'),
            'check'=>true,
            'total_only'=>true,
            'date_begin' => '2015-1-1',
            'date_end' => '2015-8-31',
            'debtor_inclue_ob'=>true,
        );


        $get_debtors_where = array();
        $debtor_no= $this->ci->uri->segment(4);
        if( $debtor_no ){
            $get_debtors_where['debtor_no'] = $debtor_no;
            $page['total_only'] = false;
        }
        $debtors = $this->debtor_model->get_debtors($get_debtors_where,$this->limit);
        $trans = array();
        foreach ($debtors AS $k=>$deb){
            $trans[$k] = array(
                'debtor'=>$deb,
                'opening'=>$this->opening_trans_model->balance_total(ST_OPENING_CUSTOMER,$deb->debtor_no),
                'trans'=>$this->debtor_model->get_transactions($deb->debtor_no, $page['date_begin'],$page['date_end'],$page['total_only'])
            );
            $trans[$k]['balance_old'] =  $this->debtor_model->get_transactions_old($deb->debtor_no, $page['date_begin'],$page['date_end'],true);
// //             $trans_total = $this->debtor_model->get_transactions($deb->debtor_no, $page['date_begin'],$page['date_end'],true);
// //             bug($trans_total);

//             $trans_old_detail =  $this->debtor_model->get_transactions_old($deb->debtor_no, $page['date_begin'],$page['date_end'],false);
//             bug($trans_old_detail);
//             bug($trans[$k]); die;

        }
        $page['trans'] = $trans;


        $this->ci->smarty->assign('charges_total',0);
        $this->ci->smarty->assign('credits_total',0);
        $this->ci->smarty->assign('allocated_total',0);
        $this->ci->smarty->assign('outstanding_total',0);

        $this->ci->page->add_css('report_html.css');
        page('Customer Analyses | Customer Balance');
        module_view('aged_customer/customer_balance',$page);
        end_page();
    }

}