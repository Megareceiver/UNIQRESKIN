<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends ci {
	function __construct() {
		global $ci;
		$this->ci = $ci;

		$this->customer_trans_model = $this->model('customer_trans',true);
		$this->sys_model = $this->model('config',true);
		$this->bank_model = $this->model('bank_account',true);
		$this->contact_model = $this->model('crm',true);
		$this->sale_order_model = $this->model('sale_order',true);
	}

	function statement_print($pdf){
		$trans_where = array();
		$customer = 			$pdf->inputVal('PARAM_0');
		$currency = 			$pdf->inputVal('PARAM_1');
		$show_also_allocated = 	$pdf->inputVal('PARAM_2') ? true : false ;
		$email = 				$pdf->inputVal('PARAM_3');
		$comments = 			$pdf->inputVal('PARAM_4');
		$orientation = 			$pdf->inputVal('PARAM_5') ? 'L' : 'P' ;
		$start_date = 			$pdf->inputVal('PARAM_6');
		if( $start_date ){
			$trans_where['tran_date >='] = date('Y-m-d',strtotime($start_date));
		}
		$end_date = 			$pdf->inputVal('PARAM_7');
		if( $end_date ){
			$trans_where['tran_date <='] = date('Y-m-d',strtotime($end_date));
		}

		$reference =			$pdf->inputVal('PARAM_8');
		if( $reference ){
			$trans_where['tran.reference'] = $reference;
		}

		if( !$customer ){
			$debtor_where = array();
		} else {
			$debtor_where = array('debtor_no'=>$customer);
		}
		$debtors = $this->ci->db->where($debtor_where)->order_by('name')->get('debtors_master')->result();

		$trans_where['type <>']= ST_CUSTDELIVERY;
		$limit = 1;
		if( $debtors AND count($debtors) > 0 ){foreach ($debtors AS $deb){

			$trans_where['debtor_no'] = $deb->debtor_no;
			$pdf->items = $this->customer_trans_model->search($trans_where,$show_also_allocated);

			if( !$pdf->items || count($pdf->items) < 1 ){
				continue;
			}
			$pdf->bankacc = $this->bank_model->get_default_account($deb->curr_code);


			$pdf->items_view = array(
					'tran_date'=>array('title'=>'Date','w'=>12,'class'=>'textcenter'),
					'deb_type'=>array('title'=>'' ,'w'=>5,'class'=>'textcenter'),
					'reference'=>array('title'=>'Ref','w'=>30),


					'trans_type'=>array('title'=>'Description' ,'w'=>20,'class'=>'textleft'),

					//'due_date'=>array('title'=>'Due Date','w'=>10,'class'=>'textcenter'),
					'debit'=>array('title'=>'Debit','w'=>15,'class'=>'textright'),
					'credit'=>array('title'=>'Credit','w'=>15,'class'=>'textright'),
					//'OverDue'=>array('title'=>'Allocated','w'=>15,'class'=>'textright'),
			);



			$contacts = $this->contact_model->get_customer_contact($deb->debtor_no,'invoice');
			$pdf->order = array(
					'debtor'=>$deb->name,
					'debtor_no'=>$deb->debtor_no,
					'address'=>$deb->address,
					'name'=>$contacts->name,
					'date'=>null,
					'contact' => (array)$contacts
			);
			$pdf->order_html = $this->ci->view('export/order-charge',$pdf->order,true);
			$pdf->customer_details = array();
			$current = now();
			$lastest_date = 0;
			foreach ($pdf->items AS $ite){
				$item_date = strtotime($ite->tran_date);
				if( $lastest_date < $item_date ){
					$lastest_date = $item_date;
				}
				if( date('Y-m',strtotime($current)) == date('Y-m',$item_date) ){
					if( !isset($pdf->customer_details['current']) ) $pdf->customer_details['current'] = 0;

					$pdf->customer_details['current']+= $ite->TotalAmount;
				}
			}
			$pdf->order['date'] = date('Y-m-d',$lastest_date);
			$pdf->make_statement_report();
			$limit++;

		}}
	}

	function statement_prints($pdf){

	    $customer_model = $this->ci->model('customer_trans',true);
	    $sys_model = $this->ci->model('config',true);
	    $currency_default = $sys_model->curr_default();
	    $pdf->currency_name = $currency_default->currency;

	    $trans_where = array();

	    $customer = 			$pdf->inputVal('PARAM_0');
	    $currency = 			$pdf->inputVal('PARAM_1');

	    $show_also_allocated = 	$pdf->inputVal('PARAM_2') ? true : false ;

	    $email = 				$pdf->inputVal('PARAM_3');
	    $comments = 			$pdf->inputVal('PARAM_4');
	    $orientation = 			$pdf->inputVal('PARAM_5') ? 'L' : 'P' ;
	    $start_date = 			$pdf->inputVal('PARAM_6');

	    if( $start_date ){
	        $trans_where['tran_date >='] = date2sql($start_date);
	    } else {
	        $start_date = date('Y-m-d');
	    }

	    $end_date = 			$pdf->inputVal('PARAM_7');
	    if( $end_date ){
	        $trans_where['tran_date <='] = date2sql($end_date);
	    } else {
	        $end_date = Now();
	    }

	    $reference =			$pdf->inputVal('PARAM_8');
	    if( is_date($reference) ){
	        $trans_where['tran.reference'] = $reference;
	    }

	    if( !$customer ){
	        $debtor_where = array();
	    } else {
	        $debtor_where = array('debtor_no'=>$customer);
	    }
	    $debtors = $this->ci->db->where($debtor_where)->order_by('name')->get('debtors_master')->result();

// 	    $trans_where['type <>']= ST_CUSTDELIVERY;
// 	    $trans_where['type in('.ST_SALESINVOICE.','.ST_OPENING_CUSTOMER.','.ST_OPENING_CUSTOMER.')'] = null;
        $type_select = array(ST_SALESINVOICE,ST_OPENING_CUSTOMER,ST_OPENING_CUSTOMER);
// 	    $limit = 1;
die('go 152');
	    if( $debtors AND count($debtors) > 0 ){foreach ($debtors AS $deb){
// 	        if( $limit > 1 ){ break; }
// 	        $trans_where['debtor_no'] = $deb->debtor_no;
// 	        $pdf->items = $customer_model->search($trans_where,$show_also_allocated,$type_select);
die('here');
// 	        if( !$pdf->items || count($pdf->items) < 1 ){
// 	            continue;
// 	        }
// 	        $pdf->bankacc = $this->bank_model->get_default_account($deb->curr_code);
// 	        $pdf->items_view = array(
// 	            'tran_date'=>array('title'=>'Date','w'=>12,'class'=>'textcenter'),
// 	            'trans_type'=>array('title'=>'Trans Type' ,'w'=>20,'class'=>'textcenter'),
// 	            'deb_type'=>array('title'=>'' ,'w'=>5,'class'=>'textcenter'),
// 	            'reference'=>array('title'=>'Ref','w'=>30),
// 	            'debit'=>array('title'=>'Debit','w'=>15,'class'=>'textright'),
// 	            'credit'=>array('title'=>'Credit','w'=>15,'class'=>'textright'),
// 	        );

// 	        $pdf->contacts = $this->contact_model->get_customer_contact($deb->debtor_no,'invoice');
// 	        bug($pdf->contacts); die;
// 	        $order = array(
// 	            'debtor'=>$deb->name,
// 	            'debtor_no'=>$deb->debtor_no,
// 	            'name'=>$pdf->contacts->name,
// 	            //'date'=>date('d-m-Y',strtotime($pdf->items[0]->tran_date)-24*60*60),
// 	            'date'=>$end_date,
// 	            'contact' => (array)$this->contacts
// 	        );
// 	        $this->ci->smarty->assign('order',$order);
// // die('go here');
// // 	        $pdf->order_html = $this->ci->view('reporting/order/charge',$this->order,true);

// // 	        $balance_date = $start_date;
// // 	        if ( is_date($balance_date) ){
// // 	            $balance_date = date('Y-m-d', strtotime($balance_date) - 60*60*24 );
// // 	        } else {
// // 	            $balance_date = date('Y-m-d', strtotime(time()) - 60*60*24 );
// // 	        }

// 	        $pdf->customer_details = $customer_model->get_statement_detail($deb->debtor_no,$end_date, $type_select);
// // 	        bug($pdf->customer_details);die;
// 	        $pdf->make_statement_report();
// 	        $limit++;

	    }}
die('go here');
	    $pdf->do_report();
	}
}