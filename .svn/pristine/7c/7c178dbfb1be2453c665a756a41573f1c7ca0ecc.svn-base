<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//class Sale extends CI_Controller {
class Sale extends ci {
	function __construct() {
		global $ci;
		$this->ci = $ci;
		if( !isset($ci->pdf) ){
		    $ci->load_library('reporting');
		}

		if( isset($ci->pdf) ){
		    $this->tcpdf = $ci->pdf->tcpdf;
		    $this->pdf = $ci->pdf;
		}



		$this->customer_trans_model = $this->model('customer_trans',true);
		$this->sys_model = $this->model('config',true);
		$this->bank_model = $this->model('bank_account',true);
		$this->contact_model = $this->model('crm',true);
		$this->sale_order_model = $this->model('sale_order',true);
		$this->common_model = $this->model('common',true);
	}

	function invoice_print(){

        $pdf = $this->ci->reporting;
		$currency_default = $this->sys_model->curr_default();
		$from = 		input_val('PARAM_0');
		$to = 			input_val('PARAM_1');
		$fno = explode("-", $from);
		$tno = explode("-", $to);
		$from = min($fno[0], $tno[0]);
		$to = max($fno[0], $tno[0]);

		$trans_where = array();
		$currency = 	input_val('PARAM_2');
		$email = 		input_val('PARAM_3');
		$pay_service = 	input_val('PARAM_4');
		$comments = 	input_val('PARAM_5');
		$customer = 	input_val('PARAM_6');
		$orientation = 	input_val('PARAM_7') ? 'L' : 'P' ;


		$start_date =	input_val('PARAM_8');
		if( $start_date ){
			$trans_where['tran_date >='] = date('Y-m-d',strtotime($start_date));
		}

		$end_date = 	input_val('PARAM_9');
		if( $end_date ){
			if( $start_date &&  strtotime($start_date) < strtotime($end_date))
				$trans_where['tran_date <='] = date('Y-m-d',strtotime($end_date));
		}

		$reference = input_val('PARAM_10');
		if( $reference ){
			$trans_where['reference'] = $reference;
		}
		$limit = 1;

		for ($i = $from; $i <= $to; $i++) {
			if (!exists_customer_trans(ST_SALESINVOICE, $i)) continue;

			$sign = 1;
			$cus_trans = $this->customer_trans_model->search_invoice(ST_SALESINVOICE,$i,$trans_where);

			if( empty($cus_trans) || !isset($cus_trans->debtor_no) || ( $customer && $cus_trans->debtor_no != $customer) ) {
				continue;
			}
			$this->bankacc = $this->bank_model->get_default_account($cus_trans->curr_code);
			$pdf->items = $this->customer_trans_model->trans_detail('*',array('debtor_trans_type'=>ST_SALESINVOICE,'debtor_trans_no'=>$i));
			if( empty($pdf->items) ){
			    continue;
			}

			if( input_val('theme')=='invoice' ){
                return $this->invoice_tax($cus_trans);
			} else {
			    $pdf->items_view = array(
			        'stock_id'=>array('title'=>'Item Code','w'=>15),
			        'description'=>array('title'=>'Item Description' ,'w'=>35),
			        'qty'=>array('title'=>'Quantity' ,'w'=>10,'class'=>'textcenter'),
			        'units'=>array('title'=>'Units','w'=>10,'class'=>'textright'),
			        'price'=>array('title'=>'Unit Price','w'=>15,'class'=>'textright'),
			        'discount_percent'=>array('title'=>'Discount %','w'=>15,'class'=>'textcenter'),
			    );
			    $pdf->content_view = 'content-invoice';
			    $contacts = $this->contact_model->get_branch_contacts($cus_trans->branch_code,'invoice',$cus_trans->debtor_no);
			    $pdf->order = array(
			        'curr_code'=>$cus_trans->curr_code,
			        'debtor'=>null,
			        'debtor_no'=>null,
			        'name'=>$cus_trans->DebtorName,
			        'address'=>$cus_trans->address,
			        'date'=>date('d-m-Y',strtotime($cus_trans->tran_date)),
			        'contact' => (array)$contacts,
			        'invoice_no'=>$cus_trans->reference,
			        'reference'=>$cus_trans->reference,
			        'tax_included'=>$cus_trans->tax_included,
			        'payment_terms'=>$cus_trans->payment_terms_name,
			        'shipping'=>$cus_trans->ov_freight

			    );
			    $pdf->order_html =$this->ci->view('reporting/order/invoice',$pdf->order,true);
			    $deliveries = get_sales_parent_numbers ( ST_SALESINVOICE, $cus_trans->trans_no );
			    foreach ( $deliveries as $n => $delivery ) {
			        $deliveries [$n] = get_reference ( ST_CUSTDELIVERY, $delivery );
			    }

			    $customer_ref = null;
			    if( $cus_trans->order_ ){
			        $customer_ref = $this->sale_order_model->get_field($cus_trans->order_,'customer_ref');
			    }
			    $aux_info = array (
			        _ ( "Customer's Reference" ) => array('w'=>20,'val'=>$customer_ref),
			        _ ( "Sales Person" ) => 		array('w'=>20,'val'=>$this->contact_model->get_salesman($cus_trans->salesman,'salesman_name')),
			        _('Your GST no.')=>				array('w'=>20,'val'=>$cus_trans->tax_id),
			        _ ( "Our Invoice No." ) => array('w'=>20,'val'=>$cus_trans->trans_no),
			        _ ( "Due Date" ) => 			array('w'=>20,'val'=> $cus_trans->due_date ? sql2date($cus_trans->due_date) : null  ),
			    );
			    $pdf->author_html = $this->ci->view('reporting/aux_info',array('items'=>$aux_info),true);

			    $pdf->make_report();
			}

			$limit++;
		}
	}

	private function invoice_docx(){
	    // Create a new PHPWord Object
	    $PHPWord = new PHPWord();
	    $section = $PHPWord->createSection();

	    $section->addText('Hello world!');

	    $objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
// 	    $objWriter->save('helloWorld.docx');

// 	    $objWriter = PHPWord_IOFactory::createWriter( $phpword_object, "Word2007" );
	    $objWriter->save( "php://output" );

	}

	function order_print($pdf){

		$order_where = array();
		$from = 		$pdf->inputVal('PARAM_0');
		$to = 			$pdf->inputVal('PARAM_1');

		if (!$from || !$to) return;

		$max_id = max($from,$to);
		$min_id = min($from,$to);
		$from = $min_id;
		$to = $max_id;

		$currency =		$pdf->inputVal('PARAM_2');
		$email = 		$pdf->inputVal('PARAM_3');
		//$email = 'legiang0212@gmail.com';
		$print_as_quote = $pdf->inputVal('PARAM_4');
		$comments = $pdf->inputVal('PARAM_5');
		$orientation = $pdf->inputVal('PARAM_6') ? 'L' : 'P';

		$start_date = $pdf->inputVal('PARAM_7');
		if( $start_date ){
			$order_where['sorder.ord_date >='] = date('Y-m-d',strtotime($start_date));
		}

		$end_date = 	$pdf->inputVal('PARAM_8');
		if( $end_date ){
			$order_where['sorder.ord_date <='] = date('Y-m-d',strtotime($end_date));
		}

		$reference = $pdf->inputVal('PARAM_9');
		if( $reference ){
			$order_where['sorder.reference'] = $reference;

		}
		$limit = 1;
		for ($i = $from; $i <= $to; $i++) {
			//if($limit > 5) break;
			$order = $this->sale_order_model->get_order($i, ST_SALESORDER,$order_where);
			if( !$order || empty($order) ){
				continue;
			}
			$pdf->items = $this->sale_order_model->get_order_details($i,ST_SALESORDER,false);
			if( !$pdf->items || empty($pdf->items) ){
				continue;
			}
                        // bug($pdf->items);die;
			$this->bankacc = $this->bank_model->get_default_account($order->curr_code);

			$pdf->items_view = array(
					'stk_code'=>array('title'=>'Item Code','w'=>15,'class'=>'boleft'),
					'description'=>array('title'=>'Item Description' ,'w'=>38),
					'qty'=>array('title'=>'Quantity' ,'w'=>10,'class'=>'textcenter'),
					'units'=>array('title'=>'Units','w'=>10,'class'=>'textright'),
					'price'=>array('title'=>'Unit Price','w'=>15,'class'=>'textright'),
			         'discount_percent'=>array('title'=>'Discount%','w'=>12,'class'=>'textright'),
			);

			$pdf->content_view = 'content-invoice';

			$contact = $this->contact_model->get_branch_contacts($order->branch_code,'order',$order->debtor_no,true);
                        //  bug($contact);die;
			$pdf->order = array(
					'debtor'=>null,
					'debtor_no'=>$order->debtor_no,
					'name'=>$order->deliver_to,
					'delivery'=>$order->deliver_to,
					'delivery_address'=>$this->ci->form->print_address(array('addr'=>$order->delivery_address)),
					'date'=>date('d-m-Y',strtotime($order->ord_date)),
					'contact' => (array)$contact,
					'order_no'=>$order->reference,
					'reference'=>$order->reference,
					'tax_included'=>$order->tax_included,
					'payment_terms'=>$order->terms_name,
					'shipping'=>$order->freight_cost,
                                        'trans_no'=>order_no,

			);

			$pdf->order_html = $this->ci->view('reporting/order',$pdf->order,true);

			$deliveries = get_sales_parent_numbers ( ST_SALESINVOICE, $order->order_no );

			foreach ( $deliveries as $n => $delivery ) {
				$deliveries [$n] = get_reference ( ST_CUSTDELIVERY, $delivery );
			}


			$aux_info = array (
					_ ( "Customer's Reference" ) => array('w'=>20,'val'=>$order->customer_ref),
					_ ( "Sales Person" ) =>     array('w'=>20,'val'=>$this->contact_model->get_salesman($order->salesman,'salesman_name')),
					_('Your GST no.')=>         array('w'=>20,'val'=>$order->tax_id),
					_ ( "Our Order No." ) =>    array('w'=>20,'val'=>$order->order_no),
					_ ( "Delivery Date" ) =>    array('w'=>20,'val'=> $order->delivery_date ? sql2date( $order->delivery_date ) : null ),
			);
			$pdf->author_html = $this->ci->view('reporting/aux_info',array('items'=>$aux_info),true);

			$pdf->make_report();
			$limit++;
		}
// 		die('iam heer');

	}



	function receipt_print($pdf){

		$from = 		$pdf->inputVal('PARAM_0');
		$to = 			$pdf->inputVal('PARAM_1');

		if (! $from || ! $to)
			return;
		$fno = explode ( "-", $from );
		$tno = explode ( "-", $to );
		$from = min( $fno [0], $tno [0] );
		$to = max( $fno [0], $tno [0] );

		if ($fno[0] == $tno[0])
			$types = array($fno[1]);
		else
			$types = array(ST_BANKDEPOSIT, ST_CUSTPAYMENT);

		$trans_where = array();
		$currency = 	$pdf->inputVal('PARAM_2');
		$comments = 	$pdf->inputVal('PARAM_3');
		$orientation = 	$pdf->inputVal('PARAM_4') ? 'L' : 'P' ;

		$start_date =	$pdf->inputVal('PARAM_5');
		if( is_date($start_date) ){
			$trans_where['tran.tran_date >='] = date2sql($start_date);
		}

		$end_date = 	$pdf->inputVal('PARAM_6');
		if( is_date($end_date) ){
			$trans_where['tran.tran_date <='] = date2sql($end_date);
		}

		$reference = $pdf->inputVal('PARAM_7');
		if( $reference ){
			$trans_where['tran.reference'] = $reference;
		}
		$limit = 1;
		for ($i = $from; $i <= $to; $i++) {
                    foreach ($types as $j){
			//if( $limit > 1 ) break;
			$trans = $this->sale_order_model->get_receipt($i, $j,$trans_where);

			if( empty($trans) )
				continue;

			$pdf->items = $this->sale_order_model->get_allocations_for_receipt( $trans->debtor_no, $trans->type, $trans->trans_no);
			if( !$pdf->items || empty($pdf->items) )
                            {
				continue;
			}

			$pdf->items_view = array(
					'type'=>array('title'=>'Trans Type','w'=>15),
					'reference'=>array('title'=>'#' ,'w'=>10,'class'=>'textcenter'),
					'tran_date'=>array('title'=>'Date' ,'w'=>15,'class'=>'textcenter'),
					'due_date'=>array('title'=>'Due Date','w'=>15,'class'=>'textcenter'),
					'total'=>array('title'=>'Total Amount','w'=>20,'class'=>'textright'),
					'left_alloc'=>array('title'=>'Left to Allocate','w'=>20,'class'=>'textright'),
			);



			$pdf->order = array(
					'comment'=>$this->common_model->get_comments($j, $i),
					'debtor'=>null,
					'debtor_no'=>$trans->debtor_no,
					'name'=>$trans->DebtorName,
					'delivery'=>'',
					//'delivery_address'=>$this->ci->form->print_address(array('addr'=>$trans->address)),
					'date'=>date('d-m-Y',strtotime($trans->tran_date)),
					'contact' => $this->contact_model->get_branch_contacts($trans->branch_code, 'invoice', $trans->debtor_no),
					//'invoice_no'=>$order->reference,
					'reference'=>$trans->reference,
					'payment_terms'=>$trans->terms_name,
					'receipt_no'=>$trans->reference,
					'curr_code'=>$trans->curr_code,
					'total_words'=>null,
					'total'=>$trans->Total,
					'left_alloc'=>$trans->Total-$trans->alloc
			);
			$pdf->order_html = $this->ci->view('reporting/order',$pdf->order,true);

			$aux_info = array (
					_( "Customer's Reference" ) => array('w'=>20,'val'=>$trans->debtor_ref),
					_( "Type" ) 				=> array('w'=>20,'val'=> $this->ci->form->trans_type(array('type'=> $trans->type))),
					_('Your GST no.')			=> array('w'=>20,'val'=>$trans->tax_id),
					_( "Our Order No" ) 		=> array('w'=>20,'val'=>$trans->order_),
					_( "Due Date" ) 			=> array('w'=>20,'val'=> $this->ci->form->date_format(array('time'=>$trans->tran_date))),
			);
			$pdf->author_html = $this->ci->view('reporting/aux_info',array('items'=>$aux_info),true);

			$pdf->make_report();
			$limit++;
		}}
	}

	private function get_items(){
		$this->items = $_SESSION['Items'];
		$this->is_batch_invoice = count($this->items->src_docs) > 1;
		$this->is_edition = $this->items->trans_type == ST_SALESINVOICE && $this->items->trans_no != 0;
	}

	function customer_invoice(){
		$this->get_items();
		$table = array(
			'stock_id'=>array('title'=>_("Item Code"),'w'=>12),
			'item_description'=>array('title'=>_("Item Description"),'class'=>'textleft'),
			'quantity'=>array('title'=>_("Delivered"),'class'=>'textcenter','w'=>5),
			'units'=>array('title'=>_("Units"),'w'=>5,'class'=>'textcenter'),
			'qty_done'=>array('title'=>_("Invoiced"),'w'=>5,'class'=>'textright'),

			'qty_dispatched'=>array('title'=>_("This Invoice"),'w'=>9,'class'=>'center'),
			'price'=>array('title'=>_("Price"),'w'=>10,'class'=>'textright'),
			'tax_type_id'=>array('title'=>_("Tax Type"),'w'=>15),
			'discount_percent'=>array('title'=>_("Discount"),'w'=>5,'class'=>'textcenter'),
			'total'=>array('title'=>_("Total"),'w'=>10,'class'=>'textright'),
			'edit'=>array('class'=>'textcenter'),
			'remove'=>array('class'=>'textcenter'),
		);
		$th = array(_("Item Code"), _("Item Description"), _("Delivered"), _("Units"), _("Invoiced"),
				_("This Invoice"), _("Price"), _("Tax Type"), _("Discount"), _("Total"));

		if ($this->is_batch_invoice) {
			$th[] = _("DN");
			$th[] = "";
		}

		if ($this->is_edition) {
			$table['qty_done']['title'] = _("Credited");

		}
		$ChargeFreightCost = 0;
		if( $this->items->any_already_delivered() != 1 ) {
			$ChargeFreightCost = $this->items->freight_cost;
			if( !check_num($ChargeFreightCost) ){
				$ChargeFreightCost = 0;
			}
		}
		$taxes = array();

		foreach ($this->items->line_items AS $ite){

		    $price = $ite->qty_dispatched * $ite->price;
			$taxes[] = tax_calculator($ite->tax_type_id,$price,$this->items->tax_included);
		}

		$view = $this->ci->view('sale/customer_invoice',array('items'=>$this->items->line_items,'table'=>$table,'ChargeFreightCost'=>$ChargeFreightCost,'taxes'=>$taxes,'tax_included'=>$this->items->tax_included),true);
		return $view;
	}



        // CHIEU


    /*
     * output PDF for Kastam 150919
     */
    private function invoice_tax($invoice){


        $this->tcpdf->startPageGroup();
//         $this->tcpdf->SetTopMargin(0);
        $margin = 3;
        $this->tcpdf->SetMargins($margin,$margin+2,$margin, true);


        $this->tcpdf->AddPage();


        $this->tcpdf->setPrintHeader(false);

        $this->tcpdf->SetAutoPageBreak(TRUE,1);
        $pdf = $this->ci->reporting;
//         bug($invoice);die;

        $gst_6_round = round($invoice->ov_gst,user_amount_dec());
        $data = array(
            'trans_no'=>$invoice->reference,
            'tran_date'=>$invoice->tran_date,
            'saleman'=>$invoice->salesman_name,
            'items'=>$this->ci->reporting->items,
            'gst_6'=>$gst_6_round,
            'rounding_adj'=>$invoice->ov_gst-$gst_6_round,
            'amount_total'=>$invoice->ov_amount + $gst_6_round,
            'y'=>$this->tcpdf->GetY()

        );
        $this->tcpdf->SetFont('algerian','',9);
//         $this->ci->pdf->writeHTML('<p style=" background-color: red;" >'.$this->ci->pdf->company['name'].' bc ddd</p>');
        $this->tcpdf->Cell(0, 0, strtoupper(trim($this->ci->pdf->company['name'])), 0, 1, 'C', 0);
        $this->ci->pdf->UpdateY(-3);
        $this->tcpdf->SetFont('pdfacourier', '', 5);

        $this->ci->smarty->assign('content_w',95);
        $this->ci->pdf->write_view('sale_invoice/mixed_supplies_with_discounts',$data);
    }

}