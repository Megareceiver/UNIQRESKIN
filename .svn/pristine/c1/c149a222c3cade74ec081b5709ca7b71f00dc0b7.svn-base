<?php

class PurchasesOrder
{

    var $cart = NULL;

    function __construct()
    {}

    function form_header()
    {
        global $Ajax, $Refs;
        $order = $this->cart;

        $editable = ($order->order_no == 0);

        // start_outer_table(TABLESTYLE2, 'width=80%');

        // table_section(1);

        row_start();
        col_start(4);
        bootstrap_set_label_column(4);
        if ($editable) {
            if (! isset($_POST['supplier_id']) && (get_global_supplier() != ALL_TEXT))
                $_POST['supplier_id'] = get_global_supplier();

            supplier_list_bootstrap(_("Supplier:"), 'supplier_id', null, false, true, false, true);
            // supplier_list_row(_("Supplier:"), 'supplier_id', null, false, true, false, true);
        } else {
            hidden('supplier_id', $order->supplier_id);
            input_label(_("Supplier"), null, $order->supplier_name);
        }

        if ($order->supplier_id != get_post('supplier_id', - 1)) {
            $old_supp = $order->supplier_id;
            get_supplier_details_to_order($order, $_POST['supplier_id']);
            get_duedate_from_terms($order);
            $_POST['due_date'] = $order->due_date;

            // supplier default price update
            foreach ($order->line_items as $line_no => $item) {
                $line = &$order->line_items[$line_no];
                $line->price = get_purchase_price($order->supplier_id, $line->stock_id);
                $line->quantity = $line->quantity / get_purchase_conversion_factor($old_supp, $line->stock_id) * get_purchase_conversion_factor($order->supplier_id, $line->stock_id);
            }
            $Ajax->activate('items_table');
            $Ajax->activate('due_date');
        }
        set_global_supplier($_POST['supplier_id']);

        // date_row($order->trans_type==ST_PURCHORDER ? _("Order Date:") : ($order->trans_type==ST_SUPPRECEIVE ? _("Delivery Date:") : _("Invoice Date:")),
        // 'OrderDate', '', true, 0, 0, 0, null, true);

        $OrderDate_title = 'Invoice Date';
        if ($order->trans_type == ST_SUPPRECEIVE) {
            $OrderDate_title = 'Delivery Date';
        } else
            if ($order->trans_type == ST_PURCHORDER) {
                $OrderDate_title = 'Order Date';
            }

        input_date_bootstrap('To', 'OrderDate');

        if (isset($_POST['_OrderDate_changed'])) {
            $order->orig_order_date = $_POST['OrderDate'];
            get_duedate_from_terms($order);
            $_POST['due_date'] = $order->due_date;
            $Ajax->activate('due_date');
        }

        // supplier_credit_row($supplier, $credit)
        input_supplier_credit($order->supplier_id, $order->credit);

        if (! is_company_currency($order->curr_code)) {
            input_label(_("Supplier Currency"), null, $order->curr_code);
            exchange_rate_display(get_company_currency(), $order->curr_code, $_POST['OrderDate']);
        }

        if ($editable) {
            input_ref(_("Reference"), 'ref');
        } else {
            hidden('ref', $order->reference);
            input_label(_("Reference"), null, $order->reference);
        }


        col_start(4);
        bootstrap_set_label_column(5);
        if ($order->trans_type == ST_SUPPINVOICE) {
            // date_row(_("Due Date:"), 'due_date', '', false, 0, 0, 0, null, true);
            // echo $ci->finput->qdate('Due Date','due_date',null,'row');
            input_date_bootstrap('Due Date', 'due_date');
        }

        input_text(_("Supplier's Reference"), 'supp_ref');
        locations_bootstrap(_("Receive Into"), 'StkLocation', null, true, false);
        // locations_list_row(_("Receive Into:"), 'StkLocation', null, false, true);

        if (defined('COUNTRY') AND  config_ci("kastam") ) {
            check_row(_("For Fixed Asset:"), 'fixed_access');
            check_row(_("Simplified invoice:"), 'simplified');
        } else {
            hidden('fixed_access');
            hidden('simplified');

        }

        if (defined('COUNTRY') && COUNTRY == 65) {
            input_text(_("Permit No"), 'permit');
        }

        bootstrap_set_label_column(0);
        col_start(4);
        if (! isset($_POST['StkLocation']) || $_POST['StkLocation'] == "" || isset($_POST['_StkLocation_update']) || ! isset($_POST['delivery_address']) || $_POST['delivery_address'] == "") {
            /* If this is the first time the form loaded set up defaults */

            // $_POST['StkLocation'] = $_SESSION['UserStockLocation'];
            $sql = "SELECT delivery_address, phone FROM " . TB_PREF . "locations WHERE loc_code=" . db_escape($_POST['StkLocation']);
            $result = db_query($sql, "could not get location info");

            if (db_num_rows($result) == 1) {
                $loc_row = db_fetch($result);
                $_POST['delivery_address'] = $loc_row["delivery_address"];
                $Ajax->activate('delivery_address');
                $_SESSION['PO']->Location = $_POST['StkLocation'];
                $_SESSION['PO']->delivery_address = $_POST['delivery_address'];
            } else { /* The default location of the user is crook */
                display_error(_("The default stock location set up for this user is not a currently defined stock location. Your system administrator needs to amend your user record."));
            }
        }
        input_textarea('Deliver to', 'delivery_address');

        row_end();
    }

    function form_items($editable = true)
    {
        // display_heading(_("Order Items"));
        $order = $this->cart;

        div_start('items_table');

        $th = array(
            'item_code' => array(
                'label' => _("Item Code"),
                'width' => '10%'
            ),
            'item_desc' => array(
                'label' => _("Item Description"),
                'width' => '15%'
            ),
            'tax' => array(
                'label' => _("Tax"),
                'width' => '10%'
            ),
            _("Quantity"),
            _("Received"),
            _("Unit"),
            'date' => array(
                'label' => _("Required Delivery Date"),
                'width' => '15%'
            ),
            $order->tax_included ? _("Price after Tax") : _("Price before Tax"),
            _("Line Total"),
            ""
        );
        if ($order->trans_type != ST_PURCHORDER)
            array_remove($th, 6);

            // if (count($order->line_items))
        $th[] = '';

        start_table(TABLESTYLE);
        table_header($th);

        $id = find_submit('Edit');
        $total = 0;
        $k = 0;
        $tax_total = array();
        foreach ($order->line_items as $line_no => $po_line) {
            $line_total = round($po_line->quantity * $po_line->price, user_price_dec());
            if (! $editable || ($id != $line_no)) {
                alt_table_row_color($k);
                label_cell($po_line->stock_id);
                label_cell($po_line->item_description);

                // $tax = $ci->api_membership->get_data('taxdetail/' . $po_line->supplier_tax_id);
                $tax = get_gst($po_line->supplier_tax_id);
                if (is_object($tax) and ! empty($tax)) {
                    $tax_line = tax_calculator($po_line->supplier_tax_id, $line_total, $order->tax_included, $tax);

                    if (! isset($tax_total[$tax_line->id])) {
                        $tax_total[$tax_line->id] = array(
                            'name' => $tax_line->name,
                            'value' => $tax_line->value,
                            'rate' => $tax_line->rate
                        );
                    } else {
                        $tax_total[$tax_line->id]['value'] += $tax_line->value;
                    }

                    label_cell($tax->no . "(" . $tax->rate . "%)");
                } else {
                    label_cell(NULL);
                }

                qty_cell($po_line->quantity, false, get_qty_dec($po_line->stock_id));
                qty_cell($po_line->qty_received, false, get_qty_dec($po_line->stock_id));
                label_cell($po_line->units);

                if ($order->trans_type == ST_PURCHORDER)
                    label_cell($po_line->req_del_date);

                amount_decimal_cell($po_line->price);
                // label_cell(number_format2($tax_line->value,user_amount_dec()), "nowrap align=right ",'line_total');
                // amount_cell($line_total);
                label_cell(number_format2($line_total, user_amount_dec()), "nowrap align=right ", 'line_total');
                if ($editable) {
                    icon_submit_cells("Edit$line_no", _("Edit"), 'warning', 'icon-pencil', true , _('Edit document line'));
                    icon_submit_cells("Delete$line_no", _("Delete"), 'danger', 'icon-trash', true , _('Remove line from document'));
                }
                end_row();
            } else {
                // po_item_controls($order, $k, $line_no);
                $this->item_edit($k, $line_no);
            }
            $total += $line_total;
        }

        if ($id == - 1 && $editable) {
            // po_item_controls($order, $k);
            $this->item_edit($k);
        }

        $colspan = count($th) - 2;
        if (count($order->line_items))
            $colspan --;

        if (count($order->line_items) > 0) {
            // $display_sub_total = price_format($total);
            $display_sub_total = number_format2($total, user_amount_dec());
            label_row(_("Sub-total"), $display_sub_total, "colspan=$colspan align=right", "align=right", 2);

            // TUANVT3

            // $taxes = $order->get_taxes_new($order->supplier_tax_id,input_num('freight_cost'));

            $tax_total_amount = 0;
            if (isset($tax_total) && count($tax_total) > 0) {
                foreach ($tax_total as $tax) {
                    if ($order->tax_included) {
                        label_row($tax['name'] . ' (' . $tax['rate'] . '%) ' . $tax['value'], null, "colspan=$colspan align=right", "align=right", 2);
                    } else {
                        label_row($tax['name'] . ' (' . $tax['rate'] . '%) ', $tax['value'], "colspan=$colspan align=right", "align=right", 2);
                    }
                    $tax_total_amount += $tax['value'];
                }
            }
            // $tax_total = display_edit_tax_items_new(null, $colspan, $order->tax_included, 2);
            hidden('gst_total', $tax_total_amount);
            // $display_total = price_format(($total + input_num('freight_cost') + $tax_total));
            $display_total = number_format2($total + input_num('freight_cost') + $tax_total_amount, user_amount_dec());

            start_row();
            label_cells(_("Amount Total"), $display_total, "colspan=$colspan align='right'", "align='right'");
            $order->order_no ? submit_cells('update', _("Update"), "colspan=2 align='center'", _("Refresh"), true) : label_cell('', "colspan=2");
            end_row();
        }
        end_table(1);
        div_end();

    }

    private function item_edit(&$rowcounter, $line_no = -1)
    {
        global $Ajax, $SysPrefs;
        $order = $this->cart;

        alt_table_row_color($rowcounter);

        $dec2 = 0;
        $id = find_submit('Edit');
        if (($id != - 1) && $line_no == $id) {
            $_POST['stock_id'] = $order->line_items[$id]->stock_id;
            $dec = get_qty_dec($_POST['stock_id']);
            $_POST['qty'] = qty_format($order->line_items[$id]->quantity, $_POST['stock_id'], $dec);
            // $_POST['price'] = price_format($order->line_items[$id]->price);
            $_POST['price'] = price_decimal_format($order->line_items[$id]->price, $dec2);
            if ($order->trans_type == ST_PURCHORDER)
                $_POST['req_del_date'] = $order->line_items[$id]->req_del_date;

            $_POST['units'] = $order->line_items[$id]->units;
            $_POST['item_description'] = $order->line_items[$id]->item_description;

            hidden('stock_id', $_POST['stock_id']);
            label_cell($_POST['stock_id']);

            if ($order->line_items[$id]->descr_editable)
                text_cells(null, 'item_description', null, 45, 150);
            else {
                hidden('item_description', $_POST['item_description']);
                // label_cell($_POST['item_description']);
                label_cell($order->line_items[$id]->item_description);
            }

            $Ajax->activate('items_table');
            $qty_rcvd = $order->line_items[$id]->qty_received;
        } else {
            // hidden('line_no', ($_SESSION['PO']->lines_on_order + 1));
            // Chaitanya : Manufcatured item can be purchased
            stock_items_list_cells(null, 'stock_id', null, false, true, false, true);
            // stock_purchasable_items_list_cells(null, 'stock_id', null, false, true, true);
            if (list_updated('stock_id')) {
                $Ajax->activate('price');
                $Ajax->activate('units');
                $Ajax->activate('qty');
                $Ajax->activate('req_del_date');
                $Ajax->activate('line_total');
                $Ajax->activate('supplier_tax_id');
            }
            // TUANVT6
            if ($order->supplier_tax_id < 0) {
                $tax_type_info = get_tax_type_by_item_purchases_tmp(get_post('stock_id'));
                $tax_id_tmp = $tax_type_info["id"];
                // display_error($tax_id_tmp);
                $_POST['supplier_tax_id'] = $tax_id_tmp;
            }
            $item_info = get_item_edit_info($_POST['stock_id']);
            $_POST['units'] = $item_info["units"];

            $dec = $item_info["decimals"];
            $_POST['qty'] = number_format2(get_purchase_conversion_factor($order->supplier_id, $_POST['stock_id']), $dec);
            // $_POST['price'] = price_format(get_purchase_price ($order->supplier_id, $_POST['stock_id']));
            $_POST['price'] = price_decimal_format(get_purchase_price($order->supplier_id, $_POST['stock_id']), $dec2);
            if ($order->trans_type == ST_PURCHORDER)
                $_POST['req_del_date'] = add_days(Today(), $SysPrefs->default_delivery_required_by());
            $qty_rcvd = '';
        }
        // TUANVT4
        if (! isset($new_item)) {
            $new_item = false;
        }
        // stock_invoice_list_row(null, 'supplier_tax_id', null, false, $new_item,'1,3');
        // echo $ci->finput->inputtaxes(null, 'supplier_tax_id', $_POST['supplier_tax_id'], '3', 'column');
        item_tax_types_list_cells(null, 'supplier_tax_id', NULL, 3);

        qty_cells(null, 'qty', null, null, null, $dec);
        qty_cell($qty_rcvd, false, $dec);

        label_cell($_POST['units'], '', 'units');
        if ($order->trans_type == ST_PURCHORDER)
            date_cells(null, 'req_del_date', '', null, 0, 0, 0);
        if ($qty_rcvd > 0) {
            amount_decimal_cell($_POST['price']);
            hidden('price', $_POST['price']);
        } else {
            amount_cells(null, 'price', null, null, null, $dec2);
        }

        // $line_total = $_POST['qty'] * $_POST['price'] * (1 - $_POST['Disc'] / 100);
        $line_total = round(input_num('qty') * input_num('price'), user_price_dec());
        // amount_cell($line_total, false, '','line_total');
        label_cell(number_format2($line_total, user_amount_dec()), "nowrap align=right ", 'line_total');

        if ($id != - 1) {
            icon_submit_cells('UpdateLine', _("Update"), 'success', 'fa-save', true, _('Confirm changes'));
            icon_submit_cells('CancelUpdate', _("Cancel"), 'warning', 'fa-refresh', true, _('Confirm changes'));

//             button_cell('UpdateLine', _("Update"), _('Confirm changes'), ICON_UPDATE);
//             button_cell('CancelUpdate', _("Cancel"), _('Cancel changes'), ICON_CANCEL);
            hidden('line_no', $line_no);
            set_focus('qty');
        } else {
            icon_submit_cells('EnterLine', _("Add Item"), 'success', 'fa-plus', true, _('Add new item to document'));
            label_cell(NULL);
            // submit_cells('EnterLine', _("Add Item"), "colspan=2 align='center'", _('Add new item to document'), true);
        }

        end_row();
    }
}