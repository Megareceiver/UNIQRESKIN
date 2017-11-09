<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//	Entry/Modify Sales Quotations
//	Entry/Modify Sales Order
//	Entry Direct Delivery
//	Entry Direct Invoice

class SalesNewInvoice {
    function __construct() {
        $ci = get_instance();
        $this->input = $ci->input;
        $this->ref = $ci->ref;

        $this->customer_model = load_module_model('customer',true);
        $this->invoice_model = load_module_model('invoice',true);

        if( !Class_exists('Sale_Cart') ){
            $cart = load_class('Cart', 'sales/libraries','sale_');
        }
    }


    function index(){
        global $Ajax;
        if( $this->input->post() ){
//
//             if( isset($_POST['_customer_id_update']) ){
//                   $Ajax->activate('branch_id');
//             } else {
                $Ajax->activate('_page_body');
//             }
            foreach ($this->fields AS $k=>$fiel){
//                 if( $this->input->post($k) ){
                    $this->fields[$k]['value'] = $this->input->post($k);
//                 }
            }
        } else {
            $this->create_cart(ST_SALESINVOICE,0);
        }


//         bug($_SESSION['Items']);

        if( !in_ajax() ){
            page("Direct Sales Invoice");
        } else {
            div_start('_page_body');
        }

        start_form(false, false, "", "",' class="formtable" ');

        print($this->form());
        end_form();
        if( !in_ajax() ){
             end_page();
        } else {
             div_end();
        }

    }

    var $fields = array(
        'customer_id'=>array('type'=>'customer','title'=>'Customer','submitchange'=>1,'value'=>null),
        'branch_id'=>array('type'=>'branch','title'=>'Branch','debtor'=>0),
        'ref'=>array('title'=>'Reference'),

        'customer_ref'=>array('title'=>'Customer Reference'),

        'cust_ref'=>array('title'=>'Customer Reference'),
        'Location2'=>array('title'=>'Deliver from Location','type'=>'location'),

        'customer_currency'=>array('title'=>'Customer Currency'),
        'credit'=>array('title'=>'Current Credit'),
        'default_discount'=>array('title'=>'Customer Discount','value'=>0,'unit'=>'%'),

        'payment'=>array('type'=>'payment'),
        'sales_type'=>array('title'=>'Price List','type'=>'sales_type'),

        'OrderDate'=>array('title'=>'Invoice Date','type'=>'date'),

        'dimension_id'=>array('type'=>'hidden','value'=>0),
        'dimension2_id'=>array('type'=>'hidden','value'=>0),

        'Location'=>array('title'=>'Deliver from Location'),
        'delivery_date'=>array('title'=>'Due Date','type'=>'date'),
        'deliver_to'=>array('title'=>'Deliver from Location','type'=>'location'),
        'delivery_address'=>array('type'=>'textarea','title'=>'Address'),
        'phone'=>'',
        'cust_ref'=>'',
        'Comments'=>array('type'=>'textarea','title'=>''),
        'ship_via'=>'',
    );

    var $item_cart = array(
        'item_code'=>'',
        'item_desc'=>'',
        'tax'=>'',
        'qty'=>'',
        'unit'=>'',
        'price'=>'',
        'discount'=>'',

    );
    private function form(){
        $html = "";
        if( !$this->fields['customer_id']['value'] ){
            $this->fields['customer_id']['value'] = $this->customer_model->debtor_id_first();
        }
        $this->fields['branch_id']['debtor'] = $this->fields['customer_id']['value'];



        $data = array('fields'=>$this->fields);
        $html.= module_view('invoice/header',$data,false);
        $html.= module_view('invoice/items',$data,false);
        $html.= module_view('invoice/delivery_detail',$data,false);
        return $html;
    }


    private function create_cart($type, $trans_no=0) {
        global $Refs;
        if (!$_SESSION['SysPrefs']->db_ok) // create_cart is called before page() where the check is done
            return;

//         processing_start();

        if (isset($_GET['NewQuoteToSalesOrder'])){
            $trans_no = $_GET['NewQuoteToSalesOrder'];
            $doc = new Cart(ST_SALESQUOTE, $trans_no, true);
            $doc->Comments = _("Sales Quotation") . " # " . $trans_no;


            $_SESSION['Items'] = $doc;
        } elseif($type != ST_SALESORDER && $type != ST_SALESQUOTE && $trans_no != 0) {
            // this is template

            $doc = new Cart(ST_SALESORDER, array($trans_no));
            $doc->trans_type = $type;
            $doc->trans_no = 0;
            $doc->document_date = new_doc_date();

            if ($type == ST_SALESINVOICE) {
                $doc->due_date = $this->invoice_model->get_duedate($doc->payment, $doc->document_date); die();
                $doc->pos = get_sales_point(user_pos());
            } else
                $doc->due_date = $doc->document_date;

            $doc->reference = $Refs->get_next($doc->trans_type);


            foreach($doc->line_items as $line_no => $line) {
                $doc->line_items[$line_no]->qty_done = 0;
            }
            $_SESSION['Items'] = $doc;
        } else {

            $cart = new Sale_Cart($type, array($trans_no));
        }
        $_SESSION['Items'] = serialize($cart);
//         unserialize
        $this->copy_from_cart();
    }

    private function copy_from_cart() {
        $cart = unserialize($_SESSION['Items']);
        $this->fields['ref']['value'] = $cart->reference;
        $this->fields['Comments']['value'] = $cart->Comments;
        $this->fields['OrderDate']['value'] = $cart->document_date;
        $this->fields['delivery_date']['value'] = $cart->due_date;
        $this->fields['cust_ref']['value'] = $cart->cust_ref;
        $this->fields['freight_cost']['value'] = price_format($cart->freight_cost);

        $this->fields['deliver_to']['value'] = $cart->deliver_to;
        $this->fields['delivery_address']['value'] = $cart->delivery_address;
        $this->fields['Location']['value'] = $cart->Location;
        $this->fields['ship_via']['value'] = $cart->ship_via;
        $this->fields['customer_id']['value'] = $cart->customer_id;
        $this->fields['branch_id']['value'] = $cart->Branch;

        $this->fields['sales_type']['value'] = $cart->sales_type;
        $this->fields['payment']['value'] = $cart->payment;
        $this->fields['branch_id']['value'] = $cart->Branch;

        if ($cart->trans_type!=ST_SALESORDER && $cart->trans_type!=ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
            $this->fields['dimension_id']['value'] = $cart->dimension_id;
            $this->fields['dimension2_id']['value'] = $cart->dimension2_id;
        }
        $this->fields['cart_id']['value'] = $cart->cart_id;
        $this->fields['_ex_rate']['value'] = $cart->ex_rate;
    }
}