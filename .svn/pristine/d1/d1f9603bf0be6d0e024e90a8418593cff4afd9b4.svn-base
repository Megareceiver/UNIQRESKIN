<?php
class PurchasesInquiryPurchaseOrder
{
    var $is_popup = false;
    function __construct()
    {
        $this->page_variables();
        $this->ajax_updates();
        if( isset($_GET['popup']) ){
            $this->is_popup = input_get("popup");
        }
    }

    function view(){
        if( !$this->is_popup ){
            box_start();
        }
        
        $this->fillter();

        $this->transactions_table();
        if( !$this->is_popup ){
            box_footer();
            box_end();
        }
        
    }

    private function page_variables(){

        $order_number = input_val('order_number');

        if (isset($_POST['SelectStockFromList']) &&	($_POST['SelectStockFromList'] != "") &&
            ($_POST['SelectStockFromList'] != ALL_TEXT))
        {
            $selected_stock_item = $_POST['SelectStockFromList'];
        }
        else
        {
            unset($selected_stock_item);
        }
    }

    /*
    * Ajax updates
    */
    private function ajax_updates(){
        global $Ajax;
        if (get_post('SearchOrders'))
        {
            $Ajax->activate('orders_tbl');
        } elseif (get_post('_order_number_changed'))
        {
            $disable = get_post('order_number') !== '';

            $Ajax->addDisable(true, 'OrdersAfterDate', $disable);
            $Ajax->addDisable(true, 'OrdersToDate', $disable);
            $Ajax->addDisable(true, 'StockLocation', $disable);
            $Ajax->addDisable(true, '_SelectStockFromList_edit', $disable);
            $Ajax->addDisable(true, 'SelectStockFromList', $disable);

            if ($disable) {
                $Ajax->addFocus(true, 'order_number');
            } else
                $Ajax->addFocus(true, 'OrdersAfterDate');

            $Ajax->activate('orders_tbl');
        }
    }

    function fillter(){
        row_start('inquiry-filter justify-content-center');
        col_start(12,'col-md-3');
        if( !isMobile() ){
            bootstrap_set_label_column(2);
        }
        input_text_bootstrap( '#', 'order_number', NULL, null, true);
        bootstrap_set_label_column(0);
        col_start(12,'col-md-2');
        input_date_bootstrap( "From", 'OrdersAfterDate', NULL, false, false, - 30);
        col_start(12,'col-md-2');
        input_date_bootstrap("To", 'OrdersToDate', NULL, false, false);
        col_start(12,'col-md-4');
        if( !isMobile() ){
            bootstrap_set_label_column(4);
        }
        locations_bootstrap( _("Into location"), 'StockLocation', null, true, true);

        row_start('inquiry-filter justify-content-center');
        col_start(12,'col-md-6');
        if( !isMobile() ){
            bootstrap_set_label_column(1);
        }
        stock_items_bootstrap( _("Item"), 'SelectStockFromList', null, true, true);

        bootstrap_set_label_column(0);
        if( !$this->is_popup ){
            col_start(12,'col-md-3');
            supplier_list_bootstrap( _("Supplier"), 'supplier_id', null, true, true);
        }
        
        $searchClass = isMobile() ? "offset-6" : "text-right col-md-2";
        col_start(12,$searchClass);
        submit_bootstrap( 'SearchOrders', _("Search"), _('Select documents'), 'default','search');
        row_end();
    }

    function transactions_table(){
        global $all_items;
        $sql = get_sql_for_po_search_completed(!@$_GET['popup'] ? $_POST['supplier_id'] : ALL_TEXT);

        $cols = array(
            _("#") => array('fun'=>'trans_view', 'ord'=>''),
            _("Reference"),
            _("Supplier") => array('ord'=>''),
            _("Location"),
            _("Supplier's Reference"),
            _("Order Date") => array('name'=>'ord_date', 'type'=>'date', 'ord'=>'desc'),
            _("Currency") => array('align'=>'center'),
            _("Order Total") => 'amount',
            array('insert'=>true, 'fun'=>'edit_link'),
            array('insert'=>true, 'fun'=>'prt_link'),
        );

        if (get_post('StockLocation') != $all_items) {
            $cols[_("Location")] = 'skip';
        }
        //---------------------------------------------------------------------------------------------------

        $table =& new_db_pager('orders_tbl', $sql, $cols);

//         $table->width = "80%";

//         display_db_pager($table);
        echo db_table_responsive($table);
    }
}