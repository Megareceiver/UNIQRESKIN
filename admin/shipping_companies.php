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
$page_security = 'SA_SHIPPING';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('shipper','manage');
page(_($help_context = "Shipping Company"));
include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/admin/db/shipping_db.inc");

simple_page_mode(true);
//----------------------------------------------------------------------------------------------

function can_process()
{
	if (strlen($_POST['shipper_name']) == 0)
	{
		display_error(_("The shipping company name cannot be empty."));
		set_focus('shipper_name');
		return false;
	}
	return true;
}

//----------------------------------------------------------------------------------------------
if ($Mode=='ADD_ITEM' && can_process())
{
	add_shipper($_POST['shipper_name'], $_POST['contact'], $_POST['phone'], $_POST['phone2'], $_POST['address']);
	display_notification(_('New shipping company has been added'));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------

if ($Mode=='UPDATE_ITEM' && can_process())
{
	update_shipper($selected_id, $_POST['shipper_name'], $_POST['contact'], $_POST['phone'], $_POST['phone2'], $_POST['address']);
	display_notification(_('Selected shipping company has been updated'));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
// PREVENT DELETES IF DEPENDENT RECORDS IN 'sales_orders'

	if (key_in_foreign_table($selected_id, 'sales_orders', 'ship_via'))
	{
		$cancel_delete = 1;
		display_error(_("Cannot delete this shipping company because sales orders have been created using this shipper."));
	}
	else
	{
		// PREVENT DELETES IF DEPENDENT RECORDS IN 'debtor_trans'
		if (key_in_foreign_table($selected_id, 'debtor_trans', 'ship_via'))
		{
			$cancel_delete = 1;
			display_error(_("Cannot delete this shipping company because invoices have been created using this shipping company."));
		}
		else
		{
			delete_shipper($selected_id);
			display_notification(_('Selected shipping company has been deleted'));
		}
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
//----------------------------------------------------------------------------------------------

$control_ci->selected_id = $selected_id;
$control_ci->mode = $Mode;
$control_ci->index();



// start_form();


//----------------------------------------------------------------------------------------------

// start_table(TABLESTYLE2);


// end_table(1);

// submit_add_or_update_center($selected_id == -1, '', 'both');

// end_form();
end_page();
?>
