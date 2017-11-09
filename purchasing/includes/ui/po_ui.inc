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
include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");

//--------------------------------------------------------------------------------------------------

function copy_from_cart()
{
	$cart = &$_SESSION['PO'];

	$_POST['supplier_id'] = $cart->supplier_id;
	$_POST['OrderDate'] = $cart->orig_order_date;
	if ($cart->trans_type==ST_SUPPINVOICE)
		$_POST['due_date'] = $cart->due_date;
    $_POST['supp_ref'] = $cart->supp_ref;
    $_POST['ref'] = $cart->reference;
	$_POST['Comments'] = $cart->Comments;
    $_POST['StkLocation'] = $cart->Location;
    $_POST['delivery_address'] = $cart->delivery_address;
	$_POST['_ex_rate'] = $cart->ex_rate;
    foreach($cart->tax_overrides as $id => $value)
	    $_POST['mantax'][$id] = price_format($value);
}

function copy_to_cart(){

    global $ci;
	$cart = &$_SESSION['PO'];

	$cart->supplier_id = $_POST['supplier_id'];
	$cart->orig_order_date = $_POST['OrderDate'];
	if ($cart->trans_type==ST_SUPPINVOICE)
		$cart->due_date = $_POST['due_date'];
	$cart->reference = $_POST['ref'];
	$cart->supp_ref = $_POST['supp_ref'];
	$cart->Comments = $_POST['Comments'];
	$cart->Location = $_POST['StkLocation'];
	$cart->fixed_access = $ci->input->post('fixed_access');
	$cart->delivery_address = $_POST['delivery_address'];


	$cart->ex_rate = input_num('_ex_rate', null);

	$cart->permit = $ci->input->post('permit');

    if (isset($_POST['mantax'])) {
		foreach($_POST['mantax'] as $id => $tax) {
			$cart->tax_overrides[$id] = user_numeric($_POST['mantax'][$id]); }
	}
}
// ------------------------------------------------------------------------------
//TUANVT3
function get_supplier_details_to_order(&$order, $supplier_id)
{
	$sql = "SELECT curr_code, supp_name, tax_group_id, supp.tax_included,
			supp.credit_limit - Sum(IFNULL(ov_amount + ov_gst + ov_discount,0)) as cur_credit,
				terms.terms, terms.days_before_due, terms.day_in_following_month,supplier_tax_id
		FROM ".TB_PREF."suppliers supp
			 LEFT JOIN ".TB_PREF."supp_trans trans ON supp.supplier_id = trans.supplier_id
			 LEFT JOIN ".TB_PREF."payment_terms terms ON supp.payment_terms=terms.terms_indicator
		WHERE supp.supplier_id = ".db_escape($supplier_id)."
		GROUP BY
			  supp.supp_name";

	$result = db_query($sql, "The supplier details could not be retreived");

	$myrow = db_fetch($result);
	$order->credit = $myrow["cur_credit"];
	$order->supplier_tax_id = $myrow["supplier_tax_id"];

	//TUANVT4
	if ($order->supplier_tax_id > 0)
	{
		$_POST['supplier_tax_id'] = $order->supplier_tax_id;
	}

	$order->terms = array(
		'description' => $myrow['terms'],
		'days_before_due' => $myrow['days_before_due'],
		'day_in_following_month' => $myrow['day_in_following_month'] );

	$_POST['supplier_id'] = $supplier_id;
	$_POST['supplier_name'] = $myrow["supp_name"];
	$_POST['curr_code'] = $myrow["curr_code"];

	$order->set_supplier($supplier_id, $myrow["supp_name"], $myrow["curr_code"],
		$myrow["tax_group_id"], $myrow["tax_included"]);
}
//--------------------------------------------------------------------------------------
//TuanVT3
//--------------------------------------------------------------------------------------

function get_tax_type_by_item_purchases_tmp($stock_id)
{
	$sql = "SELECT ".TB_PREF."tax_types.*
		FROM ".TB_PREF."stock_master,".TB_PREF."tax_types
		WHERE stock_id=".db_escape($stock_id)
		." AND ".TB_PREF."stock_master.purchase_gst_type=".TB_PREF."tax_types.id";
	$result = db_query($sql, "The standard cost cannot be retrieved");

	return db_fetch($result);
}

function get_item_edit_info_tax_cutormer_purchases_tmp($tax_id)
{
	$sql = "SELECT *
		FROM ".TB_PREF."tax_types
		WHERE id=".db_escape($tax_id);

	$result = db_query($sql, "The standard cost cannot be retrieved");

	return db_fetch($result);
}

//--------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------

function create_new_po($trans_type, $trans_no)
{
	global $Refs;

	if (isset($_SESSION['PO']))
		unset ($_SESSION['PO']->line_items, $_SESSION['PO']);

	$cart = new purch_order;
	$_POST['OrderDate'] = new_doc_date();
	if (!is_date_in_fiscalyear($_POST['OrderDate']))
		$_POST['OrderDate'] = end_fiscalyear();

	$cart->due_date = $cart->orig_order_date = $_POST['OrderDate'];

	$cart->trans_type = $trans_type;
	$cart->order_no = $trans_no;
	/*read in all the selected order into the Items cart  */
	if ($trans_no) {
		read_po($trans_no, $cart);
		$cart->order_no = $trans_no;
	} else
		$cart->reference = $Refs->get_next($trans_type);
	$cart->fixed_access = 0;

	$_SESSION['PO'] = &$cart;
}

//---------------------------------------------------------------------------------------------------

function display_po_header(&$order) {
	global $Ajax, $Refs, $ci;

	$editable = ($order->order_no == 0);

	start_outer_table(TABLESTYLE2, 'width=80%');

	table_section(1);
    if ($editable) {
        if (!isset($_POST['supplier_id']) && (get_global_supplier() != ALL_TEXT))
        	$_POST['supplier_id'] = get_global_supplier();

    	supplier_list_row(_("Supplier:"), 'supplier_id', null, false, true, false, true);
	} else {
		hidden('supplier_id', $order->supplier_id);
		label_row(_("Supplier:"), $order->supplier_name);
    }

	if ($order->supplier_id != get_post('supplier_id',-1)) {
		$old_supp = $order->supplier_id;
		get_supplier_details_to_order($order, $_POST['supplier_id']);
	    get_duedate_from_terms($order);
		$_POST['due_date'] = $order->due_date;

		// supplier default price update
		foreach ($order->line_items as $line_no=>$item) {
			$line = &$order->line_items[$line_no];
			$line->price =  get_purchase_price ($order->supplier_id, $line->stock_id);
			$line->quantity =
				$line->quantity/get_purchase_conversion_factor ($old_supp, $line->stock_id)
					*get_purchase_conversion_factor ($order->supplier_id, $line->stock_id);
		}
	    $Ajax->activate('items_table');
	    $Ajax->activate('due_date');
	}
	set_global_supplier($_POST['supplier_id']);

// 	date_row($order->trans_type==ST_PURCHORDER ? _("Order Date:") : ($order->trans_type==ST_SUPPRECEIVE ? _("Delivery Date:") : _("Invoice Date:")),
// 		'OrderDate', '', true, 0, 0, 0, null, true);

	$OrderDate_title = 'Invoice Date';
	if( $order->trans_type==ST_SUPPRECEIVE ){
	    $OrderDate_title = 'Delivery Date';
	} else if ( $order->trans_type==ST_PURCHORDER ) {
	    $OrderDate_title = 'Order Date';
	}

	echo $ci->finput->qdate('To','OrderDate',null,'row');

	if (isset($_POST['_OrderDate_changed'])) {
		$order->orig_order_date = $_POST['OrderDate'];
	    get_duedate_from_terms($order);
	    $_POST['due_date'] = $order->due_date;
		$Ajax->activate('due_date');
	}
	supplier_credit_row($order->supplier_id, $order->credit);

	if (!is_company_currency($order->curr_code)) {
		label_row(_("Supplier Currency:"), $order->curr_code);
		exchange_rate_display(get_company_currency(), $order->curr_code,
			$_POST['OrderDate']);
	}


    if ($editable) {
    	ref_row(_("Reference:"), 'ref');
    } else {
    	hidden('ref', $order->reference);
    	label_row(_("Reference:"), $order->reference);
    }
    if( defined('COUNTRY') &&  COUNTRY==65 ){
        text_row(_("Permit No:"), 'permit');
    }

	table_section(2);

	if ($order->trans_type==ST_SUPPINVOICE) {
// 	    date_row(_("Due Date:"), 'due_date', '', false, 0, 0, 0, null, true);
	    echo $ci->finput->qdate('Due Date','due_date',null,'row');
	}


  	text_row(_("Supplier's Reference:"), 'supp_ref', null, 16, 15);
	locations_list_row(_("Receive Into:"), 'StkLocation', null, false, true);
	if( defined('COUNTRY') && COUNTRY==60 ){
	    check_row(_("For Fixed Asset:"), 'fixed_access');
	    check_row(_("Simplified invoice:"), 'simplified');
	} else {
	    hidden('fixed_access');
	    hidden('simplified');
	}

	table_section(3);

    if (!isset($_POST['StkLocation']) || $_POST['StkLocation'] == "" ||
    	isset($_POST['_StkLocation_update']) || !isset($_POST['delivery_address']) ||
    	$_POST['delivery_address'] == "")
    {
    	/*If this is the first time the form loaded set up defaults */

        //$_POST['StkLocation'] = $_SESSION['UserStockLocation'];
        $sql = "SELECT delivery_address, phone FROM ".TB_PREF."locations WHERE loc_code=".db_escape($_POST['StkLocation']);
        $result = db_query($sql,"could not get location info");

        if (db_num_rows($result) == 1)
        {
    	  	$loc_row = db_fetch($result);
    	  	$_POST['delivery_address'] = $loc_row["delivery_address"];
			$Ajax->activate('delivery_address');
    	  	$_SESSION['PO']->Location = $_POST['StkLocation'];
    	  	$_SESSION['PO']->delivery_address = $_POST['delivery_address'];

        }
        else
        { /*The default location of the user is crook */
    	  	display_error(_("The default stock location set up for this user is not a currently defined stock location. Your system administrator needs to amend your user record."));
        }
    }

	textarea_row(_("Deliver to:"), 'delivery_address', $_POST['delivery_address'], 35, 4);

	end_outer_table(); // outer table
}

//---------------------------------------------------------------------------------------------------

function display_po_items(&$order, $editable=true){
	global $ci;

    display_heading(_("Order Items"));

    div_start('items_table');
    start_table(TABLESTYLE, "width=80%");

   	$th = array(_("Item Code"), _("Item Description"), _("Tax"), _("Quantity"),
		_("Received"), _("Unit"),
   		_("Required Delivery Date"), $order->tax_included ? _("Price after Tax") : _("Price before Tax"), _("Line Total"), "");
	if ($order->trans_type != ST_PURCHORDER)
		array_remove($th, 6);

	if (count($order->line_items)) $th[] = '';
   	table_header($th);

	$id = find_submit('Edit');
	$total = 0;
	$k = 0;
	$tax_total = array();
   	foreach ($order->line_items as $line_no => $po_line){
    	$line_total =	round($po_line->quantity * $po_line->price,  user_price_dec());
    	if (!$editable || ($id != $line_no)){
    		alt_table_row_color($k);
        	label_cell($po_line->stock_id);
    		label_cell($po_line->item_description);
   //  		//TUANVT3
			// $tax_name ="";
			// $tax_rate ="";
			// if ($order->supplier_tax_id > 0)
			// {
			// 	$tax_type_info = get_item_edit_info_tax_cutormer_purchases_tmp($order->supplier_tax_id);
			// 	$tax_name = $tax_type_info["name"];
			// 	$tax_rate = $tax_type_info["rate"];
			// }else
			// {
			// 	$tax_type_info = get_tax_type_by_item_purchases_tmp($po_line->stock_id);
			// 	$tax_name = $tax_type_info["name"];
			// 	$tax_rate = $tax_type_info["rate"];
			// }
   //  		label_cell($tax_name."(".$tax_rate."%)");
    		//TUANVT4
// 			$tax_type_info = get_item_edit_info_tax_cutormer_purchases_tmp($po_line->supplier_tax_id);
			$tax =$ci->api_membership->get_data('taxdetail/'.$po_line->supplier_tax_id);
			$tax_line = tax_calculator($po_line->supplier_tax_id,$line_total,$order->tax_included,$tax);
			if( !isset($tax_total[$tax_line->id]) ){
				$tax_total[$tax_line->id] = array('name'=>$tax_line->name,'value'=>$tax_line->value,'rate'=>$tax_line->rate);
			} else {
				$tax_total[$tax_line->id]['value'] += $tax_line->value;
			}
//
// 			$tax_name = $tax->name;
// 			$tax_name_new = strstr($tax_name,"(");
// 			$tax_name = str_replace(array("(",")"),"",$tax_name_new);
// 			$tax_rate = $tax->rate;
			label_cell($tax->no."(".$tax->rate."%)");

            qty_cell($po_line->quantity, false, get_qty_dec($po_line->stock_id));
            qty_cell($po_line->qty_received, false, get_qty_dec($po_line->stock_id));
    		label_cell($po_line->units);

			if ($order->trans_type == ST_PURCHORDER)
        	    label_cell($po_line->req_del_date);

    		amount_decimal_cell($po_line->price);
//     		label_cell(number_format2($tax_line->value,user_amount_dec()), "nowrap align=right ",'line_total');
           // amount_cell($line_total);
            label_cell(number_format2($line_total,user_amount_dec()), "nowrap align=right ",'line_total');
			if ($editable)
			{
					edit_button_cell("Edit$line_no", _("Edit"),
					  _('Edit document line'));
					delete_button_cell("Delete$line_no", _("Delete"),
						_('Remove line from document'));
			}
		end_row();
		} else {
			po_item_controls($order, $k, $line_no);
		}
		$total += $line_total;
    }

	if ($id==-1 && $editable)
		po_item_controls($order, $k);

	$colspan = count($th)-2;
	if (count($order->line_items))
		$colspan--;

// 	$display_sub_total = price_format($total);
	$display_sub_total = number_format2($total,user_amount_dec());
	label_row(_("Sub-total"), $display_sub_total, "colspan=$colspan align=right","align=right", 2);

	//TUANVT3

// 	$taxes = $order->get_taxes_new($order->supplier_tax_id,input_num('freight_cost'));

	$tax_total_amount = 0;
	if( isset($tax_total) && count($tax_total) > 0 ){ foreach ($tax_total AS $tax){
		if( $order->tax_included ){
			label_row($tax['name'].' ('.$tax['rate'].'%) '.$tax['value'], null, "colspan=$colspan align=right", "align=right", 2);
		} else {
			label_row($tax['name'].' ('.$tax['rate'].'%) ',	$tax['value'], "colspan=$colspan align=right", "align=right", 2);
		}
		$tax_total_amount += 	$tax['value'];
	}}
// 	$tax_total = display_edit_tax_items_new(null, $colspan, $order->tax_included, 2);
    hidden('gst_total',$tax_total_amount);
// 	$display_total = price_format(($total + input_num('freight_cost') + $tax_total));
	$display_total = number_format2($total + input_num('freight_cost') + $tax_total_amount,user_amount_dec());


	start_row();
	label_cells(_("Amount Total"), $display_total, "colspan=$colspan align='right'","align='right'");
	$order->order_no ? submit_cells('update', _("Update"), "colspan=2 align='center'", _("Refresh"), true)
		: label_cell('', "colspan=2");
	end_row();

	end_table(1);
	div_end();
}

//---------------------------------------------------------------------------------------------------

function display_po_summary(&$po, $is_self=false, $editable=false)
{
    start_table(TABLESTYLE, "width=90%");

    start_row();
    label_cells(_("Reference"), $po->reference, "class='tableheader2'");

    label_cells(_("Supplier"), $po->supplier_name, "class='tableheader2'");

    if (!is_company_currency($po->curr_code))
    	label_cells(_("Order Currency"), $po->curr_code, "class='tableheader2'");

    if (!$is_self)
    {
    	label_cells(_("Purchase Order"), get_trans_view_str(ST_PURCHORDER, $po->order_no),
    		"class='tableheader2'");
    }
	end_row();
	start_row();
    label_cells(_("Date"), $po->orig_order_date, "class='tableheader2'");

    if ($editable)
    {
        if (!isset($_POST['Location']))
        	$_POST['Location'] = $po->Location;
        label_cell(_("Deliver Into Location"), "class='tableheader2'");
        locations_list_cells(null, 'Location', $_POST['Location']);
    }
    else
    {
    	label_cells(_("Deliver Into Location"), get_location_name($po->Location),
    		"class='tableheader2'");
    }

    if ($po->supp_ref != "")
    	label_cells(_("Supplier's Reference"), $po->supp_ref, "class='tableheader2'");
    end_row();

    if (!$editable)
    	label_row(_("Delivery Address"), $po->delivery_address, "class='tableheader2'",
    		"colspan=9");

    if ($po->Comments != "")
    	label_row(_("Order Comments"), $po->Comments, "class='tableheader2'",
    		"colspan=9");
    end_table(1);
}

//--------------------------------------------------------------------------------

function po_item_controls(&$order, &$rowcounter, $line_no=-1){
   global $Ajax, $SysPrefs,$ci;

	alt_table_row_color($rowcounter);

	$dec2 = 0;
	$id = find_submit('Edit');
	if (($id != -1) && $line_no == $id) {
		$_POST['stock_id'] = $order->line_items[$id]->stock_id;
		$dec = get_qty_dec($_POST['stock_id']);
		$_POST['qty'] = qty_format($order->line_items[$id]->quantity, $_POST['stock_id'], $dec);
		//$_POST['price'] = price_format($order->line_items[$id]->price);
		$_POST['price'] = price_decimal_format($order->line_items[$id]->price, $dec2);
		if ($order->trans_type == ST_PURCHORDER)
			$_POST['req_del_date'] = $order->line_items[$id]->req_del_date;

		$_POST['units'] = $order->line_items[$id]->units;
		$_POST['item_description'] = $order->line_items[$id]->item_description;

		hidden('stock_id', $_POST['stock_id']);
		label_cell($_POST['stock_id']);

		if ($order->line_items[$id]->descr_editable)
			text_cells(null,'item_description', null, 45, 150);
		else {
			hidden('item_description', $_POST['item_description']);
//			label_cell($_POST['item_description']);
			label_cell($order->line_items[$id]->item_description);
		}

	    $Ajax->activate('items_table');
		$qty_rcvd = $order->line_items[$id]->qty_received;
	} else {
//		hidden('line_no', ($_SESSION['PO']->lines_on_order + 1));
		//Chaitanya : Manufcatured item can be purchased
		stock_items_list_cells(null, 'stock_id', null, false, true, false, true);
		//stock_purchasable_items_list_cells(null, 'stock_id', null, false, true, true);
		if (list_updated('stock_id')) {
			    $Ajax->activate('price');
			    $Ajax->activate('units');
			    $Ajax->activate('qty');
			    $Ajax->activate('req_del_date');
			    $Ajax->activate('line_total');
			    $Ajax->activate('supplier_tax_id');
		}
		//TUANVT6
		if ($order->supplier_tax_id < 0) {
			$tax_type_info = get_tax_type_by_item_purchases_tmp(get_post('stock_id'));
			$tax_id_tmp = $tax_type_info["id"];
			//display_error($tax_id_tmp);
			$_POST['supplier_tax_id'] = $tax_id_tmp;
		}
    	$item_info = get_item_edit_info($_POST['stock_id']);
		$_POST['units'] = $item_info["units"];

   		$dec = $item_info["decimals"];
   		$_POST['qty'] =	number_format2(get_purchase_conversion_factor ($order->supplier_id, $_POST['stock_id']), $dec);
		//$_POST['price'] = price_format(get_purchase_price ($order->supplier_id, $_POST['stock_id']));
		$_POST['price'] = price_decimal_format(get_purchase_price ($order->supplier_id, $_POST['stock_id']), $dec2);
		if ($order->trans_type == ST_PURCHORDER)
			$_POST['req_del_date'] = add_days(Today(), $SysPrefs->default_delivery_required_by());
		$qty_rcvd = '';
	}
	//TUANVT4
	if( !isset($new_item) ){
		$new_item = false;
	}
// 	stock_invoice_list_row(null, 'supplier_tax_id', null, false, $new_item,'1,3');
	echo $ci->finput->inputtaxes(null,'supplier_tax_id',$_POST['supplier_tax_id'],'3','column');

	qty_cells(null, 'qty', null, null, null, $dec);
	qty_cell($qty_rcvd, false, $dec);

	label_cell($_POST['units'], '', 'units');
	if ($order->trans_type == ST_PURCHORDER)
		date_cells(null, 'req_del_date', '', null, 0, 0, 0);
	if ($qty_rcvd > 0)
	{
   		amount_decimal_cell($_POST['price']);
   		hidden('price', $_POST['price']);
   	}
   	else {
   		amount_cells(null, 'price', null, null, null, $dec2);
   	}


	//$line_total = $_POST['qty'] * $_POST['price'] * (1 - $_POST['Disc'] / 100);
	$line_total = round(input_num('qty') * input_num('price'),  user_price_dec());
	//amount_cell($line_total, false, '','line_total');
	label_cell(number_format2($line_total,user_amount_dec()), "nowrap align=right ",'line_total');
	if ($id!=-1)
	{
		button_cell('UpdateLine', _("Update"),
				_('Confirm changes'), ICON_UPDATE);
		button_cell('CancelUpdate', _("Cancel"),
				_('Cancel changes'), ICON_CANCEL);
		hidden('line_no', $line_no);
		set_focus('qty');
	}
	else
	{
		submit_cells('EnterLine', _("Add Item"), "colspan=2 align='center'",
		    _('Add new item to document'), true);
	}

	end_row();
}

//---------------------------------------------------------------------------------------------------



?>