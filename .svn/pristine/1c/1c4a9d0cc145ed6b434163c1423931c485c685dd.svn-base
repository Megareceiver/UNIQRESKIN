<?php

class SalesDetailDelivery
{
    var $tran_no = 0;
    var $tran_type = ST_CUSTDELIVERY;

    function __construct ()
    {
        $this->check_tran_no();
    }

    function index ()
    {}

    private function check_tran_no ()
    {
        if (isset($_GET["trans_no"])) {
            $this->tran_no = $_GET["trans_no"];
        } elseif (isset($_POST["trans_no"])) {
            $this->tran_no = $_POST["trans_no"];
        }
    }

    function view ()
    {
        $this->transaction = get_customer_trans($this->tran_no, ST_CUSTDELIVERY);
        $this->order_tran = get_sales_order_header($this->transaction["order_"], 
                ST_SALESORDER);
        
        box_start(sprintf(_("DISPATCH NOTE #%d"), $this->tran_no));
        $this->delivery_info();
        
        echo "<hr>";
        //$this->tran_info();
        $this->tran_header();
        
        box_start(_("Delivery Items"));
        $this->tran_items();
        
        is_voided_display($this->tran_type, $this->tran_no, _("This dispatch has been voided."));
        box_end();
        
        // $this->old_data();
    }

    private function delivery_info ()
    {
        $th = array(
                _("Charge To"),
                _("Charge Branch"),
                _("Delivered To")
        );
        start_table(TABLESTYLE, "width=100%");
        table_header($th);
        start_row();
        $myrow = get_customer_trans($this->tran_no, $this->tran_type);
        label_cell($myrow["DebtorName"] . "<br>" . nl2br($myrow["address"]));
        
        $branch = get_branch($myrow["branch_code"]);
        label_cell($branch["br_name"] . "<br>" . nl2br($branch["br_address"]));
        
        $sales_order = get_sales_order_header($myrow["order_"], ST_SALESORDER);
        label_cell(
                $sales_order["deliver_to"] . "<br>" .
                         nl2br($sales_order["delivery_address"]));
        end_row();
        end_table();
    }

    private function tran_info ()
    {
        
        row_start();
        bootstrap_set_label_column(5);
        col_start(12, 'col-md-4');
        input_label(_("Reference"), null, $this->transaction["reference"]);
        input_label(_("Customer Order Ref."), null, 
                $this->order_tran["customer_ref"]);
        input_label(_("Dispatch Date"), null, 
                sql2date($this->transaction["tran_date"]));
        
        col_start(12, 'col-md-4');
        input_label(_("Currency"), null, $this->order_tran["curr_code"]);
        input_label(_("Shipping Company"), null, 
                $this->transaction["shipper_name"]);
        input_label(_("Due Date"), null, 
                sql2date($this->transaction["due_date"]));
        
        col_start(12, 'col-md-4');
        input_label(_("Our Order No"), null, 
                get_customer_trans_view_str(ST_SALESORDER, 
                        $this->order_tran["order_no"]));
        input_label(_("Sales Type"), null, $this->transaction["sales_type"]);
        
        col_start(12, 'col-md-12');
        $comments = get_comments($this->tran_type, $this->tran_no);
        if ($comments and db_num_rows($comments)) {
            echo "<p>";
            while ($comment = db_fetch($comments)) {
                echo nl2br($comment["memo_"]) . "<br>";
            }
            echo "</p>";
        }
        row_end();
    }
    
    private function tran_header ()
    {
//         $sales_order = get_sales_order_header($this->tran["order_"], ST_SALESORDER);
    
        start_table(TABLESTYLE, "width=100%");
        start_row();
        label_cells(_("Reference"), $this->transaction["reference"], "class='tableheader2'");
        label_cells(_("Currency"), $this->order_tran["curr_code"], "class='tableheader2'");
        label_cells(_("Our Order No"), 
                get_customer_trans_view_str(ST_SALESORDER, $this->order_tran["order_no"]), 
                "class='tableheader2'");
        end_row();
        
        start_row();
        label_cells(_("Customer Order Ref."), $this->order_tran["customer_ref"],
                "class='tableheader2'");
        label_cells(_("Shipping Company"), $this->transaction["shipper_name"],
                "class='tableheader2'");
        label_cells(_("Sales Type"), $this->transaction["sales_type"],
                "class='tableheader2'");
        end_row();
        start_row();
        label_cells(_("Dispatch Date"), sql2date($this->transaction["tran_date"]),
                "class='tableheader2'", "nowrap");
        label_cells(_("Due Date"), sql2date($this->transaction["due_date"]),
                "class='tableheader2'", "nowrap");
        label_cells(_("Deliveries"),
                get_customer_trans_view_str(ST_CUSTDELIVERY,
                        get_sales_parent_numbers(ST_SALESINVOICE,
                                $this->tran_no)), "class='tableheader2'");
        end_row();
        comments_display_row(ST_SALESINVOICE, $this->tran_no);
        end_table();
    
        echo "</td></tr>";
        end_table(1); // outer table
    }
    

    private function tran_items ()
    {
        $result = get_customer_trans_details($this->tran_type, $this->tran_no);
        
        start_table(TABLESTYLE);
        $item_taxes = array();
        if (db_num_rows($result) > 0) {
            $th = array(
                    _("Item Code"),
                    _("Item Description"),
                    _("Quantity"),
                    _("Unit"),
                    _("Price"),
                    _("Discount %"),
                    _("Total")
            );
            table_header($th);
            
            $k = 0; // row colour counter
            $sub_total = 0;
            while ($myrow2 = db_fetch($result)) {
                if ($myrow2['quantity'] == 0)
                    continue;
                
                $tax = tax_calculator($myrow2['tax_type_id'], 
                        $myrow2['unit_price'] * $myrow2['quantity'] *
                                 (1 - $myrow2['discount_percent']), 
                                $this->transaction['tax_included']);
                
                if (! array_key_exists($tax->id, $item_taxes)) {
                    $item_taxes[$tax->id] = array(
                            'name' => $tax->name,
                            'rate' => $tax->rate,
                            'value' => 0
                    );
                }
                $item_taxes[$tax->id]['value'] += $tax->value;
                
                alt_table_row_color($k);
                
                $value = round2(
                        ((1 - $myrow2["discount_percent"]) *
                                 $myrow2["unit_price"] * $myrow2["quantity"]), 
                                user_price_dec());
                $sub_total += $value;
                
                if ($myrow2["discount_percent"] == 0) {
                    $display_discount = "";
                } else {
                    $display_discount = percent_format(
                            $myrow2["discount_percent"] * 100) . "%";
                }
                
                label_cell($myrow2["stock_id"]);
                label_cell($myrow2["StockDescription"]);
                qty_cell($myrow2["quantity"], false, 
                        get_qty_dec($myrow2["stock_id"]));
                label_cell($myrow2["units"], "align=right");
                amount_cell($myrow2["unit_price"]);
                label_cell($display_discount, "nowrap align=right");
                // amount_cell($value);
                label_cell(number_total($value), "nowrap align=right ");
                end_row();
            } // end while there are line items to print out
            $display_sub_tot = number_format2($sub_total, user_amount_dec());
            label_row(_("Sub-total"), $display_sub_tot, "colspan=6 align=right", 
                    "nowrap align=right width=15%");
        } else
            display_note(_("There are no line items on this dispatch."), 1, 2);
        
        $display_freight = price_format($this->transaction["ov_freight"]);
        
        label_row(_("Shipping"), $display_freight, "colspan=6 align=right", 
                "nowrap align=right");
        
        // $tax_items = get_trans_tax_details(ST_CUSTDELIVERY, $trans_id);
        // display_customer_trans_tax_details($tax_items, 6);
        foreach ($item_taxes as $tax) {
            $value = number_total($tax['value']);
            if ($this->transaction['tax_included']) {
                label_row(
                        _("Included") . " " . $tax['name'] . " (" . $tax['rate'] .
                                 "%) " . ": $value ", '', 
                                "colspan=6 align=right", "align=right");
            } else {
                label_row($tax['name'] . " (" . $tax['rate'] . "%)", $value, 
                        "colspan=6 align=right", "align=right");
            }
        }
        
        $display_total = number_format2(
                $this->transaction["ov_freight"] + $this->transaction["ov_amount"] +
                         $this->transaction["ov_freight_tax"] + $this->transaction["ov_gst"], 
                        user_amount_dec());
        
        label_row(_("TOTAL VALUE"), $display_total, "colspan=6 align=right", 
                "nowrap align=right");
        end_table(1);
    }

    function old_data ()
    {
        $trans_id = $this->tran_no;
        $myrow = $this->transaction;
        $branch = get_branch($myrow["branch_code"]);
        $sales_order = $this->order_tran;
        
        display_heading(sprintf(_("DISPATCH NOTE #%d"), $trans_id));
        
        start_table(TABLESTYLE2, "width=95%");
        echo "<tr valign=top><td>"; // outer table
        
        /* Now the customer charged to details in a sub table */
        start_table(TABLESTYLE, "width=100%");
        $th = array(
                _("Charge To")
        );
        table_header($th);
        
        label_row(null, 
                $myrow["DebtorName"] . "<br>" . nl2br($myrow["address"]), 
                "nowrap");
        
        end_table();
        
        /* end of the small table showing charge to account details */
        
        echo "</td><td>"; // outer table
        
        /*
         * end of the main table showing the company name and charge to details
         */
        
        start_table(TABLESTYLE, "width=100%");
        $th = array(
                _("Charge Branch")
        );
        table_header($th);
        
        label_row(null, 
                $branch["br_name"] . "<br>" . nl2br($branch["br_address"]), 
                "nowrap");
        end_table();
        
        echo "</td><td>"; // outer table
        
        start_table(TABLESTYLE, "width=100%");
        $th = array(
                _("Delivered To")
        );
        table_header($th);
        
        label_row(null, 
                $sales_order["deliver_to"] . "<br>" .
                         nl2br($sales_order["delivery_address"]), "nowrap");
        end_table();
        
        echo "</td><td>"; // outer table
        
        start_table(TABLESTYLE, "width=100%");
        start_row();
        label_cells(_("Reference"), $myrow["reference"], "class='tableheader2'");
        label_cells(_("Currency"), $sales_order["curr_code"], 
                "class='tableheader2'");
        label_cells(_("Our Order No"), 
                get_customer_trans_view_str(ST_SALESORDER, 
                        $sales_order["order_no"]), "class='tableheader2'");
        end_row();
        start_row();
        label_cells(_("Customer Order Ref."), $sales_order["customer_ref"], 
                "class='tableheader2'");
        label_cells(_("Shipping Company"), $myrow["shipper_name"], 
                "class='tableheader2'");
        label_cells(_("Sales Type"), $myrow["sales_type"], 
                "class='tableheader2'");
        end_row();
        start_row();
        label_cells(_("Dispatch Date"), sql2date($myrow["tran_date"]), 
                "class='tableheader2'", "nowrap");
        label_cells(_("Due Date"), sql2date($myrow["due_date"]), 
                "class='tableheader2'", "nowrap");
        end_row();
        comments_display_row(ST_CUSTDELIVERY, $trans_id);
        end_table();
        
        echo "</td></tr>";
        end_table(1); // outer table
        
        $result = get_customer_trans_details(ST_CUSTDELIVERY, $trans_id);
        
        start_table(TABLESTYLE, "width=95%");
        $item_taxes = array();
        if (db_num_rows($result) > 0) {
            $th = array(
                    _("Item Code"),
                    _("Item Description"),
                    _("Quantity"),
                    _("Unit"),
                    _("Price"),
                    _("Discount %"),
                    _("Total")
            );
            table_header($th);
            
            $k = 0; // row colour counter
            $sub_total = 0;
            while ($myrow2 = db_fetch($result)) {
                if ($myrow2['quantity'] == 0)
                    continue;
                
                $tax = tax_calculator($myrow2['tax_type_id'], 
                        $myrow2['unit_price'] * $myrow2['quantity'] *
                                 (1 - $myrow2['discount_percent']), 
                                $myrow['tax_included']);
                
                if (! array_key_exists($tax->id, $item_taxes)) {
                    $item_taxes[$tax->id] = array(
                            'name' => $tax->name,
                            'rate' => $tax->rate,
                            'value' => 0
                    );
                }
                $item_taxes[$tax->id]['value'] += $tax->value;
                
                alt_table_row_color($k);
                
                $value = round2(
                        ((1 - $myrow2["discount_percent"]) *
                                 $myrow2["unit_price"] * $myrow2["quantity"]), 
                                user_price_dec());
                $sub_total += $value;
                
                if ($myrow2["discount_percent"] == 0) {
                    $display_discount = "";
                } else {
                    $display_discount = percent_format(
                            $myrow2["discount_percent"] * 100) . "%";
                }
                
                label_cell($myrow2["stock_id"]);
                label_cell($myrow2["StockDescription"]);
                qty_cell($myrow2["quantity"], false, 
                        get_qty_dec($myrow2["stock_id"]));
                label_cell($myrow2["units"], "align=right");
                amount_cell($myrow2["unit_price"]);
                label_cell($display_discount, "nowrap align=right");
                // amount_cell($value);
                label_cell(number_total($value), "nowrap align=right ");
                end_row();
            } // end while there are line items to print out
            $display_sub_tot = number_format2($sub_total, user_amount_dec());
            label_row(_("Sub-total"), $display_sub_tot, "colspan=6 align=right", 
                    "nowrap align=right width=15%");
        } else
            display_note(_("There are no line items on this dispatch."), 1, 2);
        
        $display_freight = price_format($myrow["ov_freight"]);
        
        label_row(_("Shipping"), $display_freight, "colspan=6 align=right", 
                "nowrap align=right");
        
        // $tax_items = get_trans_tax_details(ST_CUSTDELIVERY, $trans_id);
        // display_customer_trans_tax_details($tax_items, 6);
        foreach ($item_taxes as $tax) {
            $value = number_total($tax['value']);
            if ($myrow['tax_included']) {
                label_row(
                        _("Included") . " " . $tax['name'] . " (" . $tax['rate'] .
                                 "%) " . ": $value ", '', 
                                "colspan=6 align=right", "align=right");
            } else {
                label_row($tax['name'] . " (" . $tax['rate'] . "%)", $value, 
                        "colspan=6 align=right", "align=right");
            }
        }
        
        $display_total = number_format2(
                $myrow["ov_freight"] + $myrow["ov_amount"] +
                         $myrow["ov_freight_tax"] + $myrow["ov_gst"], 
                        user_amount_dec());
        
        label_row(_("TOTAL VALUE"), $display_total, "colspan=6 align=right", 
                "nowrap align=right");
        end_table(1);
        
        is_voided_display(ST_CUSTDELIVERY, $trans_id, 
                _("This dispatch has been voided."));
    }
}