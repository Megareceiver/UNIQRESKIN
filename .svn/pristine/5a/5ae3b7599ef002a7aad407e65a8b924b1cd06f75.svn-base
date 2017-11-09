<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SalesReportPayment  extends ci {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->input = $ci->input;
//         $this->report = $ci->module_control_load('report',null,true);
        $this->customer_trans_model = $this->model('customer_trans',true);
		$this->sys_model = $this->model('config',true);
		$this->bank_model = $this->model('bank_account',true);
		$this->contact_model = $this->model('crm',true);
		$this->sale_order_model = $this->model('sale_order',true);
		$this->common_model = $this->model('common',true);

		$this->report = module_control_load('report','report');

    }

    function  customer_payment($pdf){


        $from =         input_val('PARAM_0');
        $to =           input_val('PARAM_1');
        $currency =     input_val('PARAM_2');
        $comments =     input_val('PARAM_3');
        $orientation =  input_val('PARAM_4');

        $start_date =   input_val('PARAM_5');
        if( !is_date($start_date) ){
            $start_date = null;
        } else {
            $start_date = date('Y-m-d',strtotime($start_date));
        }

        $end_date = input_val('PARAM_6');
        if( !is_date($end_date) ){
            $end_date = null;
        } else {
            $end_date = date('Y-m-d',strtotime($end_date));
        }

        $reference = input_val('PARAM_7');

        if (!$from || !$to)
            return;
        $orientation = ($orientation ? 'L' : 'P');
        $dec = user_price_dec();

        $fno = explode("-", $from);
        $tno = explode("-", $to);
        $from = min($fno[0], $tno[0]);
        $to = max($fno[0], $tno[0]);
//         $export = true;

        $this->rep = $this->report->front_report(_('PAYMENT'),$this->payment_report_table);
        $this->rep->SetHeaderType('TemplateInvoicePayment');

        for ($i = $from; $i <= $to; $i++) {
            if ($fno[0] == $tno[0])
                $types = array($fno[1]);
            else
                $types = array(ST_BANKDEPOSIT, ST_CUSTPAYMENT);

            foreach ($types as $j) {
                $order = $this->sale_order_model->get_receipt_CP($j, $i,$start_date,$end_date,$reference);
                if ( empty($order)){
                    $export = false;
                    continue;
                }
                $this->pdf_print($order);

            }
//             if($export ){
//                 $pdf->make_report();
//             } else {
//                 $export = true;
//             }

        }

        $this->rep->End();
    }

    var $payment_report_table = array(
        'space1'=>array(' ',2),
        'trans_type'=>array('Tran Type',80),
        'reference'=>array('#' ,160),
        'tran_date'=>array('Date' ,220,'center'),
        'due_date'=>array('Due Date',270,'center'),
        'price'=>array('Total Amount',350,'right'),
        'left_alloc'=>array('Left to Allocate',450,'right'),
        'total'=>array('Total',515,'right'),

    );

    private function pdf_print($tran=NULL){
        $this->rep->params = array(
            'comments' => input_val('PARAM_5'),
            'bankaccount'=>0,
            'tran_date'=>sql2date($tran->tran_date),
            'reference'=>$tran->reference,
            'payment_terms'=>$tran->terms_name,
            'delivery_info'=>array(
                'Order To'=>$this->rep->print_company(),
                'Deliver To'=>$tran->name
            ),
            'contact'=>$this->contact_model->get_branch_contacts($tran->branch_code,'invoice',$tran->debtor_no),
            'aux_info' => array (
                "Cus Ref"          =>  $tran->cust_ref2,
                _ ( "Type" )            =>  "",
                _('Your GST no.')               =>  $tran->gst_no,
                _ ( "Our Invoice No." )         =>  $tran->trans_no,
                _ ( "Due Date" )                =>  $tran->tran_date ? sql2date($tran->tran_date) : null,
                "Cheque No"=>$tran->cheque
            )
        );

        if( strlen($tran->address) > 0 ){
            $this->rep->params['delivery_info']['Deliver To'] .= "\n".$tran->address;
        }
        $items = $this->sale_order_model->get_allocations_for_receipt($tran->debtor_no,$tran->type,$tran->trans_no);
        $this->rep->NewPage();
        $this->rep->TextCol(1, 7,	"As advance / full / part / payment towards:", -2);
        $this->rep->NewLine();

        if( !empty($items) ){


            $Total = $discountTotal = $left_alloc = 0;
            foreach ($items AS $detail){
                $Total += $detail->price;
                $left_alloc += $detail->left_alloc;
                $this->rep->TextCol(1, 2,	tran_name($detail->trans_type), -2);
                $this->rep->TextCol(2, 3, $detail->reference, 0);
                $this->rep->TextCol(3, 4,	sql2date($detail->tran_date));
                $this->rep->TextCol(4, 5,	sql2date($detail->due_date), -2);
                $this->rep->TextCol(5, 6,	number_total($detail->price), -2);
                $this->rep->TextCol(6, 7,	number_total($detail->left_alloc), -2);
                $this->rep->TextCol(7, 8,	number_total($detail->price-$detail->left_alloc));

            }


            $this->rep->row = $this->rep->bottomMargin + 8.5 * $this->rep->lineHeight;
            $this->rep->aligns[3] = 'right';

            $this->rep->TextCol(1, 5,	$this->rep->company['curr_default'].":".price_in_words( $Total ,ST_CUSTPAYMENT));

            $this->rep->TextCol(5, 7,	_('TOTAL RECEIPT'));
            $this->rep->TextCol(7, 8,	number_total($Total));

            $this->rep->NewLine(-1);
            $this->rep->TextCol(3, 7,	_('Total Discount'));
            $this->rep->TextCol(7, 8,	number_total($discountTotal));

            $this->rep->NewLine(-1);
            $this->rep->TextCol(3, 7,	_('Left to Allocate'));
            $this->rep->TextCol(7, 8,	number_total($left_alloc));

            $this->rep->NewLine(-1);
            $this->rep->TextCol(3, 7,	_('Total Allocated'));
            $this->rep->TextCol(7, 8,	number_total($Total-$left_alloc));
        }

    }

    private function pdf_print2($tran=NULL){

        $contact = $this->contact_model->get_branch_contacts($order->branch_code,'order',$order->debtor_no,true);

        $total_allocated = 0;

        $pdf->order = array(
            'curr_code'=>$order->curr_code,
            'debtor'=>null,
            'debtor_no'=>$order->debtor_no,
            'name'=>$order->DebtorName,
            'delivery'=>$order->DebtorName,
            'delivery_address'=>$order->address,
            'date'=>date('d-m-Y',strtotime($order->tran_date)),
            'contact' => (array)$contact,
            'order_no'=>$order->reference,
            'reference'=>$order->reference,
            'tax_included'=>'tax_included',
            'payment_terms'=>$order->terms_name,
            'shipping'=>'freight_cost',
            'trans_no'=>'order_no',
            'curr_code'=>$order->curr_code ,
            'total_words'=>price_in_words(0, ST_CUSTPAYMENT),
            'total_allocated'=> $order->alloc,
            'left_alloc'=>0,
            'discount'=>$order->ov_discount,
            'total_receipt'=>$order->alloc-$order->ov_discount,

        );

        $pdf->order_html = $this->ci->view('reporting/order',$pdf->order,true);

        $aux_info = array (
            _ ( "Cus Ref" ) =>     array('w'=>16.66,'val'=>$order->debtor_ref),
            _ ( "Type " )         =>     array('w'=>16.66,'val'=>'Customer Payment'),
            _('Your GST no.')            =>     array('w'=>16.66,'val'=>$order->gst_no),
            _ ( "Our Order No." )        =>     array('w'=>16.66,'val'=>$order->trans_no),
            _ ( "Due Date" )        =>     array('w'=>16.66,'val'=> $order->tran_date ? $this->ci->form->date_format(array('time'=>$order->tran_date)) : null ),
            _ ( "Cheque No" )        =>     array('w'=>16.66,'val'=> $order->cheque ),
        );

        $pdf->author_html = $this->ci->view('reporting/aux_info',array('items'=>$aux_info),true);
        $pdf->items = $this->sale_order_model->get_allocations_for_receipt($order->debtor_no,$order->type,$order->trans_no);

        $pdf->items_view = array(
            'trans_type'=>array('title'=>'Trans Type','w'=>15,'class'=>'boleft'),
            'reference'=>array('title'=>'#' ,'w'=>15),
            'tran_date'=>array('title'=>'Date' ,'w'=>15,'class'=>'textcenter'),
            'due_date'=>array('title'=>'Due Date','w'=>15,'class'=>'textright'),
            'price'=>array('title'=>'Total Amount','w'=>18,'class'=>'textright'),
            'left_alloc'=>array('title'=>'Left to Allocate','w'=>22,'class'=>'textright')
        );
    }


}