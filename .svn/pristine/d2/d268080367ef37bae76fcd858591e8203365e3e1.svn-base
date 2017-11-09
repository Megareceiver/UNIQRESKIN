<?php

class PurchasesInvoice
{

    var $cart = NULL;

    function __construct()
    {}

    function form_header()
    {
        global $Ajax, $Refs;
        // if vars have been lost, recopy
        if (! isset($_POST['tran_date']))
            $this->copy_from_trans();

            // start_outer_table(TABLESTYLE2, "width=95%");
            // table_section(1);
        bootstrap_set_label_column(5);
        row_start();
        col_start(5);
        if (isset($_POST['invoice_no'])) {
            $trans = get_supp_trans($_POST['invoice_no'], ST_SUPPINVOICE);
            $_POST['supplier_id'] = $trans['supplier_id'];
            input_label(_("Supplier"), NULL, $trans['supplier_name'] . " - " . $trans['curr_code']);
            hidden('supplier_id');
        } else {
            if (! isset($_POST['supplier_id']) && (get_global_supplier() != ALL_TEXT))
                $_POST['supplier_id'] = get_global_supplier();
            supplier_list_bootstrap(_("Supplier"), 'supplier_id', NULL, false, true);
        }

        if ($this->cart->supplier_id != $_POST['supplier_id']) {
            // supplier has changed
            // delete all the order items - drastic but necessary because of
            // change of currency, etc
            $this->cart->clear_items();
            read_supplier_details_to_trans($this->cart, $_POST['supplier_id']);

            /*
             * update tax_included by QuanNH 150413
             */
            read_supplier_details_150413($this->cart, $_POST['supplier_id']);

            $this->copy_from_trans();
        }

        if( empty($_POST['reference']) ){
            $_POST['reference'] = $Refs->get_next($this->cart->trans_type);
        }

        input_ref('Reference', 'reference');

        if (isset($_POST['invoice_no'])) {
            input_label(_("Supplier's Ref."), 'invoice_no');
            hidden('invoice_no', $_POST['invoice_no']);
            hidden('supp_reference', $_POST['invoice_no']);
        } else
            input_text("Supplier's Ref.", 'supp_reference');

        input_text(_("Reason"), 'reason');

        col_start(4);

        input_date_bootstrap('Date', 'tran_date', null, false, true);
        // date_row(_("Date") . ":", 'tran_date', '', true, 0, 0, 0, "", true);
        if (isset($_POST['_tran_date_changed'])) {
            $Ajax->activate('_ex_rate');
            $this->cart->tran_date = $_POST['tran_date'];
            get_duedate_from_terms($supp_trans);
            $_POST['due_date'] = $supp_trans->due_date;
            $Ajax->activate('due_date');
        }
        input_date_bootstrap("Due Date", 'due_date');
        input_label(_("Terms"), null, $this->cart->terms['description']);
        
        if( config_ci("kastam") ){
            check_bootstrap(_("Simplified invoice"), 'simplified');
            check_bootstrap(_("Imported Goods:"), 'imported_goods');    
        } else {
            hidden('simplified');
            hidden('imported_goods');
        }
        

        col_start(3);
        set_global_supplier($_POST['supplier_id']);
        $supplier_currency = get_supplier_currency($this->cart->supplier_id);
        $company_currency = get_company_currency();

        if ($supplier_currency != $company_currency) {
            label_row(_("Supplier's Currency:"), "<b>" . $supplier_currency . "</b>");
            exchange_rate_display($company_currency, $supplier_currency, $_POST['tran_date']);
        }

        input_label(_("Tax Group"), $this->cart->tax_description);
        input_supplier_credit($this->cart->supplier_id, $this->cart->credit);

        input_label(_("Paid Tax"), null, 'false');
        row_end();
        // end_outer_table(1);
    }

    // $mode = 0 none at the moment
    // = 1 display on invoice/credit page
    // = 2 display on view invoice
    // = 3 display on view credit
    var $mode = 0;

    function grn_items()
    {
        $supp_trans = $this->cart;

        $ret = true;
        // if displaying in form, and no items, exit
        if (($this->mode == 2 || $this->mode == 3) && count($supp_trans->grn_items) == 0)
            return 0;

            // start_outer_table("style='border:1px solid #cccccc;' width=95%");

        $heading2 = "";
        if ($this->mode == 1) {
            if ($supp_trans->trans_type == ST_SUPPINVOICE) {
                $heading = _("Items Received Yet to be Invoiced");
                if ($_SESSION["wa_current_user"]->can_access('SA_GRNDELETE')) // Added 2008-10-18 by Joe Hunt. Only admins can remove GRNs
                    $heading2 = _("WARNING! Be careful with removal. The operation is executed immediately and cannot be undone !!!");
            } else {
                $heading = _("Delivery Item Selected For Adding To A Supplier Credit Note");
            }
        } else {
            if ($supp_trans->trans_type == ST_SUPPINVOICE)
                $heading = _("Received Items Charged on this Invoice");
            else
                $heading = _("Received Items Credited on this Note");
        }

        box_start($heading);

        // display_heading($heading);
        row_start('justify-content-end pb-3');
        if ($this->mode == 1) {
            if ($supp_trans->trans_type == ST_SUPPCREDIT && ! isset($_POST['invoice_no'])) {
                // echo "</td>";
                col_start(4);
                // bootstrap_set_label_column(5);
                input_date_bootstrap(_("Received between"), 'receive_begin', "", false, null, - 30, 0, 0);
                col_start(3);
                bootstrap_set_label_column(3);
                input_date_bootstrap(_("and"), 'receive_end', "", false, null, 1, 0, 0);
                // date_cells(_("Received between"), 'receive_begin', "", null, -30, 0, 0, "valign=middle");
                // date_cells(_("and"), 'receive_end', '', null, 1, 0, 0, "valign=middle");

                col_start(1);
                submit('RefreshInquiry', _("Search"), true, _('Refresh Inquiry'), true);
                // echo "<td>";
            }

            if ($heading2 != "") {
//                 display_note($heading2, 0, 0, "class='overduefg'");
            }
            // echo "</td><td width=10% align='right'>";
            col_start(2,'class="text-right" ');
            submit('InvGRNAll', _("Add All Items"), true, false, true);
        }
        row_end();

        // end_outer_table(0, false);

        div_start('grn_items');
        start_table(TABLESTYLE, "width=95%");
        if ($this->mode == 1) {
            $th = array(
                _("Delivery"),
                _("P.O."),
                _("Item"),
                _("Description"),
                _("Received On"),
                _("Quantity Received"),
                _("Quantity Invoiced"),
                _("Qty Yet To Invoice"),
                $supp_trans->tax_included ? _("Price after Tax") : _("Price before Tax"),
                _("Tax"),
                _("Total")
            );
            if (($supp_trans->trans_type == ST_SUPPINVOICE) && $_SESSION["wa_current_user"]->can_access('SA_GRNDELETE')) {
                // Added 2008-10-18 by Joe Hunt. Only admins can remove GRNs
                $th[] = "";
                $th[] = "";
            }

            if ($supp_trans->trans_type == ST_SUPPCREDIT) {
                $th[8] = _("Amt Yet To Credit");
                $th[9] = _("GST");
                $th[10] = _("Total");
                $th[11] = '';
            }
        } else
            $th = array(
                _("Delivery"),
                _("Item"),
                _("Description"),
                _("Quantity"),
                _("Price"),
                _("Tax"),
                _("Line Value")
            );

        table_header($th);
        $total_grn_value = 0;
        $i = $k = 0;

        if (count($supp_trans->grn_items) > 0) {
            foreach ($supp_trans->grn_items as $entered_grn) {
                alt_table_row_color($k);
                $grn_batch = get_grn_batch_from_item($entered_grn->id);

                label_cell(get_trans_view_str(ST_SUPPRECEIVE, $grn_batch));
                if ($this->mode == 1) {
                    $row = get_grn_batch($grn_batch);
                    label_cell($row['purch_order_no']); // PO
                }
                label_cell($entered_grn->item_code);
                label_cell($entered_grn->item_description);
                $dec = get_qty_dec($entered_grn->item_code);
                if ($this->mode == 1) {
                    label_cell(sql2date($row['delivery_date']));
                    qty_cell($entered_grn->qty_recd, false, $dec);
                    qty_cell($entered_grn->prev_quantity_inv, false, $dec);
                }
                qty_cell(abs($entered_grn->this_quantity_inv), true, $dec);
                amount_decimal_cell($entered_grn->chg_price);
                // bug($entered_grn);die;
                // $tax = get_tax_type($entered_grn->supplier_tax_id);
                // $tax_include = tax_calcula($tax['rate'],$entered_grn->chg_price,$entered_grn->tax_included);
                $entered_grn_tax = tax_calculator($entered_grn->supplier_tax_id, $entered_grn->chg_price * $entered_grn->this_quantity_inv, $entered_grn->tax_included);
                // amount_cell(round2(tax_calcula($tax['rate'],$entered_grn->chg_price,$supplier->tax_included),user_price_dec()) , true);
                amount_cell(round2($entered_grn_tax->value, user_price_dec()), true);

                // amount_cell( round2(($entered_grn_tax->price+$entered_grn_tax->value) * abs($entered_grn->this_quantity_inv ) , user_price_dec()), true);
                amount_cell($entered_grn_tax->price + $entered_grn_tax->value, true);

                // amount_cell( round2($entered_grn->chg_price * abs($entered_grn->this_quantity_inv), user_price_dec()), true);

                if ($this->mode == 1) {
                    icon_submit_cells("Delete" . $entered_grn->id, _("Edit"), 'info', 'icon-pencil', true, _('Edit document line'));
//                     delete_button_cell("Delete" . $entered_grn->id, _("Edit"), );
                    if (($supp_trans->trans_type == ST_SUPPINVOICE) && $_SESSION["wa_current_user"]->can_access('SA_GRNDELETE'))
                        label_cell("");
                }
                end_row();

                // $total_grn_value += round2($entered_grn->chg_price * abs($entered_grn->this_quantity_inv), user_price_dec());
                $total_grn_value += $entered_grn_tax->price + $entered_grn_tax->value;

                $i ++;
                if ($i > 15) {
                    $i = 0;
                    table_header($th);
                }
            }
        }
        if ($this->mode == 1) {
            $ret = display_grn_items_for_selection($supp_trans, $k);
            // $colspan = ;
        }
        $colspan = count($th) - 3;
        // $colspan = 6;

        label_row(_("Total"), price_format($total_grn_value), "colspan=$colspan align=right", "nowrap align=right", 3);

        if (! $ret) {
            start_row();
            echo "<td colspan=" . (count($th)) . ">";
            if ($supp_trans->trans_type == ST_SUPPINVOICE)
                display_note(_("There are no outstanding items received from this supplier that have not been invoiced by them."), 0, 0);
            else {
                display_note(_("There are no received items for the selected supplier that have been invoiced."));
                display_note(_("Credits can only be applied to invoiced items."), 0, 0);
            }
            echo "</td>";
            end_row();
        }
        end_table(1);
        div_end();

        return $total_grn_value;
    }

    /*
     * $mode = 0 none at the moment
     * = 1 display on invoice/credit page
     * = 2 display on view invoice
     * = 3 display on view credit
     */
    function gl_items()
    {
        global $Ajax;

        $supp_trans = $this->cart;
        // if displaying in form, and no items, exit
        if (($this->mode == 2 || $this->mode == 3) && count($supp_trans->gl_codes) == 0)
            return 0;

        if ($supp_trans->trans_type == ST_SUPPINVOICE)
            $heading = _("GL Items for this Invoice");
        else
            $heading = _("GL Items for this Credit Note");


        box_start($heading);
        if ($this->mode == 1) {
            $qes = has_quick_entries(QE_SUPPINV);
            if ($qes !== false) {
                row_start('justify-content-end');
                col_start(4);
                input_quick_entries('Quick Entry','qid',null, QE_SUPPINV, true);

                $qid = get_quick_entry(get_post('qid'));
                if (list_updated('qid')) {
                    unset($_POST['totamount']); // enable default
                    $Ajax->activate('totamount');
                }
                col_start(3);
                $amount = input_num('totamount', $qid['base_amount']);
                input_money($qid['base_desc'], 'amount',$amount);
//                 echo "<input class='amount' type='text' name='totamount' size='7' maxlength='12' dec='$dec' value='$amount'>&nbsp;";

                col_start(1,' class="text-right" ');
                submit_bootstrap('go', _("Go"),false, true);
//                 submit('go', _("Go"), true, false, true);
                row_end();
                echo '<hr>';
            }
        }

        div_start('gl_items');
        start_table(TABLESTYLE);

        $dim = get_company_pref('use_dimension');
        if ($dim == 2)
            $th = array(
                'account'=>array('label'=>_("Account"),'width'=>'10%'),
                _("Name"),
                _('Tax'),
                _("Department") . " 1",
                _("Department") . " 2",
                _("Amount"),
                _("Memo")
            );
        else
            if ($dim == 1)
                $th = array(
                    'account'=>array('label'=>_("Account"),'width'=>'10%'),
                    _("Name"),
                    _('Tax'),
                    _("Department"),
                    _("Amount"),
                    _("Memo")
                );
            else
                $th = array(
                    'account'=>array('label'=>_("Account"),'width'=>'10%'),
                    _("Name"),
                    _('Tax'),
                    _("Amount"),
                    _("Memo")
                );

        if ($this->mode == 1) {
            $th[] = "";
            $th[] = "";
        }

        table_header($th);
        $total_gl_value = $total = 0;
        $i = $k = 0;

        if (count($supp_trans->gl_codes) > 0) {
            foreach ($supp_trans->gl_codes as $entered_gl_code) {
                alt_table_row_color($k);
                if ($this->mode == 3){
                    $entered_gl_code->amount = - $entered_gl_code->amount;
                }
                    
                label_cell($entered_gl_code->gl_code);
                label_cell($entered_gl_code->gl_act_name);
                
                $item_tax = (strlen($entered_gl_code->supplier_tax_id) > 0 && intval($entered_gl_code->supplier_tax_id) > 0) ? get_gst($entered_gl_code->supplier_tax_id) : array();
                if( !empty($item_tax) ){
                    label_cell( is_object($item_tax)? $item_tax->code." (".$item_tax->rate."%)" : NULL);
                } else {
                    label_cell( NULL);
                }
                
//                 item_tax_types_list_cells(null,'tax_id',$entered_gl_code->supplier_tax_id,3);

                if ($dim >= 1)
                    label_cell(get_dimension_string($entered_gl_code->gl_dim, true));
                if ($dim > 1)
                    label_cell(get_dimension_string($entered_gl_code->gl_dim2, true));

                amount_cell($entered_gl_code->amount, true);
                label_cell($entered_gl_code->memo_);

                if ($this->mode == 1) {
//                     delete_button_cell("Delete2" . $entered_gl_code->Counter, _("Delete"), _('Remove line from document'));
                    icon_submit_cells('Delete2', $entered_gl_code->Counter, 'danger', 'icon-trash', true ,_("Remove line from document"));
                    label_cell("");
                }
                end_row();
                // ///////// 2009-08-18 Joe Hunt
                if ($this->mode > 1) {
                    if ($supp_trans->tax_included || ! is_tax_account($entered_gl_code->gl_code))
                        $total_gl_value += $entered_gl_code->amount;
                } else
                    $total_gl_value += $entered_gl_code->amount;
                $total += $entered_gl_code->amount;
                $i ++;
                if ($i > 15) {
                    $i = 0;
                    table_header($th);
                }
            }
        }
        if ($this->mode == 1){
            $this->gl_item_edit();
//             display_gl_controls($supp_trans, $k);
        }

        $colspan = ($dim == 2 ? 4 : ($dim == 1 ? 3 : 2));
        label_row(_("Total"), price_format($total), "colspan=3 align=right", "nowrap align=right", ($this->mode == 1 ? 3 : 1));

        end_table(1);
        div_end();

        return $total_gl_value;
    }

    private function gl_item_edit($k=-1)
    {
        $supp_trans = $this->cart;

        $accs = get_supplier_accounts($supp_trans->supplier_id);
        $_POST['gl_code'] = $accs['purchase_account'] ? $accs['purchase_account'] : get_company_pref('default_cogs_act');

        alt_table_row_color($k);
        gl_all_accounts_list_cells('gl_code', null, true, true);
        item_tax_types_list_cells(null, 'tax_id', null, 3, 'in_row_input');
        $dim = get_company_pref('use_dimension');
        if ($dim >= 1)
            dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
        if ($dim > 1)
            dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);
        amount_cells(null, 'amount');
        if ($dim < 1)
            text_cells_ex(null, 'memo_', 35, 50, null, null, null, hidden('dimension_id', 0, false) . hidden('dimension2_id', 0, false));
        else
            if ($dim < 2)
                text_cells_ex(null, 'memo_', 35, 50, null, null, null, hidden('dimension2_id', 0, false));
            else
                text_cells_ex(null, 'memo_', 35, 50, null, null, null);

        icon_submit_cells('AddGLCodeToTrans', _("Add"), 'success', 'fa-plus', true, _('Add GL Line'));
        icon_submit_cells('ClearFields', _("Reset"), 'warning', 'fa-refresh', true ,_("Clear all GL entry fields"));
        end_row();
    }

    function form_total()
    {
        $this->copy_to_trans();
        // bug($supp_trans); die;
        $dim = get_company_pref('use_dimension');
        $colspan = ($dim == 2 ? 7 : ($dim == 1 ? 6 : 5));
        start_table(TABLESTYLE, "width=95%");
        label_row(_("Sub-total:"), price_format($this->cart->ov_amount), "colspan=$colspan align=right", "align=right");
        // TUANVT2
        // $taxes = $supp_trans->get_taxes_tran_new();
        // TUANVT6
        // bug($supp_trans);
        // $tax_total = display_edit_tax_items_new($taxes, $colspan, $supp_trans->tax_included, 0, true);

        $gst_total = 0;
        foreach ($this->cart->ov_tax as $tax) {
            if ($tax['value'] > 0) {
                $gst_total += $tax['value'];
                label_row($tax['name'], number_format2($tax['value'], user_price_dec()), "colspan=$colspan align=right style='font-weight:bold;'", "align=right");
            }
        }
        hidden('gst_total', $gst_total);
        // bug( $supp_trans->ov_tax ); die;
        $display_total = $this->cart->ov_amount + $this->cart->ov_gst_amount;
        // bug($supp_trans); die;
        // $tax_total = display_edit_tax_items_new($taxes, 5, $supp_trans->tax_included);
        // $supp_trans->tax_included = 1;
        $tax_total = $this->cart->show_tax_footer($colspan);
        if (! $this->cart->tax_included) {
            $display_total += $tax_total;
        }

        if ($this->cart->trans_type == ST_SUPPINVOICE)
            label_row(_("Invoice Total:"), price_format($display_total), "colspan=$colspan align=right style='font-weight:bold;'", "align=right style='font-weight:bold;'");
        else
            label_row(_("Credit Note Total"), price_format($display_total), "colspan=$colspan align=right style='font-weight:bold;color:red;'", "nowrap align=right style='font-weight:bold;color:red;'");

        end_table(1);

        row_start('justify-content-center');
        col_start(8);
        input_textarea(_("Memo:"), "Comments");
        row_end();
    }

    /*
     *
     */
    function copy_from_trans()
    {
        $_POST['Comments'] = $this->cart->Comments;
        $_POST['tran_date'] = $this->cart->tran_date;
        $_POST['due_date'] = $this->cart->due_date;
        $_POST['supp_reference'] = $this->cart->supp_reference;
        $_POST['reference'] = $this->cart->reference;
        $_POST['supplier_id'] = $this->cart->supplier_id;
        $_POST['_ex_rate'] = $this->cart->ex_rate;

        if (isset($this->cart->terms['imported_goods'])) {
            set_post('imported_goods', $this->cart->terms['imported_goods']);
        }

        if (isset($this->cart->terms['simplified'])) {
            set_post('simplified', $this->cart->terms['simplified']);
        }

        if (isset($this->cart->tax_overrides))
            foreach ($this->cart->tax_overrides as $id => $value)
                $_POST['mantax'][$id] = price_format($value);
    }

    /*
     *
     */
    function copy_to_trans()
    {
        $supp_trans = $this->cart;

        if (! isset($supp_trans->trans_no)) {
            $supp_trans->Comments = $_POST['Comments'];
            $supp_trans->tran_date = $_POST['tran_date'];
            $supp_trans->due_date = $_POST['due_date'];
            $supp_trans->supp_reference = $_POST['supp_reference'];
            $supp_trans->reference = $_POST['reference'];
            $supp_trans->ex_rate = input_num('_ex_rate', null);
            $supp_trans->reason = $_POST['reason'];
        }

        $supp_trans->ov_amount = $supp_trans->ov_discount = $supp_trans->ov_gst_amount = 0; /* for starters */
        $supp_trans->ov_tax = array();
        if (isset($_POST['mantax'])) {
            foreach ($_POST['mantax'] as $id => $tax) {
                $supp_trans->tax_overrides[$id] = user_numeric($_POST['mantax'][$id]);
            }
        } else
            unset($supp_trans->tax_overrides);

        if (count($supp_trans->grn_items) > 0) {
            foreach ($supp_trans->grn_items as $grn) {
                $price = $grn->this_quantity_inv * $grn->chg_price;
                $item_tax = tax_calculator($grn->supplier_tax_id, $price, $grn->tax_included);

                if (! array_key_exists($grn->supplier_tax_id, $supp_trans->ov_tax)) {
                    $supp_trans->ov_tax[$grn->supplier_tax_id] = array(
                        'name' => $item_tax->name,
                        'value' => 0
                    );
                }
                $supp_trans->ov_tax[$grn->supplier_tax_id]['value'] += $item_tax->value;

                $supp_trans->ov_amount += round2($item_tax->price, user_price_dec());
                $supp_trans->ov_gst_amount += round2($item_tax->value, user_price_dec());
            }
        }

        if (count($supp_trans->gl_codes) > 0) {
            foreach ($supp_trans->gl_codes as $gl_line) {
                // //////// 2009-08-18 Joe Hunt
                if (! is_tax_account($gl_line->gl_code) || $supp_trans->tax_included) {
                    $supp_trans->ov_amount += $gl_line->amount;
                }
            }
        }
    }
}