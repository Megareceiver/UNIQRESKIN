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
$page_security = 'SA_SUPPLIERINVOICE';
$path_to_root = "..";

include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();
page(_($help_context = "Enter Supplier Invoice"), false, false, "", $js);
$conttrol_ci = module_control_load('tran/invoice','purchases');

check_db_has_suppliers(_("There are no suppliers defined in the system."));

//--------------------------------------------------------------------------------------------------
function clear_fields(){
	global $Ajax;

	unset($_POST['gl_code']);
	unset($_POST['dimension_id']);
	unset($_POST['dimension2_id']);
	unset($_POST['amount']);
	unset($_POST['memo_']);
	unset($_POST['AddGLCodeToTrans']);
	unset($_POST['fixed_access']);
	unset($_POST['tax_id']);

	$Ajax->activate('gl_items');
	set_focus('gl_code');
}

function reset_tax_input(){
	global $Ajax;

	unset($_POST['mantax']);
	$Ajax->activate('inv_tot');
}

//------------------------------------------------------------------------------------------------

function check_data(){
	global $Refs,$ci;

	if (!$_SESSION['supp_trans']->is_valid_trans_to_post()){
		display_error(_("The invoice cannot be processed because the there are no items or values on the invoice.  Invoices are expected to have a charge."));
		return false;
	}

	if (!$Refs->is_valid($_SESSION['supp_trans']->reference))
	{
		display_error(_("You must enter an invoice reference."));
		set_focus('reference');
		return false;
	}

	if (!is_new_reference($_SESSION['supp_trans']->reference, ST_SUPPINVOICE))
	{
		display_error(_("The entered reference is already in use."));
		set_focus('reference');
		return false;
	}

	if (!$Refs->is_valid($_SESSION['supp_trans']->supp_reference))
	{
		display_error(_("You must enter a supplier's invoice reference."));
		set_focus('supp_reference');
		return false;
	}

	if (!is_date( $_SESSION['supp_trans']->tran_date))
	{
		display_error(_("The invoice as entered cannot be processed because the invoice date is in an incorrect format."));
		set_focus('trans_date');
		return false;
	}
	elseif (!is_date_in_fiscalyear($_SESSION['supp_trans']->tran_date))
	{
		display_error(_("The entered date is not in fiscal year."));
		set_focus('trans_date');
		return false;
	}
	if (!is_date( $_SESSION['supp_trans']->due_date))
	{
		display_error(_("The invoice as entered cannot be processed because the due date is in an incorrect format."));
		set_focus('due_date');
		return false;
	}

	if (is_reference_already_there($_SESSION['supp_trans']->supplier_id, $_POST['supp_reference']))
	{ 	/*Transaction reference already entered */
		display_error(_("This invoice number has already been entered. It cannot be entered again.") . " (" . $_POST['supp_reference'] . ")");
		return false;
	}
	/*
	 * remove Kastam 150921
	 */
	/*
	if ( isset($_POST['simplified']) && $_POST['simplified'] ==1 ){
	    $max_claimable = $_SESSION['SysPrefs']->prefs['maximum_claimable_input_tax'];
	    $max_claimable_curr = $_SESSION['SysPrefs']->prefs['maximum_claimable_currency'];
	    $ex_rate = get_exchange_rate_from_to($_SESSION['SysPrefs']->prefs['curr_default'], $max_claimable_curr, $_SESSION['supp_trans']->tran_date);
	    if( intval( $ci->input->post('gst_total') ) > $max_claimable/$ex_rate ) {
	        display_error(_("<b>Simplified Invoice:</b> Maximum Claimble Input Tax can't over ".$max_claimable.' '.$max_claimable_curr));
	        return false;
	    }

	}
	*/

	return true;
}

//--------------------------------------------------------------------------------------------------

function handle_commit_invoice(){

	copy_to_trans($_SESSION['supp_trans']);

	if (!check_data())
		return;


	$invoice_no = add_supp_invoice($_SESSION['supp_trans']);

	if( $invoice_no && input_val('imported_goods') ) {
	    global $ci;
	    $ci->db->update('supp_trans',array('imported_goods'=>input_val('imported_goods') ),array('trans_no'=>$invoice_no,'type'=>$_SESSION['supp_trans']->trans_type));
	}

	if( intval( $document_id = input_post('document_id')) > 0 ){
	    $mobile_model = module_model_load('mobile','documents');
	    $mobile_model->update_posting_link($_SESSION['supp_trans']->trans_type,$invoice_no,$document_id);
	}

    $_SESSION['supp_trans']->clear_items();
    unset($_SESSION['supp_trans']);


	meta_forward($_SERVER['PHP_SELF'], "AddedID=$invoice_no");
}

//--------------------------------------------------------------------------------------------------

function check_item_data($n){
	global $check_price_charged_vs_order_price, $check_qty_charged_vs_del_qty, $SysPrefs;
	if (!check_num('this_quantity_inv'.$n, 0) || input_num('this_quantity_inv'.$n)==0) {
		display_error( _("The quantity to invoice must be numeric and greater than zero."));
		set_focus('this_quantity_inv'.$n);
		return false;
	}

	if (!check_num('ChgPrice'.$n)) {
		display_error( _("The price is not numeric."));
		set_focus('ChgPrice'.$n);
		return false;
	}

	$margin = $SysPrefs->over_charge_allowance();
	if ($check_price_charged_vs_order_price == True)
	{
		if ($_POST['order_price'.$n]!=input_num('ChgPrice'.$n)) {
		     if ($_POST['order_price'.$n]==0 ||
				input_num('ChgPrice'.$n)/$_POST['order_price'.$n] >
			    (1 + ($margin/ 100)))
		    {
			display_error(_("The price being invoiced is more than the purchase order price by more than the allowed over-charge percentage. The system is set up to prohibit this. See the system administrator to modify the set up parameters if necessary.") .
			_("The over-charge percentage allowance is :") . $margin . "%");
			set_focus('ChgPrice'.$n);
			return false;
		    }
		}
	}

	if ($check_qty_charged_vs_del_qty == true && ($_POST['qty_recd'.$n] != $_POST['prev_quantity_inv'.$n]))
	{
		if (input_num('this_quantity_inv'.$n) / ($_POST['qty_recd'.$n] - $_POST['prev_quantity_inv'.$n]) >
			(1+ ($margin / 100)))
		{
			display_error( _("The quantity being invoiced is more than the outstanding quantity by more than the allowed over-charge percentage. The system is set up to prohibit this. See the system administrator to modify the set up parameters if necessary.")
			. _("The over-charge percentage allowance is :") . $margin . "%");
			set_focus('this_quantity_inv'.$n);
			return false;
		}
	}

	return true;
}

function commit_item_data($n) {
	if (check_item_data($n)) {
		$_SESSION['supp_trans']->add_grn_to_trans($_POST['tax_id'.$n],$n, $_POST['po_detail_item'.$n],
			$_POST['item_code'.$n], $_POST['item_description'.$n], $_POST['qty_recd'.$n],
			$_POST['prev_quantity_inv'.$n], input_num('this_quantity_inv'.$n),
			$_POST['order_price'.$n], input_num('ChgPrice'.$n),
			$_POST['std_cost_unit'.$n], "");
		reset_tax_input();
	}
}

//-----------------------------------------------------------------------------------------
$conttrol_ci->form();

end_page();
?>
