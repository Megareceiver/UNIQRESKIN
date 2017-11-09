<?php
class BankReconciled_Model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function get_max_reconciled($date, $bank_account)
	{
	    $date = date2sql($date);
	    if ($date == 0)
	        $date = '0000-00-00';

	    $this->db->select("MAX(reconciled) as last_date",false);
	    $this->db->select("SUM(IF(reconciled<='$date', amount, 0)) as end_balance",false);
	    $this->db->select("SUM(IF(reconciled<'$date', amount, 0)) as beg_balance",false);
	    $this->db->select("SUM(amount) as total",false);

	    $this->db->where('bank_act',$bank_account);
//         $this->db->where("trans.reconciled IS NOT NULL");

	    if( is_date($date) ){
	        $this->db->where('trans_date <=',$date);
	    }

	    $result = $this->db->get('bank_trans trans');

	    if( !is_object($result) ){
	        check_db_error("Cannot retrieve reconciliation data", $this->db->last_query());

	    } else {
            return $result->row();
	    }
	}

	function get_ending_reconciled($bank_account, $bank_date)
	{

	    $this->db->from('bank_accounts')
	    ->get('ending_reconcile_balance')
	    // 	       ->where('id',$bank_account)
	    // 	       ->where('last_reconciled_date',date2sql($bank_date))
	    ;
	    $result = $this->db->get();
	    if( !is_object($result) ){
	        check_db_error("Cannot retrieve last reconciliation", $this->db->last_query());

	    } else {
	        return $result->row();
	    }
	}

	function get_bank_account_reconcile($bank_account, $reconcile_date, $show_reconciled=true)
	{
	    $reconcile_date = date2sql($reconcile_date);

	    $this->db->select("type, trans_no, ref, trans_date, amount,	person_id,cheque, person_type_id, reconciled, id");
	    $this->db->select('IF(amount < 0,ABS(amount),0) AS credit',false);
	    $this->db->select('IF(amount > 0,amount,0) AS debit',false);

	    $this->db->from("bank_trans");
	    $this->db->where("bank_act",$bank_account);

	    $this->db->where('type NOT IN ('.ST_OPENING_BANK.')');

	    if( !$show_reconciled ){
	        $this->db->where("reconciled IS NULL");
	    } else {
	        $this->db->where(" (reconciled IS NULL OR reconciled='". ($reconcile_date) ."') ");
	    }
	    $this->db->where("ABS(amount) <> 0");

	    $this->db->where('trans_date <=',$reconcile_date);
	    $result = $this->db->order_by('trans_date, id')->get();

	    if( !is_object($result) ){
	        check_db_error("Cannot retrieve reconciliation data", $this->db->last_query());

	    } else {
	        return $result->result();
	    }

	}

	function get_reconciliation_list($account=0){
	    $this->db->from('bank_trans')->select('reconciled');
	    $this->db->where('bank_act',$account);
	    $this->db->where('reconciled IS NOT NULL');
	    $query = $this->db->group_by('reconciled')->get();

	    $data = array();
	    if( $query->num_rows() >0 ) foreach ($query->result() AS $r){
	        $date = sql2date($r->reconciled);
	        $data[] = array('id'=>$date,'title'=>$date);
	    }

	    $option = array('new'=>_('New'));
	    return array_merge($option,$data);

	}
}