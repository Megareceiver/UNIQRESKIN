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
$path_to_root = "../..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();

page(_($help_context = "Customers"), @$_REQUEST['popup'], false, "", $js);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");

$control_ci = module_control_load('manager/customer','customer');

if (isset($_GET['debtor_no'])){
	$_POST['customer_id'] = $_GET['debtor_no'];
}

// $_POST['customer_id'] = 139;

$control_ci->customer_id = get_post('customer_id','');

// $_POST['_tabs_sel'] = 'transactions';

$selected_id = get_post('customer_id','');
//--------------------------------------------------------------------------------------------

function can_process(){
	if (strlen($_POST['CustName']) == 0){
		display_error(_("The customer name cannot be empty."));
		set_focus('CustName');
		return false;
	}

	if (strlen($_POST['cust_ref']) == 0){
		display_error(_("The customer short name cannot be empty."));
		set_focus('cust_ref');
		return false;
	} else {
		$same_ref = get_customer_by_ref($_POST['cust_ref']);
		if( isset($same_ref['debtor_no']) && $same_ref['debtor_no'] !=  $_POST['customer_id'] ){
			display_error(_("Dupplicate Customer short name!"));
			set_focus('cust_ref');
		}

	}

	if (!check_num('credit_limit', 0)){
		display_error(_("The credit limit must be numeric and not less than zero."));
		set_focus('credit_limit');
		return false;
	}

	if (!check_num('pymt_discount', 0, 100)){
		display_error(_("The payment discount must be numeric and is expected to be less than 100% and greater than or equal to 0."));
		set_focus('pymt_discount');
		return false;
	}

	if (!check_num('discount', 0, 100)){
		display_error(_("The discount percentage must be numeric and is expected to be less than 100% and greater than or equal to 0."));
		set_focus('discount');
		return false;
	}
	/*

	if ($_POST['gst'] == 1)
	{
		if($_POST['customer_gst_03_type'] == -1)
		{
			display_error(_("Select GST type."));
			set_focus('customer_tax_id');
			return false;
		}
	}
	if ( $_POST['gst_03_box_msic'] == -1)
	{
		display_error(_("Select Industry code for customer."));
		set_focus('gst_03_box_msic');
		return false;
	}
	*/
	return true;
}

//--------------------------------------------------------------------------------------------

function handle_submit(&$selected_id){
	global $Ajax, $auto_create_branch;

	if (!can_process())
		return;
	
	$cus_tax_id = input_post("customer_tax_id");
	if ( input_post('gst') != 1){
	    $cus_tax_id = -1;
	}
		

	if ($selected_id){

		update_customer($_POST['customer_id'], $_POST['CustName'], trim($_POST['cust_ref']), trim($_POST['address']),
			$_POST['tax_id'], $_POST['curr_code'], $_POST['dimension_id'], $_POST['dimension2_id'],
			$_POST['credit_status'], $_POST['payment_terms'], input_num('discount') / 100, input_num('pymt_discount') / 100,
			input_num('credit_limit'), $_POST['sales_type'], $_POST['notes'], $cus_tax_id, input_val('gst_03_box_msic'));

		update_record_status($_POST['customer_id'], $_POST['inactive'],
			'debtors_master', 'debtor_no');

		$Ajax->activate('customer_id'); // in case of status change
		display_notification(_("Customer has been updated."));
	}
	else
	{ 	//it is a new customer

		begin_transaction();

		add_customer($_POST['CustName'], $_POST['cust_ref'], $_POST['address'],
			$_POST['tax_id'], $_POST['curr_code'], $_POST['dimension_id'], $_POST['dimension2_id'],
			$_POST['credit_status'], $_POST['payment_terms'], input_num('discount') / 100, input_num('pymt_discount') / 100,
			input_num('credit_limit'), $_POST['sales_type'], $_POST['notes'], $cus_tax_id, input_val('gst_03_box_msic'));

		$selected_id = $_POST['customer_id'] = db_insert_id();

		if (isset($auto_create_branch) && $auto_create_branch == 1)
		{
        	add_branch($selected_id, $_POST['CustName'], $_POST['cust_ref'],
                $_POST['address'], $_POST['salesman'], $_POST['area'], $_POST['tax_group_id'],
                get_company_pref('default_sales_act'),
                get_company_pref('default_sales_discount_act'),

                get_company_pref('debtors_act'),
                get_company_pref('default_prompt_payment_act'),

                $_POST['location'],
                $_POST['address'], 0, 0, $_POST['ship_via'], $_POST['notes']);

        	$selected_branch = db_insert_id();

			add_crm_person($_POST['CustName'], $_POST['cust_ref'], '', $_POST['address'],
				$_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'], '', '');

			$pers_id = db_insert_id();
			add_crm_contact('cust_branch', 'general', $selected_branch, $pers_id);

			add_crm_contact('customer', 'general', $selected_id, $pers_id);
		}
		commit_transaction();

		display_notification(_("A new customer has been added."));

		if (isset($auto_create_branch) && $auto_create_branch == 1)
			display_notification(_("A default Branch has been automatically created, please check default Branch values by using link below."));

		$Ajax->activate('_page_body');
	}
}
//--------------------------------------------------------------------------------------------

if (isset($_POST['submit']))
{
	handle_submit($selected_id);
}
//--------------------------------------------------------------------------------------------

if (isset($_POST['delete'])) {
    $control_ci->delete();
}


//--------------------------------------------------------------------------------------------

check_db_has_sales_types(_("There are no sales types defined. Please define at least one sales type before adding a customer."));


$control_ci->form();

end_page(@$_REQUEST['popup']);

?>
