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
$page_security = 'SA_BANKACCOUNT';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('manage/account','bank');
page(_($help_context = "Bank Accounts"));

include($path_to_root . "/includes/ui.inc");

simple_page_mode();
//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	//first off validate inputs sensible
	if (strlen($_POST['bank_account_name']) == 0)
	{
		$input_error = 1;
		display_error(_("The bank account name cannot be empty."));
		set_focus('bank_account_name');
	}
	if ($Mode=='ADD_ITEM' && (gl_account_in_bank_accounts(get_post('account_code'))
			|| key_in_foreign_table(get_post('account_code'), 'gl_trans', 'account'))) {
		$input_error = 1;
		display_error(_("The GL account selected is already in use. Select another GL account."));
		set_focus('account_code');
	}
	if ($input_error != 1)
	{
    	if ($selected_id != -1)
    	{

    		update_bank_account($selected_id, $_POST['account_code'],
				$_POST['account_type'], $_POST['bank_account_name'],
				$_POST['bank_name'], $_POST['bank_account_number'],
    			$_POST['bank_address'], $_POST['BankAccountCurrency'],
    			$_POST['dflt_curr_act']);
			display_notification(_('Bank account has been updated'));
    	}
    	else
    	{

    		add_bank_account($_POST['account_code'], $_POST['account_type'],
				$_POST['bank_account_name'], $_POST['bank_name'],
    			$_POST['bank_account_number'], $_POST['bank_address'],
				$_POST['BankAccountCurrency'], $_POST['dflt_curr_act']);
			display_notification(_('New bank account has been added'));
    	}
 		$Mode = 'RESET';
	}
}
elseif( $Mode == 'Delete')
{
	//the link to delete a selected record was clicked instead of the submit button

	$cancel_delete = 0;
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'bank_trans'

	if (key_in_foreign_table($selected_id, 'bank_trans', 'bank_act') || key_in_foreign_table(get_post('account_code'), 'gl_trans', 'account'))
	{
		$cancel_delete = 1;
		display_error(_("Cannot delete this bank account because transactions have been created using this account."));
	}

	if (key_in_foreign_table($selected_id, 'sales_pos', 'pos_account'))
	{
		$cancel_delete = 1;
		display_error(_("Cannot delete this bank account because POS definitions have been created using this account."));
	}
	if (!$cancel_delete)
	{
		delete_bank_account($selected_id);
		display_notification(_('Selected bank account has been deleted'));
	} //end if Delete bank account
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
 	$selected_id = -1;
	$_POST['bank_name']  = 	$_POST['bank_account_name']  = '';
	$_POST['bank_account_number'] = $_POST['bank_address'] = '';
}

$control_ci->mode = $Mode;
// $control_ci->mode2 = $Mode2;
$control_ci->selected_id = $selected_id;
$control_ci->index();
/* Always show the list of accounts */


end_page();
?>
