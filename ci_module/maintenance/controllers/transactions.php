<?php

class MaintenanceTransactions
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        start_form(false);

        box_start("Only documents can be printed");
        row_start();
        $this->filter();
        row_end();

        if ($this->check_valid_entries() == true) {
            row_start(null, 'style="padding-top:15px;"');
            $this->listview();
            row_end();
        }

        box_end();
        end_form();
    }

    private function filter()
    {
        col_start(6);
        systypes(_("Type:"), 'filterType', null, true);
        // if (list_updated('filterType'))
        // $selected_id = - 1;

        if (! isset($_POST['FromTransNo']))
            $_POST['FromTransNo'] = "1";
        if (! isset($_POST['ToTransNo']))
            $_POST['ToTransNo'] = "999999";

        col_start(2);
        input_text(_("from"), 'FromTransNo');
        col_start(2);
        input_text(_("to"), 'ToTransNo');
        col_start(2);
        bootstrap_set_label_column(1);
        submit('ProcessSearch', _("Search"), true, '', 'default', 'search');
    }

    function check_valid_entries()
    {
        if (! is_numeric($_POST['FromTransNo']) or $_POST['FromTransNo'] <= 0) {
            display_error(_("The starting transaction number is expected to be numeric and greater than zero."));
            return false;
        }

        if (! is_numeric($_POST['ToTransNo']) or $_POST['ToTransNo'] <= 0) {
            display_error(_("The ending transaction number is expected to be numeric and greater than zero."));
            return false;
        }

        return true;
    }

    private function listview()
    {
        $trans_ref = false;
        $sql = get_sql_for_view_transactions($_POST['filterType'], $_POST['FromTransNo'], $_POST['ToTransNo'], $trans_ref);
        if ($sql == "")
            return;

        $print_type = $_POST['filterType'];
        $print_out = ($print_type == ST_SALESINVOICE || $print_type == ST_CUSTCREDIT || $print_type == ST_CUSTDELIVERY || $print_type == ST_PURCHORDER || $print_type == ST_SALESORDER || $print_type == ST_SALESQUOTE || $print_type == ST_CUSTPAYMENT || $print_type == ST_SUPPAYMENT || $print_type == ST_WORKORDER);

        $cols = array(
            _("#") => array(
                'insert' => true,
                'fun' => 'view_link',
//                 'fun' => 'gl_view',
                'width' => '10%',
                'align' => 'center',
                'class'=>'text-center',
            ),
            _("Reference") => array(
                'fun' => 'ref_view'
            ),
            _("Date") => array(
                'type' => 'date',
                'fun' => 'date_view'
            ),
            _("PRT") => array(
                'insert' => true,
                'fun' => 'prt_link',
                'width' => '5%',
                'align' => 'center',
                'class'=>'text-center',
            ),
            _("GL") => array(
                'insert' => true,
                'fun' => 'gl_view',
                'width' => '5%',
                'align' => 'center',
                'class'=>'text-center',
            )
        );
        if (! $print_out) {
            array_remove($cols, 3);
        }
        if (! $trans_ref) {
            array_remove($cols, 1);
        }

        $table = & new_db_pager('transactions', $sql, $cols);
        display_db_pager($table);
    }
}