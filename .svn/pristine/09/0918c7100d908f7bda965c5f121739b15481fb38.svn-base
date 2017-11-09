<?php

class GlManageReevaluate
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        global $Refs;

        if (! isset($_POST['date']))
            $_POST['date'] = Today();

        start_form();

        box_start("");
        row_start();
        col_start(8, 'class="col-md-offset-2"');

//         date_row(_("Date for Revaluation:"), 'date', '', null, 0, 0, 0, null, true);
        input_date_bootstrap('Date for Revaluation','date');
        input_text_bootstrap(_("Reference"), 'ref', $Refs->get_next(ST_JOURNAL));
        input_textarea_bootstrap(_("Memo:"), 'memo_');

        col_end();
        row_end();


        box_footer_start();
        submit('submit', _("Revaluate Currencies"), true, false, false, "");
        box_footer_end();

        box_end();
        end_form();
    }


}