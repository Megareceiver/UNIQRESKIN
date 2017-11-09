<?php
class PurchasesInquiryAllocation
{

    function __construct()
    {
        $this->page_variables();
        $this->ajax_updates();
        $this->bootstrap = get_instance()->bootstrap;
    }

    function view(){
        start_form();

        box_start();
        $this->fillter();

        $this->transactions_table();
        box_footer();
        box_end();
        end_form();
    }

    private function page_variables(){
        $filterType = input_val('filterType');

        if (isset($_GET['filtertype'])) {
            $filterType = $_GET['filtertype'];
        }
        set_post('filterType', $filterType);

        $from_date = input_val('FromDate');
        if ($from_date)
        {
            $from_date = $_GET['FromDate'];
        }
        set_post('FromDate', $from_date);

        $to_date = input_val('ToDate');
        if ($to_date)
        {
            $to_date = $_GET['ToDate'];
        }
        set_post('ToDate', $to_date);

        $supplier_id= input_val('supplier_id');
        if (!$supplier_id)
            $supplier_id = get_global_supplier();

        set_post('supplier_id', $supplier_id);
        set_global_supplier($supplier_id);
    }

    private function ajax_updates(){

    }

    function fillter(){
        row_start('inquiry-filter justify-content-center');
        if (! @$_GET['popup']) {
            col_start(12,'col-md-3');
            supplier_list_bootstrap(_("Supplier"), 'supplier_id', null, true, true);
        }

        col_start(12,'col-md-2');
        input_date_bootstrap("From", 'TransAfterDate', NULL, false, false, 0, - 1);

        col_start(12,'col-md-2');
        input_date_bootstrap("To", 'TransToDate');

        col_start(12,'col-md-2');
        $fillter_type_title = isMobile() ? "Tran Type" :NULL;
        supp_transactions_bootstrap($fillter_type_title, 'filterType', input_val('filterType'), true);

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

    function transactions_table(){

        $sql = get_sql_for_supplier_allocation_inquiry();

        $cols = array(
            _("Type") => array('fun'=>'systype_name'),
            _("#") => array('fun'=>'view_link', 'ord'=>''),
            _("Reference"),
            _("Supplier") => array('ord'=>''),
            _("Supp Reference"),
            _("Date") => array('name'=>'tran_date', 'type'=>'date', 'ord'=>'asc'),
            _("Due Date") => array('fun'=>'due_date'),
            _("Currency") => array('align'=>'center'),
            _("Debit") => array('align'=>'right', 'fun'=>'fmt_debit'),
            _("Credit") => array('align'=>'right', 'insert'=>true, 'fun'=>'fmt_credit'),
            _("Allocated") => 'amount',
            _("Balance") => array('type'=>'amount', 'insert'=>true, 'fun'=>'fmt_balance'),
            array('insert'=>true, 'fun'=>'alloc_link')
        );

        if ($_POST['supplier_id'] != ALL_TEXT) {
            $cols[_("Supplier")] = 'skip';
            $cols[_("Currency")] = 'skip';
        }

        $table =& new_db_pager('doc_tbl', $sql, $cols, $table = null, $key = null, $page_len = 10);

        echo db_table_responsive($table);
    }
}