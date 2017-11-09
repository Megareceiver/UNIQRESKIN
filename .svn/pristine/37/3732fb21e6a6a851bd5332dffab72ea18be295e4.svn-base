<?php

function create_sale_cart($type, $trans_no)
{
    global $Refs;
    $cart_name = 'Cart';
    if( !class_exists($cart_name) ){
        include_once(ROOT . "/sales/includes/cart_class.inc");
    }

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
    copy_from_sale_cart();
}

function copy_from_sale_cart()
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