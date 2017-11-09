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
$page_security = 'SA_CRSTATUS';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/sales/includes/db/credit_status_db.inc");
include($path_to_root . "/includes/ui.inc");

$control_ci = module_control_load('manager/credit_status','sales');
page(_($help_context = "Credit Status"));
simple_page_mode(true);
//-----------------------------------------------------------------------------------

function can_process()
{

	if (strlen($_POST['reason_description']) == 0)
	{
		display_error(_("The credit status description cannot be empty."));
		set_focus('reason_description');
		return false;
	}

	return true;
}

//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' && can_process())
{

	add_credit_status($_POST['reason_description'], $_POST['DisallowInvoices']);
	display_notification(_('New credit status has been added'));
	$Mode = 'RESET';
}

//-----------------------------------------------------------------------------------

if ($Mode=='UPDATE_ITEM' && can_process())
{
	display_notification(_('Selected credit status has been updated'));
	update_credit_status($selected_id, $_POST['reason_description'], $_POST['DisallowInvoices']);
	$Mode = 'RESET';
}

//-----------------------------------------------------------------------------------

function can_delete($selected_id)
{
	if (key_in_foreign_table($selected_id, 'debtors_master', 'credit_status'))
	{
		display_error(_("Cannot delete this credit status because customer accounts have been created referring to it."));
		return false;
	}

	return true;
}


//-----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{

	if (can_delete($selected_id))
	{
		delete_credit_status($selected_id);
		display_notification(_('Selected credit status has been deleted'));
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

$control_ci->id = $selected_id;
$control_ci->mode = $Mode;
$control_ci->index();

end_page();

?>
