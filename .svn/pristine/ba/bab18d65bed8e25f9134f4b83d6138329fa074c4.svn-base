<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BankReportStatement  {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->input = $ci->input;

        $this->bank_transaction_model = module_model_load('trans_report','bank');
        $this->report = module_control_load('report','report');


    }

    function statement_print($pdf){

        $acc = $pdf->inputVal('PARAM_0');
        $from = $pdf->inputVal('PARAM_1');
        $to = $pdf->inputVal('PARAM_2');
        $zero = $pdf->inputVal('PARAM_3');
        $comments = $pdf->inputVal('PARAM_4');
        $orientation = $pdf->inputVal('PARAM_5')? 'L' : 'P';
        $destination = $pdf->inputVal('PARAM_6');
        if( $destination==1 ){
            $_POST['report_type'] = 'excel';
        }
        $account = get_bank_account($acc);
        $act = $account['bank_account_name']." - ".$account['bank_curr_code']." - ".$account['bank_account_number'];


        $this->rep = $this->report->front_report(_('Bank Statement'),$this->statement_report_table);
        $this->rep->SetHeaderType('BankStatement');


        $this->rep->params = array("",
            'bank_account'=>$account['bank_account_name'],
            'period'=>$from.' - '.$to
        );
        $this->rep->NewPage();
        $this->rep->Font('bold');
        $this->rep->TextCol(0, 3, $account['bank_account_name']);
        $this->rep->TextCol(3, 5, 'Opening Balance');

        $prev_balance = $this->bank_transaction_model->get_bank_balance_to($from, $account["id"]);
        if( $prev_balance > 0 ){
            $this->rep->TextCol(5, 6, number_total($prev_balance,true));
        } elseif ($prev_balance < 0){
            $this->rep->TextCol(6, 7, number_total($prev_balance,true));
        }

        $this->rep->Font();
        $trans = $this->bank_transaction_model->get_bank_transactions($from, $to, $account['id']);
        $balance = $prev_balance;
        $debit_total = $credit_total = 0;
        foreach ($trans AS $tran){
            $this->rep->NewLine();
            $col = $debit = $credit = 0;
            $balance += $tran->amount;
            foreach ($this->statement_report_table AS $k=>$val){
                $txt = NULL;
                switch ($k){
                    case 'type':
                        $txt = tran_name($tran->$k);
                        break;
                    case 'person':
                        $txt = payment_person_name($tran->person_type_id,$tran->person_id);
                        break;
                    case 'trans_date':
                        $txt = sql2date($tran->$k);
                        break;
                    case 'credit':
                        if( $tran->amount < 0 ){
                            $credit = $tran->amount;
                            $txt = number_total($credit,true);
                        }


                        break;
                    case 'debit':
                        if( $tran->amount > 0 ){
                            $debit = $tran->amount;
                            $txt = number_total($debit,true);
                        }

                        break;
                    case 'balance':
                        $txt = number_total($balance);
                        break;
                    default:
                    $txt = $tran->$k;
                    break;
                }

                $this->rep->TextCol($col, $col += 1, $txt);
            }
            $debit_total += $debit;
            $credit_total += $credit;
        }


        $fontSizeDefault = $this->rep->fontSize;
        $this->rep->fontSize = 10;
        $this->rep->Font();

        $this->rep->aligns[0] = 'right';
        $this->rep->NewLine();
        $this->rep->SetFillColor(237,237,237);
        $this->rep->TextCol(0, 5, 'Total Debit / Credit',0,0,0,1);
        $this->rep->TextCol(5, 6, number_total($debit_total,true,false),0,0,0,1);
        $this->rep->TextCol(6, 7, number_total($credit_total,true,false),0,0,0,1);
        $this->rep->TextCol(7, 8, NULL,0,0,0,1);


        $this->rep->NewLine();
        $this->rep->SetFillColor(221,221,221);
        $this->rep->TextCol(0, 5, 'Ending Balance',0,0,0,1);
        $this->rep->TextCol(5, 8, number_total($balance,true),0,0,0,1);

        $this->rep->NewLine();
        $this->rep->SetFillColor(237,237,237);
        $this->rep->TextCol(0, 5, 'Net Change',0,0,0,1);
        $this->rep->TextCol(5, 8, number_total(abs($debit_total)-abs($credit_total)),0,0,0,1);

        $this->rep->End();

    }

    var $statement_report_table = array(
        'type'=>array('Type',90),
        'trans_no'=>array('#',115,'center'),
        'ref'=>array('Reference',190),
        'trans_date'=>array('Date',240,'center'),
        'person'=>array('Person/Item',370),
	    'debit'=>array('Debit',420,'right'),
	    'credit'=>array('Credit',470,'right'),
	    'balance'=>array('Balance',525,'right'),
    );


}