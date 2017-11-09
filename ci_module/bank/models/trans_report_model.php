<?php
class BankTransReport_Model extends CI_Model {
    function __construct(){
        parent::__construct();

    }

    function get_bank_transactions($from, $to, $account){
        $from = date2sql($from);
        $to = date2sql($to);

        $this->db->select('*')->where('bank_act',$account);
        $this->db->where('amount <> 0 ');
        if( $from ){
            $this->db->where('trans_date >=',date2sql($from));
        }
        if( $to ){
            $this->db->where('trans_date <=',date2sql($to));
        }

        $data = $this->db->order_by('trans_date, id')->get('bank_trans')->result();

        return $data;

    }

    function get_bank_balance_to($to, $account){
        $to = date2sql($to);

        $row = $this->db->select('SUM(amount) AS sum')->where(array('bank_act'=>$account,'trans_date <'=>$to))->get('bank_trans')->row();

        return ( $row && isset($row->sum) ) ? $row->sum : 0;
    }
}