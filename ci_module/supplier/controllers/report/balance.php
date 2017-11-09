<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SupplierReportBalance  extends ci {
    function __construct() {
        $ci = get_instance();
        $this->input = $ci->input;
//         $this->report = $ci->module_control_load('report',null,true);
//         $this->customer_model = module_model_load('customer','sales');
//         $this->customer_trans_model = module_model_load('customer_trans','sales');

        $this->trans_model = module_model_load( 'transaction','supplier' );

    }


    var $balance_report_table = array(
        'type'      =>array( 'Journal'      ,70 ,'left'),
        'reference' =>array( 'Reference No.',130 ,'left'),
        'supp_reference' =>array( 'Supp Ref',180 ,'left'),
        'tran_date' =>array( 'Date'         ,240 ,'center'),
        'x_rate' =>array( 'Rate'         ,260 ,'right'),
        'debit'     =>array( 'Debits'       ,300 ,'right'),
        'credit'    =>array( 'Credits'      ,350 ,'right'),
        'debit_base'     =>array( 'Debits (MYR)'       ,400 ,'right'),
        'credit_base'    =>array( 'Credits (MYR)'      ,450 ,'right'),
        'credit_base'    =>array( 'Credits (MYR)'      ,450 ,'right'),
        'exchange_diff' =>array( 'FEG'      ,470 ,'right'),
        'balance'   =>array( 'Balance'  ,520 ,'right'),
    );

    function balance_print(){
        $from =        input_val('PARAM_0');
        $to =          input_val('PARAM_1');
        $supplier_id = input_val('PARAM_2');
        $currency =    input_val('PARAM_3');
        $comments =    input_val('PARAM_4');
        $orientation = input_val('PARAM_5') ? 'L' : 'P';
        $destination = input_val('PARAM_6');
        $no_zeros = _('Yes');

        $balance_report_table = $this->balance_report_table;
        if( $orientation=='L' ){
            $balance_report_table = array(
                'type'      =>array( 'Journal'      ,70 ,'left'),
                'reference' =>array( 'Reference No.',130 ,'left'),
                'supp_reference' =>array( 'Supp Ref',180 ,'left'),
                'tran_date' =>array( 'Date'         ,240 ,'center'),
                'x_rate' =>array( 'Rate'         ,260 ,'right'),
                'debit'     =>array( 'Debits'       ,310 ,'right'),
                'credit'    =>array( 'Credits'      ,350 ,'right'),
                'debit_base'     =>array( 'Debits (MYR)'       ,410 ,'right'),
                'credit_base'    =>array( 'Credits (MYR)'      ,440 ,'right'),
                'credit_base'    =>array( 'Credits (MYR)'      ,470 ,'right'),
                'exchange_diff' =>array( 'FEG'      ,510 ,'right'),
                'balance'   =>array( 'Balance'  ,550 ,'right'),
            );
        }

        $path_to_root = ROOT;
        if ($destination)
            include_once(ROOT . "/reporting/includes/excel_report.inc");
        else
            include_once(ROOT . "/reporting/includes/pdf_report.inc");

        if ($supplier_id == ALL_TEXT)
            $cust = _('All');
        else
            $cust = get_supplier_name($supplier_id);

        if ($currency == ALL_TEXT)
        {
            $convert = true;
            $currency = _('Balances in Home Currency');
        }
        else
            $convert = false;


        $params =   array( 	0 => $comments,
            1 => array('text' => _('Period'), 'from' => $from, 		'to' => $to),
            2 => array('text' => _('Customer'), 'from' => $cust,   	'to' => ''),
            3 => array('text' => _('Currency'), 'from' => $currency, 'to' => ''),
            4 => array('text' => _('Suppress Zeros'), 'from' => $no_zeros, 'to' => ''));

        $rep = new FrontReport(_('Supplier Ledger'), "SupplierBalances", user_pagesize(), 9, $orientation);

        list ($headers, $cols, $aligns) = get_instance()->reporting->report_front_params($balance_report_table);

        if ($orientation == 'L')
            recalculate_cols($cols);

        $rep->Font();
        $rep->Info($params, $cols, $headers, $aligns);
        $rep->NewPage();



        $sql = "SELECT supplier_id, supp_name AS name , curr_code FROM ".TB_PREF."suppliers ";
        if ($supplier_id != ALL_TEXT)
            $sql .= "WHERE supplier_id=".db_escape($supplier_id);
        $sql .= " ORDER BY name";
        $result = db_query($sql, "The customers could not be retrieved");



        $grand_total = array('debit'=>0,'credit'=>0,'balance'=>0);
        while ($myrow = db_fetch($result)){
            if (!$convert && $currency != $myrow['curr_code'])
                continue;


//             $rate = $convert ? get_exchange_rate_from_home_currency($myrow['curr_code'], Today()) : 1;
            $rate = 1;
            $bal = $this->trans_model->get_open_balance($myrow['supplier_id'], $from, $to);


            $rep->TextCol3(0, 2, $myrow['name'],2);
            $rep->TextCol(2,3,	_("Open Balance") );

            if ($convert) {
                $rep->TextCol3(3, 4, $myrow['curr_code'], 2);
            }

            $line_total = array('debit'=>0,'credit'=>0,'balance'=>0);

            $bal->debit = abs(round2($bal->debit));
            $bal->credit = abs(round2($bal->credit));

            $line_total['balance'] += floatval($bal->debit) - floatval($bal->credit);

//             $rep->TextCol(4,5,	number_total($bal->debit) );
//             $rep->TextCol(5,6,	number_total($bal->credit) );
            $rep->TextCol(6,7,	number_total($line_total['balance']) );

            $rep->NewLine(1, 2);

            $trans_items = $this->trans_model->get_transactions($myrow['supplier_id'], $from, $to);
            if (count($trans_items) < 1)
                continue;


            $rep->Line($rep->row + 4);
            foreach ($trans_items AS $trans ){
                $trans = (array)$trans;
                if ($no_zeros==_('No') && floatcmp($trans['TotalAmount'], $trans['Allocated']) == 0)
                    continue;

                $rep->NewLine(1, 2);

                $total_amount = $trans['amount_original'];

                if ( in_array($trans['type'], array(ST_BANKPAYMENT,ST_SUPPCREDIT,ST_SUPPAYMENT) ) ){
                    $trans['debit'] = abs($trans['amount_original']);
                }else {
                    $trans['credit'] = abs($trans['amount_original']);
                }


                //$trans['balance'] =  abs($trans['debit']) - abs($trans['credit']);
                $trans['balance'] =  (abs($trans['debit']) - abs($trans['credit']))*$trans['x_rate'] + $trans['exchange_diff'];

                foreach ($line_total AS $k=>$v){
                    $line_total[$k] += $trans[$k];
                }

                $col = 0;
                foreach ($this->balance_report_table AS $k=>$val){
                    $txt = "";
                    switch ($k){
                        case 'type':
                            $txt = tran_name($trans[$k]); break;
                        case 'credit':
                        case 'debit':
                            $txt = number_total($trans[$k]);
                            break;
                        case 'exchange_diff':
                            $txt = number_total($trans[$k],false,false);
                            break;
                        case 'credit_base':
                        case 'debit_base':
                            $k = substr($k, 0, -5);
                            $txt = number_total($trans[$k]*$trans['x_rate']);
                            break;
                        case 'balance':
                            $txt = number_total($line_total[$k]);
                             break;
                        default:
                            $txt = $trans[$k]; break;
                    }


                    if( $k=='tran_date' ){
                        $rep->DateCol($col, $col += 1,	$txt, true);
                    } else {
                        $rep->TextCol($col, $col += 1, _($txt));
                    }

                }

            } // end loop transaction;

            foreach ($line_total AS $k=>$v){
                $grand_total[$k] += $line_total[$k];
            }

            $rep->Line($rep->row - 8);
            $rep->NewLine(2);
            $rep->TextCol3(0, 3, "Total",2);
            $rep->TextNum(5, 6, $line_total['debit']);
            $rep->TextNum(6, 7, $line_total['credit']);
            $rep->TextNum(10, 11, $line_total['balance']);

       		$rep->Line($rep->row  - 4);
       		$rep->NewLine(2);

    	}

    	$rep->fontSize += 2;
    	
        $rep->TextCol(0, 3, _('Grand Total'));
        $rep->fontSize -= 2;
        $rep->TextNum(5, 6, $grand_total['debit']);
        $rep->TextNum(6, 7, $grand_total['credit']);
        //$rep->TextNum(8, 9, $grand_total['balance_origin']);
        $rep->TextNum(10, 11, $grand_total['balance']);

    	$rep->Line($rep->row  - 4);
    	$rep->NewLine();
    	$rep->End();

    }
}