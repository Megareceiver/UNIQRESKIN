<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CustomerCheck {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->db = $ci->db;

    }

    function ob_fix(){
        $config_model = $this->ci->model('config',true);
        $bank_model = $this->ci->model('bank',true);

        $this->db->select("(SELECT COUNT(gl.counter) FROM gl_trans AS gl WHERE gl.amount <> 0 AND gl.type=ob.type AND gl.type_no = ob.trans_no) AS gl_count",false);
        $ob_items = $this->db->where('ob.type',ST_OPENING_CUSTOMER)->select('ob.*')->get('opening_sale AS ob');

        if( is_object($ob_items) && $ob_items->num_rows > 0 ) foreach ($ob_items->result() AS $ite){
//             bug($ite);die;
            $data = array();
            if( !$ite->curr_rate ){
                $data['curr_rate'] = 1;
                $data['currency'] = get_company_pref('curr_default');
                $ite->currency = $data['currency'];
            }

            if( !empty($data) ){
                $this->db->where('id',$ite->id)->update('opening_sale',$data);
            }
            $amount = 0;
            if( $ite->credit > 0 ){
                $amount = -$ite->credit;
            }elseif( $ite->debit > 0 ){
                $amount = $ite->debit;
            }


            $customer_tran_where = array('type'=>$ite->type,'trans_no'=>$ite->trans_no);
            $customer_tran = $this->db->where($customer_tran_where)->get('debtor_trans');

            if( is_object($customer_tran) && $customer_tran->num_rows ==1 ){
                $tran = $customer_tran->row();
                $customer_tran_update = array();

                if( abs($tran->ov_amount) != abs($amount) ){

                    $customer_tran_update['ov_amount'] = $amount;
                    $this->db->where($customer_tran_where)->update('debtor_trans',$customer_tran_update);
                }
            }

            if( $ite->gl_count < 1 ){
                $gl_trans = $this->ci->gl_trans;
                $gl_trans->trans = array();
                $gl_trans->set_value('type_no',$ite->trans_no);
                $gl_trans->set_value('tran_date',$ite->tran_date);
                $gl_trans->set_value('type',$ite->type);
                $gl_trans->set_value('person_type_id',ST_OPENING_CUSTOMER);
                $gl_trans->customer($ite->customer,$ite->branch);


                $currency = $config_model->get_sys_pref_val($ite->currency);
                $bank_account_default = $bank_model->bank_accounts_default($currency);
                // 	                die("do write_opening bank_account_default=$bank_account_default");
                $gl_trans->add_trans($gl_trans->receivables_account,	-$amount);
                $gl_trans->add_trans($bank_account_default,				$amount);
                $gl_trans->add_trans($gl_trans->receivables_account,	$amount);
                $gl_trans->add_trans($bank_account_default,				-$amount);

                $gl_trans->insert_trans(null);
            }

        }
    }

//     function gl
}