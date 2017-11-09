<?php

class Purchase_Model {
    function __construct(){
        global $ci;
        $this->db = $ci->db;
        $this->supplier_model = $ci->model('supplier',true);
        $this->gl_trans = $ci->model('gl_trans',true);
    }

    function write_invoice($supp_trans, $invoice_no=0){
        global $Refs;

        begin_transaction();
        $trans_type = $supp_trans->trans_type;

        if ($invoice_no != 0) {
            delete_comments($trans_type, $invoice_no);
            void_gl_trans($trans_type, $invoice_no, true);
            void_cust_allocations($trans_type, $invoice_no); // ?
            void_trans_tax_details($trans_type, $invoice_no);
//             $this->cus_trans->remove_trans_detail($trans_type, $invoice_no);
        }

        hook_db_prewrite($supp_trans, $supp_trans->trans_type);
        $supplier = $this->supplier_model->supplier_detail($supp_trans->supplier_id);
        add_new_exchange_rate($supplier->curr_code, $supp_trans->tran_date, $supp_trans->ex_rate);

        $invoice_items_total = $supp_trans->get_items_total();
        $item_added_tax = $supp_trans->ov_gst_amount;



        if ($trans_type == ST_SUPPCREDIT){
            // let's negate everything because it's a credit note
            $invoice_items_total = - $invoice_items_total;
//             $tax_total = -$tax_total;
            $supp_trans->ov_discount = -$supp_trans->ov_discount; // this isn't used at all...
            $item_added_tax = -$item_added_tax;
        }

        $date_ = $supp_trans->tran_date;
//         $ex_rate = get_exchange_rate_from_home_currency($supplier->curr_code, $date_);


        /*First insert the invoice into the supp_trans table*/

        $supp_tran = array(
            'type'=>$trans_type,
            'supplier_id'=>$supp_trans->supplier_id,
            'tran_date'=>date2sql($supp_trans->tran_date),
            'due_date'=>date2sql($supp_trans->due_date),
            'reference'=>$supp_trans->reference,
            'supp_reference'=>$supp_trans->supp_reference,
            'ov_amount'=>$invoice_items_total,
            'ov_gst'=>$item_added_tax,
            'ov_discount'=>$supp_trans->ov_discount,
            'rate'=>0,
            'tax_included'=>$supp_trans->tax_included,
            'fixed_access'=>$supp_trans->fixed_access
        );
        $invoice_id = $this->write_supp_trans($invoice_no,$supp_tran);

        $gl_trans = new gl_trans();
        $gl_trans->set_value( array( 'type'=>$trans_type, 'type_no'=>$invoice_id, 'tran_date'=>$supp_trans->tran_date, 'person_type_id'=>PT_SUPPLIER, 'person_id'=>$supp_trans->supplier_id) );


        $total = 0;

        /* Now the TAX account */
        //TUANVT6
//         $taxes = $supp_trans->get_taxes_tran_new();
        $net_diff = 0;
        /*
         foreach ($taxes as $taxitem) {
         if ($taxitem['Net'] != 0){
         if (isset($taxitem['Override'])) {
         if ($supp_trans->tax_included) { // if tax included, fix net amount to preserve overall line price
         $net_diff += $taxitem['Override'] - $taxitem['Value'];
         $taxitem['Net'] += $taxitem['Override'] - $taxitem['Value'];
         }
         $taxitem['Value'] = $taxitem['Override'];
         }
         //TUANVT5
         add_trans_tax_details($trans_type, $invoice_id,
         $taxitem['id'], $taxitem['rate'], $supp_trans->tax_included, $taxitem['Value'],
         $taxitem['Net'], $ex_rate, $date_, $supp_trans->supp_reference);

         if ($trans_type == ST_SUPPCREDIT)
             $taxitem['Value'] = -$taxitem['Value'];

             $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_,
             $taxitem['purchasing_gl_code'], 0, 0, $taxitem['Value'],
             $supp_trans->supplier_id,
             "A general ledger transaction for the tax amount could not be added");
             }
             }
             */

         if ($trans_type == ST_SUPPCREDIT)
             $net_diff = -$net_diff;

         /* Now the AP account */
//          $total += add_gl_trans_supplier($trans_type, $invoice_id, $supp_trans->tran_date, $supplier->payable_account, 0, 0,
//              -($invoice_items_total +  $item_added_tax + $supp_trans->ov_discount),
//              $supp_trans->supplier_id,
//              "The general ledger transaction for the control total could not be added");

//          bug('$invoice_items_total='.$invoice_items_total);
         $gl_trans->add_trans($supplier->payable_account, -($invoice_items_total +  $item_added_tax + $supp_trans->ov_discount) );

// bug($supp_trans);die;
         foreach ($supp_trans->gl_codes as $entered_gl_code) {
             /*GL Items are straight forward - just do the debit postings to the GL accounts specified -
              the credit is to creditors control act  done later for the total invoice value + tax*/
             $tax = tax_calculator($entered_gl_code->supplier_tax_id,$entered_gl_code->amount,$supp_trans->tax_included);
             $price = $tax->price;
             if ($trans_type == ST_SUPPCREDIT){
                 $price = -$price;
                 $tax->value = -$tax->value;
             }

             $memo_ = $entered_gl_code->memo_;

//              $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $entered_gl_code->gl_code,
//                  $entered_gl_code->gl_dim, $entered_gl_code->gl_dim2, $price, $supp_trans->supplier_id, "", 0, $memo_);

             $gl_trans->add_trans($entered_gl_code->gl_code, $price,$entered_gl_code->gl_dim,$entered_gl_code->gl_dim2,$entered_gl_code->memo_ );

             $invoice_item = array(
                'supp_trans_type'=>$trans_type,
                 'supp_trans_no'=>$invoice_no,
                 'stock_id'=>null,
                 'description'=>null,
                 'gl_code'=>$entered_gl_code->gl_code,
                  'unit_price'=>$price,
                 'unit_tax'=>0,
                 'quantity'=>1,
                 'grn_item_id'=>'',
                 'po_detail_item_id'=>'',
                 'memo_'=>$entered_gl_code->memo_,
                 'tax_type_id'=>$entered_gl_code->supplier_tax_id
             );

//              add_supp_invoice_gl_item($trans_type, $invoice_id, $entered_gl_code->gl_code,
//              $price, $memo_,null,$entered_gl_code->supplier_tax_id);

             if( !$invoice_no ){
                 $this->add_invoice_items($invoice_item);
             }

             // store tax details if the gl account is a tax account
             // 		if ($trans_type == ST_SUPPCREDIT){
             // 		    $entered_gl_code->amount = -$entered_gl_code->amount;
             // 		}

//              $gl_tax_details = array();
//              $this->add_gl_tax_details($invoice_item);

//              add_gl_tax_details($entered_gl_code->gl_code,
//              $trans_type, $invoice_id, $price,
//              $ex_rate, $date_, $supp_trans->supp_reference, $supp_trans->tax_included);



//              add_trans_tax_details($trans_type, $invoice_id,$entered_gl_code->supplier_tax_id, $tax->rate, $supp_trans->tax_included, $tax->value,$price, $ex_rate, $date_, $supp_trans->supp_reference);



//              $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_,
//                  $tax->purchasing_gl_code, 0, 0, $tax->value,
//                  $supp_trans->supplier_id,
//                  "A general ledger transaction for the tax amount could not be added");
            if( $tax->purchasing_gl_code ){
                $gl_trans->add_trans($tax->purchasing_gl_code, $tax->value);
            }

         }

         $clearing_act = get_company_pref('grn_clearing_act');
//          $tax_id = 0;



         foreach ($supp_trans->grn_items as $line_no => $entered_grn){
            	$tax_id = $entered_grn->supplier_tax_id;

            	if ($trans_type == ST_SUPPCREDIT) {
            	    $entered_grn->this_quantity_inv = -$entered_grn->this_quantity_inv;
                    if( $invoice_no ) { // onlye set credited when first do credit note
                        set_grn_item_credited($entered_grn, $supp_trans->supplier_id, $invoice_id, $supp_trans->tran_date);
                    }

            	}

            	// For tax included pricelist the net price is calculated down from tax_included price.
            	// To avoid rounding errors we have to operate on line value instead of price
            	// Additionally we have to take into account differences in net value
            	// due to variations in tax calculations on supplier side. More over there is no direct relation between
            	// taxes and sales accounts, so we add net_diff just to first posted net value. This is _ugly_hack_
            	// which save us from rewriting whole routine, and works right only for small tax differences.

            	$taxfree_line = get_tax_free_price_for_item($entered_grn->item_code, $entered_grn->this_quantity_inv * $entered_grn->chg_price,
            	    $supp_trans->tax_group_id, $supp_trans->tax_included) - $net_diff;

            	$net_diff = 0;

            	$line_tax = get_full_price_for_item($entered_grn->item_code,
            	    $entered_grn->this_quantity_inv * $entered_grn->chg_price, 0, $supp_trans->tax_included) - $taxfree_line;

            	$stock_gl_code = get_stock_gl_code($entered_grn->item_code);
            	$dim = $stock_gl_code['dimension_id']; $dim2 = $stock_gl_code['dimension2_id'];
            	if( $supplier->dimension_id ) $dim = $supplier->dimension_id;
            	if( $supplier->dimension2_id ) $dim = $supplier->dimension2_id;
//             	$dim = $supplier['dimension_id'] ? $supplier['dimension_id'] : $stock_gl_code['dimension_id'];
//             	$dim2 = $supplier['dimension2_id'] ? $supplier['dimension2_id'] : $stock_gl_code['dimension2_id'];

            	if ($trans_type == ST_SUPPCREDIT) {
            	    $iv_act = (is_inventory_item($entered_grn->item_code) ? $stock_gl_code["inventory_account"] :
            	        ($supplier["purchase_account"] ? $supplier["purchase_account"] : $stock_gl_code["cogs_account"]));

            	    $tax = tax_calculator($entered_grn->supplier_tax_id,$entered_grn->this_quantity_inv * $entered_grn->chg_price,$supp_trans->tax_included);

//             	    $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $iv_act,
//             	        $dim, $dim2, $tax->price, $supp_trans->supplier_id);
//             	    $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $tax->purchasing_gl_code,
//             	        $dim, $dim2, $tax->value, $supp_trans->supplier_id, "", $ex_rate);

            	    $gl_trans->add_trans($iv_act, $tax->price,$dim, $dim2);
            	    $gl_trans->add_trans($tax->purchasing_gl_code, $tax->value,$dim, $dim2);

            	} else {
            	    // -------------- if price changed since po received.
            	    $iv_act = is_inventory_item($entered_grn->item_code) ? ($clearing_act ? $clearing_act : $stock_gl_code["inventory_account"]) :
            	    ($supplier["purchase_account"] ? $supplier["purchase_account"] : $stock_gl_code["cogs_account"]);

            	    $old = update_supp_received_items_for_invoice($entered_grn->id, $entered_grn->po_detail_item,
            	        $entered_grn->this_quantity_inv, $entered_grn->chg_price);

            	    // Since the standard cost is always calculated on basis of the po unit_price,
            	    // this is also the price that should be the base of calculating the price diff.
            	    // In cases where there is two different po invoices on the same delivery with different unit prices this will not work either

            	    $old_price = $old[2];

            	    $old_date = sql2date($old[1]);

            	    if (!is_inventory_item($entered_grn->item_code)) {
            	        /*
            	         * Service item
            	         */
            	        $tax = tax_calculator($entered_grn->supplier_tax_id,$entered_grn->this_quantity_inv * $entered_grn->chg_price,$supp_trans->tax_included);
//             	        $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $iv_act,
//             	            $dim, $dim2, $tax->price, $supp_trans->supplier_id);
//             	        $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $tax->purchasing_gl_code,
//             	            $dim, $dim2, $tax->value, $supp_trans->supplier_id, "", $ex_rate);
            	        $gl_trans->add_trans($iv_act, $tax->price,$dim, $dim2);
            	        $gl_trans->add_trans($tax->purchasing_gl_code, $tax->value,$dim, $dim2);
            	    } else {
            	        $ex_rate = get_exchange_rate_from_home_currency($supplier->curr_code, $old_date);

            	        $old_value = get_tax_free_price_for_item($entered_grn->item_code, $entered_grn->this_quantity_inv * $old_price,
            	            $supp_trans->tax_group_id, $supp_trans->tax_included);

            	        $tax = tax_calculator($entered_grn->supplier_tax_id,$entered_grn->this_quantity_inv * $old_price,$supp_trans->tax_included);
            	        // bug($tax);
            	        $currency = get_supplier_currency($supp_trans->supplier_id);

//             	        $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $iv_act,
//             	            $dim, $dim2, $tax->price, $supp_trans->supplier_id, "", $ex_rate);


//             	        $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $tax->purchasing_gl_code,
//             	            $dim, $dim2, $tax->value, $supp_trans->supplier_id, "", $ex_rate);

            	        $gl_trans->add_trans($iv_act, $tax->price,$dim, $dim2,'',$ex_rate);
            	        $gl_trans->add_trans($tax->purchasing_gl_code, $tax->value,$dim, $dim2,'',$ex_rate);

            	        $diff = get_diff_in_home_currency($supp_trans->supplier_id, $old_date, $date_, $old_value,
            	            $taxfree_line);

            	        $mat_cost = update_average_material_cost(null, $entered_grn->item_code,
            	            $diff/$entered_grn->this_quantity_inv, $entered_grn->this_quantity_inv, null, true);

            	        //Add GL transaction for GRN Provision in case of difference
            	        if ($diff != 0) {
            	            $gl_trans->add_trans($stock_gl_code["inventory_account"], $diff,$dim, $dim2,'GRN Provision');
//             	            $total += add_gl_trans($trans_type, $invoice_id, $date_, $stock_gl_code["inventory_account"],
//             	                $dim, $dim2, 'GRN Provision', $diff, null, null, null,
//             	                "The general ledger transaction could not be added for the GRN of the inventory item");

            	            //If QOH is 0 or negative then update_average_material_cost will be skipped
            	            //Thus difference in PO and Supp Invoice should be handled separately
            	            $qoh = get_qoh_on_date($entered_grn->item_code);
            	            if ($qoh <= 0){
            	                global $Refs;

            	                $id = get_next_trans_no(ST_JOURNAL);
            	                $ref = $Refs->get_next(ST_JOURNAL);
            	                $stock_id = $entered_grn->item_code;
            	                $stock_gl_code = get_stock_gl_code($stock_id);
            	                $memo = _("Supplier invoice adjustment for zero inventory of ").$stock_id." "._("Invoice")." ".$supp_trans->reference;
            	                //Reverse the inventory effect if $qoh <=0
            	                add_gl_trans_std_cost(ST_JOURNAL, $id, $date_,
            	                $stock_gl_code["inventory_account"],
            	                $dim, $dim2, $memo, -$diff);
            	                //GL Posting to inventory adjustment account
            	                add_gl_trans_std_cost(ST_JOURNAL, $id, $date_,
            	                $stock_gl_code["adjustment_account"],
            	                $dim, $dim2, $memo, $diff);

            	                add_audit_trail(ST_JOURNAL, $id, $date_);
            	                add_comments(ST_JOURNAL, $id, $date_, $memo);
            	                $Refs->save(ST_JOURNAL, $id, $ref);
            	            }
            	        }
            	    }
            	    add_or_update_purchase_data($supp_trans->supplier_id, $entered_grn->item_code, $entered_grn->chg_price);
            	}

            	if( !$invoice_no ){
            	    add_supp_invoice_item($trans_type, $invoice_id, $entered_grn->item_code,
            	    $entered_grn->item_description, 0, 	$entered_grn->chg_price, $line_tax/$entered_grn->this_quantity_inv,
            	    $entered_grn->this_quantity_inv, $entered_grn->id, $entered_grn->po_detail_item, "",null,$tax_id);

            	}


         } /* end of GRN postings */

         $total = 0;
         foreach ($gl_trans->trans AS $trans){
             $total += $this->gl_trans->add_gl_trans($trans,$supplier->curr_code);
         }

// bug('total='.$total);
//
// die('line 312');

         /*Post a balance post if $total != 0 */
         add_gl_balance($trans_type, $invoice_id, $date_, -$total, PT_SUPPLIER, $supp_trans->supplier_id); // ??

         add_comments($trans_type, $invoice_id, $date_, $supp_trans->Comments);

         $Refs->save($trans_type, $invoice_id, $supp_trans->reference);

         if ($invoice_no != 0) {
             $invoice_alloc_balance = get_supp_trans_allocation_balance(ST_SUPPINVOICE, $invoice_no);
             if ($invoice_alloc_balance > 0) { 	//the invoice is not already fully allocated

//                  $trans = get_supp_trans($invoice_id, ST_SUPPCREDIT);

                 $trans = get_supp_trans($invoice_id, $trans_type);
                 $total = -$trans['Total'];

                 $allocate_amount = ($invoice_alloc_balance > $total) ? $total : $invoice_alloc_balance;

                 /*Now insert the allocation record if > 0 */

                 if ($allocate_amount != 0) {
                     add_supp_allocation($allocate_amount, ST_SUPPCREDIT, $invoice_id, ST_SUPPINVOICE, $invoice_no,
                     $date_);
                     update_supp_trans_allocation(ST_SUPPINVOICE, $invoice_no);
                     update_supp_trans_allocation(ST_SUPPCREDIT, $invoice_id);
//                      bug('get trans type='.$trans_type);
                     exchange_variation($trans_type, $invoice_id, ST_SUPPINVOICE, $invoice_no, $date_,$allocate_amount, PT_SUPPLIER);
                 }

             }
         }

//          'ov_amount'=>$invoice_items_total,
//          'ov_gst'=>$item_added_tax,

//          $trans_tax_details = array(
//              'trans_type'=>$trans_type,
//              'trans_no'=>$invoice_id,
//              'tran_date'=>$date_,
//              'tax_type_id'=>$entered_gl_code->supplier_tax_id,
//              'rate'=>$tax->rate,
//              'ex_rate'=>get_exchange_rate_from_home_currency($supplier->curr_code, $date_),
//              'included_in_price'=>$supp_trans->tax_included,
//              'net_amount'=>$invoice_items_total,
//              'amount'=>$item_added_tax,
//              'memo'=>$supp_trans->supp_reference

//          );
// //          bug($trans_tax_details);die;
//          $this->trans_tax_detail_update($trans_tax_details);
//          die('go hrer');


         // 	bug($supp_trans);die('commit supp');
//          die('go here ='.$trans_type);
         $supp_trans->trans_no = $invoice_id;
         hook_db_postwrite($supp_trans, $supp_trans->trans_type);
         commit_transaction();

         return $invoice_id;
    }

    function write_supp_trans($trans_no=0,$data){
        if( !array_key_exists('supplier_id', $data) || !array_key_exists('tran_date', $data) ) return null;

        $date_ = date('Y-m-d',strtotime($data['tran_date']));
        $data['tran_date'] = $date_;
        $data['due_date'] = date('Y-m-d',strtotime($data['due_date']));

        $curr = get_supplier_currency($data['supplier_id']);
        $rate = get_exchange_rate_from_home_currency($curr, $date_);
//         $trans_no = 0;
        if( $trans_no && $this->check_supp_trans_existed($trans_no,$data['type']) ){
            $this->db->reset();

            $sql = $this->db->update('supp_trans',$data,array('trans_no'=>$trans_no,'type'=>$data['type']),1,true );
        } else {
            $this->db->reset();
            $trans_no = get_next_trans_no($data['type']);
            $data['trans_no'] = $trans_no;
            $sql = $this->db->insert('supp_trans',$data,true );
        }

        db_query($sql, "Cannot insert a supplier transaction record");
		return $trans_no;

    }

    function check_supp_trans_existed($trans_no,$type){
        $this->db->reset();
        $data = $this->db->where(array('trans_no'=>$trans_no,'type'=>$type))->get('supp_trans')->row();
        if( $data && isset($data->trans_no) ){
            return true;
        }
        return false;
//         bug($data);die;
    }

    function add_invoice_items($data){
        $this->db->reset();
        $data = $this->db->where(array('supp_trans_type'=>$data['supp_trans_type'],'supp_trans_no'=>$data['supp_trans_no']))->get('supp_invoice_items')->row();

        if ( !$data || !isset($data->supp_trans_no) ){
            $this->db->reset();
            $sql = $this->db->insert('supp_invoice_items',$data,true );
            db_query($sql, "Cannot insert a supplier transaction detail record");
        }

    }

    function trans_tax_detail_update($data){
        $this->db->reset();
        $where = array('trans_no'=>$data['trans_no'],'trans_type'=>$data['trans_type']);
        $exsits = $this->db->where($where)->get('trans_tax_details')->row();
        $this->db->reset();
        if( $exsits && isset($exsits->id) ){
            $sql = $this->db->insert('trans_tax_details',$data,true );
        } else {
            $sql = $this->db->update('trans_tax_details',$data,$where,1,true );
        }
        db_query($sql, "Cannot save trans tax details");
    }

    function get_purchase_tran($type, $trans_no,$stat_time='',$end_time='',$reference=''){
        $select='tran.*,';
        $select.="(tran.ov_amount+tran.ov_gst) AS Total, tran.ov_discount,";
        $select.="supl.supp_name,supl.supp_account_no,supl.curr_code,term.terms,supl.gst_no,supl.address";

       $this->db->select($select);
       $this->db->join('suppliers as supl','tran.supplier_id=supl.supplier_id');
       $this->db->join('payment_terms AS term', 'term.terms_indicator = supl.payment_terms', 'left');
       $this->db->where('tran.trans_no',$trans_no);
       $this->db->where('tran.type',$type);

      if($stat_time) {  $this->db->where('tran.tran_date >=',$stat_time);}
      if($end_time)
       {  $this->db->where('tran.tran_date <=',$end_time);}
      if($reference)
       {  $this->db->like('tran.reference',$reference);}

      $data = $this->db->get('supp_trans AS tran')->row();

      return $data;

    }

    function get_purchase_trans_details($trans_type,$trans_id){
        $this->db->where(array('supp_trans_no'=>$trans_id,'supp_trans_type'=>$trans_type));
        $data = $this->db->get('supp_invoice_items AS tran')->result();
       // bug( $this->db->last_query() );die;
        return $data;
    }

    // chieu
    function  get_bank_trans_ci($type, $trans_no=null, $person_type_id=null, $person_id=null)
    {
       $sql = "bt.*, act.*, IFNULL(abs(dt.ov_amount), IFNULL(ABS(st.ov_amount),bt.amount)) settled_amount" ;
       $this->db->select($sql);
               //->select('IFNULL(abs("dt.ov_amount"), IFNULL(abs("st.ov_amount"), bt.amount)) settled_amount,');
       $this->db->join('debtor_trans AS dt','dt.type=bt.type AND dt.trans_no=bt.trans_no','left');
       $this->db->join('debtors_master AS debtor','debtor.debtor_no = dt.debtor_no','left');
       $this->db->join('supp_trans AS st','st.type=bt.type AND st.trans_no=bt.trans_no','left');
       $this->db->join('suppliers AS supplier','supplier.supplier_id = st.supplier_id','left');
       $this->db->join('bank_accounts AS act','act.id=bt.bank_act','left');

       $this->db->where('bt.type',$type);
       $this->db->where('bt.trans_no',$trans_no);
       $this->db->where('bt.person_type_id',$person_type_id);
       $this->db->where('bt.person_id',$person_id);
       $this->db->order_by('trans_date, bt.id');
       //trans_date, bt.id

       $data =$this->db->get('bank_trans as bt');
       // bug( $this->db->last_query() );die;

    }

}