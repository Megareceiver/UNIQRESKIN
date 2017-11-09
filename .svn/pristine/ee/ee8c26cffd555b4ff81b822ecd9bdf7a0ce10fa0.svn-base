<?php

class ProductsManageCost
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        if ( @$_GET['popup']) {
            return $this->popup();
        }
        box_start("");
        col_start(8, 'class="col-md-offset-1"');
        bootstrap_set_label_column(4);
        if (! @$_GET['popup']) {
            stock_items_bootstrap('Item', 'stock_id', null, false, true);
        }

        set_global_stock_item($_POST['stock_id']);

        $this->detail();
        col_end();
        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        box_footer_end();

        box_end();
    }
    private function popup(){
        row_start('col justify-content-center');
        col_start(8, 'class="col-md-offset-1"');
        bootstrap_set_label_column(4);
        if (! @$_GET['popup']) {
            stock_items_bootstrap('Item', 'stock_id', null, false, true);
        }

        set_global_stock_item($_POST['stock_id']);

        $this->detail();
        row_end();


    }

    private function detail()
    {


        div_start('cost_table',null,false,'style="padding-top:15px;"');

        $myrow = get_item($_POST['stock_id']);

//         start_table(TABLESTYLE2,'class="table table-striped table-bordered table-hover tablestyle"');
        $dec1 = $dec2 = $dec3 = 0;
        $_POST['material_cost'] = price_decimal_format($myrow["material_cost"], $dec1);
        $_POST['labour_cost'] = price_decimal_format($myrow["labour_cost"], $dec2);
        $_POST['overhead_cost'] = price_decimal_format($myrow["overhead_cost"], $dec3);

//         amount_row(_("Standard Material Cost Per Unit"), "material_cost", null, "class='tableheader2'", null, $dec1);
        input_money('Standard Material Cost Per Unit','material_cost');

        if (@$_GET['popup']) {
            hidden('_tabs_sel', get_post('_tabs_sel'));
            hidden('popup', @$_GET['popup']);
        }
        if ($myrow["mb_flag"] == 'M') {
            input_money('Standard Labour Cost Per Unit','labour_cost');
            input_money('Standard Overhead Cost Per Unit','overhead_cost');
//             amount_row(_("Standard Labour Cost Per Unit"), "labour_cost", null, "class='tableheader2'", null, $dec2);
//             amount_row(_("Standard Overhead Cost Per Unit"), "overhead_cost", null, "class='tableheader2'", null, $dec3);
        } else {
            hidden("labour_cost", 0);
            hidden("overhead_cost", 0);
        }

//         end_table(1);


        div_end();

    }
}