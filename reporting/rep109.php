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
$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?
'SA_SALESTRANSVIEW' : 'SA_SALESBULKREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Print Sales Orders
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");

//----------------------------------------------------------------------------------------------------

print_sales_orders();

$print_as_quote = 0;

function print_sales_orders()
{
	global $path_to_root, $print_as_quote, $no_zero_lines_amount;

	include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$currency = $_POST['PARAM_2'];
	$email = $_POST['PARAM_3'];
	//$email = 'legiang0212@gmail.com';
	$print_as_quote = $_POST['PARAM_4'];
	$comments = $_POST['PARAM_5'];
	$orientation = $_POST['PARAM_6'];

	$start_date =$_POST['PARAM_7'];
	if( !is_date($start_date) ){
		$start_date = null;
	} else {
		$start_date = date('Y-m-d',strtotime($start_date));
	}

	$end_date = $_POST['PARAM_8'];
	if( !is_date($end_date) ){
		$end_date = null;
	} else {
		$end_date = date('Y-m-d',strtotime($end_date));
	}

	$reference = $_POST['PARAM_9'];
	if( isset($_POST['PARAM_9']) && !$reference ){
		display_error(_("You must enter Orders Number."));
		return;
	}

	if (!$from || !$to) return;

	$max_id = max($from,$to);
	$min_id = min($from,$to);
	$from = $min_id;
	$to = $max_id;

	$orientation = ($orientation ? 'L' : 'P');
	$dec = user_price_dec();

	$cols = array(4, 60, 225, 300, 325, 385, 450, 515);

	// $headers in doctext.inc
	$aligns = array('left',	'left',	'right', 'left', 'right', 'right', 'right');

	$params = array('comments' => $comments);

	$cur = get_company_Pref('curr_default');

	if ($email == 0) {
		if ($print_as_quote == 0)
			$rep = new FrontReport(_("SALES ORDER"), "SalesOrderBulk", user_pagesize(), 9, $orientation);
		else
			$rep = new FrontReport(_("QUOTE"), "QuoteBulk", user_pagesize(), 9, $orientation);
	}
	if ($orientation == 'L')
		recalculate_cols($cols);



	for ($i = $from; $i <= $to; $i++) {
		$myrow = get_sales_order_header($i, ST_SALESORDER,$start_date,$end_date,$reference);

		if( !isset($myrow['debtor_no']) ) {
			continue;
		}


		$baccount = get_default_bank_account($myrow['curr_code']);
		$params['bankaccount'] = $baccount['id'];
		$branch = get_branch($myrow["branch_code"]);
		if ($email == 1)
		{
			$rep = new FrontReport("", "", user_pagesize(), 9, $orientation);
			if ($print_as_quote == 1)
			{
				$rep->title = _('QUOTE');
				$rep->filename = "Quote" . $i . ".pdf";
			}
			else
			{
				$rep->title = _("SALES ORDER");
				$rep->filename = "SalesOrder" . $i . ".pdf";
			}
		}
		else
			$rep->title = ($print_as_quote==1 ? _("QUOTE") : _("SALES ORDER"));
		$rep->SetHeaderType('Header2');
		$rep->currency = $cur;
		$rep->Font();
		$rep->Info($params, $cols, null, $aligns);

		$contacts = get_branch_contacts($branch['branch_code'], 'order', $branch['debtor_no'], true);
		$rep->SetCommonData($myrow, $branch, $myrow, $baccount, ST_SALESORDER, $contacts);
		$rep->NewPage();

		$result = get_sales_order_details($i, ST_SALESORDER);
		$SubTotal = 0;
		$items = $prices = array();
		$tax = array();
		////////////////////////////////////////////////////////Sua Phan trang
		$result1 = get_sales_order_details($i, ST_SALESORDER);
		$tax1 = array();
		while ($myrow21=db_fetch($result1))
		{
			$tax1[] = $myrow21['tax_type_id'];
		}
		$countTax = 0;
		for ($i1 = 0; $i1 < count($tax1); $i1++)
		{
			$check = 1;
			for ($j1 = $i1-1; $j1 >=0; $j1--)
			{
				if ($tax1[$i1]==$tax1[$j1])
				{
					$check = 0;
				}
			}
			if ($check==1)
			{
				$countTax = $countTax + 1;
			}
		}
		$countTax = $countTax - 2;
		//////////////////////////////////////////////////
		while ($myrow2=db_fetch($result))
		{
			$Net = round2(((1 - $myrow2["discount_percent"]) * $myrow2["unit_price"] * $myrow2["quantity"]),
					user_price_dec());
			$prices[] = $Net;
			$tax[] = $myrow2['tax_type_id'];
			$items[] = $myrow2['stk_code'];
			$SubTotal += $Net;
			$DisplayPrice = number_format2($myrow2["unit_price"],$dec);
			$DisplayQty = number_format2($myrow2["quantity"],get_qty_dec($myrow2['stk_code']));
			$DisplayNet = number_format2($Net,$dec);
			if ($myrow2["discount_percent"]==0)
				$DisplayDiscount ="";
			else
				$DisplayDiscount = number_format2($myrow2["discount_percent"]*100,user_percent_dec()) . "%";
			$rep->TextCol(0, 1,	$myrow2['stk_code'], -2);
			$oldrow = $rep->row;
			if ($myrow2['long_description'] != ""){
				$rep->TextColLines(1, 2, $myrow2['long_description'], -2);
			}else
			{
				$rep->TextColLines(1, 2, $myrow2['description'], -2);
			}
			$newrow = $rep->row;
			$rep->row = $oldrow;
			if ($Net != 0.0 || !is_service($myrow2['mb_flag']) || !isset($no_zero_lines_amount) || $no_zero_lines_amount == 0)
			{
				$rep->TextCol(2, 3,	$DisplayQty, -2);
				$rep->TextCol(3, 4,	$myrow2['units'], -2);
				$rep->TextCol(4, 5,	$DisplayPrice, -2);
				$rep->TextCol(5, 6,	$DisplayDiscount, -2);
				$rep->TextCol(6, 7,	$DisplayNet, -2);
			}
			$rep->row = $newrow;
			//$rep->NewLine(1);
			if ($rep->row < $rep->bottomMargin + ((15 + $countTax ) * $rep->lineHeight))
				$rep->NewPage();
		}
		if ($myrow['comments'] != "")
		{
			$rep->NewLine();
			$rep->TextColLines(1, 5, $myrow['comments'], -2);
		}
		$DisplaySubTot = number_format2($SubTotal,$dec);
		$DisplayFreight = number_format2($myrow["freight_cost"],$dec);

		$rep->row = $rep->bottomMargin + ((15 + $countTax) * $rep->lineHeight);
		$doctype = ST_SALESORDER;

		$rep->TextCol(3, 6, _("Sub-total"), -2);
		$rep->TextCol(6, 7,	$DisplaySubTot, -2);
		$rep->NewLine();
		$rep->TextCol(3, 6, _("Shipping"), -2);
		$rep->TextCol(6, 7,	$DisplayFreight, -2);
		$rep->NewLine();

		$DisplayTotal = number_format2($myrow["freight_cost"] + $SubTotal, $dec);
		if ($myrow['tax_included'] == 0) {
			$rep->TextCol(3, 6, _("TOTAL ORDER EX GST"), - 2);
			$rep->TextCol(6, 7,	$DisplayTotal, -2);
			$rep->NewLine();
		}

		$tax_items = get_tax_for_items_new($tax, $items, $prices, $myrow["freight_cost"],
		  $myrow['tax_group_id'], $myrow['tax_included'],  null);
		$first = true;
		$company = get_company_pref();
		if( isset($company['gst_no']) &&  trim($company['gst_no']) != '' ){
			foreach($tax_items as $tax_item){
				$DisplayTax = number_format2($tax_item['Value'], $dec);

				$tax_type_name = $tax_item['name'];
				$tax_type_name = strstr($tax_type_name,"(");

				if ($myrow['tax_included'])
				{
					if (isset($alternative_tax_include_on_docs) && $alternative_tax_include_on_docs == 1)
					{
						if ($first)
						{
							$rep->TextCol(3, 6, _("Total Tax Excluded"), -2);
							$rep->TextCol(6, 7,	number_format2($sign*$tax_item['net_amount'], $dec), -2);
							$rep->NewLine();
						}
						$rep->TextCol(3, 6, $tax_type_name, -2);
						$rep->TextCol(6, 7,	$DisplayTax, -2);
						$first = false;
					}
					else
						$rep->TextCol(3, 6, _("Included Tax") . " " . $tax_type_name . " " . _("Amount"), -2);
					$rep->TextCol(6, 7,	$DisplayTax, -2);
				}
				else
				{
					$SubTotal += $tax_item['Value'];
					$rep->TextCol(3, 6, _("Tax") . " " . $tax_type_name . " " . _("Amount"), -2);
					$rep->TextCol(6, 7,	$DisplayTax, -2);
				}
				$rep->NewLine();
			}

		}

		$rep->NewLine();

		$DisplayTotal = number_format2($myrow["freight_cost"] + $SubTotal, $dec);
		$rep->Font('bold');
		$total_lable = 'TOTAL ORDER GST INCL.';
		if( !isset($company['gst_no']) ||  trim($company['gst_no']) == '' ){
			$total_lable = 'TOTAL ORDER.';
		}
		$rep->TextCol(3, 6, _($total_lable), - 2);
		$rep->TextCol(6, 7,	$DisplayTotal, -2);
		$words = price_in_words($myrow["freight_cost"] + $SubTotal, ST_SALESORDER);
		if ($words != "")
		{
			$rep->NewLine(1);
			$rep->TextCol(1, 7, $myrow['curr_code'] . ": " . $words, - 2);
		}
		$rep->Font();
		if ($email == 1)
		{
			$rep->End($email);
		}
	}
	if ($email == 0)
		$rep->End();
}

?>