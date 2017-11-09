<?php

class SalesAllocate
{

    function __construct()
    {
        $this->check_input_get();
        $this->check_submit();
    }

    function main()
    {
        start_form();
        /* show all outstanding receipts and credits to be allocated */

        if (! isset($_POST['customer_id']))
            $_POST['customer_id'] = get_global_customer();
        box_start();

        row_start('justify-content-center');
        col_start(6);
        customer_list_bootstrap('Customer', 'customer_id', null, true, $editkey = false, $spec_option = true);
        set_global_customer($_POST['customer_id']);
        if (isset($_POST['customer_id']) && ($_POST['customer_id'] == ALL_TEXT)) {
            unset($_POST['customer_id']);
        }

        col_start(3);
        bootstrap_set_label_column(6);
        check_bootstrap('Show Settled Items', 'ShowSettled', null, true);
        row_end();

        /*
         * if (isset($_POST['customer_id'])) {
         * $custCurr = get_customer_currency($_POST['customer_id']);
         * if (!is_company_currency($custCurr))
         * echo _("Customer Currency:") . $custCurr;
         * }
         */

        $settled = false;
        if (check_value('ShowSettled'))
            $settled = true;

        $customer_id = null;
        if (isset($_POST['customer_id']))
            $customer_id = $_POST['customer_id'];

        $sql = get_allocatable_from_cust_sql($customer_id, $settled);
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
            _("Customer") => array(
                'ord' => ''
            ),
            _("Currency") => array(
                'align' => 'center'
            ),
            _("Total") => 'amount',
            _("Left to Allocate") => array(
                'align' => 'right',
                'insert' => true,
                'fun' => 'amount_left'
            ),
            'alloc' => array(
                'label'=>'',
                'insert' => true,
                'fun' => 'alloc_link',
                'align' => 'center',
            )
        );

        if (isset($_POST['customer_id'])) {
            $cols[_("Customer")] = 'skip';
            $cols[_("Currency")] = 'skip';
        }

        $table = & new_db_pager('alloc_tbl', $sql, $cols);
        $table->set_marker('check_settled', _("Marked items are settled."), 'settledbg', 'settledfg');

        $table->ci_control = $this;
        // $table->width = "75%";

        display_db_pager($table);
        box_footer();
        box_end();
        end_form();
    }

    function alloc_link($row)
    {
        return pager_link(_("Allocate"), "sales/allocations/customer_allocate.php?trans_no=" . $row["trans_no"] . "&trans_type=" . $row["type"] . "&debtor_no=" . $row["debtor_no"], ICON_ALLOC);
    }

    private function check_input_get()
    {}

    private function check_submit()
    {}
}