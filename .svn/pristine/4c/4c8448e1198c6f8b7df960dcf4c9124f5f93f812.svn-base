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
$page_security = 'SA_INVENTORYLOCATION';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('manage/location','products');
page(_($help_context = "Inventory Locations"));

include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");

simple_page_mode(true);

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$_POST['loc_code'] = strtoupper($_POST['loc_code']);

	if ((strlen(db_escape($_POST['loc_code'])) > 7) || empty($_POST['loc_code'])) //check length after conversion
	{
		$input_error = 1;
		display_error( _("The location code must be five characters or less long (including converted special chars)."));
		set_focus('loc_code');
	}
	elseif (strlen($_POST['location_name']) == 0)
	{
		$input_error = 1;
		display_error( _("The location name must be entered."));
		set_focus('location_name');
	}

	if ($input_error != 1)
	{
    	if ($selected_id != -1)
    	{

    		update_item_location($selected_id, $_POST['location_name'], $_POST['delivery_address'],
    			$_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'], $_POST['contact']);
			display_notification(_('Selected location has been updated'));
    	}
    	else
    	{

    	/*selected_id is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

    		add_item_location($_POST['loc_code'], $_POST['location_name'], $_POST['delivery_address'],
    		 	$_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'], $_POST['contact']);
			display_notification(_('New location has been added'));
    	}

		$Mode = 'RESET';
	}
}

function can_delete($selected_id)
{
	if (key_in_foreign_table($selected_id, 'stock_moves', 'loc_code'))
	{
		display_error(_("Cannot delete this location because item movements have been created using this location."));
		return false;
	}

	if (key_in_foreign_table($selected_id, 'workorders', 'loc_code'))
	{
		display_error(_("Cannot delete this location because it is used by some work orders records."));
		return false;
	}

	if (key_in_foreign_table($selected_id, 'cust_branch', 'default_location'))
	{
		display_error(_("Cannot delete this location because it is used by some branch records as the default location to deliver from."));
		return false;
	}

	if (key_in_foreign_table($selected_id, 'bom', 'loc_code'))
	{
		display_error(_("Cannot delete this location because it is used by some related records in other tables."));
		return false;
	}

	if (key_in_foreign_table($selected_id, 'grn_batch', 'loc_code'))
	{
		display_error(_("Cannot delete this location because it is used by some related records in other tables."));
		return false;
	}
	if (key_in_foreign_table($selected_id, 'purch_orders', 'into_stock_location'))
	{
		display_error(_("Cannot delete this location because it is used by some related records in other tables."));
		return false;
	}
	if (key_in_foreign_table($selected_id, 'sales_orders', 'from_stk_loc'))
	{
		display_error(_("Cannot delete this location because it is used by some related records in other tables."));
		return false;
	}
	if (key_in_foreign_table($selected_id, 'sales_pos', 'pos_location'))
	{
		display_error(_("Cannot delete this location because it is used by some related records in other tables."));
		return false;
	}
	return true;
}

//----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{

	if (can_delete($selected_id))
	{
		delete_item_location($selected_id);
		display_notification(_('Selected location has been deleted'));
	} //end if Delete Location
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}

$control_ci->mode = $Mode;
$control_ci->selected_id = $selected_id;
$control_ci->index();


end_page();

?>
