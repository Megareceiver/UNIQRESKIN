<?php
class currency extends CI_finput{
	function input($name,$val,$attributes=null,$disabled=false){
		$ci = get_instance();
// 		if( !$value ) {
// 		    $value = get_company_pref('curr_default');
// 		}

        $options = array();

		$items_query = $ci->db->order_by('currency', 'ASC')->get('currencies');
		if( is_object($items_query) ){ foreach ($items_query->result() AS $curr){
            $options[] = array('id'=>$curr->curr_abrev,'title'=>$curr->curr_abrev.' - '.$curr->currency);
	    }}
        $class = 'combo2 form-control';
        if( isset($attributes['class']) ? $attributes['class']: NULL){
            $class .= " ".$attributes['class'];
        }
		return $this->options($name,$options,$val,'-No Currency Filter-',$class);
	}
}