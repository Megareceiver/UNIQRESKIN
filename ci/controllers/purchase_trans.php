<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Purchase_trans extends ci {
	function __construct() {
		global $ci;
		$this->ci = $ci;
		$this->supp_trans_model = $ci->model('supplier_trans',true);
		$this->gl_trans = $this->ci->load_library('gl_trans',true);
	}

	function submit_trans($supp_trans_cart, $invoice_no=0){
		global $Refs;

		begin_transaction();
		hook_db_prewrite($supp_trans_cart, $supp_trans_cart->trans_type);
		$tax_total = 0;
		$supplier = get_supplier($supp_trans_cart->supplier_id);
		add_new_exchange_rate($supplier['curr_code'], $supp_trans_cart->tran_date, $supp_trans_cart->ex_rate);

		$invoice_items_total = $supp_trans_cart->calculator_total();

		if ($supp_trans_cart->trans_type == ST_SUPPCREDIT ){
			$invoice_items_total = -$invoice_items_total;
			$tax_total = - $tax_total;
			$supp_trans_cart->discount_total = -$supp_trans_cart->discount_total; // this isn't used at all...
			$item_added_tax = -$item_added_tax;
		}

		$purchase_tran = array(
			'supplier_id'=>$supp_trans_cart->supplier_id,
			'tran_date'=>$supp_trans_cart->tran_date,
			'due_date'=>$supp_trans_cart->due_date,
			'reference'=>$supp_trans_cart->reference,
			'supp_reference'=>$supp_trans_cart->supp_ref,
			'ov_amount'=>$invoice_items_total,
			'ov_gst'=>$supp_trans_cart->tax_total,
			'ov_discount'=>$supp_trans_cart->discount_total,
			'tax_included'=>$supp_trans_cart->tax_included,
			'fixed_access'=>$supp_trans_cart->fixed_access,
		);
		$invoice_id = $this->supp_trans_model->update_supp_trans($supp_trans_cart->trans_type,$supp_trans_cart->order_no,$purchase_tran);
		$net_diff = 0;
		if ($supp_trans_cart->trans_type == ST_SUPPCREDIT)
			$net_diff = -$net_diff;

		$posting_total = 0;

		$this->gl_trans->set_value('type',$supp_trans_cart->trans_type);
		$this->gl_trans->set_value('type_no',$invoice_id);
		$this->gl_trans->set_value('tran_date',$supp_trans_cart->tran_date);
		$this->gl_trans->set_value('person_id',$supp_trans_cart->supplier_id);

		$this->gl_trans->add_trans( $supplier["payable_account"], -$supp_trans_cart->invoice_total );

		// send trans for GL code
/*
		foreach ($supp_trans->gl_codes as $entered_gl_code) {
		    // GL Items are straight forward - just do the debit postings to the GL accounts specified -
		    // the credit is to creditors control act  done later for the total invoice value + tax
		    $tax = tax_calculator($entered_gl_code->supplier_tax_id,$entered_gl_code->amount,$supp_trans->tax_included);
		    $price = $tax->price;
		    if ($trans_type == ST_SUPPCREDIT){
		        // 		    $entered_gl_code->amount = -$entered_gl_code->amount;
		        $price = -$price;
		        $tax->value = -$tax->value;
		    }


		    $memo_ = $entered_gl_code->memo_;

		    $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $entered_gl_code->gl_code,
		        $entered_gl_code->gl_dim, $entered_gl_code->gl_dim2, $price, $supp_trans->supplier_id, "", 0, $memo_);

		    add_supp_invoice_gl_item($trans_type, $invoice_id, $entered_gl_code->gl_code,
		    $price, $memo_,null,$entered_gl_code->supplier_tax_id);

		    // store tax details if the gl account is a tax account
		    // 		if ($trans_type == ST_SUPPCREDIT){
		    // 		    $entered_gl_code->amount = -$entered_gl_code->amount;
		    // 		}

		    add_gl_tax_details($entered_gl_code->gl_code,
		    $trans_type, $invoice_id, $price,
		    $ex_rate, $date_, $supp_trans->supp_reference, $supp_trans->tax_included);

		    add_trans_tax_details($trans_type, $invoice_id,$entered_gl_code->supplier_tax_id, $tax->rate, $supp_trans->tax_included, $tax->value,$price, $ex_rate, $date_, $supp_trans->supp_reference);



		    $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_,
		        $tax->purchasing_gl_code, 0, 0, $tax->value,
		        $supp_trans->supplier_id,
		        "A general ledger transaction for the tax amount could not be added");
		}

		 */

		$clearing_act = get_company_pref('grn_clearing_act');
		foreach ( $supp_trans_cart->line_items AS $item){
		    if ($supp_trans_cart->trans_type == ST_SUPPCREDIT) {
		        $item->this_quantity_inv = -$item->this_quantity_inv;
// 		        set_grn_item_credited($entered_grn, $supp_trans->supplier_id, $invoice_id, $date_);
		    }

		    $stock_gl_code = get_stock_gl_code($item->stock_id);
            if( !$clearing_act ){
                $clearing_act = $stock_gl_code["inventory_account"];
            }

		    $dim = $supplier['dimension_id'] ? $supplier['dimension_id'] : $stock_gl_code['dimension_id'];
		    $dim2 = $supplier['dimension2_id'] ? $supplier['dimension2_id'] : $stock_gl_code['dimension2_id'];
		    if ($trans_type == ST_SUPPCREDIT) {
		        /*
		        $iv_act = (is_inventory_item($entered_grn->item_code) ? $stock_gl_code["inventory_account"] :
		            ($supplier["purchase_account"] ? $supplier["purchase_account"] : $stock_gl_code["cogs_account"]));

		        $tax = tax_calculator($entered_grn->supplier_tax_id,$entered_grn->this_quantity_inv * $entered_grn->chg_price,$supp_trans->tax_included);
		        $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $iv_act,
		            $dim, $dim2, $tax->price, $supp_trans->supplier_id);
		        $total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $tax->purchasing_gl_code,
		            $dim, $dim2, $tax->value, $supp_trans->supplier_id, "", $ex_rate);
                */
		    } else {

		        $iv_act = is_inventory_item($entered_grn->item_code) ? $clearing_act :
		        ($supplier["purchase_account"] ? $supplier["purchase_account"] : $stock_gl_code["cogs_account"]);
		    }
		}
		bug($this->gl_trans);

/*
		foreach ($supp_trans->gl_codes as $entered_gl_code) {
			// GL Items are straight forward - just do the debit postings to the GL accounts specified -
		 	//the credit is to creditors control act  done later for the total invoice value + tax
			$tax = tax_calculator($entered_gl_code->supplier_tax_id,$entered_gl_code->amount,$supp_trans->tax_included);
			$price = $tax->price;
			if ($trans_type == ST_SUPPCREDIT){
				// 		    $entered_gl_code->amount = -$entered_gl_code->amount;
				$price = -$price;
				$tax->value = -$tax->value;
			}


			$memo_ = $entered_gl_code->memo_;

			$total += add_gl_trans_supplier($trans_type, $invoice_id, $date_, $entered_gl_code->gl_code,
					$entered_gl_code->gl_dim, $entered_gl_code->gl_dim2, $price, $supp_trans->supplier_id, "", 0, $memo_);

			add_supp_invoice_gl_item($trans_type, $invoice_id, $entered_gl_code->gl_code,
			$price, $memo_,null,$entered_gl_code->supplier_tax_id);

			// store tax details if the gl account is a tax account
			// 		if ($trans_type == ST_SUPPCREDIT){
			// 		    $entered_gl_code->amount = -$entered_gl_code->amount;
			// 		}

			add_gl_tax_details($entered_gl_code->gl_code,
			$trans_type, $invoice_id, $price,
			$ex_rate, $date_, $supp_trans->supp_reference, $supp_trans->tax_included);

			add_trans_tax_details($trans_type, $invoice_id,$entered_gl_code->supplier_tax_id, $tax->rate, $supp_trans->tax_included, $tax->value,$price, $ex_rate, $date_, $supp_trans->supp_reference);



			$total += add_gl_trans_supplier($trans_type, $invoice_id, $date_,
					$tax->purchasing_gl_code, 0, 0, $tax->value,
					$supp_trans->supplier_id,
					"A general ledger transaction for the tax amount could not be added");
		}
*/

// 		bug( $supp_trans_cart->line_items);
		die('submit trans total='.$invoice_items_total.' gst_total='.$supp_trans_cart->tax_total);
		commit_transaction();

	}
}