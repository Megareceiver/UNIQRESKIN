<?php
class supplier_invoice_goods {
	function input($name,$val){
		global $ci;
		$items = array();

        $goods_invoice = $ci->db->select('trans_no,reference')->where( array('imported_goods'=>1,'paid_tax'=>0,'type'=>ST_SUPPINVOICE) )->get('supp_trans')->result();
        if( $goods_invoice && !empty($goods_invoice) ){
            foreach ($goods_invoice AS $inv){
                $items[$inv->trans_no] = (object)array('id'=>$inv->trans_no,'title'=>$inv->trans_no.' - '.$inv->reference);
            }
        }

        if( !$val ){
            $val = input_val($name);
        }
		return $ci->finput->options( $name,$items,$val,'--Goods Invoice--', "combo2" ,'off');
	}
}