<?php
class Gl_Model {
	function __construct(){
		global $ci;
		$this->gl = $ci->db;
	}

	function get_gl_accounts(){
		$this->gl->select('chart.account_code, chart.account_name, type.name AS type_name, chart.inactive, type.id');
		$this->gl->from('chart_master AS chart, chart_types AS type');
		$this->gl->join('bank_accounts AS acc', 'chart.account_code=acc.account_code', 'left');

		$this->gl->where('chart.account_type','type.id',false);
		$this->gl->where('chart.inactive',0);
		$data = $this->gl->get()->result();

		return $data;
	}
}