<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include_once(ROOT . "/reporting/includes/pdf_report.inc");

class PurchasesReportOrder {
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

        $this->report = module_control_load('report','report');
        $this->supplier_module_model = module_model_load("supplier",'purchases');
    }

    function order_print(){
		$from = 		input_val('PARAM_0');
		$to = 			input_val('PARAM_1');

		$max_id = max($from,$to);
		$min_id = min($from,$to);
		$from = $min_id;
		$to = $max_id;

		if (!$from || !$to)
			return;

		$query_where = array();

		$currency = 	input_val('PARAM_2');
		$email = 		input_val('PARAM_3');

		if( $email ){
            $this->pdf->email = true;

		}

		$comments = 	input_val('PARAM_4');
		$orientation = 	input_val('PARAM_5') ? 'L' : 'P';

		$start_date =	input_val('PARAM_6');
		if( is_date($start_date) ){
			$query_where['o.ord_date >='] = date('Y-m-d',strtotime($start_date));
		}

		$end_date = 	input_val('PARAM_7');
		if( is_date($end_date) ){
			$query_where['o.ord_date <='] = date('Y-m-d',strtotime($end_date));
		}

		$reference = 	input_val('PARAM_8');
		if( $reference ){
			$query_where['reference'] = $reference;
		}
		$this->rep = $this->report->front_report(_('Purchase Order'),$this->order_report_table);
		$this->rep->SetHeaderType('TemplateInvoice');

		for ($i = $from; $i <= $to; $i++) {

			$query_where['order_no'] = $i;
			$order = $this->purchase_model->search($query_where);

			if( !$order || !$order->order_no ){
				continue;
			}
			$this->pdf_print($order);

		}
		$this->rep->End();
	}


	var $order_report_table = array (
	    'space1'=>array(' ',2),
	    'item_code'=>array('Item Code',60),
	    'description'=>array('Item Description' ,195),
	    'delivery_date'=>array('Delivery Date' ,250),
	    'quantity'=>array('Quantity' ,325,'center'),
	    'unit_price'=>array('Units',385,'center'),
	    'price'=>array('Unit Price',425,'right'),
	    'total'=>array('Total',515,'right')

	);

	private function pdf_print($tran){

	    $this->rep->params = array(
	        'comments' => input_val('PARAM_4'),
	        'tran_date'=>sql2date($tran->ord_date),
	        'reference'=>$tran->reference,
	        'payment_terms'=>$tran->payment_terms_name,
	        'delivery_info'=>array(
	            'Order To'=>NULL,
	            'Deliver To'=>$this->rep->company['coy_name']."\n".$this->rep->company['postal_address']
	        ),
	        'contact'=>array('address'=>$tran->address),
	        'aux_info' => array (
	            "Supplier Reference"          =>  $tran->requisition_no,
	            _ ( "Sales Person" )            =>  NULL,
	            _('Your GST no.')               =>  $tran->tax_id,
	            _ ( "Invoice Date" )         =>  sql2date($tran->ord_date),
	            _ ( "Self Bill Approval Ref" )                =>  "",
	        )
	    );
	    $supplier = $this->supplier_module_model->get_detail($tran->supplier_id);
	    if( is_object($supplier) ){
	        $this->rep->params['delivery_info']["Order To"] = $supplier->supp_name;
	        $this->rep->params['delivery_info']["Order To"].= "\n".$supplier->address;
	    }

	    $customer_ref = null;
	    if( empty($tran->items) ){
	        return;
	    } else {
	        $this->rep->NewPage();
	        $sign = 1;
	        $SubTotal = $discountTotal = $shippingTotal = $taxTotal = 0;
	        $taxes = array();
	        foreach ($tran->items AS $detail){
	            $line_price = $detail->unit_price * $detail->qty;
	            $Net = $line_price;
	            $discountTotal += $line_price -$Net;
	            $SubTotal += $Net;

	            if( $detail->tax_type_id ){
	                $tax = tax_calculator($detail->tax_type_id,$line_price,$tran->tax_included);

	                if( is_object($tax) ){
	                    if( !isset($taxes[$detail->tax_type_id]) ){
	                        $taxes[$detail->tax_type_id] = array('name'=>$tax->name ." (".$tax->code." ".$tax->rate."%)" ,'amount'=>0);
	                    }
	                    $taxes[$detail->tax_type_id]['amount'] += $tax->value;
	                    $taxTotal += $tax->value;
	                }
	            }

	            $this->rep->TextCol(1, 2,   $detail->item_code, -2);
	            $this->rep->TextCol(2, 3,   wordwrap($detail->description,40,"\n",true), 0);
	            $this->rep->TextCol(3, 4,	sql2date($detail->delivery_date));
	            //$this->rep->TextCol(4, 5,	number_format2($sign*$detail->quantity_ordered,get_qty_dec($detail->item_code)));
	            $this->rep->TextCol(4, 5,	number_format2($sign*$detail->qty,get_qty_dec($detail->item_code)));
	            $this->rep->TextCol(5, 6,	$detail->units, -2);
	            $this->rep->TextCol(6, 7,	number_total($detail->unit_price), -2);
	            $this->rep->TextCol(7, 8,	number_total($Net));
	            $this->rep->NewLine();
	        }

	        $this->rep->row = $this->rep->bottomMargin + 8.5 * $this->rep->lineHeight;


	        $this->rep->aligns[3] = 'right';

	        $this->rep->TextCol(1, 5,	$this->rep->company['curr_default'].":".price_in_words( $tran->tax_included ? $SubTotal :$SubTotal+$taxTotal ,ST_CUSTPAYMENT));
	        $this->rep->Font('bold');

	        if( $taxTotal > 0 ){

	            $this->rep->TextCol(3,7,	_('TOTAL ORDER INCL. GST'));
	            $this->rep->TextCol(7, 8,	number_total( $tran->tax_included ? $SubTotal :$SubTotal+$taxTotal ));


	            $this->rep->NewLine(-1);
	            $this->rep->TextCol(3, 7,	_('TOTAL ORDER EX GST'));
	            $this->rep->TextCol(7, 8,	number_total($tran->tax_included ? $SubTotal-$taxTotal: $SubTotal));
	        } else {
	            $this->rep->TextCol(3, 7,	_('TOTAL ORDER'));
	            $this->rep->TextCol(7, 8,	number_total($SubTotal));
	        }



	        $this->rep->Font();
	        if( abs($discountTotal) != 0 ){
	            $this->rep->NewLine(-1);
	            $this->rep->TextCol(3, 7,	_('Discount Given'));
	            $this->rep->TextCol(7, 8,	number_total($discountTotal));
	        }



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



// 	    $contacts = $this->contact_model->get_supplier_contacts($order->supplier_id,'order');
// 	    $this->bankacc = $this->bank_model->get_default_account($order->curr_code);

// 	    $pdf->order = array(
// 	        'date'=>date('d-m-Y',strtotime($order->ord_date)),
// 	        'purchase_order'=>$order->reference,
// 	        'reference'=>$order->reference,
// 	        'name'=>$order->supp_name,
// 	        'delivery'=>$pdf->company['name'],
// 	        'tax_included'=>$order->tax_included,
// 	        'payment_terms'=>$order->payment_terms_name,
// 	        'amount_total'=>$order->total

// 	    );
// 	    // 			bug($order);die;
// 	    $order_info = array();
// 	    // 			if( count($contacts) >0 ){
// 	    // 				$pdf->order['contact'] = (array)$contacts[0];
// 	    $order_info['contact'] = (array)$contacts[0];


// 	    $order_info['company'] = array('name'=>trim($order->location_name),'address'=>$order->delivery_address,'email'=>$order->local_email,'phone'=>$order->local_phone);
// 	    $order_info['name'] = trim($order->supp_name);

// 	    // 			}

// 	    $pdf->order_html = $this->ci->view('reporting/order/purchase-order',$order_info,true);

// 	    $aux_info = array (
// 	        _ ( "Customer's Reference" ) => array('w'=>20,'val'=>$order->supp_account_no),
// 	        _ ( "Sales Person" ) => 		array('w'=>20,'val'=>null),
// 	        _('Your GST no.')=>				array('w'=>20,'val'=>$order->tax_id),
// 	        _ ( "Supplier's Reference" ) => array('w'=>20,'val'=>$order->requisition_no),
// 	        _ ( "Order Date" ) => 			array('w'=>20,'val'=>$pdf->order['date']),
// 	    );
// 	    $pdf->author_html = $this->ci->view('export/aux_info',array('items'=>$aux_info),true);

// 	    $pdf->items_view = array(
// 	        'item_code'=>array('title'=>'Item code' ,'w'=>12,'class'=>'textcenter','ite_class'=>'default'),
// 	        'description'=>array('title'=>'Item Description','w'=>34),
// 	        'delivery_date'=>array('title'=>'Delivery Date','w'=>15,'class'=>'textcenter'),
// 	        'qty'=>array('title'=>'Quantity','w'=>10,'class'=>'textcenter'),
// 	        'units'=>array('title'=>'Unit','w'=>14,'class'=>'textcenter'),
// 	        'price'=>array('title'=>'Price','w'=>15,'class'=>'textright'),
// 	    );

// 	    $items = $order->items;
// 	    $pdf->items = $items;

// 	    $pdf->make_report();
// 	    $limit++;
	}
}