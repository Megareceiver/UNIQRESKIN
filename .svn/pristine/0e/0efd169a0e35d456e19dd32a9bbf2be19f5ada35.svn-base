<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaintenanceRepost {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->page_security = 'SA_GLTRANSVIEW';
        include_once(ROOT . "/includes/ui.inc");
//         $this->ci->page_title = 'General Ledger Transaction Details';
//         $this->model = $this->ci->module_model('gl',null,true);

    }

    function index(){
        show_404();
    }

    function customer_payments(){
        include_once(ROOT . "/includes/ui/allocation_cart.inc");
        include_once(ROOT . "/sales/includes/sales_db.inc");
        $tran_no = $this->ci->uri->segments[4];
        $tran_type = ST_CUSTPAYMENT;

        $cart = new allocation($tran_type,$tran_no);
    	$myrow = get_customer_trans($tran_no, $tran_type);
    	$charge = get_cust_bank_charge($tran_type, $tran_no);
bug($myrow);
        bug($cart);die;
        die('repost customer payment');
    }

    function sale_invoice(){
        include_once(ROOT . "/sales/includes/cart_class.inc");
        $tran_no = $this->ci->uri->segments[4];
        $tran_type = ST_SALESINVOICE;
        //$sale_model = module_model_load('invoice','sales');
        //$invoice_no = $sale_model->write_invoice( new Cart($tran_type, array($tran_no) ) ,true);
        
        $sale_model = module_model_load('sale_invoice','sales');
        $invoice_no = $sale_model->write_sales_invoice(new Cart($tran_type, array($tran_no) ) ,true);
        

        redirect("gl/tran_view?type_id=$tran_type&trans_no=".$invoice_no);
    }

    function customer_payment_pos(){
        include_once(ROOT . "/sales/includes/sales_db.inc");

        $gl_model = $this->ci->module_model('gl','gl',true);
        $cus_payment_trans = $gl_model->get_trans(null,ST_CUSTPAYMENT,"gl.account is NULL OR gl.account = ''");

        $reposted = array();
        foreach ($cus_payment_trans AS $tran){

            if( !in_array($tran->type_no, $reposted) ){
                $reposted[] = $tran->type_no;
                $cus_tran = get_customer_trans($tran->type_no, ST_CUSTPAYMENT);
                $bank_acc = $this->ci->db->where('account_type',3)->get('bank_accounts')->row();

                if( !$cus_tran && isset($bank_acc->id) ){

                    $this->ci->db->where(array('trans_no'=>$tran->type_no,'type'=>ST_CUSTPAYMENT))->update('bank_trans',array('bank_act'=>$bank_acc->id));
                    $this->ci->db->where('counter',$tran->counter)->update('gl_trans',array('account'=>$bank_acc->account_code));
                }
            }
        }
//


        die('iam here');
    }

    function customer_credit_note(){

        $tran_no = NULL;
        if( $this->ci->uri->segments[4] ){
            $tran_no = $this->ci->uri->segments[4];
            $repost = $this->customer_credit_note_repost($tran_no);
        }

        die('repost customer credit note');
    }

    private function customer_credit_note_repost($tran_no){
        include_once(ROOT . "/sales/includes/cart_class.inc");
        $cart = new Cart(ST_CUSTCREDIT, $tran_no);
//         $cart->trans_no = $tran_no;
//         bug($cart);

        $credit_no = $cart->write(NULL);

        bug('repost tran_no='.$credit_no);

    }
}