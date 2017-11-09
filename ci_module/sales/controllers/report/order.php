<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once(ROOT . "/reporting/includes/pdf_report.inc");

class SalesReportOrder  extends ci {
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

    function order_print($pdf){

        $order_where = array();
        $from = 		input_val('PARAM_0');
        $to = 			input_val('PARAM_1');

        if (!$from || !$to) return;

        $max_id = max($from,$to);
        $min_id = min($from,$to);
        $from = $min_id;
        $to = $max_id;

        $currency =		input_val('PARAM_2');
        $email = 		input_val('PARAM_3');
        $print_as_quote = input_val('PARAM_4');
        $comments = input_val('PARAM_5');
        $orientation = input_val('PARAM_6') ? 'L' : 'P';

        $start_date = input_val('PARAM_7');
        if( $start_date ){
            $order_where['sorder.ord_date >='] = date('Y-m-d',strtotime($start_date));
        }

        $end_date = 	input_val('PARAM_8');
        if( $end_date ){
            $order_where['sorder.ord_date <='] = date('Y-m-d',strtotime($end_date));
        }

        $reference = input_val('PARAM_9');
        if( $reference ){
            $order_where['sorder.reference'] = $reference;

        }


        $this->rep = $this->report->front_report(_('SALES ORDER'),$this->invoice_report_table,ST_SALESORDER);

        $this->rep->SetHeaderType('TemplateInvoice');

        for ($i = $from; $i <= $to; $i++) {

            $order = $this->sale_order_model->get_order($i, ST_SALESORDER,$order_where);
            if( !$order || empty($order) ){
                continue;
            }
            $this->pdf_print($order);
        }
        $this->rep->End();

    }


    var $invoice_report_table = array(
        'space1'=>array(' ',2),
        'stock_id'=>array('Item Code',60),
        'description'=>array('Item Description' ,225),
        'qty'=>array('Quantity' ,300,'center'),
        'units'=>array('Units',325,'center'),
        'price'=>array('Unit Price',385,'right'),
        'discount_percent'=>array('Discount %',450,'right'),
        'total'=>array('Total',515,'right'),

    );

    private function pdf_print($tran){

        $this->rep->params = array(
            'comments' => input_val('PARAM_5'),
            'bankaccount'=>0,
            'tran_date'=>sql2date($tran->ord_date),
            'reference'=>$tran->reference,
            'payment_terms'=>$tran->terms_name,
            'delivery_info'=>array(
                'Order To'=>$this->rep->print_company(),
                'Deliver To'=>$tran->deliver_to
            ),
            'contact'=>$this->contact_model->get_branch_contacts($tran->branch_code,'invoice',$tran->debtor_no),
            'aux_info' => array (
                "Customer's Reference"          =>  NULL,
                _ ( "Sales Person" )            =>  $this->contact_model->get_salesman($tran->salesman,'salesman_name'),
                _('Your GST no.')               =>  $tran->tax_id,
                _ ( "Our Order No." )         =>  $tran->order_no,
                _ ( "Delivery Date" )                =>  $tran->delivery_date ? sql2date($tran->delivery_date) : null,
            )
        );

        if( strlen($tran->delivery_address) > 0 ){
            $this->rep->params['delivery_info']['Deliver To'] .= "\n".$tran->delivery_address;
        }

        $items = $this->sale_order_model->get_order_details($tran->order_no,ST_SALESORDER,false);
        if( empty($items) ){
            return;
        }

        $this->rep->NewPage();
        $sign = 1;
        $this->report->discount = 0;
        foreach ($items AS $detail){
            
            $line_price = $detail->unit_price * $detail->qty;
            $Net = number_total($sign * ((1 - $detail->discount_percent) * $line_price));
            $this->report->discount += strtonumber($line_price) - strtonumber($Net);

            if( $detail->tax_type_id ){

                $tax = tax_calculator($detail->tax_type_id,$line_price,$tran->tax_included);
                if( is_object($tax) ){

                    if( !isset($this->report->taxes[$detail->tax_type_id]) ){
                        $this->report->taxes[$detail->tax_type_id] = array('name'=>$tax->name ." (".$tax->code." ".$tax->rate."%)" ,'amount'=>0);
                    }
                    $this->report->taxes[$detail->tax_type_id]['amount'] += $tax->value;
                    $this->report->subTotal += $tax->price;
                    $this->report->taxTotal += $tax->value;
                } else {
                    $this->report->subTotal += $Net;
                }
            } else {

                $this->report->subTotal += $Net;
            }


            $this->rep->TextCol(1, 2,   $detail->stk_code, -2);
            $this->rep->TextCol(2, 3,   wordwrap($detail->description,40,"\n",true), 0);
            $this->rep->TextCol(3, 4,	number_format2($sign*$detail->qty,get_qty_dec($detail->stk_code)), -21);
            $this->rep->TextCol(4, 5,	$detail->units, -2);
            $this->rep->TextCol(5, 6,	number_total($detail->unit_price), -2);
            $this->rep->TextCol(6, 7,	number_total($detail->discount_percent*100) . "%" , -2);
            $this->rep->TextCol(7, 8,	number_total($Net));
            $this->rep->NewLine();
        }

        $this->rep->row = $this->rep->bottomMargin + 8.5 * $this->rep->lineHeight;


        $this->rep->aligns[3] = 'right';
        $this->report->invoice_footer();


    }
}