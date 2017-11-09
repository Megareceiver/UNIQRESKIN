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
$page_security = 'SA_BANKTRANSFER';
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/gl_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(800, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();
page(_($help_context = "Transfer between Bank Accounts"), false, false, "", $js);

$control_ci = module_control_load('transfer','bank');

check_db_has_bank_accounts(_("There are no bank accounts defined in the system."));

//----------------------------------------------------------------------------------------


//----------------------------------------------------------------------------------------

function check_valid_entries()
{
	global $Refs;

	if (!is_date($_POST['DatePaid']))
	{
		display_error(_("The entered date is invalid."));
		set_focus('DatePaid');
		return false;
	}
	if (!is_date_in_fiscalyear($_POST['DatePaid']))
	{
		display_error(_("The entered date is not in fiscal year."));
		set_focus('DatePaid');
		return false;
	}

	if (!check_num('amount', 0))
	{
		display_error(_("The entered amount is invalid or less than zero."));
		set_focus('amount');
		return false;
	}
	if (input_num('amount') == 0) {
		display_error(_("The total bank amount cannot be 0."));
		set_focus('amount');
		return false;
	}

	$limit = get_bank_account_limit($_POST['FromBankAccount'], $_POST['DatePaid']);

	$amnt_tr = input_num('charge') + input_num('amount');

	if ($limit !== null && floatcmp($limit, $amnt_tr) < 0)
	{
		display_error(sprintf(_("The total bank amount exceeds allowed limit (%s) for source account."), price_format($limit)));
		set_focus('amount');
		return false;
	}
	if ($trans = check_bank_account_history(-$amnt_tr, $_POST['FromBankAccount'], $_POST['DatePaid'])) {

		display_error(sprintf(_("The bank transaction would result in exceed of authorized overdraft limit for transaction: %s #%s on %s."),
			$systypes_array[$trans['type']], $trans['trans_no'], sql2date($trans['trans_date'])));
		set_focus('amount');
		$input_error = 1;
	}

	if (isset($_POST['charge']) && !check_num('charge', 0))
	{
		display_error(_("The entered amount is invalid or less than zero."));
		set_focus('charge');
		return false;
	}
	if (isset($_POST['charge']) && input_num('charge') > 0 && get_company_pref('bank_charge_act') == '') {
		display_error(_("The Bank Charge Account has not been set in System and General GL Setup."));
		set_focus('charge');
		return false;
	}
	if (!$Refs->is_valid($_POST['ref']))
	{
		display_error(_("You must enter a reference."));
		set_focus('ref');
		return false;
	}

	if (!is_new_reference($_POST['ref'], ST_BANKTRANSFER))
	{
		display_error(_("The entered reference is already in use."));
		set_focus('ref');
		return false;
	}

	if ($_POST['FromBankAccount'] == $_POST['ToBankAccount'])
	{
		display_error(_("The source and destination bank accouts cannot be the same."));
		set_focus('ToBankAccount');
		return false;
	}

	if (isset($_POST['target_amount']) && !check_num('target_amount', 0))
	{
		display_error(_("The entered amount is invalid or less than zero."));
		set_focus('target_amount');
		return false;
	}
	if (isset($_POST['target_amount']) && input_num('target_amount') == 0) {
		display_error(_("The incomming bank amount cannot be 0."));
		set_focus('target_amount');
		return false;
	}

	if (!db_has_currency_rates(get_bank_account_currency($_POST['FromBankAccount']), $_POST['DatePaid']))
		return false;

	if (!db_has_currency_rates(get_bank_account_currency($_POST['ToBankAccount']), $_POST['DatePaid']))
		return false;

    return true;
}

//----------------------------------------------------------------------------------------

function handle_add_deposit()
{
	new_doc_date($_POST['DatePaid']);
	$trans_no = add_bank_transfer($_POST['FromBankAccount'], $_POST['ToBankAccount'],
		$_POST['DatePaid'], input_num('amount'), $_POST['ref'], $_POST['memo_'], input_num('charge'), input_num('target_amount'));

	meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
}

//----------------------------------------------------------------------------------------

$control_ci->form();

// gl_payment_controls();

end_page();
?>
