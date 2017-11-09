<?php

class PurchasesTranInvoice
{

    function __construct()
    {
        $this->check_input_get();
        $this->check_submit();

        $this->invoice = module_control_load('invoice', 'purchases');
        $this->document_add();
    }

    function form(){

        start_form();
        box_start();
        $this->invoice->cart = &$_SESSION['supp_trans'];

        $this->invoice->form_header();

        if ($_POST['supplier_id']=='')
            display_error(_("There is no supplier selected."));
        else {
            $this->invoice->mode = 1;
            $this->invoice->grn_items();
            $this->invoice->gl_items();
            div_start('inv_tot');
//             invoice_totals($_SESSION['supp_trans']);
            div_end();

        }


        box_footer_start();
        if( intval( $document_id = input_get('document')) > 0 ){
            hidden('document_id',$document_id);
        }
        submit('PostInvoice', _("Enter Invoice"), true, '', 'default');
//         submit('PostInvoice', _("Enter Invoice"), true, '', false);
        box_footer_end();
        box_end();
        end_form();
    }


    private function invoice_add_success(){
        $invoice_no = $_GET['AddedID'];
        $trans_type = ST_SUPPINVOICE;
        display_notification_centered(_("Supplier invoice has been processed."));

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');

        mt_list_link( _("Entry supplier &payment for this invoice"), "/purchasing/supplier_payment.php?PInvoice=$invoice_no");
        mt_list_link(_("Enter Another Invoice"), $_SERVER['PHP_SELF']."?New=1");
        mt_list_link( _("Add an Attachment"), "/admin/attachments.php?filterType=$trans_type&trans_no=$invoice_no");

        col_start(6);
        mt_list_start('Printing', null, 'red');

        mt_list_tran_view(_("View this Invoice"),$trans_type, $invoice_no);
        mt_list_gl_view(_("View the GL Journal Entries for this Invoice"),$trans_type, $invoice_no);


        row_end();
        box_footer();
        box_end();
    }
    private function check_input_get()
    {
        if (isset($_GET['AddedID'])) {

            $this->invoice_add_success();
            display_footer_exit();
        } else
            if (input_get('reinvoice')) {
                global $ci;
                $supplier_model = $ci->model('supplier',true);
                
                $trans_no = clean_val(input_get('reinvoice'));

                $_SESSION['supp_trans'] = new supp_trans(ST_SUPPINVOICE);

                read_supp_invoice($trans_no, ST_SUPPINVOICE, $_SESSION['supp_trans']);

                $supplier_model->supplier_invoice($_SESSION['supp_trans']);

                // $invoice_no = add_supp_invoice($_SESSION['supp_trans']);
 
                $_SESSION['supp_trans']->clear_items();
                unset($_SESSION['supp_trans']);

                meta_forward("/gl/view/gl_trans_view.php?type_id=" . ST_SUPPINVOICE . "&trans_no=" . $trans_no);
            }

        // --------------------------------------------------------------------------------------------------

        if (isset($_GET['New'])) {
            if (isset($_SESSION['supp_trans'])) {
                unset($_SESSION['supp_trans']->grn_items);
                unset($_SESSION['supp_trans']->gl_codes);
                unset($_SESSION['supp_trans']);
            }

            $_SESSION['supp_trans'] = new supp_trans(ST_SUPPINVOICE);
        }
    }

    private function check_submit()
    {
        global $Ajax;
        // ------------------------------------------------------------------------------------------------
        // GL postings are often entered in the same form to two accounts
        // so fileds are cleared only on user demand.
        //
        if (isset($_POST['ClearFields'])) {
            clear_fields();
        }

        if (isset($_POST['AddGLCodeToTrans'])) {

            $Ajax->activate('gl_items');
            $input_error = false;

            $result = get_gl_account_info($_POST['gl_code']);
            if (db_num_rows($result) == 0) {
                display_error(_("The account code entered is not a valid code, this line cannot be added to the transaction."));
                set_focus('gl_code');
                $input_error = true;
            } else {
                $myrow = db_fetch_row($result);
                $gl_act_name = $myrow[1];
                if (! check_num('amount')) {
                    display_error(_("The amount entered is not numeric. This line cannot be added to the transaction."));
                    set_focus('amount');
                    $input_error = true;
                }
            }

            if (! is_tax_gl_unique(get_post('gl_code'))) {
                display_error(_("Cannot post to GL account used by more than one tax type."));
                set_focus('gl_code');
                $input_error = true;
            }

            if ($input_error == false) {
                $_SESSION['supp_trans']->add_gl_codes_to_trans(input_post('tax_id'), $_POST['gl_code'], $gl_act_name, $_POST['dimension_id'], $_POST['dimension2_id'], input_num('amount'), $_POST['memo_']);
                reset_tax_input();
                set_focus('gl_code');
            }
            clear_fields();
        }

        if (isset($_POST['PostInvoice'])) {
            handle_commit_invoice();
        }

        $id = find_submit('grn_item_id');
        if ($id != - 1) {
            commit_item_data($id);
        }

        if (isset($_POST['InvGRNAll'])) {
            foreach ($_POST as $postkey => $postval) {
                if (strpos($postkey, "qty_recd") === 0) {
                    $id = substr($postkey, strlen("qty_recd"));
                    $id = (int) $id;
                    commit_item_data($id);
                }
            }
        }

        // --------------------------------------------------------------------------------------------------
        $id3 = find_submit('Delete');
        if ($id3 != - 1) {
            $_SESSION['supp_trans']->remove_grn_from_trans($id3);
            $Ajax->activate('grn_items');
            reset_tax_input();
        }

        $id4 = find_submit('Delete2');
        if ($id4 != - 1) {
            $_SESSION['supp_trans']->remove_gl_codes_from_trans($id4);
            clear_fields();
            reset_tax_input();
            $Ajax->activate('gl_items');
        }

        $id2 = - 1;
        if ($_SESSION["wa_current_user"]->can_access('SA_GRNDELETE')) {
            $id2 = find_submit('void_item_id');
            if ($id2 != - 1) {
                remove_not_invoice_item($id2);
                display_notification(sprintf(_('All yet non-invoiced items on delivery line # %d has been removed.'), $id2));
            }
        }

        if (isset($_POST['go'])) {
            $Ajax->activate('gl_items');
            display_quick_entries($_SESSION['supp_trans'], $_POST['qid'], input_num('totamount'), QE_SUPPINV);
            $_POST['totamount'] = price_format(0);
            $Ajax->activate('totamount');
            reset_tax_input();
        }

        //-----------------------------------------------------------------------------------------
        if ($id != -1 || $id2 != -1){
            $Ajax->activate('grn_items');
            $Ajax->activate('inv_tot');
        }

        if (get_post('AddGLCodeToTrans')){
            $Ajax->activate('inv_tot');
        }
            
    }

    private function document_add(){
        $document_id = input_get('document');
        if( is_numeric($document_id) AND !in_ajax() ){
            $mobile_model = module_model_load('mobile','documents');
            $row = $mobile_model->item($document_id);
            $data = unserialize($row->data);
            if( !empty($data) ){
                $_POST['reference']     = $data['ref'];
                $_SESSION['supp_trans']->reference = $data['ref'];

                $_POST['supplier_id']   = $data['supplier_id'];
                $_SESSION['supp_trans']->supplier_id = $data['supplier_id'];

                $expense_model = module_model_load('expense','admin');
                $expense = $expense_model->get_row($data['expense_id']);
                if( is_object($expense) AND !empty($expense) ){
                    $_SESSION['supp_trans']->add_gl_codes_to_trans(0, $expense->gl_account, $expense->gl_description, 0, 0, $data['supplier_amount'], $data['supplier_bill_no']);
                }
            }
//             bug($data);
        }
    }
}