<?php
class input {
	function __construct(){

// 		foreach (scandir(BASEPATH.'input') AS $input){
// 			if( !in_array($input, array('.','..') ) ){
// 				require(BASEPATH.'input/'.$input);

// 				$input_name = basename($input, ".php");
// 				$input_class = $input_name.'_input';
// // 				foreach (get_class_methods($input_class) AS $input_sub){
// // 					if( $input_sub !='__construct' ){
// // 						$input_new = new $input_class();
// // 						$this->$input_sub() = $input_new->$input_sub();
// // 					}
// // 				}
// 				//$this->$input_name = new $input_class();

// 			}
// 		}
	}

	static function html($input,$label,$output='input',$lable_width=null) {
		switch ($output){
			case 'row': $html= self::row($input, $label,$lable_width); break;
			default: $html = $input; break;
		}
		return $html;
	}

	static function row($input, $label,$lable_width=null){
		return "<tr><td class='label' ".($lable_width? 'style="width:'.$lable_width.';"': '')." >$label</td><td>$input</td></tr>";
	}


	static function select($name,$selected_id,$options,$multi=false,$class='combo'){
		$opts = '';
		if( $options && is_array($options) ){
			foreach ($options AS $opt){
				$opts .= '<option value="'.$opt->id.'" '.( $opt->id==$selected_id ? 'selected' : null ).' '.( isset($opt->parent) ? ' parent="'.$opt->parent.'" ' : null ).' >'.$opt->value.'</option>';
			}
		}
		$html = '<select autocomplete="off" name="'.$name.($multi ? '[]':'').'" class="'.$class.'" >'.$opts.'</select>';
		return '<span id="_'.$name.'_type_sel" class="select">'.$html.'</span>';
	}

	function output(){
		return 'abc';
	}


}