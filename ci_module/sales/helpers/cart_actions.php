<?php

// function handle_cancel_order() {
function cancel_sale_cart ()
{
    global $path_to_root, $Ajax;
    box_start();
    row_start();
    col_start(12);
    mt_list_start('Actions', '', 'blue');
    
    if ($_SESSION['Items']->trans_type == ST_CUSTDELIVERY) {
        display_notification( _("Direct delivery entry has been cancelled as requested."), 1);
        mt_list_link(_("Enter a New Sales Delivery"), "/sales/sales_order_entry.php?NewDelivery=1");
    } elseif ($_SESSION['Items']->trans_type == ST_SALESINVOICE) {
        display_notification(_("Direct invoice entry has been cancelled as requested."));
        mt_list_link(_("Enter a New Sales Invoice"), "/sales/sales_order_entry.php?NewInvoice=1");
    } elseif ($_SESSION['Items']->trans_type == ST_SALESQUOTE) {
        if ($_SESSION['Items']->trans_no != 0)
            delete_sales_order(key($_SESSION['Items']->trans_no), $_SESSION['Items']->trans_type);
        display_notification( _("This sales quotation has been cancelled as requested."), 1);
//         submenu_option(_("Enter a New Sales Quotation"), "/sales/sales_order_entry.php?NewQuotation=Yes");
        mt_list_link(_("Enter a New Sales Quotation"), "/sales/sales_order_entry.php?NewQuotation=Yes");
    } else { 
        // sales order
        if ($_SESSION['Items']->trans_no != 0) {
            $order_no = key($_SESSION['Items']->trans_no);
            if (sales_order_has_deliveries($order_no)) {
                close_sales_order($order_no);
                display_notification( _( "Undelivered part of order has been cancelled as requested."));
                mt_list_link(_("Select Another Sales Order for Edition"), "/sales/inquiry/sales_orders_view.php?type=" . ST_SALESORDER);
            } else {
                delete_sales_order(key($_SESSION['Items']->trans_no), 
                        $_SESSION['Items']->trans_type);
                
                display_notification( _("This sales order has been cancelled as requested."));
                mt_list_link(_("Enter a New Sales Order"), "/sales/sales_order_entry.php?NewOrder=Yes");
            }
        } else {
            processing_end();
            meta_forward($path_to_root . '/index.php', 'application=orders');
        }
    }
    row_end();
    box_footer();
    box_end();
    
    $Ajax->activate('_page_body');
    processing_end();
    display_footer_exit();
}

// function handle_delete_item($line_no) {
function delete_item_sale_cart ($line_no)
{
    if ($_SESSION['Items']->some_already_delivered($line_no) == 0) {
        $_SESSION['Items']->remove_from_cart($line_no);
    } else {
        display_error(
                _(
                        "This item cannot be deleted because some of it has already been delivered."));
    }
    line_start_focus_sale_cart();
}

// function handle_update_item() {
function update_item_sale_cart ()
{
    if ($_POST['UpdateItem'] != '' && check_item_data()) {
        $_SESSION['Items']->update_cart_item_new(get_post('tax_type_id'), 
                $_POST['LineNo'], input_num('qty'), input_num('price'), 
                input_num('Disc') / 100, $_POST['item_description']);
    }
    page_modified();
    line_start_focus_sale_cart();
}

// function handle_new_item()
function new_item_sale_cart ()
{
    if (! check_item_data()) {
        return;
    }
    add_to_sale_order(get_post('tax_type_id'), $_SESSION['Items'], 
            get_post('stock_id'), input_num('qty'), input_num('price'), 
            input_num('Disc') / 100, get_post('stock_id_text'));
    
    unset($_POST['_stock_id_edit'], $_POST['stock_id']);
    page_modified();
    
    line_start_focus_sale_cart();
}

function line_start_focus_sale_cart ()
{
    global $Ajax;
    $Ajax->activate('items_table');
    set_focus('_stock_id_edit');
}

function add_to_sale_order ($tax_type_id, &$order, $new_item, $new_item_qty, 
        $price, $discount, $description = '')
{
    // calculate item price to sum of kit element prices factor for
    // value distribution over all exploded kit items
    $std_price = get_kit_price($new_item, $order->customer_currency, 
            $order->sales_type, $order->price_factor, get_post('OrderDate'), 
            true);
    
    if ($std_price == 0)
        $price_factor = 0;
    else
        $price_factor = $price / $std_price;
    
    $kit = get_item_kit($new_item);
    $item_num = db_num_rows($kit);
    
    while ($item = db_fetch($kit)) {
        $std_price = get_kit_price($item['stock_id'], $order->customer_currency, 
                $order->sales_type, $order->price_factor, get_post('OrderDate'), 
                true);
        
        // rounding differences are included in last price item in kit
        $item_num --;
        if ($item_num) {
            $price -= $item['quantity'] * $std_price * $price_factor;
            $item_price = $std_price * $price_factor;
        } else {
            if ($item['quantity'])
                $price = $price / $item['quantity'];
            $item_price = $price;
        }
        $item_price = round($item_price, user_price_dec());
        
        if (! $item['is_foreign'] && $item['item_code'] != $item['stock_id']) { // this
                                                                                // is
                                                                                // sales
                                                                                // kit
                                                                                // -
                                                                                // recurse
            add_to_order_new($tax_type_id, $order, $item['stock_id'], 
                    $new_item_qty * $item['quantity'], $item_price, $discount);
        } else { // stock item record eventually with foreign code
                 
            // check duplicate stock item
            foreach ($order->line_items as $order_item) {
                if (strcasecmp($order_item->stock_id, $item['stock_id']) == 0) {
                    display_warning(
                            _("For Part :") . $item['stock_id'] . " " .
                                     _(
                                            "This item is already on this document. You have been warned."));
                    break;
                }
            }
            // $order->add_to_cart_new($tax_type_id,count($order->line_items),$item['stock_id'],$new_item_qty*$item['quantity'],
            // $item_price, $discount, 0,0, $description);
            $order->add_to_cart_new($tax_type_id, count($order->line_items), 
                    $item['stock_id'], $new_item_qty, $item_price, $discount, 0, 
                    0, $description);
        }
    }
}
if (! function_exists('check_item_data')) {

    function check_item_data ()
    {
        global $SysPrefs, $allow_negative_prices;
        
        $is_inventory_item = is_inventory_item(get_post('stock_id'));
        
        if ($is_inventory_item &&
                 ! $_SESSION['SysPrefs']->prefs['allow_negative_stock']) {
            $max = get_qoh_on_date(get_post('stock_id'), null, 
                    get_post('OrderDate'));
            if (! check_num('qty', 0, $max)) {
                // display_error( _("The item could not be updated because you
                // are attempting to set the quantity ordered to less than 0, or
                // the discount percent to more than 100."));
                display_error(
                        _(
                                "The item could not be updated because insufficient quantity on hand."));
                set_focus('qty');
                return false;
            }
        }
        
        if (! get_post('stock_id_text', true)) {
            display_error(_("Item description cannot be empty."));
            set_focus('stock_id_edit');
            return false;
        } elseif (! check_num('Disc', 0, 100)) {
            display_error(
                    _(
                            "The item could not be updated because you are attempting to set the discount percent to more than 100."));
            set_focus('Disc');
            return false;
            // } elseif (!check_num('price', 0) && (!$allow_negative_prices ||
        // $is_inventory_item)) {
        } elseif (! check_num('price', 0)) {
            display_error(
                    _(
                            "Price for inventory item must be entered and can not be less than 0"));
            set_focus('price');
            return false;
        } elseif (isset($_POST['LineNo']) &&
                 isset($_SESSION['Items']->line_items[$_POST['LineNo']]) &&
                 ! check_num('qty', 
                        $_SESSION['Items']->line_items[$_POST['LineNo']]->qty_done)) {
            
            set_focus('qty');
            display_error(
                    _(
                            "You attempting to make the quantity ordered a quantity less than has already been delivered. The quantity delivered cannot be modified retrospectively."));
            return false;
        }
        
        $cost_home = get_standard_cost(get_post('stock_id')); // Added
                                                              // 2011-03-27 Joe
                                                              // Hunt
        $cost = $cost_home /
                 get_exchange_rate_from_home_currency(
                        $_SESSION['Items']->customer_currency, 
                        $_SESSION['Items']->document_date);
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
            display_warning(
                    sprintf(_("Price %s is below Standard Cost %s"), $price, 
                            $std_cost));
        }
        return true;
    }
}

function line_gl_sale_cart_focus ()
{
    global $Ajax;
    $Ajax->activate('gl_items_table');
    set_focus('gl_code');
}

// --------------------------------------------------------------------------------
function can_process_sale_cart ()
{
    global $Refs, $SysPrefs;
    
    copy_to_cart_sale();
    
    if (! get_post('customer_id')) {
        display_error(_("There is no customer selected."));
        set_focus('customer_id');
        return false;
    } else {
        $currency_code = get_customer_currency(get_post('customer_id'));
        $ex_rate = get_exchange_rate_from_home_currency($currency_code, 
                $_POST['OrderDate']);
        if ($currency_code != get_company_currency() && $ex_rate == 1) {
            if ($_POST['ex_rate_allow'] != 1) {
                display_error(
                        _(
                                "Cannot retrieve exchange rate for currency $currency_code as of " .
                                         $_POST['OrderDate'] .
                                         ". Please add exchange rate manually on Exchange Rates page."));
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
    
    if ($_SESSION['Items']->trans_type != ST_SALESORDER &&
             $_SESSION['Items']->trans_type != ST_SALESQUOTE &&
             ! is_date_in_fiscalyear($_POST['OrderDate'])) {
        display_error(_("The entered date is not in fiscal year"));
        set_focus('OrderDate');
        return false;
    }
    if (count($_SESSION['Items']->line_items) == 0 and
             count($_SESSION['Items']->gl_items) < 1) {
        display_error(_("You must enter at least one non empty item line."));
        set_focus('AddItem');
        return false;
    }
    
    // if ( get_post('payment') && get_post('payment')==4 ) {
    
    // }
    
    // display_error($low_stock = $_SESSION['Items']->check_qoh());
    
    // if (!$SysPrefs->allow_negative_stock() && ($low_stock =
    // $_SESSION['Items']->check_qoh()))
    // {
    // display_error(_("This document cannot be processed because there is
    // insufficient quantity for items marked."));
    // return false;
    // }
    
    if ($_SESSION['Items']->payment_terms['cash_sale'] == 0) {
        
        if (strlen($_POST['deliver_to']) <= 1) {
            display_error(
                    _(
                            "You must enter the person or company to whom delivery should be made to."));
            set_focus('deliver_to');
            return false;
        }
        if ($_SESSION['Items']->trans_type != ST_SALESQUOTE &&
                 strlen($_POST['delivery_address']) <= 1) {
            display_error(
                    _(
                            "You should enter the street address in the box provided. Orders cannot be accepted without a valid street address."));
            set_focus('delivery_address');
            return false;
        }
        
        if ($_POST['freight_cost'] == "")
            $_POST['freight_cost'] = price_format(0);
        
        if (! check_num('freight_cost', 0)) {
            display_error(
                    _("The shipping cost entered is expected to be numeric."));
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
                display_error(
                        _(
                                "The requested valid date is before the date of the quotation."));
            else
                display_error(
                        _(
                                "The requested delivery date is before the date of the order."));
            set_focus('delivery_date');
            return false;
        }
    } else 
        if (! db_has_cash_accounts() ||
                 ! $_SESSION['Items']->pos['bank_account_name']) {
            display_error(
                    _(
                            "You need to define a cash account for your " .
                             anchor('sales/manage/sales_points.php', 
                                    'Sales Point') . "."));
            return false;
        }
    
    if (! $Refs->is_valid($_POST['ref'])) {
        display_error(_("You must enter a reference."));
        set_focus('ref');
        return false;
    }
    // if (!db_has_currency_rates($_SESSION['Items']->customer_currency,
    // $_POST['OrderDate']))
    // return false;
    
    if ($_SESSION['Items']->get_items_total() < 0) {
        display_error("Invoice total amount cannot be less than zero.");
        return false;
    }
    
    return true;
}

function copy_to_cart_invoice(){
    update_items();
    $cart = &$_SESSION['Items'];
    $cart->ship_via = $_POST['ship_via'];
    $cart->freight_cost = input_num('ChargeFreightCost');
    $cart->document_date =  $_POST['InvoiceDate'];
    $cart->due_date =  $_POST['due_date'];
    if ($cart->pos['cash_sale'] || $cart->pos['credit_sale']) {
        $cart->payment = $_POST['payment'];
        $cart->payment_terms = get_payment_terms($_POST['payment']);
    }
    $cart->Comments = $_POST['Comments'];
    if ($_SESSION['Items']->trans_no == 0){
        $ref = input_post('ref');
        if( !$ref && isset($_SESSION['Items']->reference) ){
            $ref = $_SESSION['Items']->reference;
        }
        if( !$ref  ){
            $ref = $Refs->get_next($_SESSION['Items']->trans_type);
        }
        
        $cart->reference = $ref;
    }
        
        $cart->dimension_id =  $_POST['dimension_id'];
        $cart->dimension2_id =  $_POST['dimension2_id'];

}

function copy_to_cart_sale ()
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
    
    if ($cart->trans_type != ST_SALESORDER && $cart->trans_type != ST_SALESQUOTE) { // 2008-11-12
                                                                                // Joe
                                                                                // Hunt
        $cart->dimension_id = $_POST['dimension_id'];
        $cart->dimension2_id = $_POST['dimension2_id'];
    }
    $cart->ex_rate = input_num('_ex_rate', null);
}
// --------------------------------------------------------------------------------
// TUANVT4
function add_to_order_new ($tax_type_id, &$order, $new_item, $new_item_qty, 
        $price, $discount, $description = '')
{
    // calculate item price to sum of kit element prices factor for
    // value distribution over all exploded kit items
    $std_price = get_kit_price($new_item, $order->customer_currency, 
            $order->sales_type, $order->price_factor, get_post('OrderDate'), 
            true);
    
    if ($std_price == 0)
        $price_factor = 0;
    else
        $price_factor = $price / $std_price;
    
    $kit = get_item_kit($new_item);
    $item_num = db_num_rows($kit);
    
    while ($item = db_fetch($kit)) {
        $std_price = get_kit_price($item['stock_id'], $order->customer_currency, 
                $order->sales_type, $order->price_factor, get_post('OrderDate'), 
                true);
        
        // rounding differences are included in last price item in kit
        $item_num --;
        if ($item_num) {
            $price -= $item['quantity'] * $std_price * $price_factor;
            $item_price = $std_price * $price_factor;
        } else {
            if ($item['quantity'])
                $price = $price / $item['quantity'];
            $item_price = $price;
        }
        $item_price = round($item_price, user_price_dec());
        
        if (! $item['is_foreign'] && $item['item_code'] != $item['stock_id']) { // this is sales kit - recurse
            add_to_order_new($tax_type_id, $order, $item['stock_id'], 
                    $new_item_qty * $item['quantity'], $item_price, $discount);
        } else { // stock item record eventually with foreign code
          
            // check duplicate stock item
            foreach ($order->line_items as $order_item) {
                if (strcasecmp($order_item->stock_id, $item['stock_id']) == 0) {
                    display_warning(
                            _("For Part :") . $item['stock_id'] . " " .
                                     _(
                                            "This item is already on this document. You have been warned."));
                    break;
                }
            }
            // $order->add_to_cart_new($tax_type_id,count($order->line_items),$item['stock_id'],$new_item_qty*$item['quantity'],
            // $item_price, $discount, 0,0, $description);
            $order->add_to_cart_new($tax_type_id, count($order->line_items), 
                    $item['stock_id'], $new_item_qty, $item_price, $discount, 0, 
                    0, $description);
        }
    }
}