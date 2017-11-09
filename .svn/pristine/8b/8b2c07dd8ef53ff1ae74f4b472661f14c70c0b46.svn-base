<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase extends ci {
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


		$this->db = $ci->db;
		$this->customer_trans_model = $this->model('customer_trans',true);
		$this->purchase_model = $this->model('purch_order',true);
		$this->purchase = $this->model('purchase',true);
		$this->sys_model = $this->model('config',true);
		$this->bank_model = $this->model('bank_account',true);
		$this->contact_model = $this->model('crm',true);
		$this->sale_order_model = $this->model('sale_order',true);
		$this->common_model = $this->model('common',true);
        $this->supplier_model = $this->model('supplier',true);
        $this->supplier_trans_model = $this->model('supplier_trans',true);


	}

	function order_print($pdf){
		$from = 		$pdf->inputVal('PARAM_0');
		$to = 			$pdf->inputVal('PARAM_1');

		$max_id = max($from,$to);
		$min_id = min($from,$to);
		$from = $min_id;
		$to = $max_id;

		if (!$from || !$to)
			return;

		$query_where = array();

		$currency = 	$this->ci->input->get('PARAM_2');
		$email = 		$this->ci->input->get('PARAM_3');

		if( $email ){
            $this->pdf->email = true;

		}

		$comments = 	$this->ci->input->get('PARAM_4');
		$orientation = 	$this->ci->input->get('PARAM_5') ? 'L' : 'P';


		$start_date =	$this->ci->input->get('PARAM_6');
		if( is_date($start_date) ){
			// 			$start_date = date('Y-m-d',strtotime($start_date));
			$query_where['o.ord_date >='] = date('Y-m-d',strtotime($start_date));
		}

		$end_date = 	$this->ci->input->get('PARAM_7');
		if( is_date($end_date) ){
			//$end_date = date('Y-m-d',strtotime($end_date));
			$query_where['o.ord_date <='] = date('Y-m-d',strtotime($end_date));
		}

		$reference = 	$this->ci->input->get('PARAM_8');
		if( $reference ){
			$query_where['reference'] = $reference;
		}
		$limit = 1;
		for ($i = $from; $i <= $to; $i++) {

			//if( $limit > 3 ){ break; }
			$query_where['order_no'] = $i;
			$order = $this->purchase_model->search($query_where);

			if( !$order || !$order->order_no ){
				continue;
			}
			$contacts = $this->contact_model->get_supplier_contacts($order->supplier_id,'order');
			$this->bankacc = $this->bank_model->get_default_account($order->curr_code);

			$pdf->order = array(
			    'date'=>date('d-m-Y',strtotime($order->ord_date)),
				'purchase_order'=>$order->reference,
			    'reference'=>$order->reference,
                'name'=>$order->supp_name,
				'delivery'=>$pdf->company['name'],
				'tax_included'=>$order->tax_included,
			    'payment_terms'=>$order->payment_terms_name,
			    'amount_total'=>$order->total

			);
// 			bug($order);die;
			$order_info = array();
// 			if( count($contacts) >0 ){
// 				$pdf->order['contact'] = (array)$contacts[0];
                $order_info['contact'] = (array)$contacts[0];


                $order_info['company'] = array('name'=>trim($order->location_name),'address'=>$order->delivery_address,'email'=>$order->local_email,'phone'=>$order->local_phone);
                $order_info['name'] = trim($order->supp_name);

// 			}
// 			bug($order_info);die;
			$pdf->order_html = $this->ci->view('reporting/order/purchase-order',$order_info,true);

			$aux_info = array (
					_ ( "Customer's Reference" ) => array('w'=>20,'val'=>$order->supp_account_no),
					_ ( "Sales Person" ) => 		array('w'=>20,'val'=>null),
					_('Your GST no.')=>				array('w'=>20,'val'=>$order->tax_id),
					_ ( "Supplier's Reference" ) => array('w'=>20,'val'=>$order->requisition_no),
					_ ( "Order Date" ) => 			array('w'=>20,'val'=>$pdf->order['date']),
			);
			$pdf->author_html = $this->ci->view('export/aux_info',array('items'=>$aux_info),true);

			$pdf->items_view = array(
					'item_code'=>array('title'=>'Item code' ,'w'=>12,'class'=>'textcenter','ite_class'=>'default'),
					'description'=>array('title'=>'Item Description','w'=>34),
					'delivery_date'=>array('title'=>'Delivery Date','w'=>15,'class'=>'textcenter'),
					'qty'=>array('title'=>'Quantity','w'=>10,'class'=>'textcenter'),
					'units'=>array('title'=>'Unit','w'=>14,'class'=>'textcenter'),
					'price'=>array('title'=>'Price','w'=>15,'class'=>'textright'),
			);
			$items = $order->items;
			$pdf->items = $items;
			$pdf->make_report();
			$limit++;

		}
	}

	function credit_print($pdf){

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

	    $comments =   $pdf->inputVal('PARAM_4');
	    $orientation =$pdf->inputVal('PARAM_5') ? 'L' : 'P' ;

	    $start_date =	$pdf->inputVal('PARAM_6');
        if( !is_date($start_date) ){
            $start_date = null;
        } else {
            $start_date = date('Y-m-d',strtotime($start_date));
        }

	    $end_date = 	$pdf->inputVal('PARAM_7');
	    if( !is_date($end_date) ){
            $end_date = null;
        } else {
            $end_date = date('Y-m-d',strtotime($end_date));
        }

	    $reference =    $pdf->inputVal('PARAM_8');

	    $limit = 1;

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
                $pdf->items = $this->supplier_model->get_alloc_supp_sql_ci($trans->supplier_id,$trans->type,$trans->trans_no);

                $pdf->items_view = array(
                    'trans_type'=>array('title'=>'Trans Type','w'=>19,'class'=>'boleft'),
                    'reference'=>array('title'=>'#' ,'w'=>13),
                    'tran_date'=>array('title'=>'Date' ,'w'=>14,'class'=>'textcenter'),
                    'due_date'=>array('title'=>'Due Date','w'=>14,'class'=>'textright'),
                    'price'=>array('title'=>'Total Amount','w'=>18,'class'=>'textright'),
                    'left_alloc'=>array('title'=>'Left to Allocate','w'=>22,'class'=>'textright')
                );

               $contact = $this->contact_model->get_crm_persons('supplier', 'invoice', $trans->supplier_id);

               $pdf->order = array(
                    'debtor'=>null,
                    'debtor_no'=>$trans->debtor_no,
                    'name'=>$trans->supp_name,
                    'delivery'=>$trans->supp_name,
                    'delivery_address'=>$trans->address,
                    'date'=>date('d-m-Y',strtotime($trans->tran_date)),
                    'contact' => (array)$contact,
                    'order_no'=>$trans->reference,
                    'reference'=>$trans->reference,
                    'tax_included'=>'tax_included',
                    'payment_terms'=>$trans->terms,
                    'shipping'=>'shipping',
                    'trans_no'=>'trans_no',
                    'curr_code'=>$trans->curr_code ,
                    'total_words'=>price_in_words($trans->Total, ST_CUSTPAYMENT),
                    'total'=> abs($trans->alloc),
                    'left_alloc'=>abs($trans->Total)-abs($trans->alloc),

                );
               if( $pdf->order['left_alloc'] < 0.009 ){
                   $pdf->order['left_alloc'] = 0;
               }

                $pdf->order_html = $this->ci->view('reporting/order',$pdf->order,true);

                $aux_info = array (
    	            _( "Customer's Reference" ) => array('w'=>16.66,'val'=>$trans->reference),
    	            _( "Type" ) 				=> array('w'=>16.66,'val'=> transaction_type_tostring($trans->type) ),
    	            _('Your GST no.')			=> array('w'=>16.66,'val'=>$trans->gst_no),
    	            _( "Supplier's Reference" ) 		=> array('w'=>16.66,'val'=>$trans->supp_reference),
    	            _( "Due Date" ) 			=> array('w'=>16.66,'val'=> sql2date($trans->due_date) ),
                    _('Cheque No')			=> array('w'=>16.66,'val'=>$trans->cheque),
	           );

                $pdf->author_html = $this->ci->view('reporting/aux_info',array('items'=>$aux_info),true);
	           $pdf->make_report();
            }


        }

	}

	function supplier_balances_print(){
        $this->pdf->title = 'Supplier Balances';
	    $from =    input_val('PARAM_0');
	    $to =      input_val('PARAM_1');
	    $fromsupp = input_val('PARAM_2');

	    if ($fromsupp == ALL_TEXT)
	        $supp_view = _('All');
	    else
	        $supp_view = get_supplier_name($fromsupp);

	    $dec = user_price_dec();

	    $show_balance = input_val('PARAM_3');
	    $currency = input_val('PARAM_4');
	    $exchange_rate = 1;
	    if ($currency == ALL_TEXT){
	        $convert = true;
	        $currency = _('Balances in Home currency');

	    } else
	        $convert = false;
	    $no_zeros = input_val('PARAM_5') ? _('Yes') : _('No');


	    $comments = input_val('PARAM_6');

	    $orientation = input_val('PARAM_7') ? 'L' : 'P';
	    $destination = input_val('PARAM_8');

	    $items_view = array(
	        'type'=>array('title'=>'Trans Type' ,'w'=>17),
	        'reference'=>array('title'=>'#','w'=>13,'class_header'=>'textcenter'),
	        'tran_date'=>array('title'=>'Date','w'=>10,'class'=>'textcenter'),
	        'due_date'=>array('title'=>'Due Date','w'=>10,'class'=>'textcenter'),

	        'debit'=>array('title'=>'Charges','w'=>12.5,'class'=>'textright'),
	        'credit'=>array('title'=>'Credits','w'=>12.5,'class'=>'textright'),
	        'allocated'=>array('title'=>'Allocated','w'=>12.5,'class'=>'textright'),
	        'outstanding'=>array('title'=>'Outstanding','w'=>12.5,'class'=>'textright'),
	    );
	    if ($show_balance){
	        unset($items_view['outstanding']);
	        $items_view['balance'] = array('title'=>'Balance','w'=>12,'class'=>'textright');
	    }

	    $this->ci->smarty->assign('items_view',$items_view);

	    $this->db->select('supplier_id, supp_name AS name, curr_code');
	    if ($fromsupp != ALL_TEXT){
            $this->db->where('supplier_id',intval($fromsupp));
	    }

	    $suppliers = $this->db->order_by('supp_name')->get('suppliers')->result();


	    if( $suppliers && count($suppliers) > 0 ){
	        $header_data = array(
	            'page_title'=>$this->pdf->title,
	            'now'=>date(user_date_display().' H:i O').' GMT',
	            'fiscal_year'=>$this->pdf->fiscal_year,
	            'date_range'=>$from.' '.$to,
	            'fromsupp'=>$supp_view,
	            'currency'=>$currency,
	            'no_zeros'=>$no_zeros,
	            'host'=>$_SERVER['SERVER_NAME'],
	            'user'=>$_SESSION["wa_current_user"]->name,

	            'content_w'=>$this->pdf->width
	        );
// 	        $this->tcpdf->talbe_header_data = $header_data;
	        $this->tcpdf->talbe_header = $this->ci->view('reporting/header/balances',$header_data,true);
	        $this->tcpdf->line_befor_content = false; $this->tcpdf->line_begin_page = true;

	        $this->tcpdf->startPageGroup();
	        $this->tcpdf->AddPage();

	        $grand_total = array('debit'=>0, 'credit'=>0,'allocated'=>0, 'outstanding'=>0, 'balance'=>0);

            foreach ($suppliers AS $supp){
                $total = (object)array('credit'=>0,'debit'=>0,'allocated'=>0,'outstanding'=>0,'balance'=>0);
                $balance_total = 0;
                if( $exchange_rate ){
                    $rate = get_exchange_rate_from_home_currency($supp->curr_code, Today());
                    $this->ci->smarty->assign('exchange_rate',$rate);
                }
                $trans = $this->supplier_trans_model->get_transactions($supp->supplier_id, $from, $to);
                if( $trans && count($trans) > 0 )

                    $this->tcpdf->SetY($this->tcpdf->GetY()-5);
                    $balance = $this->supplier_trans_model->get_open_balance($supp->supplier_id, $from);

                    $y_befor_header = $this->tcpdf->GetY();
                    $this->pdf->write_view('header/balance_supplier',array('name'=>$supp->name,'curr'=>$supp->curr_code,'balance'=>$balance,'content_w'=>$this->ci->pdf->width));

                    $this->tcpdf->Ln();
                    foreach ($total AS $k=>$va){
                        $total->$k = isset($balance->$k) ? $balance->$k : 0;
                    }
                    foreach ($trans AS $tran){


                        $this->pdf->check_add_page();
                        if ( $no_zeros==_('Yes') && floatcmp(abs($tran->total_amount), $tran->allocated) == 0)
                            continue;


                        if( $tran->total_amount > 0 ){
//                             $tran->debit = $tran->total_amount;
                            $tran->credit = $tran->total_amount;
                            $tran->outstanding = $tran->total_amount - $tran->allocated;
                            //$balance_total+= $tran->total_amount;
                        } else {
//                             $tran->credit = abs($tran->total_amount);
                            $tran->debit = abs($tran->total_amount);

                            $tran->outstanding = (abs($tran->total_amount) - $tran->allocated)*-1;


                        }
                        $balance_total += $tran->total_amount;

                        foreach ($total AS $k=>$va){
                            $total->$k += (isset($tran->$k)) ? $tran->$k : 0;
                        }

                        //$balance_total += ($tran->debit-$tran->credit-$tran->allocated);
                        $tran->balance = $balance_total;

                        $this->pdf->write_view('item/balance',array('tran'=>$tran,'content_w'=>$this->ci->pdf->width));
                    }

                    $total->balance = $balance_total;
//                     $this->tcpdf->SetY($this->tcpdf->GetY()-6);

//                     if( $total->outstanding != 0 ){
                        $this->pdf->write_view('footer/balance_total',array('total'=>$total,'content_w'=>$this->ci->pdf->width));
//                         $pdf->InsertText(STR_EN);
//                     }
                    foreach ($grand_total AS $key=>$vv){
                        $grand_total[$key] += $total->$key;
                    }

//                     $item = $this->ci->view('reporting/header/balance_supplier', array('name'=>$supp->name,'curr'=>$supp->curr_code,'transactions'=>$trans) ,true);
//                     $this->tcpdf->writeHTML($this->pdf->css.$item);


            }
            $this->pdf->write_view('footer/balance_supplier_GrandTotal',array('balance'=>$grand_total,'content_w'=>$this->ci->pdf->width));
	    }

	}

	function invoice_print(){
	    $tran_id = 		input_val('trans_no');

        $invoice = $this->supplier_model->get_invoice($tran_id,ST_SUPPINVOICE);

        if( $invoice ){
             $this->ci->reporting->order = array(
                'date'=>sql2date($invoice->tran_date),
                'purchase_invoice'=>$invoice->reference,
                'reference'=>$invoice->reference,
                'tax_included'=>$invoice->tax_included,
                'payment_terms'=>$invoice->payment_terms_name,
                'invoice_no'=>$invoice->trans_no
            );
            if( $invoice->self_bill ){
                $this->ci->reporting->title = 'Self-Bill Invoice';
                $aux_info = array (
                    _ ( "Supplier Reference" ) => array('w'=>20,'val'=>$invoice->supp_reference),
                    _ ( "Sales Person" ) => 		array('w'=>20,'val'=>null),
                    _('Your GST no.')=>				array('w'=>20,'val'=>$invoice->tax_id),
                    _ ( "Invoice Date" ) => 			array('w'=>20,'val'=> $this->ci->reporting->order['date']),
                    'Self Bill Approval Ref'=>array('w'=>20,'val'=> $invoice->self_bill_approval_ref),
                );
            } else {
                $aux_info = array (
                    _ ( "Supplier Reference" ) => array('w'=>25,'val'=>$invoice->supp_reference),
                    _ ( "Sales Person" ) => 		array('w'=>25,'val'=>null),
                    _('Your GST no.')=>				array('w'=>25,'val'=>$invoice->tax_id),
                    _ ( "Invoice Date" ) => 			array('w'=>25,'val'=> $this->ci->reporting->order['date']),
                );

            }
            $order_info= array(
                'contact'=>(array)$contacts[0],
                'company'=>$this->ci->reporting->company,
                'name'=>$this->ci->reporting->company['name'],
                'address'=>$this->ci->reporting->company['address'],
                'delivery'=>$invoice->supp_name
            );

            $this->ci->reporting->order_html = $this->ci->view('reporting/order/invoice',$order_info,true);


            $this->ci->reporting->author_html = $this->ci->view('export/aux_info',array('items'=>$aux_info),true);

            $this->ci->reporting->items_view = array(
                'stock_id'=>array('title'=>'Item code' ,'w'=>15,'class'=>'textcenter','ite_class'=>'default'),
                'description'=>array('title'=>'Item Description','w'=>45),
                'quantity'=>array('title'=>'Quantity','w'=>10,'class'=>'textcenter'),
                'unit_price'=>array('title'=>'Unit','w'=>15,'class'=>'textcenter'),
                'price'=>array('title'=>'Price','w'=>15,'class'=>'textright'),
            );
// bug($invoice->items);die;
            $this->ci->reporting->items = $invoice->items;
            return true;
//             $this->reporting->make_report();
        }

	   die('go here');
	}


}