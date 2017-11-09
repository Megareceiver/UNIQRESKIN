<?php

class ProductsManagePurchasingData
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        box_start("");
        if (! @$_GET['popup']) {
            col_start(10);
            stock_items_bootstrap('Item', 'stock_id', null, false, true);
            col_end();
            // Chaitanya : All items can be purchased
        }

        set_global_stock_item($_POST['stock_id']);

        $mb_flag = get_mb_flag($_POST['stock_id']);

        if ($mb_flag == - 1) {
            display_error(_("Entered item is not defined. Please re-enter."));
            set_focus('stock_id');
        } else {
            col_start(12,'style="padding-top: 15px;"');
            $this->listview();
            col_end();
        }
        if (! @$_GET['popup']) {
            box_start("");
        }
        
        $this->detail();

        if (! @$_GET['popup']) {
            box_footer_start();
            submit_add_or_update_center($this->selected_id == - 1, '', 'both');
            box_footer_end();
            
            box_end();
        }
        
    }

    function popup(){

        set_global_stock_item($_POST['stock_id']);

        $mb_flag = get_mb_flag($_POST['stock_id']);

        if ($mb_flag == - 1) {
            display_error(_("Entered item is not defined. Please re-enter."));
            set_focus('stock_id');
        } else {
            //             row_start();
            col_start(12,'style="padding-top: 15px;"');
            $this->listview();
            col_end();
            //             row_end();
        }

        $this->detail();


    }

    private function listview()
    {
        $result = get_items_purchasing_data($_POST['stock_id']);
        div_start('price_table');

        if (db_num_rows($result) == 0) {
            display_notification(_("There is no purchasing data set up for the part selected"));
        } else {
            start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');

            $th = array(
                _("Supplier"),
                _("Price"),
                _("Currency"),
                _("Supplier's Unit"),
                _("Conversion Factor"),
                _("Supplier's Description"),
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

                label_cell($myrow["supp_name"]);
                amount_decimal_cell($myrow["price"]);
                label_cell($myrow["curr_code"]);
                label_cell($myrow["suppliers_uom"]);
                qty_cell($myrow['conversion_factor'], false, 'max');
                label_cell($myrow["supplier_description"]);
                edit_button_cell("Edit" . $myrow['supplier_id'], _("Edit"));
                delete_button_cell("Delete" . $myrow['supplier_id'], _("Delete"));
                end_row();

                $j ++;
                If ($j == 12) {
                    $j = 1;
                    table_header($th);
                } // end of page full new headings
            } // end of while loop

            end_table();
        }
        div_end();
    }

    private function detail()
    {
        row_start('col justify-content-center');
        col_start(8, 'class="col-md-offset-1"');
        bootstrap_set_label_column(4);

        // -----------------------------------------------------------------------------------------------

        $dec2 = 6;

        if ($this->mode == 'Edit') {
            $myrow = get_item_purchasing_data($this->selected_id, $_POST['stock_id']);

            $supp_name = $myrow["supp_name"];
            $_POST['price'] = price_decimal_format($myrow["price"], $dec2);
            $_POST['suppliers_uom'] = $myrow["suppliers_uom"];
            $_POST['supplier_description'] = $myrow["supplier_description"];
            $_POST['conversion_factor'] = maxprec_format($myrow["conversion_factor"]);
        }


        hidden('selected_id', $this->selected_id);
        if (@$_GET['popup']) {
            hidden('_tabs_sel', get_post('_tabs_sel'));
            hidden('popup', @$_GET['popup']);
        }


        if ($this->mode == 'Edit') {
            hidden('supplier_id');
            input_label_bootstrap(_("Supplier"), null, $supp_name);
        } else {
            supplier_list_bootstrap(_("Supplier"), 'supplier_id', null, false, true);
            $_POST['price'] = $_POST['suppliers_uom'] = $_POST['conversion_factor'] = $_POST['supplier_description'] = "";
        }
//         amount_row(_("Price:"), 'price', null, '', get_supplier_currency($this->selected_id), $dec2);

        input_money(_("Price"), 'price',null,get_supplier_currency($this->selected_id));

        input_text_bootstrap(_("Suppliers Unit of Measure"), 'suppliers_uom');

        if (! isset($_POST['conversion_factor']) || $_POST['conversion_factor'] == "") {
            $_POST['conversion_factor'] = maxprec_format(1);
        }

//         amount_row(_("Conversion Factor (to our UOM):"), 'conversion_factor', maxprec_format($_POST['conversion_factor']), null, null, 'max');
        input_money( "Conversion Factor (to our UOM)" , 'conversion_factor');
        input_text_bootstrap(_("Supplier's Code or Description:"), 'supplier_description');


        col_end();
        row_end();
    }
}