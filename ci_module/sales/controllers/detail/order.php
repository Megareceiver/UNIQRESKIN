<?php

class SalesDetailOrder
{

    var $tran_no = 0;

    var $tran_type = ST_SALESQUOTE;

    function __construct ()
    {
        $this->tran_type = input_get("trans_type");
        $this->tran_no = input_get("trans_no");
    }

    public function detail ()
    {
        if ($this->tran_type == ST_SALESQUOTE) {
            box_start(sprintf(_("Sales Quotation #%d"), $this->tran_no));
        } else {
            box_start(sprintf(_("Sales Order #%d"), $this->tran_no));
        }
        
        if (isset($_SESSION['View'])) {
            unset($_SESSION['View']);
        }
        
        $_SESSION['View'] = new Cart($this->tran_type, $this->tran_no);
        $this->tran_header();
        
        box_start(_("Line Details"));
        if ($_SESSION['View']->so_type == 1){
            msg_success(_("This Sales Order is used as a Template."),"Note");
        }
        $this->order_tran_detail();
        box_end();
    }

    private function tran_header ()
    {
        row_start();
        col_start(12,'col-md-'.(($this->tran_type != ST_SALESQUOTE)?"6":12));
            portlet_start("Order Information");
            $this->order_info();
            portlet_end();
            
        if ($this->tran_type != ST_SALESQUOTE) {
            col_start(12,'col-md-3');
                portlet_start("Delivery Notes",'blue-sharp');
                $this->sale_quote();
                portlet_end();
            col_start(12,'col-md-3');
                portlet_start("Sales Invoices",'green-seagreen');
                $this->invoice();
                portlet_start("Credit Notes",'green');
                $this->credit_note();
            portlet_end();
        }
        
            
        row_end();
        
        
    }
    private function order_tran_detail(){
        start_table(TABLESTYLE, "colspan=9 width=95%");
        $th = array(
                _("Item Code"),
                _("Item Description"),
                _("Quantity"),
                _("Unit"),
                _("Price"),
                _("Discount"),
                _("Total"),
                _("Quantity Delivered")
        );
        table_header($th);

        
        foreach ($_SESSION['View']->line_items as $stock_item) {
        
            $line_total = round2(
                    $stock_item->quantity * $stock_item->price *
                    (1 - $stock_item->discount_percent),
                    user_price_dec());

            start_row();
        
            label_cell($stock_item->stock_id);
            label_cell($stock_item->item_description);
            $dec = get_qty_dec($stock_item->stock_id);
            qty_cell($stock_item->quantity, false, $dec);
            label_cell($stock_item->units);
            amount_cell($stock_item->price);
            amount_cell($stock_item->discount_percent * 100);
            amount_cell($line_total);
        
            qty_cell($stock_item->qty_done, false, $dec);
            end_row();
        }
        
        label_row(_("Shipping"), price_format($_SESSION['View']->freight_cost),
                "align=right colspan=6", "nowrap align=right", 1);
        
        $sub_tot = $_SESSION['View']->get_items_total() +
        $_SESSION['View']->freight_cost;
        
        $display_sub_tot = price_format($sub_tot);
        
        label_row(_("Sub Total"), $display_sub_tot, "align=right colspan=6",
                "nowrap align=right", 1);
        
        // TUANVT5
        $taxes = $_SESSION['View']->get_taxes_new();
        
        
        start_row();
            $tax_total = display_edit_tax_items_new($taxes, 6, $_SESSION['View']->tax_included, 2);
            $display_total = price_format($sub_tot + $tax_total);
            label_cells(_("Amount Total"), $display_total,
                    "colspan=6 align='right'", "align='right'");
            label_cell('', "colspan=2");
        end_row();
        end_table();
    }
    
    private function order_info ()
    {
        start_table(TABLESTYLE, "width=95%");
        label_row(_("Customer Name"), $_SESSION['View']->customer_name, 
                "class='tableheader2'", "colspan=3");
        start_row();
        label_cells(_("Customer Order Ref."), $_SESSION['View']->cust_ref, 
                "class='tableheader2'");
        label_cells(_("Deliver To Branch"), $_SESSION['View']->deliver_to, 
                "class='tableheader2'");
        end_row();
        start_row();
        label_cells(_("Ordered On"), $_SESSION['View']->document_date, 
                "class='tableheader2'");
        if ($_GET['trans_type'] == ST_SALESQUOTE)
            label_cells(_("Valid until"), $_SESSION['View']->due_date, 
                    "class='tableheader2'");
        else
            label_cells(_("Requested Delivery"), $_SESSION['View']->due_date, 
                    "class='tableheader2'");
        end_row();
        start_row();
        label_cells(_("Order Currency"), $_SESSION['View']->customer_currency, 
                "class='tableheader2'");
        label_cells(_("Deliver From Location"), 
                $_SESSION['View']->location_name, "class='tableheader2'");
        end_row();
        
        label_row(_("Payment Terms"), $_SESSION['View']->payment_terms['terms'], 
                "class='tableheader2'", "colspan=3");
        label_row(_("Delivery Address"), 
                nl2br($_SESSION['View']->delivery_address), 
                "class='tableheader2'", "colspan=3");
        label_row(_("Reference"), $_SESSION['View']->reference, 
                "class='tableheader2'", "colspan=3");
        label_row(_("Telephone"), $_SESSION['View']->phone, 
                "class='tableheader2'", "colspan=3");
        label_row(_("E-mail"), 
                "<a href='mailto:" . $_SESSION['View']->email . "'>" .
                         $_SESSION['View']->email . "</a>", 
                        "class='tableheader2'", "colspan=3");
        label_row(_("Comments"), nl2br($_SESSION['View']->Comments), 
                "class='tableheader2'", "colspan=3");
        end_table();
    }

    var $dn_numbers = array();
    private function sale_quote ()
    {
        start_table(TABLESTYLE);
        
        $th = array(
                _("#"),
                _("Ref"),
                _("Date"),
                _("Total")
        );
        table_header($th);
        
        
        $delivery_total = 0;
        
        if ($result = get_sales_child_documents(ST_SALESORDER, 
                $_GET['trans_no'])) {
            
            $k = 0;
            while ($del_row = db_fetch($result)) {
                
                alt_table_row_color($k);
                $this->dn_numbers[] = $del_row["trans_no"];
                $this_total = $del_row["ov_freight"] + $del_row["ov_amount"] +
                         $del_row["ov_freight_tax"] + $del_row["ov_gst"];
                $delivery_total += $this_total;
                
                label_cell(
                        get_customer_trans_view_str($del_row["type"], 
                                $del_row["trans_no"]));
                label_cell($del_row["reference"]);
                label_cell(sql2date($del_row["tran_date"]));
                amount_cell($this_total);
                end_row();
            }
        }
        
        label_row(null, price_format($delivery_total), " ", 
                "colspan=4 align=right");
        
        end_table();
    }

    var $inv_numbers = array();
    private function invoice ()
    {
        start_table(TABLESTYLE);
        $th = array(
                _("#"),
                _("Ref"),
                _("Date"),
                _("Total")
        );
        table_header($th);
        
        
        $invoices_total = 0;
        
        if ($result = get_sales_child_documents(ST_CUSTDELIVERY, $this->dn_numbers)) {
            
            $k = 0;
            
            while ($inv_row = db_fetch($result)) {
                alt_table_row_color($k);
                
                $this_total = $inv_row["ov_freight"] + $inv_row["ov_freight_tax"] +
                         $inv_row["ov_gst"] + $inv_row["ov_amount"];
                $invoices_total += $this_total;
                
                $this->inv_numbers[] = $inv_row["trans_no"];
                label_cell(
                        get_customer_trans_view_str($inv_row["type"], 
                                $inv_row["trans_no"]));
                label_cell($inv_row["reference"]);
                label_cell(sql2date($inv_row["tran_date"]));
                amount_cell($this_total);
                end_row();
            }
        }
        label_row(null, price_format($invoices_total), " ", 
                "colspan=4 align=right");
        
        end_table();
    }

    private function credit_note ()
    {
        start_table(TABLESTYLE);
        $th = array(
                _("#"),
                _("Ref"),
                _("Date"),
                _("Total")
        );
        table_header($th);
        
        $credits_total = 0;
        
        if ($result = get_sales_child_documents(ST_SALESINVOICE, $this->inv_numbers)) {
            $k = 0;
            
            while ($credits_row = db_fetch($result)) {
                
                alt_table_row_color($k);
                
                $this_total = $credits_row["ov_freight"] +
                         $credits_row["ov_freight_tax"] + $credits_row["ov_gst"] +
                         $credits_row["ov_amount"];
                $credits_total += $this_total;
                
                label_cell(
                        get_customer_trans_view_str($credits_row["type"], 
                                $credits_row["trans_no"]));
                label_cell($credits_row["reference"]);
                label_cell(sql2date($credits_row["tran_date"]));
                amount_cell(- $this_total);
                end_row();
            }
        }
        label_row(null, 
                "<font color=red>" . price_format(- $credits_total) . "</font>", 
                " ", "colspan=4 align=right");
        
        end_table();
    }
}