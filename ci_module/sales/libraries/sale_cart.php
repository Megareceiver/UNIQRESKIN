<?php

class SalesSaleCartLib
{
    var $mode = 1;
    var $cart = NULL;
    function __construct() {
        if( empty($this->cart) AND isset($_SESSION['Items']) ){
            $this->cart = $_SESSION['Items'];
        }
        $this->cart_actions();
    }
    function create_form()
    {
        if (isset($_GET['NewDelivery']) && is_numeric($_GET['NewDelivery'])) {

            $_SESSION['page_title'] = _($help_context = "Direct Sales Delivery");
            create_sale_cart(ST_CUSTDELIVERY, $_GET['NewDelivery']);

        } elseif (isset($_GET['NewInvoice']) && is_numeric($_GET['NewInvoice'])) {

            $_SESSION['page_title'] = _($help_context = "Direct Sales Invoice");
            create_sale_cart(ST_SALESINVOICE, $_GET['NewInvoice']);

        } elseif (isset($_GET['ModifyOrderNumber']) && is_numeric($_GET['ModifyOrderNumber'])) {

            $help_context = 'Modifying Sales Order';
            $_SESSION['page_title'] = sprintf(_("Modifying Sales Order # %d"), $_GET['ModifyOrderNumber']);
            create_sale_cart(ST_SALESORDER, $_GET['ModifyOrderNumber']);

        } elseif (isset($_GET['ModifyQuotationNumber']) && is_numeric($_GET['ModifyQuotationNumber'])) {

            $help_context = 'Modifying Sales Quotation';
            $_SESSION['page_title'] = sprintf(_("Modifying Sales Quotation # %d"), $_GET['ModifyQuotationNumber']);
            create_sale_cart(ST_SALESQUOTE, $_GET['ModifyQuotationNumber']);

        } elseif (isset($_GET['NewOrder'])) {

            $_SESSION['page_title'] = _($help_context = "New Sales Order Entry");
            create_sale_cart(ST_SALESORDER, 0);

        } elseif (isset($_GET['NewQuotation'])) {

            $_SESSION['page_title'] = _($help_context = "New Sales Quotation Entry");
            create_sale_cart(ST_SALESQUOTE, 0);

        } elseif (isset($_GET['NewQuoteToSalesOrder'])) {

            $_SESSION['page_title'] = _($help_context = "Sales Order Entry");
            create_sale_cart(ST_SALESQUOTE, $_GET['NewQuoteToSalesOrder']);
        } else {
            if (input_get( 'reinvoice' )) {
                // set_time_limit(0);
                $cart = new Cart(ST_SALESINVOICE, array(
                    $ci->input->get('reinvoice')
                ));
                $sale_model = $ci->model('sale', true);

                $invoice_no = $sale_model->write_sales_invoice($cart, true);
                // die('go here');
                redirect('gl/view/gl_trans_view.php?type_id=10&trans_no=' . $invoice_no);
                // die('go here to repost ivoice no='.$invoice_no);
            }
        }

    }

    function check_edit_conflicts($cartname = 'Items')
    {
        global $Ajax, $no_check_edit_conflicts;

        if ((! isset($no_check_edit_conflicts) || $no_check_edit_conflicts == 0) && get_post('cart_id') && $_POST['cart_id'] != $_SESSION[$cartname]->cart_id) {
            display_error(_('This edit session has been abandoned by opening sales document in another browser tab. You cannot edit more than one sales document at once.'));
            $Ajax->activate('_page_body');
            display_footer_exit();
        }
    }

    function display_order_header(&$order, $editable=FALSE)
    {
        global $Ajax, $SysPrefs, $idate;

        $customer_error = "";
        $change_prices = 0;

        row_start();
        col_start(12,'col-md-5');
        if( !isMobile() ){
            bootstrap_set_label_column(4);
        }
        
        if (isset($order) && ! $editable) {
            // can't change the customer/branch if items already received on this order
            label_row(null, $order->customer_name . " - " . $order->deliver_to);
            hidden('customer_id', $order->customer_id);
            hidden('branch_id', $order->Branch);
            hidden('sales_type', $order->sales_type);
            if ($order->trans_type != ST_SALESORDER && $order->trans_type != ST_SALESQUOTE) {
                hidden('dimension_id', $order->dimension_id); // 2008-11-12 Joe Hunt
                hidden('dimension2_id', $order->dimension2_id);
            }
        } else {
            customer_list_bootstrap(_("Customer"), 'customer_id', null, false, true, false, true);

            if ($order->customer_id != get_post('customer_id', - 1)) {
                // customer has changed
                $Ajax->activate('branch_id');
            }
            customer_branches_bootstrap(_("Branch"), $_POST['customer_id'], 'branch_id', null, false, true, true, true);
//             customer_branches_list_row(_("Branch:"), $_POST['customer_id'], 'branch_id', null, false, true, true, true);

            if (($order->customer_id != get_post('customer_id', - 1)) || ($order->Branch != get_post('branch_id', - 1)) || list_updated('customer_id')) {

                if (! isset($_POST['branch_id']) || $_POST['branch_id'] == "") {
                    // ignore errors on customer search box call
                    if ($_POST['customer_id'] == '')
                        $customer_error = _("No customer found for entered text.");
                    else
                        $customer_error = _("The selected customer does not have any branches. Please create at least one branch.");
                    unset($_POST['branch_id']);
                    $order->Branch = 0;
                } else {

                    $old_order = (PHP_VERSION < 5) ? $order : clone ($order);

                    $customer_error = $this->get_customer_details_to_order($order, $_POST['customer_id'], $_POST['branch_id']);
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
        input_ref('Reference', 'ref', null, _('Reference number unique for this document type'));
        // ref_row(_("Reference") . ':', 'ref', _('Reference number unique for this document type'), null, '');

        // if( defined('COUNTRY') && COUNTRY==65 ){
        // $ci->finput->text('Customer Reference','customer_ref',null,'row_echo');
        // }
        hidden('customer_ref');
        $ref2_changed = input_post("cust_ref2");
        if( $ref2_changed ){
            $order->cust_ref = $ref2_changed;
        }
        $ref_changed = input_post("cust_ref");
        if( $ref_changed ){
            $order->cust_ref = $ref_changed;
        }
 
        if( in_ajax() AND ($ref_changed OR $ref2_changed)){
            $_POST['cust_ref'] = $_POST['cust_ref2'] =  $order->cust_ref;
            $Ajax->activate('_page_body');
        }

        if (! $order->payment_terms['cash_sale']) {
            input_ref(_("Customer Reference"), 'cust_ref2', $order->cust_ref, _('Customer reference number for this order (if any)'));
            locations_bootstrap(_("Deliver from Location"), 'Location2', null, false, true);
        }

        col_start(12,'col-md-4');
        if( !isMobile() ){
            bootstrap_set_label_column(3);
        }


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
            $sale_payment_category = (in_array($order->trans_type, array(
                ST_SALESQUOTE,
                ST_SALESORDER
            )) ? PM_ANY : $paymcat);
            sale_payments_bootstrap(_('Payment'), 'payment', $sale_payment_category);
        } else {
            input_label(_('Payment'), null, $order->payment_terms['terms']);
        }

        if ($editable) {
            sales_types_bootstrap(_("Price List"), 'sales_type', null, true);
        } else {
            input_label(_("Price List "), NULL, $order->sales_type_name);
        }

        if ($order->sales_type != $_POST['sales_type']) {
            $myrow = get_sales_type($_POST['sales_type']);
            $order->set_sales_type($myrow['id'], $myrow['sales_type'], $myrow['tax_included'], $myrow['factor']);
            $Ajax->activate('sales_type');
            $change_prices = 1;
        }
        /*
         * END column 3
         */

        if ($editable) {
            if (! isset($_POST['OrderDate']) || $_POST['OrderDate'] == "")
                $_POST['OrderDate'] = $order->document_date;

            input_date_bootstrap('Date', 'OrderDate');

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
                if ($dim > 0)
                    dimensions_bootstrap(_("Dimension"), 'dimension_id', null, true, ' ', false, 1, false);
                else
                    hidden('dimension_id', 0);
                if ($dim > 1)
                    dimensions_bootstrap(_("Dimension") . " 2:", 'dimension2_id', null, true, ' ', false, 2, false);
                else
                    hidden('dimension2_id', 0);
            }
        } else {
            input_label('Date', null, $order->document_date);
            hidden('OrderDate', $order->document_date);
        }

        col_start(12,'col-md-3');
        if( !isMobile() ){
            bootstrap_set_label_column(7);
        }


        if (! is_company_currency($order->customer_currency) && in_array($order->trans_type, array(
            ST_SALESINVOICE,
            ST_CUSTDELIVERY
        ))) {
            label_row(_("Customer Currency:"), $order->customer_currency);
            exchange_rate_display(get_company_currency(), $order->customer_currency, ($editable ? $_POST['OrderDate'] : $order->document_date));
        }
        customer_credit_bootstrap($_POST['customer_id'], $order->credit);
        input_label(_("Customer Discount"), null, ($order->default_discount * 100) . "%");
        bootstrap_set_label_column(0);
        row_end();

        if ($change_prices != 0) {
            foreach ($order->line_items as $line_no => $item) {
                $line = &$order->line_items[$line_no];
                $line->price = get_kit_price($line->stock_id, $order->customer_currency, $order->sales_type, $order->price_factor, get_post('OrderDate'));
                // $line->discount_percent = $order->default_discount;
            }
            $Ajax->activate('items_table');
        }

        return $customer_error;
    }


 function display_order_header_col_md_12(&$order, $editable=FALSE)
    {
        global $Ajax, $SysPrefs, $idate;

        $customer_error = "";
        $change_prices = 0;

        row_start();
        col_start(12,'col-md-12');
        if( !isMobile() ){
            bootstrap_set_label_column(4);
        }
        
        if (isset($order) && ! $editable) {
            // can't change the customer/branch if items already received on this order
            label_row(null, $order->customer_name . " - " . $order->deliver_to);
            hidden('customer_id', $order->customer_id);
            hidden('branch_id', $order->Branch);
            hidden('sales_type', $order->sales_type);
            if ($order->trans_type != ST_SALESORDER && $order->trans_type != ST_SALESQUOTE) {
                hidden('dimension_id', $order->dimension_id); // 2008-11-12 Joe Hunt
                hidden('dimension2_id', $order->dimension2_id);
            }
        } else {
            customer_list_bootstrap(_("Customer"), 'customer_id', null, false, true, false, true);

            if ($order->customer_id != get_post('customer_id', - 1)) {
                // customer has changed
                $Ajax->activate('branch_id');
            }
            customer_branches_bootstrap(_("Branch"), $_POST['customer_id'], 'branch_id', null, false, true, true, true);
//             customer_branches_list_row(_("Branch:"), $_POST['customer_id'], 'branch_id', null, false, true, true, true);

            if (($order->customer_id != get_post('customer_id', - 1)) || ($order->Branch != get_post('branch_id', - 1)) || list_updated('customer_id')) {

                if (! isset($_POST['branch_id']) || $_POST['branch_id'] == "") {
                    // ignore errors on customer search box call
                    if ($_POST['customer_id'] == '')
                        $customer_error = _("No customer found for entered text.");
                    else
                        $customer_error = _("The selected customer does not have any branches. Please create at least one branch.");
                    unset($_POST['branch_id']);
                    $order->Branch = 0;
                } else {

                    $old_order = (PHP_VERSION < 5) ? $order : clone ($order);

                    $customer_error = $this->get_customer_details_to_order($order, $_POST['customer_id'], $_POST['branch_id']);
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
        input_ref('Reference', 'ref', null, _('Reference number unique for this document type'));
        // ref_row(_("Reference") . ':', 'ref', _('Reference number unique for this document type'), null, '');

        // if( defined('COUNTRY') && COUNTRY==65 ){
        // $ci->finput->text('Customer Reference','customer_ref',null,'row_echo');
        // }
        hidden('customer_ref');
        $ref2_changed = input_post("cust_ref2");
        if( $ref2_changed ){
            $order->cust_ref = $ref2_changed;
        }
        $ref_changed = input_post("cust_ref");
        if( $ref_changed ){
            $order->cust_ref = $ref_changed;
        }
 
        if( in_ajax() AND ($ref_changed OR $ref2_changed)){
            $_POST['cust_ref'] = $_POST['cust_ref2'] =  $order->cust_ref;
            $Ajax->activate('_page_body');
        }

        if (! $order->payment_terms['cash_sale']) {
            input_ref(_("Customer Reference"), 'cust_ref2', $order->cust_ref, _('Customer reference number for this order (if any)'));
            locations_bootstrap(_("Deliver from Location"), 'Location2', null, false, true);
        }

        col_start(12,'col-md-12');
        if( !isMobile() ){
            bootstrap_set_label_column(3);
        }


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
            $sale_payment_category = (in_array($order->trans_type, array(
                ST_SALESQUOTE,
                ST_SALESORDER
            )) ? PM_ANY : $paymcat);
            sale_payments_bootstrap(_('Payment'), 'payment', $sale_payment_category);
        } else {
            input_label(_('Payment'), null, $order->payment_terms['terms']);
        }

        if ($editable) {
            sales_types_bootstrap(_("Price List"), 'sales_type', null, true);
        } else {
            input_label(_("Price List "), NULL, $order->sales_type_name);
        }

        if ($order->sales_type != $_POST['sales_type']) {
            $myrow = get_sales_type($_POST['sales_type']);
            $order->set_sales_type($myrow['id'], $myrow['sales_type'], $myrow['tax_included'], $myrow['factor']);
            $Ajax->activate('sales_type');
            $change_prices = 1;
        }
        /*
         * END column 3
         */

        if ($editable) {
            if (! isset($_POST['OrderDate']) || $_POST['OrderDate'] == "")
                $_POST['OrderDate'] = $order->document_date;

            input_date_bootstrap('Date', 'OrderDate');

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
                if ($dim > 0)
                    dimensions_bootstrap(_("Dimension"), 'dimension_id', null, true, ' ', false, 1, false);
                else
                    hidden('dimension_id', 0);
                if ($dim > 1)
                    dimensions_bootstrap(_("Dimension") . " 2:", 'dimension2_id', null, true, ' ', false, 2, false);
                else
                    hidden('dimension2_id', 0);
            }
        } else {
            input_label('Date', null, $order->document_date);
            hidden('OrderDate', $order->document_date);
        }

        col_start(12,'col-md-3');
        if( !isMobile() ){
            bootstrap_set_label_column(7);
        }


        if (! is_company_currency($order->customer_currency) && in_array($order->trans_type, array(
            ST_SALESINVOICE,
            ST_CUSTDELIVERY
        ))) {
            label_row(_("Customer Currency:"), $order->customer_currency);
            exchange_rate_display(get_company_currency(), $order->customer_currency, ($editable ? $_POST['OrderDate'] : $order->document_date));
        }
        customer_credit_bootstrap($_POST['customer_id'], $order->credit);
        input_label(_("Customer Discount"), null, ($order->default_discount * 100) . "%");
        bootstrap_set_label_column(0);
        row_end();

        if ($change_prices != 0) {
            foreach ($order->line_items as $line_no => $item) {
                $line = &$order->line_items[$line_no];
                $line->price = get_kit_price($line->stock_id, $order->customer_currency, $order->sales_type, $order->price_factor, get_post('OrderDate'));
                // $line->discount_percent = $order->default_discount;
            }
            $Ajax->activate('items_table');
        }

        return $customer_error;
    }




    function display_order_summary(&$order, $editable_items = false)
    {
        div_start('items_table',$trigger = null, $non_ajax = false, $attributes = ' class="pb-4" ');
        start_table(TABLESTYLE);
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

        // if (count($order->line_items))
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
                    tbl_edit("Edit$line_no");
                    tbl_remove("Delete$line_no");
                }
                end_row();
            } else {
                $this->order_item_controls($order, $k, $line_no);
            }

            $total += $line_total;
        }

        if ($id == - 1 && $editable_items)
            $this->order_item_controls($order, $k);

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
//         start_row();

        // label_cells();
        // label_cells();
//         end_row();

        end_table();
        if ($low_stock)
            display_note(_("Marked items have insufficient quantities in stock as on day of delivery."), 0, 1, "class='stockmankofg'");

        div_end();

        box_footer_start($class = NULL, $attributes = NULL,$show_back=false);
        submit('items_update', _("Update"),true, _("Refresh"), true,'save');
        box_footer_end();
    }

    function display_delivery_details(&$order)
    {
        global $Ajax;
        if ( $order->payment_terms['cash_sale'] ){
            box_start( _('Cash payment') );
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
            box_start($title);
        }
        div_start('delivery',$trigger = null, $non_ajax = false, $attributes = 'class="pb-2" ');

        row_start('justify-content-center');


        if ( $order->payment_terms['cash_sale'] ) { // Direct payment sale
            $Ajax->activate('items_table');
            col_start(8,"col-md-8 col-md-offset-2");
            locations_bootstrap(_("Deliver from Location"), 'Location', null, false, true);
            if (list_updated('Location'))
                $Ajax->activate('items_table');

            $cash_sales_invoice = get_gl_account(get_company_pref('cash_sales_invoice'));
            input_label(_("Cash account"), null, $cash_sales_invoice['account_name']);
            input_textarea(_("Comments:"), "Comments", $order->Comments);
            hidden('delivery_date', $order->due_date);
        } else {
            
            col_start(12,'col-md-6');
            if( !isMobile() ){
                bootstrap_set_label_column(4);
            }
            locations_bootstrap( _("Deliver from Location"), 'Location', null, false, true );
//             locations_list_row(_("Deliver from Location:"), 'Location', null, false, true);

            if (list_updated('Location'))
                $Ajax->activate('items_table');

            input_date_bootstrap($delname, 'delivery_date',null);
//             date_row($delname, 'delivery_date', $order->trans_type == ST_SALESORDER ? _('Enter requested day of delivery') : $order->trans_type == ST_SALESQUOTE ? _('Enter Valid until Date') : '');
            input_text(_("Deliver To"), 'deliver_to',$order->deliver_to);
//             text_row(_("Deliver To:"), 'deliver_to', $order->deliver_to, 40, 40, _('Additional identifier for delivery e.g. name of receiving person'));

            input_textarea( _("Address"), 'delivery_address', $order->delivery_address );

            col_start(12,'col-md-6');
            input_text( _("Contact Phone Number"), 'phone', $order->phone );
            input_ref(_("Customer Reference"), 'cust_ref', $order->cust_ref, _('Customer reference number for this order (if any)'));
            input_textarea( _("Comments"), "Comments", $order->Comments );
            shippers_bootstrap( _("Shipping Company"), 'ship_via', $order->ship_via);

        }
        row_end();
        div_end();
    }

     function display_delivery_details_offset_2(&$order)
    {
        global $Ajax;
        if ( $order->payment_terms['cash_sale'] ){
            box_start( _('Cash payment') );
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
            box_start($title);
        }
        div_start('delivery',$trigger = null, $non_ajax = false, $attributes = 'class="pb-2" ');

        row_start('justify-content-center');


        if ( $order->payment_terms['cash_sale'] ) { // Direct payment sale
            $Ajax->activate('items_table');
            col_start(12,'col-md-offset-2 col-md-8');
            locations_bootstrap(_("Deliver from Location"), 'Location', null, false, true);
            if (list_updated('Location'))
                $Ajax->activate('items_table');

            $cash_sales_invoice = get_gl_account(get_company_pref('cash_sales_invoice'));
            input_label(_("Cash account"), null, $cash_sales_invoice['account_name']);
            input_textarea(_("Comments:"), "Comments", $order->Comments);
            hidden('delivery_date', $order->due_date);
        } else {
            
            col_start(12,'col-md-6');
            if( !isMobile() ){
                bootstrap_set_label_column(4);
            }
            locations_bootstrap( _("Deliver from Location"), 'Location', null, false, true );
//             locations_list_row(_("Deliver from Location:"), 'Location', null, false, true);

            if (list_updated('Location'))
                $Ajax->activate('items_table');

            input_date_bootstrap($delname, 'delivery_date',null);
//             date_row($delname, 'delivery_date', $order->trans_type == ST_SALESORDER ? _('Enter requested day of delivery') : $order->trans_type == ST_SALESQUOTE ? _('Enter Valid until Date') : '');
            input_text(_("Deliver To"), 'deliver_to',$order->deliver_to);
//             text_row(_("Deliver To:"), 'deliver_to', $order->deliver_to, 40, 40, _('Additional identifier for delivery e.g. name of receiving person'));

            input_textarea( _("Address"), 'delivery_address', $order->delivery_address );

            col_start(12,'col-md-6');
            input_text( _("Contact Phone Number"), 'phone', $order->phone );
            input_ref(_("Customer Reference"), 'cust_ref', $order->cust_ref, _('Customer reference number for this order (if any)'));
            input_textarea( _("Comments"), "Comments", $order->Comments );
            shippers_bootstrap( _("Shipping Company"), 'ship_via', $order->ship_via);

        }
        row_end();
        div_end();
    }

    private function order_item_controls(&$order, &$rowcounter, $line_no = -1)
    {
        global $Ajax;

        alt_table_row_color($rowcounter);

        $id = find_submit('Edit');
        if ($line_no != - 1 && $line_no == $id) { // edit old line
            $_POST['stock_id'] = $order->line_items[$id]->stock_id;
            $dec = get_qty_dec($_POST['stock_id']);
            $_POST['qty'] = number_format2($order->line_items[$id]->qty_dispatched, $dec);
            $_POST['price'] = price_format($order->line_items[$id]->price);
            $_POST['Disc'] = percent_format($order->line_items[$id]->discount_percent * 100);
            $units = $order->line_items[$id]->units;
            $_POST['item_description'] = $order->line_items[$id]->item_description;
            hidden('stock_id', $_POST['stock_id']);
            label_cell($_POST['stock_id']);
            if ($order->line_items[$id]->descr_editable)
                text_cells(null, 'item_description', null, 45, 150);
            else {
                hidden('item_description', $_POST['item_description']);
                label_cell($_POST['item_description']);
            }
            // } else {
            // sales_items_list_cells(null,'item_description', null, false, true);
            // }
            // label_cell($order->line_items[$line_no]->item_description, "nowrap");
            $Ajax->activate('items_table');
        } else { // prepare new line
            sales_items_list_cells(null, 'stock_id', null, false, true, true);

            // TUANVT4
            if (list_updated('stock_id')) {
                $Ajax->activate('price');
                $Ajax->activate('units');
                $Ajax->activate('qty');
                $Ajax->activate('line_total');
                $Ajax->activate('tax_type_id');
            }

            if ($order->customer_tax_id < 0) {
                $tax_type_info = get_tax_type_by_item(get_post('stock_id'));
                $tax_id_tmp = $tax_type_info["id"];
                // display_error($tax_id_tmp);
                $_POST['tax_type_id'] = $tax_id_tmp;
            }

            $item_info = get_item_edit_info($_POST['stock_id']);

            $units = $item_info["units"];
            $dec = $item_info['decimals'];
            $_POST['qty'] = number_format2(1, $dec);
            $price = get_kit_price($_POST['stock_id'], $order->customer_currency, $order->sales_type, $order->price_factor, get_post('OrderDate'));
            $_POST['price'] = price_format($price);
            // default to the customer's discount %
            $_POST['Disc'] = percent_format($order->default_discount * 100);
        }

        item_tax_types_list_cells(null, 'tax_type_id', NULL, 2);

        qty_cells(null, 'qty', $_POST['qty'], null, null, $dec);

        if ($order->trans_no != 0) {
            qty_cell($line_no == - 1 ? 0 : $order->line_items[$line_no]->qty_done, false, $dec);
        }

        label_cell($units, '', 'units');

        amount_cells(null, 'price');

        small_amount_cells(null, 'Disc', percent_format($_POST['Disc']), null, null, user_percent_dec());

        $line_total = input_num('qty') * input_num('price') * (1 - input_num('Disc') / 100);

        label_cell(number_format2($line_total, user_amount_dec()), "nowrap align=right ", 'line_total');

        if ($id != - 1) {
            tbl_update("UpdateItem");
            tbl_cancel("CancelItemChanges");
            hidden('LineNo', $line_no);
            set_focus('qty');
        } else {
            tbl_add("AddItem");
            label_cell(NULL);
        }

        end_row();
    }

    private function get_customer_details_to_order(&$order, $customer_id, $branch_id)
    {
        global $SysPrefs;

        $ret_error = "";

        $myrow = get_customer_to_order($customer_id);

        $name = $myrow['name'];

        // TUANVT2
        $order->customer_tax_id = $myrow['customer_tax_id'];

        // TUANVT4
        if ($order->customer_tax_id > 0) {
            $_POST['tax_type_id'] = $order->customer_tax_id;
        }

        if ($myrow['dissallow_invoices'] == 1)
            $ret_error = _("The selected customer account is currently on hold. Please contact the credit control personnel to discuss.");

        $deliver = $myrow['address']; // in case no branch address use company address

        $order->set_customer($customer_id, $name, $myrow['curr_code'], $myrow['discount'], $myrow['payment_terms'], $myrow['pymt_discount']);

        // the sales type determines the price list to be used by default
        $order->set_sales_type($myrow['salestype'], $myrow['sales_type'], $myrow['tax_included'], $myrow['factor']);

        $order->credit = $myrow['cur_credit'];

        if ($order->trans_type != ST_SALESORDER && $order->trans_type != ST_SALESQUOTE) {
            $order->dimension_id = $myrow['dimension_id'];
            $order->dimension2_id = $myrow['dimension2_id'];
        }
        $result = get_branch_to_order($customer_id, $branch_id);

        if (db_num_rows($result) == 0) {
            return _("The selected customer and branch are not valid, or the customer does not have any branches.");
        }

        $myrow = db_fetch($result);

        // FIX - implement editable contact selector in sales order
        $contact = get_branch_contacts($branch_id, 'order', $customer_id);
        $order->set_branch($branch_id, $myrow["tax_group_id"], $myrow["tax_group_name"], @$contact["phone"], @$contact["email"]);

        $address = trim($myrow["br_post_address"]) != '' ? $myrow["br_post_address"] : (trim($myrow["br_address"]) != '' ? $myrow["br_address"] : $deliver);

        $order->set_delivery($myrow["default_ship_via"], $myrow["br_name"], $address);
        if ($order->trans_type == ST_SALESINVOICE) {
            $order->due_date = get_invoice_duedate($order->payment, $order->document_date);
        } elseif ($order->trans_type == ST_SALESORDER)
            $order->due_date = add_days($order->document_date, $SysPrefs->default_delivery_required_by());
        if ($order->payment_terms['cash_sale']) {
            $order->set_location($order->pos["pos_location"], $order->pos["location_name"]);
        } else
            $order->set_location($myrow["default_location"], $myrow["location_name"]);

        return $ret_error;
    }


    function display_gl_items($editable_items = false){
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

        $dim = get_company_pref('use_dimension');
        $id = find_submit('EditGLCode');


        box_start("GL Items for Invoice");
        div_start('gl_items_table');
        start_table(TABLESTYLE);
        table_header($th);
        $total_gl_value = $total = 0;
        $k = 0;
        if (count($this->cart->gl_items) > 0) {
            foreach ($this->cart->gl_items as $line_num => $entered_gl) {

                if ( !$editable_items || $id != $line_num) {
                    alt_table_row_color($k);
                    if ($this->mode == 3)
                        $entered_gl->amount = - $entered_gl->amount;

                    label_cell($entered_gl->gl_code);
                    $gl_acc = get_gl_account($entered_gl->gl_code,true);
                    label_cell( is_object($gl_acc) ? $gl_acc->account_name : NULL );
                    item_tax_types_list_cells(null,'gl_tax_id',$entered_gl->tax_id,2);
                    //                 echo $ci->finput->inputtaxes(null, 'tax_id', $entered_gl_code->supplier_tax_id, 2, 'in_row_title');
                    if ($dim >= 1)
                        label_cell(get_dimension_string($entered_gl->gl_dim, true));
                    if ($dim > 1)
                        label_cell(get_dimension_string($entered_gl->gl_dim2, true));

                    amount_cell($entered_gl->amount, true);
                    label_cell($entered_gl->memo_);

                    if ($this->mode == 1) {
                        //                     delete_button_cell("Delete2" . $entered_gl_code->Counter, _("Delete"), _('Remove line from document'));
                        //                     icon_submit_cells('DeleteGLCode', $line_num, 'danger', 'icon-trash', true ,_("Remove line from document"));
                        //                     label_cell("");
                        tbl_edit("EditGLCode$line_num");
                        tbl_remove("DeleteGLCode$line_num");
                    }
                    end_row();
                } else {
                    $this->gl_item_edit($line_num);
                }


                // ///////// 2009-08-18 Joe Hunt
                if ($this->mode > 1) {
                    if ($this->cart->tax_included || ! is_tax_account($entered_gl->gl_code))
                        $total_gl_value += $entered_gl->amount;
                } else
                    $total_gl_value += $entered_gl->amount;

                $total += $entered_gl->amount;

            }
        }
        if ($this->mode == 1 AND $id == - 1 ){
            $this->gl_item_edit();
        }

        end_table();
//         if ($low_stock)
//             display_note(_("Marked items have insufficient quantities in stock as on day of delivery."), 0, 1, "class='stockmankofg'");

        div_end();
        box_footer_start($class = NULL, $attributes = NULL,$show_back=false);
        submit('gl_update', _("Update"),true, _("Refresh"), true,'save');
        box_footer_end();
        box_end();
    }

    private function gl_item_edit($line_no=0)
    {
        $id = find_submit('EditGLCode');
        $edit_line = false;
        if ($line_no != - 1 AND $line_no == $id) { // edit old line
            $gl_line_item = $this->cart->gl_items[$line_no];
            $_POST['gl_code'] = $gl_line_item->gl_code;
            $_POST['gl_tax_id'] = $gl_line_item->tax_id;
            $_POST['gl_amount'] = $gl_line_item->amount;
            $_POST['gl_memo_'] = $gl_line_item->memo_;
            $edit_line = true;
        }

        alt_table_row_color($k);
        gl_all_accounts_list_cells('gl_code', null, true, true);
        item_tax_types_list_cells(null, 'gl_tax_id', null, 2, 'in_row_input');
        $dim = get_company_pref('use_dimension');

        if ($dim >= 1)
            dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
        if ($dim > 1)
            dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);

        amount_cells(null, 'gl_amount');

        if ($dim < 1)
            text_cells_ex(null, 'gl_memo_', 35, 50, null, null, null, hidden('dimension_id', 0, false) . hidden('dimension2_id', 0, false));
        else
            if ($dim < 2)
                text_cells_ex(null, 'gl_memo_', 35, 50, null, null, null, hidden('dimension2_id', 0, false));
            else
                text_cells_ex(null, 'gl_memo_', 35, 50, null, null, null);

        if( $edit_line ){
            tbl_update("UpdateGLCode$line_no",$id);
            tbl_cancel('CancelUpdateGL', _("Reset"));
        } else {
            tbl_add('AddGLCodeToTrans', _("Add"));
            tbl_cancel('ClearFieldsGL', _("Reset"));
//             icon_submit_cells('AddGLCodeToTrans', _("Add"), 'success', 'fa-plus', true, _('Add GL Line'));
//             icon_submit_cells('ClearFields', _("Reset"), 'warning', 'fa-refresh', true ,_("Clear all GL entry fields"));
        }

        end_row();
    }

    private function cart_actions(){
        if (isset($_POST['CancelOrder']))
            cancel_sale_cart();

        /*
         * product item line actions
         */

        if (isset($_POST['items_update'])) {
            //copy_to_cart();
            global $Ajax;
            copy_to_cart_sale();
            $Ajax->activate('items_table');
        }

        $id = find_submit('Delete');

        if ($id != - 1)
            delete_item_sale_cart($id);

        if (isset($_POST['UpdateItem']))
            update_item_sale_cart();

        if (isset($_POST['AddItem'])) {
            new_item_sale_cart();
        }

        if (isset($_POST['CancelItemChanges'])) {
            line_start_focus_sale_cart();
        }

        /*
         * gl item actions
         */
        if (isset($_POST['AddGLCodeToTrans'])) {

            $add = $this->cart->add_gl_line(count($this->cart->gl_items), input_post("gl_code"),input_post("gl_tax_id"), input_num('gl_amount'),input_post("gl_memo_"));
            if ( $add ){
                unset($_POST['gl_code'], $_POST['_gl_code_edit'],$_POST['gl_tax_id'],$_POST['gl_amount'],$_POST['gl_memo_']);
                page_modified();
                line_gl_sale_cart_focus();
            }
        }
        $gl_edit_lineno = find_submit('UpdateGLCode');
        if( in_ajax() AND $gl_edit_lineno > 0 ){
            $this->cart->update_gl_line($gl_edit_lineno, input_post("gl_code"),input_post("gl_tax_id"), input_num('gl_amount'),input_post("gl_memo_"));
            unset($_POST['gl_code'], $_POST['_gl_code_edit'],$_POST['gl_tax_id'],$_POST['gl_amount'],$_POST['gl_memo_']);
            line_gl_sale_cart_focus();
        }
        if( input_post('ClearFieldsGL')){
            unset($_POST['gl_code'], $_POST['_gl_code_edit'],$_POST['gl_tax_id'],$_POST['gl_amount'],$_POST['gl_memo_']);
            line_gl_sale_cart_focus();
        }
        $id = find_submit('DeleteGLCode');
        if ( $id != -1 ) {
            $this->cart->remove_gl_from_cart($id);
            line_gl_sale_cart_focus();
        }
        if ( find_submit('EditGLCode') != -1 ) {
            line_gl_sale_cart_focus();
        }
    }
}