<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Credit_note extends ci {
	function __construct() {
		global $ci;
		$this->ci = $ci;
		$this->customer_trans_model = $this->model('customer_trans',true);
		$this->contact_model = $this->model('crm',true);
	}


}