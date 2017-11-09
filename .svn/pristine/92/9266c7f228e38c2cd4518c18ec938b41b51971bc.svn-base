<?php

class PurchasesDetailInvoice
{

    var $tran_no = 0;

    var $tran_type = ST_SUPPINVOICE;

    function __construct ()
    {
        $this->check_tran_no();
    }

    private function check_tran_no ()
    {
        if (isset($_GET["trans_no"])) {
            $this->tran_no = $_GET["trans_no"];
        } elseif (isset($_POST["trans_no"])) {
            $this->tran_no = $_POST["trans_no"];
        }
        
        $this->trans = new supp_trans($this->tran_type);
        read_supp_invoice($this->tran_no, $this->tran_type, $this->trans);
        $this->supplier_curr_code = get_supplier_currency(
                $this->trans->supplier_id);
    }

    function view ()
    {
        box_start(_("SUPPLIER INVOICE") . " # " . $this->tran_no);
        $this->tran_header();
        
        
        if ($this->tran_type == ST_SUPPINVOICE){
            box_start(_("Received Items Charged on this Invoice"));
        }
        else {
            box_start(_("Received Items Credited on this Note"));
        }
        $this->tran_items();
        box_end();
        
        $voided = is_voided_display(ST_SUPPINVOICE, $this->tran_no, 
                _("This invoice has been voided."));
        
        if (! $voided) {
            display_allocations_to(PT_SUPPLIER, $this->trans->supplier_id, 
                    ST_SUPPINVOICE, $this->tran_no, 
                    ($this->trans->ov_amount + $this->trans->ov_gst));
        }
    }

    private function tran_header ()
    {
        if( !isMobile() ){
            bootstrap_set_label_column(5);
        }
        row_start();
        col_start(12,'col-md-4');
            input_label(_("Supplier"),NULL,$this->trans->supplier_name);
            input_label(_("Invoice Date"),NULL,$this->trans->tran_date);
        col_start(12,'col-md-4');
            input_label(_("Reference"),NULL,$this->trans->reference);
            input_label(_("Due Date"),NULL,$this->trans->due_date);
        col_start(12,'col-md-4');
            input_label(_("Supplier's Reference"),NULL,$this->trans->supp_reference);
            if (! is_company_currency($this->supplier_curr_code)){
                input_label(_("Currency"),NULL,$this->supplier_curr_code);
            }
        col_start(12);
            comments_display(ST_SUPPINVOICE, $this->tran_no);
        row_end();

    }

    private function tran_items ()
    {
        $total_gl = display_gl_items($this->trans, 2);
        
        $total_grn = display_grn_items($this->trans, 2,false);
        
        $display_sub_tot = number_format2($total_gl + $total_grn, 
                user_price_dec());
        
        start_table(TABLESTYLE);
        label_row(_("Sub Total"), $display_sub_tot, "align=right", 
                "nowrap align=right width=15%");
        
        // $tax_items = get_trans_tax_details(ST_SUPPINVOICE, $trans_no);
        // display_supp_trans_tax_details($tax_items, 1);
        display_supp_trans_tax_details2($this->trans, 1);
        $display_total = number_format2(
                $this->trans->ov_amount + $this->trans->ov_gst, user_price_dec());
        label_row(_("TOTAL INVOICE"), $display_total, "colspan=1 align=right", 
                "nowrap align=right");
        end_table(1);
    }
}