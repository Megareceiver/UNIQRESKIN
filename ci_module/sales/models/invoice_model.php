<?php
class Sales_Invoice_Model extends CI_Model {
    function __construct(){
        $this->customer_trans = module_model_load('customer_trans','sales');
        $this->gl_trans = module_model_load('trans','gl');
        $this->payment_term_model = module_model_load('payment_term','company');
    }

//     function write_invoice($invoice,$repost=false){

//         global $Refs;

//         $customer = get_customer($invoice->customer_id);

//         $trans_no = $invoice->trans_no;
//         if ( is_array($trans_no) ){
//             $trans_no = key($trans_no);
//             if( !$repost ){
//                 $this->delivery_update($trans_no,$invoice);
//             }

//         }

//         $date_ = $invoice->document_date;
//         $charge_shipping = $invoice->freight_cost;

//         begin_transaction();
//         hook_db_prewrite($invoice, ST_SALESINVOICE);

//         if ($trans_no != 0 && !isset($_GET['bug']) ) {

//             delete_comments(ST_SALESINVOICE, $trans_no);
//             void_gl_trans(ST_SALESINVOICE, $trans_no, true);

//             if( !$repost ){
//                 void_cust_allocations(ST_SALESINVOICE, $trans_no);
//                 $this->customer_trans->remove_trans_detail(ST_SALESINVOICE, $trans_no);
//             }
//             void_trans_tax_details(ST_SALESINVOICE, $trans_no);

//             $invoice_new = false;
//         } else {
//             $invoice_new = true;
//         }

//         $currency_code = $invoice->customer_currency;
//         if( !$currency_code ){
//             $currency_code = $customer['curr_code'];
//         }

//         add_new_exchange_rate($currency_code, $date_, $invoice->ex_rate);

//         // offer price values without freight costs
//         // 	$items_total = $invoice->get_items_total_dispatch();
//         $items_total = 0;
//         $freight_tax = $invoice->get_shipping_tax();

//         update_customer_trans_version(get_parent_type(ST_SALESINVOICE), $invoice->src_docs);

//         // Insert/update the debtor_trans
//         $sales_order = $invoice->order_no;
//         if (is_array($sales_order))
//             $sales_order = $sales_order[0]; // assume all crucial SO data are same for every delivery


//         $cus_trans = array(
//             'debtor_no'=>$invoice->customer_id,
//             'branch_code'=>$invoice->Branch,
//             'tran_date'=>$date_,
//             'due_date'=>$invoice->due_date,
//             'reference'=>$invoice->reference,
//             'tpe'=>$invoice->sales_type,
//             'order_'=>$sales_order,
//             'ov_amount'=>0,
//             //'ov_discount'=>0,
//             //'ov_gst'=>$items_added_tax,
//             'ov_freight'=>$invoice->freight_cost,
//             //'ov_freight_tax'=>$freight_added_tax,
//             //'rate'=>0,
//             'ship_via'=>$invoice->ship_via,
//             //'alloc'=>0,
//             'dimension_id'=>$invoice->dimension_id,
//             'dimension2_id'=>$invoice->dimension2_id,
//             'payment_terms'=>$invoice->payment
//         );
//         $invoice_no = $this->customer_trans->write_trans(ST_SALESINVOICE, $trans_no, $cus_trans);

//         if ($trans_no == 0) {
//             $invoice->trans_no = array($invoice_no=>0);
//         }

//         $invoice_tax_total = 0;
//         $branch_data = get_branch_accounts($invoice->Branch);

//         $gl_trans = new gl_trans();
//         $gl_trans->set_value( array( 'type'=>ST_SALESINVOICE, 'type_no'=>$invoice_no, 'tran_date'=>$date_, 'person_type_id'=>PT_CUSTOMER, 'person_id'=>$invoice->customer_id) );
//         // bug($invoice->line_items);
//         $ov_amount = $ov_discount = $ov_gst = 0;
//         foreach ($invoice->line_items as $line_no => $invoice_line) {
//             $price = $invoice_line->price*(1-$invoice_line->discount_percent)*$invoice_line->qty_dispatched;
//             $item_tax_detail = tax_calculator($invoice_line->tax_type_id,$price,$invoice->tax_included);
//             $items_total += $item_tax_detail->price + $item_tax_detail->value;

//             $cus_trans_detail = array(
//                 'tax_type_id'=>$invoice_line->tax_type_id,
//                 'debtor_trans_no'=>$invoice_no,
//                 'stock_id'=>$invoice_line->stock_id,
//                 'description'=>$invoice_line->item_description,
//                 'quantity'=>$invoice_line->qty_dispatched,
//                 'unit_price'=>$invoice_line->line_price(),
//                 'unit_tax'=>0,
//                 'discount_percent'=>$invoice_line->discount_percent,
//                 'standard_cost'=>$invoice_line->standard_cost,
//                 'src_id'=>$invoice_line->src_id

//             );

//             if ( isset($invoice_line->qty_done) ){
//                 $cus_trans_detail['qty_done'] = $invoice_line->qty_done;
//             }
//             $line_id = 0;
//             if( $invoice_line->id ) {
//                 $line_id = $invoice_line->id;
//             }
//             if ( $invoice_line->qty_dispatched != 0 ){
//                 $cus_trans_detail['unit_tax'] = $item_tax_detail->value;
//             }

//             $this->customer_trans->write_trans_detail(ST_SALESINVOICE, $invoice_no, $cus_trans_detail,true);

//             // Update delivery items for the quantity invoiced
//             if ($invoice_line->qty_old != $invoice_line->qty_dispatched){
//                 // 			update_parent_line(ST_SALESINVOICE, $invoice_line->src_id, ($invoice_line->qty_dispatched-$invoice_line->qty_old));
//                 $cus_trans_detail_update = array( 'qty_done'=> "qty_done +" .($invoice_line->qty_dispatched-$invoice_line->qty_old) );
//                 $this->customer_trans->update_trans_detail(ST_SALESINVOICE, $invoice_line->src_id, $cus_trans_detail_update );
//             }


//             if ( $invoice_line->qty_dispatched != 0 && $invoice_line->line_price() != 0) {

//                 $invoice_tax_total += $item_tax_detail->value;

//                 $stock_gl_code = get_stock_gl_code($invoice_line->stock_id);
//                 $sales_account = $stock_gl_code['sales_account'];

//                 if( $branch_data['sales_account'] != "" ) {
//                     $sales_account =$branch_data['sales_account'];
//                 }
//                 if( !$sales_account ){
//                     $sales_account = $this->config->get_sys_pref_val('default_sales_act');
//                 }


//                 // 2008-08-01. If there is a Customer Dimension, then override with this, else take the Item Dimension (if any)
//                 $dim = ($invoice->dimension_id != $customer['dimension_id'] ? $invoice->dimension_id : ($customer['dimension_id'] != 0 ? $customer["dimension_id"] : $stock_gl_code["dimension_id"]));
//                 $dim2 = ($invoice->dimension2_id != $customer['dimension2_id'] ? $invoice->dimension2_id : ($customer['dimension2_id'] != 0 ? $customer["dimension2_id"] : $stock_gl_code["dimension2_id"]));


//                 // 				$ov_amount += ($item_tax_detail->price*$invoice_line->qty_dispatched );
// //                 $item_amount = $item_tax_detail->price + ( $invoice_line->price*$invoice_line->qty_dispatched*($invoice_line->discount_percent) ) ;
//                 $item_discount = $invoice_line->price*$invoice_line->qty_dispatched*($invoice_line->discount_percent);
//                 $item_amount = $item_tax_detail->price;
//                 // 				bug($invoice_line); die('check sale account');
//                 $ov_amount += $item_amount;
//                 $gl_trans->add_trans($sales_account, - $item_amount ,$dim,$dim2);
//                 // 				bug($invoice_line);
//                 if( $invoice_line->tax_type_id ){
//                     $ex_rate = get_exchange_rate_from_home_currency(get_customer_currency($invoice->customer_id), $date_);

//                     $tax_trans = array(
//                         'trans_type'=>ST_SALESINVOICE,
//                         'trans_no'=>$invoice_no,
//                         'tran_date'=>$date_,
//                         'tax_type_id'=>$invoice_line->tax_type_id,
//                         'included_in_price'=>$invoice->tax_included,
//                         'ex_rate'=>$ex_rate,
//                         'memo'=>$invoice->reference,
//                         'rate'=>$item_tax_detail->rate,
//                         'amount'=>$item_tax_detail->value,
//                         'net_amount'=>$item_tax_detail->price

//                     );
//                     $this->gl_trans->add_tax_trans_detail($tax_trans);

//                     $gl_tax_account =$item_tax_detail->sales_gl_code;

//                     if( !$gl_tax_account ) {

//                         $gl_tax_account = 2150;
//                     }
//                     $item_tax = $item_tax_detail->value;
//                     $ov_gst += $item_tax;
//                     $gl_trans->add_trans( $gl_tax_account, - $item_tax );
//                     // 					$item_tax+=$item_tax_detail->value*$invoice_line->quantity;
//                     // 					bug('tax='.$item_tax);
//                 }


//                 if ( $invoice_line->discount_percent != 0) {
//                     $gl_discount_account = $branch_data["sales_discount_account"];
//                     if( !$gl_discount_account ) {
//                         $gl_discount_account = $ci->config->get_sys_pref_val('default_sales_discount_act');
//                     }
//                     $item_discount = $invoice_line->price*$invoice_line->qty_dispatched*($invoice_line->discount_percent);
//                     $ov_discount += $item_discount;
//                     // 					$ov_amount -= $item_discount;
//                     $gl_trans->add_trans( $gl_discount_account,$item_discount );
//                     // 					bug('discount='.$item_tax_detail->price*$invoice_line->qty_dispatched*$invoice_line->discount_percent);
//                 } // end of if discount !=0


//             } // quantity dispatched is more than 0
//         } // end of delivery_line loop

//         // 		bug($gl_trans->trans);

//         $debtor_trans_update = array('ov_amount'=>$ov_amount-$ov_discount,'ov_gst'=>$ov_gst,'ov_discount'=>$ov_discount);
//         // 		$invoice_allocations = $this->db->where(array('type'=>'ST_CUSTPAYMENT','debtor_no'=>$invoice->customer_id,'trans_no_to'=>$invoice_no))->get('cust_allocations')->row();
//         $this->db->reset();
//         $this->customer_trans->update_trans(ST_SALESINVOICE, $invoice_no, array('ov_amount'=>$ov_amount-$ov_discount,'ov_gst'=>$ov_gst,'ov_discount'=>$ov_discount));


//         if ( ($items_total + $charge_shipping) != 0) {
//             $receivables_account = $branch_data["receivables_account"];
//             if( !$receivables_account ) {
//                 $receivables_account = $this->config->get_sys_pref_val('debtors_act');
//             }

//             $gl_trans->add_trans( $receivables_account, $items_total + $charge_shipping + $invoice->get_shipping_tax() -$ov_discount );
//         }
//         // 		bug('total='.($items_total + $charge_shipping + $invoice->get_shipping_tax()));
//         // 		bug('$charge_shipping='.$charge_shipping);
//         // 		die('bug');

//         if ($charge_shipping != 0) {
//             $company_data = get_company_prefs();
//             $gl_trans->add_trans( $company_data["freight_act"], -$invoice->get_tax_free_shipping());
//         }


//         $total = 0;

//         //
//         foreach ($gl_trans->trans AS $trans){

//             $total += $this->gl_trans->add_gl_trans($trans,$currency_code);
//             // 			bug($total);
//         }
//         if( isset($_GET['bug']) ) {
//             bug($gl_trans->trans); die('bug total = '.$total);
//         }
//         // bug($gl_trans->trans);
//         // bug($total);die;
//         // Post a balance post if $total != 0

//         if( abs($total) < 0.02 ){
//             // 		    die('go here');
//             // 		    add_gl_balance(ST_SALESINVOICE, $invoice_no, $date_, -$total, PT_CUSTOMER, $invoice->customer_id);
//             add_gl_trans(ST_SALESINVOICE, $invoice_no, $date_, get_company_pref('rounding_difference_act'), 0, 0, "", -$total, null, PT_CUSTOMER, $invoice->customer_id, "The balanced GL transaction could not be inserted");
//         } else {
//             add_gl_balance(ST_SALESINVOICE, $invoice_no, $date_, -$total, PT_CUSTOMER, $invoice->customer_id);
//         }



//         add_comments(ST_SALESINVOICE, $invoice_no, $date_, $invoice->Comments);

//         $this->db->reset();
//         $cust_allocations = $this->db->where(array('trans_no_from'=>ST_CUSTPAYMENT,'trans_type_to'=>ST_SALESINVOICE,'trans_no_to'=>$invoice_no))->get('cust_allocations')->row();



//         // 		if (!$cust_allocations ) {
//         if( $invoice_new ) {
//             $debtor_trans = $this->db->where(array('trans_no'=>$invoice_no,'type'=>ST_SALESINVOICE))->get('debtor_trans')->row();

//             //             if( isset($debtor_trans->trans_no) && $debtor_trans->alloc <= 0 ){
//             //                 $Refs->save(ST_SALESINVOICE, $invoice_no, $invoice->reference);

//             if ($invoice->payment_terms['cash_sale'] && $invoice->pos['pos_account']) {
//                 $amount = $items_total + $items_added_tax + $invoice->freight_cost + $freight_added_tax;
//                 $discount = 0; // $invoice->cash_discount*$amount;

//                 $pmtno = write_customer_payment(0, $invoice->customer_id,
//                     $invoice->Branch, $invoice->pos['pos_account'], $date_,
//                     $Refs->get_next(ST_CUSTPAYMENT), $amount-$discount, $discount,_('Cash invoice').' '.$invoice_no);

//                 add_cust_allocation($amount, ST_CUSTPAYMENT, $pmtno, ST_SALESINVOICE, $invoice_no);

//                 update_debtor_trans_allocation(ST_SALESINVOICE, $invoice_no, $amount);
//                 update_debtor_trans_allocation(ST_CUSTPAYMENT, $pmtno, $amount);
//             }
//             //             }
//         }

//         $Refs->save(ST_SALESINVOICE, $invoice_no, $invoice->reference);


//         // 		die('end post invoice');
//         hook_db_postwrite($invoice, ST_SALESINVOICE);
//         commit_transaction();

//         // 		die(' end write invoice no='.$invoice_no);
//         return $invoice_no;
//     }

    private function delivery_update($trans_no,$cart){
        $delivery = clone $cart;
        $debtor_trans = $this->db->where( array('trans_no'=>$trans_no,'type'=>ST_SALESINVOICE) )->get('debtor_trans')->row();
        if( $debtor_trans->order_ ){
            $debtor_delivery_trans = $this->db->where( array('order_'=>$debtor_trans->order_,'type'=>ST_CUSTDELIVERY) )->get('debtor_trans')->row();
            $delivery->trans_no = $debtor_delivery_trans->trans_no;

            $this->write_delivery($delivery,0);
        }
    }

    function write_sales_delivery(&$delivery,$bo_policy=false){
        global $Refs, $ci;

        $trans_no = $delivery->trans_no;

        if (is_array($trans_no)) $trans_no = key($trans_no);

        begin_transaction();
        $delivery->bo_policy = $bo_policy;
        hook_db_prewrite($delivery, ST_CUSTDELIVERY);

        $customer = get_customer($delivery->customer_id);

        add_new_exchange_rate($customer['curr_code'], $delivery->document_date, $delivery->ex_rate);

        $delivery_items_total = $delivery->get_items_total_dispatch();
        $freight_tax = $delivery->get_shipping_tax();

        // mark sales order for concurrency conflicts check
        update_sales_order_version($delivery->src_docs);

        $tax_total = 0;
        //TUANVT5
        $taxes = $delivery->get_taxes_new(); // all taxes with freight_tax

        foreach ($taxes as $taxitem) {
            $taxitem['Value'] =  round2($taxitem['Value'], user_price_dec());
            $tax_total +=  $taxitem['Value'];
        }
        /* Insert/update the debtor_trans */
        //TUANVT6
        $delivery_no = write_customer_trans(ST_CUSTDELIVERY, $trans_no, $delivery->customer_id,
            $delivery->Branch, $delivery->document_date, $delivery->reference,
            $delivery_items_total, 0,
            $delivery->tax_included ? 0 : $tax_total-$freight_tax,
            $delivery->freight_cost,
            $delivery->tax_included ? 0 : $freight_tax,
            $delivery->sales_type, $delivery->order_no,
            $delivery->ship_via, $delivery->due_date, 0, 0, $delivery->dimension_id,
            $delivery->dimension2_id, $delivery->payment);

        if ($trans_no == 0) {
            $delivery->trans_no = array($delivery_no=>0);
        } else {
            void_gl_trans(ST_CUSTDELIVERY, $delivery_no, true);
            void_stock_move(ST_CUSTDELIVERY, $delivery_no);
            void_trans_tax_details(ST_CUSTDELIVERY, $delivery_no);
            delete_comments(ST_CUSTDELIVERY, $delivery_no);

            $cus_trans = $ci->model('customer_trans',true);
            $cus_trans->remove_trans_detail(ST_CUSTDELIVERY, $delivery_no);
        }

        foreach ($delivery->line_items as $line_no => $delivery_line) {
            if( method_exists($delivery_line, 'line_price') ){
                $line_price = $delivery_line->line_price();
            } else {
                $line_price = $delivery_line->price;
            }

            $line_taxfree_price = get_tax_free_price_for_item($delivery_line->stock_id,
                $delivery_line->price, 0, $delivery->tax_included,
                $delivery->tax_group_array);

            $line_tax = get_full_price_for_item($delivery_line->stock_id, $delivery_line->price,
                0, $delivery->tax_included, $delivery->tax_group_array) - $line_taxfree_price;

            //if ($trans_no != 0) // Inserted 2008-09-25 Joe Hunt. This condition is removed after experience by Chaitanya
            $delivery_line->standard_cost = get_standard_cost($delivery_line->stock_id);

            /* add delivery details for all lines */
            //TUANVT6
            write_customer_trans_detail_item($delivery_line->tax_type_id,ST_CUSTDELIVERY, $delivery_no, $delivery_line->stock_id,
            $delivery_line->item_description, $delivery_line->qty_dispatched,
            $delivery_line->line_price(), $line_tax,
            $delivery_line->discount_percent, $delivery_line->standard_cost, $delivery_line->src_id,
            $trans_no ? $delivery_line->id : 0);

            // Now update sales_order_details for the quantity delivered
            if ($delivery_line->qty_old != $delivery_line->qty_dispatched)
                update_parent_line(ST_CUSTDELIVERY, $delivery_line->src_id,
                    $delivery_line->qty_dispatched-$delivery_line->qty_old);

            if ($delivery_line->qty_dispatched != 0) {
                add_stock_move_customer(ST_CUSTDELIVERY, $delivery_line->stock_id, $delivery_no,
                $delivery->Location, $delivery->document_date, $delivery->reference,
                -$delivery_line->qty_dispatched, $delivery_line->standard_cost,1,
                $line_price, $delivery_line->discount_percent);


                $stock_gl_code = get_stock_gl_code($delivery_line->stock_id);

                /* insert gl_trans to credit stock and debit cost of sales at standard cost*/
                if (is_inventory_item($delivery_line->stock_id) && $delivery_line->standard_cost != 0) {

                    /*first the cost of sales entry*/
                    // 2008-08-01. If there is a Customer Dimension, then override with this,
                    // else take the Item Dimension (if any)
                    $dim = ($delivery->dimension_id != $customer['dimension_id'] ? $delivery->dimension_id :
                        ($customer['dimension_id'] != 0 ? $customer["dimension_id"] : $stock_gl_code["dimension_id"]));
                    $dim2 = ($delivery->dimension2_id != $customer['dimension2_id'] ? $delivery->dimension2_id :
                        ($customer['dimension2_id'] != 0 ? $customer["dimension2_id"] : $stock_gl_code["dimension2_id"]));

                    add_gl_trans_std_cost(ST_CUSTDELIVERY, $delivery_no,
                    $delivery->document_date, $stock_gl_code["cogs_account"], $dim, $dim2, "",
                    $delivery_line->standard_cost * $delivery_line->qty_dispatched,
                    PT_CUSTOMER, $delivery->customer_id,
                    "The cost of sales GL posting could not be inserted");

                    /*now the stock entry*/

                    add_gl_trans_std_cost(ST_CUSTDELIVERY, $delivery_no, $delivery->document_date,
                    $stock_gl_code["inventory_account"], 0, 0, "",
                    (-$delivery_line->standard_cost * $delivery_line->qty_dispatched),
                    PT_CUSTOMER, $delivery->customer_id,
                    "The stock side of the cost of sales GL posting could not be inserted");

                } /* end of if GL and stock integrated and standard cost !=0 */

            } /*quantity dispatched is more than 0 */
        } /*end of order_line loop */

        if ($bo_policy == 0) {
            // if cancelling any remaining quantities
            close_sales_order($delivery->order_no);
        }
        //TUANVT5
        // taxes - this is for printing purposes
        foreach ($taxes as $taxitem) {
            if ($taxitem['Net'] != 0) {
                $ex_rate = get_exchange_rate_from_home_currency(get_customer_currency($delivery->customer_id), $delivery->document_date);
                add_trans_tax_details(ST_CUSTDELIVERY, $delivery_no, $taxitem['id'],
                $taxitem['rate'], $delivery->tax_included, $taxitem['Value'],
                $taxitem['Net'], $ex_rate, $delivery->document_date, $delivery->reference );
            }
        }

        add_comments(ST_CUSTDELIVERY, $delivery_no, $delivery->document_date, $delivery->Comments);

        if ($trans_no == 0) {
            $Refs->save(ST_CUSTDELIVERY, $delivery_no, $delivery->reference);
        }

        hook_db_postwrite($delivery, ST_CUSTDELIVERY);
        commit_transaction();

        return $delivery_no;
    }

    function get_duedate($terms, $invdate)
    {
        if (!is_date($invdate))
        {
            return new_doc_date();
        }

        $myrow = $this->payment_term_model->item($terms); die();

        if (!$myrow)
            return $invdate;

        if ($myrow['day_in_following_month'] > 0)
            $duedate = add_days(end_month($invdate), $myrow['day_in_following_month']);
        else
            $duedate = add_days($invdate, $myrow['days_before_due']);
        return $duedate;
    }
}