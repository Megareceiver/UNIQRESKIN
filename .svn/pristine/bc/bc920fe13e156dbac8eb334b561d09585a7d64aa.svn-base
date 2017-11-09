<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_repost extends ci {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->db = $ci->db;
        $this->purchase_model =  $this->ci->model('purchase',true);
    }

    function index(){
        $item = self::get_item(19);

    }

    function get_invoice($supp_trans=0,$type){
        $this->db->where( array('trans.trans_no'=>$supp_trans,'trans.type'=>$type ) );


        $transaction = $this->db->select('*')->get('supp_trans AS trans')->row();
        if( $transaction ) {
            $transaction->item = $this->db->where( array('supp_trans_no'=>$transaction->trans_no,'supp_trans_type'=>$transaction->type) )->get('supp_invoice_items')->result();
        }
        return $transaction;
    }

    function create_cart($trans_no=0,$type=ST_SUPPCREDIT){
        $supp_trans = new supp_trans($type);

        $credit_note = $this->get_invoice($trans_no,$type);

        $supp_trans->Comments = '';
        $supp_trans->tran_date = sql2date( $credit_note->tran_date );
        $supp_trans->due_date = sql2date( $credit_note->due_date );
        $supp_trans->supp_reference = $credit_note->supp_reference;
        $supp_trans->reference = $credit_note->reference;
        $supp_trans->ex_rate = $credit_note->rate;
        $supp_trans->supplier_id = $credit_note->supplier_id;
        $supp_trans->tax_included = $credit_note->tax_included;


        if( !isset($credit_note->item) || count($credit_note->item) < 1) return NULL;

        $supp_trans->ov_amount = $supp_trans->ov_gst_amount = 0;
        foreach ($credit_note->item AS $item){
            if($item->grn_item_id){


                $invoice_item = $this->db->where( array('grn_item_id'=>$item->grn_item_id,'supp_trans_type'=>$type) )->get('supp_invoice_items')->row();


//                 $_SESSION['supp_trans']->add_grn_to_trans($_POST['tax_id'.$n],$n,
//                     $_POST['po_detail_item'.$n], $_POST['item_code'.$n],
//                     $_POST['item_description'.$n], $_POST['qty_recd'.$n],
//                     $_POST['prev_quantity_inv'.$n], input_num('This_QuantityCredited'.$n),
//                     $_POST['order_price'.$n], input_num('ChgPrice'.$n),
//                     $_POST['std_cost_unit'.$n], "");

                if( !$invoice_item->tax_type_id ){
                    $this->db->reset();

                    $trans_tax_details = $this->db->where( array('trans_no'=>$trans_no,'trans_type'=>$type) )->get('trans_tax_details')->row();
                    if( $trans_tax_details && isset($trans_tax_details->tax_type_id) ){
                        $this->db->reset();
                        $this->db->where('id',$invoice_item->id)->update('supp_invoice_items',array('tax_type_id'=>$trans_tax_details->tax_type_id) );
                        $invoice_item->tax_type_id = $trans_tax_details->tax_type_id;
                    }
                }

                $supp_trans->add_grn_to_trans($invoice_item->tax_type_id,$item->grn_item_id,
                    $item->grn_item_id, $item->stock_id,
                    $item->description, $invoice_item->quantity,
                    $prev_quantity_inv =  abs($item->quantity) , abs($item->quantity),
                    $item->unit_price, $item->unit_price,
                    $item->unit_price, "");

                $item_tax = tax_calculator($invoice_item->tax_type_id, $item->unit_price*abs($item->quantity), $credit_note->tax_included);
                $supp_trans->ov_amount += $item_tax->price;
                $supp_trans->ov_gst_amount += $item_tax->value;
            } else if ($item->gl_code) {
                $gl_account = $this->db->select('account_name')->where('account_code',$item->gl_code)->get('chart_master')->row();

                if( $gl_account && isset($gl_account->account_name) ){
                    $supp_trans->add_gl_codes_to_trans($item->tax_type_id,$item->gl_code, $gl_account->account_name,$dimension_id=0, $dimension2_id=0,abs($item->unit_price), $item->memo_);
                    $supp_trans->ov_amount += abs($item->unit_price);
                }

            }
        }
// bug($supp_trans);die;
        $invoice_no = $this->purchase_model->write_invoice($supp_trans, $trans_no);

    }

}