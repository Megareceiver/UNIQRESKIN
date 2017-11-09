<?php

class BankTransfer
{

    function __construct()
    {
        // $this->dim = get_company_pref('use_dimension');
        $this->page_finish();
        $this->input_submit();
    }

    function form(){
        global $Refs;
        $home_currency = get_company_currency();

        start_form();

        box_start();
        row_start();
        col_start(12,'col-md-6');
        bank_accounts(_("From Account"), 'FromBankAccount', null, true);
        bank_accounts(_("To Account"), 'ToBankAccount', null, true);

        if (!isset($_POST['DatePaid'])) { // init page
            $_POST['DatePaid'] = new_doc_date();
            if (!is_date_in_fiscalyear($_POST['DatePaid']))
                $_POST['DatePaid'] = end_fiscalyear();
        }

        input_date_bootstrap(_("Transfer Date"), 'DatePaid',NULL, $disabled = false, true);
        input_ref(_("Reference"), 'ref', $Refs->get_next(ST_BANKTRANSFER));

        col_start(12,'col-md-6');
        bank_balance_label($_POST['FromBankAccount']);
        $from_currency = get_bank_account_currency($_POST['FromBankAccount']);
        $to_currency = get_bank_account_currency($_POST['ToBankAccount']);
        if ($from_currency != "" && $to_currency != "" && $from_currency != $to_currency)
        {

            input_money(_("Amount"), 'amount',null,$from_currency);
            input_money(_("Bank Charge"), 'charge',null,$from_currency);
            input_money(_("Incoming Amount"), 'target_amount',null,$to_currency);

//             amount_row(_("Amount:"), 'amount', null, null, $from_currency);
//             amount_row(_("Bank Charge:"), 'charge', null, null, $from_currency);
//             amount_row(_("Incoming Amount:"), 'target_amount', null, '', $to_currency, 2);
        }
        else
        {
            input_money(_("Amount"), 'amount',null,$from_currency);
            input_money(_("Bank Charge"), 'charge',null,$from_currency);

//             amount_row(_("Amount:"), 'amount');
//             amount_row(_("Bank Charge:"), 'charge');
        }

        input_textarea(_("Memo"), 'memo_');

        row_end();
        box_footer_start();
//         submit_center('AddPayment',_("Enter Transfer"), true, '', 'default');
        submit('AddPayment',_("Enter Transfer"), true, '', 'default','save');
        box_footer_end();
        box_end();
        end_form();
    }

    private function input_submit()
    {
        if (isset($_POST['_DatePaid_changed'])) {
            $Ajax->activate('_ex_rate');
        }
        if (isset($_POST['AddPayment'])) {
            if (check_valid_entries() == true) {
                handle_add_deposit();
            }
        }
    }

    private function page_finish()
    {
        if (isset($_GET['AddedID'])) {
            $trans_no = $_GET['AddedID'];
            $trans_type = ST_BANKTRANSFER;

            display_notification(_("Transfer has been entered"));
            box_start();
            row_start('justify-content-center');
            col_start(6);
            mt_list_start('Actions', '', 'blue');

            mt_list_print(_("&Print This Transfer"), ST_BANKTRANSFER, $trans_no);

            mt_list_gl_view( _("&View the GL Journal Entries for this Transfer"),$trans_type, $trans_no);
            mt_list_hyperlink($_SERVER['PHP_SELF'], _("Enter &Another Transfer"));

            row_end();
            box_footer();
            box_end();
            display_footer_exit();
        }
    }
}