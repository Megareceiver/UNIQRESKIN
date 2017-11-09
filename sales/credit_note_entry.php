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
//---------------------------------------------------------------------------
//
//	Entry/Modify free hand Credit Note
//
$page_security = 'SA_SALESCREDIT';
$path_to_root = "..";
include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
// include_once($path_to_root . "/sales/includes/ui/sales_credit_ui.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}
// if ($use_date_picker) {
// 	$js .= get_js_date_picker();
// }

if(isset($_GET['NewCredit'])) {
	$_SESSION['page_title'] = _($help_context = "Customer Credit Note");
	handle_new_credit(0);
} elseif (isset($_GET['ModifyCredit'])) {
	$_SESSION['page_title'] = sprintf(_("Modifying Customer Credit Note #%d"), $_GET['ModifyCredit']);
	handle_new_credit($_GET['ModifyCredit']);
	$help_context = "Modifying Customer Credit Note";
}

page($_SESSION['page_title'],false, false, "", $js);
$control_ci = module_control_load('tran/credit_note','sales');
//-----------------------------------------------------------------------------

check_db_has_stock_items(_("There are no items defined in the system."));
check_db_has_customer_branches(_("There are no customers, or there are no customers with branches. Please define customers and customer branches."));

//-----------------------------------------------------------------------------



//--------------------------------------------------------------------------------

function line_start_focus() {
  global $Ajax;
  $Ajax->activate('items_table');
  set_focus('_stock_id_edit');
}

//-----------------------------------------------------------------------------

function copy_to_cn()
{
	$cart = &$_SESSION['Items'];
	$cart->Comments = $_POST['CreditText'];
	$cart->document_date = $_POST['OrderDate'];
	$cart->freight_cost = input_num('ChargeFreightCost');
	$cart->Location = (isset($_POST["Location"]) ? $_POST["Location"] : "");
	$cart->sales_type = $_POST['sales_type_id'];
	if ($cart->trans_no == 0)
		$cart->reference = $_POST['ref'];
	$cart->ship_via = $_POST['ShipperID'];
	$cart->dimension_id = $_POST['dimension_id'];
	$cart->dimension2_id = $_POST['dimension2_id'];
	$cart->reason = $_POST['reason'];
}

//-----------------------------------------------------------------------------

function copy_from_cn()
{
	$cart = &$_SESSION['Items'];
	$_POST['CreditText'] = $cart->Comments;
	$_POST['OrderDate'] = $cart->document_date;
	$_POST['ChargeFreightCost'] = price_format($cart->freight_cost);
	$_POST['Location'] = $cart->Location;
	$_POST['sales_type_id'] = $cart->sales_type;
	if ($cart->trans_no == 0)
		$_POST['ref'] = $cart->reference;
	$_POST['ShipperID'] = $cart->ship_via;
	$_POST['dimension_id'] = $cart->dimension_id;
	$_POST['dimension2_id'] = $cart->dimension2_id;
	$_POST['cart_id'] = $cart->cart_id;
	$_POST['reason'] = $cart->reason;
}

//-----------------------------------------------------------------------------

function handle_new_credit($trans_no){
	processing_start();
	$_SESSION['Items'] = new Cart(ST_CUSTCREDIT,$trans_no);
	copy_from_cn();
}

//-----------------------------------------------------------------------------

function can_process(){
	global $Refs;

	$input_error = 0;

	if ($_SESSION['Items']->count_items() == 0 && (!check_num('ChargeFreightCost',0)))
		return false;

	if($_SESSION['Items']->trans_no == 0) {
	    if (!$Refs->is_valid($_POST['ref'])) {
			display_error( _("You must enter a reference."));
			set_focus('ref');
			$input_error = 1;
		}
	}

	if (!is_date($_POST['OrderDate'])) {
		display_error(_("The entered date for the credit note is invalid."));
		set_focus('OrderDate');
		$input_error = 1;
	} elseif (!is_date_in_fiscalyear($_POST['OrderDate'])) {
		display_error(_("The entered date is not in fiscal year."));
		set_focus('OrderDate');
		$input_error = 1;
	}

	if ( !isset($_POST['reason']) || trim($_POST['reason'])=='' ){
	    display_error(_("Entered Reason field!."));
	    set_focus('reason');
	    $input_error = 1;
	}




	return ($input_error == 0);
}

//-----------------------------------------------------------------------------


  //-----------------------------------------------------------------------------

// function check_item_data(){
// 	if (!check_num('qty',0)) {
// 		display_error(_("The quantity must be greater than zero."));
// 		set_focus('qty');
// 		return false;
// 	}
// 	if (!check_num('price',0)) {
// 		display_error(_("The entered price is negative or invalid."));
// 		set_focus('price');
// 		return false;
// 	}
// 	if (!check_num('Disc', 0, 100)) {
// 		display_error(_("The entered discount percent is negative, greater than 100 or invalid."));
// 		set_focus('Disc');
// 		return false;
// 	}
// 	return true;
// }

//-----------------------------------------------------------------------------

function handle_update_item(){
	if ($_POST['UpdateItem'] != "" && check_item_data()) {
		$_SESSION['Items']->update_cart_item_new(get_post('tax_type_id'),$_POST['line_no'], input_num('qty'),
			input_num('price'), input_num('Disc') / 100);
	}
    line_start_focus();
}

//-----------------------------------------------------------------------------

function handle_delete_item($line_no){
	$_SESSION['Items']->remove_from_cart($line_no);
    line_start_focus();
}

//-----------------------------------------------------------------------------

function handle_new_item(){

	if (!check_item_data())
		return;

	add_to_order_new(get_post('tax_type_id'),$_SESSION['Items'], $_POST['stock_id'], input_num('qty'),
		input_num('price'), input_num('Disc') / 100);
    line_start_focus();
}
//-----------------------------------------------------------------------------


//-----------------------------------------------------------------------------
$control_ci->form();

end_page();

?>
