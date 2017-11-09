<?php

class SalesManagerGroup
{

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");
        $this->groups_list();
        box_footer_show_active();

        box_start("Group Detail");
        $this->group_item();


        box_footer_start();
        div_start('controls');
            submit_add_or_update_center($this->id == - 1, '', 'both');
        div_end();
        box_footer_end();
        box_end();

        end_form();
    }

    var $id = - 1;
    var $mode = NULL;
    private function groups_list()
    {
        global $Ajax;

        start_table(TABLESTYLE,'class="table table-striped table-bordered table-hover tablestyle"');
        $th = array(
            'id'=>array('label'=>'ID','width'=>'5%','class'=>'center'),
            _("Group Name"),
            "edit"=>array('label'=>'','width'=>'5%','class'=>'center'),
            "delete"=>array('label'=>'','width'=>'5%','class'=>'center')
        );

        if (check_value('show_inactive')){
            $th = array(
                'id'=>array('label'=>'ID','width'=>'5%','class'=>'center'),
                _("Group Name"),
                'inactive'=>array('label'=>'Inactive','width'=>'5%','class'=>'center'),
                "edit"=>array('label'=>'','width'=>'5%','class'=>'center'),
                "delete"=>array('label'=>'','width'=>'5%','class'=>'center')
            );
        }
        if (get_post('_show_inactive_update')) {
            $Ajax->activate('_page_body');
        }

        table_header($th);
        $k = 0;

        $result = get_sales_groups(check_value('show_inactive'));
        while ($myrow = db_fetch($result)) {
            alt_table_row_color($k);
            label_cell($myrow["id"]);
            label_cell($myrow["description"]);
            inactive_control_cell($myrow["id"], $myrow["inactive"], 'groups', 'id');
            edit_button_cell("Edit" . $myrow["id"], _("Edit"));
            delete_button_cell("Delete" . $myrow["id"], _("Delete"));
            end_row();
        }

//         inactive_control_row($th);
        end_table(1);
    }

    private function group_item()
    {
        col_start(8);
        if ( $this->id != - 1) {
            if ($this->mode == 'Edit' ) {
                // editing an existing group
                $myrow = get_sales_group($this->id);

                $_POST['description'] = $myrow["description"];
                $_POST['id'] = $myrow["id"];
                input_label_bootstrap('ID','id');
            }
            hidden("selected_id", $this->id);


        }
        input_text_bootstrap("Group Name", 'description');
        col_end();
    }
}