<?php

class SalesManagerCreditStatus
{

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");
        $this->status_list();
        box_footer_show_active();

        box_start("Status Detail");
        row_start('justify-content-center');
        $this->status_item();
        row_end();

        box_footer_start();
        div_start('controls');
        submit_add_or_update_center($this->id == - 1, '', 'both');
        div_end();
        box_footer_end();
        box_end();

        end_form();
    }

    private function status_list()
    {
        $result = get_all_credit_status(check_value('show_inactive'));

        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle mb-3"');
        $th = array(
            _("Description"),
            _("Dissallow Invoices"),
            '',
            ''
        );
        inactive_control_column($th);
        table_header($th);

        $k = 0;
        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);

            if ($myrow["dissallow_invoices"] == 0) {
                $disallow_text = _("Invoice OK");
            } else {
                $disallow_text = "<b>" . _("NO INVOICING") . "</b>";
            }

            label_cell($myrow["reason_description"]);
            label_cell($disallow_text);
            inactive_control_cell($myrow["id"], $myrow["inactive"], 'credit_status', 'id');
            edit_button_cell("Edit" . $myrow['id'], _("Edit"));
            delete_button_cell("Delete" . $myrow['id'], _("Delete"));
            end_row();
        }

//         inactive_control_row($th);
        end_table();
    }

    private function status_item()
    {

        if ( $this->id != -1)
        {
            if ( $this->mode == 'Edit') {
                //editing an existing status code

                $myrow = get_credit_status($this->id);

                $_POST['reason_description']  = $myrow["reason_description"];
                $_POST['dissallow_invoices']  = $myrow["dissallow_invoices"];
            }
            hidden('selected_id', $this->id);
        }

        col_start(8);
        input_textarea_bootstrap('Description','reason_description');
//         text_row_ex(_("Description:"), 'reason_description', 50);

//         yesno_list_row(_("Dissallow invoicing ?"), 'DisallowInvoices', null);

        yesno_bootstrap('Dissallow invoicing','dissallow_invoices');
        col_end();
    }
}