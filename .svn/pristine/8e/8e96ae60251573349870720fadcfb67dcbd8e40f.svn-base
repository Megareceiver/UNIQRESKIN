<?php
class multitaxes {
	function input($name,$val){
		global $ci;
		$taxes =$ci->api_membership->get_data('taxes');

		$html= '<div class="form-control multicheckbox" style="clear: both;" >';

		  if( isset($taxes->options) && !empty($taxes->options) ){
		      foreach ($taxes->options AS $tax){
		          $idname = "tax".$tax->id;
		          $html.= '<label for="'.$idname.'"><input type="checkbox" '.( ((is_array($val) && in_array($tax->id, $val)) OR ($val==$tax->id)) ? 'checked' : null ).' id="'.$idname.'" name="'.$name.'['.$tax->id.']" value="'.$tax->id.'" />'.$tax->title.'</label>';
		      }
		  }
		$html.= '</div>';
		$html.= '<p class="form-control-newline" ><button class="inputsubmit checkall" type="button" >Select All</button> <button class="inputsubmit uncheckall" type="button" >Uncheck All</button></p>';
		return $html;
	}
}