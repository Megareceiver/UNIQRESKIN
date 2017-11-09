<?php
class gl_acc {
	function input($name,$val,$group=0,$input_return_type='html',$readonly=false,$skip_bank_accounts=1){
		global $Ajax, $ci;


// 		$ci->db->reset();
// 		$ci->db->select('chart.account_code, chart.account_name, type.name, chart.inactive, type.id');
// 		$ci->db->from('chart_master AS chart');
// 		$ci->db->join('chart_types AS type','chart.account_type=type.id');
// 		if( !$skip_bank_accounts ){
// 		    $ci->db->join('bank_accounts AS acc','chart.account_code=acc.account_code');
// 		    $ci->db->where('acc.account_code IS NULL');
// 		}

//         $gl_acc = $ci->db->get()->result();

        $ci->db->reset();
        $items = $ci->db->select('id, name AS title')->get('chart_types')->result();

        if( $items && !empty($items)) foreach ($items AS $k=>$gl_type){
            $ci->db->reset();
            $items[$k]->items = $ci->db->select("account_code AS id, CONCAT(account_code,'  ',account_name) AS title",false)->where(array('account_type'=>$gl_type->id))->get('chart_master')->result();
        }




		return $ci->finput->options( $name,$items,$val,'--Select GL Account--', "combo2 ");
	}
}