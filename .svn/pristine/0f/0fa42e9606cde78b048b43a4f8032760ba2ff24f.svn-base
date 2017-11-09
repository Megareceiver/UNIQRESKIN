<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class SalesTranInvoice
{
    function __construct()
    {
        $this->check_submit();
        $this->check_page_finish();
        $this->create_form();
    }

    function form()
    {
        start_form();
        box_start();
        hidden('cart_id');
        $this->form_header();

        $row = get_customer_to_order($_SESSION['Items']->customer_id);
        if ($row['dissallow_invoices'] == 1) {
            display_error(_("The selected customer account is currently on hold. Please contact the credit control personnel to discuss."));
            end_form();
            end_page();
            exit();
        }

        box_start(_("Invoice Items"));

        div_start('Items');
        $this->form_items();
        div_end();

        echo '<hr>';
        row_start('justify-content-center');
        col_start(8);
        input_textarea(_("Memo"), 'Comments');
        row_end();

        box_footer_start();
        submit('Update', _("Update"),true, _('Refresh document page'), true,'save');
        submit('process_invoice', _("Process Invoice"),true, _('Check entered data and save document'), 'default');
        
        box_footer_end();
        box_end();
    }

    private function form_header()
    {
        // find delivery spans for batch invoice display
        $dspans = array();
        $lastdn = '';
        $spanlen = 1;

        for ($line_no = 0; $line_no < count($_SESSION['Items']->line_items); $line_no ++) {
            $line = $_SESSION['Items']->line_items[$line_no];
            if ($line->quantity == $line->qty_done) {
                continue;
            }
            if ($line->src_no == $lastdn) {
                $spanlen ++;
            } else {
                if ($lastdn != '') {
                    $dspans[] = $spanlen;
                    $spanlen = 1;
                }
            }
            $lastdn = $line->src_no;
        }
        $dspans[] = $spanlen;

        // -----------------------------------------------------------------------------

        $is_batch_invoice = count($_SESSION['Items']->src_docs) > 1;

        $is_edition = $_SESSION['Items']->trans_type == ST_SALESINVOICE && $_SESSION['Items']->trans_no != 0;

        $dim = get_company_pref('use_dimension');

        bootstrap_set_label_column(4);
        row_start();
        col_start(4);
        input_label(_("Customer"), null, $_SESSION['Items']->customer_name);
        if ($_SESSION['Items']->trans_no == 0) {
            input_label(_("Reference"), 'ref');
        } else {
            input_label(_("Reference"), null, $_SESSION['Items']->reference);
        }

        if (! isset($_POST['ship_via'])) {
            $_POST['ship_via'] = $_SESSION['Items']->ship_via;
        }
        shippers_bootstrap(_("Shipping Company"), 'ship_via');

        col_start(4);
        input_label(_("Branch"), null, get_branch_name($_SESSION['Items']->Branch));
        input_label(_("Sales Type"), null, $_SESSION['Items']->sales_type_name);

        input_date_bootstrap(_("Date"), 'InvoiceDate');
        // date_cells(_("Date"), 'InvoiceDate', '', $_SESSION['Items']->trans_no == 0,
        // 0, 0, 0, "class='tableheader2'", true);

        // 2010-09-03 Joe Hunt
        // if ($dim > 0)
        // label_cells(_("Dimension"), get_dimension_string($_SESSION['Items']->dimension_id), "class='tableheader2'");
        if ($dim > 0) {
            label_cell(_("Dimension") . ":", "class='tableheader2'");
            $_POST['dimension_id'] = $_SESSION['Items']->dimension_id;
            dimensions_list_cells(null, 'dimension_id', null, true, ' ', false, 1, false);
        } else
            hidden('dimension_id', 0);

        col_start(4);

        if ($_SESSION['Items']->pos['credit_sale'] || $_SESSION['Items']->pos['cash_sale']) {
            $paymcat = ! $_SESSION['Items']->pos['cash_sale'] ? PM_CREDIT : (! $_SESSION['Items']->pos['credit_sale'] ? PM_CASH : PM_ANY);
            sale_payments_bootstrap(_("Payment terms"), 'payment', $paymcat);
        } else {
            input_label(_("Payment"), null, $_SESSION['Items']->payment_terms['terms']);
        }

        // label_cells(_("Delivery Notes:"),
        // get_customer_trans_view_str(ST_CUSTDELIVERY, array_keys($_SESSION['Items']->src_docs)), "class='tableheader2'");

        input_label(_("Currency"), null, $_SESSION['Items']->customer_currency);
        if (! isset($_POST['InvoiceDate']) || ! is_date($_POST['InvoiceDate'])) {
            $_POST['InvoiceDate'] = new_doc_date();
            if (! is_date_in_fiscalyear($_POST['InvoiceDate'])) {
                $_POST['InvoiceDate'] = end_fiscalyear();
            }
        }

        if (! isset($_POST['due_date']) || ! is_date($_POST['due_date'])) {
            $_POST['due_date'] = get_invoice_duedate($_SESSION['Items']->payment, $_POST['InvoiceDate']);
        }
        input_date_bootstrap(_("Due Date"), 'due_date');
        /*
         * if ($dim > 1)
         * label_cells(_("Dimension"). " 2", get_dimension_string($_SESSION['Items']->dimension2_id), "class='tableheader2'");
         * else if ($dim > 0)
         * label_cell("&nbsp;", "colspan=2");
         */
        if ($dim > 1) {
            label_cell(_("Dimension") . " 2:", "class='tableheader2'");
            $_POST['dimension2_id'] = $_SESSION['Items']->dimension2_id;
            dimensions_list_cells(null, 'dimension2_id', null, true, ' ', false, 2, false);
        } else
            hidden('dimension2_id', 0);

        row_end();
    }

    private function form_items()
    {
        $is_batch_invoice = count($_SESSION['Items']->src_docs) > 1;
        $is_edition = $_SESSION['Items']->trans_type == ST_SALESINVOICE && $_SESSION['Items']->trans_no != 0;

        start_table(TABLESTYLE, "width=80%");
        $th = array(
            _("Item Code"),
            _("Item Description"),
            _("Delivered"),
            _("Units"),
            _("Invoiced"),
            _("This Invoice"),
            _("Price"),
            _("Tax Type"),
            _("Discount"),
            _("Total")
        );

        if ($is_batch_invoice) {
            $th[] = _("DN");
            $th[] = "";
        }

        if ($is_edition) {
            $th[4] = _("Credited");
        }
        table_header($th);
        $k = 0;
        $has_marked = false;
        $show_qoh = true;

        $dn_line_cnt = 0;

        foreach ($_SESSION['Items']->line_items as $line => $ln_itm) {
            if ($ln_itm->quantity == $ln_itm->qty_done) {
                continue; // this line was fully invoiced
            }
            alt_table_row_color($k);
            view_stock_status_cell($ln_itm->stock_id);

            text_cells(null, 'Line' . $line . 'Desc', $ln_itm->item_description, 30, 50);
            $dec = get_qty_dec($ln_itm->stock_id);
            qty_cell($ln_itm->quantity, false, $dec);
            label_cell($ln_itm->units);
            qty_cell($ln_itm->qty_done, false, $dec);

            if ($is_batch_invoice) {
                // for batch invoices we can only remove whole deliveries
                echo '<td nowrap align=right>';
                hidden('Line' . $line, $ln_itm->qty_dispatched);
                echo number_format2($ln_itm->qty_dispatched, $dec) . '</td>';
            } else {
                small_qty_cells(null, 'Line' . $line, qty_format($ln_itm->qty_dispatched, $ln_itm->stock_id, $dec), null, null, $dec);
            }
            $display_discount_percent = percent_format($ln_itm->discount_percent * 100) . " %";

            $line_total = ($ln_itm->qty_dispatched * $ln_itm->price * (1 - $ln_itm->discount_percent));

            amount_cell($ln_itm->price);
            label_cell($ln_itm->tax_type_name);
            label_cell($display_discount_percent, "nowrap align=right");
            // amount_cell($line_total);
            label_cell(number_format2($line_total, user_amount_dec()), "nowrap align=right ");

            if ($is_batch_invoice) {
                if ($dn_line_cnt == 0) {
                    $dn_line_cnt = $dspans[0];
                    $dspans = array_slice($dspans, 1);
                    label_cell($ln_itm->src_no, "rowspan=$dn_line_cnt class=oddrow");
                    label_cell("<a href='" . $_SERVER['PHP_SELF'] . "?RemoveDN=" . $ln_itm->src_no . "'>" . _("Remove") . "</a>", "rowspan=$dn_line_cnt class=oddrow");
                }
                $dn_line_cnt --;
            }
            end_row();
        }

        // Don't re-calculate freight if some of the order has already been delivered -
        // depending on the business logic required this condition may not be required.
        // It seems unfair to charge the customer twice for freight if the order
        // was not fully delivered the first time ??

        if (! isset($_POST['ChargeFreightCost']) || $_POST['ChargeFreightCost'] == "") {
            if ($_SESSION['Items']->any_already_delivered() == 1) {
                $_POST['ChargeFreightCost'] = price_format(0);
            } else {
                $_POST['ChargeFreightCost'] = price_format($_SESSION['Items']->freight_cost);
            }

            if (! check_num('ChargeFreightCost')) {
                $_POST['ChargeFreightCost'] = price_format(0);
            }
        }

        $accumulate_shipping = get_company_pref('accumulate_shipping');
        if ($is_batch_invoice && $accumulate_shipping)
            set_delivery_shipping_sum(array_keys($_SESSION['Items']->src_docs));

        $colspan = 9;
        start_row();
        label_cell(_("Shipping Cost"), "colspan=$colspan align=right");
        small_amount_cells(null, 'ChargeFreightCost', null);
        if ($is_batch_invoice) {
            label_cell('', 'colspan=2');
        }

        end_row();
        $inv_items_total = $_SESSION['Items']->get_items_total_dispatch();

        $display_sub_total = number_format2($inv_items_total + input_num('ChargeFreightCost'), user_amount_dec());

        label_row(_("Sub-total"), $display_sub_total, "colspan=$colspan align=right", "align=right", $is_batch_invoice ? 2 : 0);
        // TUANVT5
        $taxes = $_SESSION['Items']->get_taxes_new(null, input_num('ChargeFreightCost'));
        $tax_total = display_edit_tax_items_new($taxes, $colspan, $_SESSION['Items']->tax_included, $is_batch_invoice ? 2 : 0);

        $display_total = number_format2(($inv_items_total + input_num('ChargeFreightCost') + $tax_total), user_amount_dec());

        label_row(_("Invoice Total"), $display_total, "colspan=$colspan align=right", "align=right", $is_batch_invoice ? 2 : 0);

        end_table(1);
    }

    private function check_submit()
    {
        if (isset($_POST['process_invoice']) && check_data()) {
            $newinvoice = $_SESSION['Items']->trans_no == 0;
//             copy_to_cart();
//             copy_to_cart_sale();
            copy_to_cart_invoice();
            if ($newinvoice)
                new_doc_date($_SESSION['Items']->document_date);

            $invoice_no = $_SESSION['Items']->write();

           
            if ($invoice_no == - 1) {
                display_error(_("The entered reference is already in use."));
                set_focus('ref');
            } else {
                processing_end();
                if ($newinvoice) {
                    meta_forward($_SERVER['PHP_SELF'], "AddedID=$invoice_no");
                } else {
                    meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$invoice_no");
                }
            }
        }

        if (list_updated('payment')) {
            $order = &$_SESSION['Items'];
            $order->payment = get_post('payment');
            $order->payment_terms = get_payment_terms($order->payment);
            $order->due_date = get_invoice_duedate($order->payment, $order->document_date);
            if ($order->payment_terms['cash_sale']) {
                $_POST['Location'] = $order->Location = $order->pos['pos_location'];
                $order->location_name = $order->pos['location_name'];
            }
        }
    }

    private function check_page_finish()
    {
        if (isset($_GET['AddedID'])) {

            $invoice_no = $_GET['AddedID'];
            $trans_type = ST_SALESINVOICE;
            $this->invoice_finish($trans_type,$invoice_no);
            
            display_footer_exit();
        } elseif (isset($_GET['UpdatedID'])) {

            $invoice_no = $_GET['UpdatedID'];
            $tran_type = ST_SALESINVOICE;

            box_start();
            row_start();
            
            col_start(12);
            mt_list_start(sprintf(_('Sales Invoice # %d has been updated.'), $invoice_no), '', 'blue');
            
            mt_list_tran_view(_("&View This Invoice"), $tran_type, $invoice_no);
            
            $invoice_print = print_document_link($invoice_no . "-" . $tran_type, _("&Print This Invoice"), true, $tran_type);
            mt_list($invoice_print);
            
            $invoice_email = print_document_link($invoice_no . "-" . $tran_type, _("&Email This Invoice"), true, $tran_type, false, "printlink", "", 1);
            mt_list($invoice_email);
            
            mt_list_link(_("Select Another &Invoice to Modify"), "sales/inquiry/customer_inquiry.php");
            row_end();
            box_footer();
            box_end();

            display_footer_exit();
        } elseif (isset($_GET['RemoveDN'])) {

            for ($line_no = 0; $line_no < count($_SESSION['Items']->line_items); $line_no ++) {
                $line = &$_SESSION['Items']->line_items[$line_no];
                if ($line->src_no == $_GET['RemoveDN']) {
                    $line->quantity = $line->qty_done;
                    $line->qty_dispatched = 0;
                }
            }
            unset($line);

            // Remove also src_doc delivery note
            $sources = &$_SESSION['Items']->src_docs;
            unset($sources[$_GET['RemoveDN']]);
        }
    }
    private function invoice_finish($tran_type='',$tran_no=0,$addnew=true){
        display_notification(_("Selected deliveries has been processed"));
        
        box_start();
        row_start();
        
        col_start(6);
        mt_list_start('Actions', '', 'blue');
        
        
       
        mt_list_link(_("Select Another &Delivery For Invoicing"), "/sales/inquiry/sales_deliveries_view.php?OutstandingOnly=1");
        
        $sql = "SELECT trans_type_from, trans_no_from FROM " . TB_PREF . "cust_allocations
			WHERE trans_type_to=" . ST_SALESINVOICE . " AND trans_no_to=" . db_escape($tran_no);
        $result = db_query($sql, "could not retrieve customer allocation");
        $row = db_fetch($result);
        
        if ($row === false){
            mt_list_link(_("Entry &customer payment for this invoice"), "/sales/customer_payments.php?SInvoice=$tran_no");
        }
        mt_list_link(_("Add an Attachment"), "admin/attachments.php?filterType=$tran_type&trans_no=$tran_no");
        col_start(6);
        mt_list_start('Printing', null, 'red');
        mt_list_tran_view(_("&View This Invoice"), $tran_type, $tran_no);
        mt_list_gl_view(_("View the GL &Journal Entries for this Invoice"), $tran_type,$tran_no);
        $invoice_print = print_document_link($tran_no . "-" . $tran_type, _("&Print This Invoice"), true, $tran_type);
        mt_list($invoice_print);
        
        $invoice_email = print_document_link($tran_no . "-" . $tran_type, _("&Email This Invoice"), true, $tran_type, false, "printlink", "", 1);
        mt_list($invoice_email);
        row_end();
        box_footer();
        box_end();
        
        
    }

    private function create_form()
    {
        if ((isset($_GET['DeliveryNumber']) && ($_GET['DeliveryNumber'] > 0)) || isset($_GET['BatchInvoice'])) {

            processing_start();

            if (isset($_GET['BatchInvoice'])) {
                $src = $_SESSION['DeliveryBatch'];
                unset($_SESSION['DeliveryBatch']);
            } else {
                $src = array(
                    $_GET['DeliveryNumber']
                );
            }

            /* read in all the selected deliveries into the Items cart */
            $dn = new Cart(ST_CUSTDELIVERY, $src, true);

            if ($dn->count_items() == 0) {
                display_notification("There are no delivered items with a quantity left to invoice. There is nothing left to invoice.");
                box_start();
                row_start();
                
                col_start(12);
                mt_list_start('Actions', '', 'blue');
                mt_list_link(_("Select a different delivery to invoice"), "/sales/inquiry/sales_deliveries_view.php?OutstandingOnly=1");
                
                row_end();
                box_footer();
                box_end();
                display_footer_exit();
//                 hyperlink_params( "/sales/inquiry/sales_deliveries_view.php", _("Select a different delivery to invoice"), "OutstandingOnly=1");
//                 die("<br><b>" . _("There are no delivered items with a quantity left to invoice. There is nothing left to invoice.") . "</b>");
            }

            $_SESSION['Items'] = $dn;
            copy_from_cart();
        } elseif (isset($_GET['ModifyInvoice']) && $_GET['ModifyInvoice'] > 0) {

            if (get_sales_parent_numbers(ST_SALESINVOICE, $_GET['ModifyInvoice']) == 0) { // 1.xx compatibility hack
                echo "<center><br><b>" . _("There are no delivery notes for this invoice.<br>
		Most likely this invoice was created in Front Accounting version prior to 2.0
		and therefore can not be modified.") . "</b></center>";
                display_footer_exit();
            }
            processing_start();
            $_SESSION['Items'] = new Cart(ST_SALESINVOICE, $_GET['ModifyInvoice']);

            if ($_SESSION['Items']->count_items() == 0) {
                echo "<center><br><b>" . _("All quantities on this invoice has been credited. There is nothing to modify on this invoice") . "</b></center>";
                display_footer_exit();
            }
            copy_from_cart();
        } elseif (! processing_active()) {
            /* This page can only be called with a delivery for invoicing or invoice no for edit */
            display_error(_("This page can only be opened after delivery selection. Please select delivery to invoicing first."));

            hyperlink_no_params("$path_to_root/sales/inquiry/sales_deliveries_view.php", _("Select Delivery to Invoice"));

            end_page();
            exit();
        } elseif (! isset($_POST['process_invoice']) && ! check_quantities()) {
            display_error(_("Selected quantity cannot be less than quantity credited nor more than quantity not invoiced yet."));
        }
    }
}