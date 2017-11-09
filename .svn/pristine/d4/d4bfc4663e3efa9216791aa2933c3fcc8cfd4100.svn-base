<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class SalesTranDelivery
{

    function __construct()
    {
        $this->check_submit();
        $this->check_page_finish();
    }

    function form()
    {

        start_form();

        hidden('cart_id');

        box_start();
        $this->form_header();

        $row = get_customer_to_order($_SESSION['Items']->customer_id);
        if ($row['dissallow_invoices'] == 1) {
            display_error(_("The selected customer account is currently on hold. Please contact the credit control personnel to discuss."));
            end_form();
            end_page();
            exit();
        }
        box_start( _("Delivery Items") );
            div_start('Items');
            $this->form_items();
            echo '<hr>';
            $this->form_details();
            div_end();

        box_footer_start();
        submit('Update', _("Update"), true, _('Refresh document page'), true, 'save');

        if (isset($_POST['clear_quantity'])) {
            submit('reset_quantity', _('Reset quantity'), true, _('Refresh document page'));
        } else {
            submit('clear_quantity', _('Clear quantity'), true, _('Refresh document page'));
        }
        submit('process_delivery', _("Process Dispatch"),true, _('Check entered data and save document'), 'default');

        box_footer_end();
        box_end();
        end_form();
    }

    private function form_header()
    {
        global $Refs;
        row_start();
        col_start(3);
        input_label(_("Customer"), null, $_SESSION['Items']->customer_name);
        input_label(_("Branch"), null, get_branch_name($_SESSION['Items']->Branch));
        input_label(_("Currency"), null, $_SESSION['Items']->customer_currency);

        col_start(3);
        if ($_SESSION['Items']->trans_no == 0) {
            $_POST['ref'] = $Refs->get_next(ST_CUSTDELIVERY);
            input_ref(_("Reference"), 'ref');
        } else {
            input_label(_("Reference"), null, $_SESSION['Items']->reference);
        }

        input_label(_("For Sales Order"),null, get_customer_trans_view_str(ST_SALESORDER, $_SESSION['Items']->order_no));
        input_label(_("Sales Type"),null, $_SESSION['Items']->sales_type_name);

        col_start(3);
        if (! isset($_POST['Location'])) {
            $_POST['Location'] = $_SESSION['Items']->Location;
        }
        locations_bootstrap('Delivery From', 'Location',null, false, true);

        if (! isset($_POST['ship_via'])) {
            $_POST['ship_via'] = $_SESSION['Items']->ship_via;
        }
        shippers_bootstrap("Shipping Company", 'ship_via');

        // set this up here cuz it's used to calc qoh
        if (! isset($_POST['DispatchDate']) || ! is_date($_POST['DispatchDate'])) {
            $_POST['DispatchDate'] = new_doc_date();
            if (! is_date_in_fiscalyear($_POST['DispatchDate'])) {
                $_POST['DispatchDate'] = end_fiscalyear();
            }
        }
        input_date_bootstrap(_("Date"), 'DispatchDate');

        col_start(3);
        if (! isset($_POST['due_date']) || ! is_date($_POST['due_date'])) {
            $_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['DispatchDate']);
        }
        customer_credit_bootstrap($_SESSION['Items']->customer_id, $_SESSION['Items']->credit);

        // 2010-09-03 Joe Hunt
        $dim = get_company_pref('use_dimension');
        if ($dim > 0) {

//             label_cell(_("Dimension") . ":", "class='tableheader2'");
//             dimensions_list_cells(null, 'dimension_id', null, true, ' ', false, 1, false);
            dimensions_bootstrap('Dimension', 'dimension_id', null, true, ' ', false, 1, false);
        } else
            hidden('dimension_id', 0);
        if ($dim > 1) {

//             label_cell(_("Dimension") . " 2:", "class='tableheader2'");
//             dimensions_list_cells(null, 'dimension2_id', null, true, ' ', false, 2, false);
            dimensions_bootstrap('Dimension 2', 'dimension2_id', null, true, ' ', false, 2, false);
        } else
            hidden('dimension2_id', 0);
            // ---------

//         date_cells(_("Invoice Dead-line"), 'due_date', '', null, 0, 0, 0, "class='tableheader2'");
        input_date_bootstrap(_("Invoice Dead-line"), 'due_date');
        row_end();
   }

    private function form_details()
    {

        row_start('justify-content-center');
        col_start(8);

//             $_POST['ChargeFreightCost'] = get_post('ChargeFreightCost', price_format($_SESSION['Items']->freight_cost));
            input_money('Shipping Cost', 'ChargeFreightCost',$_SESSION['Items']->freight_cost);

            $inv_items_total = $_SESSION['Items']->get_items_total_dispatch();
            $display_sub_total = price_format($inv_items_total + input_num('ChargeFreightCost'));
            input_label('Sub-total', NULL,$display_sub_total);

            $taxes = $_SESSION['Items']->get_taxes_new(null, input_num('ChargeFreightCost'));
            $tax_total = display_edit_tax_items_new($taxes, $colspan = 0 , $_SESSION['Items']->tax_included);
            $display_total = price_format(($inv_items_total + input_num('ChargeFreightCost') + $tax_total));
            input_label(_("Amount Total"),NULL, $display_total);

            policies_input(_("Action For Balance"), "bo_policy");
            input_textarea(_("Memo"), 'Comments');

        row_end();


    }

    private function form_items()
    {
        start_table(TABLESTYLE, "width=80%");

        $new = $_SESSION['Items']->trans_no == 0;
        $th = array(
            _("Item Code"),
            _("Item Description"),
            $new ? _("Ordered") : _("Max. delivery"),
            _("Units"),
            $new ? _("Delivered") : _("Invoiced"),
            _("This Delivery"),
            _("Price"),
            _("Tax Type"),
            _("Discount"),
            _("Total")
        );

        table_header($th);
        $k = 0;
        $has_marked = false;

        foreach ($_SESSION['Items']->line_items as $line => $ln_itm) {
            if ($ln_itm->quantity == $ln_itm->qty_done) {
                continue; // this line is fully delivered
            }
            if (isset($_POST['_Location_update']) || isset($_POST['clear_quantity']) || isset($_POST['reset_quantity'])) {
                // reset quantity
                $ln_itm->qty_dispatched = $ln_itm->quantity - $ln_itm->qty_done;
            }
            // if it's a non-stock item (eg. service) don't show qoh
            $row_classes = null;
            if (has_stock_holding($ln_itm->mb_flag) && $ln_itm->qty_dispatched) {
                // It's a stock : call get_dispatchable_quantity hook to get which quantity to preset in the
                // quantity input box. This allows for example a hook to modify the default quantity to what's dispatchable
                // (if there is not enough in hand), check at other location or other order people etc ...
                // This hook also returns a 'reason' (css classes) which can be used to theme the row.
                //
                // FIXME: hook_get_dispatchable definition does not allow qoh checks on transaction level
                // (but anyway dispatch is checked again later before transaction is saved)

                $qty = $ln_itm->qty_dispatched;
                if ($check = check_negative_stock($ln_itm->stock_id, - $ln_itm->qty_dispatched, $_POST['Location'], $_POST['DispatchDate']))
                    $qty = $check['qty'];

                $q_class = hook_get_dispatchable_quantity($ln_itm, $_POST['Location'], $_POST['DispatchDate'], $qty);

                // Skip line if needed
                if ($q_class === 'skip')
                    continue;
                if (is_array($q_class)) {
                    list ($ln_itm->qty_dispatched, $row_classes) = $q_class;
                    $has_marked = true;
                }
            }

            alt_table_row_color($k, $row_classes);
            view_stock_status_cell($ln_itm->stock_id);

            if ($ln_itm->descr_editable)
                text_cells(null, 'Line' . $line . 'Desc', $ln_itm->item_description, 30, 50);
            else
                label_cell($ln_itm->item_description);

            $dec = get_qty_dec($ln_itm->stock_id);
            qty_cell($ln_itm->quantity, false, $dec);
            label_cell($ln_itm->units);
            qty_cell($ln_itm->qty_done, false, $dec);

            if (isset($_POST['clear_quantity'])) {
                $ln_itm->qty_dispatched = 0;
            }
            $_POST['Line' . $line] = $ln_itm->qty_dispatched; // / clear post so value displayed in the fiel is the 'new' quantity
            small_qty_cells(null, 'Line' . $line, qty_format($ln_itm->qty_dispatched, $ln_itm->stock_id, $dec), null, null, $dec);

            $display_discount_percent = percent_format($ln_itm->discount_percent * 100) . "%";

            $line_total = ($ln_itm->qty_dispatched * $ln_itm->price * (1 - $ln_itm->discount_percent));

            $tax = get_gst($ln_itm->tax_type_id);

            amount_cell($ln_itm->price);
            label_cell($tax->no . " (" . $tax->rate . "%)");
            label_cell($display_discount_percent, "nowrap align=right");
            amount_cell($line_total);

            end_row();
        }

        end_table();
        if ($has_marked) {
            display_note(_("Marked items have insufficient quantities in stock as on day of delivery."), 0, 1, "class='stockmankofg'");
        }
    }

    private function check_submit()
    {
        global $Ajax;

        if (isset($_POST['process_delivery']) && check_data()) {
            $dn = &$_SESSION['Items'];

            if ($_POST['bo_policy']) {
                $bo_policy = 0;
            } else {
                $bo_policy = 1;
            }
            $newdelivery = ($dn->trans_no == 0);

            if ($newdelivery)
                new_doc_date($dn->document_date);

            $delivery_no = $dn->write($bo_policy);

            if ($delivery_no == - 1) {
                display_error(_("The entered reference is already in use."));
                set_focus('ref');
            } else {
                processing_end();
                if ($newdelivery) {
                    meta_forward($_SERVER['PHP_SELF'], "AddedID=$delivery_no");
                } else {
                    meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$delivery_no");
                }
            }
        }

        if (isset($_POST['Update']) || isset($_POST['_Location_update']) || isset($_POST['qty']) || isset($_POST['process_delivery'])) {
            $Ajax->activate('Items');
        }
    }

    private function check_page_finish()
    {
        if (isset($_GET['AddedID'])) {
            $dispatch_no = $_GET['AddedID'];
            $this->page_success($dispatch_no);
            display_footer_exit();
        } elseif (isset($_GET['UpdatedID'])) {

            $delivery_no = $_GET['UpdatedID'];
            $this->page_success($delivery_no,false);
            
//             display_notification_centered(sprintf(_('Delivery Note # %d has been updated.'), $delivery_no));

//             display_note(get_trans_view_str(ST_CUSTDELIVERY, $delivery_no, _("View this delivery")), 0, 1);

//             display_note(print_document_link($delivery_no, _("&Print Delivery Note"), true, ST_CUSTDELIVERY));
//             display_note(print_document_link($delivery_no, _("&Email Delivery Note"), true, ST_CUSTDELIVERY, false, "printlink", "", 1), 1, 1);
//             display_note(print_document_link($delivery_no, _("P&rint as Packing Slip"), true, ST_CUSTDELIVERY, false, "printlink", "", 0, 1));
//             display_note(print_document_link($delivery_no, _("E&mail as Packing Slip"), true, ST_CUSTDELIVERY, false, "printlink", "", 1, 1), 1);

//             hyperlink_params($path_to_root . "/sales/customer_invoice.php", _("Confirm Delivery and Invoice"), "DeliveryNumber=$delivery_no");
//             hyperlink_params($path_to_root . "/sales/inquiry/sales_deliveries_view.php", _("Select A Different Delivery"), "OutstandingOnly=1");

            display_footer_exit();
        }
    }
    
    private function page_success($tran_no=0,$addNew=true){
        if( $addNew ){
            display_notification(sprintf(_("Delivery # %d has been entered."), $tran_no));
        } else {
            display_notification(sprintf(_("Delivery Note # %d has been updated."), $tran_no));
        }
        
        
        box_start();
        row_start();
        
        col_start(6);
        mt_list_start('Actions', '', 'blue');
        
            mt_list_tran_view(_("&View This Delivery"), ST_CUSTDELIVERY, $tran_no);
            mt_list_gl_view(_("View the GL Journal Entries for this Dispatch"), ST_CUSTDELIVERY, $tran_no);
            if( $addNew ){
                mt_list_link(_("Invoice This Delivery"), "/sales/customer_invoice.php?DeliveryNumber=$tran_no");
                mt_list_link(_("Select Another Order For Dispatch"), "/sales/inquiry/sales_orders_view.php?OutstandingOnly=1");
            } else {
                mt_list_link(_("Confirm Delivery and Invoice"), "/sales/customer_invoice.php?DeliveryNumber=$tran_no");
                mt_list_link(_("Select A Different Delivery"), "/sales/inquiry/sales_orders_view.php?OutstandingOnly=1");
            }
            
        
        
        col_start(6);
        mt_list_start('Printing', null, 'red');
            mt_list_print(_("&Print Delivery Note"), ST_CUSTDELIVERY, $tran_no);
            mt_list_print(_("&Email Delivery Note"), ST_CUSTDELIVERY, $tran_no,null,1);
            
            $PackingSlip = print_document_link($tran_no, _("P&rint as Packing Slip"), true, ST_CUSTDELIVERY, false, "printlink", "", 0, 1);
            get_instance()->bootstrap->mt_list($PackingSlip);
            
            $PackingSlipEmail = print_document_link($tran_no, _("E&mail as Packing Slip"), true, ST_CUSTDELIVERY, false, "printlink", "", 1, 1);
            get_instance()->bootstrap->mt_list($PackingSlipEmail);

        row_end();
        box_footer();
        box_end();
    }
}