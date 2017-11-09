<?php

class ProductsManageItemCode
{

    var $id, $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {
    }

    function index()
    {
        if (! isset($_POST['stock_id']))
            $_POST['stock_id'] = get_global_stock_item();
        $this->unit_load();

        start_form();
        box_start("");

        row_start();
        col_start(9);
        stock_items_bootstrap("Item", 'stock_id', $_POST['stock_id'], false, true);
        col_end();
        row_end();

        set_global_stock_item($_POST['stock_id']);

        row_start(null, 'style="padding: 15px;"');
        div_start('code_table');
            $this->codes_list();
        div_end();
        row_end();

        // -----------------------------------------------------------------------------------------------

        box_start();
        $this->code_item();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        box_footer_end();
        box_form_end();


        box_end();
        end_form();
    }


    private function unit_load(){
        $item_unit = NULL;
        if( intval($stock_id = input_post('stock_id')) > 0 ){
            $item_unit = get_instance()->db
                    ->from('stock_master, item_units')
                    ->select('units, decimals, description, category_id')
                    ->where('stock_id',$stock_id)
                    ->get()->row();

        }
        if( !is_object($item_unit) ){
            $this->item_unit = (object)array(
                'decimals'=>0,
                'units'=>0,
                'description'=>NULL,
                'category_id'=>0
            );
        } else {
            $this->item_unit = $item_unit;
        }
    }

    private function codes_list()
    {
        $result = get_all_item_codes($_POST['stock_id']);
        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');

        $th = array(
            _("EAN/UPC Code"),
            _("Quantity"),
            _("Units"),
            _("Description"),
            _("Category"),
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

        $k = $j = 0; // row colour counter

        while ($myrow = db_fetch($result)) {
            alt_table_row_color($k);

            label_cell($myrow["item_code"]);
            qty_cell($myrow["quantity"], $this->item_unit->decimals);
            label_cell($this->item_unit->units);
            label_cell($myrow["description"]);
            label_cell($myrow["cat_name"]);
            edit_button_cell("Edit" . $myrow['id'], _("Edit"));
            edit_button_cell("Delete" . $myrow['id'], _("Delete"));
            end_row();

            $j ++;
            If ($j == 12) {
                $j = 1;
                table_header($th);
            } // end of page full new headings
        } // end of while loop

        end_table();
    }

    private function code_item()
    {



        if ($this->selected_id != '') {
            if ($this->mode == 'Edit') {
                $myrow = get_item_code($this->selected_id);
                $_POST['item_code'] = $myrow["item_code"];
                $_POST['quantity'] = $myrow["quantity"];
                $_POST['description'] = $myrow["description"];
                $_POST['category_id'] = $myrow["category_id"];
            }

            hidden('selected_id', $this->selected_id);
        } else {
            $_POST['quantity'] = 1;
            $_POST['description'] = $this->item_unit->description;
            $_POST['category_id'] = $this->item_unit->category_id;
        }

        if( input_post('_stock_id_edit') != input_post('stock_id') ){
            $this->mode = 'ADD_ITEM';
            $this->selected_id= -1;
            unset($_POST);
        }

        col_start(8);
        bootstrap_set_label_column(NULL);

        hidden('code_id', $this->selected_id);

        input_text_bootstrap(_("UPC/EAN code:"), 'item_code');
        input_text_addon_both('Quantity','quantity',null,$this->item_unit->units);
//         qty_row(_("Quantity:"), 'quantity', null, '', $this->item_unit->units, $this->item_unit->decimals);
        input_text_bootstrap(_("Description:"), 'description');
        stock_categories(_("Category:"), 'category_id', null);
        col_end();

    }
}