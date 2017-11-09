<?php

class SalesInquirySalesOrders
{

    function __construct()
    {
        $this->get_val();
        $this->ajax_updates();
        $this->bootstrap = get_instance()->bootstrap;
    }

    function index(){

    }

    function view(){

        if (!@$_GET['popup']){
            start_form();
            box_start();
        }

        $this->fillter();

        $this->transactions_table();


        if (!@$_GET['popup']){
            box_footer_start();
            submit('Update', _("Update"), true, '', true);
            box_footer_end();
            box_end();
            end_form();
        }

    }

    private function get_val()
    {
        if (isset($_GET['selected_customer'])) {
            $this->selected_customer = $_GET['selected_customer'];
        } elseif (isset($_POST['selected_customer'])) {
            $this->selected_customer = $_POST['selected_customer'];
        } else {
            $this->selected_customer = - 1;
        }
    }
    private function ajax_updates(){
        $id = find_submit('_chgtpl');
        if ($id != -1)
            change_tpl_flag($id);

        if (isset($_POST['Update']) && isset($_POST['last'])) {
            foreach($_POST['last'] as $id => $value)
                if ($value != check_value('chgtpl'.$id))
                    change_tpl_flag($id);
        }
    }

    var $tran_type, $selected_customer;

    function fillter()
    {
        global $Ajax;
        $trans_type = $this->tran_type;
        $show_dates = ! in_array($_POST['order_view_mode'], array(
            'OutstandingOnly',
            'InvoiceTemplates',
            'DeliveryTemplates'
        ));

        if (get_post('_OrderNumber_changed') || get_post('_OrderReference_changed')) { // enable/disable selection controls

            $disable = get_post('OrderNumber') !== '' || get_post('OrderReference') !== '';

            if ($show_dates) {
                $Ajax->addDisable(true, 'OrdersAfterDate', $disable);
                $Ajax->addDisable(true, 'OrdersToDate', $disable);
            }

            $Ajax->activate('orders_tbl');
        }

        row_start('inquiry-filter justify-content-center');;
        col_start(12,'col-md-2');
        input_text_bootstrap( '#', 'OrderNumber', '', null, true);
        col_start(12,'col-md-2');
        input_text_bootstrap(_("Ref"), 'OrderReference', '', null,true);

        if ($show_dates) {
            col_start(12,'col-md-2');
            input_date_bootstrap("From", 'OrdersAfterDate', NULL, false, false, - 30);
            col_start(12,'col-md-2');
            input_date_bootstrap( "To", 'OrdersToDate', NULL);
        }
        col_start(12,'col-md-3');
        locations_bootstrap( _("Location"), 'StockLocation', null, true, true);


        row_start('inquiry-filter justify-content-center');
        col_start(12,'col-md-5');
        stock_items_bootstrap( _("Product"), 'SelectStockFromList', null, true, true);

        if (! @$_GET['popup']){
            col_start(12,'col-md-3');
            customer_list_bootstrap( _("Customer"), 'customer_id', input_val('customer_id'), true, true);
        }


        if ($trans_type == ST_SALESQUOTE){
            col_start(12,'col-md-2');
            if( !isMobile() ){
                bootstrap_set_label_column(6);
            }
            
            check_bootstrap('Show All', 'show_all');
        }

        col_start(12,'col-md-1');
        submit_bootstrap( 'SearchOrders', _("Search"), _('Select documents'), 'default');

        hidden('order_view_mode', $_POST['order_view_mode']);
        hidden('type', $trans_type);

       row_end();
    }

    function transactions_table()
    {
        $trans_type = $this->tran_type;

        $sql = get_sql_for_sales_orders_view($this->selected_customer, $this->tran_type, input_val('OrderNumber'), input_val('order_view_mode'), @$selected_stock_item, input_val('OrdersAfterDate'), input_val('OrdersToDate'), input_val('OrderReference'), input_val('StockLocation'), input_val('customer_id') );

        if ($trans_type == ST_SALESORDER)
            $cols = array(
                _("Order #") => array(
                    'fun' => 'view_link',
                    'align'=>'center'
                ),
                _("Ref") => array(
                    'type' => 'sorder.reference',
                    'ord' => ''
                ),
                _("Customer") => array(
                    'type' => 'debtor.name',
                    'ord' => ''
                ),
                _("Branch"),
                _("Cust Order Ref"),
                _("Order Date") => array(
                    'type' => 'date',
                    'ord' => ''
                ),

                // _("Required By") =>array('type'=>'date', 'ord'=>''),
                _("Delivery To"),
                _("Order Total") => array(
                    'type' => 'amount',
                    'ord' => ''
                ),
                'Type' => 'skip',
                _("Currency") => array(
                    'align' => 'center'
                )
            );
        else
            $cols = array(
                _("Quote #") => array(
                    'fun' => 'view_link',
                    'align'=>'center'
                ),
                _("Ref"),
                _("Customer"),
                _("Branch"),
                _("Cust Order Ref"),
                _("Quote Date") => 'date',
                _("Valid until") => array(
                    'type' => 'date',
                    'ord' => ''
                ),
                _("Delivery To"),
                _("Quote Total") => array(
                    'type' => 'amount',
                    'ord' => ''
                ),
                'Type' => 'skip',
                _("Currency") => array(
                    'align' => 'center'
                )
            );
        if ($_POST['order_view_mode'] == 'OutstandingOnly') {
            // array_substitute($cols, 4, 1, _("Cust Order Ref"));
            array_append($cols, array(
                'disp'=>array(
                    'insert' => true,
                    'fun' => 'dispatch_link'
                ),
                'edit'=>array(
                    'insert' => true,
                    'fun' => 'edit_link'
                )
            ));
        } elseif ($_POST['order_view_mode'] == 'InvoiceTemplates') {
            array_substitute($cols, 4, 1, _("Description"));
            array_append($cols, array(
                'inv'=>array(
                    'insert' => true,
                    'fun' => 'invoice_link'
                )
            ));
        } else
            if ($_POST['order_view_mode'] == 'DeliveryTemplates') {
                array_substitute($cols, 4, 1, _("Description"));
                array_append($cols, array(
                    'deli'=>array(
                        'insert' => true,
                        'fun' => 'delivery_link'
                    )
                ));
            } elseif ($trans_type == ST_SALESQUOTE) {
                array_append($cols, array(
                    'edit'=>array(
                        'insert' => true,
                        'fun' => 'edit_link'
                    ),
                    'order'=>array(
                        'insert' => true,
                        'fun' => 'order_link'
                    ),
                    'prt'=>array(
                        'insert' => true,
                        'fun' => 'prt_link'
                    )
                ));
            } elseif ($trans_type == ST_SALESORDER) {
                array_append($cols, array(
                    _("Tmpl") => array(
                        'insert' => true,
                        'fun' => 'tmpl_checkbox'
                    ),
                    'edit'=>array(
                        'insert' => true,
                        'fun' => 'edit_link'
                    ),
                    'prt'=>array(
                        'insert' => true,
                        'fun' => 'prt_link'
                    )
                ));
            }
        ;

        $table = & new_db_pager('orders_tbl', $sql, $cols);
        $table->set_marker('check_overdue', _("Marked items are overdue."));

        echo db_table_responsive($table);
    }
}