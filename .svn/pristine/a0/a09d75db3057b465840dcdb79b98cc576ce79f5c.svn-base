<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class WrongPosting extends ci {
    var $date_max = null;
	function __construct() {
		global $ci, $Ajax;;
		$this->ci = $ci;
		$this->db = $ci->db;
		$this->ajax = $Ajax;


	}
	function index(){
		$js = null;
		$js .= get_js_open_window(900, 500);

		page('Wrong GL Posting',false, false, "", $js);

		$this->show_items();
		end_page();

	}

	private function show_items(){

		$page = $this->ci->input->get('p');
		if( !$page ){
			$page = 1;
		}

		$this->db->select('gl.*')->from('gl_trans AS gl')->where_in('gl.account',array(4450,4451))->where('gl.amount >',0.01);
		$this->db->select('ac.account_name')->join('chart_master AS ac','ac.account_code=gl.account','left');

		$tempdb = clone $this->db;

		$data['items'] = $this->db->limit(page_padding_limit, page_padding_limit*($page-1) )->get()->result();

		$data['total'] = $tempdb->count_all_results();
		$data['lastpage'] = round($data['total']/page_padding_limit);
		$data['fields'] = array(
			'account'=>'Account',
			'account_name'=>' Account Name',
			'type'=>'Type',
			'type_no'=>'#',
			'tran_date'=>'Trans Date',
			'amount'=>'Amount'
		);
		$this->ci->view('gl/wrong_posting',$data);
	}

	function sale_credit_invoice(){
	    $allo_where = array(
// 	       'trans_no_to'=>23,
	        'trans_type_to'=>ST_SALESINVOICE,
	        'trans_type_from'=>ST_CUSTPAYMENT
	    );
	    $allocates = $this->db->where($allo_where)->get('cust_allocations AS al')->result();
        $limit = 0;
       
        $customer_payment_voided = array();
	    if( $allocates && count($allocates) >0 ) {
	        $customer_payment = $update_qty_done = array();
	       
	        $invoice_payment = array();
	       
	        foreach ($allocates AS $allo) {
	            
	            if( !isset($invoice_payment[$allo->trans_no_to]) ){
	                $invoice_payment[$allo->trans_no_to] = 0;
	            }
	            $this->db->reset();
	            $select = '*, SUM(det.quantity*(det.unit_price+det.unit_tax)*deb.rate) AS allocated, Sum(det.quantity-det.qty_done) AS Outstanding';
	            $this->db->select($select);
	            $this->db->where( array('det.quantity <>'=>0) , true)->where(array('deb.type'=>$allo->trans_type_to,'deb.trans_no'=>$allo->trans_no_to));
	            $this->db->join('debtor_trans AS deb','deb.type = det.debtor_trans_type AND deb.trans_no = det.debtor_trans_no');
	            $invoice = $this->db->get('debtor_trans_details AS det')->row();
	      
	            if( $invoice && $invoice->alloc >= $allo->amt && $invoice->Outstanding > 0 ){ //&& $invoice->allocated != $allo->amt
	                $this->db->reset();
	                $this->db->query("UPDATE `debtor_trans_details` SET `qty_done` = `quantity` WHERE `debtor_trans_type` =  '".$invoice->type."' AND `debtor_trans_no` =  '".$invoice->trans_no."';");
	                $update_qty_done[] = $invoice->trans_no;
	                
	            }
	            
	            $invoice_payment[$allo->trans_no_to] += $allo->amt;
	           
	            
	            if( $invoice_payment[$allo->trans_no_to] > $invoice->allocated){
	                
	                $customer_payment_voided[] = $allo->trans_no_from;
	                void_transaction($allo->trans_type_from,$allo->trans_no_from,Today(),'remove duplicate customer payment');
	            }
	        }
	        bug($update_qty_done);
	        bug($customer_payment_voided);die('quannh');
	    }
	    die('go here');
	}
}