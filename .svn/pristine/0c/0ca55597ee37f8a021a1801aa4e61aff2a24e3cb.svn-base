<?php

class SalesInquiryTransactions
{

    function __construct()
    {
        $this->page_variables();
        $this->ajax_updates();
        $this->bootstrap = get_instance()->bootstrap;
    }
    function view(){

        if (!@$_GET['popup'])
            start_form();

        box_start();
        $this->fillter();
        $this->customer_totals('totals_tbl');
        $this->transactions_table();

        box_footer();
        box_end();
    }

    function popup(){
        $this->fillter();
        $this->customer_totals('totals_tbl');
        $this->transactions_table();
    }

    private function page_variables()
    {
        $filterType = input_val('filterType');

        if (isset($_GET['filtertype'])) {
            $filterType = $_GET['filtertype'];
        }
        set_post('filterType', $filterType);

        $customer_id = input_val('customer_id');
        if (! $customer_id)
            $customer_id = get_global_customer();

        set_post('customer_id', $customer_id);
        set_global_customer($customer_id);
    }

    private function ajax_updates()
    {
        global $Ajax;
        if(get_post('RefreshInquiry'))
        {
            $Ajax->activate('totals_tbl');
        }
    }

    private function fillter()
    {
        row_start('inquiry-filter justify-content-center');
        if (! @$_GET['popup']) {
            col_start(12,'col-md-3');
            customer_list_bootstrap( _("Customer"), 'customer_id', input_val('customer_id'), false, ! @$_GET['popup']);
        }
        col_start(12,'col-md-2');
        input_date_bootstrap("From", 'TransAfterDate', NULL, false, false, 0, - 1);

        col_start(12,'col-md-2');
        input_date_bootstrap("To", 'TransToDate', NULL);

        col_start(12,'col-md-2');
        $fillter_type_title = isMobile() ? "Tran Type" :NULL;
        cust_allocations_bootstrap( $fillter_type_title, 'filterType', input_val('filterType'), true);

        col_start(12,'col-md-1');
        if( !isMobile() ){
            bootstrap_set_label_column(7);
        }
        
        check_bootstrap('Voided', 'voided');

        col_start(12,'col-md-1');
        submit_bootstrap('RefreshInquiry', _("Search"), _('Refresh Inquiry'), 'default','search');
        row_end();
    }

    private function transactions_table()
    {
        $sql = get_sql_for_customer_inquiry();
        // ------------------------------------------------------------------------------------------------
        db_query("set @bal:=0");

        $cols = array(
            _("Type") => array(
                'fun' => 'systype_name',
                'ord' => ''
            ),
            _("#") => array(
                'fun' => 'tbl_trans_view',
                'ord' => ''
            ),
            _("Order") => array(
                'align' => 'center',
                'fun' => 'order_view'
            ),
            _("Reference"),
            _("Date") => array(
                'name' => 'tran_date',
                'type' => 'date',
                'ord' => 'desc'
            ),
            _("Due Date") => array(
                'type' => 'date',
                'fun' => 'due_date'
            ),
            _("Customer") => array(
                'ord' => ''
            ),
            _("Branch") => array(
                'ord' => ''
            ),
            _("Currency") => array(
                'align' => 'center'
            ),
            _("Debit") => array(
                'label'=>'DR',
                'align' => 'right',
                'fun' => 'fmt_debit'
            ),
            _("Credit") => array(
                'label'=>'CR',
                'align' => 'right',
                'insert' => true,
                'fun' => 'fmt_credit'
            ),

            // _("GL Receivable") => array('align'=>'right', 'insert'=>true,'fun'=>'gl_receivable'),
            _("RB") => array(
                'align' => 'right',
                'type' => 'amount'
            ),

            'gl'=>array(
                'align' => 'center',
                'insert' => true,
                'fun' => 'gl_view'
            ),
            'credit'=>array(
                'label'=>'Credit',
                'align' => 'center',
                'insert' => true,
                'fun' => 'credit_link'
            ),
            'edit'=>array(
                'align' => 'center',
                'insert' => true,
                'fun' => 'edit_link'
            ),
            'prt'=>array(
                'align' => 'center',
                'insert' => true,
                'fun' => 'prt_link'
            )
        );

        if ($_POST['customer_id'] != ALL_TEXT) {
            $cols[_("Customer")] = 'skip';
            $cols[_("Currency")] = 'skip';
        }
        if ($_POST['filterType'] == ALL_TEXT)
            $cols[_("RB")] = 'skip';

        $table = & new_db_pager('trans_tbl', $sql, $cols, $table = null, $key = null, $page_len = 10);
        $table->ci_control = $this;
        // $table->set_marker('check_overdue', _("Marked items are overdue."));

        // echo '<div class="row"><div class="col-md-12" ><div class="portlet light"><div class=" portlet-body">';
//         echo '<div class="fixed-table-container" style="padding-bottom: 0px;">';
        echo db_table_responsive($table);
//         echo '</div>';

        // echo '</div></div></div></div>';
    }

    function fmt_debit($row)
    {
        $tran_type = $row["type"];
        if( strlen($tran_type) < 1 ){
            $tran_type = $row['tran_type'];
        }

        $number = 0;
        switch ($tran_type){
            case ST_CUSTCREDIT:
            case ST_CUSTPAYMENT:
            case ST_BANKDEPOSIT:
                $number = -$row["TotalAmount"];
                break;
            default:
                $number = $row["TotalAmount"];
                break;

        }



        return $number>=0 ? number_total($number) : '';

    }

    function fmt_credit($row)
    {
        $tran_type = $row["type"];
        if( strlen($tran_type) < 1 ){
            $tran_type = $row['tran_type'];
        }

        $number = 0;
        switch ($tran_type){
            case ST_CUSTCREDIT:
            case ST_CUSTPAYMENT:
            case ST_BANKDEPOSIT:
                $number = $row["TotalAmount"];
                break;
            default:
                $number = 0;
                break;

        }

        return number_total($number,true,false);
    }

    function customer_totals($area_id)
    {
        div_start($area_id,$trigger = null, $non_ajax = false, 'class="mb-3"');
        if ($_POST['customer_id'] != "" && $_POST['customer_id'] != ALL_TEXT) {
            $customer_record = get_customer_details($_POST['customer_id'], input_val('TransToDate'));
            $this->display_customer_summary($customer_record);
        }
        div_end();
    }

    function display_customer_summary($customer_record)
    {
        $past1 = get_company_pref('past_due_days');
        $past2 = 2 * $past1;

        if ($customer_record["dissallow_invoices"] != 0) {
            echo "<div class=\"text-center text-primary \" ><h4>" . _("CUSTOMER ACCOUNT IS ON HOLD") . "</h4></div>";
        }

        $nowdue = "1-" . $past1 . " " . _('Days');
        $pastdue1 = $past1 + 1 . "-" . $past2 . " " . _('Days');
        $pastdue2 = _('Over') . " " . $past2 . " " . _('Days');

        start_table(TABLESTYLE, array(
            'class' => 'table table-striped table-bordered table-hover'
        ));
        $th = array(
            _("Currency"),
            _("Terms"),
            _("Current"),
            $nowdue,
            $pastdue1,
            $pastdue2,
            _("Total Balance")
        );
        table_header($th);

        start_row();
        label_cell($customer_record["curr_code"]);
        label_cell($customer_record["terms"]);
        amount_cell($customer_record["Balance"] - $customer_record["Due"]);
        amount_cell($customer_record["Due"] - $customer_record["Overdue1"]);
        amount_cell($customer_record["Overdue1"] - $customer_record["Overdue2"]);
        amount_cell($customer_record["Overdue2"]);
        amount_cell($customer_record["Balance"]);
        end_row();
        end_table();
    }

    function tbl_trans_view($trans)
    {
    	return tran_view_detail($trans["type"], $trans["trans_no"]);
    }
}