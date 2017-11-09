<?php

class PurchasesTranReceive
{

    function __construct ()
    {
        $this->check_finish();
        $this->create_po();
        $this->check_submit();
    }

    function form ()
    {
        start_form();
        
        $this->edit_grn_summary($_SESSION['PO']);
        
        box_start('Items to Receive');
        $this->po_receive_items();
        
        box_footer_start();
        submit_center_first('Update', _("Update"), '', true);
        submit_center_last('ProcessGoodsReceived', _("Process Receive Items"), 
                _("Clear all GL entry fields"), 'default');
        box_footer_end();
        box_end();
        end_form();
    }

    private function edit_grn_summary (&$po)
    {
        box_start(); row_start();
        col_start(3);
        bootstrap_set_label_column(7);
        input_label(_("Supplier"), null, $po->supplier_name);
        
        if (! is_company_currency($po->curr_code)) {
            input_label(_("Order Currency"), null, $po->curr_code);
        }
        
        $tran_view_href = get_trans_view_str(ST_PURCHORDER, $po->order_no);
        input_label(_("For Purchase Order"), null, $tran_view_href );
        
        input_label(_("Ordered On"), null, $po->orig_order_date);
        
        col_start(5);
        bootstrap_set_label_column(4);
        if (! isset($_POST['ref'])){
            global $Refs;
            $_POST['ref'] = $Refs->get_next(ST_SUPPRECEIVE);
        }
           
        input_ref(_("Reference"), 'ref');
        
        if (! isset($_POST['Location'])){
            $_POST['Location'] = $po->Location;
        }
        locations_bootstrap(_("Deliver Into Location"), "Location");
        
        if (! isset($_POST['DefaultReceivedDate'])){
            $_POST['DefaultReceivedDate'] = new_doc_date();
        }
        input_date_bootstrap(_("Date Items Received"), 'DefaultReceivedDate',null,false,true);
//         date_row(_("Date Items Received"), 'DefaultReceivedDate', '', true, 0, 0, 0, '', true);
        
        col_start(4);
        bootstrap_set_label_column(5);
        // currently this is related order supp reference
        // ref_row(_("Supplier's Reference"), 'supp_ref', _("Supplier's
        // Reference"));
        input_label(_("Supplier's Reference"),null, $po->supp_ref);
        input_label(_("Delivery Address"),null,$po->delivery_address);
        
        if ($po->Comments != ""){
            col_start(12);
            bootstrap_set_label_column(2);
            input_label(_("Order Comments"), null, $po->Comments);
        }
            
        
        if (! is_company_currency($po->curr_code)){
            exchange_rate_display(get_company_currency(), $po->curr_code, get_post('DefaultReceivedDate'));
        }
        row_end();
        box_end();

    }


    private function po_receive_items()
    {
        
        div_start('grn_items');
        
        start_table(TABLESTYLE, "colspan=7 width=90%");
        $th = array(_("Item Code"), _("Description"), _("Ordered"), _("Units"), _("Received"), _("Outstanding"), _("This Delivery"), _("Price"), _("Total"));
        table_header($th);
    
        /*show the line items on the order with the quantity being received for modification */
    
        $total = 0;
        $k = 0; //row colour counter
    
        if (count($_SESSION['PO']->line_items)> 0 )
        {
           	foreach ($_SESSION['PO']->line_items as $ln_itm)
           	{
    
           	    alt_table_row_color($k);
    
           	    $qty_outstanding = $ln_itm->quantity - $ln_itm->qty_received;
    
           	    if (!isset($_POST['Update']) && !isset($_POST['ProcessGoodsReceived']) && $ln_itm->receive_qty == 0)
           	    {   //If no quantites yet input default the balance to be received
           	        $ln_itm->receive_qty = $qty_outstanding;
           	    }
    
           	    $line_total = ($ln_itm->receive_qty * $ln_itm->price);
           	    $total += $line_total;
    
           	    label_cell($ln_itm->stock_id);
           	    if ($qty_outstanding > 0)
           	        text_cells(null, $ln_itm->stock_id . "Desc", $ln_itm->item_description, 30, 50);
           	        else
           	            label_cell($ln_itm->item_description);
           	            $dec = get_qty_dec($ln_itm->stock_id);
           	            qty_cell($ln_itm->quantity, false, $dec);
           	            label_cell($ln_itm->units);
           	            qty_cell($ln_itm->qty_received, false, $dec);
           	            qty_cell($qty_outstanding, false, $dec);
    
           	            if ($qty_outstanding > 0)
           	                qty_cells(null, $ln_itm->line_no, number_format2($ln_itm->receive_qty, $dec), "align=right", null, $dec);
           	                else
           	                    label_cell(number_format2($ln_itm->receive_qty, $dec), "align=right");
    
           	                    amount_decimal_cell($ln_itm->price);
           	                    amount_cell($line_total);
           	                    end_row();
           	}
        }
    
        $colspan = count($th)-1;
    
        $display_sub_total = price_format($total/* + input_num('freight_cost')*/);
    
        label_row(_("Sub-total"), $display_sub_total, "colspan=$colspan align=right","align=right");
        //TUANVT5
        $taxes = $_SESSION['PO']->get_taxes_new(null,input_num('freight_cost'), true);
    
        $tax_total = display_edit_tax_items_new($taxes, $colspan, $_SESSION['PO']->tax_included);
    
        $display_total = price_format(($total + input_num('freight_cost') + $tax_total));
    
        start_row();
        label_cells(_("Amount Total"), $display_total, "colspan=$colspan align='right'","align='right'");
        end_row();
        end_table();
        div_end();
    }
    
    
    private function check_submit ()
    {
        global $Ajax;
        if (isset($_POST['Update']) || isset($_POST['ProcessGoodsReceived'])) {
            
            /*
             * if update quantities button is hit page has been called and
             * ${$line->line_no} would have be
             * set from the post to the quantity to be received in this receival
             */
            foreach ($_SESSION['PO']->line_items as $line) {
                if (($line->quantity - $line->qty_received) > 0) {
                    $_POST[$line->line_no] = max($_POST[$line->line_no], 0);
                    if (! check_num($line->line_no))
                        $_POST[$line->line_no] = number_format2(0, 
                                get_qty_dec($line->stock_id));
                    
                    if (! isset($_POST['DefaultReceivedDate']) ||
                             $_POST['DefaultReceivedDate'] == "")
                        $_POST['DefaultReceivedDate'] = new_doc_date();
                    
                    $_SESSION['PO']->line_items[$line->line_no]->receive_qty = input_num(
                            $line->line_no);
                    
                    if (isset($_POST[$line->stock_id . "Desc"]) &&
                             strlen($_POST[$line->stock_id . "Desc"]) > 0) {
                        $_SESSION['PO']->line_items[$line->line_no]->item_description = $_POST[$line->stock_id .
                         "Desc"];
            }
        }
    }
    $Ajax->activate('grn_items');
}

// --------------------------------------------------------------------------------------------------

if (isset($_POST['ProcessGoodsReceived'])) {
    process_receive_po();
}
}

    private function create_po ()
    {
        if (isset($_GET['PONumber']) && $_GET['PONumber'] > 0 && ! isset($_POST['Update'])) {
            create_new_po(ST_PURCHORDER, $_GET['PONumber']);
            $_SESSION['PO']->trans_type = ST_SUPPRECEIVE;
            
            global $Refs;
            $_SESSION['PO']->reference = $Refs->get_next(ST_SUPPRECEIVE);
            copy_from_cart();
        }
    }

    private function check_finish ()
    {
        if (isset($_GET['AddedID'])) {
            $grn = $_GET['AddedID'];
            $trans_type = ST_SUPPRECEIVE;
            
            display_notification( _("Purchase Order Delivery has been processed"));
            
            box_start();
            row_start();
            col_start(12);
            mt_list_start('Actions', '', 'blue');
            
//             display_note( get_trans_view_str($trans_type, $grn, _("&View this Delivery")));
            mt_list_tran_view(_("&View this Delivery"),$trans_type, $grn);
            $clearing_act = get_company_pref('grn_clearing_act');
            if ($clearing_act){
//                 display_note( get_gl_view_str($trans_type, $grn, _("View the GL Journal Entries for this Delivery")), 1);
                mt_list_gl_view( _("View the GL Journal Entries for this Delivery"),$trans_type, $grn);
            }
                
            mt_list_hyperlink("/purchasing/supplier_invoice.php", _("Entry purchase &invoice for this receival"), "New=1");
            mt_list_hyperlink("/purchasing/inquiry/po_search.php", _("Select a different &purchase order for receiving items against"));
            
            row_end();
            box_footer();
            box_end();
            
            display_footer_exit();
        }
    
        if ((! isset($_GET['PONumber']) || $_GET['PONumber'] == 0) && ! isset($_SESSION['PO'])) {
            die( _("This page can only be opened if a purchase order has been selected. Please select a purchase order first."));
        }
    }
}