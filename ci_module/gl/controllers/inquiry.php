<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GLInquiry {

    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->gl_account_model = module_model_load('gl_account');
        $this->gl_trans_model = module_model_load('trans');
//         if( !class_exists('TIC') ){
//             require_once BASEPATH.'thirdparty/tic/tic.php';
//         }

    }

    function account(){
        $data = array(
            'classBackground'=>array('6495ED','008B8B','5F9EA0','7FFFD4'),
            'groupBackground'=>array('F8F8FF','DCDCDC','FFFAF0','A9A9A9','FFF8DC','DEB887'),
            'datefrom'=>'1-1-2015',
            'dateto'=>'31-12-2015',
            'account'=>1200,
            'dimension'=>0,'dimension2'=>0,
//             'journalAcc'=>array(),

        );

        $act_name = $this->gl_account_model->account_name($data['account']);

        $transactions = $this->gl_trans_model->get_transactions($data['datefrom'], $data['dateto'], null,$data['account'],$data['dimension'],$data['dimension2']);
// bug( count($transactions) ); die;
        $data['items'] = $transactions;
//         bug($transactions);die;

        // bug($data['journalAcc']);die;
        //         bug($data['accClass']); die;


        page('GL Balacne | Check');
        global $Ajax;
        if(in_ajax()) {
            $Ajax->activate('_page_body');
        }
        start_form();

        $this->ci->smarty->assign('current',array('credit'=>0,'debit'=>0));
        module_view('inquiry/gl_account',$data);
        end_form();
        end_page();
    }

    function balance_check(){
        $check = $this->ci->uri->segment(4);


    }
}