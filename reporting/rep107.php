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
$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?  'SA_SALESTRANSVIEW' : 'SA_SALESBULKREP';

$path_to_root="..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");


/*
include_once($path_to_root . "/reporting/at_print.php");
$pdf = new at_print();

$pdf->setval('PARAM_0','from');
$pdf->setval('PARAM_1','to');
$pdf->setval('PARAM_2','currency');
$pdf->setval('PARAM_3','email');
$pdf->setval('PARAM_4','pay_service');
$pdf->setval('PARAM_5','comments');
$pdf->setval('PARAM_6','customer');
$pdf->setval('PARAM_7','orientation');
$pdf->setval('PARAM_10','reference');
;

$pdf->setdate('PARAM_8','start_date');
$pdf->setdate('PARAM_9','end_date');

if( array_key_exists("PARAM_10",$_POST) && !$pdf->reference ){
	display_error(_("You must enter Invoice Number."));
	return;
}
*/

//----------------------------------------------------------------------------------------------------

print_invoices();



//----------------------------------------------------------------------------------------------------

function print_invoices() {
	global $path_to_root, $alternative_tax_include_on_docs, $suppress_tax_rates, $no_zero_lines_amount, $company;

	include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$from = input_val('PARAM_0');
	$to = input_val('PARAM_1');
	$currency = input_val('PARAM_2');
	$email = input_val('PARAM_3');
	$pay_service = input_val('PARAM_4');
	$comments = input_val('PARAM_5');
	$customer = input_val('PARAM_6');
	$orientation = input_val('PARAM_7');


	$start_date =input_val('PARAM_8');
	if( !is_date($start_date) ){
		$start_date = null;
	} else {
		$start_date = date('Y-m-d',strtotime($start_date));
	}

	$end_date = input_val('PARAM_9');

	if( !is_date($end_date) ){
		$end_date = null;
	} else {
		$end_date = date('Y-m-d',strtotime($end_date));
	}

	$reference = input_val('PARAM_10');

// 	if( isset($_POST['PARAM_10']) && !$reference ){
// 		display_error(_("You must enter Invoice Number."));
// 		return;
// 	}


	if (!$from || !$to) return;

	$orientation = ($orientation ? 'L' : 'P');
	$dec = user_price_dec();

	$fno = explode("-", $from);
	$tno = explode("-", $to);
	$from = min($fno[0], $tno[0]);
	$to = max($fno[0], $tno[0]);

	$cols = array(4, 60, 225, 300, 325, 385, 450, 515);

	// $headers in doctext.inc
	$aligns = array('left',	'left',	'right', 'left', 'right', 'right', 'right');

	$params = array('comments' => $comments);

	$cur = get_company_Pref('curr_default');

	if ($email == 0)
		$rep = new FrontReport(_('INVOICE'), "InvoiceBulk", user_pagesize(), 9, $orientation);
	if ($orientation == 'L')
		recalculate_cols($cols);

	for ($i = $from; $i <= $to; $i++) {
		if (!exists_customer_trans(ST_SALESINVOICE, $i)) continue;
		$sign = 1;

		$myrow = get_customer_trans($i, ST_SALESINVOICE,$start_date,$end_date,$reference);

		if( !isset($myrow['debtor_no']) || ( $customer && $myrow['debtor_no'] != $customer) ) {
			continue;
		}

		$baccount = get_default_bank_account($myrow['curr_code']);

		$params['bankaccount'] = $baccount['id'];

		$branch = get_branch($myrow["branch_code"]);

		$sales_order = get_sales_order_header($myrow["order_"], ST_SALESORDER);
		if ($email == 1) {
			$rep = new FrontReport("", "", user_pagesize(), 9, $orientation);
			$rep->title = _('INVOICE');
			$rep->filename = "Invoice" . $myrow['reference'] . ".pdf";
		}
		$rep->SetHeaderType('Header2');
		$rep->currency = $cur;
		$rep->Font();
		// 		$rep->header = array(_("Item Code"), _("Item Description"), _("Quantity"),	_("Unit"), null,_("Discount %"), _("Total"));

		// 		$headers = array(_('Item Code 1'), _('Item Description'),	_('Quantity Unit'), _('Unit Price'),'','');

		$rep->Info($params, $cols, null , $aligns);


		$contacts = get_branch_contacts($branch['branch_code'], 'invoice', $branch['debtor_no'], true);



		$baccount['payment_service'] = $pay_service;

// bug($branch);die;

// 		bug($contacts);die;

		$rep->SetCommonData($myrow, $branch, $sales_order, $baccount, ST_SALESINVOICE, $contacts);

		$rep->customer = get_customer($branch['debtor_no']);


		$rep->NewPage();
		// 		bug($rep->headers);die('quannh');


		$SubTotal = 0;
		///////////////////////////////////////////////////////////////////////////////
		$tax_items1 = get_trans_tax_details(ST_SALESINVOICE, $i);
		$tax1 = array();
		while ($tax_item1 = db_fetch($tax_items1)) {
			$tax1[] = $tax_item1['tax_type_id'];
		}

		for ($i1 = 0; $i1 < count($tax1); $i1++){
			$check = 1;
			for ($j1 = $i1-1; $j1 >=0; $j1--) {
				if ($tax1[$i1]==$tax1[$j1]) {
					$check = 0;
				}
			}
			if ($check==1) {
				$countTax = $countTax + 1;
			}
		}
		$countTax = $countTax - 2;
		///////////////////////////////////////////////////////////////////////////////
		$result = get_customer_trans_details(ST_SALESINVOICE, $i);

		while ($myrow2=db_fetch($result)){
			if ($myrow2["quantity"] == 0) continue;

			$Net = round2($sign * ((1 - $myrow2["discount_percent"]) * $myrow2["unit_price"] * $myrow2["quantity"]),
					user_price_dec());
			$SubTotal += $Net;
			$DisplayPrice = number_format2($myrow2["unit_price"],$dec);
			$DisplayQty = number_format2($sign*$myrow2["quantity"],get_qty_dec($myrow2['stock_id']));
			$DisplayNet = number_format2($Net,$dec);
			if ($myrow2["discount_percent"]==0)
				$DisplayDiscount ="";
			else
				$DisplayDiscount = number_format2($myrow2["discount_percent"]*100,user_percent_dec()) . "%";

			$rep->TextCol(0, 1,	$myrow2['stock_id'], -2);
			$oldrow = $rep->row;
			if ($myrow2['long_description'] != ""){
				$rep->TextColLines(1, 2, $myrow2['long_description'], -2);
			}else
			{
				$rep->TextColLines(1, 2, $myrow2['StockDescription'], -2);
			}
			$newrow = $rep->row;
			$rep->row = $oldrow;
			if ($Net != 0.0 || !is_service($myrow2['mb_flag']) || !isset($no_zero_lines_amount) || $no_zero_lines_amount == 0){
				$rep->TextCol(2, 3,	$DisplayQty, -2);
				$rep->TextCol(3, 4,	$myrow2['units'], -2);
				$rep->TextCol(4, 5,	$DisplayPrice, -2);
				$rep->TextCol(5, 6,	$DisplayDiscount, -2);
				$rep->TextCol(6, 7,	$DisplayNet);
				// 				$rep->TextCol(5, 6,	'abc', -2);
			}
			$rep->row = $newrow;
			//$rep->NewLine(1);
			if ($rep->row < $rep->bottomMargin + ((15 + $countTax) * $rep->lineHeight))
				$rep->NewPage();
		}

		$memo = get_comments_string(ST_SALESINVOICE, $i);
		if ($memo != "") {
			$rep->NewLine();
			$rep->TextColLines(1, 5, $memo, -2);
		}

		$DisplaySubTot = number_format2($SubTotal,$dec);
		$DisplayFreight = number_format2($sign*$myrow["ov_freight"],$dec);

		$rep->row = $rep->bottomMargin + ((15 + $countTax) * $rep->lineHeight);
		$doctype = ST_SALESINVOICE;

		$rep->TextCol(3, 6, _("Sub-total"), -2);
		$rep->TextCol(6, 7,	$DisplaySubTot, -2);
		$rep->NewLine();
		$rep->TextCol(3, 6, _("Shipping"), -2);
		$rep->TextCol(6, 7,	$DisplayFreight, -2);
		$rep->NewLine();
		$tax_items = get_trans_tax_details(ST_SALESINVOICE, $i);
		$first = true;
		$taxTotal = 0;
		$taxAmount = 0;
		if( isset($company['gst_no']) &&  trim($company['gst_no']) != '' ){
			while ($tax_item = db_fetch($tax_items)){

				$DisplayTax = number_format2($sign*$tax_item['amount'], $dec);
				$taxAmount+=$tax_item['amount'];
				$tax_type_name = $tax_item['tax_type_name'];
				$tax_type_name = strstr($tax_type_name,"(");
				if ( floatval($DisplayTax) > 0 ){
					if ($tax_item['included_in_price'] ){
						if (isset($alternative_tax_include_on_docs) && $alternative_tax_include_on_docs == 1){
							if ($first){
								$rep->TextCol(3, 6, _("Total Tax Excluded"), -2);
								$rep->TextCol(6, 7,	number_format2($sign*$tax_item['net_amount'], $dec), -2);
								$rep->NewLine();
							}
							$rep->TextCol(3, 6, $tax_type_name, -2);
							$rep->TextCol(6, 7,	$DisplayTax, -2);
							$first = false;
						} else
							$rep->TextCol(3, 6, _("Included Tax") . " " . $tax_type_name . " " . _("Amount"), -2);

						$rep->TextCol(6, 7,	$DisplayTax, -2);
					} else {
						$rep->TextCol(3, 6, _("Tax") . " " . $tax_type_name . " " . _("Amount"), -2);
						$rep->TextCol(6, 7,	$DisplayTax, -2);
					}
					$rep->NewLine();
				}

				$taxTotal++;
			}
		}

		// 		bug($taxTotal);
		if( isset($company['gst_no']) &&  trim($company['gst_no']) != '' && $taxTotal > 1 ){
			$rep->Font('bold');
			$rep->TextCol(3, 6, _("Tax Total"), -2);
			$rep->TextCol(6, 7,	number_format2($sign*$taxAmount, $dec), -2);
			$rep->NewLine();
		}


		$rep->NewLine();
		$DisplayTotal = number_format2($sign*($myrow["ov_freight"] + $myrow["ov_gst"] +
				$myrow["ov_amount"]+$myrow["ov_freight_tax"]),$dec);
		$rep->Font('bold');

// 		$rep->TextCol(3, 6, $myrow['rate'], - 2);
		$rep->TextCol(3, 6, _("TOTAL INVOICE"), - 2);
		$rep->TextCol(6, 7, $DisplayTotal, -2);

		$words = price_in_words($DisplayTotal, ST_SALESINVOICE);
		if ($words != "")
		{
			$rep->NewLine(1);
			$rep->TextCol( 4, 6, $myrow['curr_code'] . ": " . $words, - 2);
		}
		$rep->Font();
		if ($email == 1) {
			$rep->End($email);
		}
	}



	if ($email == 0){
		$rep->End();
	}

}

?>
