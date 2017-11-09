<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GLGLLost {

    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->gl_account_model = module_model_load('gl_account');
    }
    function index(){
        return $this->gl_missing_account();
    }

    private function gl_missing_account(){
        $data = array(
            'datefrom'=>'1-1-2015',
            'dateto'=>'31-12-2015',
            'items'=>array()
        );

        //         $glAccTypeJournals = $this->ci->db->where(array('tran_date >='=>date2sql($data['datefrom']),'tran_date <='=>date2sql($data['dateto']),'type'=>ST_JOURNAL))->group_by('account')->select('account')->get('gl_trans');
        //         if( is_object($glAccTypeJournals) ) foreach ($glAccTypeJournals->result() AS $journalAcc){
        //             $data['journalAcc'][$journalAcc->account] = $journalAcc->account;
        //         }

        //         $glWrong = $data['journalAcc'];

        $acc_using = array();
        $data['accClass'] = $this->gl_account_model->get_classes(false);
        if( count($data['accClass']) > 0 ) foreach ($data['accClass'] AS $k=>$class){
            $type_items = $this->gl_account_model->get_types(false, $class->cid, -1);
            if( count($type_items) > 0 ) foreach ($type_items AS $kk=>$type){
                $accounts = $this->gl_account_model->get_accounts(null, null, $type->id);
                if( count($accounts) >0 ) foreach ($accounts AS $kkk=>$acc){
                    $acc_using[] = $acc->account_code;
                }
            }
        }

        $this->ci->db->where(array('tran_date >='=>date2sql($data['datefrom']),'tran_date <='=>date2sql($data['dateto'])));
        $this->ci->db->where('amount <>',0);
        $result = $this->ci->db->where_not_in('account',$acc_using)->get('gl_trans');

        if( is_object($result) ){
            $data['items'] = $result->result();
        }
//         bug($data['items']);die;

        page('GL Account Missing');
        start_form();
        module_view('gl_missing',$data);
        end_form();
        end_page();

    }
}