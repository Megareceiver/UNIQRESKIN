<?php

class BankInquiryTrans
{

    function __construct()
    {
        $this->check_value_default();
        $this->check_input_get();
        $this->check_submit();
    }

    function index()
    {}

    function view()
    {
        box_start();
        $this->filter();
        box_footer_start();
        box_footer_end();
        box_end();

        if (! isset($_POST['bank_account']))
            $_POST['bank_account'] = "";

        div_start('trans_tbl');

        $act = get_bank_account($_POST["bank_account"]);
        // display_heading($act['bank_account_name']." - ".$act['bank_curr_code']);
        box_start($act['bank_account_name'] . " - " . $act['bank_curr_code']);
        $this->items();
        box_end();

        div_end();
    }

    private function filter()
    {
        start_form();
        row_start();
        col_start(12,'col-md-5');
        bank_accounts(_("Account"), 'bank_account', null);

        col_start(12,'col-md-3');
        input_date_bootstrap(_("From"), 'TransAfterDate', null, false, false, - 30);

        col_start(12,'col-md-3');
        input_date_bootstrap(_("To"), 'TransToDate');

        col_start(12,'col-md-1 offset-md-0 offset-3');
        submit('Show', _("Show"), true, '', 'default', 'search');

        row_end();
        end_form();
    }

    private function items()
    {
        global $systypes_array;
        $result = get_bank_trans_for_bank_account(input_val('bank_account'), input_val('TransAfterDate'), input_val('TransToDate'));

        start_table(TABLESTYLE);

        $th = array(
            _("Type"),
            _("#"),
            _("Reference"),
            _("Date"),
            _("Debit"),
            _("Credit"),
            _("Balance"),
            _("Person/Item"),
            _("Memo"),
            'gl'=>array(
                'class'=>'text-center',

            )
        );
        table_header($th);

        $bfw = get_balance_before_for_bank_account(input_val('bank_account'), input_val('TransAfterDate'));

        $credit = $debit = 0;
        start_row("class='inquirybg' style='font-weight:bold'");
        label_cell(_("Opening Balance") . " - " . input_val('TransAfterDate'), "colspan=4");
        display_debit_or_credit_cells($bfw);
        label_cell("");
        label_cell("", "colspan=2");

        end_row();
        $running_total = $bfw;
        if ($bfw > 0)
            $debit += $bfw;
        else
            $credit += $bfw;
        $j = 1;
        $k = 0; // row colour counter
        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);

            $running_total += $myrow["amount"];

            $trandate = sql2date($myrow["trans_date"]);

            label_cell($systypes_array[$myrow["type"]]);
            // $ref_link = get_trans_view_str($myrow["type"],$myrow["trans_no"]);
            $ref_link = trans_view_anchor($myrow["type"], $myrow["trans_no"]);
            label_cell($ref_link);

            $link = trans_view_anchor($myrow["type"], $myrow["trans_no"], $myrow['ref']);
            // label_cell(get_trans_view_str($myrow["type"],$myrow["trans_no"],$myrow['ref']));
            label_cell($link);

            label_cell($trandate);
            display_debit_or_credit_cells($myrow["amount"]);
            amount_cell($running_total);
            label_cell(payment_person_name($myrow["person_type_id"], $myrow["person_id"]));
            label_cell(get_comments_string($myrow["type"], $myrow["trans_no"]));
            label_cell(get_gl_view_str($myrow["type"], $myrow["trans_no"]),'align="center"');

            end_row();

            if ($myrow["amount"] > 0)
                $debit += $myrow["amount"];
            else
                $credit += $myrow["amount"];

            if ($j == 12) {
                $j = 1;
                table_header($th);
            }
            $j ++;
        }
        // end of while loop

        start_row("class='inquirybg' style='font-weight:bold'");
        label_cell(_("Ending Balance") . " - " . input_val('TransToDate'), "colspan=4");
        amount_cell($debit);
        amount_cell(- $credit);
        // display_debit_or_credit_cells($running_total);
        amount_cell($debit + $credit);
        label_cell("");
        label_cell("", "colspan=2");
        end_row();
        end_table(2);
    }

    private function check_value_default()
    {
        if (isset($_GET['bank_account']))
            $_POST['bank_account'] = $_GET['bank_account'];
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
}