<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_TAXRATES';
$path_to_root = "..";

include($path_to_root . "/includes/session.inc");
get_instance()->auto_load_module('gl');
$control_ci = module_control_load('manage/taxes','gst');
page(_($help_context = "Tax Types"));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/taxes/db/tax_types_db.inc");

simple_page_mode(true);
//-----------------------------------------------------------------------------------

function can_process(){
	global $selected_id;

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

	//if (!is_tax_gl_unique(get_post('sales_gl_code'), get_post('purchasing_gl_code'), $selected_id)) {
	//	display_error( _("Selected GL Accounts cannot be used by another tax type."));
	//	set_focus('sales_gl_code');
	//	return false;
	//}
	return true;
}

//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' && can_process())
{
    //if($_POST['inactive'] == 1) $_POST['inactive'] = 0;
    //else  $_POST['inactive'] = 1;
	add_tax_type($_POST['name'], $_POST['sales_gl_code'],$_POST['purchasing_gl_code'], input_num('rate', 0),$_POST['gst_03_type'],null,$_POST['use_for']);
	display_notification(_('New tax type has been added'));
	$Mode = 'RESET';
}

//-----------------------------------------------------------------------------------

if ($Mode=='UPDATE_ITEM' && can_process()){
    //if($_POST['inactive'] == 1) $_POST['inactive'] = 0;
    //else $_POST['inactive'] = 1;
	update_tax_type($selected_id, $_POST['name'], $_POST['sales_gl_code'], $_POST['purchasing_gl_code'], input_num('rate'),input_val('gst_03_type'),null,$_POST['use_for']);
	display_notification(_('Selected tax type has been updated'));
	$Mode = 'RESET';
}

//-----------------------------------------------------------------------------------

function can_delete($selected_id)
{
	if (key_in_foreign_table($selected_id, 'tax_group_items', 'tax_type_id'))
	{
		display_error(_("Cannot delete this tax type because tax groups been created referring to it."));

		return false;
	}

	return true;
}


//-----------------------------------------------------------------------------------

if ($Mode == 'Delete'){
	if (can_delete($selected_id)){
		delete_tax_type($selected_id);
		display_notification(_('Selected tax type has been deleted'));
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}
//-----------------------------------------------------------------------------------

// $result = get_all_tax_types(check_value('show_inactive'));


$control_ci->selected_id = $selected_id;
$control_ci->mode = $Mode;
$control_ci->index();


// $taxes = $ci->api_membership->get_data('taxdetail');
// $tax_model = $ci->model('tax',true);


end_page();

?>
