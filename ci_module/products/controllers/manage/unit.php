<?php
class ProductsManageUnit
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

        $result = get_all_item_units(check_value('show_inactive'));

        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');
        $th = array(_('Unit'), _('Description'), _('Decimals'), "", "");
        inactive_control_column($th);

        table_header($th);
        $k = 0; //row colour counter

        while ($myrow = db_fetch($result))
        {

            alt_table_row_color($k);

            label_cell($myrow["abbr"]);
            label_cell($myrow["name"]);
            label_cell(($myrow["decimals"]==-1?_("User Quantity Decimals"):$myrow["decimals"]));
            $id = htmlentities($myrow["abbr"]);
            inactive_control_cell($id, $myrow["inactive"], 'item_units', 'abbr');
            edit_button_cell("Edit".$id, _("Edit"));
            delete_button_cell("Delete".$id, _("Delete"));
            end_row();
        }

        inactive_control_row($th);
        end_table(1);
    }

    private function detail()
    {
        row_start();
        col_start(8,'class="col-md-offset-2"');

        if ($this->selected_id != '')
        {
            if ( $this->mode == 'Edit') {
                //editing an existing item category

                $myrow = get_item_unit($this->selected_id);

                $_POST['abbr'] = $myrow["abbr"];
                $_POST['description']  = $myrow["name"];
                $_POST['decimals']  = $myrow["decimals"];
            }
            hidden('selected_id', $myrow["abbr"]);
        }
        if ($this->selected_id != '' && item_unit_used($this->selected_id)) {
            input_label_bootstrap(_("Unit Abbreviation:"), 'abbr');
            hidden('abbr', $_POST['abbr']);
        } else
            input_text_bootstrap(_("Unit Abbreviation:"), 'abbr');


        input_text_bootstrap(_("Descriptive Name:"), 'description');
        numbers_list(_("Decimal Places:"), 'decimals', null, 0, 6, _("User Quantity Decimals"));

        col_end();
        row_end();
    }
}