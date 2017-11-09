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
$page_security = 'SA_SUPPLIER';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");

$common_model = get_instance()->model('common',true);
$control_ci = module_control_load('manage/supplier','supplier');

$js = "";
if ($use_popup_windows)
    $js .= get_js_open_window(900, 500);

page(_($help_context = "Suppliers"), @$_REQUEST['popup'], false, "", $js);

check_db_has_tax_groups(_("There are no tax groups defined in the system. At least one tax group is required before proceeding."));

if (isset($_GET['supplier_id'])){
	$_POST['supplier_id'] = $_GET['supplier_id'];
}

$supplier_id = get_post('supplier_id');
//--------------------------------------------------------------------------------------------


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['supp_name']) == 0 || $_POST['supp_name'] == "") {
		$input_error = 1;
		display_error(_("The supplier name must be entered."));
		set_focus('supp_name');
	}

	if (strlen($_POST['supp_ref']) == 0 || $_POST['supp_ref'] == "") {
		$input_error = 1;
		display_error(_("The supplier short name must be entered."));
		set_focus('supp_ref');
	} else {
		$same_ref = $ci->db->where('supp_ref',$_POST['supp_ref'])->get('suppliers')->row();

		if( isset($same_ref->supplier_id) && $same_ref->supplier_id !=  $_POST['supplier_id']){
			display_error(_("Dupplicate Supplier short name!"));
			set_focus('supp_ref');
		}

	}

	if ($input_error !=1 ) {

	    $supplier_tax_id = (input_val('gst') == 0)  ? -1 : input_val('supplier_gst_03_type');
		begin_transaction();

		$supplier_update = array(
		    'supp_name'=>null,
		    'supp_ref'=>null,
		    'address'=>null,
		    'supp_address'=>null,
		    'gst_no'=>null,
		    'website'=>null,
		    'supp_account_no'=>null,
		    'bank_account'=>null,
		    'credit_limit'=>null,
		    'dimension_id'=>null,
		    'dimension2_id'=>null,
		    'curr_code'=>null,
		    'payment_terms'=>null,
		    'payable_account'=>null,
		    'purchase_account'=>null,
		    'payment_discount_account'=>null,
		    'notes'=>null,
		    'tax_group_id'=>null,
		    'tax_included'=>null,

		    'self_bill_approval_ref'=>null,
		    'self_bill'=>null,
		    'valid_gst'=>0,
		    'last_verifile'=>null
		);
		$supplier_update = $ci->finput->get_post($supplier_update);
		$supplier_update['credit_limit'] = floatval($supplier_update['credit_limit']);

		if( array_key_exists('tax_included', $supplier_update) ) {
		    $supplier_update['tax_included'] = ($supplier_update['tax_included'] || $supplier_update['tax_included']=='on') ? 1: 0;
		} else {
		    $supplier_update['tax_included'] = 0;
		}

        if( !isset($supplier_update['valid_gst']) ){
            $supplier_update['valid_gst'] = false;
        }

        if( isset($_POST['gst']) && isset($_POST['supplier_tax_id']) ){
            $supplier_update['supplier_tax_id'] = $ci->input->post('supplier_tax_id');

//             $supplier_update['gst'] = $ci->input->post('gst');
        } else {
            $supplier_update['supplier_tax_id'] = null;
//             $supplier_update['gst'] = 0;
        }
		if ($supplier_id) {
// 			update_supplier($_POST['supplier_id'], $_POST['supp_name'], $_POST['supp_ref'], $_POST['address'],
// 				$_POST['supp_address'], $_POST['gst_no'],
// 				$_POST['website'], $_POST['supp_account_no'], $_POST['bank_account'],
// 				input_num('credit_limit', 0), $_POST['dimension_id'], $_POST['dimension2_id'], $_POST['curr_code'],
// 				$_POST['payment_terms'], $_POST['payable_account'], $_POST['purchase_account'], $_POST['payment_discount_account'],
// 				$_POST['notes'], $ci->input->post('tax_group_id'), $ci->input->post('tax_included'), $supplier_tax_id, $ci->input->post('industry_code'),
// 				$ci->input->post('self_bill_approval_ref'),
// 				$ci->input->post('self_bill') );
		    $common_model->update($supplier_update,'suppliers',array('supplier_id'=>$_POST['supplier_id'] ),false);
// 		    bug($supplier_update);die;
			update_record_status($_POST['supplier_id'], $_POST['inactive'],
				'suppliers', 'supplier_id');

			$Ajax->activate('supplier_id'); // in case of status change
			display_notification(_("Supplier has been updated."));
		} else {
// 			add_supplier($_POST['supp_name'], $_POST['supp_ref'], $_POST['address'], $_POST['supp_address'],
// 				$_POST['gst_no'], $_POST['website'], $_POST['supp_account_no'], $_POST['bank_account'],
// 				input_num('credit_limit',0), $_POST['dimension_id'], $_POST['dimension2_id'],
// 				$_POST['curr_code'], $_POST['payment_terms'], $_POST['payable_account'], $_POST['purchase_account'],
// 				$_POST['payment_discount_account'], $_POST['notes'], $_POST['tax_group_id'], check_value('tax_included'), $supplier_tax_id, $_POST['industry_code'],
// 			    $ci->input->post('self_bill_approval_ref'),
// 				$ci->input->post('self_bill'));
			$common_model->update($supplier_update,'suppliers',array('supplier_id'=>$_POST['supplier_id'] ),false);

			$supplier_id = $_POST['supplier_id'] = db_insert_id();

			add_crm_person($_POST['supp_ref'], $_POST['contact'], '', $_POST['address'],
				$_POST['phone'], $_POST['phone2'], $_POST['fax'], $_POST['email'],
				$_POST['rep_lang'], '');

			add_crm_contact('supplier', 'general', $supplier_id, db_insert_id());

			display_notification(_("A new supplier has been added."));

			if( !check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."debtors_master WHERE inactive=0") ){
			    display_notification(_("Go to : ".anchor('sales/manage/customers.php','add new Customer')));
			}


			$Ajax->activate('_page_body');
		}
		commit_transaction();
	}

} elseif (isset($_POST['delete']) && $_POST['delete'] != "") {
	//the link to delete a selected record was clicked instead of the submit button

	$cancel_delete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'supp_trans' , purch_orders

	if (key_in_foreign_table($_POST['supplier_id'], 'supp_trans', 'supplier_id')) {
		$cancel_delete = 1;
		display_error(_("Cannot delete this supplier because there are transactions that refer to this supplier."));

	}
	else
	{
		if (key_in_foreign_table($_POST['supplier_id'], 'purch_orders', 'supplier_id'))
		{
			$cancel_delete = 1;
			display_error(_("Cannot delete the supplier record because purchase orders have been created against this supplier."));
		}

	}
	if ($cancel_delete == 0)
	{
		delete_supplier($_POST['supplier_id']);

		unset($_SESSION['supplier_id']);
		$supplier_id = '';
		$Ajax->activate('_page_body');
	} //end if Delete supplier
}

$control_ci->id = $supplier_id;
$control_ci->index();


end_page(@$_REQUEST['popup']);

?>
