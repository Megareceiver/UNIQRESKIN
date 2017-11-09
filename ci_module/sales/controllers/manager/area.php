<?php

class SalesManagerArea
{

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");
        $this->areas_list();
        box_footer_show_active();

        box_start("Area Detail");
        row_start();
        $this->area_item();
        row_end();

        box_footer_start();
        div_start('controls');
        submit_add_or_update_center($this->id == - 1, '', 'both');
        div_end();
        box_footer_end();
        box_end();

        end_form();
    }

    private function areas_list()
    {
        $result = get_sales_areas(check_value('show_inactive'));

        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');

        $th = array(
            _("Area Name"),
            "edit"=>array('label'=>'','width'=>'5%','class'=>'center'),
            "delete"=>array('label'=>'','width'=>'5%','class'=>'center')
        );
        inactive_control_column($th);

        table_header($th);
        $k = 0;

        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);

            label_cell($myrow["description"]);

            inactive_control_cell($myrow["area_code"], $myrow["inactive"], 'areas', 'area_code');

            edit_button_cell("Edit" . $myrow["area_code"], _("Edit"));
            delete_button_cell("Delete" . $myrow["area_code"], _("Delete"));
            end_row();
        }

        end_table();
    }

    private function area_item()
    {
        if ($this->id != - 1) {
            if ($this->mode == 'Edit') {
                // editing an existing area
                $myrow = get_sales_area($this->id);

                $_POST['description'] = $myrow["description"];
            }
            hidden("selected_id", $this->id);
        }
        row_start('justify-content-center');
        col_start(8);
        input_text_bootstrap("Area Name", 'description');
        row_end();
    }
}