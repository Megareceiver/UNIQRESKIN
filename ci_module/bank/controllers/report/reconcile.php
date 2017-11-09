<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BankReportReconcile   {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->input = $ci->input;
        if( !isset($ci->reporting) ){
            $ci->front_report = true;
            $ci->load_library('reporting',false,true);
        }
        $this->report = module_control_load('report','report');
        $this->reconciled_model = module_model_load('reconciled','bank');
        $this->bank_account_model = module_model_load('account','bank');
    }

    var $report_form = array(

    );
    function index(){

//         $_POST['bank_account'] = 1;
//         $_POST['reconcile_date'] = '30-4-2016';

        if( $this->ci->input->post() ) {
            return $this->reconcile_print();
        }

        $report = module_control_load(NULL,'report',true);
        $report->fields = array(
            'bank_account' => array('type'=>'BANK_ACCOUNTS','title'=>_('Account'),'value'=>null ),
            'reconcile_date' => array('type'=>'qdate','title'=>_('Reconcile Date'),'value'=>begin_month() ),
            'orientation'=>array('type'=>'orientation','title'=>_('Orientation')),
        );

        $report->form('Bank Reconciliation');
    }

    var $reconcile_report_table = array(
        'trans_date'            =>array( '',60 ,'left'),
        'cheque'                =>array( '',120 ,'left'),
        'payment_person_name'   =>array( '',360 ,'left'),
        'amount'                =>array( 'amount',440 ,'right'),
        'total'                 =>array( 'total',520 ,'right'),

    );

    private function reconcile_print_line($row){
        $col = 0;
        foreach ($this->reconcile_report_table AS $k=>$val){
            $txt = NULL;
            switch ($k){
                case 'payment_person_name':
                    $txt = payment_person_name($row->person_type_id,$row->person_id);
                    break;
                case 'debit':
                    $number = ( $row->amount > 0 ) ? $row->amount : 0;

                    $txt = $number > 0 ? number_total($number) : null;
                    break;
                case 'credit':
                    $number = ( $row->amount <0 ) ? abs($row->amount) : 0;
                    $txt = $number > 0 ? number_total($number) : null;
                    break;
                case 'amount':
                    $txt = strlen($row->amount) > 0 ? number_total($row->amount,true) : null;
                    break;
                default:
                    if( isset($row->$k) ){
                        $txt = $row->$k;
                    }
                    break;
            }
            if( $k=='trans_date' ){
                $this->rep->DateCol($col, $col += 1,$txt, true);
            } else {
                $this->rep->TextCol($col, $col += 1, $txt);
            }

        }
    }

    private function reconcile_print(){
        global $Ajax;
        $destination = input_val('report_type');
        $orientation = input_val('orientation');
        $bank_acc = input_val('bank_account');
        $reconcile_date = input_val('reconcile_date');
        if( !$orientation ){
            $orientation = 'P';
        }else{
            $orientation = ($orientation = 'orientation') ? "P" :'L';

        }

        $comments = NULL;


        $bank_reconcile = $this->reconciled_model->get_bank_account_reconcile($bank_acc, $reconcile_date,false);
        $bank_account_detail = $this->bank_account_model->get_bank_account($bank_acc);
        if( !is_object($bank_account_detail) OR empty($bank_account_detail) ){
            display_error('Please select Bank Account');
//             $Ajax->activate('bank_account');
            return;
        }
        if ($destination)
            include_once(ROOT . "/reporting/includes/excel_report.inc");
        else
            include_once(ROOT . "/reporting/includes/pdf_report.inc");

        $params =   array( $comments,$bank_account_detail->bank_account_name." - ".$bank_account_detail->bank_curr_code);

        $this->rep = new FrontReport(_('BANK RECONCILIATION AS AT '.sql2date($reconcile_date)), "BankReconcile", user_pagesize(), 9, $orientation);

        $this->rep->SetHeaderType('BankReconcile');

        $this->reconcile_report_table['amount'][0] = $this->reconcile_report_table['total'][0] = curr_default();
        list ($headers, $cols, $aligns) = $this->report->report_front_params($this->reconcile_report_table);
        if ($orientation == 'L')
            recalculate_cols($cols);

        $this->rep->Font();
        $this->rep->Info($params, $cols, $headers, $aligns);
        $this->rep->NewPage();

        $summary =  $this->reconciled_model->get_max_reconciled($reconcile_date, $bank_acc);
        $total_check = ($summary->total);

        $this->rep->TextCol3(0, 3, _('Balance as per Bank Account'),1);
        $this->rep->TextCol(4, 5, number_total($summary->total));
        $this->rep->NewLine(2);
        $this->rep->TextCol3(0, 3, _('ADD: Unpresendted Cheque'),1);

        $credit_amount = 0;
        if( count($bank_reconcile) > 0 ) foreach ($bank_reconcile AS $k=>$tran){

            if($tran->amount < 0){
                $this->rep->NewLine();
                $credit_amount += abs($tran->amount);
                $this->reconcile_print_line($tran);
                unset($bank_reconcile[$k]);
            }
        }

        $this->rep->NewLine(1);
        $this->rep->TextNum(4, 5,$credit_amount);
        $this->rep->UnderlineCell(4,5);
        $this->rep->NewLine(2);
        $this->rep->TextNum(4, 5,$total_check += $credit_amount);

        $this->rep->NewLine(1);
        $this->rep->TextCol3(0, 3, _('LESS: Deposit not credited by Bank'),1);

        $debit_amount = 0;
        if( count($bank_reconcile) > 0 ) foreach ($bank_reconcile AS $k=>$tran){

            if($tran->amount > 0){
                $this->rep->NewLine();
                $debit_amount += abs($tran->amount);
                $this->reconcile_print_line($tran);
                unset($bank_reconcile[$k]);
            }
        }
        $this->rep->NewLine();
        $this->rep->TextNum(4, 5,$debit_amount);
        $this->rep->UnderlineCell(4,5);
        $this->rep->NewLine(2);
        $this->rep->TextCol3(0, 3, _('Balance as per Bank statement'),1);
        $this->rep->TextNum(4, 5,$total_check -= $debit_amount);

        $this->rep->NewLine();
        $this->rep->End();
        die();
    }
}