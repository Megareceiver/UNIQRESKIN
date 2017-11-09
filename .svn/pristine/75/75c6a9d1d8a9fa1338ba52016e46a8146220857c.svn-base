<?php

class SalesManagerType
{

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");
        $this->types_list();
        box_footer_show_active();
        display_note(_("Marked sales type is the company base pricelist for prices calculations."), 0, 0, "class='overduefg'");

        box_start("Type Detail");
            row_start();
            $this->type_item();
            row_end();

        box_footer_start();
        div_start('controls');
        submit_add_or_update_center($this->id == - 1, '', 'both');
        div_end();
        box_footer_end();
        box_end();

        end_form();
    }

    private function types_list()
    {
        $result = get_all_sales_types(check_value('show_inactive'));

        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');

        $th = array(
            _('Type Name'),
            _('Factor'),
            'tax_incl' => array(
                'label' => _('Tax Incl'),
                'width' => '10%'
            ),
            "edit"=>array('label'=>'','width'=>'5%','class'=>'center'),
            "delete"=>array('label'=>'','width'=>'5%','class'=>'center')

        );
        inactive_control_column($th);
        table_header($th);
        $k = 0;
        $base_sales = get_base_sales_type();

        while ($myrow = db_fetch($result)) {
            if ($myrow["id"] == $base_sales)
                start_row("class='overduebg'");
            else
                alt_table_row_color($k);
            label_cell($myrow["sales_type"]);

            // $f = number_format2($myrow["factor"],4);
            $f = number_total($myrow["factor"]);
            if ($myrow["id"] == $base_sales)
                $f = "<I>" . _('Base') . "</I>";

            label_cell($f);
            label_cell($myrow["tax_included"] ? _('Yes') : _('No'), 'align=center');
            inactive_control_cell($myrow["id"], $myrow["inactive"], 'sales_types', 'id');
            edit_button_cell("Edit" . $myrow['id'], _("Edit"));
            delete_button_cell("Delete" . $myrow['id'], _("Delete"));
            end_row();
        }
        end_table();
    }

    private function type_item()
    {
        if (! isset($_POST['tax_included']))
            $_POST['tax_included'] = 0;
        if (! isset($_POST['base']))
            $_POST['base'] = 0;

//         start_table(TABLESTYLE2);

        if ($this->id != - 1) {

            if ($this->mode == 'Edit') {
                $myrow = get_sales_type($this->id);

                $_POST['sales_type'] = $myrow["sales_type"];
                $_POST['tax_included'] = $myrow["tax_included"];
                $_POST['factor'] = $myrow["factor"];
            }
            hidden('selected_id', $this->id);
        } else {
            $_POST['factor'] = number_total(1);
        }
        col_start(8);
        input_text_bootstrap("Sales Type Name", 'sales_type');
        input_money('Calculation factor','factor',input_val('factor'));
        check_bootstrap('Tax included','tax_included');
//         text_row_ex(_("Sales Type Name") . ':', 'sales_type', 20);
//         amount_row(_("Calculation factor") . ':', 'factor', null, null, null, 4);
//         check_row(_("Tax included") . ':', 'tax_included', $_POST['tax_included']);
        col_end();
    }
}