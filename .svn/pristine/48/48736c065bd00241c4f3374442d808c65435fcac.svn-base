<?php

class ProductsManageSalesKit
{

    var $id, $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {
        $this->actions();
    }

    private function actions()
    {
        global $Ajax;

        if (list_updated('item_code')) {
            if (get_post('item_code') == '')
                $_POST['description'] = '';
            $Ajax->activate('_page_body');
        }
    }

    function index()
    {
        start_form();
        box_start("");

        row_start();
        col_start(9);
        sales_kits('Select a sale kit', 'item_code', null, _('New kit'), true);
        col_end();
        row_end();

        $selected_kit = $_POST['item_code'];
        // ----------------------------------------------------------------------------------
        if (get_post('item_code') == '') {
            // New sales kit entry
            // text_row(_("Alias/kit code:"), 'kit_code', null, 20, 21);
        } else {
            row_start();
            col_start(12, 'style="padding: 15px;"');
            $this->kit_items($selected_kit);

            col_start(9, 'style="padding: 5px;"');

            $props = get_kit_props($_POST['item_code']);
            // Kit selected so display bom or edit component
            $_POST['description'] = $props['description'];
            $_POST['category'] = $props['category_id'];

            input_text_bootstrap(_("Description:"), 'description', null, 50, 200);
            stock_categories(_("Category:"), 'category', null);

            col_end();
            row_end();

            box_footer_start();
            submit('update_name', _("Update"), true, _('Update kit/alias name'), true);
            box_form_end();
            box_end();
        }

        if ($this->mode == 'Edit') {
            $myrow = get_item_code($this->selected_id);
            $_POST['component'] = $myrow["stock_id"];
            $_POST['quantity'] = number_format2($myrow["quantity"], get_qty_dec($myrow["stock_id"]));
        }
        hidden("selected_id", $this->selected_id);

        box_start();
        $this->kit_item();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        box_form_end();

        box_end();
        end_form();
    }

    private function kit_items($selected_kit)
    {
        $result = get_item_kit($selected_kit);
        div_start('bom');
        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');
        $th = array(
            _("Stock Item"),
            _("Description"),
            _("Quantity"),
            _("Units"),
            "edit" => array(
                'label' => NULL,
                'width' => '5%'
            ),
            "delete" => array(
                'label' => NULL,
                'width' => '5%'
            )
        );
        table_header($th);

        $k = 0;
        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);

            label_cell($myrow["stock_id"]);
            label_cell($myrow["comp_name"]);
            qty_cell($myrow["quantity"], false, $myrow["units"] == '' ? 0 : get_qty_dec($myrow["comp_name"]));
            label_cell($myrow["units"] == '' ? _('kit') : $myrow["units"]);
            edit_button_cell("Edit" . $myrow['id'], _("Edit"));
            delete_button_cell("Delete" . $myrow['id'], _("Delete"));
            end_row();
        } // END WHILE LIST LOOP
        end_table();
        div_end();
    }

    private function kit_item()
    {
        col_start(8);
        bootstrap_set_label_column(NULL);

        sales_local_items(_("Component:"), 'component', null, false, true);

        // if (get_post('description') == '')
        // $_POST['description'] = get_kit_name($_POST['component']);
        if (get_post('item_code') == '') { // new kit/alias
            if ( $this->mode != 'ADD_ITEM' && $this->mode != 'UPDATE_ITEM') {
//                 $_POST['description'] = $props['description'];
//                 $_POST['category'] = $props['category_id'];
            }
            input_text_bootstrap(_("Description:"), 'description', null, 50, 200);
            stock_categories(_("Category:"), 'category', null);
        }
        $res = get_item_edit_info(get_post('component'));
        $dec = $res["decimals"] == '' ? 0 : $res["decimals"];
        $units = $res["units"] == '' ? _('kits') : $res["units"];
        if (list_updated('component')) {
            $_POST['quantity'] = number_format2(1, $dec);
            global $Ajax;
            $Ajax->activate('quantity');
            $Ajax->activate('category');
        }
        input_text_addon_both('Quantity','quantity',null,$units);
        col_end();
//         qty_row(_("Quantity:"), 'quantity', number_format2(1, $dec), '', $units, $dec);
    }
}