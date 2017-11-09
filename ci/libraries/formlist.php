<?php
class formlist {

	function __construct(){
		global $Ajax, $ci;
		$this->ci = $ci;
	}

	static function editAjax(){
		return '<a href="javascript:void(0)" class="edit" ><img border="0" title="Edit" src="'.site_url().'themes/template/images/edit.gif"></a>';
	}
	static function saveAjax(){
		return '<a href="javascript:void(0)" class="save" ><img border="0" title="Edit" src="'.site_url().'themes/template/images/ok.gif"></a>';
	}
	static function removeAjax(){
		return '<a href="javascript:void(0)" class="remove" ><img border="0" title="Edit" src="'.site_url().'themes/template/images/delete.gif"></a>';
	}
	static function cancelAjax(){
		return '<a href="javascript:void(0)" class="cancel" ><img border="0" title="Edit" src="'.site_url().'themes/template/images/cancel.png"></a>';
	}
	static function input_taxes($template){
		global $Ajax, $ci;
		$name = ( isset($template['name']) )?$template['name']:null;
		$transaction_type = ( isset($template['trans']) )?$template['trans']:null;
        $group = ( isset($template['group']) )?$template['group']:2;

		return $ci->finput->inputtaxes(null,$name,null,$group,'input',$transaction_type);
	}

	static function input_products($template){
		global $Ajax, $ci;
		$name = ( isset($template['name']) )?$template['name']:null;
		return $ci->finput->product_items(null,$name,null,'input');
	}

	static function input_tax_title($template){
		global $ci;
		$name = ( isset($template['name']) )?$template['name']:null;
		$value = ( isset($template['value']) )?$template['value']:null;
		$title = ( isset($template['title']) )?$template['title']:null;
		$group = ( isset($template['group']) )?$template['group']:2;
		$readonly = ( isset($template['readonly']) )?$template['readonly']:false;

		if( $value && $readonly ){

		}

		if( isset($template['input']) && $template['input'] != true ){
			return $ci->finput->inputstring($template['title'],null,$value,'bootstrap');
		}
		return $ci->finput->inputtaxes(null,$name,$value,$group,'inputtitle');
	}


}