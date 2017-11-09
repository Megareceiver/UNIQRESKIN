<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SalesReportCustomerBalance {
    function __construct() {
        $ci = get_instance();
        $this->input = $ci->input;
//         $this->report = $ci->module_control_load('report',null,true);
        $this->customer_model = module_model_load('customer','sales');
        $this->customer_trans_model = module_model_load('customer_trans','sales');

        $this->db = $ci->db;
    }
    function index(){
        if( get_instance()->uri->segment(4)=='test' ){
            return $this->test();
        }
        die('index of SalesReportCustomerBalance');
    }
    function test(){

        $this->balance_print();
    }

    var $balance_report_table = array(
        //'type'      =>array( 'Journal'      ,150 ,'left'),
        'type'      =>array( 'Journal'      ,100 ,'left'),
        'trans_no'      =>array( '#'      ,150 ,'left'),
        'reference' =>array( 'Reference No.',230 ,'left'),
        'cust_ref2' =>array( 'Cust Ref',280 ,'left'),
        'tran_date' =>array( 'Date'         ,320 ,'center'),
        'debit'     =>array( 'Debits'       ,380 ,'right'),
        'credit'    =>array( 'Credits'      ,450 ,'right'),
        'balance'   =>array( 'Balance'      ,515 ,'right'),
    );

    function balance_print(){
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
            include_once(ROOT . "/reporting/includes/excel_report.inc");
        else
            include_once(ROOT . "/reporting/includes/pdf_report.inc");

        $orientation = ($orientation ? 'L' : 'P');
        if ($fromcust == ALL_TEXT)
            $cust = _('All');
        else
            $cust = get_customer_name($fromcust);

        $dec = user_price_dec();

        $currency_page = $currency;
        if ($currency == ALL_TEXT)
        {
            $convert = true;
            $currency_page = _('Balances in Home Currency');
        } else {
            $convert = false;
        }


        $params =   array(  0 => $comments,
            1 => array('text' => _('Period'), 'from' => $from,      'to' => $to),
            2 => array('text' => _('Customer'), 'from' => $cust,    'to' => ''),
            3 => array('text' => _('Currency'), 'from' => $currency_page, 'to' => ''),
            4 => array('text' => _('Suppress Zeros'), 'from' => $no_zeros, 'to' => ''));

        $rep = new FrontReport(_('Customer Ledger'), "CustomerBalances", user_pagesize(), 9, $orientation);

        list ($headers, $cols, $aligns) = get_instance()->reporting->report_front_params($this->balance_report_table);
        if ($orientation == 'L')
            recalculate_cols($cols);

        $rep->Font();
        $rep->Info($params, $cols, $headers, $aligns);
        $rep->NewPage();


        $this->db->from('debtors_master')->select('debtor_no, name, curr_code');

        if ($fromcust != ALL_TEXT){
            $this->db->where('debtor_no',$fromcust);
        }


        if ( $currency != ALL_TEXT){
            $this->db->where('curr_code',$currency);
        }

        $result = $this->db->order_by('name')->get()->result_array();


        $grand_total = array('debit'=>0,'credit'=>0,'balance'=>0);

        foreach ($result AS $myrow){

//             if (!$convert && $currency != $myrow['curr_code'])
//                 continue;

            $curr_code = $myrow['curr_code'];

            $bal = $this->customer_trans_model->get_open_balance($myrow['debtor_no'], $from, $to);

            $rep->TextCol3(0, 2, $myrow['name']);
            $rep->TextCol(2,3,  _("Open Balance") );
            $rep->TextCol3(3, 4, $curr_code, 2);
//             if ($currency != ALL_TEXT){
//                 $rep->TextCol3(3, 4, $curr_code, 2);
//             } else {
//                 $rep->TextCol3(3, 4, get_company_currency(), 2);
//             }

            $line_total = array('debit'=>0,'credit'=>0,'balance'=>0);

//             if ($bal->TotalAmount > 0.0) {
//                 $bal->debit = $bal->TotalAmount;

//             } else {
//                 $bal->credit = abs($bal->TotalAmount);

//             }
            $bal->debit = abs(round2($bal->debit));
            $bal->credit = abs(round2($bal->credit));

            $line_total['balance'] = ($bal->debit- $bal->credit);
            $grand_total['balance'] += $line_total['balance'];

            $rep->TextCol(5,6,  number_total($bal->debit) );
            $rep->TextCol(6,7,  number_total($bal->credit,true) );
//             $rep->TextCol(7,8,   number_total($line_total['balance']) );
            $rep->TextCol(7,8,  number_total($grand_total['balance']) );
            $rep->NewLine(1, 2);

            $trans_items = $this->customer_trans_model->get_transactions($myrow['debtor_no'], $from, $to);
//             $trans_items = array();
            if (count($trans_items) < 1)
                continue;

            $rep->Line($rep->row + 4);
            foreach ($trans_items AS $trans ){

//                 $rate = $convert ? get_exchange_rate_from_home_currency($curr_code, $trans->tran_date) : 1;
                $rate = 1;
                if ($no_zeros==_('No') && floatcmp($trans['TotalAmount'], $trans['Allocated']) == 0)
                    continue;

                $rep->NewLine(1, 2);
                if ( in_array($trans['type'], array(ST_CUSTCREDIT,ST_CUSTPAYMENT,ST_BANKDEPOSIT)) ){
                    $trans['TotalAmount'] *= -1;
                }

                $trans['debit'] = $trans['credit'] = 0;
                $total_amount = $trans['TotalAmount'];
//                 $total_amount = $trans['amount_original'];

                if ($trans['TotalAmount'] > 0.0) {
                    $trans['debit'] = $total_amount;
                } else {
                    $trans['credit'] = abs($total_amount);
//                     $trans['credit'] = abs($trans['amount_original']);
                }

                $trans['balance'] =  abs($trans['debit']) - abs($trans['credit']);

                foreach ($line_total AS $k=>$v){
                    $line_total[$k] += $trans[$k];
                    $grand_total[$k] += $trans[$k];
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
                        case 'balance':
                            $txt = number_total($line_total[$k]);
                             break;
                        default:
                            $txt = $trans[$k]; break;
                    }


                    if( $k=='tran_date' ){
                        $rep->DateCol($col, $col += 1,  $txt, true);
                    } else {
                        $rep->TextCol($col, $col += 1, _($txt));
                    }

                }

            } // end loop transaction;


            $rep->Line($rep->row - 8);
            $rep->NewLine(2);
            $rep->TextCol3(0, 3, "Total",2);
            $rep->TextNum(5, 6, $line_total['debit']);
            $rep->TextNum(6, 7, $line_total['credit']);
            $rep->TextNum(7, 8, $line_total['balance']);


            $rep->Line($rep->row  - 4);
            $rep->NewLine(2);

        }

        $rep->fontSize += 2;
        $rep->TextCol(0, 3, _('Grand Total'));
        $rep->fontSize -= 2;
        $rep->TextNum(5, 6, $grand_total['debit']);
        $rep->TextNum(6, 7, $grand_total['credit']);
        $rep->TextNum(7, 8, $grand_total['balance']);
        $rep->Line($rep->row  - 4);
        $rep->NewLine();
        $rep->End();

    }
}