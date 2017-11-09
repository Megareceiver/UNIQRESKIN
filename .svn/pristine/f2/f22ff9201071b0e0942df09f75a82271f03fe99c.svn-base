<?php 
$page_security = 'SA_TAXRATES';
$path_to_root = "..";

include($path_to_root . "/includes/session.inc");
page(_($help_context = "MSIC Code"));

include_once($path_to_root . "/includes/ui.inc");
include $path_to_root . "/admin/db/msic_db.php";
$msic = new msic();
global $selected_id;
simple_page_mode(true);
$model = new msic($selected_id);




if ($Mode == 'RESET'){
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}
//-----------------------------------------------------------------------------------

// $result = $model->get_all_items( check_value('show_inactive') );


start_form();

display_note(_("To avoid problems with manual journal entry all tax types should have unique Sales/Purchasing GL accounts."), 0, 1);
start_table(TABLESTYLE);

$th = array('ID',_(" Section Name"), _("Divsion Name"),_("MSIC Code"),"", "");
inactive_control_column($th);
table_header($th);

$k = 0;
$limit = 10;
$page = 0;
if( isset($_GET['page'] ) ){
	$page = $_GET['page']-1;
}
$msic = file_get_contents("http://register.accountanttoday.net/msic/items?limit=$limit&page=$page");
$msic = json_decode($msic);
if( $msic ){
	foreach ($msic->data AS $ite){
		alt_table_row_color($k);
		label_cell($ite->id);
		label_cell($ite->section);
		label_cell($ite->division);
		label_cell($ite->code);
		inactive_control_cell($ite->id, 1, 'msic', 'id');
		edit_button_cell("Edit".$ite->id, _("Edit"));
		null;
		end_row();
	}
}

inactive_control_row($th);
paging_control_row($th,$limit,$page,$msic->total);
end_table(1);
//-----------------------------------------------------------------------------------

start_table(TABLESTYLE2);

if ($selected_id != -1) {
	if ($Mode == 'Edit') {
		//$msic->get_row($selected_id);
		$msic_item = file_get_contents("http://register.accountanttoday.net/msic/item/$selected_id");
		$msic_item = json_decode($msic_item);
		if( $msic_item ){
			foreach ($msic_item AS $field_key=>$value){
				$_POST[$field_key] = $value;
			}
		}
	}
	hidden('selected_id', $selected_id);
}

text_row_ex(_("Section Name:"), 'section', 50);
// textarea_row(_("Section Desciption:"), 'section_description',null,70);
text_row_ex(_("Division name:"), 'division', 50);
text_row_ex(_("MSIC Code:"), 'code', 50);
textarea_row(_("MSIC Description:"), 'description',NULL,70,5);
// textarea_row(_("Desciption:"), 'description',null,70);





//function customer_list_row($label, $name, $selected_id, $all_option=false, $submit_on_change=false)


end_table(1);

// submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();

?>
