<?php

class SalesEntryOrder
{

    function __construct()
    {
        $this->bootstrap = get_instance()->bootstrap;
        $this->page_variables();
        $this->ajax_updates();
    }

    private function page_variables()
    {
        if (isset($_SESSION['Items'])) {
            $tran_type = $_SESSION['Items']->trans_type;
        } else {
            $tran_type = NULL;
        }

        switch ($tran_type) {
            case ST_SALESINVOICE:
                $this->date_title = _("Invoice Date");
                $this->orderitems_title = _("Sales Invoice Items");
                $this->deliverydetails_title = _("Enter Delivery Details and Confirm Invoice");
                $this->cancelorder_title = _("Cancel Invoice");
                $this->porder_title = _("Place Invoice");
                break;
            case ST_CUSTDELIVERY:
                $this->date_title = _("Delivery Date");
                $this->orderitems_title = _("Delivery Note Items");
                $this->deliverydetails_title = _("Enter Delivery Details and Confirm Dispatch");
                $this->cancelorder_title = _("Cancel Delivery");
                $this->porder_title = _("Place Delivery");
                break;
            case ST_SALESQUOTE:
                $this->date_title = _("Quotation Date");
                $this->orderitems_title = _("Sales Quotation Items");
                $this->deliverydetails_title = _("Enter Delivery Details and Confirm Quotation");
                $this->cancelorder_title = _("Cancel Quotation");
                $this->porder_title = _("Place Quotation");
                $this->corder_title = _("Commit Quotations Changes");
                break;
            default:
                $this->date_title = _("Order Date");
                $this->orderitems_title = _("Sales Order Items");
                $this->deliverydetails_title = _("Enter Delivery Details and Confirm Order");
                $this->cancelorder_title = _("Cancel Order");
                $this->porder_title = _("Place Order");
                $this->corder_title = _("Commit Order Changes");
                break;
        }
    }

    var $error = FALSE;

    function form_header()
    {
        global $Ajax, $SysPrefs, $ci;

        $order = $_SESSION['Items'];
        $editable = ($_SESSION['Items']->any_already_delivered() == 0);
        $change_prices = 0;

        $this->bootstrap->col_start(5);
        $this->bootstrap->label_column = 4;

        if (isset($order) && ! $editable) {
            // can't change the customer/branch if items already received on this order
            // echo $order->customer_name . " - " . $order->deliver_to;
            label_row(null, $order->customer_name . " - " . $order->deliver_to);
            hidden('customer_id', $order->customer_id);
            hidden('branch_id', $order->Branch);
            hidden('sales_type', $order->sales_type);
            if ($order->trans_type != ST_SALESORDER && $order->trans_type != ST_SALESQUOTE) {
                hidden('dimension_id', $order->dimension_id); // 2008-11-12 Joe Hunt
                hidden('dimension2_id', $order->dimension2_id);
            }
        } else {

            customer_list_bootstrap(_("Customer"), 'customer_id', null, true, false, false);

            if ($order->customer_id != get_post('customer_id', - 1)) {
                $Ajax->activate('branch_id');
            }

            customer_branches_bootstrap(_("Branch"), input_post('customer_id'), 'branch_id', null, true, true, true);
//             customer_branches_list_cells($label, $customer_id, $name, $selected_id = null, $all_option = true, $enabled = true, $submit_on_change = false, $editkey = false)

            if (($order->customer_id != get_post('customer_id', - 1)) || ($order->Branch != get_post('branch_id', - 1)) || list_updated('customer_id')) {

                if (! isset($_POST['branch_id']) || $_POST['branch_id'] == "") {
                    // ignore errors on customer search box call
                    if ($_POST['customer_id'] == '')
                        $this->error = _("No customer found for entered text.");
                    else
                        $this->error = _("The selected customer does not have any branches. Please create at least one branch.");
                    unset($_POST['branch_id']);
                    $order->Branch = 0;
                } else {

                    $old_order = (PHP_VERSION < 5) ? $order : clone ($order);

                    $this->error = get_customer_details_to_order($order, $_POST['customer_id'], $_POST['branch_id']);
                    $_POST['Location'] = $order->Location;
                    $_POST['deliver_to'] = $order->deliver_to;
                    $_POST['delivery_address'] = $order->delivery_address;
                    $_POST['phone'] = $order->phone;
                    $_POST['delivery_date'] = $order->due_date;

                    if (! in_array($order->trans_type, array(
                        ST_SALESQUOTE,
                        ST_SALESORDER
                    )) && ($order->pos['cash_sale'] != $order->pos['credit_sale']) && (($order->payment_terms['cash_sale'] && ! $order->pos['cash_sale']) || (! $order->payment_terms['cash_sale'] && ! $order->pos['credit_sale']))) {
                        // force payment terms refresh if terms are editable
                        // and pos have no permitions for terms selected in customer record.
                        // Terms are set to first terms in allowed category below.
                        display_warning(sprintf(_("Customer's payment terms '%s' cannot be selected on this POS"), $order->payment_terms['terms']));
                        $order->payment = '';
                    } elseif (get_post('payment') !== $order->payment) {
                        $_POST['payment'] = $order->payment;
                        $Ajax->activate('delivery');
                        $Ajax->activate('payment');
                    } else {
                        if ($order->trans_type == ST_SALESINVOICE) {
                            $_POST['delivery_date'] = $order->due_date;
                            $Ajax->activate('delivery_date');
                        }
                        $Ajax->activate('Location');
                        $Ajax->activate('deliver_to');
                        $Ajax->activate('phone');
                        $Ajax->activate('delivery_address');
                    }
                    // change prices if necessary
                    // what about discount in template case?
                    if ($old_order->customer_currency != $order->customer_currency) {
                        $change_prices = 1;
                    }
                    if ($old_order->sales_type != $order->sales_type) {
                        // || $old_order->default_discount!=$order->default_discount
                        $_POST['sales_type'] = $order->sales_type;
                        $Ajax->activate('sales_type');
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
            } else { // changed branch
                $row = get_customer_to_order($_POST['customer_id']);
                if ($row['dissallow_invoices'] == 1)
                    $customer_error = _("The selected customer account is currently on hold. Please contact the credit control personnel to discuss.");
            }
        }

        input_text_iconright_bootstrap(_("Reference"), 'ref', null, 'fa-key');

        if (defined('COUNTRY') && COUNTRY == 65) {
            input_text_bootstrap(_("Customer Reference"), 'customer_ref');
        } else {
            hidden('customer_ref');
        }

        if (! $order->payment_terms['cash_sale']) {
            input_text_bootstrap(_("Customer Reference"), 'cust_ref2', $order->cust_ref, _('Reference number unique for this document type'));
            locations_bootstrap(_("Deliver from Location"), 'Location2', null, true);
        }

        $this->bootstrap->col_start(4);
        $this->bootstrap->label_column = 5;

        if (! is_company_currency($order->customer_currency) && in_array($order->trans_type, array(
            ST_SALESINVOICE,
            ST_CUSTDELIVERY
        ))) {
            input_label_bootstrap(_("Customer Currency"), NULL, $order->customer_currency);
        }

        exchange_rate_bootstrap(get_company_currency(), $order->customer_currency, ($editable ? input_val('OrderDate') : $order->document_date));

        customer_credit_bootstrap($_POST['customer_id'], $order->credit);
        input_label_bootstrap('Customer Discount', NULL, ($order->default_discount * 100) . "%");

        $this->bootstrap->col_start(3);

        if ($order->pos['cash_sale'] || $order->pos['credit_sale']) {
            // editable payment type
            if (get_post('payment') !== $order->payment) {
                $order->payment = get_post('payment');
                $order->payment_terms = get_payment_terms($order->payment);
                $order->due_date = get_invoice_duedate($order->payment, $order->document_date);
                if ($order->payment_terms['cash_sale']) {
                    $_POST['Location'] = $order->Location = $order->pos['pos_location'];
                    $order->location_name = $order->pos['location_name'];
                }
                $Ajax->activate('items_table');
                $Ajax->activate('delivery');
            }

            $paymcat = ! $order->pos['cash_sale'] ? PM_CREDIT : (! $order->pos['credit_sale'] ? PM_CASH : PM_ANY);
            // all terms are available for SO

            if (in_array($order->trans_type, array(
                ST_SALESQUOTE,
                ST_SALESORDER
            ))) {
                $paymcat = PM_ANY;
            }
            sale_payments_bootstrap(_('Payment:'), 'payment', $paymcat, null, false);
        } else {
            input_label_bootstrap('Payment', NULL, $order->payment_terms['terms']);
        }

        if ($editable) {
            sales_types_bootstrap(_("Price List"), 'sales_type', null, true);
        } else {
            input_label_bootstrap('Price List', NULL, $order->sales_type_name);
        }
        // bug('post='.input_post('sales_type'));
        // bug($order->sales_type);
        if ($order->sales_type != input_post('sales_type')) {
            $myrow = get_sales_type(input_post('sales_type'));
            $order->set_sales_type($myrow['id'], $myrow['sales_type'], $myrow['tax_included'], $myrow['factor']);
            $Ajax->activate('sales_type');
            $change_prices = 1;
        }

        if ($editable) {
            if (! isset($_POST['OrderDate']) || $_POST['OrderDate'] == "")
                $_POST['OrderDate'] = $order->document_date;

                // echo $ci->finput->qdate($date_text,'OrderDate',Today(),'row');
            input_date_bootstrap($this->date_title, 'OrderDate', Today());

            if (isset($_POST['_OrderDate_changed']) || list_updated('payment')) {
                if (! is_company_currency($order->customer_currency) && (get_base_sales_type() > 0)) {
                    $change_prices = 1;
                }
                $Ajax->activate('_ex_rate');

                if ($order->trans_type == ST_SALESINVOICE) {
                    $_POST['delivery_date'] = get_invoice_duedate(get_post('payment'), get_post('OrderDate'));
                } else
                    $_POST['delivery_date'] = add_days(get_post('OrderDate'), $SysPrefs->default_delivery_required_by());
                $Ajax->activate('items_table');
                $Ajax->activate('delivery_date');
            }

            if ($order->trans_type != ST_SALESORDER && $order->trans_type != ST_SALESQUOTE) { // 2008-11-12 Joe Hunt added dimensions
                $dim = get_company_pref('use_dimension');
                if ($dim > 0) {
                    dimensions_bootstrap(0, _("Dimension") . ":", 'dimension_id', null, true, ' ', false, 1, false);
                } else
                    hidden('dimension_id', 0);

                if ($dim > 1)
                    dimensions_bootstrap(0, _("Dimension 2") . ":", 'dimension2_id', null, true, ' ', false, 2, false);
                else
                    hidden('dimension2_id', 0);
            }
        } else {
            input_label_bootstrap(0, $this->date_title, NULL, $order->document_date, 4);
            hidden('OrderDate', $order->document_date);
        }
        bootstrap_div_end();

        if ($change_prices != 0) {
            foreach ($order->line_items as $line_no => $item) {
                $line = &$order->line_items[$line_no];
                $line->price = get_kit_price($line->stock_id, $order->customer_currency, $order->sales_type, $order->price_factor, get_post('OrderDate'));
                // $line->discount_percent = $order->default_discount;
            }
            $Ajax->activate('items_table');
        }
    }

    function form_delivery()
    {
        global $Ajax;
        $order = $_SESSION['Items'];

        div_start('delivery');

        if ($order->payment_terms['cash_sale']) { // Direct payment sale
            $Ajax->activate('items_table');
            display_heading(_('Cash payment'));

            start_table(TABLESTYLE2, "width=60%");

            locations_list_row(_("Deliver from Location:"), 'Location', null, false, true);
            if (list_updated('Location'))
                $Ajax->activate('items_table');

                // label_row(_("Cash account:"), $order->pos['bank_account_name']);
            $cash_sales_invoice = get_gl_account(get_company_pref('cash_sales_invoice'));
            label_row(_("Cash account:"), $cash_sales_invoice['account_name']);

            textarea_row(_("Comments:"), "Comments", $order->Comments, 31, 5);
            end_table();
            hidden('delivery_date', $order->due_date);
        } else {
            if ($order->trans_type == ST_SALESINVOICE) {
                $title = _("Delivery Details");
                $delname = _("Due Date") . ':';
            } elseif ($order->trans_type == ST_CUSTDELIVERY) {
                $title = _("Invoice Delivery Details");
                $delname = _("Invoice before") . ':';
            } elseif ($order->trans_type == ST_SALESQUOTE) {
                $title = _("Quotation Delivery Details");
                $delname = _("Valid until") . ':';
            } else {
                $title = _("Order Delivery Details");
                $delname = _("Required Delivery Date") . ':';
            }
            $this->bootstrap->box_start($title);
            // echo '<div class="text-center font-blue" ><h4>'.$title.'</h4></div>';
            // display_heading($title);

            $this->bootstrap->col_start(6);
            $this->bootstrap->label_column = 4;

            locations_bootstrap(_("Deliver from Location"), 'Location');

            if (list_updated('Location'))
                $Ajax->activate('items_table');

                // date_row($delname, 'delivery_date',
                // $order->trans_type==ST_SALESORDER ? _('Enter requested day of delivery')
                // : $order->trans_type==ST_SALESQUOTE ? _('Enter Valid until Date') : '');

            input_date_bootstrap($this->date_title, 'delivery_date', Today());

            input_text_bootstrap(_("Deliver To"), 'deliver_to', $order->deliver_to, _('Additional identifier for delivery e.g. name of receiving person'));
            input_textarea_bootstrap(_("Address:"), 'delivery_address', $order->delivery_address, _('Delivery address. Default is address of customer branch'), 5);

            $this->bootstrap->col_start(6);
            input_text_bootstrap(_("Contact Phone Number"), 'phone', $order->phone, _('Phone number of ordering person. Defaults to branch phone number'));
            input_text_bootstrap(_("Customer Reference"), 'cust_ref', $order->cust_ref, _('Customer reference number for this order (if any)'));
            shippers_bootstrap(_("Shipping Company"), 'ship_via', $order->ship_via);

            input_textarea_bootstrap(_("Comments"), 'Comments', $order->Comments, _('Delivery address. Default is address of customer branch'), 5);
            $this->bootstrap->col_end();
        }
        $this->bootstrap->box_end();
        div_end();
    }

    function form_items($title, &$order, $editable_items=false)
    {
        $this->bootstrap->box_start($title);
        div_start('items_table');

        start_table(TABLESTYLE, array("colspan"=>8 ,"width"=>"70%" ,'class'=>'table table-striped table-bordered table-hover'));
        $th = array(
            _("Item Code"),
            _("Item Description"),
            _("Tax"),
            _("Quantity"),
            _("Delivered"),
            _("Unit"),
            $order->tax_included ? _("Price after Tax") : _("Price before Tax"),
            _("Discount %"),
            _("Total"),
            ""
        );

        if ($order->trans_no == 0) {
            unset($th[4]);
        }

        if (count($order->line_items))
            $th[] = '';

        table_header($th);

        $total = $discount_total = 0;
        $k = 0; // row colour counter

        $id = find_submit('Edit');

        $low_stock = $order->check_qoh($_POST['OrderDate'], $_POST['Location']);
        foreach ($order->get_items() as $line_no => $stock_item) {

            $line_total = round($stock_item->qty_dispatched * $stock_item->price * (1 - $stock_item->discount_percent), user_price_dec());
            $discount_total += $stock_item->qty_dispatched * $stock_item->price * $stock_item->discount_percent;
            $qoh_msg = '';
            if (! $editable_items || $id != $line_no) {
                if (in_array($stock_item->stock_id, $low_stock))
                    start_row("class='stockmankobg'"); // notice low stock status
                else
                    alt_table_row_color($k);

                view_stock_status_cell($stock_item->stock_id);

                // label_cell($stock_item->item_description, "nowrap" );
                label_cell($stock_item->item_description);

                // TUANVT5

                $tax_str = NULL;
                $tax_detail = get_gst($stock_item->tax_type_id);
                if ($stock_item->tax_type_id && is_object($tax_detail)) {
                    $tax_str = $tax_detail->no . "(" . $tax_detail->rate . "%)";
                }

                label_cell($tax_str);

                $dec = get_qty_dec($stock_item->stock_id);
                qty_cell($stock_item->qty_dispatched, false, $dec);

                if ($order->trans_no != 0)
                    qty_cell($stock_item->qty_done, false, $dec);

                label_cell($stock_item->units);
                amount_cell($stock_item->price);

                percent_cell($stock_item->discount_percent * 100);
                // amount_cell($line_total);
                label_cell(number_format2($line_total, user_amount_dec()), "nowrap align=right ");

                if ($editable_items) {
                    edit_button_cell("Edit$line_no", _("Edit"), _('Edit document line'));
                    delete_button_cell("Delete$line_no", _("Delete"), _('Remove line from document'));
                }
                end_row();
            } else {
                sales_order_item_controls($order, $k, $line_no);
            }

            $total += $line_total;
        }

        if ($id == - 1 && $editable_items)
            sales_order_item_controls($order, $k);

        $colspan = 6;
        if ($order->trans_no != 0)
            ++ $colspan;

        start_row();
        label_cell(_("Shipping Charge"), "colspan=$colspan align=right", 3);
        small_amount_cells(null, 'freight_cost', price_format(get_post('freight_cost', 0)));
        label_cell('', 'colspan=3');
        end_row();
        // $display_sub_total = price_format($total + input_num('freight_cost'));
        $display_sub_total = number_format2($total + input_num('freight_cost'), user_amount_dec());

        label_row(_("Sub-total"), $display_sub_total, "colspan=$colspan align=right", "align=right", 3);

        $taxes = $order->get_taxes_new($order->customer_tax_id, input_num('freight_cost'));
        $tax_total = display_edit_tax_items_new($taxes, $colspan, $order->tax_included, 3);

        // $taxes = $order->get_taxes(input_num('freight_cost'));
        // $tax_total = display_edit_tax_items($taxes, $colspan, $order->tax_included, 2);

        // $display_total = price_format(($total + input_num('freight_cost') + $tax_total));
        $display_total = number_format2(($total + input_num('freight_cost') + $tax_total), user_amount_dec());

        label_row(_("Discount Given"), number_format2($discount_total, user_amount_dec()), "colspan=$colspan align=right", "align=right", 3);
        // label_cells(_("Amount Total"), $display_total, "colspan=$colspan align=right","align=right");
        label_row(_("Amount Total"), $display_total, "colspan=$colspan align=right", "align=right", 3);
        start_row();
        submit_cells('update', _("Update"), "colspan=7 align='right'", _("Refresh"), true);
        // label_cells();
        // label_cells();
        end_row();

        end_table();
        if ($low_stock)
            display_note(_("Marked items have insufficient quantities in stock as on day of delivery."), 0, 1, "class='stockmankofg'");

        div_end();
        $this->bootstrap->box_end();
    }

    function create_cart_entry()
    {
        $delivery_new = input_get('NewDelivery');
        $invoice_new = input_get('NewInvoice');

        if (! is_null($delivery_new)) {

            $_SESSION['page_title'] = _($help_context = "Direct Sales Delivery");
            create_cart(ST_CUSTDELIVERY, $delivery);
        } elseif (! is_null($invoice_new)) {

            $_SESSION['page_title'] = _($help_context = "Direct Sales Invoice");
            $this->create_cart(ST_SALESINVOICE, 0);
        } elseif (isset($_GET['ModifyOrderNumber']) && is_numeric($_GET['ModifyOrderNumber'])) {

            $help_context = 'Modifying Sales Order';
            $_SESSION['page_title'] = sprintf(_("Modifying Sales Order # %d"), $_GET['ModifyOrderNumber']);
            create_cart(ST_SALESORDER, $_GET['ModifyOrderNumber']);
        } elseif (isset($_GET['ModifyQuotationNumber']) && is_numeric($_GET['ModifyQuotationNumber'])) {

            $help_context = 'Modifying Sales Quotation';
            $_SESSION['page_title'] = sprintf(_("Modifying Sales Quotation # %d"), $_GET['ModifyQuotationNumber']);
            create_cart(ST_SALESQUOTE, $_GET['ModifyQuotationNumber']);
        } elseif (isset($_GET['NewOrder'])) {

            $_SESSION['page_title'] = _($help_context = "New Sales Order Entry");
            create_cart(ST_SALESORDER, 0);
        } elseif (isset($_GET['NewQuotation'])) {

            $_SESSION['page_title'] = _($help_context = "New Sales Quotation Entry");
            create_cart(ST_SALESQUOTE, 0);
        } elseif (isset($_GET['NewQuoteToSalesOrder'])) {
            $_SESSION['page_title'] = _($help_context = "Sales Order Entry");
            create_cart(ST_SALESQUOTE, $_GET['NewQuoteToSalesOrder']);
        } else {
            $reinvoice = input_get('reinvoice');
            if ($reinvoice) {
                // set_time_limit(0);
                $cart = new Cart(ST_SALESINVOICE, array(
                    $reinvoice
                ));
                $sale_model = $ci->model('sale', true);

                $invoice_no = $sale_model->write_sales_invoice($cart, true);
                // die('go here');
                redirect('gl/view/gl_trans_view.php?type_id=10&trans_no=' . $invoice_no);
                // die('go here to repost ivoice no='.$invoice_no);
            }
        }
    }

    private function ajax_updates()
    {
        global $Ajax;

        if (list_updated('branch_id')) {
            // when branch is selected via external editor also customer can change
            $br = get_branch(get_post('branch_id'));
            $_POST['customer_id'] = $br['debtor_no'];
            $Ajax->activate('customer_id');
        }

        if (isset($_POST['update'])) {
            $this->copy_to_cart();
            $Ajax->activate('items_table');
        }

        if (isset($_POST['page_reload'])) {
            page_modified();
        }

        if (isset($_POST['ProcessOrder']) && $this->can_process()) {
            $this->entry_process();
        }
        
        if (isset($_POST['CancelOrder']))
            $this->handle_cancel_order();

        $id = find_submit('Delete');
        if ($id != - 1)
            $this->handle_delete_item($id);

        if (isset($_POST['UpdateItem']))
            $this->handle_update_item();

        if (isset($_POST['AddItem'])) {
            $this->handle_new_item();
        }

        if (isset($_POST['CancelItemChanges'])) {
            $this->line_start_focus();
        }
    }

    function line_start_focus()
    {
        global $Ajax;

        $Ajax->activate('items_table');
        set_focus('_stock_id_edit');
    }

    function entry_process()
    {
        $modified = ($_SESSION['Items']->trans_no != 0);
        $so_type = $_SESSION['Items']->so_type;

        $ret = $_SESSION['Items']->write(1);
        if ($ret == - 1) {
            display_error(_("The entered reference is already in use."));
            $ref = get_next_reference($_SESSION['Items']->trans_type);
            if ($ref != $_SESSION['Items']->reference) {
                display_error(_("The reference number field has been increased. Please save the document again."));
                $_POST['ref'] = $_SESSION['Items']->reference = $ref;
                $Ajax->activate('ref');
            }
            set_focus('ref');
        } else {
            if (isset($messages) && count($messages)) { // abort on failure or error messages are lost
                $Ajax->activate('_page_body');
                display_footer_exit();
            }
            $trans_no = key($_SESSION['Items']->trans_no);
            $trans_type = $_SESSION['Items']->trans_type;
            new_doc_date($_SESSION['Items']->document_date);
            if (isset($_POST['customer_ref'])) {
                update_source_ref($trans_type, $trans_no, $_POST['customer_ref']);
            }
            processing_end();

            if ($modified) {
                if ($trans_type == ST_SALESQUOTE)
                    meta_forward($_SERVER['PHP_SELF'], "UpdatedQU=$trans_no");
                else
                    meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$trans_no");
            } elseif ($trans_type == ST_SALESORDER) {
                meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
            } elseif ($trans_type == ST_SALESQUOTE) {
                meta_forward($_SERVER['PHP_SELF'], "AddedQU=$trans_no");
            } elseif ($trans_type == ST_SALESINVOICE) {
                meta_forward($_SERVER['PHP_SELF'], "AddedDI=$trans_no&Type=$so_type");
            } else {
                meta_forward($_SERVER['PHP_SELF'], "AddedDN=$trans_no&Type=$so_type");
            }
        }
    }

    private function can_process()
    {
        global $Refs, $SysPrefs;

        $this->copy_to_cart();

        if (! get_post('customer_id')) {
            display_error(_("There is no customer selected."));
            set_focus('customer_id');
            return false;
        } else {

            $currency_code = get_customer_currency(get_post('customer_id'));

            $ex_rate = get_exchange_rate_from_home_currency($currency_code, $_POST['OrderDate']);
            if ($currency_code != get_company_currency() && $ex_rate == 1) {
                if ($_POST['ex_rate_allow'] != 1) {
                    display_error(_("Cannot retrieve exchange rate for currency $currency_code as of " . $_POST['OrderDate'] . ". Please add exchange rate manually on Exchange Rates page."));
                    set_focus('_ex_rate');
                    return false;
                }
            }
        }

        if (! get_post('branch_id')) {
            display_error(_("This customer has no branch defined."));
            set_focus('branch_id');
            return false;
        }

        if (! is_date($_POST['OrderDate'])) {
            display_error(_("The entered date is invalid."));
            set_focus('OrderDate');
            return false;
        }

        if ($_SESSION['Items']->trans_type != ST_SALESORDER && $_SESSION['Items']->trans_type != ST_SALESQUOTE && ! is_date_in_fiscalyear($_POST['OrderDate'])) {
            display_error(_("The entered date is not in fiscal year"));
            set_focus('OrderDate');
            return false;
        }
        if (count($_SESSION['Items']->line_items) == 0) {
            display_error(_("You must enter at least one non empty item line."));
            set_focus('AddItem');
            return false;
        }

        // if ( get_post('payment') && get_post('payment')==4 ) {

        // }

        // display_error($low_stock = $_SESSION['Items']->check_qoh());

        // if (!$SysPrefs->allow_negative_stock() && ($low_stock = $_SESSION['Items']->check_qoh()))
        // {
        // display_error(_("This document cannot be processed because there is insufficient quantity for items marked."));
        // return false;
        // }

        if ($_SESSION['Items']->payment_terms['cash_sale'] == 0) {

            if (strlen($_POST['deliver_to']) <= 1) {
                display_error(_("You must enter the person or company to whom delivery should be made to."));
                set_focus('deliver_to');
                return false;
            }
            if ($_SESSION['Items']->trans_type != ST_SALESQUOTE && strlen($_POST['delivery_address']) <= 1) {
                display_error(_("You should enter the street address in the box provided. Orders cannot be accepted without a valid street address."));
                set_focus('delivery_address');
                return false;
            }

            if ($_POST['freight_cost'] == "")
                $_POST['freight_cost'] = price_format(0);

            if (! check_num('freight_cost', 0)) {
                display_error(_("The shipping cost entered is expected to be numeric."));
                set_focus('freight_cost');
                return false;
            }
            if (! is_date($_POST['delivery_date'])) {
                if ($_SESSION['Items']->trans_type == ST_SALESQUOTE)
                    display_error(_("The Valid date is invalid."));
                else
                    display_error(_("The delivery date is invalid."));
                set_focus('delivery_date');
                return false;
            }
            if (date1_greater_date2($_POST['OrderDate'], $_POST['delivery_date'])) {
                if ($_SESSION['Items']->trans_type == ST_SALESQUOTE)
                    display_error(_("The requested valid date is before the date of the quotation."));
                else
                    display_error(_("The requested delivery date is before the date of the order."));
                set_focus('delivery_date');
                return false;
            }
        } else
            if (! db_has_cash_accounts() || ! $_SESSION['Items']->pos['bank_account_name']) {
                display_error(_("You need to define a cash account for your " . anchor('sales/manage/sales_points.php', 'Sales Point') . "."));
                return false;
            }

        if (! $Refs->is_valid($_POST['ref'])) {
            display_error(_("You must enter a reference."));
            set_focus('ref');
            return false;
        }
        // if (!db_has_currency_rates($_SESSION['Items']->customer_currency, $_POST['OrderDate']))
        // return false;

        if ($_SESSION['Items']->get_items_total() < 0) {
            display_error("Invoice total amount cannot be less than zero.");
            return false;
        }

        return true;
    }

    /*
     * Cart entry
     */
    function create_cart($type, $trans_no)
    {
        global $Refs;

        if (! $_SESSION['SysPrefs']->db_ok) // create_cart is called before page() where the check is done
            return;

        processing_start();

        if (isset($_GET['NewQuoteToSalesOrder'])) {
            $trans_no = $_GET['NewQuoteToSalesOrder'];
            $doc = new Cart(ST_SALESQUOTE, $trans_no, true);
            $doc->Comments = _("Sales Quotation") . " # " . $trans_no;

            $_SESSION['Items'] = $doc;
        } elseif ($type != ST_SALESORDER && $type != ST_SALESQUOTE && $trans_no != 0) { // this is template

            $doc = new Cart(ST_SALESORDER, array(
                $trans_no
            ));
            $doc->trans_type = $type;
            $doc->trans_no = 0;
            $doc->document_date = new_doc_date();
            if ($type == ST_SALESINVOICE) {
                $doc->due_date = get_invoice_duedate($doc->payment, $doc->document_date);
                $doc->pos = get_sales_point(user_pos());
            } else
                $doc->due_date = $doc->document_date;
            $doc->reference = $Refs->get_next($doc->trans_type);
            // $doc->Comments='';
            foreach ($doc->line_items as $line_no => $line) {
                $doc->line_items[$line_no]->qty_done = 0;
            }
            $_SESSION['Items'] = $doc;
        } else
            $_SESSION['Items'] = new Cart($type, array(
                $trans_no
            ));
        $this->copy_from_cart();
    }

    function copy_from_cart()
    {
        $cart = &$_SESSION['Items'];
        $_POST['ref'] = $cart->reference;
        $_POST['Comments'] = $cart->Comments;

        $_POST['OrderDate'] = $cart->document_date;
        $_POST['delivery_date'] = $cart->due_date;
        $_POST['cust_ref'] = $cart->cust_ref;
        $_POST['freight_cost'] = price_format($cart->freight_cost);

        $_POST['deliver_to'] = $cart->deliver_to;
        $_POST['delivery_address'] = $cart->delivery_address;
        $_POST['phone'] = $cart->phone;
        $_POST['Location'] = $cart->Location;
        $_POST['ship_via'] = $cart->ship_via;

        $_POST['customer_id'] = $cart->customer_id;

        $_POST['branch_id'] = $cart->Branch;
        $_POST['sales_type'] = $cart->sales_type;
        // POS
        $_POST['payment'] = $cart->payment;
        if ($cart->trans_type != ST_SALESORDER && $cart->trans_type != ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
            $_POST['dimension_id'] = $cart->dimension_id;
            $_POST['dimension2_id'] = $cart->dimension2_id;
        }
        $_POST['cart_id'] = $cart->cart_id;
        $_POST['_ex_rate'] = $cart->ex_rate;
    }

    function copy_to_cart()
    {
        $cart = &$_SESSION['Items'];

        $cart->reference = $_POST['ref'];

        $cart->Comments = $_POST['Comments'];

        $cart->document_date = $_POST['OrderDate'];

        $newpayment = false;

        if (isset($_POST['payment']) && ($cart->payment != $_POST['payment'])) {
            $cart->payment = $_POST['payment'];
            $cart->payment_terms = get_payment_terms($_POST['payment']);
            $newpayment = true;
        }
        if ($cart->payment_terms['cash_sale']) {
            if ($newpayment) {
                $cart->due_date = $cart->document_date;
                $cart->phone = $cart->cust_ref = $cart->delivery_address = '';
                $cart->ship_via = 0;
                $cart->deliver_to = '';
            }
        } else {
            $cart->due_date = $_POST['delivery_date'];
            $cart->cust_ref = $_POST['cust_ref'];
            $cart->deliver_to = $_POST['deliver_to'];
            $cart->delivery_address = $_POST['delivery_address'];
            $cart->phone = $_POST['phone'];
            $cart->ship_via = $_POST['ship_via'];
        }
        $cart->Location = $_POST['Location'];
        $cart->freight_cost = input_num('freight_cost');
        if (isset($_POST['email']))
            $cart->email = $_POST['email'];
        else
            $cart->email = '';
        $cart->customer_id = $_POST['customer_id'];
        $cart->Branch = $_POST['branch_id'];
        $cart->sales_type = $_POST['sales_type'];

        if ($cart->trans_type != ST_SALESORDER && $cart->trans_type != ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
            $cart->dimension_id = $_POST['dimension_id'];
            $cart->dimension2_id = $_POST['dimension2_id'];
        }
        $cart->ex_rate = input_num('_ex_rate', null);
    }

    function check_item_data()
    {
        global $SysPrefs, $allow_negative_prices;

        $is_inventory_item = is_inventory_item(get_post('stock_id'));

        if ($is_inventory_item && ! $_SESSION['SysPrefs']->prefs['allow_negative_stock']) {
            $max = get_qoh_on_date(get_post('stock_id'), null, get_post('OrderDate'));
            if (! check_num('qty', 0, $max)) {
                // display_error( _("The item could not be updated because you are attempting to set the quantity ordered to less than 0, or the discount percent to more than 100."));
                display_error(_("The item could not be updated because insufficient quantity on hand."));
                set_focus('qty');
                return false;
            }
        }

        if (! get_post('stock_id_text', true)) {
            display_error(_("Item description cannot be empty."));
            set_focus('stock_id_edit');
            return false;
        } elseif (! check_num('Disc', 0, 100)) {
            display_error(_("The item could not be updated because you are attempting to set the discount percent to more than 100."));
            set_focus('Disc');
            return false;
            // } elseif (!check_num('price', 0) && (!$allow_negative_prices || $is_inventory_item)) {
        } elseif (! check_num('price', 0)) {
            display_error(_("Price for inventory item must be entered and can not be less than 0"));
            set_focus('price');
            return false;
        } elseif (isset($_POST['LineNo']) && isset($_SESSION['Items']->line_items[$_POST['LineNo']]) && ! check_num('qty', $_SESSION['Items']->line_items[$_POST['LineNo']]->qty_done)) {

            set_focus('qty');
            display_error(_("You attempting to make the quantity ordered a quantity less than has already been delivered. The quantity delivered cannot be modified retrospectively."));
            return false;
        }

        $cost_home = get_standard_cost(get_post('stock_id')); // Added 2011-03-27 Joe Hunt
        $cost = $cost_home / get_exchange_rate_from_home_currency($_SESSION['Items']->customer_currency, $_SESSION['Items']->document_date);
        if (input_num('price') < $cost) {
            $dec = user_price_dec();
            $curr = $_SESSION['Items']->customer_currency;
            $price = number_format2(input_num('price'), $dec);
            if ($cost_home == $cost)
                $std_cost = number_format2($cost_home, $dec);
            else {
                $price = $curr . " " . $price;
                $std_cost = $curr . " " . number_format2($cost, $dec);
            }
            display_warning(sprintf(_("Price %s is below Standard Cost %s"), $price, $std_cost));
        }
        return true;
    }

    /*
     * Handler action entry form
     */
    function handle_cancel_order()
    {
        global $path_to_root, $Ajax;
        if ($_SESSION['Items']->trans_type == ST_CUSTDELIVERY) {
            display_notification(_("Direct delivery entry has been cancelled as requested."), 1);
            submenu_option(_("Enter a New Sales Delivery"), "/sales/sales_order_entry.php?NewDelivery=1");
        } elseif ($_SESSION['Items']->trans_type == ST_SALESINVOICE) {
            
//             display_notification(_("Direct invoice entry has been cancelled as requested."), 1);
//             box_start();
//             row_start();
//             mt_list_link(_("Enter a New Sales Invoice"), "/sales/sales_order_entry.php?NewInvoice=1");
//             box_footer();
//             box_end();
            
        } elseif ($_SESSION['Items']->trans_type == ST_SALESQUOTE) {
            if ($_SESSION['Items']->trans_no != 0)
                delete_sales_order(key($_SESSION['Items']->trans_no), $_SESSION['Items']->trans_type);
            display_notification(_("This sales quotation has been cancelled as requested."), 1);
            submenu_option(_("Enter a New Sales Quotation"), "/sales/sales_order_entry.php?NewQuotation=Yes");
        } else { // sales order
            if ($_SESSION['Items']->trans_no != 0) {
                $order_no = key($_SESSION['Items']->trans_no);
                if (sales_order_has_deliveries($order_no)) {
                    close_sales_order($order_no);
                    display_notification(_("Undelivered part of order has been cancelled as requested."), 1);
                    submenu_option(_("Select Another Sales Order for Edition"), "/sales/inquiry/sales_orders_view.php?type=" . ST_SALESORDER);
                } else {
                    delete_sales_order(key($_SESSION['Items']->trans_no), $_SESSION['Items']->trans_type);

                    display_notification(_("This sales order has been cancelled as requested."), 1);
                    submenu_option(_("Enter a New Sales Order"), "/sales/sales_order_entry.php?NewOrder=Yes");
                }
            } else {
                processing_end();
                meta_forward($path_to_root . '/index.php', 'application=orders');
            }
        }
        $Ajax->activate('_page_body');
        processing_end();
        display_footer_exit();
    }

    function handle_delete_item($line_no)
    {
        if ($_SESSION['Items']->some_already_delivered($line_no) == 0) {
            $_SESSION['Items']->remove_from_cart($line_no);
        } else {
            display_error(_("This item cannot be deleted because some of it has already been delivered."));
        }
        $this->line_start_focus();
    }

    function handle_new_item()
    {
        if (! $this->check_item_data()) {
            return;
        }
        add_to_order_new(get_post('tax_type_id'), $_SESSION['Items'], get_post('stock_id'), input_num('qty'), input_num('price'), input_num('Disc') / 100, get_post('stock_id_text'));

        unset($_POST['_stock_id_edit'], $_POST['stock_id']);
        page_modified();

        $this->line_start_focus();
    }

    function handle_update_item()
    {
        if ($_POST['UpdateItem'] != '' && check_item_data()) {
            $_SESSION['Items']->update_cart_item_new(get_post('tax_type_id'), $_POST['LineNo'], input_num('qty'), input_num('price'), input_num('Disc') / 100, $_POST['item_description']);
        }
        page_modified();
        $this->line_start_focus();
    }
}