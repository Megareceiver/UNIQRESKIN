<?php
class Config_Model {
	function __construct(){
		global $ci;
		$this->co = $ci->db;
		$this->co->reset();
	}

	function get_user_val($name=''){
		$value = null;
		if ( !$this->co->field_exists('amount_dec', 'users')){
			return $value;
		}

		$data = $this->co->select($name)->where('id',$_SESSION["wa_current_user"]->user)->get('users')->row();
		if( $data && isset($data->$name) ){
			$value = $data->$name;
		}
		return $value;
	}

	function get_sys_pref_val($name=''){
		$value = null;
		$this->co->reset();
		$data = $this->co->like('name',$name)->get('sys_prefs')->row();
// 		display_error( $this->co->last_query() );
		if( $data && isset($data->value) ){
			$value = $data->value;
		}
		return $value;

	}

	function curr_default(){
		$curr = $this->get_sys_pref_val('curr_default');
		$row = $this->co->where('curr_abrev',$curr)->get('currencies')->row();
		return $row;
	}

	function currency_options(){
		$options = array();
		$data = $this->co->order_by('currency', 'ASC')->get('currencies')->result();
		if( $data ){
			foreach ($data AS $row){
				$options[$row->curr_abrev] = $row->currency;
			}
		}
		return $options;
	}

	function exchange_rate_get($curr_code='',$date=''){
		$this->co->where('curr_code',$curr_code);
		$this->co->where('date_ <=',$date);

		$data = $this->co->get('exchange_rates')->row();

		if( $data && $data->rate_sell ){
			return $data->rate_sell;
		} else {
			return 1;
		}
	}

	function sales_type_get($id=1){
		return $this->co->where('id',$id)->get('sales_types')->row();
	}
}