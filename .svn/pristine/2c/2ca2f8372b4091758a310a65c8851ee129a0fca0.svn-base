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
$page_security = 'SA_INVENTORYMOVETYPE';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('manage/movement_type','products');
page(_($help_context = "Inventory Movement Types"));

include_once($path_to_root . "/inventory/includes/inventory_db.inc");

include_once($path_to_root . "/includes/ui.inc");

simple_page_mode(true);
//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['name']) == 0)
	{
		$input_error = 1;
		display_error(_("The inventory movement type name cannot be empty."));
		set_focus('name');
	}

	if ($input_error != 1)
	{
    	if ($selected_id != -1)
    	{
    		update_movement_type($selected_id, $_POST['name']);
			display_notification(_('Selected movement type has been updated'));
    	}
    	else
    	{
    		add_movement_type($_POST['name']);
			display_notification(_('New movement type has been added'));
    	}

		$Mode = 'RESET';
	}
}

//-----------------------------------------------------------------------------------

function can_delete($selected_id)
{
	if (movement_types_in_stock_moves($selected_id))
	{
		display_error(_("Cannot delete this inventory movement type because item transactions have been created referring to it."));
		return false;
	}

	return true;
}


//-----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	if (can_delete($selected_id))
	{
		delete_movement_type($selected_id);
		display_notification(_('Selected movement type has been deleted'));
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

$control_ci->mode = $Mode;
$control_ci->selected_id = $selected_id;
$control_ci->index();

end_page();

?>
