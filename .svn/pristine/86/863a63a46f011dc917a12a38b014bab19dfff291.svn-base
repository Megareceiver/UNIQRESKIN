<?php

class ProductsManageMovementType
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

        box_start("");
        $this->detail();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        box_footer_end();

        box_end();
        end_form();
    }

    private function listview()
    {
        $result = get_all_movement_type(check_value('show_inactive'));

        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');

        $th = array(
            _("Description"),
            "edit" => array(
                'label' => NULL,
                'width' => '5%'
            ),
            "delete" => array(
                'label' => NULL,
                'width' => '5%'
            )
        );
        inactive_control_column($th);
        table_header($th);
        $k = 0;
        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);

            label_cell($myrow["name"]);
            inactive_control_cell($myrow["id"], $myrow["inactive"], 'movement_types', 'id');
            edit_button_cell("Edit" . $myrow['id'], _("Edit"));
            delete_button_cell("Delete" . $myrow['id'], _("Delete"));
            end_row();
        }
        inactive_control_row($th);
        end_table(1);
    }

    private function detail()
    {
        row_start();
        col_start(8,'class="col-md-offset-2"');
        if ($this->selected_id != - 1) {
            if ($this->mode == 'Edit') {
                // editing an existing status code

                $myrow = get_movement_type($this->selected_id);

                $_POST['name'] = $myrow["name"];
            }
            hidden('selected_id', $this->selected_id);
        }

        input_text_bootstrap(_("Description:"), 'name');
        col_end();
        row_end();
    }
}