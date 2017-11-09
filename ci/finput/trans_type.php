<?php
class trans_type {
	function input($name,$val,$group=0,$input_return_type='html',$readonly=false,$ids_filter=null){
		global $systypes_array, $ci;
		$options = array();
// 		$options[] = (object) array('id'=>'*','title'=>'All Type');
		foreach ($systypes_array AS $type_id=>$typename){
			$options[] = (object) array('id'=>$type_id,'title'=>$typename);
		}
		return $ci->finput->options( $name,$options,$val,null, "combo2 form-control" ,'off',true,$readonly);
	}
}