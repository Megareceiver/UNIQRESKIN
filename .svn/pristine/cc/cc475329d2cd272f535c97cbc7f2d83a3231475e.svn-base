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
$page_security = 'SA_ITEMCATEGORY';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('manage/categorie','products');

page(_($help_context = "Item Categories"));

include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");

simple_page_mode(true);
//----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['description']) == 0)
	{
		$input_error = 1;
		display_error(_("The item category description cannot be empty."));
		set_focus('description');
	}

	if ($input_error !=1)
	{
    	if ($selected_id != -1)
    	{
		    update_item_category($selected_id, $_POST['description'],
				$_POST['tax_type_id'],	$_POST['sales_account'],
				$_POST['cogs_account'], $_POST['inventory_account'],
				$_POST['adjustment_account'], $_POST['assembly_account'],
				$_POST['units'], $_POST['mb_flag'],	$_POST['dim1'],	$_POST['dim2'],
				check_value('no_sale'));
			display_notification(_('Selected item category has been updated'));
    	}
    	else
    	{
		    add_item_category($_POST['description'],
				$_POST['tax_type_id'],	$_POST['sales_account'],
				$_POST['cogs_account'], $_POST['inventory_account'],
				$_POST['adjustment_account'], $_POST['assembly_account'],
				$_POST['units'], $_POST['mb_flag'],	$_POST['dim1'],
				$_POST['dim2'],	check_value('no_sale'));
			display_notification(_('New item category has been added'));
    	}
		$Mode = 'RESET';
	}
}

//----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'stock_master'
	if (key_in_foreign_table($selected_id, 'stock_master', 'category_id'))
	{
		display_error(_("Cannot delete this item category because items have been created using this item category."));
	}
	else
	{
		delete_item_category($selected_id);
		display_notification(_('Selected item category has been deleted'));
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
if (list_updated('mb_flag')) {
	$Ajax->activate('details');
}
//----------------------------------------------------------------------------------

$control_ci->mode = $Mode;
$control_ci->selected_id = $selected_id;

$control_ci->index();

end_page();

?>
