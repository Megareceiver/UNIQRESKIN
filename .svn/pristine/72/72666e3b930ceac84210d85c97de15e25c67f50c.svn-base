<?php

class PurchasesAllocate
{

    function __construct()
    {
        $this->check_input_get();
        $this->check_submit();
    }

    function main()
    {
        start_form();
        box_start();

        /* show all outstanding receipts and credits to be allocated */
        $this->main_filter();
        $this->main_items();

        box_footer();
        box_end();
        end_form();
    }

    function form()
    {
        if (isset($_GET['trans_no']) && isset($_GET['trans_type'])) {
            $_SESSION['alloc'] = new allocation($_GET['trans_type'], $_GET['trans_no'], @$_GET['supplier_id'], PT_SUPPLIER);
        }
        if (isset($_SESSION['alloc'])) {
            $this->form_allocation($_SESSION['alloc']->type, $_SESSION['alloc']->trans_no);
        }
    }

    private function form_allocation($type, $trans_no)
    {
        global $systypes_array;
        $cart = $_SESSION['alloc'];

        start_form();div_start('alloc_tbl');
        box_start(_("Allocation of") . " " . $systypes_array[$cart->type] . " # " . $cart->trans_no);

        row_start('justify-content-center');
        col_start(3);
        input_label("Supplier", NULL , $cart->person_name);
        col_start(3);
        input_label("Date", NULL , $cart->date_);
        col_start(3);
        input_label("Total", NULL , price_format(- $cart->bank_amount) . ' ' . $cart->currency );


//         display_heading();

//         display_heading($cart->person_name);

//         display_heading2(_("Date:") . " <b>" . $cart->date_ . "</b>");
//         display_heading2(_("Total:") . " <b>" . price_format(- $cart->bank_amount) . ' ' . $cart->currency . "</b>");

        if ($cart->currency != $cart->person_curr) {
//             $total = _("Total in clearing currency:") . " <b>" ;
//             display_heading2($total);
            col_start(3);
            input_label("Total in clearing currency", NULL , price_format(- $cart->amount) . "</b>" . sprintf(" %s (%s %s/%s)", $cart->person_curr, exrate_format($cart->bank_amount / $cart->amount), $cart->currency, $cart->person_curr) );
        }
//         echo "<br>";
        row_end();




        if (count($cart->allocs) > 0) {
            $this->alloc_cart = module_control_load('cart','allocation');
            $this->alloc_cart->alloca_table(true);
        }

        box_footer_start();

        if (count($cart->allocs) > 0) {
            submit('UpdateDisplay', _("Refresh"),true, _('Start again allocation of selected amount'), true);
            submit('Process', _("Process"), true, _('Process allocations'), 'default');
            submit('Cancel', _("Back to Allocations"),true, _('Abandon allocations and return to selection of allocatable amounts'), true);
        } else {
            display_note(_("There are no unsettled transactions to allocate."), 0, 1);
            submit_center('Cancel', _("Back to Allocations"), true, _('Abandon allocations and return to selection of allocatable amounts'), 'cancel');
        }
        box_footer_end();



        box_end();
        div_end();end_form();
    }

    private function main_filter()
    {
        if (! isset($_POST['supplier_id']))
            $_POST['supplier_id'] = get_global_supplier();

        row_start('justify-content-center');
        col_start(6);
        supplier_list_bootstrap('Select a Supplier', 'supplier_id', $_POST['supplier_id'], true, true);
        set_global_supplier($_POST['supplier_id']);
        if (isset($_POST['supplier_id']) && ($_POST['supplier_id'] == ALL_TEXT)) {
            unset($_POST['supplier_id']);
        }

        col_start(3);
        bootstrap_set_label_column(7);
        check_bootstrap(_("Show Settled Items"), 'ShowSettled', null, true);

        row_end();
    }

    private function main_items()
    {
        $settled = false;
        if (check_value('ShowSettled'))
            $settled = true;

        $sql = get_allocatable_from_supp_sql(input_val('supplier_id'), $settled);
        // bug($sql);die;
        $cols = array(
            _("Transaction Type") => array(
                'fun' => 'systype_name'
            ),
            _("#") => array(
                'fun' => 'trans_view'
            ),
            _("Reference"),
            _("Date") => array(
                'name' => 'tran_date',
                'type' => 'date',
                'ord' => 'asc'
            ),
            _("Supplier") => array(
                'ord' => ''
            ),
            _("Currency") => array(
                'align' => 'center'
            ),
            _("Total") => array(
                'align' => 'right',
                'fun' => 'amount_total'
            ),
            _("Left to Allocate") => array(
                'align' => 'right',
                'insert' => true,
                'fun' => 'amount_left'
            ),
            array(
                'insert' => true,
                'fun' => 'alloc_link',
                'align' => 'center'
            )
        );

        if (isset($_POST['customer_id'])) {
            $cols[_("Supplier")] = 'skip';
            $cols[_("Currency")] = 'skip';
        }

        $table = & new_db_pager('alloc_tbl', $sql, $cols);
        $table->set_marker('check_settled', _("Marked items are settled."), 'settledbg', 'settledfg');

        $table->ci_control = $this;

        display_db_pager($table);
    }

    function amount_left($row)
    {
        return price_format(- $row["Total"] - $row["alloc"]);
    }

    function amount_total($row)
    {
        return price_format(- $row["Total"]);
    }

    function alloc_link($row)
    {
        return anchor("/purchasing/allocations/supplier_allocate.php?trans_no=" . $row["trans_no"] . "&trans_type=" . $row["type"], '<i class="fa fa-chain" ></i>', 'class="button"');
    }

    private function check_submit()
    {
        global $Ajax;
        if (isset($_POST['Process'])) {
            if (check_allocations()) {
                $_SESSION['alloc']->write();
                clear_allocations();
                $_POST['Cancel'] = 1;
            }
        }

        if (isset($_POST['Cancel'])) {
            clear_allocations();
            meta_forward(site_url() . "/purchasing/allocations/supplier_allocation_main.php");
        }
        if (get_post('UpdateDisplay')) {
            $_SESSION['alloc']->read();
            $Ajax->activate('alloc_tbl');
        }
    }

    private function check_input_get()
    {}
}