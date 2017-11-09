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
$page_security = 'SA_CUSTPAYMREP';

// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Customer Balances
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/sales/includes/db/customers_db.inc");

//----------------------------------------------------------------------------------------------------

// trial_inquiry_controls();
print_customer_balances();

function get_open_balance($debtorno, $to, $date_max=NULL){
	if($to)
		$to = date2sql($to);

	$allocation_model = module_model_load('allocation','gl');
// 	$alloc = 't.alloc';
// 	$alloc = "(".$allocation_model->str_for_invoice('t.trans_no', $to).")";
	$alloc = $allocation_model->alloc_sum('t.trans_no','t.type', sql2date($date_max),'alloc_',array(ST_SALESINVOICE,ST_OPENING_CUSTOMER));

    $sql = "SELECT SUM(IF(t.type = ".ST_SALESINVOICE." OR t.type = ".ST_BANKPAYMENT." OR t.type= ".ST_OPENING_CUSTOMER.",
    	(t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax ), 0)) AS charges,

    	SUM( IF(t.type <> ".ST_SALESINVOICE." AND t.type <> ".ST_BANKPAYMENT." AND t.type <> ".ST_OPENING." AND t.type <> ".ST_OPENING_CUSTOMER." ,
	    	(t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax ) * -1, 0)
    	    ) AS credits,

		SUM(($alloc)) AS Allocated,

		SUM(IF(t.type = ".ST_SALESINVOICE." OR t.type = ".ST_BANKPAYMENT." OR t.type = ".ST_OPENING." OR t.type= ".ST_OPENING_CUSTOMER.",
			(t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax  - ($alloc)),
	    	((t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax) * -1 + ($alloc)))) AS OutStanding,

	    	t.type

		FROM debtor_trans t
    	WHERE t.debtor_no = ".db_escape($debtorno)
    	." AND t.`trans_no` NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=t.type ) "
		." AND t.type <> ".ST_CUSTDELIVERY ." AND t.type <> ".ST_BANKPAYMENT ."  ";
    if ($to)
    	$sql .= " AND t.tran_date < '$to'";
	$sql .= " GROUP BY debtor_no";
// bug($sql);die;
    $result = db_query($sql,"No transactions were returned");
    return db_fetch($result);
}

function get_transactions($debtorno, $from, $to){
	$from = date2sql($from);
	$to = date2sql($to);

	$allocation_model = module_model_load('allocation','gl');
// 	$alloc = 'debtor_trans.alloc';
	$alloc = $allocation_model->alloc_sum('tran.trans_no','tran.type', $to,'alloc_',array(ST_SALESINVOICE,ST_OPENING_CUSTOMER));

	$db = get_instance()->db;

	$db->select("($alloc) AS Allocated",FALSE);

	$db->select("tran.*")->from('debtor_trans AS tran');
	$db->select("(tran.ov_amount + tran.ov_gst + tran.ov_freight + tran.ov_freight_tax + tran.ov_discount) AS TotalAmount");

	$db->select("( tran.type=".ST_SALESINVOICE." AND tran.due_date < '".$to."') AS OverDue ");

	if( is_date($from) ){
	    $db->where("tran.tran_date >=",$from);
	}
	if( is_date($to) ){
	    $db->where("tran.tran_date <=",$to);
	}

	$db->where(array('tran.debtor_no'=>$debtorno,'tran.type <>'=>ST_CUSTDELIVERY,'tran.ov_amount <>'=>0));
	$db->where(array('tran.debtor_no'=>$debtorno,'tran.type <>'=>ST_CUSTDELIVERY,'tran.ov_amount <>'=>0));
    $db->where("tran.`trans_no` NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=tran.type )");

    $query = $db->order_by('tran.tran_date ASC')->get();


    if( is_object($query) )
        return $query->result_array();
    else
        check_db_error("No transactions were returned", $query->result(), true);

//     $sql = "SELECT ".TB_PREF."debtor_trans.*,
// 		(".TB_PREF."debtor_trans.ov_amount + ".TB_PREF."debtor_trans.ov_gst + ".TB_PREF."debtor_trans.ov_freight + ".TB_PREF."debtor_trans.ov_freight_tax + debtor_trans.ov_discount ) AS TotalAmount,"

// 	    //."IF( debtor_trans.type=".ST_SALESINVOICE.",($allocated_query),debtor_trans.alloc) AS Allocated,"
// // 	    ."($alloc) AS Allocated, "
//     ."0 AS Allocated, "
// 	    //.TB_PREF."debtor_trans.alloc AS Allocated,"

// 		."((".TB_PREF."debtor_trans.type = ".ST_SALESINVOICE.") AND ".TB_PREF."debtor_trans.due_date < '$to') AS OverDue

//     	FROM ".TB_PREF."debtor_trans
//     	WHERE ".TB_PREF."debtor_trans.tran_date >= '$from'
// 		AND ".TB_PREF."debtor_trans.tran_date <= '$to'
// 		AND ".TB_PREF."debtor_trans.debtor_no = ".db_escape($debtorno)."
// 		AND ".TB_PREF."debtor_trans.type <> ".ST_CUSTDELIVERY."

// 	    AND ov_amount != 0
// 	    AND debtor_trans.`trans_no` NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=debtor_trans.type )
//     	ORDER BY ".TB_PREF."debtor_trans.tran_date";

    return db_query($sql,"No transactions were returned");
}

//----------------------------------------------------------------------------------------------------

function print_customer_balances()
{
    	global $path_to_root, $systypes_array;

//     	$from = $_POST['PARAM_0'];
//     	$to = $_POST['PARAM_1'];
//     	$fromcust = $_POST['PARAM_2'];
//     	$show_balance = $_POST['PARAM_3'];
//     	$currency = $_POST['PARAM_4'];
//     	$no_zeros = $_POST['PARAM_5'];
//     	$comments = $_POST['PARAM_6'];
// 	$orientation = $_POST['PARAM_7'];
// 	$destination = $_POST['PARAM_8'];

/*
 * update date Uniq
 */
    	$from =        input_val('PARAM_0');
    	$to =          input_val('PARAM_1');
    	$fromcust =    input_val('PARAM_2');
    	$currency =    input_val('PARAM_3');
    	$comments =    input_val('PARAM_4');
    	$orientation = input_val('PARAM_5');
    	$destination = input_val('PARAM_6');

    	$show_balance = true;
    	$no_zeros = _('Yes');



	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$orientation = ($orientation ? 'L' : 'P');
	if ($fromcust == ALL_TEXT)
		$cust = _('All');
	else
		$cust = get_customer_name($fromcust);
    	$dec = user_price_dec();

	if ($currency == ALL_TEXT)
	{
		$convert = true;
		$currency = _('Balances in Home Currency');
	}
	else
		$convert = false;

// 	if ($no_zeros) $nozeros = _('Yes');
// 	else $nozeros = _('No');

// 	$cols = array(0, 100, 150, 210,	210, 320, 385, 450,	515);
	$cols = array(0, 110, 180, 240,	240, 370, 435, 515);

// 	$headers = array(_('Trans Type'), _('#'), _('Date'), _('Due Date'), _('Charges'), _('Credits'),
// 		_('Allocated'), 	_('Outstanding'));

	$headers = array(_('Trans Type'), _('#'), _('Date'), _('Due Date'), _('Debits'), _('Credits'),_('Outstanding'));


	if ($show_balance)
		$headers[6] = _('Balance');
	   $aligns = array('left',	'left',	'left',	'left',	'right', 'right', 'right', 'right');

    $params =   array( 	0 => $comments,
    				    1 => array('text' => _('Period'), 'from' => $from, 		'to' => $to),
    				    2 => array('text' => _('Customer'), 'from' => $cust,   	'to' => ''),
    				    3 => array('text' => _('Currency'), 'from' => $currency, 'to' => ''),
						4 => array('text' => _('Suppress Zeros'), 'from' => $no_zeros, 'to' => ''));

    $rep = new FrontReport(_('Customer Balances'), "CustomerBalances", user_pagesize(), 9, $orientation);
    if ($orientation == 'L')
    	recalculate_cols($cols);
    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$grandtotal = array(0,0,0,0);

	$sql = "SELECT debtor_no, name, curr_code FROM ".TB_PREF."debtors_master ";
	if ($fromcust != ALL_TEXT)
		$sql .= "WHERE debtor_no=".db_escape($fromcust);
	$sql .= " ORDER BY name";
	$result = db_query($sql, "The customers could not be retrieved");
	$num_lines = 0;

	$openning_balance_total = 0;

	while ($myrow = db_fetch($result)){
		if (!$convert && $currency != $myrow['curr_code']) continue;

		$accumulate = 0;
		$rate = $convert ? get_exchange_rate_from_home_currency($myrow['curr_code'], Today()) : 1;
		$bal = get_open_balance($myrow['debtor_no'], $from, $to);
// bug($bal);die;
		$init[0] = $init[1] = 0.0;
		$init[0] = round2(($bal['charges']*$rate), $dec);
		$init[1] = round2(($bal['credits']*$rate), $dec);
		$init[2] = round2($bal['Allocated']*$rate, $dec);
		if( !isset($bal['balance']) ){
		    $bal['balance'] = 0;
		}
		if( !isset($bal['charges']) ){
		    $bal['charges'] = 0;
		}
		if( !isset($bal['credits']) ){
		    $bal['credits'] = 0;
		}
		if( !isset($bal['OutStanding']) ){
		    $bal['OutStanding'] = 0;
		}
		$init[3] = round2( ($bal['charges'] - $bal['credits'] - $bal['balance'])*$rate, $dec);

		$outstanding = $bal['OutStanding']*$rate;


		if ($show_balance) {
		    $init[3] = $init[0] - $init[1] - $init[2];
		    $accumulate += $init[3];
		}  else {
		    $init[3] = ($bal['type']==ST_OPENING_CUSTOMER) ? $bal['charges']-$bal['Allocated'] : $bal['OutStanding'];
		    $init[3] = round2($init[3]*$rate, $dec);
		}



		$trans_items = get_transactions($myrow['debtor_no'], $from, $to);
// 		bug($trans_items); die;
		//if ($no_zeros && db_num_rows($res) == 0) continue;

 		$num_lines++;
		$rep->fontSize += 2;
		$rep->TextCol(0, 2, $myrow['name']);
		if ($convert)
			$rep->TextCol(2, 3,	$myrow['curr_code']);
		$rep->fontSize -= 2;

		$rep->TextCol(3, 4,	_("Open Balance"));
		$rep->AmountCol(4, 5, $init[0], $dec);
		$rep->AmountCol(5, 6, $init[1], $dec);
// 		$rep->AmountCol(6, 7, $init[2], $dec);
		$rep->AmountCol(6, 7, $init[3], $dec);

		$total = array(0,0,0,0);
		for ($i = 0; $i < 4; $i++)
		{
			$total[$i] += $init[$i];
			$grandtotal[$i] += $init[$i];
		}

		/*
		 * add openning balacne
		 */
// 		$accumulate += $init[3];

		$rep->NewLine(1, 2);
		if (count($trans_items) < 1)
			continue;

		$rep->Line($rep->row + 4);

		//while ($trans = db_fetch($res)){
		foreach ($trans_items AS $trans ){
			if ($no_zeros==_('No') && floatcmp($trans['TotalAmount'], $trans['Allocated']) == 0) continue;

			$rep->NewLine(1, 2);
			$rep->TextCol(0, 1, $systypes_array[$trans['type']]);
			$rep->TextCol(1, 2,	$trans['reference']);
			$rep->DateCol(2, 3,	$trans['tran_date'], true);

			if ($trans['type'] == ST_SALESINVOICE)
				$rep->DateCol(3, 4,	$trans['due_date'], true);

			$item[0] = $item[1] = 0.0;

			if ($trans['type'] == ST_CUSTCREDIT || $trans['type'] == ST_CUSTPAYMENT || $trans['type'] == ST_BANKDEPOSIT )
				$trans['TotalAmount'] *= -1;

			if ($trans['TotalAmount'] > 0.0) {
				$item[0] = round2(abs($trans['TotalAmount']) * $rate, $dec);
				$rep->AmountCol(4, 5, $item[0], $dec);
// 				$accumulate += $item[0] - $trans['Allocated'];
				$accumulate += $item[0];
			} else {
				$item[1] = round2(Abs($trans['TotalAmount']) * $rate, $dec);
				$rep->AmountCol(5, 6, $item[1], $dec);
// 				$accumulate -= $item[1] - $trans['Allocated'];
				$accumulate -= $item[1];
			}

			$item[2] = round2($trans['Allocated'] * $rate, $dec);
// 			$rep->AmountCol(6, 7, $item[2], $dec);

			if ($trans['type'] == ST_SALESINVOICE || $trans['type'] == ST_BANKPAYMENT  )
				$item[3] = $item[0] + $item[1] - $item[2];
			else
				$item[3] = $item[0] - $item[1] + $item[2];

			/*
			 * 20160816 QuanNH left allocated
			 */
// 			$accumulate -= $item[2];
			if ($show_balance)
				$rep->AmountCol(6, 7, $accumulate, $dec);
			else
				$rep->AmountCol(6, 7, $item[3], $dec);

			for ($i = 0; $i < 4; $i++) {
				$total[$i] += $item[$i];
				$grandtotal[$i] += $item[$i];
			}

			if ($show_balance) {
// 			    $total[3] = $total[0] - $total[1] + $outstanding;
			    $total[3] = $total[0] - $total[1];
			}

		}
// 		die('check');
		$rep->Line($rep->row - 8);
		$rep->NewLine(2);
		$rep->TextCol(0, 3, _('Total'));
		for ($i = 0; $i < 4; $i++){
		    if( $i < 2 ){
		        $rep->AmountCol($i + 4, $i + 5, $total[$i], $dec);
		    } elseif ($i==3){
		        $rep->AmountCol(6, 7, $total[3], $dec);
		    }

		}


   		$rep->Line($rep->row  - 4);
   		$rep->NewLine(2);

	}

	$rep->fontSize += 2;
	$rep->TextCol(0, 3, _('Grand Total'));
	$rep->fontSize -= 2;
	if ($show_balance) {
// 	    $grandtotal[3] = $grandtotal[0] - $grandtotal[1] + $openning_balance_total;
	    $grandtotal[3] = $grandtotal[0] - $grandtotal[1];
	}


	for ($i = 0; $i < 4; $i++){
	    if( $i < 2 ){
	        $rep->AmountCol($i + 4, $i + 5, $grandtotal[$i], $dec);
	    } elseif ($i==3){
	        $rep->AmountCol(6, 7, $grandtotal[$i], $dec);
	    }
	}

	$rep->Line($rep->row  - 4);
	$rep->NewLine();
	$rep->End();
}

?>
