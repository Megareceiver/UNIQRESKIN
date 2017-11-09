<?php
class tax_inclusive  {
	var $options = array('1',0);
	function __construct(){
		//finput::tax_inclusive = $this->input();
	}
	function input($name,$val){
		global $Ajax;
		$onchange = null;

		$onchange = ' onclick="JsHttpRequest.request(document.getElementsByName(\'_'.$name.'_update\')[0], this.form);" ';
		$input = '<p class="groupinput groupradio" >'
				.'<input type="radio" name="'.$name.'" value="1" '.( $val==true ? 'checked' : null ).' '.$onchange.' ><span>Yes</span>'
				.'<input type="radio" name="'.$name.'" value="0" '.( $val==false ? 'checked': null ).' '.$onchange.' ><span>No</span>'
				.'</p>';
		$input.='<input type="submit" title="Select" value="1" name="_'.$name.'_update" aspect="fallback" style="display: none;" class="combo_select">';

		$Ajax->addUpdate($name, $name, $val);
		return $input;
	}

}