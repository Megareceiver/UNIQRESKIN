<?php
class PurchasesTranCredit
{

    function __construct()
    {
        $this->check_input_get();
        $this->check_submit();

        $this->invoice = module_control_load('invoice','purchases');
    }

    function index()
    {}

    function form()
    {
        start_form();
        box_start();

        $this->invoice->cart = $_SESSION['supp_trans'];
        $this->invoice->form_header();

        if ($_POST['supplier_id']=='')
            display_error('No supplier found for entered search text');
        else {
            $this->invoice->mode = 1;

            $total_grn_value = $this->invoice->grn_items();
            $total_gl_value = $this->invoice->gl_items();

            div_start('inv_tot');
//             $this->invoice->form_total();
//             invoice_totals($_SESSION['supp_trans']);
            div_end();
        }

        box_footer_start();
        submit('PostCreditNote', _("Enter Credit Note"), true, '', true);
        box_footer_end();


        box_end();
        end_form();
    }

    private function credit_finish()
    {
        $invoice_no = $_GET['AddedID'];
        $trans_type = ST_SUPPCREDIT;
        
        display_notification_centered(_("Supplier credit note has been processed."));
        
        box_start();
        row_start();
        col_start(12);
        
        mt_list_start('Actions', '', 'blue');
        mt_list_tran_view(_("&View this Credit Note"),$trans_type, $invoice_no);
        mt_list_gl_view( _("View the GL Journal Entries for this Credit Note"),$trans_type, $invoice_no);
        mt_list_hyperlink($_SERVER['PHP_SELF'], _("Enter Another Credit Note"), "New=1");
        mt_list_hyperlink("admin/attachments.php", _("Add an Attachment"), "filterType=$trans_type&trans_no=$invoice_no");
//         display_note(get_gl_view_str($trans_type, $invoice_no, _("View the GL Journal Entries for this Credit Note")), 1);

//         hyperlink_params($_SERVER['PHP_SELF'], _("Enter Another Credit Note"), "New=1");
//         hyperlink_params("$path_to_root/admin/attachments.php", _("Add an Attachment"), "filterType=$trans_type&trans_no=$invoice_no");
        row_end();
        box_footer();
        box_end();
        display_footer_exit();
    }

    private function check_input_get()
    {
        if (isset($_GET['AddedID'])) {
            $this->credit_finish();
        }

        if (isset($_GET['New'])) {
            if (isset($_SESSION['supp_trans'])) {
                unset($_SESSION['supp_trans']->grn_items);
                unset($_SESSION['supp_trans']->gl_codes);
                unset($_SESSION['supp_trans']);
            }

            $_SESSION['supp_trans'] = new supp_trans(ST_SUPPCREDIT);
            if (isset($_GET['invoice_no'])) {
                $_SESSION['supp_trans']->supp_reference = $_POST['invoice_no'] = $_GET['invoice_no'];
            }
        }
    }

    private function check_submit()
    {
        global $Ajax;
        if (isset($_POST['ClearFields'])) {
            clear_fields();
        }

        // GL postings are often entered in the same form to two accounts
        // so fileds are cleared only on user demand.
        //
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
                $_SESSION['supp_trans']->add_gl_codes_to_trans($_POST['tax_id'], $_POST['gl_code'], $gl_act_name, $_POST['dimension_id'], $_POST['dimension2_id'], input_num('amount'), $_POST['memo_']);
                reset_tax_input();
                set_focus('gl_code');
            }
        }

        if (isset($_POST['PostCreditNote'])) {
            handle_commit_credit_note();
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
        if (isset($_POST['RefreshInquiry'])) {
            $Ajax->activate('grn_items');
            reset_tax_input();
        }

        if (isset($_POST['go'])) {
            $Ajax->activate('gl_items');
            display_quick_entries($_SESSION['supp_trans'], $_POST['qid'], input_num('totamount'), QE_SUPPINV);
            $_POST['totamount'] = price_format(0);
            $Ajax->activate('totamount');
            reset_tax_input();
        }

        if ($id != -1){
            $Ajax->activate('grn_items');
            $Ajax->activate('inv_tot');
        }
        if (get_post('AddGLCodeToTrans'))
            $Ajax->activate('inv_tot');
    }
}