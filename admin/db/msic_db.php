<?php
class msic {
	static $selected_id;
	static $limit = 10;
	static $current_page = 1;
	function __construct(){
		$this->fields();
		// 		self::$current_page = 1;
		if( isset($_GET['page']) ){
			self::$current_page = $_GET['page'];
		}
	}

	static $fields;
	function fields(){
		$fields = array(
				'id','section','section_description','division','code','description','status'
		);
		self::$fields = $fields;
	}

	function can_process(){


		if (strlen($_POST['name']) == 0){
			display_error(_("The tax type name cannot be empty."));
			set_focus('name');
			return false;
		}
		elseif (!check_num('rate', 0))
		{
			display_error( _("The default tax rate must be numeric and not less than zero."));
			set_focus('rate');
			return false;
		}

		return true;
	}

	function can_delete($selected_id){
		if (key_in_foreign_table($selected_id, 'tax_group_items', 'tax_type_id')){
			display_error(_("Cannot delete this tax type because tax groups been created referring to it."));

			return false;
		}

		return true;
	}
	
	function get_all_items($all=false){
		$begin = 0;
		if( $_GET['page'] ){
			$begin = ($_GET['page']-1)*self::$limit;
		}
		//, d.description AS division, s.description AS section, 1 AS inactive
		$sql = "SELECT m.id, m.code, d.description AS division, s.description AS section  FROM ".TB_PREF."msic_item AS m LEFT JOIN  ".TB_PREF."msic_division AS d on m.division = d.id LEFT JOIN msic_section AS s ON d.section = s.id LIMIT $begin,".self::$limit;
// 		bug($sql);die;
		return db_query($sql, "could not get all MSIC");
	}
	function total(){
		$sql = "SELECT COUNT(*) AS total FROM ".TB_PREF."msic";
		$result = db_query($sql, "could not get all MSIC");
		$data =  db_fetch($result);
		return $data['total'];
	}
	function get_row($selected_id=0){
		if( $selected_id ){
			$sql = "SELECT m.id, m.code, m.description, d.description AS division, s.description AS section  FROM ".TB_PREF."msic_item AS m LEFT JOIN  ".TB_PREF."msic_division AS d on m.division = d.id LEFT JOIN msic_section AS s ON d.section = s.id WHERE m.id= $selected_id";
			$result = db_query($sql, "could not get MSIC");
			$data =  db_fetch($result);
			if( $data ){

				foreach (self::$fields AS $field){
					$_POST[$field] = $data[$field];
				}
				// 				bug(self::$fields);
				// 				bug($data);
				// 				bug($_POST);
				// 				die('quannh');
			}
		}

	}
}

