<?php
class Tax_trans_Model {
	function __construct(){
		global $ci;
		$this->trans = $ci->db;
	}
}