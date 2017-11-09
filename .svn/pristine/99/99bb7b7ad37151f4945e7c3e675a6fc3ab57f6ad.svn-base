<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class SalesTranOrder
{

    function __construct()
    {
        get_instance()->librarie_in_module('sale_cart', 'sales');
        $this->cart = get_instance()->sale_cart;

        if( empty(get_instance()->module_name) ){
            get_instance()->auto_load_module('sales');
        }

        $this->cart->create_form();

        $this->check_submit();
        $this->check_page_finish();
    }

    function form()
    {
        start_form();

        hidden('cart_id');
        box_start();
        $customer_error = $this->cart->display_order_header($_SESSION['Items'], ($_SESSION['Items']->any_already_delivered() == 0));

        if ($customer_error == "") {
            switch ($_SESSION['Items']->trans_type) {
                case ST_SALESINVOICE:
                    box_start(_("Sales Invoice Items"));
                    break;
                case ST_CUSTDELIVERY:
                    box_start(_("Delivery Note Items"));
                    break;
                case ST_SALESQUOTE:
                    box_start(_("Sales Quotation Items"));
                    break;
                default:
                    box_start(_("Sales Order Items"));
                    break;
            }

            $this->cart->display_order_summary($_SESSION['Items'], true);
            $this->cart->display_delivery_details($_SESSION['Items']);

            box_footer_start();
            $this->form_buttons();
            box_footer_end();
        } else {
            display_error($customer_error);
            box_footer();
        }
        box_end();
        end_form();
    }

    private function form_buttons()
    {
        if ($_SESSION['Items']->trans_type == ST_SALESINVOICE) {
            $idate = _("Invoice Date:");
            $orderitems = _("Sales Invoice Items");
            $deliverydetails = _("Enter Delivery Details and Confirm Invoice");
            $cancelorder = _("Cancel Invoice");
            $porder = _("Place Invoice");
        } elseif ($_SESSION['Items']->trans_type == ST_CUSTDELIVERY) {
            $idate = _("Delivery Date:");
            $orderitems = _("Delivery Note Items");
            $deliverydetails = _("Enter Delivery Details and Confirm Dispatch");
            $cancelorder = _("Cancel Delivery");
            $porder = _("Place Delivery");
        } elseif ($_SESSION['Items']->trans_type == ST_SALESQUOTE) {
            $idate = _("Quotation Date:");
            $orderitems = _("Sales Quotation Items");
            $deliverydetails = _("Enter Delivery Details and Confirm Quotation");
            $cancelorder = _("Cancel Quotation");
            $porder = isMobile() ? _("Place") : _("Place Quotation");
            $corder = _("Commit Quotations Changes");
        } else {
            $idate = _("Order Date:");
            $orderitems = _("Sales Order Items");
            $deliverydetails = _("Enter Delivery Details and Confirm Order");
            $cancelorder = _("Cancel Order");
            $porder = _("Place Order");
            $corder = _("Commit Order Changes");
        }

        if (isMobile()) {
            $cancelorder = _("Cancel");
        }

        if ($_SESSION['Items']->trans_no == 0) {

            submit_center_first('ProcessOrder', $porder, _('Check entered data and save document'), 'default');
            // submit_center_first('ProcessOrder', $porder, _('Check entered data and save document'));

            submit('CancelOrder', $cancelorder, true, _('Cancels document entry or removes sales order when editing an old document'), true);
            // submit_center_last('CancelOrder', $cancelorder,
            // );

            submit_js_confirm('CancelOrder', _('You are about to void this Document.\nDo you want to continue?'));
        } else {
            submit_center_first('ProcessOrder', $corder, _('Validate changes and update document'), 'default');
            submit_center_last('CancelOrder', $cancelorder, _('Cancels document entry or removes sales order when editing an old document'), true);

            if ($_SESSION['Items']->trans_type == ST_SALESORDER)
                submit_js_confirm('CancelOrder', _('You are about to cancel undelivered part of this order.\nDo you want to continue?'));
            else
                submit_js_confirm('CancelOrder', _('You are about to void this Document.\nDo you want to continue?'));
        }
    }

    private function check_submit()
    {
        global $Ajax;
        if (list_updated('branch_id')) {
            // when branch is selected via external editor also customer can change
            $br = get_branch(get_post('branch_id'));
            $_POST['customer_id'] = $br['debtor_no'];
            $Ajax->activate('customer_id');
        }

        if (isset($_POST['update'])) {
            //copy_to_cart();
            copy_to_cart_sale();
            $Ajax->activate('items_table');
        }

        if (isset($_POST['page_reload'])) {
            page_modified();
        }

        if (isset($_POST['ProcessOrder']) && can_process_sale_cart()) {
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
                $messages = NULL;
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

                // die('end process');
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

//         if (isset($_POST['CancelOrder']))
//             handle_cancel_order();

//         $id = find_submit('Delete');

//         if ($id != - 1)
//             handle_delete_item($id);

//         if (isset($_POST['UpdateItem']))
//             handle_update_item();

//         if (isset($_POST['AddItem'])) {
//             handle_new_item();
//         }

//         if (isset($_POST['CancelItemChanges'])) {
//             line_start_focus();
//         }
    }

    private function check_page_finish()
    {
        
        if (isset($_GET['AddedID'])) {
            $order_no = $_GET['AddedID'];
            page($_SESSION['page_title']);
            $this->order_finish($order_no);
            display_footer_exit();
        } elseif (isset($_GET['UpdatedID'])) {
            $order_no = $_GET['UpdatedID'];
            page($_SESSION['page_title']);
            display_notification_centered(sprintf(_("Order # %d has been updated."), $order_no));
            $this->order_update_finish($order_no);
            display_footer_exit();
        } elseif (isset($_GET['AddedQU'])) {
            page($_SESSION['page_title']);
            $this->quotation_finish();
            display_footer_exit();
        } elseif (isset($_GET['UpdatedQU'])) {
            page($_SESSION['page_title']);
            $order_no = $_GET['UpdatedQU'];
            $this->quotation_update_finish($order_no);
            display_footer_exit();
        } elseif (isset($_GET['AddedDN'])) {
            $delivery = $_GET['AddedDN'];
            page($_SESSION['page_title']);
            display_notification_centered(sprintf(_("Delivery # %d has been entered."), $delivery));
            $this->delivery_finish($delivery);

            display_footer_exit();
        } elseif (isset($_GET['AddedDI'])) {
            $invoice = $_GET['AddedDI'];
            page($_SESSION['page_title']);
            display_notification_centered(sprintf(_("Invoice # %d has been entered."), $invoice));
            $this->invoice_finish($invoice);
            display_footer_exit();
        } else
            $this->cart->check_edit_conflicts();
    }

    private function quotation_finish()
    {
        $order_no = $_GET['AddedQU'];
        display_notification(sprintf(_("Quotation # %d has been entered."), $order_no));

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');

        mt_list_link(_("Make &Sales Order Against This Quotation"), "/sales/sales_order_entry.php?NewQuoteToSalesOrder=$order_no");
        mt_list_link(_("Enter a New &Quotation"), "/sales/sales_order_entry.php?NewQuotation=0");
        mt_list_tran_view(_("&View This Quotation"), ST_SALESQUOTE, $order_no);
        col_start(6);
        mt_list_start('Printing', null, 'red');
        mt_list_print(_("&Print This Quotation"), ST_SALESQUOTE, $order_no, 'prtopt');
        mt_list_print(_("&Email This Quotation"), ST_SALESQUOTE, $order_no, null, 1);
        set_focus('prtopt');
        row_end();
        box_footer();
        box_end();
    }

    private function quotation_update_finish($order_no = 0)
    {
        display_notification(sprintf(_("Quotation # %d has been updated."), $order_no));

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_link(_("Make &Sales Order Against This Quotation"), "/sales/sales_order_entry.php?NewQuoteToSalesOrder=$order_no");
        mt_list_link(_("Select A Different &Quotation"), "/sales/inquiry/sales_orders_view.php?type=" . ST_SALESQUOTE);
        mt_list_link(_("Make &Sales Order Against This Quotation"), "/sales/sales_order_entry.php?NewQuoteToSalesOrder=$order_no");

        mt_list_tran_view(_("&View This Quotation"), ST_SALESQUOTE, $order_no);

        col_start(6);
        mt_list_start('Printing', null, 'red');

        mt_list_print(_("&Print This Quotation"), ST_SALESQUOTE, $order_no, 'prtopt');
        mt_list_print(_("&Email This Quotation"), ST_SALESQUOTE, $order_no, null, 1);
        set_focus('prtopt');

        row_end();
        box_footer();
        box_end();
    }

    private function order_finish($order_no = 0)
    {
        display_notification_centered(sprintf(_("Order # %d has been entered."), $order_no));
        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_link(_("Make &Delivery Against This Order"), "/sales/customer_delivery.php?OrderNumber=$order_no");
        //mt_list_link(_("Work &Order Entry"), "/manufacturing/work_order_entry.php?");
        mt_list_link(_("Enter a &New Order"), "/sales/sales_order_entry.php?NewOrder=0");

        col_start(6);
        mt_list_start('Printing', null, 'red');
        mt_list_tran_view(_("&View This Order"), ST_SALESORDER, $order_no);

        mt_list_print(_("&Print This Order"), ST_SALESORDER, $order_no, 'prtopt');
        mt_list_print(_("&Email This Order"), ST_SALESORDER, $order_no, null, 1);
        set_focus('prtopt');
        row_end();
        box_footer();
        box_end();
    }

    private function order_update_finish($order_no=0){
        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_link(_("Confirm Order Quantities and Make &Delivery"), "/sales/customer_delivery.php?OrderNumber=$order_no");
        mt_list_link(_("Select A Different &Order"), "/sales/inquiry/sales_orders_view.php?OutstandingOnly=1");

        col_start(6);
        mt_list_start('Printing', null, 'red');
        mt_list_tran_view(_("&View This Order"), ST_SALESORDER, $order_no);

        mt_list_print(_("&Print This Order"), ST_SALESORDER, $order_no, 'prtopt');
        mt_list_print(_("&Email This Order"), ST_SALESORDER, $order_no, null, 1);
        set_focus('prtopt');
        row_end();
        box_footer();
        box_end();
    }

    private function delivery_finish($delivery=0){
        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_tran_view(_("&View This Delivery"), ST_CUSTDELIVERY, $delivery);
        mt_list_gl_view(_("View the GL Journal Entries for this Dispatch"),ST_CUSTDELIVERY,$delivery);
        mt_list_link(_("Make &Invoice Against This Delivery"), "/sales/customer_invoice.php?DeliveryNumber=$delivery");

        if ((isset($_GET['Type']) && $_GET['Type'] == 1))
            mt_list_link(_("Enter a New Template &Delivery"), "/sales/inquiry/sales_orders_view.php?DeliveryTemplates=Yes");
        else
            mt_list_link(_("Enter a &New Delivery"), "/sales/sales_order_entry.php?NewDelivery=0");

        col_start(6);
        mt_list_start('Printing', null, 'red');
        mt_list_print(_("&Print Delivery Note"), ST_CUSTDELIVERY, $delivery, 'prtopt');
        mt_list_print(_("&Email Delivery Note"), ST_CUSTDELIVERY, $delivery, null, 1);
        mt_list_print(_("P&rint as Packing Slip"), ST_CUSTDELIVERY, $delivery, 'prtopt', null, 1);
        mt_list_print(_("E&mail as Packing Slip"), ST_CUSTDELIVERY, $delivery, null, 1, 1);
        set_focus('prtopt');

        row_end();
        box_footer();
        box_end();
    }

    private function invoice_finish($invoice=0){
        $sql = "SELECT trans_type_from, trans_no_from FROM " . TB_PREF . "cust_allocations
			WHERE trans_type_to=" . ST_SALESINVOICE . " AND trans_no_to=" . db_escape($invoice);
        $result = db_query($sql, "could not retrieve customer allocation");
        $row = db_fetch($result);

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');

        mt_list_tran_view(_("&View This Invoice"), ST_SALESINVOICE, $invoice);
        mt_list_gl_view(_("View the GL &Journal Entries for this Invoice"), ST_SALESINVOICE,$invoice);

        if ((isset($_GET['Type']) && $_GET['Type'] == 1))
            mt_list_link(_("Enter a &New Template Invoice"), "/sales/inquiry/sales_orders_view.php?InvoiceTemplates=Yes");
        else
            mt_list_link(_("Enter a &New Direct Invoice"), "/sales/sales_order_entry.php?NewInvoice=0");

        if ($row === false)
            mt_list_link(_("Entry &customer payment for this invoice"), "/sales/customer_payments.php?SInvoice=" . $invoice);

        mt_list_link(_("Add an Attachment"), "/admin/attachments.php?filterType=" . ST_SALESINVOICE . "&trans_no=$invoice");

        col_start(6);
        mt_list_start('Printing', null, 'red');

        mt_list_print(_("&Print Sales Invoice"), ST_SALESINVOICE, $invoice . "-" . ST_SALESINVOICE, 'prtopt');
        mt_list_print(_("&Email Sales Invoice"), ST_SALESINVOICE, $invoice . "-" . ST_SALESINVOICE, null, 1);
        set_focus('prtopt');


        if ($row !== false)
            mt_list_print(_("Print &Receipt"), $row['trans_type_from'], $row['trans_no_from'] . "-" . $row['trans_type_from'], 'prtopt');

        row_end();
        box_footer();
        box_end();

    }
}