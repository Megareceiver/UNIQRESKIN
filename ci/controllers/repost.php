<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Repost {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->db = $ci->db;
        $this->sale_model = $ci->model('sale',true);
    }
    var $all = false;
    var $type = ST_CUSTDELIVERY;
    function index(){
        $this->start_date = '05-05-2015';
        $this->end_date = '16-06-2015';

        return $this->sale();
    }

    function sale(){
        $this->db->select('trans.trans_no')->where(array('trans.type'=>$this->type,'trans.ov_amount !='=>0));

        if( $this->all ){

            $this->db->join('gl_trans AS gl','gl.type=trans.type AND gl.type_no=trans.trans_no');
            $this->db->where_in('gl.account',array(4450,4451))->where('gl.amount !=',0);
            $this->db->get('gl.*');
        }

        $this->db->group_by('trans.trans_no');
        if( $this->start_date ){
            $this->db->where('trans.tran_date >=',date2sql($this->start_date));
        }
        if( $this->end_date ){
            $this->db->where('trans.tran_date <=',date2sql($this->end_date));
        }
        $invoices = $this->db->get('debtor_trans AS trans')->result();
        return $this->sale_repost();
    }

    function sale_repost(){
        global $path_to_root;
        include_once($path_to_root . "/sales/includes/cart_class.inc");
//         $data = array('done'=>1,'next'=>$ci->input->get('next'));

        $trans_no = 520;
        $cart = new Cart($this->type, array($trans_no)) ;
        $this->sale_model->delivery_update($trans_no,$cart); die('quannh');
//         $invoice_no = $sale_model->write_sales_invoice( $cart,true );
//         echo json_encode($data); die;
    }

    function supp_receive($trans_no){
        $trans_no = intval($trans_no);
        $ci = $this->ci;

        $trans_type = ST_SUPPRECEIVE;

        create_new_po($trans_type, 0);

        //     $po_detail = $ci->db->where('id',$trans_no)->get('grn_batch')->row();
        $ci->db->where('grn.grn_batch_id',$trans_no);
        $ci->db->join('purch_order_details AS po','po.po_detail_item = grn.po_detail_item');
        $ci->db->join('purch_orders AS ord','ord.order_no = po.order_no');

        $grn_items = $ci->db->get('grn_items AS grn')->result();

        if( $grn_items && count($grn_items) > 0 ) {
            $grn_first = current($grn_items);

            $supp_trans = $ci->db->where(array('trans_no'=>$grn_first->supp_trans_no,'type'=>$grn_first->supp_trans_type) )->get('supp_trans')->row();
            $delivery = $ci->db->where(array('id'=>$grn_first->grn_batch_id) )->get('grn_batch')->row();
            $supplier = $ci->db->where(array('supplier_id'=>$supp_trans->supplier_id) )->get('suppliers')->row();

            $date_ = $delivery->delivery_date;
            $ex_rate = get_exchange_rate_from_home_currency($supplier->curr_code, $date_);
            $dim = $dim2 = 0;


            begin_transaction();
            void_gl_trans($trans_type, $trans_no);
            $clearing_act = get_company_pref('grn_clearing_act');
            foreach ($grn_items AS $entered_grn) {
                $stock_gl_code = get_stock_gl_code($entered_grn->item_code);


                $iv_act = (is_inventory_item($entered_grn->item_code) ? $stock_gl_code["inventory_account"] :
                    ($supplier->purchase_account ? $supplier->purchase_account : $stock_gl_code["cogs_account"]));

                $tax = tax_calculator($entered_grn->tax_type_id,$entered_grn->qty_recd * $entered_grn->unit_price,$entered_grn->tax_included);

                $total += add_gl_trans_supplier($trans_type, $trans_no, $date_, $iv_act,
                    $dim, $dim2, $tax->price, $supp_trans->supplier_id);
//                 if( $tax->value != 0 ){
//                     $total += add_gl_trans_supplier($trans_type, $trans_no, $date_, $tax->purchasing_gl_code, $dim, $dim2, $tax->value, $supp_trans->supplier_id, "", $ex_rate);
//                 }

            }
            add_gl_trans_supplier($trans_type, $trans_no, $date_, $clearing_act ,$dim, $dim2, -$total,null);
            commit_transaction();

        }
//         bug($grn_items);
//         bug($ci->db->last_query()); die();

    }

}