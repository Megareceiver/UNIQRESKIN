<?php

class ProductsManagePrice
{

    var $id = 0;
    var $mode = NULL;
    var $calculated = false;
    var $price_id = -1;

    function __construct()
    {
        $this->price_id = post_edit('Edit');
        
    }

    function index()
    {
        
        if (! @$_GET['popup']) {
            start_form();
            box_start("");

            row_start();
            col_start(9);
            sales_items('Item', 'stock_id', NULL, false, true, '', array(
                'editable' => false
            ));

           
            col_end();
            row_end();
         
        }
        $this->id = input_val('stock_id');
        row_start(null,'style="width:100%"');
        col_start(12, 'style="padding: 15px;"');
        $this->prices_list();


        if (@$_GET['popup']) {
            hidden('_tabs_sel', get_post('_tabs_sel'));
            hidden('popup', @$_GET['popup']);
        }
        
        $selected_id = input_post('selected_id');
        if( $selected_id > 0 ){
            $this->price_id = $selected_id;
        }
        hidden('selected_id', $this->price_id );
        hidden('sales_type_id', $this->price_id );
        
        row_end();
//         if ($this->mode == 'Edit' AND $this->price_id > 0) {
            
            $this->price_item();
//         }

        

        if (! @$_GET['popup']) {
            box_footer_start();
            submit_add_or_update( $this->price_id < 1 , '', 'both');
            box_form_end();
            
            box_end();
            end_form();
        }
    }

    function popup(){
        row_start(null,'style="width:100%"');
        col_start(12, 'style="padding: 15px;"');
        $this->prices_list();
        hidden('selected_id');
        row_end();

        $this->price_item();


    }


    private function prices_list()
    {
        $prices_list = get_prices($this->id);

        div_start('price_table');

        start_table(TABLESTYLE, 'class="table table-striped table-bordered table-hover tablestyle"');

        $th = array(
            _("Currency"),
            _("Sales Type"),
            'price'=>array('label'=>_("Price"),'class'=>'text-right'),
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
        $k = 0; // row colour counter
        $this->calculated = false;
        while ($myrow = db_fetch($prices_list)) {

            alt_table_row_color($k);

            label_cell($myrow["curr_abrev"]);
            label_cell($myrow["sales_type"]);
            amount_cell($myrow["price"]);
            edit_button_cell("Edit" . $myrow['id'], _("Edit"));
            delete_button_cell("Delete" . $myrow['id'], _("Delete"));
            end_row();
        }
        end_table();
        if (db_num_rows($prices_list) == 0) {
            if (get_company_pref('add_pct') != - 1)
                $this->calculated = true;
//             display_note(_("There are no prices set up for this part."), 1);
        }
        col_end();

    }

    private function price_item()
    {
        if ( $this->mode == 'Edit') {
            $myrow = get_stock_price($this->price_id);
            $_POST['curr_abrev'] = $myrow["curr_abrev"];
            $_POST['sales_type_id'] = $myrow["sales_type_id"];
            $_POST['price'] = price_format($myrow["price"]);
        }

        div_start('price_details',$trigger = null, $non_ajax = false, 'class="col"');
        row_start('justify-content-center');
        col_start(8);
        bootstrap_set_label_column(NULL);

        currency_bootstrap(_("Currency"), 'curr_abrev', null, true);
        sales_types_bootstrap( "Sales Type", 'sales_type_id', null, true);

        if (! isset($_POST['price'])) {
            $_POST['price'] = price_format(get_kit_price(get_post('stock_id'), get_post('curr_abrev'), get_post('sales_type_id')));
        }

        $kit = get_item_code_dflts($_POST['stock_id']);
        input_text_addon_both('Price','price',null, 'per '.$kit["units"],'$ ');
//         input_money(_("Price:"), 'price', null, _('per') . ' ' . $kit["units"]);

        if ($this->calculated){
            display_notification_centered(_("The price is calculated."));
//             display_note(_("The price is calculated."), 0, 1);
        }


        row_end();
//         submit_add_or_update_center($selected_id == - 1, '', 'both');
        div_end();
    }
}