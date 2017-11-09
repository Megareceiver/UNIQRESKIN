<?php

class GlTransaction
{

    var $dim = 0;

    var $tran_cart = NULL;

    function __construct()
    {
        $this->check_input_get();
        $this->check_submit();
    }

    function index()
    {}

    function form()
    {
        $this->tran_cart = $_SESSION['journal_items'];
        start_form(true);

        box_start();
        $this->tran_header();

        box_start();
        row_start('justify-content-md-center');
        col_start(12,'col-md-6');
        if( !isMobile() ){
            bootstrap_set_label_column(1);
        }
        
        file_bootstrap('File', 'uploadfile');
        bootstrap_set_label_column(0);
        col_end();
        row_end();

        box_footer_start(null, null, false);
        submit('upload', _("Upload file"), true, '', true);
        box_footer_end();

        box_start(_("Rows"));
        div_start('items_table');
        $this->gl_items();
        div_end();

        box_start();
        row_start('justify-content-md-center');
        col_start(12,'col-md-6');
        input_textarea_bootstrap('Memo', 'memo_');
        col_end();
        row_end();

        box_footer_start();
        submit('Process', _("Process Journal Entry"), true, _('Process journal entry only if debits equal to credits'), 'default');
        box_footer_end();

        box_end();
        end_form();
    }

    private function tran_header()
    {
        global $Ajax;

        $qes = has_quick_entries(QE_JOURNAL);
        $new = ($this->tran_cart->order_id == 0) ? true : false;

        row_start('justify-content-center');
        col_start(12,'col-md-3');
        input_date_bootstrap(_("Date"), 'date_', null, false, true);

        col_start(12,'col-md-4');
        input_ref("Reference", 'ref');
        hidden('ref_original');

        col_start(12,'col-md-3');
        if ($new) {
            if( !isMobile() ){
                bootstrap_set_label_column(7);
            }
            check_bootstrap('Reverse Transaction', 'Reverse');
            bootstrap_set_label_column(0);
        }

        if ($qes !== false) {
            input_quick_entries('Quick Entry', 'person_id', null, 1, true);

            $qid = get_quick_entry(get_post('person_id'));

            if (list_updated('person_id')) {
                unset($_POST['totamount']); // enable default
                $Ajax->activate('totamount');
            }

            $button_input_attributes = array(
                'name' => 'go',
                'id' => 'go',
                'class' => 'input-group-addon ajaxsubmit'
            );

            if ($qid['bal_type'] == 1) {

                $accname = get_gl_account_name($qid['base_desc']);

                $quick_entry_title = $qid['base_amount'] == 0 ? _("Yearly") : _("Monthly");

                $quick_entry_title .= ' balance from account ' . $qid['base_desc'] . " $accname";

                $button_input_attributes['class'] = 'ajaxsubmit btn';
                $button_input = " <button " . _parse_attributes($button_input_attributes) . " >Go</button>";
                input_label_bootstrap(NULL, NULL, $quick_entry_title . $button_input, 0);
            } elseif (! empty($qid)) {
                $button_input = "<button " . _parse_attributes($button_input_attributes) . " >Go</button>";
                input_text_addon_both($qid['base_desc'], 'totamount', price_format($qid['base_amount']), $button_input, '$');
            }
        }
        col_end();
        row_end();
    }

    function check_input_get()
    {
        if (isset($_GET['AddedID'])) {
            $this->journal_add_success();
            display_footer_exit();
        } elseif (isset($_GET['UpdatedID'])) {
            $this->journal_update_success();
            display_footer_exit();
        }
        // --------------------------------------------------------------------------------------------------

        if (isset($_GET['NewJournal'])) {
            create_cart(0, 0);
        } elseif (isset($_GET['ModifyGL'])) {
            if (! isset($_GET['trans_type']) || $_GET['trans_type'] != 0) {
                display_error(_("You can edit directly only journal entries created via Journal Entry page."));
                hyperlink_params("gl/gl_journal.php", _("Entry &New Journal Entry"), "NewJournal=Yes");
                display_footer_exit();
            }
            create_cart($_GET['trans_type'], $_GET['trans_no']);
        }
    }
    private function journal_add_success(){
        $trans_no = $_GET['AddedID'];
        $trans_type = ST_JOURNAL;

        display_notification_centered(_("Journal entry has been entered") . " #$trans_no");

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_link( _("Enter &New Journal Entry"), $_SERVER['PHP_SELF']."NewJournal=Yes");
        mt_list_link(_("Add an Attachment"), "/admin/attachments.php?filterType=$trans_type&trans_no=$trans_no");

        col_start(6);
        mt_list_start('Printing', null, 'red');
        mt_list_gl_view(  _("&View this Journal Entry"), $trans_type, $trans_no );
        reset_focus();

        row_end();
        box_footer();
        box_end();

    }
    private function journal_update_success(){
        $trans_no = $_GET['UpdatedID'];
        $trans_type = ST_JOURNAL;

        display_notification_centered(_("Journal entry has been updated") . " #$trans_no");

        box_start();
        row_start();
        col_start(12);

        mt_list_start('Actions', '', 'blue');
        mt_list_gl_view( _("&View this Journal Entry"), $trans_type, $trans_no );
        mt_list_link( _("Return to Journal &Inquiry"), "/gl/inquiry/journal_inquiry.php");

        row_end();
        box_footer();
        box_end();
    }




    private function check_submit()
    {
        global $Ajax;

        $id = find_submit('Delete');
        if ($id != - 1)
            handle_delete_item($id);

        if (isset($_POST['AddItem']))
            handle_new_item();

        if (isset($_POST['UpdateItem']))
            handle_update_item();

        if (isset($_POST['CancelItemChanges']))
            line_start_focus();

        if (isset($_POST['go'])) {
            display_quick_entries($_SESSION['journal_items'], $_POST['person_id'], input_num('totamount'), QE_JOURNAL);
            $_POST['totamount'] = price_format(0);
            $Ajax->activate('totamount');
            line_start_focus();
        }
        // TUANVT100
        if (get_post('upload')) {
            $tmpname = $_FILES['uploadfile']['tmp_name'];
            $fname = trim(basename($_FILES['uploadfile']['name']));

            if ($fname) {
                if (is_uploaded_file($tmpname)) {
                    rename($tmpname, BACKUP_PATH . $fname);
                    display_notification(_("File uploaded to backup directory"));
                    $Ajax->activate('backups');
                    $filename = BACKUP_PATH . $fname;
                    $data = new Spreadsheet_Excel_Reader($filename, true, "UTF-8"); // khoi tao doi tuong doc file excel
                    $rowsnum = $data->rowcount($sheet_index = 0); // lay so hang cua sheet
                    $colsnum = $data->colcount($sheet_index = 0); // lay so cot cua sheet
                    for ($i = 2; $i <= $rowsnum; $i ++) {
                        if ($data->val($i, 4) == "") {
                            if ($data->val($i, 5) == "") {
                                display_error(_("You must enter either a debit amount or a credit amount."));
                                return false;
                            } else {
                                if (! is_numeric($data->val($i, 5))) {
                                    display_error(_("The debit amount entered is not a valid number or is less than zero."));
                                    return false;
                                } else {
                                    $amount = - ($data->val($i, 5));
                                }
                            }
                        } else {
                            if ($data->val($i, 5) == "") {
                                if (! is_numeric($data->val($i, 4))) {
                                    display_error(_("The credit amount entered is not a valid number or is less than zero."));
                                    return false;
                                } else {
                                    $amount = $data->val($i, 4);
                                }
                            } else {
                                display_error(_("You only enter debit amount or a credit amount."));
                                return false;
                            }
                        }
                        // display_error("CCC:".$data->val($i,1));
                        // display_error("CCC:".$data->val($i,3));
                        // display_error("CCC:".$amount);
                        // display_error("CCC:".$data->val($i,6));
                        $_SESSION['journal_items']->add_gl_item($data->val($i, 1), $data->val($i, 3), $data->val($i, 3), $amount, $data->val($i, 6), $data->val($i, 2));
                    }
                    line_start_focus();
                } else
                    display_error(_("File was not uploaded into the system."));
            } else
                display_error(_("Select backup file first."));
        }
    }

    private function gl_items()
    {
        $this->dim = $dim = get_company_pref('use_dimension');

        start_table(TABLESTYLE);
        if ($dim == 2)
            $th = array(
                _("Account Code"),
                _("Account Description"),
                _("Dimension") . " 1",
                _("Dimension") . " 2",
                _("Debit"),
                _("Credit"),
                _("Memo"),
                ""
            );
        else
            if ($dim == 1)
                $th = array(
                    _("Account Code"),
                    _("Account Description"),
                    _("Dimension"),
                    _("Debit"),
                    _("Credit"),
                    _("Memo"),
                    ""
                );
            else
                $th = array(
                    _("Account Code"),
                    _("Account Description"),
                    _("Debit"),
                    _("Credit"),
                    _("Memo"),
                    ""
                );

//         if (count($this->tran_cart->gl_items))
            $th[] = '';

        table_header($th);

        $k = 0;

        $id = find_submit('Edit');
        foreach ($this->tran_cart->gl_items as $line => $item) {
            if ($id != $line) {
                alt_table_row_color($k);

                // label_cells($item->code_id, $item->description);
                label_cells($item->code_id, get_gl_account_name($item->code_id));

                if ($dim >= 1)
                    label_cell(get_dimension_string($item->dimension_id, true));
                if ($dim > 1)
                    label_cell(get_dimension_string($item->dimension2_id, true));

                if ($item->amount > 0) {
                    amount_cell(abs($item->amount));
                    label_cell("");
                } else {
                    label_cell("");
                    amount_cell(abs($item->amount));
                }
                label_cell($item->description);

//                 edit_button_cell("Edit$line", _("Edit"), _('Edit journal line'));
//                 delete_button_cell("Delete$line", _("Delete"), _('Remove line from journal'));
                tbl_edit("Edit$line");
                tbl_remove("Delete$line");
                end_row();
            } else {
                $this->gl_item_edit($line);
                // gl_edit_item_controls($order, $dim, $line);
            }
        }

        if ($id == - 1) {
            // gl_edit_item_controls($this->tran_cart, $dim);
            $this->gl_item_edit();
        }

        if ($this->tran_cart->count_gl_items()) {
            $colspan = ($dim == 2 ? "4" : ($dim == 1 ? "3" : "2"));
            start_row();
            label_cell(_("Total"), "align=right colspan=" . $colspan);
            amount_cell($this->tran_cart->gl_items_total_debit());
            amount_cell(abs($this->tran_cart->gl_items_total_credit()));
            label_cell('', "colspan=3");
            end_row();
        }

        end_table();
    }

    private function gl_item_edit($Index = -1)
    {
        global $Ajax;
        start_row();

        $id = find_submit('Edit');
        if ($Index != - 1 && $Index == $id) {
            // Modifying an existing row
            $item = $order->gl_items[$Index];
            $_POST['code_id'] = $item->code_id;
            $_POST['dimension_id'] = $item->dimension_id;
            $_POST['dimension2_id'] = $item->dimension2_id;
            if ($item->amount > 0) {
                $_POST['AmountDebit'] = price_format($item->amount);
                $_POST['AmountCredit'] = "";
            } else {
                $_POST['AmountDebit'] = "";
                $_POST['AmountCredit'] = price_format(abs($item->amount));
            }
            $_POST['description'] = $item->description;
            $_POST['LineMemo'] = $item->reference;

            hidden('Index', $id);
            $skip_bank = ! $_SESSION["wa_current_user"]->can_access('SA_BANKJOURNAL');
            gl_all_accounts_list_cells('code_id', null, $skip_bank, true, $all_option = false, $submit_on_change = false, $all = false, $skip_bank_currency = true);

            if ($this->dim >= 1)
                dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
            if ($this->dim > 1)
                dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);
            $Ajax->activate('items_table');
        } else {
            // Adding a new row
            $_POST['AmountDebit'] = ''; // price_format(0);
            $_POST['AmountCredit'] = ''; // price_format(0);
            $_POST['dimension_id'] = 0;
            $_POST['dimension2_id'] = 0;
            // $_POST['LineMemo'] = ""; // let memo go to next line Joe Hunt 2010-05-30
            $_POST['_code_id_edit'] = "";
            $_POST['code_id'] = "";
            if (isset($_POST['_code_id_update'])) {
                $Ajax->activate('code_id');
            }

            $skip_bank = ! $_SESSION["wa_current_user"]->can_access('SA_BANKJOURNAL');
            gl_all_accounts_list_cells('code_id', null, $skip_bank, true, $all_option = false, $submit_on_change = false, $all = false, $skip_bank_currency = true);
            if ($this->dim >= 1)
                dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
            if ($this->dim > 1)
                dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);
        }
        if ($this->dim < 1)
            hidden('dimension_id', 0);
        if ($this->dim < 2)
            hidden('dimension2_id', 0);

        input_money_cells('AmountDebit');
        input_money_cells('AmountCredit');
        text_cells_ex(null, 'LineMemo', 35, 255);

        if ($id != - 1) {
            tbl_update("UpdateItem");
            tbl_cancel("CancelItemChanges");
//             button_cell('UpdateItem', _("Update"), _('Confirm changes'), ICON_UPDATE);
//             button_cell('CancelItemChanges', _("Cancel"), _('Cancel changes'), ICON_CANCEL);
            set_focus('amount');
        } else {
            tbl_add("AddItem");
            label_cell(NULL);
//             submit_cells('AddItem', _("Add Item"), "colspan=2", _('Add new line to journal'), true);
        }


        end_row();
    }
}