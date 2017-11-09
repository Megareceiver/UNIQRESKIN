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
$page_security = 'SA_GRN';
$path_to_root = "..";
include_once($path_to_root . "/purchasing/includes/po_class.inc");

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();
page(_($help_context = "Receive Purchase Order Items"), false, false, "", $js);
$conttrol_ci = module_control_load('tran/receive','purchases');
//---------------------------------------------------------------------------------------------------------------

function check_po_changed()
{
	/*Now need to check that the order details are the same as they were when they were read
	into the Items array. If they've changed then someone else must have altered them */
	// Sherifoz 22.06.03 Compare against COMPLETED items only !!
	// Otherwise if you try to fullfill item quantities separately will give error.
	$result = get_po_items($_SESSION['PO']->order_no);

	$line_no = 0;
	while ($myrow = db_fetch($result))
	{
		$ln_item = $_SESSION['PO']->line_items[$line_no];
		// only compare against items that are outstanding
		$qty_outstanding = $ln_item->quantity - $ln_item->qty_received;
		if ($qty_outstanding > 0)
		{
    		if ($ln_item->qty_inv != $myrow["qty_invoiced"]	||
    			$ln_item->stock_id != $myrow["item_code"] ||
    			$ln_item->quantity != $myrow["quantity_ordered"] ||
    			$ln_item->qty_received != $myrow["quantity_received"])
    		{
    			return true;
    		}
		}
	 	$line_no++;
	} /*loop through all line items of the order to ensure none have been invoiced */

	return false;
}

//--------------------------------------------------------------------------------------------------

function can_process()
{
	global $SysPrefs, $Refs;

	if (count($_SESSION['PO']->line_items) <= 0)
	{
        display_error(_("There is nothing to process. Please enter valid quantities greater than zero."));
    	return false;
	}

	if (!is_date($_POST['DefaultReceivedDate']))
	{
		display_error(_("The entered date is invalid."));
		set_focus('DefaultReceivedDate');
		return false;
	}

	if (!is_date_in_fiscalyear($_POST['DefaultReceivedDate']))
	{
		display_error(_("The entered date is not in fiscal year"));
		set_focus('DefaultReceivedDate');
		return false;
	}

    if (!$Refs->is_valid($_POST['ref']))
    {
		display_error(_("You must enter a reference."));
		set_focus('ref');
		return false;
	}

	if (!is_new_reference($_POST['ref'], ST_SUPPRECEIVE))
	{
		display_error(_("The entered reference is already in use."));
		set_focus('ref');
		return false;
	}

	$something_received = 0;
	foreach ($_SESSION['PO']->line_items as $order_line)
	{
	  	if ($order_line->receive_qty > 0)
	  	{
			$something_received = 1;
			break;
	  	}
	}

    // Check whether trying to deliver more items than are recorded on the actual purchase order (+ overreceive allowance)
    $delivery_qty_too_large = 0;
	foreach ($_SESSION['PO']->line_items as $order_line)
	{
	  	if ($order_line->receive_qty+$order_line->qty_received >
	  		$order_line->quantity * (1+ ($SysPrefs->over_receive_allowance() / 100)))
	  	{
			$delivery_qty_too_large = 1;
			break;
	  	}
	}

    if ($something_received == 0)
    { 	/*Then dont bother proceeding cos nothing to do ! */
        display_error(_("There is nothing to process. Please enter valid quantities greater than zero."));
    	return false;
    }
    elseif ($delivery_qty_too_large == 1)
    {
    	display_error(_("Entered quantities cannot be greater than the quantity entered on the purchase order including the allowed over-receive percentage") . " (" . $SysPrefs->over_receive_allowance() ."%)."
    		. "<br>" .
    	 	_("Modify the ordered items on the purchase order if you wish to increase the quantities."));
    	return false;
    }

	return true;
}

//--------------------------------------------------------------------------------------------------

function process_receive_po()
{
	global $path_to_root, $Ajax;

	if (!can_process())
		return;

	if (check_po_changed())
	{
		display_error(_("This order has been changed or invoiced since this delivery was started to be actioned. Processing halted. To enter a delivery against this purchase order, it must be re-selected and re-read again to update the changes made by the other user."));

		hyperlink_no_params("$path_to_root/purchasing/inquiry/po_search.php",
		 _("Select a different purchase order for receiving goods against"));

		hyperlink_params("$path_to_root/purchasing/po_receive_items.php",
			 _("Re-Read the updated purchase order for receiving goods against"),
			 "PONumber=" . $_SESSION['PO']->order_no);

		unset($_SESSION['PO']->line_items);
		unset($_SESSION['PO']);
		unset($_POST['ProcessGoodsReceived']);
		$Ajax->activate('_page_body');
		display_footer_exit();
	}

	$grn = &$_SESSION['PO'];
	$grn->orig_order_date = $_POST['DefaultReceivedDate'];
	$grn->reference = $_POST['ref'];
	$grn->Location = $_POST['Location'];
	$grn->ex_rate = input_num('_ex_rate', null);

	$grn_no = add_grn($grn);

	new_doc_date($_POST['DefaultReceivedDate']);
	unset($_SESSION['PO']->line_items);
	unset($_SESSION['PO']);

	meta_forward($_SERVER['PHP_SELF'], "AddedID=$grn_no");
}


$conttrol_ci->form();

//--------------------------------------------------------------------------------------------------
end_page();
?>

