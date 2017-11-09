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
//	Entry/Modify Sales Invoice against single delivery
//	Entry/Modify Batch Sales Invoice against batch of deliveries
//
$page_security = 'SA_SALESINVOICE';
$path_to_root = "..";
include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/manufacturing.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");
// QuanICT will work here
$js = "";
if ($use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}
// if ($use_date_picker) {
// 	$js .= get_js_date_picker();
// }

if (isset($_GET['ModifyInvoice'])) {
	$_SESSION['page_title'] = sprintf(_("Modifying Sales Invoice # %d.") ,$_GET['ModifyInvoice']);
	$help_context = "Modifying Sales Invoice";
} elseif (isset($_GET['DeliveryNumber'])) {
	$_SESSION['page_title'] = _($help_context = "Issue an Invoice for Delivery Note");
} elseif (isset($_GET['BatchInvoice'])) {
	$_SESSION['page_title'] = _($help_context = "Issue Batch Invoice for Delivery Notes");
}

page($_SESSION['page_title'], false, false, "", $js);


//-----------------------------------------------------------------------------
check_edit_conflicts();

//-----------------------------------------------------------------------------
$ci_controller = module_control_load('tran/invoice','sales');

if (isset($_POST['Update'])) {
    copy_to_cart();
	$Ajax->activate('Items');
}
if (isset($_POST['_InvoiceDate_changed'])) {
	$_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['InvoiceDate']);
	$Ajax->activate('due_date');
}
if (list_updated('payment')) {
	$_SESSION['Items']->payment = get_post('payment');
	$_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['InvoiceDate']);
	$Ajax->activate('due_date');
}

//-----------------------------------------------------------------------------
function check_quantities(){
	$ok =1;
	foreach ($_SESSION['Items']->line_items as $line_no=>$itm) {
		if (isset($_POST['Line'.$line_no])) {
			if($_SESSION['Items']->trans_no) {
				$min = $itm->qty_done;
				$max = $itm->quantity;
			} else {
				$min = 0;
				$max = $itm->quantity - $itm->qty_done;
			}
			if (check_num('Line'.$line_no, $min, $max)) {
				$_SESSION['Items']->line_items[$line_no]->qty_dispatched =
				    input_num('Line'.$line_no);
			}
			else {
				$ok = 0;
			}

		}

		if (isset($_POST['Line'.$line_no.'Desc'])) {
			$line_desc = $_POST['Line'.$line_no.'Desc'];
			if (strlen($line_desc) > 0) {
				$_SESSION['Items']->line_items[$line_no]->item_description = $line_desc;
			}
		}
	}
 return $ok;
}

function set_delivery_shipping_sum($delivery_notes){
    $shipping = 0;
    foreach($delivery_notes as $delivery_num){
        $myrow = get_customer_trans($delivery_num, 13);
        //$branch = get_branch($myrow["branch_code"]);
        //$sales_order = get_sales_order_header($myrow["order_"]);

        //$shipping += $sales_order['freight_cost'];
        $shipping += $myrow['ov_freight'];
    }
    $_POST['ChargeFreightCost'] = price_format($shipping);
}


/* QuanNH update edit item */
function update_items(){
    $item_use = array();

    $update_fields = array('id','stock_id','item_description','quantity','units','qty_done','qty_dispatched','price','tax_type_id','discount_percent');

    $line_no = 0;
    $line_update = input_post("line");
    if( !empty($line_update) ){
        foreach ($line_update AS $ite){
        
            $_SESSION['Items']->remove_from_cart($line_no);
            $_SESSION['Items']->add_to_cart_new($ite['tax_type_id'], $line_no, $ite['stock_id'], $ite['quantity'], $ite['price'], $ite['discount_percent']/100, $ite['qty_done'], 0, $ite['item_description']);
            $line_no++;
        }
    }
    
}

function copy_to_cart(){
    //update_items();
	$cart = &$_SESSION['Items'];
	$cart->ship_via = $_POST['ship_via'];
	$cart->freight_cost = input_num('ChargeFreightCost');
	$cart->document_date =  $_POST['InvoiceDate'];
	$cart->due_date =  $_POST['due_date'];
	if ($cart->pos['cash_sale'] || $cart->pos['credit_sale']) {
		$cart->payment = $_POST['payment'];
		$cart->payment_terms = get_payment_terms($_POST['payment']);
	}
	$cart->Comments = $_POST['Comments'];
	if ($_SESSION['Items']->trans_no == 0)
		$cart->reference = $_POST['ref'];
	$cart->dimension_id =  $_POST['dimension_id'];
	$cart->dimension2_id =  $_POST['dimension2_id'];

}
//-----------------------------------------------------------------------------

function copy_from_cart(){
	$cart = &$_SESSION['Items'];
	$_POST['ship_via'] = $cart->ship_via;
	$_POST['ChargeFreightCost'] = price_format($cart->freight_cost);
	$_POST['InvoiceDate']= $cart->document_date;
	$_POST['due_date'] = $cart->due_date;
	$_POST['Comments']= $cart->Comments;
	$_POST['cart_id'] = $cart->cart_id;
	$_POST['ref'] = $cart->reference;
	$_POST['payment'] = $cart->payment;
	$_POST['dimension_id'] = $cart->dimension_id;
	$_POST['dimension2_id'] = $cart->dimension2_id;
}

//-----------------------------------------------------------------------------

function check_data(){
	global $Refs;

	if (!isset($_POST['InvoiceDate']) || !is_date($_POST['InvoiceDate'])) {
		display_error(_("The entered invoice date is invalid."));
		set_focus('InvoiceDate');
		return false;
	}

	if (!is_date_in_fiscalyear($_POST['InvoiceDate'])) {
		display_error(_("The entered invoice date is not in fiscal year."));
		set_focus('InvoiceDate');
		return false;
	}

	if (!isset($_POST['due_date']) || !is_date($_POST['due_date']))	{
		display_error(_("The entered invoice due date is invalid."));
		set_focus('due_date');
		return false;
	}

	if ($_SESSION['Items']->trans_no == 0) {
	    $ref = input_post('ref');
	    if( !$ref && isset($_SESSION['Items']->reference) ){
	        $ref = $_SESSION['Items']->reference;
	    }
	    if( !$ref  ){
	        $ref = $Refs->get_next($_SESSION['Items']->trans_type);
	    }
		if (!$Refs->is_valid($ref)) {
			display_error(_("You must enter a reference."));
			if( isset($_POST['ref']) ){
			    set_focus('ref');
			}
			
			return false;
		}
	}

	if ($_POST['ChargeFreightCost'] == "") {
		$_POST['ChargeFreightCost'] = price_format(0);
	}

	if (!check_num('ChargeFreightCost', 0)) {
		display_error(_("The entered shipping value is not numeric."));
		set_focus('ChargeFreightCost');
		return false;
	}

	if ($_SESSION['Items']->has_items_dispatch() == 0 && input_num('ChargeFreightCost') == 0) {
		display_error(_("There are no item quantities on this invoice."));
		return false;
	}

	if (!check_quantities()) {
		display_error(_("Selected quantity cannot be less than quantity credited nor more than quantity not invoiced yet."));
		return false;
	}

	return true;
}


$ci_controller->form();

end_form();

end_page();

?>
