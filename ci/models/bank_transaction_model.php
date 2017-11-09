<?php
class Bank_transaction_Model {
	function __construct(){
		global $ci;
		$this->bank = $ci->db;
	}

	function get_bank_balance_to($to, $account){
	    $to = date2sql($to);

	    $row = $this->bank->select('SUM(amount) AS sum')->where(array('bank_act'=>$account,'trans_date <'=>$to))->get('bank_trans')->row();

	    return ( $row && isset($row->sum) ) ? $row->sum : 0;
	}

    function get_bank_transactions($from, $to, $account){
    	$from = date2sql($from);
    	$to = date2sql($to);

    	$this->bank->select('*')->where('bank_act',$account);

    	if( $from ){
    	    $this->bank->where('trans_date >=',date2sql($from));
    	}
    	if( $to ){
    	    $this->bank->where('trans_date <=',date2sql($to));
    	}

    	$data = $this->bank->order_by('trans_date, id')->get('bank_trans')->result();

        return $data;

    }
}