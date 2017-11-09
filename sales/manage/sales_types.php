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
$page_security = 'SA_SALESTYPES';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");

$control_ci = module_control_load('manager/type','sales');
page(_($help_context = "Sales Types"));

simple_page_mode(true);
//----------------------------------------------------------------------------------------------------

function can_process()
{
	if (strlen($_POST['sales_type']) == 0)
	{
		display_error(_("The sales type description cannot be empty."));
		set_focus('sales_type');
		return false;
	}

	if (!check_num('factor', 0))
	{
		display_error(_("Calculation factor must be valid positive number."));
		set_focus('factor');
		return false;
	}
	return true;
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' && can_process())
{
	add_sales_type($_POST['sales_type'], isset($_POST['tax_included']) ? 1:0,
	    input_num('factor'));
	display_notification(_('New sales type has been added'));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------------

if ($Mode=='UPDATE_ITEM' && can_process())
{

	update_sales_type($selected_id, $_POST['sales_type'], isset($_POST['tax_included']) ? 1:0,
	     input_num('factor'));
	display_notification(_('Selected sales type has been updated'));
	$Mode = 'RESET';
}

//----------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'debtor_trans'

	if (key_in_foreign_table($selected_id, 'debtor_trans', 'tpe'))
	{
		display_error(_("Cannot delete this sale type because customer transactions have been created using this sales type."));

	}
	else
	{
		if (key_in_foreign_table($selected_id, 'debtors_master', 'sales_type'))
		{
			display_error(_("Cannot delete this sale type because customers are currently set up to use this sales type."));
		}
		else
		{
			delete_sales_type($selected_id);
			display_notification(_('Selected sales type has been deleted'));
		}
	} //end if sales type used in debtor transactions or in customers set up
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}
//----------------------------------------------------------------------------------------------------
$control_ci->id = $selected_id;
$control_ci->mode = $Mode;
$control_ci->index();
end_page();

?>
