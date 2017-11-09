<?php
class Supplier_Model {
	function __construct(){
		global $ci;

		$this->supp = $ci->db;
        $this->crm_model = $ci->model('crm',true);
        $this->common = $ci->model('common',true);
        $this->void = $ci->model('void',true);
        //$this->gl_trans = $ci->model('gl_trans',true);
        $this->gl_trans = module_model_load('trans','gl');
	}

	function item_options(){
		$options = array();
		$data = $this->supp->select('supplier_id AS id, supp_name AS name')->order_by('name', 'ASC')->get('suppliers')->result();
		if( $data ){
			foreach ($data AS $row){
				$options[$row->id] = $row->name;
			}
		}
		return $options;
	}

	function supplier_detail($supp_id=0,$field=null){
	    if (!$supp_id) return fasle;
	    $this->supp->reset();
		$data = $this->supp->select('*')->where('supplier_id',$supp_id)->get('suppliers')->row();


		if($field && isset($data->$field)){
			return $data->$field;
		} else {
			return $data;
		}
	}
	// chieu 21/8/2015
	function get_supplier_contacts($supplier_id, $action=null){
    	$results = array();
    	$res =$this->crm_model->get_crm_persons('supplier', $action, $supplier_id);
       // bug($res);die;
	while($contact = db_fetch($res))
		$results[] = $contact;

	return $results;
        }


        function get_alloc_supp_sql_ci($supplier_id, $type, $trans_no)
        {
            $sql = "trans.type AS trans_type,
                trans.trans_no,
		IF(trans.supp_reference='',trans.reference,trans.supp_reference) as reference,
 		trans.tran_date,
		supplier.supp_name,
		supplier.curr_code,
		ov_amount+ov_gst+ov_discount AS Total,
            ov_amount+ov_gst+ov_discount AS price,

		trans.due_date,
		trans.supplier_id,
		supplier.address, amt, supp_reference, trans.alloc AS line_total, (ov_amount+ov_gst+ov_discount-trans.alloc) AS left_alloc";

            $this->supp->select($sql,FALSE);
            $this->supp->join('suppliers as supplier', 'trans.supplier_id=supplier.supplier_id', 'left');
            $this->supp->join('supp_allocations as alloc', 'trans.trans_no = alloc.trans_no_to AND trans.type = alloc.trans_type_to', 'left');
            $this->supp->where('alloc.trans_no_from',$trans_no);
            $this->supp->where('alloc.trans_type_from',$type);
            $this->supp->where('trans.supplier_id',$supplier_id);
            $this->supp->group_by('trans_no');

            $data= $this->supp->get('supp_trans as trans')->result();
           // bug( $this->supp->last_query());die;
           // bug($data);die;
            return $data;
        }


    // end chieu 21/8/2015
    function bab_deb_load($date_current='',$days_left=1,$supplier_id=null,$page=1){
        if($page > 0){
            $page --;
        } else {
            $page = 0;
        }
            if( is_numeric($supplier_id) ){
                $this->supp->where('trans.supplier_id',$supplier_id);
            }
//             if($typedate=='M')
//             {
//                 $date_baddeb = explode("/", $date_current);
//                 $month_int=intval($date_baddeb[0])-1;
//                 $date_current = $this->GetEndMonth($month_int).'-'.$month_int.'-'.$date_baddeb[1];
//                 // bug($date_current);die;
//             }

            if( is_date($date_current) ){
                $date_current = strtotime($date_current);
                if( is_numeric($days_left) && $days_left > 0 ){
                    $date = $date_current-$days_left*60*60*24;
                }
               // bug(date('Y/m/d',$date));die;
                if( $date > 0 ){
                    $this->supp->where('trans.tran_date <=',date('Y/m/d',$date));
                }
            }
            $this->supp->where( array('trans.ov_amount >'=>0,'trans.type'=>ST_SUPPINVOICE));
            $this->supp->join('suppliers AS supp','supp.supplier_id = trans.supplier_id','left');


            // 	    $this->debtor->select('0 AS paid',false);

            $this->supp->select('trans.*,supp.supp_name AS debtor_name, supp.curr_code')->from('supp_trans AS trans');
            // 	    $this->debtor->select(' AS gl_count',false);

            // 	    $this->debtor->join('gl_trans AS gl',"gl.type_no = trans.debtor_no AND gl.type=trans.type",'left');
            $this->supp->where("( SELECT count(*) FROM gl_trans AS gl WHERE gl.type_no= trans.trans_no AND gl.type= trans.type AND gl.openning ='') >",0);
            $this->supp->select("( SELECT id FROM bad_debts AS baddeb WHERE baddeb.type_no= trans.trans_no AND baddeb.type= trans.type AND step=1) AS process");
            $this->supp->select("( SELECT id FROM bad_debts AS baddeb WHERE baddeb.type_no= trans.trans_no AND baddeb.type= trans.type AND step=2) AS paid");

            $tempdb = clone $this->supp;
            $data = $this->supp->limit(page_padding_limit,$page*page_padding_limit)->get()->result();

            return array('items'=>$data,'total'=>$tempdb->count_all_results());
        }

        function bab_deb_6month_find(){
            $this->supp->where( array('trans.ov_amount >'=>0,'trans.type'=>ST_SUPPINVOICE));
            $today = new DateTime();
            $day = $today->modify('-6 month');
            $this->supp->where('trans.tran_date <=',$today->format('Y/m/d'));
//             $this->supp->join('debtors_master AS deb','deb.debtor_no = trans.debtor_no','left');
            $this->supp->where('trans.trans_no NOT IN (SELECT type_no FROM bad_debts AS deb where deb.type= trans.type)');

            $this->supp->select('*')->from('supp_trans AS trans');
            $this->supp->where("( SELECT count(*) FROM gl_trans AS gl WHERE gl.type_no= trans.trans_no AND gl.type= trans.type AND gl.openning ='') >",0);
//             bug($this->supp->last_query() );
            $count = $this->supp->count_all_results();

// bug( $count );die;
            return $count;
        }


    function get_invoice($trans_no,$type){
        $this->supp->select('tran.*,supp.*');
        $this->supp->where(array('trans_no'=>$trans_no,'type'=>$type));
        $this->supp->join('suppliers AS supp', 'supp.supplier_id= tran.supplier_id', 'left');
        $this->supp->select('supp.supp_account_no, supp.gst_no AS tax_id');
//         $this->supp->join('locations AS local', 'local.loc_code = o.into_stock_location', 'left');
        $this->supp->join('payment_terms AS term', 'term.terms_indicator=supp.payment_terms', 'left');
        $this->supp->select('term.terms AS payment_terms_name');



        $data = $this->supp->get('supp_trans AS tran')->row();
        if( $data ){
            $data->items = $this->invoice_details($trans_no,$type);
        }

        return $data;
    }

    function invoice_details($trans_no,$type){
        $this->supp->select('ite.*, pro.units, pro.long_description ,ite.unit_price AS price, ite.quantity AS qty');

        $this->supp->join('stock_master AS pro', 'pro.stock_id=ite.po_detail_item_id', 'left');

        $this->supp->where(array('ite.supp_trans_no'=>$trans_no,'ite.supp_trans_type'=>$type));

        $items = $this->supp->group_by('ite.id')->get('supp_invoice_items AS ite')->result();
//         return $items;
// 	   bug( $this->supp->last_query() );
		foreach ($items AS $k=>$ite){
			if( isset($ite->gl_code) && $ite->gl_code ){
				$this->supp->reset();
				$gl_acc =  $this->supp->where('account_code',$ite->gl_code)->get('chart_master')->row();
				$items[$k]->stock_id = $ite->gl_code;
				$items[$k]->description = $gl_acc->account_name;
				$items[$k]->unit_price = 0;
			}
		}
		return $items;
// 	   bug($items);
// 	   die('quannh');
    }


    function goods_invoices(){

    }



    var $supp_trans_requirements = array('trans_no','type','supplier_id','tran_date');
    private function write_supp_trans($supp_trans){
        foreach ($this->supp_trans_requirements AS $field){
            if( !isset($supp_trans[$field]) ) {
                die("field $field not exist");
                return false;
            }
        }
        if ($supp_trans['rate'] == 0) {
            $curr = get_supplier_currency($supp_trans['supplier_id']);
            $supp_trans['rate'] = get_exchange_rate_from_home_currency($curr, $supp_trans['tran_date']);
        }

        $supp_trans['tran_date'] = date2sql($supp_trans['tran_date']);
        $supp_trans['due_date'] = ( !isset($supp_trans['due_date']) ) ? "0000-00-00" : date2sql($supp_trans['due_date']);

        if( !isset($supp_trans['trans_no']) || !$supp_trans['trans_no']  ){
            $supp_trans['trans_no'] = get_next_trans_no($supp_trans['type']);
        }

        $update_where = array(
            'trans_no'=>$supp_trans['trans_no'],
            'type'=>$supp_trans['type']
        );
        unset($supp_trans['type']);
        unset($supp_trans['trans_no']);

        $this->common->update($supp_trans,'supp_trans',$update_where,false);
        add_audit_trail($update_where['type'], $update_where['trans_no'], $supp_trans['tran_date']);

        return $update_where['trans_no'];
    }

    function supplier_invoice($cart,$invoice_number=0){
        global $Refs;
        $trans_type = $cart->trans_type;
        $date_ = $cart->tran_date;

        begin_transaction();
        hook_db_prewrite($cart, $trans_type);

        $supplier = $this->supplier_detail($cart->supplier_id);
        $clearing_act = get_company_pref('grn_clearing_act');
        add_new_exchange_rate($supplier->curr_code, $date_ , $cart->ex_rate);
        $ex_rate = get_exchange_rate_from_home_currency($supplier->curr_code, $date_);

        if ($cart->trans_no != 0 ) {
            $this->void->supplier_transaction($trans_type, $cart->trans_no);
//             void_trans_tax_details($trans_type, $cart->trans_no);
        }

        $update_where = array(
            'trans_no'=>$cart->trans_no,
            'type'=>$trans_type
        );

        $supp_trans = array(
            'trans_no'=>$cart->trans_no,
            'type'=>$trans_type,
            'supplier_id'=>$cart->supplier_id,
            'tran_date'=>date2sql( $date_ ),
            'due_date'=>date2sql( $cart->due_date ),
            'reference'=>$cart->reference,
            'supp_reference'=>$cart->supp_reference,
            'ov_amount'=>0,
            'ov_gst'=>0,
            'rate'=>$cart->ex_rate,
            'ov_discount'=>$cart->ov_discount,
            'tax_included'=>$cart->tax_included,
            'fixed_access'=>$cart->fixed_access,
            'cheque'=>null,
            'reason'=>$cart->reason
        );

        $invoice_items_total = $cart->get_items_total();
        $item_added_tax = 0;

        if ($trans_type == ST_SUPPCREDIT){
            $invoice_items_total = - $invoice_items_total;
            $tax_total = -$tax_total;
            $supp_trans['ov_discount'] = -$supp_trans['ov_discount'];
            $item_added_tax = -$item_added_tax;
        }

        $invoice_no = $this->write_supp_trans($supp_trans);
        $gl_trans = new gl_trans();
        $gl_trans->set_value( array( 'type'=>$trans_type, 'type_no'=>$invoice_no, 'tran_date'=>$date_,'person_type_id'=>PT_SUPPLIER,'person_id'=>$cart->supplier_id) );
        if ($trans_type == ST_SUPPCREDIT)
             $net_diff = -$net_diff;


        $ov_amount = $ov_gst = 0;
        $payable = 0;
        $rounding_difference = 0;
         foreach ($cart->gl_codes as $gl_code) {
             $tax = tax_calculator($gl_code->supplier_tax_id,$gl_code->amount,$cart->tax_included);
             $price = $tax->price;
             $payable -= ( $tax->price +  $tax->value );
             $ov_amount+= $tax->price;
             $ov_gst+= $tax->value;
             $invoice_gl_item = array(
                 'supp_trans_type'=>$trans_type,
                 'supp_trans_no'=>$invoice_no,
                 'gl_code'=>$gl_code->gl_code,
                 'unit_price'=> ( $cart->tax_included )? $tax->price : $gl_code->amount,
                 'unit_tax'=>$tax->value,
                 'quantity'=>0,
                 'memo_'=>$gl_code->memo_,
                 'tax_type_id'=>$gl_code->supplier_tax_id
             );
             $this->common->update($invoice_gl_item,'supp_invoice_items',null,false);

             if ($trans_type == ST_SUPPCREDIT){
                 $price = -$price;
                 $tax->value = -$tax->value;
             }
             $memo_ = $gl_code->memo_;
            $trans_tax_detail = array(
                'trans_type'=>$trans_type,
                'trans_no'=>$invoice_no,
                'tran_date'=>$date_,
                'tax_type_id'=>$gl_code->supplier_tax_id,
                'rate'=>$tax->rate,
                'ex_rate'=>$ex_rate,
                'included_in_price'=>$cart->tax_included,
                'net_amount'=>$price,
                'amount'=>$tax->value,
                'memo'=>$cart->supp_reference
            );
            $this->common->update($trans_tax_detail,'trans_tax_details',null,false);
            // add_trans_tax_details($trans_type, $invoice_id,$entered_gl_code->supplier_tax_id, $tax->rate, $supp_trans->tax_included, $tax->value,$price, $ex_rate, $date_, $supp_trans->supp_reference);
            $gl_trans->add_trans($gl_code->gl_code, $price ,$gl_code->gl_dim,$gl_code->gl_dim2,$gl_code->memo_);
//             $gl_trans->add_trans($gl_code->gl_code, $price,$gl_code->gl_dim,$gl_code->gl_dim2);
            if( $tax->value && $tax->value !=0 ){
                $gl_trans->add_trans($tax->purchasing_gl_code, $tax->value);
            }
            $rounding_difference++;

         }

        if( !empty($cart->grn_items)) foreach ($cart->grn_items as $line_no => $grn){

            $tax = tax_calculator($grn->supplier_tax_id,$grn->this_quantity_inv * $grn->chg_price,$cart->tax_included);
            $payable -= ( $tax->price +  $tax->value );

             if ($trans_type == ST_SUPPCREDIT) {
                 $grn->this_quantity_inv = -$grn->this_quantity_inv;
                 set_grn_item_credited($grn, $cart->supplier_id, $invoice_no, $date_);
             }

            $stock_gl_code = get_stock_gl_code($grn->item_code);

            $dim = $supplier->dimension_id ? $supplier->dimension_id : $stock_gl_code['dimension_id'];
            $dim2 = $supplier->dimension2_id ? $supplier->dimension2_id : $stock_gl_code['dimension2_id'];
            if ($trans_type == ST_SUPPCREDIT) {


                $iv_act = (is_inventory_item($grn->item_code) ? $stock_gl_code["inventory_account"] :
            	        ($supplier->purchase_account ? $supplier->purchase_account : $stock_gl_code["cogs_account"]));

                $gl_trans->add_trans($iv_act, $tax->price);
                if( $tax->value && $tax->value !=0 ){
                    $gl_trans->add_trans($tax->purchasing_gl_code, $tax->value);
                }

            } else {

                if( is_inventory_item($grn->item_code) ){
                    $iv_act = $clearing_act ? $clearing_act : $stock_gl_code["inventory_account"];
                } else {
                    $iv_act = $supplier->purchase_account ? $supplier->purchase_account : $stock_gl_code["cogs_account"];
                }
//             	$iv_act = is_inventory_item($grn->item_code) ? ($clearing_act ? $clearing_act : $stock_gl_code["inventory_account"]) :
//             	    ($supplier->purchase_account ? $supplier->purchase_account : $stock_gl_code["cogs_account"]);

            	$old = update_supp_received_items_for_invoice($grn->id, $grn->po_detail_item, $grn->this_quantity_inv, $grn->chg_price);

            	$old_price = $old[2];
            	$old_date = sql2date($old[1]);

            	if (!is_inventory_item($grn->item_code)) { /* Service item */

            	    $gl_trans->add_trans($iv_act, $tax->price);
            	    if( $tax->value && $tax->value !=0 ){
            	        $gl_trans->add_trans($tax->purchasing_gl_code, $tax->value);
            	    }
                } else {


                    $ex_rate = get_exchange_rate_from_home_currency($supplier->curr_code, $old_date);

                    $old_value = get_tax_free_price_for_item($grn->item_code, $grn->this_quantity_inv * $old_price, $grn->supplier_tax_id, $supp_trans['tax_included']);
        	        $tax = tax_calculator($grn->supplier_tax_id,$grn->this_quantity_inv * $old_price,$cart->tax_included);

        	        $currency = get_supplier_currency($cart->supplier_id);

        	        $gl_trans->add_trans($iv_act, $tax->price);
        	        if( $tax->value && $tax->value !=0 ){
        	            $gl_trans->add_trans($tax->purchasing_gl_code, $tax->value);
        	        }

        	        $diff = get_diff_in_home_currency($cart->supplier_id, $old_date, $date_, $old_value,$tax->price);

        	        $mat_cost = update_average_material_cost(null, $grn->item_code, $diff/$grn->this_quantity_inv, $grn->this_quantity_inv, null, true);

        	        if ($diff != 0) { //Add GL transaction for GRN Provision in case of difference
        	            $gl_trans->add_trans($stock_gl_code["inventory_account"], $diff,$dim, $dim2);

        	            //If QOH is 0 or negative then update_average_material_cost will be skipped
        	            //Thus difference in PO and Supp Invoice should be handled separately
        	            $qoh = get_qoh_on_date($grn->item_code);
        	            if ($qoh <= 0){
        	                $id = get_next_trans_no(ST_JOURNAL);
        	                $ref = $Refs->get_next(ST_JOURNAL);
        	                $stock_id = $grn->item_code;
        	                $stock_gl_code = get_stock_gl_code($stock_id);
        	                $memo = _("Supplier invoice adjustment for zero inventory of ").$stock_id." "._("Invoice")." ".$cart->reference;
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
        	    add_or_update_purchase_data($cart->supplier_id, $grn->item_code, $grn->chg_price);
        	}

        	$ov_amount+= $tax->price;
        	$ov_gst+= $tax->value;

        	$invoice_gl_item = array(
        	    'supp_trans_type'=>$trans_type,
        	    'supp_trans_no'=>$invoice_no,
        	    'stock_id'=>$grn->item_code,
        	    'description'=>$grn->item_description,
        	    'gl_code'=>0,
//         	    'unit_price'=> $grn->chg_price,
        	    'unit_price'=> ( $cart->tax_included )? $tax->price : $grn->chg_price,
        	    'unit_tax'=>$tax->value/$grn->this_quantity_inv,
        	    'quantity'=>$grn->this_quantity_inv,
        	    'grn_item_id'=>$grn->id,
        	    'po_detail_item_id'=>$grn->po_detail_item,

//         	    'memo_'=>$gl_code->memo_,
        	    'tax_type_id'=>$grn->supplier_tax_id
        	);
        	$this->common->update($invoice_gl_item,'supp_invoice_items',null,false);

        	$rounding_difference++;
         } /* end of GRN postings */

         $this->common->update($supp_trans,'supp_trans',$update_where,false);

         $this->common->update(array('ov_amount'=>$ov_amount,'ov_gst'=>$ov_gst),'supp_trans',array('trans_no'=>$invoice_no,'type'=>$trans_type),false);

         $gl_trans->add_trans($supplier->payable_account, -($ov_amount+ $ov_gst) ,0,0);

         $total = 0;

         foreach ($gl_trans->trans AS $trans){
             $total += $this->gl_trans->add_gl_trans($trans);
         }

         /*Post a balance post if $total != 0 */
         $balace_account = $gl_trans->balance(-$total);

         if( abs($total) <=  $rounding_difference*0.01 ){
             $balace_account['account'] =get_company_pref('rounding_difference_act');
         }
         $this->gl_trans->add_gl_trans($balace_account);
//          add_gl_balance($trans_type, $invoice_no, $date_, -$total, PT_SUPPLIER, $supp_trans->supplier_id); // ??
        if( !input_get('reinvoice') ){
            add_comments($trans_type, $invoice_no, $date_, $cart->Comments);
        }
         
         $Refs->save($trans_type, $invoice_no, $cart->reference);

         if ($invoice_number != 0){
             $invoice_alloc_balance = get_supp_trans_allocation_balance(ST_SUPPINVOICE, $invoice_number);
             if ($invoice_alloc_balance > 0) { 	//the invoice is not already fully allocated
                 $trans = get_supp_trans($invoice_no, ST_SUPPCREDIT);
                 $total = -$trans['Total'];
                 $allocate_amount = ($invoice_alloc_balance > $total) ? $total : $invoice_alloc_balance;

                 /*Now insert the allocation record if > 0 */
                 if ($allocate_amount != 0){
                     add_supp_allocation($allocate_amount, ST_SUPPCREDIT, $invoice_no, ST_SUPPINVOICE, $invoice_number,
                     $date_);
                     update_supp_trans_allocation(ST_SUPPINVOICE, $invoice_number);
                     update_supp_trans_allocation(ST_SUPPCREDIT, $invoice_no);

                     exchange_variation(ST_SUPPCREDIT, $invoice_no, ST_SUPPINVOICE, $invoice_number, $date_,
                     $allocate_amount, PT_SUPPLIER);
                 }
             }
         }
         // 	bug($supp_trans);die('commit supp');

         $cart->trans_no = $invoice_no;
         hook_db_postwrite($cart, $trans_type);
//          die('end repost');
         commit_transaction();

         return $invoice_no;
    }
}