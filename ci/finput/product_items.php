<?php
class product_items{
	function input($name,$val,$group=0,$input_return_type='html'){
		global $Ajax, $ci;
		$onchange = null;

		$items = $ci->db->select("stock_id AS id,  CONCAT(stock_id,'-', description) AS title, category_id AS cat, units, description AS descr ",false)->get('stock_master')->result();

		if( !$val ){
			$val = $ci->input->post($name);
		}

		if( !empty($items) && $val=='' ){

			$val = $_POST[$name] = $items[0]->id;
		}

		if( $input_return_type =='value')
			return $val;

		$class = null;

		$options = array();
		foreach ($items AS $opt){
			if( !isset($options[$opt->cat]) ){
				$category = $ci->db->select("description AS title")->where('category_id',$opt->cat)->get('stock_category')->row();

				if( isset($category->title) ){
					$options[$opt->cat] = array('title'=>$category->title,'items'=>array());
				}

			}

			if(isset($options[$opt->cat])){
				$options[$opt->cat]['items'][] = $opt;
			}

		}

		$html ='<select class="'.$class.' products" title="Items Products" name="'.$name.'" >';
		foreach ($options AS $opt){
			if( is_array($opt['items']) ){
				$html.=' <optgroup label="'.$opt['title'].'">';
				foreach ($opt['items'] AS $ite){
					$value = ( $ite->id==$val ) ? ' selected ' : null;
					$html.='<option value="'.$ite->id.'" '.$value.' units="'.$ite->units.'" desc="'.$ite->descr.'" >'.$ite->title.'</option>';
				}
				$html.=' </optgroup>';

			}

		}
		$html.='</select>';
		return '<span id="_'.$name.'_sel" class="select">'.$html.'</span>';

// 		return $ci->finput->options( $name,$items,$val,null);
	}

}