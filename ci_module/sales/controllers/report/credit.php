<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SalesReportCredit  extends ci {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->input = $ci->input;
        $this->customer_trans_model = $this->model('customer_trans',true);
		$this->contact_model = $this->model('crm',true);

		$this->report = module_control_load('report','report');

    }

    function cn_sale_print($pdf){


        $from =       $pdf->inputVal('PARAM_0');
		$to =         $pdf->inputVal('PARAM_1');
		$fno = explode("-", $from);
		$tno = explode("-", $to);
		$from = min($fno[0], $tno[0]);
		$to = max($fno[0], $tno[0]);
		if (!$from || !$to) return;

		$trans_where = array();
		$currency =   $pdf->inputVal('PARAM_2');
		$email =      $pdf->inputVal('PARAM_3');
		$paylink =    $pdf->inputVal('PARAM_4');
		$comments =   $pdf->inputVal('PARAM_5');
		$orientation =$pdf->inputVal('PARAM_6') ? 'L' : 'P' ;

		$start_date =	$pdf->inputVal('PARAM_7');
		if( $start_date ){
		    $trans_where['trans.tran_date >='] = date('Y-m-d',strtotime($start_date));
		}

		$end_date = 	$pdf->inputVal('PARAM_8');
		if( $end_date ){
		    if( $start_date &&  strtotime($start_date) < strtotime($end_date))
		        $trans_where['trans.tran_date <='] = date('Y-m-d',strtotime($end_date));
		}
        $reference =    $pdf->inputVal('PARAM_9');
        if( $reference ){
            $trans_where['trans.reference'] = $reference;
        }
        $limit = 1;

        $this->rep = $this->report->front_report(_('CREDIT NOTE'),$this->credit_report_table);

        $this->rep->SetHeaderType('TemplateCustemerCreditNote');

        for ($i = $from; $i <= $to; $i++) {

            if (!exists_customer_trans(ST_CUSTCREDIT, $i)) continue;
                $trans = $this->customer_trans_model->get_customer_tran(ST_CUSTCREDIT,$i,$trans_where);
            if( !$trans || !isset($trans->debtor_no)  ) {
                continue;
            }
            $this->pdf_print($trans);
        }
        $this->rep->End();

	}

	var $credit_report_table = array(
	    'space1'=>array(' ',2),
	    'stock_id'=>array('Item Code',60),
	    'description'=>array('Item Description' ,225),
	    'qty'=>array('Quantity' ,300,'center'),
	    'units'=>array('Units',325,'center'),
	    'price'=>array('Unit Price',385,'right'),
	    'discount_percent'=>array('Discount %',450,'right'),
	    'total'=>array('Total',515,'right'),

	);

	private function pdf_print($tran=NULL){

	    $this->rep->params = array(
	        'comments' => input_val('PARAM_5'),
	        'tran_date'=>sql2date($tran->tran_date),
	        'reference'=>$tran->reference,
	        'payment_terms'=>$tran->payment_terms_name,
	        'delivery_info'=>array(
	            'Order To'=>"",
	            'Deliver To'=>''
	        ),
	        'contact'=>$this->contact_model->get_branch_contacts($tran->branch_code,'invoice',$tran->debtor_no),
	        'aux_info' => array (
	            "Customer's Reference"          =>  NULL,
	            _ ( "Sales Person" )            =>  $this->contact_model->get_salesman($tran->salesman,'salesman_name'),
	            _('Your GST no.')               =>  $tran->tax_id,
	            _ ( "Our Credit Note No." )         =>  $tran->trans_no,
	            _ ( "Due Date" )                =>  strlen($tran->due_date) > 0 ? sql2date($tran->due_date) : null,
	        )
	    );

	    $this->rep->params['delivery_info']['Order To'] = $tran->DebtorName;

	    $customer_ref = null;
	    if( $tran->order_ ){
	        //$this->rep->params['aux_info']["Customer's Reference"] = $this->sale_order_model->get_field($tran->order_,'customer_ref');
	        $this->rep->params['aux_info']["Customer's Reference"] = get_field('sales_orders','customer_ref',array('order_no'=>$tran->order_));
	    }

	    $items =  $this->customer_trans_model->get_customer_trans_details(ST_CUSTCREDIT, $tran->trans_no);
	    if( empty($items) ){
	        return;
	    } else {
	        $this->rep->NewPage();
	        $sign = 1;
	        $SubTotal = $discountTotal = $shippingTotal = $taxTotal = 0;
	        $taxes = array();
	        foreach ($items AS $detail){
	            $line_price = $detail->unit_price * $detail->quantity;
	            $Net = round2($sign * ((1 - $detail->discount_percent) * $line_price), user_price_dec());
	            $discountTotal += $line_price -$Net;
	            $SubTotal += $Net;

	            if( $detail->tax_type_id ){
	                $tax = tax_calculator($detail->tax_type_id,$line_price,$tran->tax_included);

	                if( is_object($tax) ){
	                    if( !isset($taxes[$detail->tax_type_id]) ){
	                        $taxes[$detail->tax_type_id] = array('name'=>$tax->name ." (".$tax->code." ".$tax->rate."%)" ,'amount'=>0);
	                    }
	                    $taxes[$detail->tax_type_id]['amount'] += $tax->value;
	                    $taxTotal +=$tax->value;
	                }


	            }


	            $this->rep->TextCol(1, 2,	$detail->stock_id, -2);
	            $this->rep->TextCol(2, 3, wordwrap($detail->description,40,"\n",true), 0);
	            $this->rep->TextCol(3, 4,	number_format2($sign*$detail->quantity,get_qty_dec($detail->stock_id)), -21);
	            $this->rep->TextCol(4, 5,	$detail->units, -2);
	            $this->rep->TextCol(5, 6,	number_total($detail->unit_price), -2);
	            $this->rep->TextCol(6, 7,	number_total($detail->discount_percent*100) . "%" , -2);
	            $this->rep->TextCol(7, 8,	number_total($Net));
	            $this->rep->NewLine();
	        }

	        $this->rep->row = $this->rep->bottomMargin + 8.5 * $this->rep->lineHeight;


	        $this->rep->aligns[3] = 'right';

	        $this->rep->TextCol(1, 5,	$this->rep->company['curr_default'].":".price_in_words( $SubTotal ,ST_CUSTPAYMENT));

	        $this->rep->TextCol(5, 7,	_('TOTAL CREDIT'));
	        $this->rep->TextCol(7, 8,	number_total($tran->tax_included ? $SubTotal: $SubTotal+$taxTotal));

	        if( count($taxes) > 0 ) foreach ($taxes AS $tax){
                if( abs($tax['amount']) != 0 ){
                    $this->rep->NewLine(-1);
                    $this->rep->TextCol(3, 7,	$tax['name'].' Amount');
                    $this->rep->TextCol(7, 8,	number_total($tax['amount']) );
                }
            }

            if( abs($shippingTotal) != 0 ){
                $this->rep->NewLine(-1);
                $this->rep->TextCol(3, 7,	_('Shipping'));
                $this->rep->TextCol(7, 8,	number_total($shippingTotal));
            }


            $this->rep->NewLine(-1);
            $this->rep->TextCol(3, 7,	_(' Sub-total'));
            $this->rep->TextCol(7, 8,	number_total($SubTotal));


	    }




	}

}