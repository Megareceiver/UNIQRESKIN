<?php
class Sales_Sale_Invoice_Model extends CI_Model
{

    function __construct()
    {
        if( !function_exists('write_sales_delivery') ){
            include_once(ROOT . "/sales/includes/sales_db.inc");
        }

        $this->cus_trans = module_model_load('trans','sales');
        $this->config = module_model_load('config','setup');
        $this->gl_trans = module_model_load('trans','gl');
    }

    function write_sales_invoice($invoice, $repost = false)
    {
        global $Refs;

        $customer = get_customer($invoice->customer_id);

        $trans_no = $invoice->trans_no;
        if (is_array($trans_no)) {
            $trans_no = key($trans_no);
            $this->delivery_update($trans_no, $invoice);
        }

        $date_ = $invoice->document_date;
        $charge_shipping = $invoice->freight_cost;

        begin_transaction();
        hook_db_prewrite($invoice, ST_SALESINVOICE);

        if ($trans_no != 0 && ! isset($_GET['bug'])) {

            delete_comments(ST_SALESINVOICE, $trans_no);
            void_gl_trans(ST_SALESINVOICE, $trans_no, true);
            if (! $repost) {
                void_cust_allocations(ST_SALESINVOICE, $trans_no);
            }

            void_trans_tax_details(ST_SALESINVOICE, $trans_no);
            $this->cus_trans->remove_trans_detail(ST_SALESINVOICE, $trans_no);
            $invoice_new = false;
        } else {
            $invoice_new = true;
        }

        $currency_code = $invoice->customer_currency;
        if (! $currency_code) {
            $currency_code = $customer['curr_code'];
        }

        add_new_exchange_rate($currency_code, $date_, $invoice->ex_rate);

        // offer price values without freight costs
        // $items_total = $invoice->get_items_total_dispatch();
        $items_total = 0;
        $freight_tax = $invoice->get_shipping_tax();

        update_customer_trans_version(get_parent_type(ST_SALESINVOICE), $invoice->src_docs);

        // Insert/update the debtor_trans
        $sales_order = $invoice->order_no;
        if (is_array($sales_order))
            $sales_order = $sales_order[0]; // assume all crucial SO data are same for every delivery

        $cus_trans = array(
            'debtor_no' => $invoice->customer_id,
            'branch_code' => $invoice->Branch,
            'tran_date' => $date_,
            'due_date' => $invoice->due_date,
            'reference' => $invoice->reference,
            'tpe' => $invoice->sales_type,
            'order_' => $sales_order,
            'ov_amount' => 0,

            // 'ov_discount'=>0,
            // 'ov_gst'=>$items_added_tax,
            'ov_freight' => $invoice->freight_cost,

            // 'ov_freight_tax'=>$freight_added_tax,
            // 'rate'=>0,
            'ship_via' => $invoice->ship_via,

            // 'alloc'=>0,
            'dimension_id' => $invoice->dimension_id,
            'dimension2_id' => $invoice->dimension2_id,
            'payment_terms' => $invoice->payment
        );
        $invoice_no = $this->cus_trans->write_customer_trans(ST_SALESINVOICE, $trans_no, $cus_trans);

        if ($trans_no == 0) {
            $invoice->trans_no = array(
                $invoice_no => 0
            );
        }

        $invoice_tax_total = 0;
        $branch_data = get_branch_accounts($invoice->Branch);

        $gl_trans = new gl_trans();
        $gl_trans->set_value(array(
            'type' => ST_SALESINVOICE,
            'type_no' => $invoice_no,
            'tran_date' => $date_,
            'person_type_id' => PT_CUSTOMER,
            'person_id' => $invoice->customer_id
        ));

        $default_sale_account = $this->config->get_sys_pref_val('default_sales_act');

        $ov_amount = $ov_gst = $ov_discount = 0;
        foreach ($invoice->line_items as $line_no => $invoice_line) {
            $price = $invoice_line->price * (1 - $invoice_line->discount_percent) * $invoice_line->qty_dispatched;
            $item_tax_detail = tax_calculator($invoice_line->tax_type_id, $price, $invoice->tax_included);
            $items_total += $item_tax_detail->price + $item_tax_detail->value;

            $cus_trans_detail = array(
                'tax_type_id' => $invoice_line->tax_type_id,
                'debtor_trans_no' => $invoice_no,
                'stock_id' => $invoice_line->stock_id,
                'description' => $invoice_line->item_description,
                'quantity' => $invoice_line->qty_dispatched,
                'unit_price' => $invoice_line->line_price(),
                'unit_tax' => 0,
                'discount_percent' => $invoice_line->discount_percent,
                'standard_cost' => $invoice_line->standard_cost,
                'src_id' => $invoice_line->src_id
            );

            if (isset($invoice_line->qty_done)) {
                $cus_trans_detail['qty_done'] = $invoice_line->qty_done;
            }
            $line_id = 0;
            if ($invoice_line->id) {
                $line_id = $invoice_line->id;
            }
            if ($invoice_line->qty_dispatched != 0) {
                $cus_trans_detail['unit_tax'] = $item_tax_detail->value;
            }

            $this->cus_trans->write_customer_trans_detail(ST_SALESINVOICE, $invoice_no, $cus_trans_detail, true);

            // Update delivery items for the quantity invoiced
            if ($invoice_line->qty_old != $invoice_line->qty_dispatched) {
                // update_parent_line(ST_SALESINVOICE, $invoice_line->src_id, ($invoice_line->qty_dispatched-$invoice_line->qty_old));
                $cus_trans_detail_update = array(
                    'qty_done' => "qty_done +" . ($invoice_line->qty_dispatched - $invoice_line->qty_old)
                );
                $this->cus_trans->update_customer_trans_detail(ST_SALESINVOICE, $invoice_line->src_id, $cus_trans_detail_update);
            }

            if ($invoice_line->qty_dispatched != 0 && $invoice_line->line_price() != 0) {

                $invoice_tax_total += $item_tax_detail->value;

                $stock_gl_code = get_stock_gl_code($invoice_line->stock_id);
                $sales_account = $stock_gl_code['sales_account'];

                if ($branch_data['sales_account'] != "") {
                    $sales_account = $branch_data['sales_account'];
                }
                if (! $sales_account) {
                    $sales_account = $default_sale_account;
                }

                // 2008-08-01. If there is a Customer Dimension, then override with this, else take the Item Dimension (if any)
                $dim = ($invoice->dimension_id != $customer['dimension_id'] ? $invoice->dimension_id : ($customer['dimension_id'] != 0 ? $customer["dimension_id"] : $stock_gl_code["dimension_id"]));
                $dim2 = ($invoice->dimension2_id != $customer['dimension2_id'] ? $invoice->dimension2_id : ($customer['dimension2_id'] != 0 ? $customer["dimension2_id"] : $stock_gl_code["dimension2_id"]));

                // $ov_amount += ($item_tax_detail->price*$invoice_line->qty_dispatched );
                $item_amount = $item_tax_detail->price + ($invoice_line->price * $invoice_line->qty_dispatched * ($invoice_line->discount_percent));
                // bug($invoice_line); die('check sale account');
                $ov_amount += $item_amount;
                $gl_trans->add_trans($sales_account, - $item_amount, $dim, $dim2);
                // bug($invoice_line);
                if ($invoice_line->tax_type_id) {
                    $ex_rate = get_exchange_rate_from_home_currency(get_customer_currency($invoice->customer_id), $date_);

                    $tax_trans = array(
                        'trans_type' => ST_SALESINVOICE,
                        'trans_no' => $invoice_no,
                        'tran_date' => $date_,
                        'tax_type_id' => $invoice_line->tax_type_id,
                        'included_in_price' => $invoice->tax_included,
                        'ex_rate' => $ex_rate,
                        'memo' => $invoice->reference,
                        'rate' => $item_tax_detail->rate,
                        'amount' => $item_tax_detail->value,
                        'net_amount' => $item_tax_detail->price
                    );
                    $this->gl_trans->add_tax_trans_detail($tax_trans);

                    $gl_tax_account = $item_tax_detail->sales_gl_code;

                    if (! $gl_tax_account) {

                        $gl_tax_account = 2150;
                    }
                    $item_tax = $item_tax_detail->value;
                    $ov_gst += $item_tax;
                    $gl_trans->add_trans($gl_tax_account, - $item_tax);
                    // $item_tax+=$item_tax_detail->value*$invoice_line->quantity;
                    // bug('tax='.$item_tax);
                }

                if ($invoice_line->discount_percent != 0) {
                    $gl_discount_account = $branch_data["sales_discount_account"];
                    if (! $gl_discount_account) {
                        $gl_discount_account = get_instance()->config->get_sys_pref_val('default_sales_discount_act');
                    }
                    $item_discount = $invoice_line->price * $invoice_line->qty_dispatched * ($invoice_line->discount_percent);
                    $ov_discount += $item_discount;
                    // $ov_amount -= $item_discount;
                    $gl_trans->add_trans($gl_discount_account, $item_discount);
                    // bug('discount='.$item_tax_detail->price*$invoice_line->qty_dispatched*$invoice_line->discount_percent);
                } // end of if discount !=0
            } // quantity dispatched is more than 0
        } // end of delivery_line loop


        foreach ($invoice->gl_items as $line_no => $gl_line) {
            $item_tax = tax_calculator($gl_line->tax_id, floatval($gl_line->amount), $invoice->tax_included);

            if( !is_object($item_tax) OR !is_numeric($item_tax->price) ){
                continue;
            }
            $cus_trans_detail = array(
                'gl_code'=>$gl_line->gl_code,
    	        'tax_type_id'=>$gl_line->tax_id,
    	        'unit_price'=>$item_tax->price,
                'unit_tax'=>$item_tax->value,
            );
            $this->cus_trans->write_customer_trans_detail(ST_SALESINVOICE, $invoice_no, $cus_trans_detail, true);
            $gl_trans->add_trans($gl_line->gl_code, -$item_tax->price, $gl_line->gl_dim, $gl_line->gl_dim2,$gl_line->memo_);
            $items_total += $item_tax->price;
            if( $item_tax->value != 0 ){
                $gl_trans->add_trans($item_tax->sales_gl_code, -$item_tax->value,0,0,$gl_line->memo_);
                $ov_gst += $item_tax->value;
                $items_total += $item_tax->value;
            }
            
            $ov_amount += $items_total;

        }
        $debtor_trans_update = array(
            'ov_amount' => $ov_amount - $ov_discount,
            'ov_gst' => $ov_gst,
            'ov_discount' => $ov_discount
        );
        // $invoice_allocations = $this->db->where(array('type'=>'ST_CUSTPAYMENT','debtor_no'=>$invoice->customer_id,'trans_no_to'=>$invoice_no))->get('cust_allocations')->row();
        $this->db->reset();
        $this->cus_trans->update_customer_trans(ST_SALESINVOICE, $invoice_no, array(
            'ov_amount' => $ov_amount - $ov_discount,
            'ov_gst' => $ov_gst,
            'ov_discount' => $ov_discount
        ));

        if (($items_total + $charge_shipping) != 0) {
            $receivables_account = $branch_data["receivables_account"];
            if (! $receivables_account) {
                $receivables_account = $this->config->get_sys_pref_val('debtors_act');
            }

            $gl_trans->add_trans($receivables_account, $items_total + $charge_shipping + $invoice->get_shipping_tax());
        }

        if ($charge_shipping != 0) {
            $company_data = get_company_prefs();
            $gl_trans->add_trans($company_data["freight_act"], - $invoice->get_tax_free_shipping());
        }

        $total = 0;

        foreach ($gl_trans->trans as $trans) {
            $total += $this->gl_trans->add_gl_trans($trans, $currency_code);
        }

        if (isset($_GET['bug'])) {
            bug($gl_trans->trans);
            die('bug total = ' . $total);
        }
        // bug($gl_trans->trans);
        // bug($total);die;
        // Post a balance post if $total != 0

        if (abs($total) < 0.02) {
            // die('go here');
            // add_gl_balance(ST_SALESINVOICE, $invoice_no, $date_, -$total, PT_CUSTOMER, $invoice->customer_id);
            add_gl_trans(ST_SALESINVOICE, $invoice_no, $date_, get_company_pref('rounding_difference_act'), 0, 0, "", - $total, null, PT_CUSTOMER, $invoice->customer_id, "The balanced GL transaction could not be inserted");
        } else {
            add_gl_balance(ST_SALESINVOICE, $invoice_no, $date_, - $total, PT_CUSTOMER, $invoice->customer_id);
        }

        add_comments(ST_SALESINVOICE, $invoice_no, $date_, $invoice->Comments);

        $this->db->reset();
        $cust_allocations = $this->db->where(array(
            'trans_no_from' => ST_CUSTPAYMENT,
            'trans_type_to' => ST_SALESINVOICE,
            'trans_no_to' => $invoice_no
        ))
            ->get('cust_allocations')
            ->row();

        $items_added_tax = $freight_added_tax = 0;
        // if (!$cust_allocations ) {
        if ($invoice_new) {
            $debtor_trans = $this->db->where(array(
                'trans_no' => $invoice_no,
                'type' => ST_SALESINVOICE
            ))
                ->get('debtor_trans')
                ->row();

            // if( isset($debtor_trans->trans_no) && $debtor_trans->alloc <= 0 ){
            // $Refs->save(ST_SALESINVOICE, $invoice_no, $invoice->reference);

            /*
             * update Cash Sales Invoice 20160925
             */
            if ($invoice->payment_terms['cash_sale'] && $invoice->pos['pos_account']) {
                $amount = $items_total + $items_added_tax + $invoice->freight_cost + $freight_added_tax;
                $discount = 0; // $invoice->cash_discount*$amount;

                $pmtno = write_customer_payment(0, $invoice->customer_id, $invoice->Branch, $invoice->pos['pos_account'], $date_, $Refs->get_next(ST_CUSTPAYMENT), $amount - $discount, $discount, _('Cash invoice') . ' ' . $invoice_no);

                add_cust_allocation($amount, ST_CUSTPAYMENT, $pmtno, ST_SALESINVOICE, $invoice_no);

                update_debtor_trans_allocation(ST_SALESINVOICE, $invoice_no, $amount);
                update_debtor_trans_allocation(ST_CUSTPAYMENT, $pmtno, $amount);
            }
        }

        $Refs->save(ST_SALESINVOICE, $invoice_no, $invoice->reference);

        // die('end post invoice');
        hook_db_postwrite($invoice, ST_SALESINVOICE);
        commit_transaction();

        // die(' end write invoice no='.$invoice_no);
        return $invoice_no;
    }

    function delivery_update($trans_no, $cart)
    {
        $delivery = clone $cart;
        $debtor_trans = $this->db->where(array(
            'trans_no' => $trans_no,
            'type' => ST_SALESINVOICE
        ))->get('debtor_trans')->row();

        if (is_object($debtor_trans) AND $debtor_trans->order_) {
            $debtor_delivery_trans = $this->db->where(array(
                'order_' => $debtor_trans->order_,
                'type' => ST_CUSTDELIVERY
            ))->get('debtor_trans')->row();
            $delivery->trans_no = $debtor_delivery_trans->trans_no;
            write_sales_delivery($delivery, 0);
        }
    }
}