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
$path_to_root = "..";
$page_security = 'SA_PURCHASEORDER';
include_once($path_to_root . "/purchasing/includes/po_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");
include_once($path_to_root . "/purchasing/includes/db/suppliers_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
global $ci;

set_page_security( @$_SESSION['PO']->trans_type,
	array(	ST_PURCHORDER => 'SA_PURCHASEORDER',
			ST_SUPPRECEIVE => 'SA_GRN',
			ST_SUPPINVOICE => 'SA_SUPPLIERINVOICE'),
	array(	'NewOrder' => 'SA_PURCHASEORDER',
			'ModifyOrderNumber' => 'SA_PURCHASEORDER',
			'AddedID' => 'SA_PURCHASEORDER',
			'NewGRN' => 'SA_GRN',
			'AddedGRN' => 'SA_GRN',
			'NewInvoice' => 'SA_SUPPLIERINVOICE',
			'AddedPI' => 'SA_SUPPLIERINVOICE')
);

$js = '';
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();

if (isset($_GET['ModifyOrderNumber']) && is_numeric($_GET['ModifyOrderNumber'])) {

	$_SESSION['page_title'] = _($help_context = "Modify Purchase Order #") . $_GET['ModifyOrderNumber'];
	create_new_po(ST_PURCHORDER, $_GET['ModifyOrderNumber']);
	copy_from_cart();
} elseif (isset($_GET['NewOrder'])) {

	$_SESSION['page_title'] = _($help_context = "Purchase Order Entry");
	create_new_po(ST_PURCHORDER, 0);
	copy_from_cart();
} elseif (isset($_GET['NewGRN'])) {

	$_SESSION['page_title'] = _($help_context = "Direct GRN Entry");
	create_new_po(ST_SUPPRECEIVE, 0);
	copy_from_cart();
} elseif (isset($_GET['NewInvoice'])) {

	$_SESSION['page_title'] = _($help_context = "Direct Purchase Invoice Entry");
	create_new_po(ST_SUPPINVOICE, 0);
	copy_from_cart();
} else if ( $ci->input->get('reinvoice') ) {
    global $ref;
    $trans_no = $ci->input->get('reinvoice');
// unset($_SESSION['PO']);
    create_new_po(ST_PURCHORDER, $trans_no);
// bug($_SESSION['PO']);die;
    // edn test get

    $cart = $ci->load_library('purchase_cart',true);
    $cart->invoice($trans_no);
    $ci->purchase_trans->submit_trans($cart);

   redirect('gl/view/gl_trans_view.php?type_id='.ST_SUPPINVOICE.'&trans_no='.$ci->input->get('reinvoice'));
} else if ($ci->input->get('re-receive')) {
    $trans_no = $ci->input->get('re-receive');
    $ci->controller_load('repost');
    $ci->repost->supp_receive( $trans_no );
    meta_forward( site_url()."/gl/view/gl_trans_view.php?type_id=".ST_SUPPRECEIVE."&trans_no=".$trans_no);
}
if( !isset($_SESSION['page_title']) ){
    $_SESSION['page_title'] = "GRN Entry";
}
page($_SESSION['page_title'], false, false, "", $js);
$conttrol_ci = module_control_load('tran/entry','purchases');
//---------------------------------------------------------------------------------------------------

check_db_has_suppliers(_("There are no suppliers defined in the system."));

check_db_has_purchasable_items(_("There are no purchasable inventory items defined in the system."));

//---------------------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------

function line_start_focus() {
  global 	$Ajax;

  $Ajax->activate('items_table');
  set_focus('_stock_id_edit');
}
//--------------------------------------------------------------------------------------------------

function unset_form_variables() {
	unset($_POST['stock_id']);
    unset($_POST['qty']);
    unset($_POST['price']);
    unset($_POST['req_del_date']);
}

//---------------------------------------------------------------------------------------------------

function handle_delete_item($line_no) {
	if($_SESSION['PO']->some_already_received($line_no) == 0){
		$_SESSION['PO']->remove_from_order($line_no);
		unset_form_variables();
	} else {
		display_error(_("This item cannot be deleted because some of it has already been received."));
	}
    line_start_focus();
}

//---------------------------------------------------------------------------------------------------

function handle_cancel_po()
{
	global $path_to_root;

	//need to check that not already dispatched or invoiced by the supplier
	if(($_SESSION['PO']->order_no != 0) &&
		$_SESSION['PO']->any_already_received() == 1)
	{
		display_error(_("This order cannot be cancelled because some of it has already been received.")
			. "<br>" . _("The line item quantities may be modified to quantities more than already received. prices cannot be altered for lines that have already been received and quantities cannot be reduced below the quantity already received."));
		return;
	}

	if($_SESSION['PO']->order_no != 0)
	{
		delete_po($_SESSION['PO']->order_no);
	} else {
		unset($_SESSION['PO']);
		meta_forward($path_to_root.'/index.php','application=AP');
	}

	$_SESSION['PO']->clear_items();
	$_SESSION['PO'] = new purch_order;

	display_notification(_("This purchase order has been cancelled."));

	hyperlink_params($path_to_root . "/purchasing/po_entry_items.php", _("Enter a new purchase order"), "NewOrder=Yes");
	echo "<br>";

	end_page();
	exit;
}

//---------------------------------------------------------------------------------------------------

function check_data()
{
	if(!get_post('stock_id_text', true)) {
		display_error( _("Item description cannot be empty."));
		set_focus('stock_id_edit');
		return false;
	}

	$dec = get_qty_dec($_POST['stock_id']);
	$min = 1 / pow(10, $dec);
    if (!check_num('qty',$min))
    {
    	$min = number_format2($min, $dec);
	   	display_error(_("The quantity of the order item must be numeric and not less than ").$min);
		set_focus('qty');
	   	return false;
    }

    if (!check_num('price', 0))
    {
	   	display_error(_("The price entered must be numeric and not less than zero."));
		set_focus('price');
	   	return false;
    }
    if ($_SESSION['PO']->trans_type == ST_PURCHORDER && !is_date($_POST['req_del_date'])){
    		display_error(_("The date entered is in an invalid format."));
		set_focus('req_del_date');
   		return false;
    }

    return true;
}

//---------------------------------------------------------------------------------------------------

function handle_update_item()
{
	$allow_update = check_data();

	if ($allow_update)
	{
		if ($_SESSION['PO']->line_items[$_POST['line_no']]->qty_inv > input_num('qty') ||
			$_SESSION['PO']->line_items[$_POST['line_no']]->qty_received > input_num('qty'))
		{
			display_error(_("You are attempting to make the quantity ordered a quantity less than has already been invoiced or received.  This is prohibited.") .
				"<br>" . _("The quantity received can only be modified by entering a negative receipt and the quantity invoiced can only be reduced by entering a credit note against this item."));
			set_focus('qty');
			return;
		}
	//TUANVT4
		$_SESSION['PO']->update_order_item(get_post('supplier_tax_id'),$_POST['line_no'], input_num('qty'), input_num('price'),
  			@$_POST['req_del_date'], $_POST['item_description'] );
		unset_form_variables();
	}
    line_start_focus();
}

//---------------------------------------------------------------------------------------------------

function handle_add_new_item()
{
	$allow_update = check_data();

	if ($allow_update == true)
	{
		if (count($_SESSION['PO']->line_items) > 0)
		{
		    foreach ($_SESSION['PO']->line_items as $order_item)
		    {
    			/* do a loop round the items on the order to see that the item
    			is not already on this order */
   			    if (($order_item->stock_id == $_POST['stock_id']))
   			    {
					display_warning(_("The selected item is already on this order."));
			    }
		    } /* end of the foreach loop to look for pre-existing items of the same code */
		}

		if ($allow_update == true)
		{
			$result = get_short_info($_POST['stock_id']);

			if (db_num_rows($result) == 0)
			{
				$allow_update = false;
			}

			if ($allow_update)
			{
				$myrow = db_fetch($result);
				//TUANVT4
				$_SESSION['PO']->add_to_order (get_post('supplier_tax_id'),count($_SESSION['PO']->line_items), $_POST['stock_id'], input_num('qty'),
					get_post('stock_id_text'), //$myrow["description"],
					input_num('price'), '', // $myrow["units"], (retrived in cart)
					$_SESSION['PO']->trans_type == ST_PURCHORDER ? $_POST['req_del_date'] : '', 0, 0);

				unset_form_variables();
				$_POST['stock_id']	= "";
	   		}
	   		else
	   		{
			     display_error(_("The selected item does not exist or it is a kit part and therefore cannot be purchased."));
		   	}

		} /* end of if not already on the order and allow input was true*/
    }
	line_start_focus();
}

//---------------------------------------------------------------------------------------------------

function can_commit(){
	global $Refs, $ci;

	if (!get_post('supplier_id')){
		display_error(_("There is no supplier selected."));
		set_focus('supplier_id');
		return false;
	}

	if (!is_date($_POST['OrderDate']))
	{
		display_error(_("The entered order date is invalid."));
		set_focus('OrderDate');
		return false;
	}

	if ($_SESSION['PO']->trans_type != ST_PURCHORDER && !is_date_in_fiscalyear($_POST['OrderDate']))
	{
		display_error(_("The entered date is not in fiscal year"));
		set_focus('OrderDate');
		return false;
	}

	if (($_SESSION['PO']->trans_type==ST_SUPPINVOICE) && !is_date($_POST['due_date']))
	{
		display_error(_("The entered due date is invalid."));
		set_focus('due_date');
		return false;
	}

	if (!$_SESSION['PO']->order_no) {
    	if (!$Refs->is_valid(get_post('ref')))
    	{
    		display_error(_("There is no reference entered for this purchase order."));
			set_focus('ref');
    		return false;
    	}

    	if (!is_new_reference(get_post('ref'), $_SESSION['PO']->trans_type))
    	{
    		display_error(_("The entered reference is already in use."));
			set_focus('ref');
    		return false;
    	}
	}

	if ($_SESSION['PO']->trans_type == ST_SUPPINVOICE && !$Refs->is_valid(get_post('supp_ref')))
	{
		display_error(_("You must enter a supplier's invoice reference."));
		set_focus('supp_ref');
		return false;
	}
	if ($_SESSION['PO']->trans_type==ST_SUPPINVOICE
		&& is_reference_already_there($_SESSION['PO']->supplier_id, get_post('supp_ref'), $_SESSION['PO']->order_no))
	{
		display_error(_("This invoice number has already been entered. It cannot be entered again.") . " (" . get_post('supp_ref') . ")");
		set_focus('supp_ref');
		return false;
	}
	if ($_SESSION['PO']->trans_type == ST_PURCHORDER && get_post('delivery_address') == '')
	{
		display_error(_("There is no delivery address specified."));
		set_focus('delivery_address');
		return false;
	}
	if (get_post('StkLocation') == '')
	{
		display_error(_("There is no location specified to move any items into."));
		set_focus('StkLocation');
		return false;
	}
	if (!db_has_currency_rates($_SESSION['PO']->curr_code, $_POST['OrderDate']))
		return false;
	if ( method_exists($_SESSION['PO'],'order_has_items') && $_SESSION['PO']->order_has_items() == false){
     	display_error (_("The order cannot be placed because there are no lines entered on this order."));
     	return false;
	}

	if ( isset($_POST['simplified']) && $_POST['simplified'] ==1 ){
	    $max_claimable = $_SESSION['SysPrefs']->prefs['maximum_claimable_input_tax'];
	    $max_claimable_curr = $_SESSION['SysPrefs']->prefs['maximum_claimable_currency'];
	    $ex_rate = get_exchange_rate_from_to($_SESSION['SysPrefs']->prefs['curr_default'], $max_claimable_curr, $_SESSION['supp_trans']->tran_date);
	    if( intval( $ci->input->post('gst_total') ) > $max_claimable/$ex_rate ) {
	        display_error(_("<b>Simplified Invoice:</b> Maximum Claimble Input Tax can't over ".$max_claimable.' '.$max_claimable_curr));
	        return false;
	    }

	}

	return true;
}

//---------------------------------------------------------------------------------------------------

function handle_commit_order(){
	$cart = &$_SESSION['PO'];

	if (can_commit()) {
		copy_to_cart();

		if ($cart->trans_type != ST_PURCHORDER) {
			// for direct grn/invoice set same dates for lines as for whole document
			foreach ($cart->line_items as $line_no =>$line)
				$cart->line_items[$line_no]->req_del_date = $cart->orig_order_date;
		}

		if ($cart->order_no == 0) { // new po/grn/invoice
			/*its a new order to be inserted */
			$ref = $cart->reference;
			if ($cart->trans_type != ST_PURCHORDER) {
				$cart->reference = 'auto';
				begin_transaction();	// all db changes as single transaction for direct document
			}

			$order_no = add_po($cart);
			new_doc_date($cart->orig_order_date);
        	$cart->order_no = $order_no;

			if ($cart->trans_type == ST_PURCHORDER) {
				unset($_SESSION['PO']);
        		meta_forward($_SERVER['PHP_SELF'], "AddedID=$order_no");
        	}
			//Direct GRN
			if ($cart->trans_type == ST_SUPPRECEIVE)
				$cart->reference = $ref;
			if ($cart->trans_type != ST_SUPPINVOICE)
				$cart->Comments = $cart->reference; //grn does not hold supp_ref

			foreach($cart->line_items as $key => $line)
				$cart->line_items[$key]->receive_qty = $line->quantity;
			$grn_no = add_grn($cart);

			if ($cart->trans_type == ST_SUPPRECEIVE) {
				commit_transaction(); // save PO+GRN
				unset($_SESSION['PO']);
        		meta_forward($_SERVER['PHP_SELF'], "AddedGRN=$grn_no");
			}
//			Direct Purchase Invoice
 			$inv = new supp_trans(ST_SUPPINVOICE);
			$inv->Comments = $cart->Comments;
			$inv->supplier_id = $cart->supplier_id;
			$inv->tran_date = $cart->orig_order_date;
			$inv->due_date = $cart->due_date;
			$inv->reference = $ref;
			$inv->supp_reference = $cart->supp_ref;
			$inv->tax_included = $cart->tax_included;
			$inv->permit = $cart->permit;

			$supp = get_supplier($cart->supplier_id);

			$inv->ov_amount = $inv->ov_gst = $inv->ov_discount = 0;

			$total = 0;
			foreach($cart->line_items as $key => $line) {
				$inv->add_grn_to_trans($line->supplier_tax_id,$line->grn_item_id, $line->po_detail_rec, $line->stock_id,
					$line->item_description, $line->receive_qty, 0, $line->receive_qty,
					$line->price, $line->price, true, get_standard_cost($line->stock_id), '');
				$inv->ov_amount += round2(($line->receive_qty * $line->price), user_price_dec());
			}
			$inv->tax_overrides = $cart->tax_overrides;
//TUANVT6
			if (!$inv->tax_included) {
				$taxes = $inv->get_taxes_tran_new();
				foreach( $taxes as $taxitem) {
					$total += isset($taxitem['Override']) ? $taxitem['Override'] : $taxitem['Value'];
				}
			}
			$inv->ex_rate = $cart->ex_rate;
			$inv->fixed_access = $cart->fixed_access;

			$inv_no = add_supp_invoice($inv);

// 			$cart->order_no = 0;
			commit_transaction(); // save PO+GRN+PI
			// FIXME payment for cash terms. (Needs cash account selection)
			unset( $_SESSION['PO'] );
       		meta_forward($_SERVER['PHP_SELF'], "AddedPI=$inv_no");
		} else { // order modification

			$order_no = update_po($cart);
			unset($_SESSION['PO']);
        	meta_forward($_SERVER['PHP_SELF'], "AddedID=$order_no&Updated=1");
		}
	}
}
//---------------------------------------------------------------------------------------------------


$conttrol_ci->form();

end_page();
?>
