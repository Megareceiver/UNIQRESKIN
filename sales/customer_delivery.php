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
//-----------------------------------------------------------------------------
//
//	Entry/Modify Delivery Note against Sales Order
//
$page_security = 'SA_SALESDELIVERY';
$path_to_root = "..";

include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/manufacturing.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");

$js = "";
if ($use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}
// if ($use_date_picker) {
// 	$js .= get_js_date_picker();
// }

if (isset($_GET['ModifyDelivery'])) {
	$_SESSION['page_title'] = sprintf(_("Modifying Delivery Note # %d."), $_GET['ModifyDelivery']);
	$help_context = "Modifying Delivery Note";
	processing_start();
} elseif (isset($_GET['OrderNumber'])) {
	$_SESSION['page_title'] = _($help_context = "Deliver Items for a Sales Order");
	processing_start();
}

page($_SESSION['page_title'], false, false, "", $js);
$control_ci = module_control_load('tran/delivery','sales');

//-----------------------------------------------------------------------------

if (isset($_GET['OrderNumber']) && $_GET['OrderNumber'] > 0) {

	$ord = new Cart(ST_SALESORDER, $_GET['OrderNumber'], true);

	if ($ord->count_items() == 0) {
		hyperlink_params($path_to_root . "/sales/inquiry/sales_orders_view.php",
			_("Select a different sales order to delivery"), "OutstandingOnly=1");
		die ("<br><b>" . _("This order has no items. There is nothing to delivery.") . "</b>");
	}

 	// Adjust Shipping Charge based upon previous deliveries TAM
	adjust_shipping_charge($ord, $_GET['OrderNumber']);

	$_SESSION['Items'] = $ord;
	copy_from_cart();

} elseif (isset($_GET['ModifyDelivery']) && $_GET['ModifyDelivery'] > 0) {

	$_SESSION['Items'] = new Cart(ST_CUSTDELIVERY, $_GET['ModifyDelivery']);

	if ($_SESSION['Items']->count_items() == 0) {
		hyperlink_params( "sales/inquiry/customer_inquiry.php",
			_("Select a different delivery"), "OutstandingOnly=1");
		echo "<br><center><b>" . _("This delivery has all items invoiced. There is nothing to modify.") .
			"</center></b>";
		display_footer_exit();
	}

	copy_from_cart();

} elseif ( !processing_active() ) {
	/* This page can only be called with an order number for invoicing*/

	display_error(_("This page can only be opened if an order or delivery note has been selected. Please select it first."));

	hyperlink_params("$path_to_root/sales/inquiry/sales_orders_view.php", _("Select a Sales Order to Delivery"), "OutstandingOnly=1");

	end_page();
	exit;

} else {
	check_edit_conflicts();

	if (!check_quantities()) {
		display_error(_("Selected quantity cannot be less than quantity invoiced nor more than quantity	not dispatched on sales order."));

	} elseif(!check_num('ChargeFreightCost', 0)) {
		display_error(_("Freight cost cannot be less than zero"));
		set_focus('ChargeFreightCost');
	}
}

//-----------------------------------------------------------------------------

function check_data()
{
	global $Refs, $SysPrefs;

	if (!isset($_POST['DispatchDate']) || !is_date($_POST['DispatchDate']))	{
		display_error(_("The entered date of delivery is invalid."));
		set_focus('DispatchDate');
		return false;
	}

	if (!is_date_in_fiscalyear($_POST['DispatchDate'])) {
		display_error(_("The entered date of delivery is not in fiscal year."));
		set_focus('DispatchDate');
		return false;
	}

	if (!isset($_POST['due_date']) || !is_date($_POST['due_date']))	{
		display_error(_("The entered dead-line for invoice is invalid."));
		set_focus('due_date');
		return false;
	}

	if ($_SESSION['Items']->trans_no==0) {
		if (!$Refs->is_valid($_POST['ref'])) {
			display_error(_("You must enter a reference."));
			set_focus('ref');
			return false;
		}
	}
	if ($_POST['ChargeFreightCost'] == "") {
		$_POST['ChargeFreightCost'] = price_format(0);
	}

	if (!check_num('ChargeFreightCost',0)) {
		display_error(_("The entered shipping value is not numeric."));
		set_focus('ChargeFreightCost');
		return false;
	}

	if ($_SESSION['Items']->has_items_dispatch() == 0 && input_num('ChargeFreightCost') == 0) {
		display_error(_("There are no item quantities on this delivery note."));
		return false;
	}

	if (!check_quantities()) {
		return false;
	}

	copy_to_cart();

	if (!$SysPrefs->allow_negative_stock() && ($low_stock = $_SESSION['Items']->check_qoh()))
	{
		display_error(_("This document cannot be processed because there is insufficient quantity for: ").implode(',', $low_stock));
		return false;
	}

	return true;
}
//------------------------------------------------------------------------------
function copy_to_cart()
{
	$cart = &$_SESSION['Items'];
	$cart->ship_via = $_POST['ship_via'];
	$cart->freight_cost = input_num('ChargeFreightCost');
	$cart->document_date = $_POST['DispatchDate'];
	$cart->due_date =  $_POST['due_date'];
	$cart->Location = $_POST['Location'];
	$cart->Comments = $_POST['Comments'];
	$cart->dimension_id = $_POST['dimension_id'];
	$cart->dimension2_id = $_POST['dimension2_id'];
	if ($cart->trans_no == 0)
		$cart->reference = $_POST['ref'];

}
//------------------------------------------------------------------------------

function copy_from_cart()
{
	$cart = &$_SESSION['Items'];
	$_POST['ship_via'] = $cart->ship_via;
	$_POST['ChargeFreightCost'] = price_format($cart->freight_cost);
	$_POST['DispatchDate'] = $cart->document_date;
	$_POST['due_date'] = $cart->due_date;
	$_POST['Location'] = $cart->Location;
	$_POST['Comments'] = $cart->Comments;
	$_POST['dimension_id'] = $cart->dimension_id;
	$_POST['dimension2_id'] = $cart->dimension2_id;
	$_POST['cart_id'] = $cart->cart_id;
	$_POST['ref'] = $cart->reference;
}
//------------------------------------------------------------------------------

function check_quantities()
{
	$ok =1;
	// Update cart delivery quantities/descriptions
	foreach ($_SESSION['Items']->line_items as $line=>$itm) {
		if (isset($_POST['Line'.$line])) {
		if($_SESSION['Items']->trans_no) {
			$min = $itm->qty_done;
			$max = $itm->quantity;
		} else {
			$min = 0;
			$max = $itm->quantity - $itm->qty_done;
		}

			if (check_num('Line'.$line, $min, $max)) {
				$_SESSION['Items']->line_items[$line]->qty_dispatched =
				  input_num('Line'.$line);
			} else {
				set_focus('Line'.$line);
				$ok = 0;
			}

		}

		if (isset($_POST['Line'.$line.'Desc'])) {
			$line_desc = $_POST['Line'.$line.'Desc'];
			if (strlen($line_desc) > 0) {
				$_SESSION['Items']->line_items[$line]->item_description = $line_desc;
			}
		}
	}
// ...
//	else
//	  $_SESSION['Items']->freight_cost = input_num('ChargeFreightCost');
	return $ok;
}
//------------------------------------------------------------------------------

$control_ci->form();


end_page();

?>
