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
$page_security = 'SA_CUSTOMER';
$path_to_root="../..";

include($path_to_root . "/includes/db_pager.inc");
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/includes/ui/contacts_view.inc");

$control_ci = module_control_load('manager/branches','customer');
page(_($help_context = "Customer Branches"), @$_REQUEST['popup']);
//-----------------------------------------------------------------------------------------------

check_db_has_customers(_("There are no customers defined in the system. Please define a customer to add customer branches."));
check_db_has_sales_people(_("There are no sales people defined in the system. At least one sales person is required before proceeding."));
check_db_has_sales_areas(_("There are no sales areas defined in the system. At least one sales area is required before proceeding."));
check_db_has_shippers(_("There are no shipping companies defined in the system. At least one shipping company is required before proceeding."));
check_db_has_tax_groups(_("There are no tax groups defined in the system. At least one tax group is required before proceeding."));


simple_page_mode(true);
//-----------------------------------------------------------------------------------------------

if (isset($_GET['debtor_no']))
{
	$_POST['customer_id'] = strtoupper($_GET['debtor_no']);
}

$_POST['branch_code'] = $selected_id;

if (isset($_GET['SelectedBranch']))
{
	$br = get_branch($_GET['SelectedBranch']);
	$_POST['customer_id'] = $br['debtor_no'];
	$selected_id = $_POST['branch_code'] = $br['branch_code'];

	$Mode = 'Edit';
}
//-----------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['br_name']) == 0)
	{
		$input_error = 1;
		display_error(_("The Branch name cannot be empty."));
		set_focus('br_name');
	}

	if (strlen($_POST['br_ref']) == 0)
	{
		$input_error = 1;
		display_error(_("The Branch short name cannot be empty."));
		set_focus('br_ref');
	}

	if ($input_error != 1)
	{

		begin_transaction();
    	if ($selected_id != -1)
		{
			update_branch($_POST['customer_id'], $_POST['branch_code'], $_POST['br_name'], $_POST['br_ref'],
				$_POST['br_address'], $_POST['salesman'], $_POST['area'], $_POST['tax_group_id'], $_POST['sales_account'],
				$_POST['sales_discount_account'], $_POST['receivables_account'], $_POST['payment_discount_account'],
				$_POST['default_location'], $_POST['br_post_address'], $_POST['disable_trans'], $_POST['group_no'],
				$_POST['default_ship_via'], $_POST['notes']);
//			update_record_status($_POST['supplier_id'], $_POST['inactive'],
//				'cust_branch', 'branch_code');

			$note =_('Selected customer branch has been updated');
  		}
		else
		{
			add_branch($_POST['customer_id'], $_POST['br_name'], $_POST['br_ref'],
				$_POST['br_address'], $_POST['salesman'], $_POST['area'], $_POST['tax_group_id'], $_POST['sales_account'],
				$_POST['sales_discount_account'], $_POST['receivables_account'], $_POST['payment_discount_account'],
				$_POST['default_location'], $_POST['br_post_address'], 0, $_POST['group_no'],
				$_POST['default_ship_via'], $_POST['notes']);
			$selected_id = db_insert_id();

			add_crm_person($_POST['contact_name'], $_POST['contact_name'], '', $_POST['br_post_address'],
				$_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'],
				$_POST['rep_lang'], '');

			add_crm_contact('cust_branch', 'general', $selected_id, db_insert_id());


			$note = _('New customer branch has been added');
		}
		commit_transaction();
		display_notification($note);
//		$Mode = 'RESET';
		if (@$_REQUEST['popup']) {
			set_focus("Select".($_POST['branch_code'] == -1 ? $selected_id: $_POST['branch_code']));
		}
	}

}
elseif ($Mode == 'Delete')
{
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'debtor_trans'

	if (branch_in_foreign_table($_POST['customer_id'], $_POST['branch_code'], 'debtor_trans'))
	{
		display_error(_("Cannot delete this branch because customer transactions have been created to this branch."));

	}
	else
	{
		if (branch_in_foreign_table($_POST['customer_id'], $_POST['branch_code'], 'sales_orders'))
		{
			display_error(_("Cannot delete this branch because sales orders exist for it. Purge old sales orders first."));
		}
		else
		{
			delete_branch($_POST['customer_id'], $_POST['branch_code']);
			display_notification(_('Selected customer branch has been deleted'));
		}
	} //end ifs to test if the branch can be deleted
	$Mode = 'RESET';
}

if ($Mode == 'RESET' || get_post('_customer_id_update'))
{
	$selected_id = -1;
	$cust_id = $_POST['customer_id'];
	$inact = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $inact;
	$_POST['customer_id'] = $cust_id;
	$Ajax->activate('_page_body');
}


function branch_email($row) {
	return	'<a href = "mailto:'.$row["email"].'">'.$row["email"].'</a>';
}

// function edit_link($row) {
// 	return button("Edit".$row["branch_code"],_("Edit"), '', ICON_EDIT);
// }

function del_link($row) {
	return button("Delete".$row["branch_code"],_("Delete"), '', ICON_DELETE);
}

function select_link($row) {
	return button("Select".$row["branch_code"], $row["branch_code"], '', ICON_ADD, 'selector');
}



$control_ci->customer_id = $selected_id;
$control_ci->id = $selected_id;

$control_ci->mode = $Mode;
$control_ci->form();


end_page(@$_REQUEST['popup']);

?>
