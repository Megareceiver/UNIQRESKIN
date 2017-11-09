<?php

class GlInquiryAccount
{

    function __construct()
    {
        $this->check_input_get();
        $this->check_submit();
    }

    function index()
    {
        page('General Ledger Inquiry');
        $this->view();
    }

    function view()
    {
        $this->check_value_default();
        $this->filter();

        div_start('trans_tbl');
        if (get_post('Show') || get_post('account')){
            box_start();
            $this->items();
            box_end();
        }


        div_end();
    }

    private function filter()
    {
        $dim = get_company_pref('use_dimension');
        start_form();

        box_start(); row_start();
        
        col_start(12,'col-md-6');
        if( !isMobile() ){
            bootstrap_set_label_column(3);
        }
        
        gl_accounts_bootstrap(_("Account"), 'account', null, false, false, _("All Accounts"));

        if (! isset($_POST["TransFromDate"]) || ! $_POST["TransFromDate"]) {
            $_POST["TransFromDate"] = add_days(Today(), - 30);
        }
        col_start(12,'col-md-3');
        input_date_bootstrap(_("From"), 'TransFromDate');
        col_start(12,'col-md-3');
        input_date_bootstrap(_("To"), 'TransToDate');

        if ($dim >= 1)
            dimensions_list_cells(_("Dimension") . " 1:", 'Dimension', null, true, " ", false, 1);
        if ($dim > 1)
            dimensions_list_cells(_("Dimension") . " 2:", 'Dimension2', null, true, " ", false, 2);

        if( !isMobile() ){
            bootstrap_set_label_column(4);
        }
        col_start(12,'col-md-4');
        input_money('Amount min', 'amount_min');
        col_start(12,'col-md-4');
        input_money('Amount max', 'amount_max');
        bootstrap_set_label_column(0);


        col_end();
        row_end();
        box_footer_start();
        submit('Show', _("Show"), true, '', '', 'default');
        box_footer_end();

        box_end();
        end_form();
    }

    private function items()
    {
        global $path_to_root, $systypes_array;
        $gl_tran_model = module_model_load('trans', 'gl');

        if (! isset($_POST["account"]))
            $_POST["account"] = null;

        $act_name = $_POST["account"] ? get_gl_account_name($_POST["account"]) : "";
        $dim = get_company_pref('use_dimension');

        /* Now get the transactions */
        if (! isset($_POST['Dimension']))
            $_POST['Dimension'] = 0;
        if (! isset($_POST['Dimension2']))
            $_POST['Dimension2'] = 0;

        $result = $gl_tran_model->get_transactions($_POST['TransFromDate'], $_POST['TransToDate'], - 1, $_POST["account"], $_POST['Dimension'], $_POST['Dimension2'], null, input_num('amount_min'), input_num('amount_max'));

        // $result = get_gl_transactions($_POST['TransFromDate'], $_POST['TransToDate'], -1,
        // $_POST["account"], $_POST['Dimension'], $_POST['Dimension2'], null,
        // input_num('amount_min'), input_num('amount_max'));

        $colspan = ($dim == 2 ? "6" : ($dim == 1 ? "5" : "4"));

        if ($_POST["account"] != null)
            display_heading($_POST["account"] . "&nbsp;&nbsp;&nbsp;" . $act_name);

            // Only show balances if an account is specified AND we're not filtering by amounts
        $show_balances = $_POST["account"] != null && input_num("amount_min") == 0 && input_num("amount_max") == 0;

        start_table(TABLESTYLE);

        $first_cols = array(
            _("Type"),
            _("#"),
            _("Date")
        );

        if ($_POST["account"] == null)
            $account_col = array(
                _("Account")
            );
        else
            $account_col = array();

        if ($dim == 2)
            $dim_cols = array(
                _("Dimension") . " 1",
                _("Dimension") . " 2"
            );
        else
            if ($dim == 1)
                $dim_cols = array(
                    _("Dimension")
                );
            else
                $dim_cols = array();

        if ($show_balances)
            $remaining_cols = array(
                _("Person/Item"),
                _("Debit"),
                _("Credit"),
                _("Balance"),
                _("Memo")
            );
        else
            $remaining_cols = array(
                _("Person/Item"),
                _("Debit"),
                _("Credit"),
                _("Memo")
            );

        $th = array_merge($first_cols, $account_col, $dim_cols, $remaining_cols);

        table_header($th);
        if ($_POST["account"] != null && is_account_balancesheet($_POST["account"]))
            $begin = "";
        else {
            $begin = get_fiscalyear_begin_for_date($_POST['TransFromDate']);
            if (date1_greater_date2($begin, $_POST['TransFromDate']))
                $begin = $_POST['TransFromDate'];
            $begin = add_days($begin, - 1);
        }

        $bfw = 0;
        if ($show_balances) {
            $bfw = get_gl_balance_from_to($begin, $_POST['TransFromDate'], $_POST["account"], $_POST['Dimension'], $_POST['Dimension2']);
            start_row("class='inquirybg'");
            label_cell("<b>" . _("Opening Balance") . " - " . $_POST['TransFromDate'] . "</b>", "colspan=$colspan");
            display_debit_or_credit_cells($bfw, true);
            label_cell("");
            label_cell("");
            end_row();
        }

        $running_total = $bfw;
        $j = 1;
        $k = 0; // row colour counter

        // while ($myrow = db_fetch($result))
        foreach ($result as $row) {
            /*
             * 20160510
             * BEGIN dont want to see Supplier OB in bank GL
             */
            if ($row->type == ST_OPENING_SUPPLIER)
                continue;
                /*
             * END hide supplier OB
             */
            alt_table_row_color($k);

            $running_total += $row->amount;

            $trandate = sql2date($row->tran_date);

            label_cell($systypes_array[$row->type]);
            label_cell(get_gl_view_str($row->type, $row->type_no, $row->type_no, true));
            label_cell($trandate);

            if ($_POST["account"] == null)
                label_cell($row->account . ' ' . get_gl_account_name($row->account));

            if ($dim >= 1)
                label_cell(get_dimension_string($row->dimension_id, true));
            if ($dim > 1)
                label_cell(get_dimension_string($row->dimension2_id, true));
            label_cell(payment_person_name($row->person_type_id, $row->person_id));

            display_debit_or_credit_cells($row->amount);
            if ($show_balances)
                amount_cell($running_total);

            if ($row->memo_ == "")
                $row->memo_ = get_comments_string($row->type, $row->type_no);
            label_cell($row->memo_);
            end_row();

            $j ++;
            if ($j == 12) {
                $j = 1;
                table_header($th);
            }
        }
        // end of while loop

        if ($show_balances) {
            start_row("class='inquirybg'");
            label_cell("<b>" . _("Ending Balance") . " - " . $_POST['TransToDate'] . "</b>", "colspan=$colspan");
            display_debit_or_credit_cells($running_total, true);
            label_cell("");
            label_cell("");
            end_row();
        }

        end_table(2);

        if (count($result) == 0){
            display_notification('No general ledger transactions have been created for the specified criteria.');
        }

    }

    private function check_input_get()
    {}

    private function check_submit()
    {
        global $Ajax;

        if (get_post('Show')) {
            $Ajax->activate('trans_tbl');
        }
    }

    private function check_value_default()
    {
        if (isset($_GET["account"]))
            $_POST["account"] = $_GET["account"];

        if (isset($_GET["TransFromDate"]))
            $_POST["TransFromDate"] = $_GET["TransFromDate"];

        if (isset($_GET["TransToDate"]))
            $_POST["TransToDate"] = $_GET["TransToDate"];

        if (isset($_GET["Dimension"]))
            $_POST["Dimension"] = $_GET["Dimension"];
        if (isset($_GET["Dimension2"]))
            $_POST["Dimension2"] = $_GET["Dimension2"];
        if (isset($_GET["amount_min"]))
            $_POST["amount_min"] = $_GET["amount_min"];
        if (isset($_GET["amount_max"]))
            $_POST["amount_max"] = $_GET["amount_max"];

        if (! isset($_POST["amount_min"]))
            $_POST["amount_min"] = price_format(0);
        if (! isset($_POST["amount_max"]))
            $_POST["amount_max"] = price_format(0);

        $_POST["account"] = input_val('account');
    }
}