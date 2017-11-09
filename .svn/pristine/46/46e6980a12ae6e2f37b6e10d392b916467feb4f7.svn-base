<?php

class ProductsAdjustment
{

    function __construct()
    {
        $this->validation();
        $this->check_input_get();
        $this->check_submit();
        $this->cart = $_SESSION['adj_items'];
    }

    function index()
    {}

    function form()
    {
        start_form();
        box_start();
        $this->form_header();

        box_start('Adjustment Items');
        div_start('items_table');
        $this->form_items();
        div_end();

        row_start('justify-content-md-center');
        col_start(12,'col-md-8');
        if( !isMobile() ){
            bootstrap_set_label_column(2);
        }
        input_textarea_bootstrap('Memo', 'memo_');
        col_end();
        row_end();


        box_footer_start();
        submit('Process', _("Process Adjustment"),true, '', 'default','mail-forward');
        submit('Update', _("Update"),true, '', 'default','save');
        box_footer_end();

        box_end();
        end_form();
    }

    private function form_header()
    {
        global $Refs;
        row_start();

        col_start(12,'col-md-4');
        locations_bootstrap(_("Location"), 'StockLocation', null);
        input_ref(_("Reference"), 'ref', '', $Refs->get_next(ST_INVADJUST));

        col_start(12,'col-md-4');
        movement_types(_("Detail"), 'type', null);

        if (! isset($_POST['Increase']))
            $_POST['Increase'] = 1;

        yesno_bootstrap(_("Type"), 'Increase', $_POST['Increase'], _("Positive Adjustment"), _("Negative Adjustment"));

        col_start(12,'col-md-4');
        // date_row(_("Date:"), 'AdjDate', '', true);
        input_date_bootstrap('Date', 'AdjDate');
        
        row_end();
    }

    private function form_items()
    {
        start_table(TABLESTYLE);
        $th = array(
            'code'=>array('label'=>_("Item Code"),'width'=>'15%'),
            _("Item Description"),
            'qty'=>array('label'=>_("Quantity"),'width'=>'10%','class'=>'text-center'),
            'unit'=>array('label'=>_("Unit"),'class'=>'text-center'),
            _("Unit Cost"),
            _("Total"),
           'edit'=>array('label'=>'','width'=>'5%')
        );
        if (count($this->cart->line_items))
            $th[] = '';

        table_header($th);
        $total = 0;
        $k = 0; // row colour counter

        $low_stock = $this->cart->check_qoh($_POST['StockLocation'], $_POST['AdjDate'], ! $_POST['Increase']);
        $id = find_submit('Edit');
        foreach ($this->cart->line_items as $line_no => $stock_item) {

            $total += ($stock_item->standard_cost * $stock_item->quantity);

            if ($id != $line_no) {
                if (in_array($stock_item->stock_id, $low_stock))
                    start_row("class='stockmankobg'"); // notice low stock status
                else
                    alt_table_row_color($k);

                view_stock_status_cell($stock_item->stock_id);
                label_cell($stock_item->item_description);
                qty_cell($stock_item->quantity, false, get_qty_dec($stock_item->stock_id));
                label_cell($stock_item->units);
                amount_decimal_cell($stock_item->standard_cost);
                amount_cell($stock_item->standard_cost * $stock_item->quantity);

                edit_button_cell("Edit$line_no", _("Edit"), _('Edit document line'));
                delete_button_cell("Delete$line_no", _("Delete"), _('Remove line from document'));
                end_row();
            } else {
                $this->item_edit($line_no);
                // adjustment_edit_item_controls($this->cart, $line_no);
            }
        }

        if ($id == - 1)
            $this->item_edit();
            // adjustment_edit_item_controls($this->cart);

        label_row(_("Total"), number_format2($total, user_price_dec()), "align=right colspan=5", "align=right", 2);

        end_table();
        if ($low_stock)
            display_note(_("Marked items have insufficient quantities in stock as on day of adjustment."), 0, 1, "class='stockmankofg'");
    }
    private function item_edit($line_no = -1)
    {
        global $Ajax;
        start_row();

        $dec2 = 0;
        $id = find_submit('Edit');
        if ($line_no != - 1 && $line_no == $id) {
            $_POST['stock_id'] = $this->cart->line_items[$id]->stock_id;
            $_POST['qty'] = qty_format($this->cart->line_items[$id]->quantity, $this->cart->line_items[$id]->stock_id, $dec);
            // $_POST['std_cost'] = price_format($order->line_items[$id]->standard_cost);
            $_POST['std_cost'] = price_decimal_format($this->cart->line_items[$id]->standard_cost, $dec2);
            $_POST['units'] = $this->cart->line_items[$id]->units;

            hidden('stock_id', $_POST['stock_id']);
            label_cell($_POST['stock_id']);
            label_cell($this->cart->line_items[$id]->item_description, 'nowrap');
            $Ajax->activate('items_table');
        } else {
            stock_costable_items_list_cells(null, 'stock_id', null, false, true);
            if (list_updated('stock_id')) {
                $Ajax->activate('units');
                $Ajax->activate('qty');
                $Ajax->activate('std_cost');
            }

            $item_info = get_item_edit_info($_POST['stock_id']);
            $dec = $item_info['decimals'];
            $_POST['qty'] = number_format2(0, $dec);
            // $_POST['std_cost'] = price_format($item_info["standard_cost"]);
            $_POST['std_cost'] = price_decimal_format($item_info["standard_cost"], $dec2);
            $_POST['units'] = $item_info["units"];
        }

        qty_cells(null, 'qty', $_POST['qty'], null, null, $dec);
        label_cell($_POST['units'], '', 'units');

        // amount_cells(null, 'std_cost', $_POST['std_cost']);
        amount_cells(null, 'std_cost', null, null, null, $dec2);
        label_cell("&nbsp;");

        if ($id != - 1) {
            button_cell('UpdateItem', _("Update"), _('Confirm changes'), ICON_UPDATE);
            button_cell('CancelItemChanges', _("Cancel"), _('Cancel changes'), ICON_CANCEL);
            hidden('LineNo', $line_no);
            set_focus('qty');
        } else {
            submit_cells('AddItem', _("Add Item"), "colspan=2", _('Add new item to document'), true);
        }

        end_row();
    }

    private function validation()
    {
        check_db_has_costable_items(_("There are no inventory items defined in the system which can be adjusted (Purchased or Manufactured)."));
        check_db_has_movement_types(_("There are no inventory movement types defined in the system. Please define at least one inventory adjustment type."));
    }

    private function check_input_get()
    {
        if (isset($_GET['AddedID'])) {
            $trans_no = $_GET['AddedID'];
            $trans_type = ST_INVADJUST;
            
            display_notification(_("Inventory transfer has been processed"));
            
            box_start();
            row_start();
            
            col_start(12);
            mt_list_start('Actions', '', 'blue');
            
            mt_list_tran_view(_("&View This adjustment"), $trans_type, $trans_no);
            mt_list_gl_view(_("View the GL &Postings for this Adjustment"),$trans_type,$trans_no);
            mt_list_link(_("Enter &Another Adjustment"), $_SERVER['PHP_SELF']);
            mt_list_link(_("Add an Attachment"), "admin/attachments.php?filterType=$trans_type&trans_no=$trans_no");

            row_end();
            box_footer();
            box_end();
            display_footer_exit();
        }
    }

    private function check_submit()
    {
        if (isset($_POST['Process']) && can_process()) {

            $trans_no = add_stock_adjustment($_SESSION['adj_items']->line_items, $_POST['StockLocation'], $_POST['AdjDate'], $_POST['type'], $_POST['Increase'], $_POST['ref'], $_POST['memo_']);
            new_doc_date($_POST['AdjDate']);
            $_SESSION['adj_items']->clear_items();
            unset($_SESSION['adj_items']);

            meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
        } /* end of process credit note */

        // -----------------------------------------------------------------------------------------------
        $id = find_submit('Delete');
        if ($id != - 1)
            handle_delete_item($id);

        if (isset($_POST['AddItem']))
            handle_new_item();

        if (isset($_POST['UpdateItem']))
            handle_update_item();

        if (isset($_POST['CancelItemChanges'])) {
            line_start_focus();
        }
        // -----------------------------------------------------------------------------------------------

        if (isset($_GET['NewAdjustment']) || ! isset($_SESSION['adj_items'])) {
            handle_new_order();
        }
    }
}