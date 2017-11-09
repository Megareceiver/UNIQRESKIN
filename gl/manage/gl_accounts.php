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
$page_security = 'SA_GLACCOUNT';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('manage/account','gl');
page(_($help_context = "Chart of Accounts"));

include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/gl/includes/gl_db.inc");
include($path_to_root . "/admin/db/tags_db.inc");
include_once($path_to_root . "/includes/data_checks.inc");

check_db_has_gl_account_groups(_("There are no account groups defined. Please define at least one account group before entering accounts."));

//-------------------------------------------------------------------------------------

if (isset($_POST['_AccountList_update']))
{
	$_POST['selected_account'] = $_POST['AccountList'];
	unset($_POST['account_code']);
}

if (isset($_POST['selected_account']))
{
	$selected_account = $_POST['selected_account'];
}
elseif (isset($_GET['selected_account']))
{
	$selected_account = $_GET['selected_account'];
}
else
	$selected_account = "";
//-------------------------------------------------------------------------------------

if (isset($_POST['add']) || isset($_POST['update']))
{

	$input_error = 0;

	if (strlen(trim($_POST['account_code'])) == 0)
	{
		$input_error = 1;
		display_error( _("The account code must be entered."));
		set_focus('account_code');
	}
	elseif (strlen(trim($_POST['account_name'])) == 0)
	{
		$input_error = 1;
		display_error( _("The account name cannot be empty."));
		set_focus('account_name');
	}
// 	elseif (!$accounts_alpha && !is_numeric($_POST['account_code']))
// 	{
// 	    $input_error = 1;
// 	    display_error( _("The account code must be numeric."));
// 		set_focus('account_code');
// 	}
	if ($input_error != 1)
	{
		if ($accounts_alpha == 2)
			$_POST['account_code'] = strtoupper($_POST['account_code']);

		if (!isset($_POST['account_tags']))
			$_POST['account_tags'] = array();

    	if ($selected_account)
		{
			if (get_post('inactive') == 1 && is_bank_account($_POST['account_code']))
			{
				display_error(_("The account belongs to a bank account and cannot be inactivated."));
			}
    		elseif (update_gl_account($_POST['account_code'], $_POST['account_name'],
				$_POST['account_type'], $_POST['account_code2'])) {
				update_record_status($_POST['account_code'], $_POST['inactive'],
					'chart_master', 'account_code');
				update_tag_associations(TAG_ACCOUNT, $_POST['account_code'],
					$_POST['account_tags']);
				$Ajax->activate('account_code'); // in case of status change
				display_notification(_("Account data has been updated."));
			}
		}
    	else
		{
    		if (add_gl_account($_POST['account_code'], $_POST['account_name'],
				$_POST['account_type'], $_POST['account_code2']))
				{
					add_tag_associations($_POST['account_code'], $_POST['account_tags']);
					display_notification(_("New account has been added."));
					$selected_account = $_POST['AccountList'] = $_POST['account_code'];
				}
			else
                 display_error(_("Account not added, possible duplicate Account Code."));
		}
		$Ajax->activate('_page_body');
	}
}

//-------------------------------------------------------------------------------------

function can_delete($selected_account)
{
	if ($selected_account == "")
		return false;

	if (key_in_foreign_table($selected_account, 'gl_trans', 'account'))
	{
		display_error(_("Cannot delete this account because transactions have been created using this account."));
		return false;
	}

	if (gl_account_in_company_defaults($selected_account))
	{
		display_error(_("Cannot delete this account because it is used as one of the company default GL accounts."));
		return false;
	}

	if (key_in_foreign_table($selected_account, 'bank_accounts', 'account_code'))
	{
		display_error(_("Cannot delete this account because it is used by a bank account."));
		return false;
	}

	if (gl_account_in_stock_category($selected_account))
	{
		display_error(_("Cannot delete this account because it is used by one or more Item Categories."));
		return false;
	}

	if (gl_account_in_stock_master($selected_account))
	{
		display_error(_("Cannot delete this account because it is used by one or more Items."));
		return false;
	}

	if (gl_account_in_tax_types($selected_account))
	{
		display_error(_("Cannot delete this account because it is used by one or more Taxes."));
		return false;
	}

	if (gl_account_in_cust_branch($selected_account))
	{
		display_error(_("Cannot delete this account because it is used by one or more Customer Branches."));
		return false;
	}

	if (gl_account_in_suppliers($selected_account))
	{
		display_error(_("Cannot delete this account because it is used by one or more suppliers."));
		return false;
	}

	if (gl_account_in_quick_entry_lines($selected_account))
	{
		display_error(_("Cannot delete this account because it is used by one or more Quick Entry Lines."));
		return false;
	}

	return true;
}

//--------------------------------------------------------------------------------------

if (isset($_POST['delete']))
{

	if (can_delete($selected_account))
	{
		delete_gl_account($selected_account);
		$selected_account = $_POST['AccountList'] = '';
		delete_tag_associations(TAG_ACCOUNT,$selected_account, true);
		$selected_account = $_POST['AccountList'] = '';
		display_notification(_("Selected account has been deleted"));
		unset($_POST['account_code']);
		$Ajax->activate('_page_body');
	}
}

//-------------------------------------------------------------------------------------
$control_ci->selected_id = $selected_account;

// start_form();
$control_ci->index();
// end_form();

end_page();

?>
