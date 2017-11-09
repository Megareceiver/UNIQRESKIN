<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once(ROOT . "/reporting/includes/pdf_report.inc");

class SalesReportDebtorLedger  extends ci {
    function __construct() {
        $ci = get_instance();
        $this->input = $ci->input;
        $this->report = $ci->module_control_load('report',null,true);
        $this->customer_model = module_model_load('customer','sales');
        $this->customer_trans_model = module_model_load('customer_trans','sales');

    }

    function index(){
        if( $this->input->post() ) {
            $path_to_root = ROOT;
            if( input_val('destination')=='excel' ){
                include_once(ROOT . "/reporting/includes/excel_report.inc");
            } else {
//                 include_once(ROOT . "/reporting/includes/pdf_report.inc");
            }
            return $this->debtor_ledger_pdf();
        }
        $this->report->fields = array(
            'start_date' => array('type'=>'qdate','title'=>_('Start Date'),'value'=>begin_month() ),
            'end_date' => array('type'=>'qdate','title'=>_('End Date'),'value'=>end_month() ),
            'customer'=>array('value'=>'','type'=>'CUSTOMER','title'=>'Customer','value'=>get_cookie('customer')),
            'currency' => array('type'=>'currency','title'=>_('Currency Filter') ),
            'comments'=> array('type'=>'TEXTBOX','title'=>'Comments' ),
            'orientation'=>array('type'=>'orientation','title'=>_('Orientation')),
            'destination'=>array('type'=>'destination','title'=>_('Destination')),
        );

        $this->report->form('Debtor Ledger');
    }

    private function debtor_ledger_pdf(){

        $orientation = (input_val('orientation') ? 'L' : 'P');
        $comments = input_val("comments");
        $from = input_val("start_date");
        $to = input_val("end_date");
        $dec = user_price_dec();
        $show_balance = true;
        $no_zeros = _('Yes');

        $fromcust = input_val('customer');
        if( is_numeric($fromcust) && $fromcust>0 ){
            $cust = $this->customer_model->get_customer_name($fromcust);
        } else {
            $cust = _('All');
        }

        $currency = input_val('currency');
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


        $num_lines = 0;

        $openning_balance_total = 0;

        $customters = $this->customer_model->get_customers($fromcust);

        while (list ($key, $myrow) = each ($customters) ){
            if (!$convert && $currency != $myrow['curr_code'])
                continue;


            $accumulate = 0;
            $rate = $convert ? get_exchange_rate_from_home_currency($myrow['curr_code'], Today()) : 1;
            $bal = $this->customer_trans_model->get_open_balance($myrow['debtor_no'], $from, $to);

            $init[0] = $init[1] = 0.0;
            $init[0] = round2(($bal->charges*$rate), $dec);
            $init[1] = round2(($bal->credits*$rate), $dec);
            $init[2] = round2($bal->allocated*$rate, $dec);
            $init[3] = round2( ($bal->charges - $bal->credits - $bal->balance)*$rate, $dec);

            $outstanding = $bal->outStanding*$rate;
            // 		$init[3] = 0;

            /*
             if (!$show_balance){
             // update request 160126 QuanNH

             // 			$init[3] = $init[0] - $init[1];


             $accumulate += $init[3];
             // 			$total[3] += $outstanding;
             } else {

             // 		    $init[3] = round2($bal['OutStanding']*$rate, $dec);
             $init[3] += $bal['OutStanding']*$rate;
             $openning_balance_total + $outstanding;
             }
             */

            if ($show_balance) {
                $init[3] = $init[0] - $init[1] - $init[2];
                $accumulate += $init[3];
            }  else {
                $init[3] = ($bal['type']==ST_OPENING_CUSTOMER) ? $bal['charges']-$bal['Allocated'] : $bal['OutStanding'];
                $init[3] = round2($init[3]*$rate, $dec);
            }



            $trans_items = $this->customer_trans_model->get_transactions($myrow['debtor_no'], $from, $to);
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
}