<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SalesReportQuotation{
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->input = $ci->input;
//         $this->report = $ci->module_control_load('report',null,true);
        $this->customer_trans_model = $ci->model('customer_trans',true);
		$this->sys_model = $ci->model('config',true);
		$this->bank_model = $ci->model('bank_account',true);
		$this->contact_model = $ci->model('crm',true);
		$this->sale_order_model = $ci->model('sale_order',true);
		$this->common_model = $ci->model('common',true);

    }

    function quotation_print($pdf){
        
	    $from = 		input_val('PARAM_0');
	    $to = 			input_val('PARAM_1');

	    if (! $from || ! $to)
	        return;
	    $fno = explode ( "-", $from );
	    $tno = explode ( "-", $to );
	    $from = min ( $fno [0], $tno [0] );
	    $to = max ( $fno [0], $tno [0] );


	    $trans_where = array();
	    $currency = 	input_val('PARAM_2');
	    $email = 		input_val('PARAM_3');
	    $comments = 	input_val('PARAM_4');
	    $orientation = 	input_val('PARAM_5') ? 'L' : 'P' ;

	    $start_date =	input_val('PARAM_6');
	    if( is_date($start_date) ){
	        $trans_where['sorder.ord_date >='] = date('Y-m-d',strtotime($start_date));
	    }

	    $end_date = 	input_val('PARAM_7');
	    if( is_date($end_date) ){
	        $trans_where['sorder.ord_date <='] = date('Y-m-d',strtotime($end_date));
	    }

	    $reference = input_val('PARAM_8');
	    if( $reference ){
	        $trans_where['sorder.reference'] = $reference;
	    }
	    $limit = 1;
	    for ($i = $from; $i <= $to; $i++) {

// 	        $cus_trans = $this->customer_trans_model->search_invoice(ST_SALESQUOTE,$i,$trans_where);
	        $cus_trans = $this->sale_order_model->get_order($i, ST_SALESQUOTE,$trans_where);
            if( !is_object($cus_trans) )
                continue;

//             bug($cus_trans);
// 	        die('go here');

	        if( empty($cus_trans) || !isset($cus_trans->debtor_no) ) {
	            continue;
	        }

	        $this->bankacc = $this->bank_model->get_default_account($cus_trans->curr_code);

	        $pdf->items_view = array(
	            'stk_code'=>array('title'=>'Item Code','w'=>15,'boleft'=>1),
	            'description'=>array('title'=>'Item Description' ,'w'=>35),
	            'qty'=>array('title'=>'Quantity' ,'w'=>10,'class'=>'textcenter'),
	            'units'=>array('title'=>'Units','w'=>10,'class'=>'textright'),
	            'price'=>array('title'=>'Unit Price','w'=>15,'class'=>'textright'),
	            'discount_percent'=>array('title'=>'Discount %','w'=>15,'class'=>'textcenter'),
	        );
// 	        $items = $this->customer_trans_model->trans_detail('*',array('debtor_trans_type'=>ST_SALESQUOTE,'debtor_trans_no'=>$i),ST_SALESQUOTE);
// 	        $pdf->items = $items;
	        $pdf->items = $this->sale_order_model->get_order_details($i,ST_SALESQUOTE,false);

	        if( empty($pdf->items) ){
	            continue;
	        }



	        $pdf->content_view = 'content-invoice';

	        $contacts = $this->contact_model->get_branch_contacts($cus_trans->branch_code,'invoice',$cus_trans->debtor_no);

	        $pdf->order = array(
	            'debtor'=>null,
	            'debtor_no'=>null,
	            'name'=>$cus_trans->name,
	            'date'=>sql2date($cus_trans->ord_date),
	            'contact' => (array)$contacts,
	            'delivery_addr'=>$cus_trans->delivery_address,
	            'quotation_no'=>$cus_trans->reference,
	            'reference'=>$cus_trans->reference,
	            'tax_included'=>$cus_trans->tax_included,
	            'payment_terms'=>$cus_trans->terms_name,
	            'shipping_total'=>$cus_trans->freight_cost,
	            'order_ex_gst'=>true,
	            'amount_total_val'=>$cus_trans->total,
	            'amount_total_title'=>'TOTAL ORDER GST INCL.',
	            'curr_code'=>$cus_trans->curr_code

	        );

	        if( isset($cus_trans->terms_name) && $cus_trans->terms_name ){
	            $pdf->tcpdf->table_header_payment_terms ='<p class="paymen_terms" >Payment Terms: '.$cus_trans->terms_name.'</p>';
	        }
	        $pdf->order_html =$this->ci->view('reporting/order/sale_delivery',$pdf->order,true);

	        $deliveries = get_sales_parent_numbers ( ST_SALESINVOICE, $cus_trans->order_no );
	        foreach ( $deliveries as $n => $delivery ) {
	            $deliveries [$n] = get_reference ( ST_SALESINVOICE, $delivery );
	        }


	        $aux_info = array (
	            _ ( "Customer's Reference" ) => array('w'=>20,'val'=>$cus_trans->customer_ref),
	            _ ( "Sales Person" ) => 		array('w'=>20,'val'=>$this->contact_model->get_salesman($cus_trans->salesman,'salesman_name')),
	            _('Your GST no.')=>				array('w'=>20,'val'=>$cus_trans->tax_id),
	            _ ( "Our Quotation No" ) => array('w'=>20,'val'=>$cus_trans->order_no),
	            _ ( "Valid until" ) => 			array('w'=>20,'val'=> $cus_trans->delivery_date ? $this->ci->form->date_format(array('time'=>$cus_trans->delivery_date)) : null  ),
	        );
	        $pdf->author_html = $this->ci->view('reporting/aux_info/quotation',array('items'=>$aux_info),true);

	        $pdf->make_report();
	        $limit++;


	    }
	}
}