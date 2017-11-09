<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class View {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->page_security = 'SA_SALESTRANSVIEW';
        $this->customer_model = $this->ci->module_model($this->ci->module,'customer',true);
        $this->ci->template->module = $this->ci->module;
    }

    function receipt(){
        $this->ci->page_title = 'View Customer Payment';
        $this->ci->template->layout = 'popup';

        $tran_no = $this->ci->uri->segments[4];
        $receipt = $this->customer_model->get_trans($tran_no, ST_CUSTPAYMENT);

        $data = array('title'=>"Customer Payment #$tran_no");

//         bug($receipt);
        if( $receipt ){
            global $bank_transfer_types;
            $receipt->BankTransType = $bank_transfer_types[$receipt->BankTransType];
        }
        $data['tran'] = $receipt;
// bug($data); die;
        $this->ci->temp_view('customer_payment',$data);
    }

    function invoice(){
        $this->ci->page_title = 'View Sales Invoice';
        $this->ci->template->layout = 'popup';

        $tran_no = $this->ci->uri->segments[4];
        $invoice = $this->customer_model->get_trans($tran_no, ST_SALESINVOICE);

        $data = array('title'=>"Sales Invoice #$tran_no");
        $data['tran'] = $invoice;
        if( $invoice ){
            $data['branch'] = get_branch($invoice->branch_code);
            $data['payment'] = get_payment_terms($invoice->payment_terms);
            $data['sales_order'] = get_sales_order_header($invoice->order_, ST_SALESORDER);
        }
//         bug($data);die;
//         $branch = get_branch($myrow["branch_code"]);

//         $invoice = $receipt = $this->customer_model->get_trans($tran_no, ST_SALESINVOICE);
//         bug($invoice);die;
        $this->ci->temp_view('view_invoice',$data);
    }
}