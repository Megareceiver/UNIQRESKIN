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
$page_security = 'SA_SALESPAYMNT';
$path_to_root = "..";
include_once($path_to_root . "/includes/ui/allocation_cart.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
global $ci;
$js = "";
if ($use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}
// if ($use_date_picker) {
// 	$js .= get_js_date_picker();
// }

$js.=" $(function() { $('select[name=bank_account]').next('.btn-group').find('button').click(); });";

add_js_file('js/payalloc.js');

page(_($help_context = "Customer Payment Entry"), false, false, "", $js);
$control_ci = module_control_load('tran/payment','sales');
//----------------------------------------------------------------------------------------------

check_db_has_customers(_("There are no customers defined in the system."));
check_db_has_bank_accounts(_("There are no bank accounts defined in the system."));



//----------------------------------------------------------------------------------------------

function can_process()
{
	global $Refs;

	if (!get_post('customer_id'))
	{
		display_error(_("There is no customer selected."));
		set_focus('customer_id');
		return false;
	}

	if (!get_post('BranchID'))
	{
		display_error(_("This customer has no branch defined."));
		set_focus('BranchID');
		return false;
	}

	if (!isset($_POST['DateBanked']) || !is_date($_POST['DateBanked'])) {
		display_error(_("The entered date is invalid. Please enter a valid date for the payment."));
		set_focus('DateBanked');
		return false;
	} elseif (!is_date_in_fiscalyear($_POST['DateBanked'])) {
		display_error(_("The entered date is not in fiscal year."));
		set_focus('DateBanked');
		return false;
	}

	if (!$Refs->is_valid($_POST['ref'])) {
		display_error(_("You must enter a reference."));
		set_focus('ref');
		return false;
	}

	//Chaitanya : 13-OCT-2011 - To support Edit feature
	if (isset($_POST['trans_no']) && $_POST['trans_no'] == 0 && (!is_new_reference($_POST['ref'], ST_CUSTPAYMENT))) {
		display_error(_("The entered reference is already in use."));
		set_focus('ref');
		return false;
	}
	//Avoid duplicate reference while modifying
	elseif ($_POST['ref'] != $_POST['old_ref'] && !is_new_reference($_POST['ref'], ST_CUSTPAYMENT))
	{
		display_error( _("The entered reference is already in use."));
		set_focus('ref');
		return false;
	}

	if (!check_num('amount', 0)) {
		display_error(_("The entered amount is invalid or negative and cannot be processed."));
		set_focus('amount');
		return false;
	}

	if (isset($_POST['charge']) && !check_num('charge', 0)) {
		display_error(_("The entered amount is invalid or negative and cannot be processed."));
		set_focus('charge');
		return false;
	}
	if (isset($_POST['charge']) && input_num('charge') > 0) {
		$charge_acct = get_company_pref('bank_charge_act');
		if (get_gl_account($charge_acct) == false) {
			display_error(_("The Bank Charge Account has not been set in System and General GL Setup."));
			set_focus('charge');
			return false;
		}
	}

// 	if (isset($_POST['_ex_rate']) && !check_num('_ex_rate', 0.000001))
// 	{
// 		display_error(_("The exchange rate must be numeric and greater than zero."));
// 		set_focus('_ex_rate');
// 		return false;
// 	}

	if (@$_POST['discount'] == "")
	{
		$_POST['discount'] = 0;
	}

	if (!check_num('discount')) {
		display_error(_("The entered discount is not a valid number."));
		set_focus('discount');
		return false;
	}

	//if ((input_num('amount') - input_num('discount') <= 0)) {
	if (input_num('amount') <= 0) {
		display_error(_("The balance of the amount and discount is zero or negative. Please enter valid amounts."));
		set_focus('discount');
		return false;
	}

	if (isset($_POST['bank_amount']) && input_num('bank_amount')<=0)
	{
		display_error(_("The entered payment amount is zero or negative."));
		set_focus('bank_amount');
		return false;
	}

	if (!db_has_currency_rates(get_customer_currency($_POST['customer_id']), $_POST['DateBanked'], true))
		return false;

	$_SESSION['alloc']->amount = input_num('amount');

	if (isset($_POST["TotalNumberOfAllocs"]))
		return check_allocations();
	else
		return true;
}

//----------------------------------------------------------------------------------------------


//if (isset($_POST['_DateBanked_changed'])) {
//  $Ajax->activate('_ex_rate');
//}

//----------------------------------------------------------------------------------------------


//----------------------------------------------------------------------------------------------

function read_customer_data(){
	global $Refs;

	$myrow = get_customer_habit($_POST['customer_id']);

	$_POST['HoldAccount'] = $myrow["dissallow_invoices"];
	$_POST['pymt_discount'] = $myrow["pymt_discount"];
	//Chaitanya : 13-OCT-2011 - To support Edit feature
	//If page is called first time and New entry fetch the nex reference number
	if (!$_SESSION['alloc']->trans_no && !isset($_POST['charge']))
		$_POST['ref'] = $Refs->get_next(ST_CUSTPAYMENT);
}

$control_ci->form();

end_page();
?>
