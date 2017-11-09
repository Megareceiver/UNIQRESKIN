<?php

class SalesInquiryRecurrentInvoice
{

    function __construct()
    {
        $this->get_val();
        $this->ajax_updates();
    }

    function view()
    {
        $result = get_recurrent_invoices();
        start_form();
        box_start();
        // start_table(TABLESTYLE_NOBORDER);
        // start_row();
        row_start('justify-content-center');
        col_start(6);
        input_date_bootstrap(_("Invoice date"), 'date', '');
        row_end();

        start_table(TABLESTYLE);
        $th = array(
            _("Description"),
            _("Template No"),
            _("Customer"),
            _("Branch") . "/" . _("Group"),
            _("Days"),
            _("Monthly"),
            _("Begin"),
            _("End"),
            _("Last Created"),
            ""
        );
        table_header($th);
        $k = 0;
        $today = add_days($_POST['date'], 1);
        $due = false;
        while ($myrow = db_fetch($result)) {
            $begin = sql2date($myrow["begin"]);
            $end = sql2date($myrow["end"]);
            $last_sent = calculate_from($myrow);
            $due_date = add_months($last_sent, $myrow['monthly']);
            $due_date = add_days($due_date, $myrow['days']);

            $overdue = date1_greater_date2($today, $due_date) && date1_greater_date2($today, $begin) && date1_greater_date2($end, $today);
            if ($overdue) {
                start_row("class='overduebg'");
                $due = true;
            } else
                alt_table_row_color($k);

            label_cell($myrow["description"]);
            label_cell(get_customer_trans_view_str(30, $myrow["order_no"]));
            if ($myrow["debtor_no"] == 0) {
                label_cell("");
                label_cell(get_sales_group_name($myrow["group_no"]));
            } else {
                label_cell(get_customer_name($myrow["debtor_no"]));
                label_cell(get_branch_name($myrow['group_no']));
            }
            label_cell($myrow["days"]);
            label_cell($myrow['monthly']);
            label_cell($begin);
            label_cell($end);
            label_cell(($myrow['last_sent'] == "0000-00-00") ? "" : $last_sent);
            if ($overdue)
                button_cell("create" . $myrow["id"], _("Create Invoices"), "", ICON_DOC);
            else
                label_cell("");
            end_row();
        }
        end_table();

        if ($due)
            display_notification(_("Marked items are due."));
        else
            display_notification(_("No recurrent invoices are due."));

        box_footer();
        box_end();
        end_form();


    }

    private function get_val()
    {
        if (! isset($_POST['date'])) {
            $_POST['date'] = Today();
        }
    }

    private function ajax_updates()
    {
        $id = find_submit("create");
        if ($id != - 1) {
            $Ajax->activate('_page_body');
            $date = $_POST['date'];
            if (is_date_in_fiscalyear($date)) {
                $invs = array();
                $myrow = get_recurrent_invoice($id);
                $from = calculate_from($myrow);
                $to = add_months($from, $myrow['monthly']);
                $to = add_days($to, $myrow['days']);
                if ($myrow['debtor_no'] == 0) {
                    $cust = get_cust_branches_from_group($myrow['group_no']);
                    while ($row = db_fetch($cust)) {
                        $invs[] = create_recurrent_invoices($row['debtor_no'], $row['branch_code'], $myrow['order_no'], $myrow['id'], $date, $from, $to);
                    }
                } else {
                    $invs[] = create_recurrent_invoices($myrow['debtor_no'], $myrow['group_no'], $myrow['order_no'], $myrow['id'], $date, $from, $to);
                }
                if (count($invs) > 0) {
                    $min = min($invs);
                    $max = max($invs);
                } else
                    $min = $max = 0;
                display_notification(sprintf(_("%s recurrent invoice(s) created, # %s - # %s."), count($invs), $min, $max));
                if (count($invs) > 0) {
                    $ar = array(
                        'PARAM_0' => $min . "-" . ST_SALESINVOICE,
                        'PARAM_1' => $max . "-" . ST_SALESINVOICE,
                        'PARAM_2' => "",
                        'PARAM_3' => 0,
                        'PARAM_4' => 0,
                        'PARAM_5' => "",
                        'PARAM_6' => $def_print_orientation
                    );
                    display_note(print_link(sprintf(_("&Print Recurrent Invoices # %s - # %s"), $min, $max), 107, $ar), 0, 1);
                    $ar['PARAM_3'] = 1; // email
                    display_note(print_link(sprintf(_("&Email Recurrent Invoices # %s - # %s"), $min, $max), 107, $ar), 0, 1);
                }
            } else
                display_error(_("The entered date is not in fiscal year."));
        }
    }
}