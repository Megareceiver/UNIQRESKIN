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
$page_security = 'SA_SUPPLIERCREDIT';
$path_to_root = "..";

include_once($path_to_root . "/purchasing/includes/supp_trans_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();
page(_($help_context = "Supplier Credit Note"), false, false, "", $js);
$conttrol_ci = module_control_load('tran/credit','purchases');
//----------------------------------------------------------------------------------------

check_db_has_suppliers(_("There are no suppliers defined in the system."));

//---------------------------------------------------------------------------------------------------------------


function clear_fields(){
	global $Ajax;

	unset($_POST['gl_code']);
	unset($_POST['dimension_id']);
	unset($_POST['dimension2_id']);
	unset($_POST['amount']);
	unset($_POST['memo_']);
	unset($_POST['AddGLCodeToTrans']);
	$Ajax->activate('gl_items');
	set_focus('gl_code');
}

function reset_tax_input() {
	global $Ajax;

	unset($_POST['mantax']);
	$Ajax->activate('inv_tot');
}

//------------------------------------------------------------------------------------------------


function check_data() {
	global $total_grn_value, $total_gl_value, $Refs, $SysPrefs;

	if (!$_SESSION['supp_trans']->is_valid_trans_to_post()) {
		display_error(_("The credit note cannot be processed because the there are no items or values on the invoice.  Credit notes are expected to have a charge."));
		set_focus('');
		return false;
	}

	if (!$Refs->is_valid($_SESSION['supp_trans']->reference)) {
		display_error(_("You must enter an credit note reference."));
		set_focus('reference');
		return false;
	}

	if (!is_new_reference($_SESSION['supp_trans']->reference, ST_SUPPCREDIT))
	{
		display_error(_("The entered reference is already in use."));
		set_focus('reference');
		return false;
	}

	if (!$Refs->is_valid($_SESSION['supp_trans']->supp_reference))
	{
		display_error(_("You must enter a supplier's credit note reference."));
		set_focus('supp_reference');
		return false;
	}

	if (!is_date($_SESSION['supp_trans']->tran_date))
	{
		display_error(_("The credit note as entered cannot be processed because the date entered is not valid."));
		set_focus('tran_date');
		return false;
	}
	elseif (!is_date_in_fiscalyear($_SESSION['supp_trans']->tran_date))
	{
		display_error(_("The entered date is not in fiscal year."));
		set_focus('tran_date');
		return false;
	}
	if (!is_date( $_SESSION['supp_trans']->due_date))
	{
		display_error(_("The invoice as entered cannot be processed because the due date is in an incorrect format."));
		set_focus('due_date');
		return false;
	}

	if( abs($_SESSION['supp_trans']->ov_amount +$_SESSION['supp_trans']->ov_gst_amount) < ($total_gl_value + $total_grn_value) ) {
		display_error(_("The credit note total as entered is less than the sum of the the general ledger entires (if any) and the charges for goods received. There must be a mistake somewhere, the credit note as entered will not be processed."));
		return false;
	}

	if ( !isset($_POST['reason']) || trim($_POST['reason'])=='' ){
	    display_error(_("Entered Reason field!."));
	    set_focus('reason');
	    return false;
	}

	if (!$SysPrefs->allow_negative_stock()) {
		foreach ($_SESSION['supp_trans']->grn_items as $n => $item) {
			if (is_inventory_item($item->item_code)) {
				if (check_negative_stock($item->item_code, -$item->this_quantity_inv, null, $_SESSION['supp_trans']->tran_date)){
				    if( !isset($qoh) ) $qoh = 0;
					$stock = get_item($item->item_code);
					display_error(_("The return cannot be processed because there is an insufficient quantity for item:") .
						" " . $stock['stock_id'] . " - " . $stock['description'] . " - " .
						_("Quantity On Hand") . " = " . number_format2($qoh, get_qty_dec($stock['stock_id'])));
					return false;
				}
			}
		}
	}
	return true;
}

//---------------------------------------------------------------------------------------------------

function handle_commit_credit_note(){
	copy_to_trans($_SESSION['supp_trans']);

	if (!check_data())
		return;

	if (isset($_POST['invoice_no']))
		$invoice_no = add_supp_invoice($_SESSION['supp_trans'], $_POST['invoice_no']);
	else
		$invoice_no = add_supp_invoice($_SESSION['supp_trans']);

    $_SESSION['supp_trans']->clear_items();
    unset($_SESSION['supp_trans']);

	meta_forward($_SERVER['PHP_SELF'], "AddedID=$invoice_no");
}

//--------------------------------------------------------------------------------------------------

function check_item_data($n){
	if (!check_num('This_QuantityCredited'.$n, 0)){
		display_error(_("The quantity to credit must be numeric and greater than zero."));
		set_focus('This_QuantityCredited'.$n);
		return false;
	}

	if (!check_num('ChgPrice'.$n, 0))
	{
		display_error(_("The price is either not numeric or negative."));
		set_focus('ChgPrice'.$n);
		return false;
	}

	return true;
}

function commit_item_data($n) {
	if (check_item_data($n)) {

		$_SESSION['supp_trans']->add_grn_to_trans($_POST['tax_id'.$n],$n,
    		$_POST['po_detail_item'.$n], $_POST['item_code'.$n],
    		$_POST['item_description'.$n], $_POST['qty_recd'.$n],
    		$_POST['prev_quantity_inv'.$n], input_num('This_QuantityCredited'.$n),
    		$_POST['order_price'.$n], input_num('ChgPrice'.$n),
    		$_POST['std_cost_unit'.$n], "");

// 		bug($_SESSION['supp_trans']);
	}
}

//-----------------------------------------------------------------------------------------

$conttrol_ci->form();

end_page();
?>
