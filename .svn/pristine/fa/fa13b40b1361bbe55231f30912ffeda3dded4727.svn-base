<?php
class Gl_account_model {
	function __construct(){
		global $ci;
		$this->gl = $ci->db;
	}

	function openning_group(){
		$this->gl->select('*, cl.class_name AS classname');
		$this->gl->join('chart_class cl', 'cl.cid = type.class_id', 'left');

		$this->gl->where_in('cl.ctype',array(1,2,3));

		$data = $this->gl->get('chart_types AS type')->result();


		return $data;

	}
}