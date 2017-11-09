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
function display_bank_header(&$order) {
	global $Ajax, $Refs,$ci;
	$payment = $order->trans_type == ST_BANKPAYMENT;

	$customer_error = false;
	div_start('pmt_header');

	start_outer_table(TABLESTYLE2, "width=90%"); // outer table

	table_section(1);

	date_row(_("Date:"), 'date_', '', true, 0, 0, 0, null, true);
	ref_row(_("Reference:"), 'ref', '');

// 	if (!isset($_POST['tax_inclusive'])){
// 		$_POST['tax_inclusive'] = 0;
// 	}
	$order->tax_inclusive = $ci->input->post('tax_inclusive');
	echo $ci->finput->tax_inclusive(_('Tax Inclusive'),'tax_inclusive',$order->tax_inclusive,'row');
	$order->cheque = $ci->input->post('cheque');
	echo $ci->finput->text(_('Cheque Number'),'cheque',$order->cheque,'row');


	table_section(2, "35%");

	if (!isset($_POST['PayType'])){
		if (isset($_GET['PayType']))
			$_POST['PayType'] = $_GET['PayType'];
		else
			$_POST['PayType'] = "";
	}

	if (!isset($_POST['person_id'])){
		if (isset($_GET['PayPerson']))
			$_POST['person_id'] = $_GET['PayPerson'];
		else
			$_POST['person_id'] = "";
	}

	if( $ci->input->post('_tax_inclusive_update') ){
		$Ajax->activate('pmt_header');
		$Ajax->activate('items_table');
	}

	if (isset($_POST['_PayType_update']) ) {
		$_POST['person_id'] = '';

		$Ajax->activate('pmt_header');
		$Ajax->activate('code_id');
		$Ajax->activate('pagehelp');
		$Ajax->activate('editors');
		$Ajax->activate('footer');
	}

	payment_person_types_list_row( $payment ? _("Pay To:"):_("From:"), 'PayType', $_POST['PayType'], true);

	switch ($_POST['PayType']) {
		case PT_MISC :
			text_row_ex($payment ?_("To the Order of:"):_("Name:"),
			'person_id', 40, 50);
			break;
			//case PT_WORKORDER :
			//	workorders_list_row(_("Work Order:"), 'person_id', null);
			//	break;
		case PT_SUPPLIER :
			supplier_list_row(_("Supplier:"), 'person_id', null, false, true, false, true);
			break;
		case PT_CUSTOMER :
			customer_list_row(_("Customer:"), 'person_id', null, false, true, false, true);

			if (db_customer_has_branches($_POST['person_id']))
			{
				customer_branches_list_row(_("Branch:"), $_POST['person_id'],
						'PersonDetailID', null, false, true, true, true);
			}
			else
			{
				$_POST['PersonDetailID'] = ANY_NUMERIC;
				hidden('PersonDetailID');
			}
			$trans = get_customer_habit($_POST['person_id']); // take care of customers on hold
			if ($trans['dissallow_invoices'] != 0)
			{
				if ($payment)
				{
					$customer_error = true;
					display_error(_("This customer account is on hold."));
				}
				else
					display_warning(_("This customer account is on hold."));
			}
			break;

		case PT_QUICKENTRY :
			quick_entries_list_row(_("Type").":", 'person_id', null, ($payment ? QE_PAYMENT : QE_DEPOSIT), true);
			$qid = get_quick_entry(get_post('person_id'));
			if (list_updated('person_id')) {
				unset($_POST['totamount']); // enable default
				$Ajax->activate('footer');
				$Ajax->activate('totamount');
			}
			amount_row($qid['base_desc'].":", 'totamount', price_format($qid['base_amount']),
				 null, "&nbsp;&nbsp;".submit('go', _("Go"), false, false, true));
			break;
			//case payment_person_types::Project() :
			//	dimensions_list_row(_("Dimension:"), 'person_id', $_POST['person_id'], false, null, true);
			//	break;
	}
    if( !isset($order->goods_invoice) ){
        $order->goods_invoice = false;
    }
    if( defined('COUNTRY') && COUNTRY== 60 ){
        echo $ci->finput->supplier_invoice_goods(_('Load Import'),'goods_invoice',$order->goods_invoice,'row');
    } else {
        hidden('goods_invoice');
    }


	if( defined('COUNTRY') && COUNTRY==65 ){
	    $ci->finput->text('Source Ref','source_ref',null,'row_echo');
	}
	table_section(3, "30%");

	if (!$order->order_id && !get_post('bank_account')) {
		if ($_POST['PayType'] == PT_CUSTOMER)
			$_POST['bank_account'] = get_default_customer_bank_account($_POST['person_id']);
		elseif ($_POST['PayType'] == PT_SUPPLIER)
		$_POST['bank_account'] = get_default_supplier_bank_account($_POST['person_id']);
		else
			unset($_POST['bank_account']);
	}

	bank_accounts_list_row( $payment ? _("From:") : _("Into:"), 'bank_account', null, true);
	if ($payment)
		bank_balance_row($_POST['bank_account']);

	$bank_currency = get_bank_account_currency($_POST['bank_account']);

	exchange_rate_display(get_company_currency(), $bank_currency, input_post('date_') );

	end_outer_table(1); // outer table

	div_end();
	if ($customer_error){
		end_form();
		end_page();
		exit;
	}
}
//---------------------------------------------------------------------------------

function display_gl_items($title, &$order){
	global $path_to_root , $ci;


	$dim = get_company_pref('use_dimension');
	$colspan = ($dim == 2 ? 4 : ($dim == 1 ? 3 : 2));
	if( $order->trans_type==ST_BANKPAYMENT && defined('COUNTRY') && COUNTRY== 60 ){
	    echo '<center><a title="Add new item to document" class="inputsubmit leftbutton" data-toggle="modal" data-target="#bankpayment_simplified_invoice" >SIMPLIFIED INVOICE</a> <span class="headingtext">'.$title.'</span></center>';
	} else {
	    display_heading($title);
	}


	div_start('items_table');
	start_table(TABLESTYLE, "colspan=7 width=95%");

	if ($dim == 2)
		$th = array(_("Account Code"), _("Account Description"), _("Dimension")." 1",
				_("Dimension")." 2", _("Amount"), _("Memo"), "");
	else if ($dim == 1)
		$th = array(_("Account Code"), _("Account Description"), _("Dimension"),
				_("Amount"), _("Memo"), "");
	else {
	    if( isset($order->invoice_import) && !empty($order->invoice_import) ){
	        $th = array(_("Account Code"), _("Account Description"),
	            _("Amount"),_('GST'), _("Memo"),'Import Declaration Number' ,"");
	        $colspan +=1;
	    } else {
	        $th = array(_("Account Code"), _("Account Description"),
	            _("Amount"),_('GST'), _("Memo"), "");
	    }
	}


	if (count($order->gl_items)) $th[] = '';

	table_header($th);
	$k = 0;  //row colour counter

	$id = find_submit('Edit');

	foreach ($order->gl_items as $line => $item){

		if ($id != $line) {
			alt_table_row_color($k);

			label_cell($item->code_id);
			$gl_acc = get_gl_account($item->code_id);
			label_cell( isset($gl_acc['account_name']) ? $gl_acc['account_name'] : $item->description );
			if ($dim >= 1)
				label_cell(get_dimension_string($item->dimension_id, true));
			if ($dim > 1)
				label_cell(get_dimension_string($item->dimension2_id, true));
			//amount_cell(abs($item->amount));
			if ($order->trans_type == ST_BANKDEPOSIT)
				amount_cell(-$item->amount);
			else
				amount_cell($item->amount);

			$tax_title = '';
			if( $item->gst ){
				$tax_title = get_gst_string($item->gst);
			}
			label_cell($tax_title);
			label_cell($item->description);
			if( isset($order->invoice_import) && !empty($order->invoice_import) ){
			    text_cells(null,'import_declaration_number');
			}
			edit_button_cell("Edit$line", _("Edit"),
					_('Edit document line'));
			delete_button_cell("Delete$line", _("Delete"),
					_('Remove line from document'));
			end_row();
		} else {
			gl_edit_item_controls($order, $dim, $line);
		}
	}

	if ($id == -1)
		gl_edit_item_controls($order, $dim);

	if ($order->count_gl_items()){
// 		if( !class_exists('tax_model')){
// 			include_once(ROOT. "/taxes/tax_model.php");
// 		}
// 		$tax_model = $ci->model('tax',true);
		$tax_total =0;
		foreach ($order->gl_items AS $ite){
			if( $ite->gst ){
// 				$tax_detail = $tax_model->item_type_code($ite->gst)
				$tax_detail = $ci->api_membership->get_data('taxdetail/'.$ite->gst);
				if( $order->tax_inclusive) {
					$tax_total += $tax_detail->rate/(100+$tax_detail->rate)*$ite->amount;
				} else {
					$tax_total += $ite->amount*$tax_detail->rate/100;
				}

			}

		}
		echo "<tr>";

		$total_price =  number_format2(abs($order->gl_items_total()), user_price_dec());
		label_cells( _("Sum"), $total_price, "colspan=" . $colspan . " align=right",  "align=right", 4 );

		echo "<td align=right >".number_format2(  abs($tax_total),user_price_dec())."</td>";
		echo "<td colspan=3></td>";
		echo "</tr>";

		$total =( $order->gl_items_total() );


		if( $order->tax_inclusive != 1 ) {
			$total += $tax_total;
		}

		echo '<tr><td align="right" colspan="'.$colspan.'">Total </td><td align="right" colspan="2">'.number_format2( abs($total), user_price_dec()).'</td><td align="right" colspan="3"></td></tr>';

// 		label_row(_("Total"), number_format2(abs($order->gl_items_total()), user_price_dec()),"colspan=" . $colspan . " align=right", "align=right",4);

	}


	end_table();
	div_end();
}

//---------------------------------------------------------------------------------

function gl_edit_item_controls(&$order, $dim, $Index=null){
	global $Ajax,$ci;
	$payment = $order->trans_type == ST_BANKPAYMENT;

	start_row();
	$id = find_submit('Edit');
	if ($Index != -1 && $Index == $id){
		$item = $order->gl_items[$Index];
// 		bug($item);die;
		$_POST['code_id'] = $item->code_id;
		$_POST['dimension_id'] = $item->dimension_id;
		$_POST['dimension2_id'] = $item->dimension2_id;
		$_POST['amount'] = price_format(abs($item->amount));
		$_POST['description'] = $item->description;
		$_POST['LineMemo'] = $item->description;
		$_POST['gst'] = $item->gst;

		hidden('Index', $id);
		echo gl_all_accounts_list('code_id', null, true, true);
		if ($dim >= 1)
			dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
		if ($dim > 1)
			dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);
		$Ajax->activate('items_table');
	} elseif( isset($order->invoice_import) && !empty($order->invoice_import) ){
	    return;
	} else {
		$_POST['amount'] = price_format(0);
		$_POST['dimension_id'] = 0;
		$_POST['dimension2_id'] = 0;
		//$_POST['LineMemo'] = ""; // let memo go to next line Joe Hunt 2010-05-30
		if(isset($_POST['_code_id_update'])) {
			$Ajax->activate('code_id');
		}


		if ($_POST['PayType'] == PT_CUSTOMER){
			$acc = get_branch_accounts($_POST['PersonDetailID']);
			$_POST['code_id'] = $acc['receivables_account'];

		}
		elseif ($_POST['PayType'] == PT_SUPPLIER)
		{
			$acc = get_supplier_accounts($_POST['person_id']);
			$_POST['code_id'] = $acc['payable_account'];
			$gst_usefor = 3;
		}
		//elseif ($_POST['PayType'] == PT_WORKORDER)
		//	$_POST['code_id'] = get_company_pref('default_assembly_act');
		else {
			$_POST['code_id'] =
			get_company_pref($payment ? 'default_cogs_act':'default_inv_sales_act');
		}
		echo gl_all_accounts_list('code_id', null, true, true);
		if ($dim >= 1)
			dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
		if ($dim > 1)
			dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);
	}
	if ($dim < 1)
		hidden('dimension_id', 0);
	if ($dim < 2)
		hidden('dimension2_id', 0);

	amount_cells(null, 'amount');
	$gst_usefor = 1;
	if( isset($_SESSION['pay_items']->trans_type) ){
        if( $_SESSION['pay_items']->trans_type==ST_BANKPAYMENT){
            $gst_usefor = 3;
        }else if ($_SESSION['pay_items']->trans_type==ST_BANKDEPOSIT){
            $gst_usefor = 2;
        }
	}
// 	echo '<td>'.gst_type_list('gst',null,false,false,$gst_usefor).'</td>';
    $gst_post = $ci->input->post('gst');
	echo $ci->finput->inputtaxes(null,'gst',$gst_post,$gst_usefor,'column');
	text_cells_ex(null, 'LineMemo', 35, 255);

	if ($id != -1)
	{
		button_cell('UpdateItem', _("Update"),
				_('Confirm changes'), ICON_UPDATE);
		button_cell('CancelItemChanges', _("Cancel"),
				_('Cancel changes'), ICON_CANCEL);
		set_focus('amount');
	}
	else
	{
		submit_cells('AddItem', _("Add Item"), "colspan=2",
				_('Add new item to document'), true);
	}

	end_row();
}


//---------------------------------------------------------------------------------

function gl_options_controls($order) {
	div_start('footer');
	echo "<table align='center' style='margin-top: 10px;' >";

	if( defined('COUNTRY') && COUNTRY== 60 ){
	    echo "<tr>";
	    text_cells(_('Custom Duty'),'custom_duty',null,null,null,null,' width="20%" ');
	    text_cells(_('Custom Assessed Value'),'custom_assessed_value',null,null,null,null,' width="20%" ');
	    echo "</tr>";
	} else {
	    hidden('custom_duty');
	    hidden('custom_assessed_value');
	}


	$type = get_post('PayType');
	$bank_curr = get_bank_account_currency(get_post('bank_account'));

	$person_curr = $type == PT_CUSTOMER ? get_customer_currency(get_post('person_id')) : ($type == PT_SUPPLIER ? get_supplier_currency(get_post('person_id')) : $bank_curr);

	if ($person_curr != $bank_curr){
		$_POST['settled_amount'] = price_format(abs($order->gl_items_total() / get_exchange_rate_from_to($bank_curr, $person_curr, get_post('date_'))));

		amount_row($type == PT_CUSTOMER ? _("Settled AR Amount:") :  _("Settled AP Amount:"), 'settled_amount', null, null, $person_curr, user_price_dec());
	}
// 	textarea_row(_("GST"), 'gst_', null, 20, 3);
// 	textarea_row(_("Memo"), 'memo_', null, 50, 3,null,'colspan="3"');
	textarea_cells(_("Memo"), 'memo_', null, 50, 3,null,null,'colspan="3"');

	echo "</table>";
	div_end();
}


//---------------------------------------------------------------------------------

?>