<?php

class PurchasesInquiryTransactions
{

    var $is_popup = false;
    function __construct()
    {
        $this->page_variables();
        $this->ajax_updates();
        $this->bootstrap = get_instance()->bootstrap;
        if( isset($_GET['popup']) ){
            $this->is_popup = input_get("popup");
        }
    }

    function view(){
        if( !$this->is_popup ){
            box_start();
        }
        
        $this->fillter();

        $supplier_id=input_val('supplier_id');

        if ($supplier_id > 0 )
        {
            $this->display_supplier_summary($supplier_id,'totals_tbl');
        }

        $this->transactions_table();
        if( !$this->is_popup ){
            box_footer();
            box_end();
        }
        
    }

    function page_variables()
    {
        if (isset($_GET['supplier_id'])) {
            $_POST['supplier_id'] = $_GET['supplier_id'];
        }
        if (isset($_GET['FromDate'])) {
            $_POST['TransAfterDate'] = $_GET['FromDate'];
        }
        if (isset($_GET['ToDate'])) {
            $_POST['TransToDate'] = $_GET['ToDate'];
        }
        if (! isset($_POST['supplier_id']))
            $_POST['supplier_id'] = get_global_supplier();
        set_global_supplier($_POST['supplier_id']);

        $filterType = input_get('filtertype');
        if( empty($filterType) ){
            $filterType = input_post('filterType');
        }
        set_post('filterType', $filterType);
    }

    private function ajax_updates()
    {
        global $Ajax;
        if(get_post('RefreshInquiry'))
        {
            $Ajax->activate('totals_tbl');
        }
    }

    function fillter()
    {
        bootstrap_set_label_column(0);
        row_start('inquiry-filter justify-content-center','style="width:100%; margin-left:0;"');
        if( !$this->is_popup ){
            col_start(12,'col-md-3');
            supplier_list_bootstrap(_("Supplier"), 'supplier_id', null, true, false, false, ! @$_GET['popup']);
        }

        if( !$this->is_popup ){
            col_start(12,'col-md-2');
        } else {
            col_start(12,'col-md-3');
        }
        
        if( strlen(input_post('TransAfterDate')) < 4){
            set_post('TransAfterDate',begin_month());
        }
        input_date_bootstrap("From", 'TransAfterDate');

        if( !$this->is_popup ){
            col_start(12,'col-md-2');
        } else {
            col_start(12,'col-md-3');
        }
        input_date_bootstrap("To", 'TransToDate', NULL);

        col_start(12,'col-md-2');
        if( isMobile() ){
            supp_transactions_bootstrap('Tran Type', 'filterType', input_val('filterType'), true);
        } else {
            supp_transactions_bootstrap(null, 'filterType', input_val('filterType'), true);
        }


        col_start(12,'col-md-1');
        if( !isMobile() ){
            bootstrap_set_label_column(7);
        }

        check_bootstrap('Voided', 'voided');
        bootstrap_set_label_column(0);

        col_start(12,'col-md-1 offset-6 offset-md-0');
        submit_bootstrap('RefreshInquiry', _("Search"), _('Refresh Inquiry'), 'default','search');

        row_end();
    }

    function transactions_table()
    {
        $sql = get_sql_for_supplier_inquiry($_POST['filterType'], input_val('TransAfterDate'), input_val('TransToDate'), $_POST['supplier_id'], input_val('voided') != true);

        $cols = array(
            _("Type") => array(
                'fun' => 'systype_name',
                'ord' => ''
            ),
            _("#") => array(
                'fun' => 'trans_view_supplier',
                'ord' => ''
            ),
            _("Reference"),
            _("Supplier"),
            _("Supplier's Reference"),
            _("Date") => array(
                'name' => 'tran_date',
                'type' => 'date',
                'ord' => 'desc'
            ),
//             _("Due Date") => array(
//                 'type' => 'date',
//                 'fun' => 'due_date'
//             ),
            _("Currency") => array(
                'align' => 'center'
            ),
            _("Debit") => array(
                'align' => 'right',
                'fun' => 'fmt_debit'
            ),
            _("Credit") => array(
                'align' => 'right',
                'insert' => true,
                'fun' => 'fmt_credit'
            ),

            // _("GL Payable") => array('align'=>'right', 'insert'=>true,'fun'=>'gl_payable'),
            array(
                'insert' => true,
                'fun' => 'gl_view'
            ),
            array(
                'insert' => true,
                'fun' => 'credit_link'
            ),
            array(
                'insert' => true,
                'fun' => 'edit_link'
            ),
            array(
                'insert' => true,
                'fun' => 'prt_link'
            )
        );

        if ($_POST['supplier_id'] != ALL_TEXT) {
            $cols[_("Supplier")] = 'skip';
            $cols[_("Currency")] = 'skip';
        }
        /* show a table of the transactions returned by the sql */
        $table = & new_db_pager('trans_tbl', $sql, $cols);
        $table->ci_control = $this;
//        $table->set_marker('check_overdue', _("Marked items are overdue."));


        echo db_table_responsive($table);
    }
    
    function fmt_debit($row)
    {
        $value = -$row["TotalAmount"];
        return $value > 0 ? number_total($value) : '';
    
    }
    function fmt_credit($row)
    {
        $value = $row["TotalAmount"];
        return $value>0 ? number_total($value) : '';
    }

    function display_supplier_summary($supplier_id,$area='totals_tbl') {

        $supplier_record = get_supplier_details($supplier_id, input_val('TransToDate'));

        $past1 = get_company_pref('past_due_days');
        $past2 = 2 * $past1;
        $nowdue = "1-" . $past1 . " " . _('Days');
        $pastdue1 = $past1 + 1 . "-" . $past2 . " " . _('Days');
        $pastdue2 = _('Over') . " " . $past2 . " " . _('Days');

        div_start($area, null, false, array('class'=>'mb-3 mt-1', 'style'=>"width:100%; margin-left:0;") );

        start_table(TABLESTYLE, array(
            'class' => 'table table-striped table-bordered table-hover'
           
        ));

        $th = array(_("Currency"), _("Terms"), _("Current"), $nowdue,
            $pastdue1, $pastdue2, _("Total Balance"));

        table_header($th);
        start_row();
        label_cell($supplier_record["curr_code"]);
        label_cell($supplier_record["terms"]);
        amount_cell($supplier_record["Balance"] - $supplier_record["Due"]);
        amount_cell($supplier_record["Due"] - $supplier_record["Overdue1"]);
        amount_cell($supplier_record["Overdue1"] - $supplier_record["Overdue2"]);
        amount_cell($supplier_record["Overdue2"]);
        amount_cell($supplier_record["Balance"]);
        end_row();
        end_table();

        div_end();
    }
}