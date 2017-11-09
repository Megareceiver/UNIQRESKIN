<?php
class Void_Model {
	function __construct(){
		global $ci;
		$this->db = $ci->db;
		$this->config = $ci->model('config',true);
		$this->common = $ci->model('common',true);
	}

	function supplier_transaction($type, $type_no){

        $this->common->update(array('amount'=>0),'gl_trans',array('type'=>$type, 'type_no'=>$type_no),false,0);
//         $this->common->update( array('quantity'=>0,'unit_price'=>0,'unit_tax'=>0) , 'supp_invoice_items',array('supp_trans_type'=>$type, 'supp_trans_no'=>$type_no),false,0);
        $this->common->delete('supp_invoice_items', array('supp_trans_type'=>$type, 'supp_trans_no'=>$type_no),0,false);
	}
}