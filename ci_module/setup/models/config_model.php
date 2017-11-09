<?php
class Setup_Config_Model extends CI_Model {
	/*
	 * @see CI_Model::__construct()
	 */
	public function __construct() {
		// TODO: Auto-generated method stub

	}

	function get_sys_pref_val($name=''){
	    $value = null;
	    $this->db->reset();
	    $data = $this->db->like('name',$name)->get('sys_prefs')->row();
	    if( $data && isset($data->value) ){
	        $value = $data->value;
	    }
	    return $value;

	}
}