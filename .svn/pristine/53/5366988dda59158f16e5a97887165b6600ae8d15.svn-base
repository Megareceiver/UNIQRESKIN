<?php
class Bank_account_Model {
	function __construct(){
		global $ci;
		$this->bank = $ci->db;
	}
	
	function get_default_account($curr_code=''){
		global $ci;
		$config_model = $ci->model('config',true);
		$home_curr = $config_model->get_sys_pref_val('curr_default');
		
		$this->bank->select("*,bank_curr_code='$home_curr' as fall_back",false);
		$this->bank->from('bank_accounts');
		$this->bank->where('bank_curr_code',$curr_code);
		$this->bank->or_where('bank_curr_code',$home_curr);
		
		$data = $this->bank->group_by('fall_back, dflt_curr_act desc')->get()->row();
// 		bug( $this->bank->last_query() );
// 		bug( $data );die;
		return $data;
	}
}