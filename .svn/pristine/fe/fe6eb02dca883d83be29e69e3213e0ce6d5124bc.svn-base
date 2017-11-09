<?php
//class msic_input extends input {
class msic_input {

	function __construct(){

		if( !class_exists(' api_membership') ){
			include_once(BASEPATH.'libraries/api_membership.php');
		}
		$this->api = new api_membership();
	}

	public function group($label, $name, $selected_id=null,$output='input'){

		if( isset($_POST['gst_03_box_msic']) ){
			$selected_id = $_POST['gst_03_box_msic'];
		}
		$section_id = $division_id = null;
// 		if( $selected_id ){
// 			$msic = $this->api->get_data("msic/item/$selected_id");
// 			if( $msic && isset($msic->section_id) ){
// 				$section_id = $msic->section_id;
// 			}
// 			if( $msic && isset($msic->division_id) ){
// 				$division_id = $msic->division_id;
// 			}
// 		}
		$input = self::section($name.'_section',$section_id);
		$input.= self::division($name.'_division',$division_id,$section_id).'<span style="display:none;" >'.self::division('division_cache',null,'*').'</span>';
		$input.= self::items($name.'_msic',$selected_id,$division_id).'<span style="display:none;" >'.self::items('item_cache',null,'*').'</span>';
		$input.= '<input type="text" name="'.$name.'_code" size="36" value="'.$selected_id.'" readonly >';

		return input::html($input,$label,$output,'35%');
	}


	function items($name,$selected_id=null,$parent='*'){

		$default = array('id'=>-1,'value'=>'Select MSIC Code');
		$options = array((object)$default);

		if( $parent=='*' || $parent==null ){
			$msic = $this->api->get_data("msic2/msic_items");
		} else if( isset($parent) && is_numeric($parent)) {
			$msic = $this->api->get_data("msic2/msic_items/$parent");
		}
		if( $msic ){

			$options = array_merge($options,$msic );
		}
		return input::select($name,$selected_id,$options);
	}

	function section($name,$selected_id=null){

		$default = array('id'=>-1,'value'=>'Select Section');
		$options = array((object)$default);
		$section = $this->api->get_data("msic2/section");

		if( $section ){
			$options = array_merge($options,$section );
		}
		return input::select($name,$selected_id,$options);
	}
	function division($name,$selected_id=null,$parent=null){
		$default = array('id'=>-1,'value'=>'Select Category');
		$options = array((object)$default);

		if( $parent==null || $parent=='*' ){
			$division = $this->api->get_data("msic2/division");
		} else {
			$division = $this->api->get_data("msic2/division/$parent");
		}
		if( isset($division) ){
			$options = array_merge($options,$division);
		}
		return input::select($name,$selected_id,$options);
	}
}