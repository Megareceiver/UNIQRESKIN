<?php

class SalesDetailInvoice
{

    var $tran_no = 0;

    var $tran_type = ST_SALESINVOICE;

    function __construct ()
    {
        $this->tran_type = input_get("trans_type");
        
        if (isset($_GET["trans_no"])) {
            $this->tran_no = $_GET["trans_no"];
        } elseif (isset($_POST["trans_no"])) {
            $this->tran_no = $_POST["trans_no"];
        }
    }

    function view ()
    {
        
        // 3 different queries to get the information - what a JOKE !!!!
        $this->tran = get_customer_trans($this->tran_no, ST_SALESINVOICE);
        $this->tran_branch = get_branch($this->tran["branch_code"]);
        
        
        box_start(sprintf(_("SALES INVOICE #%d"), $this->tran_no));
        
        $this->info();
        echo "<hr>";
        $this->tran_header();
        
        box_start("Invoice Items");
        $this->items();
        
        $voided = is_voided_display(ST_SALESINVOICE, $this->tran_no, 
                _("This invoice has been voided."));
        
        if (! $voided) {
            display_allocations_to(PT_CUSTOMER, $this->tran['debtor_no'], 
                    ST_SALESINVOICE, $this->tran_no, $this->tran['Total']);
        }
        box_end();
    }

    private function info ()
    {
        start_table(TABLESTYLE, "width=100%");
        $th = array(
                _("Charge To"),
                _("Charge Branch"),
                _("Payment Terms")
        );
        table_header($th);
        start_row();
        label_cell(
                $this->tran["DebtorName"] . "<br>" .
                         nl2br($this->tran["address"]));
        label_cell(
                $this->tran_branch["br_name"] . "<br>" .
                         nl2br($this->tran_branch["br_address"]));
        
        $paym = get_payment_terms($this->tran['payment_terms']);
        label_cell($paym["terms"]);
        end_row();
        end_table();
    }

    private function tran_header ()
    {
        $sales_order = get_sales_order_header($this->tran["order_"], ST_SALESORDER);
        
        start_table(TABLESTYLE, "width=100%");
        start_row();
        label_cells(_("Reference"), $this->tran["reference"], 
                "class='tableheader2'");
        label_cells(_("Currency"), $sales_order["curr_code"], 
                "class='tableheader2'");
        label_cells(_("Our Order No"), 
                get_customer_trans_view_str(ST_SALESORDER, 
                        $sales_order["order_no"]), "class='tableheader2'");
        end_row();
        start_row();
        label_cells(_("Customer Order Ref."), $sales_order["customer_ref"], 
                "class='tableheader2'");
        label_cells(_("Shipping Company"), $this->tran["shipper_name"], 
                "class='tableheader2'");
        label_cells(_("Sales Type"), $this->tran["sales_type"], 
                "class='tableheader2'");
        end_row();
        start_row();
        label_cells(_("Invoice Date"), sql2date($this->tran["tran_date"]), 
                "class='tableheader2'", "nowrap");
        label_cells(_("Due Date"), sql2date($this->tran["due_date"]), 
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

    private function items ()
    {
        $result = get_customer_trans_details(ST_SALESINVOICE, $this->tran_no);
        
        start_table(TABLESTYLE, "width=95%");
        // bug($this->tran);die;
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
            while ($this->tran2 = db_fetch($result)) {
                
                // $tax =
                // tax_calculator($this->tran2['tax_type_id'],$this->tran2['unit_price']*$this->tran2['quantity']*(1-$this->tran2['discount_percent']),$this->tran['tax_included']);
                $tax = tax_calculator($this->tran2['tax_type_id'], 
                        $this->tran2['unit_price'] * $this->tran2['quantity'] *
                                 (1 - $this->tran2['discount_percent']), 
                                $this->tran['tax_included']);
                
                if (! array_key_exists($tax->id, $item_taxes)) {
                    $item_taxes[$tax->id] = array(
                            'name' => $tax->name,
                            'rate' => $tax->rate,
                            'value' => 0,
                            'no' => $tax->code
                    );
                }
                $item_taxes[$tax->id]['value'] += $tax->value;
                
                if ($this->tran2["quantity"] == 0)
                    continue;
                alt_table_row_color($k);
                
                $value = round2(
                        ((1 - $this->tran2["discount_percent"]) *
                                 $this->tran2["unit_price"] *
                                 $this->tran2["quantity"]), user_price_dec());
                $sub_total += $value;
                
                if ($this->tran2["discount_percent"] == 0) {
                    $display_discount = "";
                } else {
                    $display_discount = percent_format(
                            $this->tran2["discount_percent"] * 100) . "%";
                }
                
                label_cell($this->tran2["stock_id"]);
                label_cell($this->tran2["StockDescription"]);
                qty_cell($this->tran2["quantity"], false, 
                        get_qty_dec($this->tran2["stock_id"]));
                label_cell($this->tran2["units"], "align=right");
                amount_cell($this->tran2["unit_price"]);
                label_cell($display_discount, "nowrap align=right");
                // amount_cell($value);
                label_cell(number_total($value), "nowrap align=right ");
                end_row();
            } // end while there are line items to print out
            
            $display_sub_tot = number_format2($sub_total, user_amount_dec());
            label_row(_("Sub-total"), $display_sub_tot, "colspan=6 align=right", 
                    "nowrap align=right width=15%");
        } else
            display_note(_("There are no line items on this invoice."), 1, 2);
        
        $display_freight = price_format($this->tran["ov_freight"]);
        
        /* Print out the invoice text entered */
        label_row(_("Shipping"), $display_freight, "colspan=6 align=right", 
                "nowrap align=right");
        label_row(_("Discount Given"), 
                number_format2($this->tran["ov_discount"], user_amount_dec()), 
                "colspan=6 align=right", "nowrap align=right");
        // $tax_items = get_trans_tax_details(ST_SALESINVOICE, $trans_id);
        // display_customer_trans_tax_details($tax_items, 6);
        // display_supp_trans_tax_details2();
        // bug($item_taxes);die;
        foreach ($item_taxes as $tax) {
            $value = number_total($tax['value']);
            if ($this->tran['tax_included']) {
                label_row(
                        _("Included") . " " . $tax['name'] . " " . $tax['no'] .
                                 "(" . $tax['rate'] . "%) " . ": $value ", '', 
                                "colspan=6 align=right", "align=right");
            } else {
                label_row(
                        $tax['name'] . " (" . $tax['rate'] . "%) " . $tax['no'], 
                        $value, "colspan=6 align=right", "align=right");
            }
        }
        // bug($this->tran);
        // bug($this->tran);die;
        $display_total = number_format2(
                $this->tran["ov_freight"] + $this->tran["ov_gst"] +
                         $this->tran["ov_amount"] + $this->tran["ov_freight_tax"], 
                        user_amount_dec());
        
        label_row(_("TOTAL INVOICE"), $display_total, "colspan=6 align=right", 
                "nowrap align=right");
        end_table(1);
    }
}