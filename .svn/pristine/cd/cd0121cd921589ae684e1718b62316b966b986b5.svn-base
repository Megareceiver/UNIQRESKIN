<?php
class Supp_trans_Model {
	function __construct(){
		global $ci;
		$this->trans = $ci->db;
	}
	
	function update_supp_trans($data){
	    if( !isset($data['type']) ) return false;
	    
	    $new = $trans_no==0;
	    $date_ = $data['tran_date'];
	    $data['tran_date'] = date2sql($date_);
	    
	    $data['due_date'] = ($data['due_date'] == "") ? '0000-00-00' : date2sql($data['due_date']);
	    
	    if ($new)
	        $data['trans_no'] = get_next_trans_no($data['type']);
	    
	    if ( floatval($data['rate']) == 0){
	        $curr = get_supplier_currency( $data['supplier_id'] );
	        $data['rate'] = get_exchange_rate_from_home_currency($curr, $date_);
	    }
	    
	    $sql = $this->trans->insert('supp_trans',$data,true );
	    db_query($sql, $err_msg);
	    add_audit_trail($data['type'], $data['trans_no'], $date_);
	    
	    return $data['trans_no'];
	        
	     
	}
}