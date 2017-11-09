<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GLBalanceCheck {

    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->db = $ci->db;
        $this->gl_account_model = module_model_load('gl_account');
        if( !class_exists('TIC') ){
            require_once BASEPATH.'thirdparty/tic/tic.php';
        }

    }

    function index(){
        $data = array(
            'classBackground'=>array('6495ED','008B8B','5F9EA0','7FFFD4'),
            'groupBackground'=>array('F8F8FF','DCDCDC','FFFAF0','A9A9A9','FFF8DC','DEB887'),
            'dimension'=>0,
            'dimension2'=>0,
            'datefrom'=>'28-09-2015',
            'dateto'=>'28-09-2015',
            'journalAcc'=>array(),

        );



        $glAccTypeJournals = $this->ci->db->where(array('tran_date >='=>date2sql($data['datefrom']),'tran_date <='=>date2sql($data['dateto']),'type'=>ST_JOURNAL))->group_by('account')->select('account')->get('gl_trans');
        if( is_object($glAccTypeJournals) ) foreach ($glAccTypeJournals->result() AS $journalAcc){
            $data['journalAcc'][$journalAcc->account] = $journalAcc->account;
        }

        $glWrong = $data['journalAcc'];

        $data['accClass'] = $this->gl_account_model->get_classes(false);
        if( count($data['accClass']) > 0 ) foreach ($data['accClass'] AS $k=>$class){
            $type_items = $this->gl_account_model->get_types(false, $class->cid, -1);

            if( count($type_items) > 0 ) foreach ($type_items AS $kk=>$type){
                $accounts = $this->gl_account_model->get_accounts(null, null, $type->id);
                if( count($accounts) >0 ) foreach ($accounts AS $kkk=>$acc){
//                     $accounts[$kkk]->balance_pre = $this->gl_account_model->get_balance($acc->account_code, $data['dimension'], $data['dimension2'], null, $data['datefrom'], false, false);
                    $accounts[$kkk]->balance_current = $this->gl_account_model->get_balance($acc->account_code, $data['dimension'], $data['dimension2'], $data['datefrom'], $data['dateto'], true, true);
                    if( in_array($acc->account_code, $glWrong) ){
                        unset($glWrong[$acc->account_code]);
                    }
                }

                $type_items[$kk]->account = $accounts;
            }

            $data['accClass'][$k]->types = $type_items;
        }

// bug($glWrong);die;

// bug($data['journalAcc']);die;
//         bug($data['accClass']); die;


        page('GL Balacne | Check');
        global $Ajax;
        if(in_ajax()) {
            $Ajax->activate('_page_body');
        }
        start_form();

        $this->ci->smarty->assign('current',array('credit'=>0,'debit'=>0));
        module_view('account_class/balance_check',$data);
        end_form();
        end_page();

    }

    function supplier(){
        $data = array(
            'start_date'=>begin_fiscalyear(),
            'end_date'=>end_fiscalyear(),
            'gl_account'=>get_company_pref("creditors_act"),
            'gl_trans'=>array()
        );
        $data["gl_detail"] = $this->db->where('account_code',$data['gl_account'])->get('chart_master')->row();

        /*
         * SUM gl trans
         */
        $this->db->from('gl_trans AS gl');
        $this->db->select("SUM(gl.amount) AS total, gl.type")->group_by('gl.type');

        $this->db->where('gl.tran_date BETWEEN "'. date2sql($data['start_date']). '" and "'. date2sql($data['end_date']).'"');
        $this->db->where('gl.account',$data['gl_account']);

        $gl_trans = $this->db->get();
        if( $gl_trans->num_rows > 0 ){
            $data['gl_trans'] = $gl_trans->result();
        }

        /*
         * SUM supplier trans
         */
         $this->db->select("SUM(tran.amount) AS total, tran.type")->group_by('tran.type');
         $this->db->where('tran.tran_date BETWEEN "'. date2sql($data['start_date']). '" and "'. date2sql($data['end_date']).'"');
         $supp_trans = $this->db->get('supp_trans AS tran');
         if( $supp_trans->num_rows > 0 ){
             $data['supp_trans'] = $supp_trans->result();
         }


        page('GL Balacne | Check');
//         global $Ajax;
//         if(in_ajax()) {
//             $Ajax->activate('_page_body');
//         }
//         start_form();

        $this->ci->smarty->assign('current',array('credit'=>0,'debit'=>0));
        module_view('account_class/balance_check_type',$data);
//         end_form();
        end_page();
    }
}