<?php

class ProductsStockStatus
{

    function __construct()
    {
        $this->validation();
        $this->check_input_get();
        $this->check_submit();
    }

    function view(){
        if (!@$_GET['popup']){
            box_start();
            $this->filter();
            box_end();
        }
        

        div_start('status_tbl',null,false,'style="width:100%;"');
            if (!@$_GET['popup']){
                box_start();
            }
            
            $this->items();
            if (!@$_GET['popup']){
                box_footer();
                box_end();
            }
            

        div_end();


    }
    private function filter(){
        
            if( isMobile() ){
                bootstrap_set_label_column(2);
            }
            row_start('justify-content-md-center');
            col_start(12,'col-md-8');
            stock_items_bootstrap('Product Item','stock_id',null,true,true);
            row_end();
            bootstrap_set_label_column(false);
        
    }
    private function items(){
        set_global_stock_item($_POST['stock_id']);

        $mb_flag = get_mb_flag($_POST['stock_id']);
        $kitset_or_service = false;

        if (is_service($mb_flag))
        {
            display_notification(_("This is a service and cannot have a stock holding, only the total quantity on outstanding sales orders is shown."), 0, 1);
            $kitset_or_service = true;
        }

        $loc_details = get_loc_details($_POST['stock_id']);

        start_table(TABLESTYLE, array('class'=>'table table-striped table-bordered table-hover'));

        if ($kitset_or_service == true)
        {
            $th = array(_("Location"), _("Demand"));
        }
        else
        {
            $th = array(_("Location"), _("Quantity On Hand"), _("Re-Order Level"),
                _("Demand"), _("Available"), _("On Order"));
        }
        table_header($th);
        $dec = get_qty_dec($_POST['stock_id']);
        $j = 1;
        $k = 0; //row colour counter

        while ($myrow = db_fetch($loc_details))
        {

            alt_table_row_color($k);

            $demand_qty = get_demand_qty($_POST['stock_id'], $myrow["loc_code"]);
            $demand_qty += get_demand_asm_qty($_POST['stock_id'], $myrow["loc_code"]);

            $qoh = get_qoh_on_date($_POST['stock_id'], $myrow["loc_code"]);

            if ($kitset_or_service == false)
            {
                $qoo = get_on_porder_qty($_POST['stock_id'], $myrow["loc_code"]);
                $qoo += get_on_worder_qty($_POST['stock_id'], $myrow["loc_code"]);
                label_cell($myrow["location_name"]);
                qty_cell($qoh, false, $dec);
                qty_cell($myrow["reorder_level"], false, $dec);
                qty_cell($demand_qty, false, $dec);
                qty_cell($qoh - $demand_qty, false, $dec);
                qty_cell($qoo, false, $dec);
                end_row();

            }
            else
            {
                /* It must be a service or kitset part */
                label_cell($myrow["location_name"]);
                qty_cell($demand_qty, false, $dec);
                end_row();

            }
            $j++;
            If ($j == 12)
            {
                $j = 1;
                table_header($th);
            }
        }

        end_table();
    }

    private function check_input_get()
    {
        if (isset($_GET['stock_id']))
            $_POST['stock_id'] = $_GET['stock_id'];


        if (!isset($_POST['stock_id']))
            $_POST['stock_id'] = get_global_stock_item();
    }
    private function validation()
    {
        check_db_has_stock_items(_("There are no items defined in the system."));
    }
    private function check_submit()
    {
        global $Ajax;
        if (list_updated('stock_id'))
            $Ajax->activate('status_tbl');
    }

}