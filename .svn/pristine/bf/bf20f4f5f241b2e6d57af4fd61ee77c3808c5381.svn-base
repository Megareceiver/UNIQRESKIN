<?php

class GlManageClasses
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

        box_start("GL Account Class Detail",'fa-columns');
        $this->detail();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == "", '', 'both');
        box_footer_end();

        box_end();
        end_form();
    }

    private function listview()
    {
        $result = get_account_classes(check_value('show_inactive'));
        start_table(TABLESTYLE);
        $th = array(
            _("Class ID"),
            _("Class Name"),
            _("Class Type"),
            "edit" => array(
                'label' => "Edit",
                'width' => '5%'
            ),
            "delete" => array(
                'label' => 'Del',
                'width' => '5%'
            )
        );
        if (isset($use_oldstyle_convert) && $use_oldstyle_convert == 1)
            $th[2] = _("Balance Sheet");
        inactive_control_column($th);
        table_header($th);

        $k = 0;
        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);

            label_cell($myrow["cid"]);
            label_cell($myrow['class_name']);
            if (isset($use_oldstyle_convert) && $use_oldstyle_convert == 1) {
                $myrow['ctype'] = ($myrow["ctype"] >= CL_ASSETS && $myrow["ctype"] < CL_INCOME ? 1 : 0);
                label_cell(($myrow['ctype'] == 1 ? _("Yes") : _("No")));
            } else {
                global $class_types;
                label_cell($class_types[$myrow["ctype"]]);
            }

            inactive_control_cell($myrow["cid"], $myrow["inactive"], 'chart_class', 'cid');
            edit_button_cell("Edit" . $myrow["cid"], _("Edit"));
            delete_button_cell("Delete" . $myrow["cid"], _("Delete"));
            end_row();
        }
        end_table(1);
    }

    private function detail()
    {
        row_start('justify-content-md-center');
        col_start(8);

        if ($this->selected_id != "") {
            if ( $this->mode == 'Edit') {
                // editing an existing status code
                $myrow = get_account_class($this->selected_id);

                $_POST['id'] = $myrow["cid"];
                $_POST['name'] = $myrow["class_name"];
                if (isset($use_oldstyle_convert) && $use_oldstyle_convert == 1)
                    $_POST['ctype'] = ($myrow["ctype"] >= CL_ASSETS && $myrow["ctype"] < CL_INCOME ? 1 : 0);
                else
                    $_POST['ctype'] = $myrow["ctype"];
                hidden('selected_id', $this->selected_id);
            }
            hidden('id');
            input_label_bootstrap(_("ID"), 'id', $_POST['id']);
        } else {
            input_text_bootstrap(_("ID"), 'id');
        }

        input_text_bootstrap(_("Name"), 'name');

        if (isset($use_oldstyle_convert) && $use_oldstyle_convert == 1) {
            check_bootstrap('Balance Sheet', 'ctype');
        } else
            class_types_list(_("Type"), 'ctype', null);

        col_end();
        row_end();
    }
}