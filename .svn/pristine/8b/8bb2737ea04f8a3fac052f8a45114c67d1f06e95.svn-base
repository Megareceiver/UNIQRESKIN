<?php
class Bank_payment_Model extends CI_Model {
    function __construct(){
        parent::__construct();

    }

    function add_trans($data=array(),$currency=null,$rate=0){
        if( !array_key_exists('trans_date', $data) || !array_key_exists('bank_act', $data) ){
            return false;
        }
        $data['trans_date'] = date2sql($data['trans_date']);
        if (!$currency) {
            $bank_account_currency = get_bank_account_currency($data['bank_act']);
            if ($rate == 0){
                $to_bank_currency = get_exchange_rate_from_to($currency, $bank_account_currency, $data['trans_date']);
            } else
                $to_bank_currency = 1 / $rate;

            $data['amount'] = $data['amount'] / $to_bank_currency;
        }

        $data['amount'] = floatval( $data['amount'] );
        $this->db->reset();
        $sql = $this->db->insert('bank_trans',$data,true );

//         $this->db->reset();

        if ($err_msg == "")
            $err_msg = "The bank transaction could not be inserted";
        db_query($sql, $err_msg);
    }
}