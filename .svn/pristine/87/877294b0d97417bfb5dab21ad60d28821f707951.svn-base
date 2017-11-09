<?php
class view {
	function __construct($fields=array()){
		$this->fields = $fields;
	}

	function form(){

		foreach ($this->fields AS $name=>$field){
			$title = ( isset($field['title']) ) ? $field['title']: _($name);
			$input = null;



			switch ($field['input']){
				case 'datefix':

					$this->datefix($name,$field);
					echo '</tr>';
					break;
				case 'date':
					echo '<tr><td>'.$title.'</td>';
					date_cells(null,$name);
					echo '</tr>';
					break;
				case 'bank_accounts':
					echo '<tr><td>'.$title.'</td>';
					bank_accounts_list_cells(null,$name);
					echo '</tr>';
					break;
				case 'hidden':
					echo '<tr style="display: none;" ><td>'.$title.'</td>';
					hidden($name,$field['value']);
					echo '</tr>';
					break;
				default:
					echo '<tr><td>'.$title.'</td>';
					text_cells_ex(null,$name,50);
					echo '</tr>';
				break;
			}


		}
	}



// 	function reset(){
// 		unset($_POST);
// 	}

	function row($title,$input=''){
		$out = '<tr><td>'.$title.'</td><td>'.$input.'</td></tr>';
		echo $out;
	}

	function input($name,$field=array(),$returnRow = true){
		$input = NULL;
		if( !isset($field['input']) ){
			$field['input'] = 'text';
		}
		switch ($field['input']){

			case 'hidden':
				echo '<tr style="display: none;" ><td>'.$title.'</td>';
				hidden($name,$field['value']);
				echo '</tr>';
				break;
			case 'products':
				$sql = "SELECT stock_id, s.description, c.description, s.inactive, s.editable
				FROM ".TB_PREF."stock_master s,".TB_PREF."stock_category c WHERE s.category_id=c.category_id";
				$selected_id = ( isset($field['value']) ) ? $field['value'] : null;
				$input = combo_input($name, $selected_id, $sql, 'product','s.description',array('category' => 2,'order' => array('c.description','stock_id')));
				break;
			case 'customers':
				$sql = "SELECT debtor_no, debtor_ref, curr_code, inactive FROM ".TB_PREF."debtors_master ";
				$selected_id = ( isset($field['value']) ) ? $field['value'] : null;
				$input = combo_input($name, $selected_id, $sql,  'debtor_no', 'debtor_ref',array('order' => array('debtor_ref')));
				break;
			default:
				$size = ( isset($field['size']) ) ? $field['size']: '';
				$value = ( isset($field['value']) ) ? $field['value'] : null;
				$input = '<input type="text" name="'.$name.'" size="'.$size.'" maxlength="" value="'.$value.'" >';

				break;
		}


		if( $returnRow ){
			return $this->row($title,$input);
		} else {
			return $input;
		}
	}

	function datefix($name,$field=array(),$returnRow = true ){
		$title = ( isset($field['title']) ) ? $field['title']: _($name);
		$value = '';

		if( isset($field['value']) ){
			$value = $field['value'];
		}

		if( isset($_POST[$name]) ){
			$value = $_POST[$name];
		}

		$readonly = ( isset($field['readonly']) && $field['readonly'] ) ? ' readonly ': NULL;

		$input = '<input type="text" name="'.$name.'" class="date" size="11" maxlength="12" value="'.$value.'" '.$readonly.' >';
		if( $returnRow ){
			return $this->row($title,$input);
		} else {
			return $input;
		}


	}
}