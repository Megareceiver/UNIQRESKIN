<?php

class SalesTranCreditNote
{

    function __construct()
    {
        $this->page_start();
        $this->page_submit();
        $this->page_finish();
    }

    function form()
    {
        start_form();
        hidden('cart_id');
        box_start();
        $customer_error = $this->form_header($_SESSION['Items']);

        if ($customer_error == "") {
            box_start(_("Credit Note Items"));
            $this->form_items($_SESSION['Items']);
            box_start();
            $this->form_details($_SESSION['Items']);
            // display_credit_items(_("Credit Note Items"), $_SESSION['Items']);
//             credit_options_controls($_SESSION['Items']);
            // echo "</td></tr>";
            // end_table();
        } else {
            display_error($customer_error);
        }

        box_footer_start();
        submit('Update', _("Update"));
        submit('ProcessCredit', _("Process Credit Note"), true, null, 'default');
        box_footer_end();
        box_end();
        end_form();
    }

    private function form_header(&$order)
    {
        global $Ajax, $Refs;

        // start_outer_table(TABLESTYLE, "width=80%");
        // table_section(1);

        $customer_error = "";
        $change_prices = 0;

        if (! isset($_POST['customer_id']) && (get_global_customer() != ALL_TEXT))
            $_POST['customer_id'] = get_global_customer();

        row_start();
        col_start(4);
        customer_list_bootstrap(_("Customer:"), 'customer_id', null, false, true, false, true);

        if ($order->customer_id != $_POST['customer_id'] /*|| $order->sales_type != $_POST['sales_type_id']*/)
            {
            // customer has changed
            $Ajax->activate('branch_id');
        }

        customer_branches_bootstrap(_("Branch:"), $_POST['customer_id'], 'branch_id', null, false, true, true, true);

        // if (($_SESSION['credit_items']->order_no == 0) ||
        // ($order->customer_id != $_POST['customer_id']) ||
        // ($order->Branch != $_POST['branch_id']))
        // $customer_error = get_customer_details_to_order($order, $_POST['customer_id'], $_POST['branch_id']);
        if (($order->customer_id != $_POST['customer_id']) || ($order->Branch != $_POST['branch_id'])) {

            $old_order = (PHP_VERSION < 5) ? $order : clone ($order);
            $customer_error = get_customer_details_to_order($order, $_POST['customer_id'], $_POST['branch_id']);

            $_POST['Location'] = $order->Location;
            $_POST['deliver_to'] = $order->deliver_to;
            $_POST['delivery_address'] = $order->delivery_address;
            $_POST['phone'] = $order->phone;
            $Ajax->activate('Location');
            $Ajax->activate('deliver_to');
            $Ajax->activate('phone');
            $Ajax->activate('delivery_address');
            // change prices if necessary
            // what about discount in template case?
            if ($old_order->customer_currency != $order->customer_currency) {
                $change_prices = 1;
            }
            if ($old_order->sales_type != $order->sales_type) {
                // || $old_order->default_discount!=$order->default_discount
                $_POST['sales_type_id'] = $order->sales_type;
                $Ajax->activate('sales_type_id');
                $change_prices = 1;
            }
            if ($old_order->dimension_id != $order->dimension_id) {
                $_POST['dimension_id'] = $order->dimension_id;
                $Ajax->activate('dimension_id');
            }
            if ($old_order->dimension2_id != $order->dimension2_id) {
                $_POST['dimension2_id'] = $order->dimension2_id;
                $Ajax->activate('dimension2_id');
            }
            unset($old_order);
        }
        set_global_customer($_POST['customer_id']);

        if (! isset($_POST['ref']))
            $_POST['ref'] = $Refs->get_next(ST_CUSTCREDIT);
        if ($order->trans_no == 0)
            input_ref(_("Reference"), 'ref');
        else
            input_label(_("Reference"), NULL, $order->reference);

        if (! is_company_currency($order->customer_currency)) {

            input_label(_("Customer Currency:"), null, $order->customer_currency);
            exchange_rate_display(get_company_currency(), $order->customer_currency, $_POST['OrderDate']);
        }

        col_start(4);
        bootstrap_set_label_column(5);
        if (! isset($_POST['sales_type_id']))
            $_POST['sales_type_id'] = $order->sales_type;
        sales_types_bootstrap(_("Sales Type"), 'sales_type_id', $_POST['sales_type_id'], true);

        if ($order->sales_type != $_POST['sales_type_id']) {
            $myrow = get_sales_type($_POST['sales_type_id']);
            $order->set_sales_type($myrow['id'], $myrow['sales_type'], $myrow['tax_included'], $myrow['factor']);
            $Ajax->activate('sales_type_id');
            $change_prices = 1;
        }

        shippers_bootstrap(_("Shipping Company:"), 'ShipperID', $order->ship_via);
        // shippers_list_row(_("Shipping Company:"), 'ShipperID', $order->ship_via);

        input_label(_("Customer Discount:"), null, ($order->default_discount * 100) . "%");
        // label_row(_("Customer Discount:"), ($order->default_discount * 100) . "%");

        col_start(4);
        bootstrap_set_label_column(3);
        if (! isset($_POST['OrderDate']) || $_POST['OrderDate'] == "")
            $_POST['OrderDate'] = $order->document_date;

        input_date_bootstrap(_("Date:"), 'OrderDate');
        if (isset($_POST['_OrderDate_changed'])) {
            if (! is_company_currency($order->customer_currency) && (get_base_sales_type() > 0)) {
                $change_prices = 1;
            }
            $Ajax->activate('_ex_rate');
        }
        // 2008-11-12 Joe Hunt added dimensions
        $dim = get_company_pref('use_dimension');
        if ($dim > 0)
            dimensions_bootstrap(_("Dimension") . ":", 'dimension_id', null, true, ' ', false, 1, false);
        else
            hidden('dimension_id', 0);
        if ($dim > 1)
            dimensions_bootstrap(_("Dimension") . " 2:", 'dimension2_id', null, true, ' ', false, 2, false);
        else
            hidden('dimension2_id', 0);

        input_text(_("Reason"), 'reason');

        if ($change_prices != 0) {
            foreach ($order->line_items as $line_no => $item) {
                $line = &$order->line_items[$line_no];
                $line->price = get_price($line->stock_id, $order->customer_currency, $order->sales_type, $order->price_factor, get_post('OrderDate'));
                // $line->discount_percent = $order->default_discount;
            }
            $Ajax->activate('items_table');
        }
        row_end();
        return $customer_error;
    }

    private function form_items(&$order)
    {
        div_start('items_table',$trigger = null, $non_ajax = false, 'class="mb-3"');
        start_table(TABLESTYLE, "width=90%");
        $th = array(
            _("Item Code"),
            _("Item Description"),
            _('Tax'),
            _("Quantity"),
            _("Unit"),
            _("Price"),
            _("Discount %"),
            _("Total"),
            'edit'=>array('label'=>''),
            'delete'=>array('label'=>''),
        );

//         if (count($order->line_items))
//             $th[] = '';

        table_header($th);

        $subtotal = 0;
        $k = 0; // row colour counter

        $id = find_submit('Edit');

        foreach ($order->get_items() as $line_no => $line) {
            $line_total = round($line->qty_dispatched * $line->price * (1 - $line->discount_percent), user_price_dec());

            if ($id != $line_no) {
                alt_table_row_color($k);

                label_cell("<a target='_blank' href='".site_url()."inventory/inquiry/stock_status.php?stock_id=" . $line->stock_id . "'>$line->stock_id</a>");
                label_cell($line->item_description, "nowrap");
                tax_cell($line->tax_type_id);


                qty_cell($line->qty_dispatched, false, get_qty_dec($line->stock_id));
                label_cell($line->units);
                amount_cell($line->price);

                percent_cell($line->discount_percent * 100);
                amount_cell($line_total);

                tbl_edit("Edit$line_no");
                tbl_remove("Delete$line_no");

//                 edit_button_cell("Edit$line_no", _('Edit'), _('Edit document line'));
//                 delete_button_cell("Delete$line_no", _('Delete'), _('Remove line from document'));

                end_row();
            } else {
                $this->item_edit($k, $line_no);
//                 credit_edit_item_controls($order, $k, $line_no);
            }

            $subtotal += $line_total;
        }

        if ($id == - 1){
            $this->item_edit($k);
//             credit_edit_item_controls($order, $k);
        }


        $colspan = 6;
        $display_sub_total = price_format($subtotal);
        label_row(_("Sub-total"), $display_sub_total, "colspan=$colspan align=right", "align=right", 3);

        if (! isset($_POST['ChargeFreightCost']) or ($_POST['ChargeFreightCost'] == ""))
            $_POST['ChargeFreightCost'] = 0;
        start_row();

        label_cell(_("Shipping"), "colspan=$colspan align=right");

        small_amount_cells(null, 'ChargeFreightCost', price_format(get_post('ChargeFreightCost', 0)));
        label_cell('', 'colspan=3');

        end_row();
        // TUANVT5
        $taxes = $order->get_taxes_new(null, $_POST['ChargeFreightCost']);

        $tax_total = display_edit_tax_items_new($taxes, $colspan, $order->tax_included, 3);

        $display_total = price_format(($subtotal + $_POST['ChargeFreightCost'] + $tax_total));

        label_row(_("Credit Note Total"), $display_total, "colspan=$colspan align=right", "class='amount'", 3);

        end_table();
        div_end();

        box_footer(false);
    }

    private function item_edit($rowcounter, $line_no=-1){
        global $Ajax;
        $order = $_SESSION['Items'];
        alt_table_row_color($rowcounter);
        $id = find_submit('Edit');

        if ($line_no!=-1 && $line_no == $id){
            $_POST['stock_id'] = $order->line_items[$id]->stock_id;
            $dec = get_qty_dec($_POST['stock_id']);
            $_POST['qty'] = qty_format($order->line_items[$id]->qty_dispatched, $_POST['stock_id'], $dec);
            $_POST['price'] = price_format($order->line_items[$id]->price);
            $_POST['Disc'] = percent_format(($order->line_items[$id]->discount_percent)*100);
            $units = $order->line_items[$id]->units;
            hidden('stock_id', $_POST['stock_id']);
            label_cell($_POST['stock_id']);
            label_cell($order->line_items[$id]->item_description, "nowrap");
            $Ajax->activate('items_table');
        }
        else
        {
            stock_items_list_cells(null,'stock_id', null, false, true);
            if (list_updated('stock_id')) {
                $Ajax->activate('price');
                $Ajax->activate('qty');
                $Ajax->activate('units');
                $Ajax->activate('line_total');
            }
            $item_info = get_item_edit_info($_POST['stock_id']);

            $dec = $item_info['decimals'];
            $_POST['qty'] = number_format2(0, $dec);
            $units = $item_info["units"];
            $price = get_price($_POST['stock_id'],
                $order->customer_currency, $order->sales_type,
                $order->price_factor, get_post('OrderDate'));
            $_POST['price'] = price_format($price);

            // default to the customer's discount %
            $_POST['Disc'] = percent_format($order->default_discount * 100);
        }
        // 	stock_invoice_list_row(null, 'tax_type_id', null, false, null,'3');
//         echo $ci->finput->inputtaxes(null,'tax_type_id',null,2,'in_row_input');
        item_tax_types_list_cells(null, 'tax_type_id', NULL, 2);
        qty_cells(null, 'qty', $_POST['qty'], null, null, $dec);

        label_cell($units, '', 'units');
        amount_cells(null, 'price');
        small_amount_cells(null, 'Disc', percent_format($_POST['Disc']), null, null, user_percent_dec());

        amount_cell(input_num('qty') * input_num('price') * (1 - input_num('Disc')/100), false, '', 'line_total');

        if ($id!=-1){
            tbl_update("UpdateItem");
            tbl_cancel("CancelItemChanges");

//             icon_submit_cells('UpdateItem', _("Update"), 'success', 'fa-save', true, _('Confirm changes'));
//             icon_submit_cells('CancelItemChanges', _("Cancel"), 'warning', 'fa-refresh', true, _('Cancel changes'));

            hidden('line_no', $line_no);
            set_focus('qty');
        } else {
            tbl_add('AddItem');
//             icon_submit_cells('AddItem', _("Add Item"), 'success', 'fa-plus', true, _('Add new item to document'));
            label_cell(NULL);

        }

        end_row();
    }


    private function form_details($credit){
        global $Ajax;

        if (isset($_POST['_CreditType_update']))
            $Ajax->activate('options');


        div_start('options');
        row_start('justify-content-center');
        col_start(8);
//         start_table(TABLESTYLE2);

        credit_types(_("Credit Note Type"), 'CreditType', null, true);

        if ($_POST['CreditType'] == "Return"){

            /*if the credit note is a return of goods then need to know which location to receive them into */
            if (!isset($_POST['Location']))
                $_POST['Location'] = $credit->Location;
            locations_bootstrap(_("Items Returned to Location"), 'Location', $_POST['Location']);
        } else {
            /* the goods are to be written off to somewhere */
            gl_all_accounts_list_row(_("Write off the cost of the items to"), 'WriteOffGLCode', null);
        }
        input_textarea(_("Memo"), "CreditText");
        row_end();
        div_end();
    }

    private function page_start()
    {}

    private function page_submit()
    {
        if (list_updated('branch_id')) {
            // when branch is selected via external editor also customer can change
            $br = get_branch(get_post('branch_id'));
            $_POST['customer_id'] = $br['debtor_no'];
            $Ajax->activate('customer_id');
        }

        if (isset($_POST['ProcessCredit']) && can_process()) {
            copy_to_cn();
            if ($_POST['CreditType'] == "WriteOff" && (! isset($_POST['WriteOffGLCode']) || $_POST['WriteOffGLCode'] == '')) {
                display_note(_("For credit notes created to write off the stock, a general ledger account is required to be selected."), 1, 0);
                display_note(_("Please select an account to write the cost of the stock off to, then click on Process again."), 1, 0);
                exit();
            }
            if (! isset($_POST['WriteOffGLCode'])) {
                $_POST['WriteOffGLCode'] = 0;
            }
            copy_to_cn();
            $credit_no = $_SESSION['Items']->write($_POST['WriteOffGLCode']);
            if ($credit_no == - 1) {
                display_error(_("The entered reference is already in use."));
                set_focus('ref');
            } else {
                new_doc_date($_SESSION['Items']->document_date);
                processing_end();
                meta_forward($_SERVER['PHP_SELF'], "AddedID=$credit_no");
            }
        } /* end of process credit note */

        $id = find_submit('Delete');
        if ($id != - 1)
            handle_delete_item($id);

        if (isset($_POST['AddItem']))
            handle_new_item();

        if (isset($_POST['UpdateItem']))
            handle_update_item();

        if (isset($_POST['CancelItemChanges']))
            line_start_focus();

            // -----------------------------------------------------------------------------

        if (! processing_active()) {
            handle_new_credit(0);
        }
    }

    private function page_finish()
    {
        if (isset($_GET['AddedID'])) {
            $credit_no = $_GET['AddedID'];
            $trans_type = ST_CUSTCREDIT;

            display_notification_centered(sprintf(_("Credit Note # %d has been processed"), $credit_no));

            box_start();
            row_start();
            col_start(6);
            mt_list_start('Printing',null,'red');
            
//             display_note(get_customer_trans_view_str($trans_type, $credit_no, _("&View this credit note")), 0, 1);
            mt_list_tran_view(_("&View this credit note"),$trans_type, $credit_no);
            

            mt_list(print_document_link($credit_no . "-" . $trans_type, _("&Print This Credit Invoice"), true, ST_CUSTCREDIT), 0, 1);
            mt_list(print_document_link($credit_no . "-" . $trans_type, _("&Email This Credit Invoice"), true, ST_CUSTCREDIT, false, "printlink", "", 1), 0, 1);


//             display_note(get_gl_view_str($trans_type, $credit_no, _("View the GL &Journal Entries for this Credit Note")));
            mt_list_gl_view( _("View the GL &Journal Entries for this Credit Note"),$trans_type, $credit_no);

            col_start(6);
            mt_list_start('Actions', '', 'blue');
//             hyperlink_params($_SERVER['PHP_SELF'], _("Enter Another &Credit Note"), "NewCredit=yes");
            mt_list_hyperlink($_SERVER['PHP_SELF'], _("Enter Another &Credit Note"), "NewCredit=yes");

//             hyperlink_params(site_url() . "/admin/attachments.php", _("Add an Attachment"), "filterType=$trans_type&trans_no=$credit_no");
            mt_list_hyperlink("/admin/attachments.php", _("Add an Attachment"), "filterType=$trans_type&trans_no=$credit_no");
            
            row_end();
            box_footer();
            box_end();
            display_footer_exit();
        } else
            check_edit_conflicts();
    }
}