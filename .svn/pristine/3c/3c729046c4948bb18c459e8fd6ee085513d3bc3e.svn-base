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
$page_security = 'SA_POSSETUP';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('sales_point','manage');
page(_($help_context = "POS settings"));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/sales/includes/db/sales_points_db.inc");

simple_page_mode(true);
//----------------------------------------------------------------------------------------------------

function can_process()
{
	if (strlen($_POST['name']) == 0)
	{
		display_error(_("The POS name cannot be empty."));
		set_focus('pos_name');
		return false;
	}
	return true;
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' && can_process())
{
	add_sales_point($_POST['name'], $_POST['location'], $_POST['account'],
		check_value('cash'), check_value('credit'));
	display_notification(_('New point of sale has been added'));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='UPDATE_ITEM' && can_process())
{

	update_sales_point($selected_id, $_POST['name'], $_POST['location'],
		$_POST['account'], check_value('cash'), check_value('credit'));
	display_notification(_('Selected point of sale has been updated'));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	if (key_in_foreign_table($selected_id, 'users', 'pos'))
	{
		display_error(_("Cannot delete this POS because it is used in users setup."));
	} else {
		delete_sales_point($selected_id);
		display_notification(_('Selected point of sale has been deleted'));
		$Mode = 'RESET';
	}
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}
//----------------------------------------------------------------------------------------------------

$control_ci->selected_id = $selected_id;
$control_ci->mode = $Mode;
$control_ci->index();



// start_form();

//----------------------------------------------------------------------------------------------------


// submit_add_or_update_center($selected_id == -1, '', 'both');

// end_form();

end_page();

?>
