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
$page_security = 'SA_CURRENCY';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('currency','manage');

page(_($help_context = "Currencies"));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");

simple_page_mode(false);

//---------------------------------------------------------------------------------------------

function check_data()
{
	if (strlen($_POST['Abbreviation']) == 0)
	{
		display_error( _("The currency abbreviation must be entered."));
		set_focus('Abbreviation');
		return false;
	}
	elseif (strlen($_POST['CurrencyName']) == 0)
	{
		display_error( _("The currency name must be entered."));
		set_focus('CurrencyName');
		return false;
	}
	elseif (strlen($_POST['Symbol']) == 0)
	{
		display_error( _("The currency symbol must be entered."));
		set_focus('Symbol');
		return false;
	}
	elseif (strlen($_POST['hundreds_name']) == 0)
	{
		display_error( _("The hundredths name must be entered."));
		set_focus('hundreds_name');
		return false;
	}

	return true;
}

//---------------------------------------------------------------------------------------------

function handle_submit()
{
	global $selected_id, $Mode;

	if (!check_data())
		return false;

	if ($selected_id != "")
	{

		update_currency($_POST['Abbreviation'], $_POST['Symbol'], $_POST['CurrencyName'],
			$_POST['country'], $_POST['hundreds_name'], check_value('auto_update'));
		display_notification(_('Selected currency settings has been updated'));
	}
	else
	{

		add_currency($_POST['Abbreviation'], $_POST['Symbol'], $_POST['CurrencyName'],
			$_POST['country'], $_POST['hundreds_name'], check_value('auto_update'));
		display_notification(_('New currency has been added'));
	}
	$Mode = 'RESET';
}

//---------------------------------------------------------------------------------------------

function check_can_delete($curr)
{

	if ($curr == "")
		return false;

	// PREVENT DELETES IF DEPENDENT RECORDS IN debtors_master
	if (key_in_foreign_table($curr, 'debtors_master', 'curr_code'))
	{
		display_error(_("Cannot delete this currency, because customer accounts have been created referring to this currency."));
		return false;
	}

	if (key_in_foreign_table($curr, 'suppliers', 'curr_code'))
	{
		display_error(_("Cannot delete this currency, because supplier accounts have been created referring to this currency."));
		return false;
	}

	if ($curr == get_company_pref('curr_default'))
	{
		display_error(_("Cannot delete this currency, because the company preferences uses this currency."));
		return false;
	}

	// see if there are any bank accounts that use this currency
	if (key_in_foreign_table($curr, 'bank_accounts', 'bank_curr_code'))
	{
		display_error(_("Cannot delete this currency, because thre are bank accounts that use this currency."));
		return false;
	}

	return true;
}

//---------------------------------------------------------------------------------------------

function handle_delete()
{
	global $selected_id, $Mode;
	if (check_can_delete($selected_id)) {
	//only delete if used in neither customer or supplier, comp prefs, bank trans accounts
		delete_currency($selected_id);
		display_notification(_('Selected currency has been deleted'));
	}
	$Mode = 'RESET';
}

//---------------------------------------------------------------------------------------------

// function display_currencies()
// {

// }

//---------------------------------------------------------------------------------------------

// function display_currency_edit($selected_id)
// {
// 	global $Mode;


// }

//---------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
	handle_submit();

//---------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
	handle_delete();

//---------------------------------------------------------------------------------------------
if ($Mode == 'RESET')
{
 		$selected_id = '';
		$_POST['Abbreviation'] = $_POST['Symbol'] = '';
		$_POST['CurrencyName'] = $_POST['country']  = '';
		$_POST['hundreds_name']  = '';
}

$control_ci->mode = $Mode;
$control_ci->selected_id = $selected_id;
$control_ci->index();

// start_form();
// display_currencies();

// display_currency_edit($selected_id);
// end_form();
//---------------------------------------------------------------------------------------------

end_page();

?>
