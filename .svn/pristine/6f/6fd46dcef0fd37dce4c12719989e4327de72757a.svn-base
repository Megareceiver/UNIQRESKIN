<?php

class ProductsTransfers
{

    function __construct()
    {
        $this->validation();
        $this->check_input_get();
        $this->check_submit();
        $this->cart = $_SESSION['transfer_items'];
    }

    function index()
    {}

    function form()
    {
        start_form();
        box_start();
        $this->form_header();

        box_start('Items');
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

        submit('Process', _("Process Transfer"),true, '', 'default','mail-forward');
        submit('Update', _("Update"),true, '', 'default','save');
        box_footer_end();
        box_end();
        end_form();
    }

    private function form_header()
    {
        global $Refs;
        if( !isMobile() ){
            bootstrap_set_label_column(4);
        }
        row_start();

        col_start(12,'col-md-4');
        locations_bootstrap(_("From Location"), 'FromStockLocation', null);
        locations_bootstrap(_("To Location"), 'ToStockLocation', null);

        col_start(12,'col-md-4');
        input_ref(_("Reference"), 'ref', $Refs->get_next(ST_LOCTRANSFER));
        input_date_bootstrap(_("Date"), 'AdjDate', '', true);

        col_start(12,'col-md-4');
        movement_types(_("Transfer Type"), 'type', null);

        row_end();
    }

    private function form_items()
    {

        // display_heading($title);
        start_table(TABLESTYLE, "width=80%");
        $th = array(
            'code'=>array('label'=>_("Item Code"),'width'=>'15%'),
            _("Item Description"),
            'qty'=>array('label'=>_("Quantity"),'width'=>'10%','class'=>'text-center'),
            'unit'=>array('label'=>_("Unit"),'class'=>'text-center'),
            'edit'=>array('label'=>'','width'=>'5%')
        );
        if (count($this->cart->line_items))
            $th[] = '';
        table_header($th);
        $subtotal = 0;
        $k = 0; // row colour counter

        $low_stock = $this->cart->check_qoh($_POST['FromStockLocation'], $_POST['AdjDate'], true);
        $id = find_submit('Edit');
        foreach ($this->cart->line_items as $line_no => $stock_item) {

            if ($id != $line_no) {
                if (in_array($stock_item->stock_id, $low_stock))
                    start_row("class='stockmankobg'"); // notice low stock status
                else
                    alt_table_row_color($k);

                view_stock_status_cell($stock_item->stock_id);
                label_cell($stock_item->item_description);
                qty_cell($stock_item->quantity, false, get_qty_dec($stock_item->stock_id));
                label_cell($stock_item->units);

                edit_button_cell("Edit$line_no", _("Edit"), _('Edit document line'));
                delete_button_cell("Delete$line_no", _("Delete"), _('Remove line from document'));
                end_row();
            } else {
                $this->item_edit($line_no);
            }
        }

        if ($id == - 1) {
            // transfer_edit_item_controls();
            $this->item_edit();
        }

        end_table();

        if ($low_stock)
            display_note(_("Marked items have insufficient quantities in stock as on day of transfer."), 0, 1, "class='stockmankofg'");
    }
    private function item_edit( $line_no = -1)
    {
        global $Ajax;

        start_row();
        $id = find_submit('Edit');
        if ($line_no != - 1 && $line_no == $id) {
            $_POST['stock_id'] = $this->cart->line_items[$id]->stock_id;
            $_POST['qty'] = qty_format($this->cart->line_items[$id]->quantity, $this->cart->line_items[$id]->stock_id, $dec);
            $_POST['units'] = $this->cart->line_items[$id]->units;

            hidden('stock_id', $_POST['stock_id']);
            label_cell($_POST['stock_id']);
            label_cell($this->cart->line_items[$id]->item_description);
            $Ajax->activate('items_table');
        } else {
            stock_costable_items_list_cells(null, 'stock_id', null, false, true);

            if (list_updated('stock_id')) {
                $Ajax->activate('units');
                $Ajax->activate('qty');
            }

            $item_info = get_item_edit_info($_POST['stock_id']);

            $dec = $item_info['decimals'];
            $_POST['qty'] = number_format2(0, $dec);
            $_POST['units'] = $item_info["units"];
        }


        small_qty_cells(null, 'qty', $_POST['qty'], null, null, $dec);
        label_cell($_POST['units'], 'align="center"', 'units');

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
        check_db_has_costable_items(_("There are no inventory items defined in the system (Purchased or manufactured items)."));

        check_db_has_movement_types(_("There are no inventory movement types defined in the system. Please define at least one inventory adjustment type."));
    }

    private function check_input_get()
    {
        if (isset($_GET['AddedID'])) {
            $trans_no = $_GET['AddedID'];
            $trans_type = ST_LOCTRANSFER;

            display_notification(_("Inventory transfer has been processed"));
            
            box_start();
            row_start();
            
            col_start(12);
            mt_list_start('Actions', '', 'blue');

            mt_list_tran_view(_("&View This transfer"), $trans_type, $trans_no);
            mt_list_link(_("Enter &Another Inventory Transfer"), $_SERVER['PHP_SELF']);
            
            row_end();
            box_footer();
            box_end();
            display_footer_exit();
        }

        if (isset($_GET['NewTransfer']) || ! isset($_SESSION['transfer_items'])) {
            handle_new_order();
        }
    }

    private function check_submit()
    {
        if (isset($_POST['Process'])) {
            global $Refs, $SysPrefs;
            $tr = &$_SESSION['transfer_items'];
            $input_error = 0;

            if (count($tr->line_items) == 0) {
                display_error(_("You must enter at least one non empty item line."));
                set_focus('stock_id');
                $input_error = 1;
            }
            if (! $Refs->is_valid($_POST['ref'])) {
                display_error(_("You must enter a reference."));
                set_focus('ref');
                $input_error = 1;
            } elseif (! is_new_reference($_POST['ref'], ST_LOCTRANSFER)) {
                display_error(_("The entered reference is already in use."));
                set_focus('ref');
                $input_error = 1;
            } elseif (! is_date($_POST['AdjDate'])) {
                display_error(_("The entered transfer date is invalid."));
                set_focus('AdjDate');
                $input_error = 1;
            } elseif (! is_date_in_fiscalyear($_POST['AdjDate'])) {
                display_error(_("The entered date is not in fiscal year."));
                set_focus('AdjDate');
                $input_error = 1;
            } elseif ($_POST['FromStockLocation'] == $_POST['ToStockLocation']) {
                display_error(_("The locations to transfer from and to must be different."));
                set_focus('FromStockLocation');
                $input_error = 1;
            } elseif (! $SysPrefs->allow_negative_stock()) {
                $low_stock = $tr->check_qoh($_POST['FromStockLocation'], $_POST['AdjDate'], true);

                if ($low_stock) {
                    display_error(_("The transfer cannot be processed because it would cause negative inventory balance in source location for marked items as of document date or later."));
                    $input_error = 1;
                }
            }

            if ($input_error == 1)
                unset($_POST['Process']);
        }

        // -------------------------------------------------------------------------------

        if (isset($_POST['Process'])) {

            $trans_no = add_stock_transfer($_SESSION['transfer_items']->line_items, $_POST['FromStockLocation'], $_POST['ToStockLocation'], $_POST['AdjDate'], $_POST['type'], $_POST['ref'], $_POST['memo_']);
            new_doc_date($_POST['AdjDate']);
            $_SESSION['transfer_items']->clear_items();
            unset($_SESSION['transfer_items']);

            meta_forward($_SERVER['PHP_SELF'], "AddedID=$trans_no");
        } /* end of process credit note */

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
    }
}