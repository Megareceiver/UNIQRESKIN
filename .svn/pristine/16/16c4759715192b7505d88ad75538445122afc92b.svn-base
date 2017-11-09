<?php

class GlBudget
{

    function __construct()
    {
        $this->check_submit();
    }

    function index()
    {}

    function form()
    {
        if (! db_has_gl_accounts()) {
            return NULL;
        }

        start_form();
        box_start();
        $this->form_header();
        box_footer_start(null, null, false);
        submit('submit', _("Get"), true, '', true);
        box_footer_end();

        box_start();
        div_start('budget_tbl');
        $this->form_detail();
        div_end();

        box_footer_start();
        echo submit('delete', _("Delete"), '', true);
        echo submit('add', _("Save"), true, '', 'default');
        echo submit('update', _("Update"), '', null);
        box_footer_end();
        box_end();
        end_form();
    }

    private function form_header()
    {
        $this->dim = $dim = get_company_pref('use_dimension');

        row_start('justify-content-md-center');
        col_start(12,'col-md-8');
        fiscalyear_bootstrap(_("Fiscal Year"), 'fyear', null);
        gl_accounts_bootstrap(_("Account Code"), 'account', null);

        if (! isset($_POST['dim1']))
            $_POST['dim1'] = 0;
        if (! isset($_POST['dim2']))
            $_POST['dim2'] = 0;
        if ($dim == 2) {
            dimensions_bootstrap(_("Dimension") . " 1", 'dim1', $_POST['dim1'], true, null, false, 1);
            dimensions_bootstrap(_("Dimension") . " 2", 'dim2', $_POST['dim2'], true, null, false, 2);
        } else
            if ($dim == 1) {
                dimensions_bootstrap(_("Dimension"), 'dim1', $_POST['dim1'], true, null, false, 1);
                hidden('dim2', 0);
            } else {
                hidden('dim1', 0);
                hidden('dim2', 0);
            }
        col_end();
        row_end();
    }

    var $form_detail_table = array(
        'period' => array(
            'label' => 'Period',
            'width' => '15%'
        ),
        'amount' => array(
            'label' => 'Amount',
            'width' => '40%'
        )
    );

    private function form_detail()
    {
        start_table(TABLESTYLE2);
        $showdims = (($this->dim == 1 && $_POST['dim1'] == 0) || ($this->dim == 2 && $_POST['dim1'] == 0 && $_POST['dim2'] == 0));

        if ($showdims) {
            $this->form_detail_table[] = _("Dim. incl.");
        }
        $this->form_detail_table[] = _("Last Year");

        table_header($this->form_detail_table);

        $year = $_POST['fyear'];

        if (get_post('update') == '') {
            $fyear = get_fiscalyear($year);
            $_POST['begin'] = sql2date($fyear['begin']);
            $_POST['end'] = sql2date($fyear['end']);
        }
        hidden('begin');
        hidden('end');

        $total = $btotal = $ltotal = 0;
        for ($i = 0, $date_ = $_POST['begin']; date1_greater_date2($_POST['end'], $date_); $i ++) {
            start_row();
            if (get_post('update') == '') {
                $_POST['amount' . $i] = number_format2(get_only_budget_trans_from_to($date_, $date_, $_POST['account'], $_POST['dim1'], $_POST['dim2']), 0);
            }

            label_cell($date_, 'align=center');
            input_money_cells('amount' . $i);
            // amount_cells(null, 'amount'.$i, null, 15, null, 0);
            if ($showdims) {
                $d = get_budget_trans_from_to($date_, $date_, $_POST['account'], $_POST['dim1'], $_POST['dim2']);
                label_cell(number_format2($d, 0), "nowrap align=right");
                $btotal += $d;
            }
            $lamount = get_gl_trans_from_to(add_years($date_, - 1), add_years(end_month($date_), - 1), $_POST['account'], $_POST['dim1'], $_POST['dim2']);
            $total += input_num('amount' . $i);
            $ltotal += $lamount;
            label_cell(number_format2($lamount, 0), "nowrap align=right");
            $date_ = add_months($date_, 1);
            end_row();
        }

        start_row();
        label_cell("<b>" . _("Total") . "</b>");
        label_cell(number_format2($total, 0), 'align=right style="font-weight:bold"', 'Total');
        if ($showdims)
            label_cell("<b>" . number_format2($btotal, 0) . "</b>", "nowrap align=right");
        label_cell("<b>" . number_format2($ltotal, 0) . "</b>", "nowrap align=right");
        end_row();
        end_table(1);
    }

    private function check_submit()
    {
        global $Ajax;

        if (isset($_POST['add']) || isset($_POST['delete'])) {
            begin_transaction();

            for ($i = 0, $da = $_POST['begin']; date1_greater_date2($_POST['end'], $da); $i ++) {
                if (isset($_POST['add']))
                    add_update_gl_budget_trans($da, $_POST['account'], $_POST['dim1'], $_POST['dim2'], input_num('amount' . $i));
                else
                    delete_gl_budget_trans($da, $_POST['account'], $_POST['dim1'], $_POST['dim2']);
                $da = add_months($da, 1);
            }
            commit_transaction();

            if (isset($_POST['add']))
                display_notification_centered(_("The Budget has been saved."));
            else
                display_notification_centered(_("The Budget has been deleted."));

                // meta_forward($_SERVER['PHP_SELF']);
            $Ajax->activate('budget_tbl');
        }
        if (isset($_POST['submit']) || isset($_POST['update']))
            $Ajax->activate('budget_tbl');
    }
}