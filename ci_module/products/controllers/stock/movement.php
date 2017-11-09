<?php
class ProductsStockMovement
{

    function __construct()
    {
        $this->validation();
        $this->check_input_get();
        $this->check_submit();
//         $this->cart = $_SESSION['adj_items'];
    }

    function view(){
        if (!@$_GET['popup']){
            start_form();
            box_start();
        }
            
        $this->filter();
        if (!@$_GET['popup']){
            end_form();
            box_end();
        }
        div_start('doc_tbl');
        if (!@$_GET['popup']){
            
            box_start();
            $this->items();
            
            box_footer();
            box_end();
        } else {
            $this->items();
        }
        div_end();
        
    }
    private function filter(){
        if (!@$_GET['popup']){
            if( !isMobile() ){
                bootstrap_set_label_column(2);
            }
            row_start();
            col_start(12,'col-md-8');
            stock_items_bootstrap('Product Item','stock_id',null,true,true);
            row_end();
            bootstrap_set_label_column(false);
        }

        row_start(null,'style="width:100%;"');
        col_start(12,'col-md-4');
        if( !isMobile() ){
            bootstrap_set_label_column(4);
        }
        locations_bootstrap(_("From Location"), 'StockLocation', null, true);
        bootstrap_set_label_column(false);
        col_start(12,'col-md-3');
        input_date_bootstrap('From', 'AfterDate',null,false,false,-30);
        col_start(12,'col-md-3');
        input_date_bootstrap( "To", 'BeforeDate');

        col_start(12,'col-md-2');
        submit('ShowMoves',_("Show Movements"),true,_('Refresh Inquiry'), 'default','search');
        row_end();

    }
    private function items(){
        set_global_stock_item($_POST['stock_id']);

        $before_date = date2sql(input_val('BeforeDate'));
        $after_date = date2sql(input_val('AfterDate'));
        $display_location = !$_POST['StockLocation'];

        $result = get_stock_movements($_POST['stock_id'], input_val('StockLocation'), $before_date, $after_date);

        start_table(TABLESTYLE);
        $th = array(_("Type"), _("#"), _("Reference"));
        if($display_location) array_push($th, _("Location"));
        if( isMobile() ){
            array_push($th, _("Date"), _("Detail"), _("Qtty In"), _("Qty Out"), _("Qty On Hand"));
        } else {
            array_push($th, _("Date"), _("Detail"), _("Quantity In"), _("Quantity Out"), _("Quantity On Hand"));
        }
        
        table_header($th);

        $before_qty = get_stock_movements_before($_POST['stock_id'], $_POST['StockLocation'], $before_date);

        $after_qty = $before_qty;

        /*
         if (!isset($before_qty_row[0]))
         {
         $after_qty = $before_qty = 0;
         }
         */
        start_row("class='inquirybg'");
        $header_span = $display_location ? 6 : 5;
        label_cell("<b>"._("Quantity on hand before") . " " . input_val('BeforeDate')."</b>", "align=center colspan=$header_span");
        label_cell("&nbsp;", "colspan=2");
        $dec = get_qty_dec($_POST['stock_id']);
        qty_cell($before_qty, false, $dec);
        end_row();

        $j = 1;
        $k = 0; //row colour counter

        $total_in = 0;
        $total_out = 0;

        while ($myrow = db_fetch($result))
        {

            alt_table_row_color($k);

            $trandate = sql2date($myrow["tran_date"]);

            $type_name = systype_name(null,$myrow["type"]);

            if ($myrow["qty"] > 0)
            {
                $quantity_formatted = number_format2($myrow["qty"], $dec);
                $total_in += $myrow["qty"];
            }
            else
            {
                $quantity_formatted = number_format2(-$myrow["qty"], $dec);
                $total_out += -$myrow["qty"];
            }
            $after_qty += $myrow["qty"];

            label_cell($type_name);

            label_cell(get_trans_view_str($myrow["type"], $myrow["trans_no"]));

            label_cell(get_trans_view_str($myrow["type"], $myrow["trans_no"], $myrow["reference"]));
            if($display_location) {
                label_cell($myrow['loc_code']);
            }
            label_cell($trandate);

            $person = $myrow["person_id"];
            $gl_posting = "";

            if (($myrow["type"] == ST_CUSTDELIVERY) || ($myrow["type"] == ST_CUSTCREDIT))
            {
                $cust_row = get_customer_details_from_trans($myrow["type"], $myrow["trans_no"]);

                if (strlen($cust_row['name']) > 0)
                    $person = $cust_row['name'] . " (" . $cust_row['br_name'] . ")";

            }
            elseif ($myrow["type"] == ST_SUPPRECEIVE || $myrow['type'] == ST_SUPPCREDIT)
            {
                // get the supplier name
                $supp_name = get_supplier_name($myrow["person_id"]);

                if (strlen($supp_name) > 0)
                    $person = $supp_name;
            }
            elseif ($myrow["type"] == ST_LOCTRANSFER || $myrow["type"] == ST_INVADJUST)
            {
                // get the adjustment type
                $movement_type = get_movement_type($myrow["person_id"]);
                $person = $movement_type["name"];
            }
            elseif ($myrow["type"]==ST_WORKORDER || $myrow["type"] == ST_MANUISSUE  ||
                $myrow["type"] == ST_MANURECEIVE)
            {
                $person = "";
            }

            label_cell($person);

            label_cell((($myrow["qty"] >= 0) ? $quantity_formatted : ""), "nowrap align=right");
            label_cell((($myrow["qty"] < 0) ? $quantity_formatted : ""), "nowrap align=right");
            qty_cell($after_qty, false, $dec);
            end_row();
            $j++;
            If ($j == 12)
            {
                $j = 1;
                table_header($th);
            }
            //end of page full new headings if
        }
        //end of while loop

        start_row("class='inquirybg'");
        label_cell("<b>"._("Quantity on hand after") . " " . input_val('BeforeDate')."</b>", "align=center colspan=$header_span");
        qty_cell($total_in, false, $dec);
        qty_cell($total_out, false, $dec);
        qty_cell($after_qty, false, $dec);
        end_row();

        end_table(1);
    }


    private function validation(){
        check_db_has_stock_items(_("There are no items defined in the system."));
    }
    private function check_input_get(){
        if (isset($_GET['stock_id']))
        {
            $_POST['stock_id'] = $_GET['stock_id'];
        }
        if (!isset($_POST['stock_id']))
            $_POST['stock_id'] = get_global_stock_item();
    }
    private function check_submit(){
        global $Ajax;
        if(get_post('ShowMoves'))
        {
            $Ajax->activate('doc_tbl');
        }

    }
}