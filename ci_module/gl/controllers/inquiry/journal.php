<?php

class GLInquiryJournal
{

    function __construct()
    {
    }

    function index()
    {
        start_form();
        box_start();
        $this->fillter();

        $this->items();

        box_footer_start();
        box_footer_end();

        box_end();
        end_form();
    }

    function edit_link($row)
    {
        global $editors;

        $ok = true;
        if ($row['type'] == ST_SALESINVOICE) {
            $myrow = get_customer_trans($row["type_no"], $row["type"]);
            if ($myrow['alloc'] != 0 || get_voided_entry(ST_SALESINVOICE, $row["type_no"]) !== false)
                $ok = false;
        }

        return isset($editors[$row["type"]]) && ! is_closed_trans($row["type"], $row["type_no"]) && $ok ? pager_link(_("Edit"), sprintf($editors[$row["type"]], $row["type_no"], $row["type"]), ICON_EDIT) : '';
    }

    function fillter()
    {
        row_start('row-filter');

        col_start(12,"col-md-4");
        input_text("Reference", 'Ref', null, 'Enter reference fragment or leave empty');

        col_start(12,"col-md-4");
        journal_types_list(_("Type"), "filterType");

        col_start(12,"col-md-2");
        input_date_bootstrap(_("From"), 'FromDate', null, false, null, 0, - 1, 0);

        col_start(12,"col-md-2");
        input_date_bootstrap(_("To"), 'ToDate');

        col_start(12,"col-md-2");
        show_voided_inquiry('AlsoClosed');

        col_start(12,"col-md-3");
        input_text(_("Memo"), 'Memo', null, _('Enter memo fragment or leave empty'));

        col_start(6,"col-md-2 offset-md-0 offset-3");
        submit('Search', _("Search"), true, '', 'default', 'search');

        col_end();
        row_end();
    }

    var $inquiry_col = array(

        // _("#") => array('fun'=>'journal_pos', 'align'=>'center'),
        "Date" => array(
            'name' => 'tran_date',
            'type' => 'date',
            'ord' => ''
        ),
        "Type" => array(
            'fun' => 'systype_name',
            'name' => 'type'
        ),
        "Trans #" => array(
            'fun' => 'view_link',
            'ord' => '',
            'name' => 'type_no'
        ),
        "Reference",
        "Amount" => array(
            'type' => 'amount'
        ),
        "Memo",
        "User",
        "gl" => array(
            'label' => 'View',
            'width' => '5%',
            'align' => 'center',
            'insert' => true,
            'fun' => 'gl_link'
        ),
        'edit' => array(
            'label' => 'Edit',
            'width' => '5%',
            'align' => 'center',
            'insert' => true,
            'fun' => 'edit_link'
        ),
        'print' => array(
            'label' => 'Prt',
            'width' => '5%',
            'align' => 'center',
            'insert' => true,
            'fun' => 'gl_print'
        )
    );

    private function items()
    {
        $sql = get_sql_for_journal_inquiry(get_post('filterType', - 1), get_post('FromDate'), get_post('ToDate'), get_post('Ref'), get_post('Memo'), check_value('AlsoClosed'));
        if (! check_value('AlsoClosed')) {
            $this->inquiry_col[_("#")] = 'skip';
        }

        $table = & new_db_pager('journal_tbl', $sql, $this->inquiry_col);

        $table->ci_control = $this;

        display_db_pager($table);
    }
}