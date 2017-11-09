<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class PurchasesReportPayment {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->input = $ci->input;
        $this->customer_trans_model = $ci->model('customer_trans',true);
		$this->purchase_model = $ci->model('purch_order',true);
		$this->purchase = $ci->model('purchase',true);
		$this->sys_model = $ci->model('config',true);
		$this->bank_model = $ci->model('bank_account',true);
		$this->contact_model = $ci->model('crm',true);
		$this->sale_order_model = $ci->model('sale_order',true);
		$this->common_model = $ci->model('common',true);
        $this->supplier_model = $ci->model('supplier',true);
        $this->supplier_trans_model = $ci->model('supplier_trans',true);

        $this->payment_report = module_model_load('payment_report','purchase');
        $this->report = module_control_load('report','report');
        
        $this->supplier_module_model = module_model_load("supplier",'purchases');

    }

    function payment_print(){

        $from =       input_val('PARAM_0');
        $to =         input_val('PARAM_1');

        $fno = explode("-", $from);
        $tno = explode("-", $to);
        $from = min($fno[0], $tno[0]);
        $to = max($fno[0], $tno[0]);
        if (!$from || !$to) return;

        $trans_where = array();
        $currency =   input_val('PARAM_2');
        $email =      input_val('PARAM_3');

        $comments =   input_val('PARAM_4');
        $orientation =input_val('PARAM_5') ? 'L' : 'P' ;

        $start_date =	input_val('PARAM_6');
        if( !is_date($start_date) ){
            $start_date = null;
        } else {
            $start_date = date('Y-m-d',strtotime($start_date));
        }

        $end_date = 	input_val('PARAM_7');
        if( !is_date($end_date) ){
            $end_date = null;
        } else {
            $end_date = date('Y-m-d',strtotime($end_date));
        }

        $reference =    input_val('PARAM_8');

        $limit = 1;

        $this->rep = $this->report->front_report(_('REMITTANCE'),$this->payment_report_table);
        $this->rep->SetHeaderType('TemplateInvoicePayment');
        $this->report->type = ST_SUPPAYMENT;

        for ($i = $from; $i <= $to; $i++){

            if ($fno[0] == $tno[0])
                $types = array($fno[1]);
            else
                $types = array(ST_BANKPAYMENT, ST_SUPPAYMENT, ST_SUPPCREDIT);

            foreach ($types as $j) {
                $trans = $this->purchase->get_purchase_tran($j,$i,$start_date,$end_date,$reference);

                if( !$trans || !$trans->supplier_id ){
                    continue;
                }
                $this->pdf_print($trans);
            }
        }

        $this->rep->End();

    }

    var $payment_report_table = array(
        'space1'=>array(' ',2),
        'trans_type'=>array(
                    'Tran Type',
                    80
            ),
            'reference' => array(
                    '#',
                    160
            ),
            'tran_date' => array(
                    'Date',
                    220,
                    'center'
            ),
            'due_date' => array(
                    'Due Date',
                    270,
                    'center'
            ),
            'price' => array(
                    'Total Amount',
                    350,
                    'right'
            ),
            'left_alloc' => array(
                    'Left to Allocate',
                    450,
                    'right'
            ),
            'total' => array(
                    'Total',
                    515,
                    'right'
            )
    )
    ;

    private function pdf_print ($tran = NULL)
    {
        
        $this->rep->params = array(
                'comments' => input_val('PARAM_5'),
                'bankaccount' => 0,
                'tran_date' => sql2date($tran->tran_date),
                'reference' => $tran->reference,
                'payment_terms' => $tran->terms,
                'delivery_info' => array(
                        'Order To' => "",
                        'Charge To' => $this->rep->print_company()
                ),
                'contact' => NULL,
                'aux_info' => array(
                        "Reference" => $tran->reference,
                        _("Type") => transaction_type_tostring($tran->type),
                        _('Your GST no.') => $tran->gst_no,
                        _("Supplier's Reference") => $tran->cheque,
                        _("Due Date") => $tran->due_date ? sql2date(
                                $tran->due_date) : null,
                        //"Cheque No" => $tran->cheque
                )
        );
        $supplier = $this->supplier_module_model->get_detail($tran->supplier_id);
        if( is_object($supplier) ){
            $this->rep->params['delivery_info']["Order To"] = $supplier->supp_name;
            $this->rep->params['delivery_info']["Order To"].= "\n".$supplier->address;
        }


        $items = $this->supplier_model->get_alloc_supp_sql_ci(
                $tran->supplier_id, $tran->type, $tran->trans_no);
        
        // $items2 = get_allocations_for_remittance($myrow['supplier_id'],
        // $myrow['type'], $myrow['trans_no']);
        
        // if( !empty($items) ){
        $this->rep->NewPage();
        $this->rep->TextCol(1, 7, "As advance / full / part / payment towards:", 
                - 2);
        $this->rep->NewLine();
        
        $Total = $discountTotal = $left_alloc = 0;
        if (! empty($items))
            foreach ($items as $detail) {
                $this->report->subTotal += $detail->price;
                $this->report->leftAllocate += $detail->left_alloc;
                
                $left_alloc += $detail->left_alloc;
                $this->rep->TextCol(1, 2, tran_name($detail->trans_type), - 2);
                $this->rep->TextCol(2, 3, $detail->reference, 0);
                $this->rep->TextCol(3, 4, sql2date($detail->tran_date));
                $this->rep->TextCol(4, 5, sql2date($detail->due_date), - 2);
                $this->rep->TextCol(5, 6, number_total($detail->price), - 2);
                $this->rep->TextCol(6, 7, number_total($detail->left_alloc), - 2);
                $this->rep->TextCol(7, 8, 
                        number_total($detail->price - $detail->left_alloc));
                $this->rep->NewLine();

            }
            else{
                $this->report->subTotal = abs($tran->Total);
                $this->report->leftAllocate = abs($tran->Total) - abs($tran->alloc);
            }


            $this->rep->row = $this->rep->bottomMargin + 8.5 * $this->rep->lineHeight;
            $this->rep->aligns[3] = 'right';
            $this->report->invoice_footer("REMITTANCE");

    }

}