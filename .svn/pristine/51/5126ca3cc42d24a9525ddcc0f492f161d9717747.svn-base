<?php
class BankAccount_Model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function get_bank_account($id)
	{
	    $result = $this->db->where('id',$id)->get('bank_accounts');
	    if( !is_object($result) ){
	        check_db_error("could not retreive bank account for", $this->db->last_query());
	    } else {
	        return $result->row();
	    }
	}
}