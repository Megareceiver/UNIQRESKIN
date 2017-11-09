<?php
/**
 * Created by PhpStorm.
 * User: QuanICT
 * Date: 6/26/2017
 * Time: 5:05 PM
 */
class PurchasesDetailCredit
{

    var $tran_no = 0;
    var $tran_type = ST_SUPPCREDIT;

    function __construct()
    {
        $this->check_tran();
    }

    private function check_tran(){

        if (isset($_GET["trans_no"]))
        {
            $this->tran_no = $_GET["trans_no"];
        }
        elseif (isset($_POST["trans_no"]))
        {
            $this->tran_no = $_POST["trans_no"];
        }

        $this->transaction = new supp_trans($this->tran_type);

        read_supp_invoice($this->tran_no, $this->tran_type, $this->transaction);
    }
    function view ()
    {
        box_start(_("SUPPLIER CREDIT NOTE") . " # " . $this->tran_no);
        $this->tran_header();

        $this->tran_items();
        box_end();

        $voided = is_voided_display($this->tran_type, $this->tran_no, _("This credit note has been voided."));

        if (! $voided) {
            display_allocations_to(PT_SUPPLIER, $this->transaction->supplier_id,
                $this->tran_type, $this->tran_no,
                ($this->transaction->ov_amount + $this->transaction->ov_gst));
        }

    }

    private function tran_header ()
    {
        if( !isMobile() ){
            bootstrap_set_label_column(5);
        }
        row_start();
        col_start(12,'col-md-4');
        input_label(_("Supplier"),NULL,$this->transaction->supplier_name);
        input_label(_("Invoice Date"),NULL,$this->transaction->tran_date);
        col_start(12,'col-md-4');
        input_label(_("Reference"),NULL,$this->transaction->reference);
        input_label(_("Due Date"),NULL,$this->transaction->due_date);
        col_start(12,'col-md-4');
        input_label(_("Supplier's Reference"),NULL,$this->transaction->supp_reference);

        input_label(_("Currency"),NULL,get_supplier_currency($this->transaction->supplier_id) );

        col_start(12);
        comments_display($this->tran_type, $this->tran_no);
        row_end();
    }

    private function tran_items(){

        $tran_detail_view = module_control_load('detail/transaction','purchases');
        $tran_detail_view->cart = $this->transaction;
        $tran_detail_view->tran_no = $this->tran_no;
        $tran_detail_view->tran_type = $this->tran_type;

        $total_gl = display_gl_items($this->transaction, 3);
        //$total_grn = display_grn_items($this->transaction, 2);

        $total_grn = $tran_detail_view->grn_items_box(2);
        $display_sub_tot = number_format2(-$total_gl-$total_grn,user_price_dec());

        start_table(TABLESTYLE, "width=95%");
        label_row(_("Sub Total"), $display_sub_tot, "align=right", "nowrap align=right width=17%");

        //$tax_amount = view_tax_credit_note($this->transaction,1);
        $tax_amount = $tran_detail_view->tax_credit_note(1);

        $display_total = number_format2( -(-$total_gl-$total_grn-$tax_amount),user_price_dec());

        label_row("<font color=red>" . _("TOTAL CREDIT NOTE") . "</font", "<font color=red>$display_total </font>",
            "colspan=1 align=right", "nowrap align=right");

        end_table(1);
    }
}