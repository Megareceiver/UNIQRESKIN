<?php

class ProductsManageCategorie
{

    var $id, $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");
        $this->categories_list();
        box_footer_show_active();
        // ----------------------------------------------------------------------------------
        box_start("");
        $this->categorie_item();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both', true);
        box_footer_end();


        box_end();
        end_form();
    }

    private function categories_list()
    {
        global $stock_types;
        $result = get_item_categories(check_value('show_inactive'));

        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');
        $th = array(
            _("Name"),
            _("Tax type"),
            _("Units"),
            _("Type"),
            _("Sales Act"),
            _("Inventory Account"),
            _("COGS Account"),
            _("Adjustment Account"),
            _("Assembly Account"),
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
        $k = 0; // row colour counter

        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);

            label_cell($myrow["description"]);
            label_cell($myrow["tax_name"]);
            label_cell($myrow["dflt_units"], "align=center");
            label_cell($stock_types[$myrow["dflt_mb_flag"]]);
            label_cell($myrow["dflt_sales_act"], "align=center");
            label_cell($myrow["dflt_inventory_act"], "align=center");
            label_cell($myrow["dflt_cogs_act"], "align=center");
            label_cell($myrow["dflt_adjustment_act"], "align=center");
            label_cell($myrow["dflt_assembly_act"], "align=center");
            inactive_control_cell($myrow["category_id"], $myrow["inactive"], 'stock_category', 'category_id');
            edit_button_cell("Edit" . $myrow["category_id"], _("Edit"));
            delete_button_cell("Delete" . $myrow["category_id"], _("Delete"));
            end_row();
        }

//         inactive_control_row($th);
        end_table();
    }

    private function categorie_item()
    {

        if ($this->selected_id != - 1) {
            if ($this->mode == 'Edit') {
                // editing an existing item category
                $myrow = get_item_category($this->selected_id);

                $_POST['category_id'] = $myrow["category_id"];
                $_POST['description'] = $myrow["description"];
                $_POST['tax_type_id'] = $myrow["dflt_tax_type"];
                $_POST['sales_account'] = $myrow["dflt_sales_act"];
                $_POST['cogs_account'] = $myrow["dflt_cogs_act"];
                $_POST['inventory_account'] = $myrow["dflt_inventory_act"];
                $_POST['adjustment_account'] = $myrow["dflt_adjustment_act"];
                $_POST['assembly_account'] = $myrow["dflt_assembly_act"];
                $_POST['units'] = $myrow["dflt_units"];
                $_POST['mb_flag'] = $myrow["dflt_mb_flag"];
                $_POST['dim1'] = $myrow["dflt_dim1"];
                $_POST['dim2'] = $myrow["dflt_dim2"];
                $_POST['no_sale'] = $myrow["dflt_no_sale"];
            }
            hidden('selected_id', $this->selected_id);
            hidden('category_id');
        } else
            if ($this->mode != 'CLONE') {
                $_POST['long_description'] = '';
                $_POST['description'] = '';
                $_POST['no_sale'] = 0;

                $company_record = get_company_prefs();

                if (get_post('inventory_account') == "")
                    $_POST['inventory_account'] = $company_record["default_inventory_act"];

                if (get_post('cogs_account') == "")
                    $_POST['cogs_account'] = $company_record["default_cogs_act"];

                if (get_post('sales_account') == "")
                    $_POST['sales_account'] = $company_record["default_inv_sales_act"];

                if (get_post('adjustment_account') == "")
                    $_POST['adjustment_account'] = $company_record["default_adj_act"];

                if (get_post('assembly_account') == "")
                    $_POST['assembly_account'] = $company_record["default_assembly_act"];
            }

        div_start('details');
        col_start(12);
        bootstrap_set_label_column(3);

        input_text_bootstrap(_("Category Name:"), 'description');

        fieldset_start('Default values for new items');

        item_tax_types(_("Item Tax Type:"), 'tax_type_id');
        stock_item_types(_("Item Type:"), 'mb_flag', null, true);
        stock_units(_("Units of Measure:"), 'units');
        check_bootstrap(_("Exclude from sales:"), 'no_sale');

        gl_accounts_bootstrap(_("Sales Account:"), 'sales_account');

        if (is_service($_POST['mb_flag'])) {
            gl_accounts_bootstrap(_("C.O.G.S. Account:"), 'cogs_account');
            hidden('inventory_account', $_POST['inventory_account']);
            hidden('adjustment_account', $_POST['adjustment_account']);
        } else {
            gl_accounts_bootstrap(_("Inventory Account:"), 'inventory_account', $_POST['inventory_account']);

            gl_accounts_bootstrap(_("C.O.G.S. Account:"), 'cogs_account', $_POST['cogs_account']);
            gl_accounts_bootstrap(_("Inventory Adjustments Account:"), 'adjustment_account', $_POST['adjustment_account']);
        }

        if (is_manufactured($_POST['mb_flag']))
            gl_accounts_bootstrap(_("Item Assembly Costs Account:"), 'assembly_account', $_POST['assembly_account']);
        else
            hidden('assembly_account', $_POST['assembly_account']);

        $dim = get_company_pref('use_dimension');
        if ($dim >= 1) {
            dimensions_bootstrap(_("Dimension") . " 1", 'dim1', null, true, " ", false, 1);
            if ($dim > 1)
                dimensions_bootstrap(_("Dimension") . " 2", 'dim2', null, true, " ", false, 2);
        }
        if ($dim < 1)
            hidden('dim1', 0);
        if ($dim < 2)
            hidden('dim2', 0);

        if (defined('COUNTRY') && COUNTRY == 60) {
            echo get_instance()->finput->msic(_("Industry Code:"), 'gst_03_box', null, 'row');
        } else {
            hidden('gst_03_box');
        }
        fieldset_end();

        col_end();
        div_end();
    }
}