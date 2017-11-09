<?php

class ManagePaymentTerm
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");
        $this->listview();
        box_footer_show_active();

        box_start("Payment Term Details",'fa-file-word-o');
        $this->detail();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        box_footer_end();

        box_end();
        end_form();
    }

    private function listview()
    {
        global $pterm_types;
        $result = get_payment_terms_all(check_value('show_inactive'));
        start_table(TABLESTYLE);
        $th = array(
            _("Description"),
            _("Type"),
            _("Due After/Days"),
            "edit" => array(
                'label' => "Edit",
                'width' => '5%',
                'class'=>'text-center',
            ),
            "delete" => array(
                'label' => 'Del',
                'width' => '5%',
                'class'=>'text-center',
            )
        );
        inactive_control_column($th);
        table_header($th);

        $k = 0; // row colour counter
        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);
            $type = term_type($myrow);
            $days = term_days($myrow);
            label_cell($myrow["terms"]);
            label_cell($pterm_types[$type]);
            label_cell($type == PTT_DAYS ? "$days " . _("days") : ($type == PTT_FOLLOWING ? $days : _("N/A")));
            inactive_control_cell($myrow["terms_indicator"], $myrow["inactive"], 'payment_terms', "terms_indicator");
            edit_button_cell("Edit" . $myrow["terms_indicator"], _("Edit"));
            delete_button_cell("Delete" . $myrow["terms_indicator"], _("Delete"));
            end_row();
        }
        end_table(1);
    }

    private function detail()
    {
        div_start('edits');
        row_start('justify-content-md-center');
        col_start(8);

        // start_table(TABLESTYLE2);

        $day_in_following_month = $days_before_due = 0;
        if ($this->selected_id != - 1) {
            if ($this->mode == 'Edit') {
                // editing an existing payment terms
                $myrow = get_payment_terms($this->selected_id);

                $_POST['terms'] = $myrow["terms"];
                $_POST['DayNumber'] = term_days($myrow);
                $_POST['type'] = term_type($myrow);
            }
            hidden('selected_id', $this->selected_id);
        }

        input_text(_("Terms Description"), 'terms');

        payment_terms_bootstrap(_("Payment type"), 'type', null, true);

        if (in_array(get_post('type'), array(
            PTT_FOLLOWING,
            PTT_DAYS
        )))
            input_text(_("Days (Or Day In Following Month)"), 'DayNumber');
        else
            hidden('DayNumber', 0);

            // end_table(1);

        col_end();
        row_end();

        div_end();
    }
}