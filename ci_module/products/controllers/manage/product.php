<?php

class ProductsManageProduct
{

    var $new_item = false;
    var $id = 0;
    function __construct()
    {
        $this->new_item = get_post('stock_id')=='' || get_post('cancel') || get_post('clone');
//         $_POST['stock_id'] = "P001";
    }

    function index()
    {
        $this->id = input_post('stock_id');
        if( is_null($this->id) ){
            $this->id = -1;
        }
        box_start();
        start_form(true);
        

        if (db_has_stock_items()) {
            row_start();
            col_start(12,'col-md-9');
            if( !isMobile() ){
                bootstrap_set_label_column(2);
            }
            
            stock_items_bootstrap(_("Select an item"), 'stock_id', input_post('stock_id'), _('New item'), true, check_value('show_inactive'));
            col_start(12,'col-md-3');
            $new_item = get_post('stock_id') == '';
            if( !isMobile() ){
                bootstrap_set_label_column(6);
            }
            check_bootstrap(_("Show inactive"), 'show_inactive', null, true);
            row_end();

            if (get_post('_show_inactive_update')) {
                global $Ajax;
                $Ajax->activate('stock_id');
                set_focus('stock_id');
            }
        } else {
            hidden('stock_id', get_post('stock_id'));
        }

        div_start('details');
        if (! $this->id)
            unset($_POST['_tabs_sel']); // force settings tab for new customer

        col_start(12,null,false);

        tabs_bootstrap('tabs', array(
            'settings' => array(
                _('&General settings'),
                $this->id
            ),
            'sales_pricing' => array(
                _('S&ales Pricing'),
                $this->id !=-1
            ),
            'purchase_pricing' => array(
                _('&Purchasing Pricing'),
                $this->id!=-1
            ),
            'standard_cost' => array(
                _('Standard &Costs'),
                $this->id!=-1
            ),
            'reorder_level' => array(
                _('&Reorder Levels'),
                (is_inventory_item($this->id) ? $this->id : null)
            ),
            'movement' => array(
                _('&Transactions'),
                $this->id!=-1
            ),
            'status' => array(
                _('&Status'),
                $this->id!=-1
            )
        ));

        global $Mode, $stock_id, $selected_id ;

        switch (get_post('_tabs_sel')) {
            default:
            case 'settings':
                $this->item();
                break;
            case 'sales_pricing':
                $_GET['stock_id'] = $this->id;
                $_POST['stock_id'] = $this->id;
                $_GET['popup'] = 1;
//                 $pricing_form = module_control_load('manage/price','products');
//                 $pricing_form->mode = "";
//                 $pricing_form->id = $this->id;
//                 $pricing_form->popup();

                include_once (ROOT . "/inventory/prices.php");
                break;
            case 'purchase_pricing':
                $_GET['stock_id'] = $this->id;
                $_GET['popup'] = 1;
                include_once (ROOT . "/inventory/purchasing_data.php");
                break;
            case 'standard_cost':
                $_GET['stock_id'] = $this->id;
                $_GET['popup'] = 1;
                include_once (ROOT . "/inventory/cost_update.php");
                break;
            case 'reorder_level':
                if (! is_inventory_item($this->id)) {
                    break;
                }
                $_GET['stock_id'] = $this->id;
                $_GET['popup'] = 1;
                include_once (ROOT . "/inventory/reorder_level.php");
                break;
            case 'movement':
                $_GET['stock_id'] = $this->id;
                $_POST['stock_id'] = 
                $_GET['popup'] = 1;
                include_once (ROOT . "/inventory/inquiry/stock_movements.php");
                break;
            case 'status':
                $_GET['stock_id'] = $this->id;
                $_GET['popup'] = 1;
                include_once (ROOT . "/inventory/inquiry/stock_status.php");
                break;
        }

        tabbed_content_end();
        col_end();
        div_end();


        box_footer_start();
        div_start('controls');
        switch (get_post('_tabs_sel')) {

            case 'settings':
                if (! isset($_POST['NewStockID']) || $this->new_item) {
                    submit('addupdate', _("Insert New Item"), true, '', 'default');
                } else {
                    submit_center_first('addupdate', _("Update Item"), '', @$_REQUEST['popup'] ? true : 'default');
                    submit_return('select', get_post('stock_id'), _("Select this items and return to document entry."), 'default');
                    submit('clone', _("Clone This Item"), true, '', true);
                    submit('delete', _("Delete This Item"), true, '', true);
                    submit_center_last('cancel', _("Cancel"), _("Cancel Edition"), 'cancel');
                }
                break;
            case 'sales_pricing':
                submit_add_or_update( $_GET['stock_id'] < 1 , '', 'both');
                
                break;
            case 'purchase_pricing':
                submit_add_or_update_center(input_post('selected_id')== - 1, '', 'both');
                break;
            case 'standard_cost':
                //submit_add_or_update_center(input_post('selected_id') == - 1, '', 'both');
                submit_center('UpdateData', _("Update"), true, false, 'default');
                break;
            case 'reorder_level';
                submit_center('UpdateData', _("Update"), true, false, 'default');
                break;
            default:
                break;
        }

        div_end();
        box_footer_end();

        box_end();

        hidden('popup', @$_REQUEST['popup']);
        end_form();
    }

    function check_data_requirement(){
        $pass = true;

        if( !check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."stock_category") ){
            display_error(_("There are no item categories defined in the system. At least one item category is required to ".anchor('inventory/manage/item_categories.php','add a item')."."), true);
            $pass = false;
        }

        if( !check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."item_tax_types") ){
            display_error(_("There are no item tax types defined in the system. At least one item tax type is required to add a item."), true);
        }

        if( !$pass ){
            end_page();
            exit;
        }



    }
    private function item()
    {
        global $SysPrefs, $path_to_root, $pic_height, $ci;

        $stock_id = $this->id;

        col_start(6);
        bootstrap_set_label_column(4);
        fieldset_start("Item");

        // ------------------------------------------------------------------------------------
        if ($this->new_item) {
            input_text_bootstrap("Item Code", 'NewStockID');
//             text_row(_("Item Code:"), 'NewStockID', null, 21, 20);

            $_POST['inactive'] = 0;
        } else { // Must be modifying an existing item
            //if (get_post('NewStockID') != get_post('stock_id') || get_post('addupdate')) {
//             bug($_POST);
//             if (get_post('NewStockID') != get_post('stock_id')) {
                // first item display

                $_POST['NewStockID'] = $_POST['stock_id'];

                $myrow = get_item($_POST['NewStockID']);

                $_POST['long_description'] = $myrow["long_description"];
                $_POST['description'] = $myrow["description"];
                $_POST['category_id'] = $myrow["category_id"];
                $_POST['units'] = $myrow["units"];
                $_POST['mb_flag'] = $myrow["mb_flag"];

                $_POST['sales_account'] = $myrow['sales_account'];
                $_POST['inventory_account'] = $myrow['inventory_account'];
                $_POST['cogs_account'] = $myrow['cogs_account'];
                $_POST['adjustment_account'] = $myrow['adjustment_account'];
                $_POST['assembly_account'] = $myrow['assembly_account'];
                $_POST['dimension_id'] = $myrow['dimension_id'];
                $_POST['dimension2_id'] = $myrow['dimension2_id'];
                $_POST['no_sale'] = $myrow['no_sale'];
                $_POST['del_image'] = 0;
                $_POST['inactive'] = $myrow["inactive"];
                $_POST['editable'] = $myrow["editable"];

                $_POST['sales_gst_type_id'] = $myrow["sales_gst_type"];
                $_POST['purchase_gst_type_id'] = $myrow["purchase_gst_type"];
//             }
            
            input_label_bootstrap(_("Item Code Currency"), 'NewStockID');
            hidden('NewStockID', $_POST['NewStockID']);
            set_focus('description');
        }

        input_text_bootstrap("Name", 'description');
        input_textarea_bootstrap( "Description:", 'long_description');
        stock_categories('Category','category_id', null, false, $this->new_item);
//         text_row(_("Name:"), 'description', null, 52, 200);

//         textarea_row(_('Description:'), 'long_description', null, 42, 3);

//         stock_categories_list_row(_("Category:"), 'category_id', null, false, $new_item);

        if ($this->new_item && (list_updated('category_id') || ! isset($_POST['units']))) {

            $category_record = get_item_category($_POST['category_id']);

            $_POST['units'] = $category_record["dflt_units"];
            $_POST['mb_flag'] = $category_record["dflt_mb_flag"];
            $_POST['inventory_account'] = $category_record["dflt_inventory_act"];
            $_POST['cogs_account'] = $category_record["dflt_cogs_act"];
            $_POST['sales_account'] = $category_record["dflt_sales_act"];
            $_POST['adjustment_account'] = $category_record["dflt_adjustment_act"];
            $_POST['assembly_account'] = $category_record["dflt_assembly_act"];
            $_POST['dimension_id'] = $category_record["dflt_dim1"];
            $_POST['dimension2_id'] = $category_record["dflt_dim2"];
            $_POST['no_sale'] = $category_record["dflt_no_sale"];
            $_POST['editable'] = 0;
        }

        $fresh_item = ! isset($_POST['NewStockID']) || $this->new_item || check_usage($_POST['stock_id'], false);

        stock_item_types('Item Type','mb_flag',null, $fresh_item);
        stock_units('Units','units',null, $fresh_item);
        check_bootstrap( "Editable description", 'editable');
        check_bootstrap( "Exclude from sales", 'no_sale');
        
        $sales_gst_type = input_val('sales_gst_type_id');
        if( empty($sales_gst_type) OR is_null($sales_gst_type) ){
            $sales_gst_type = get_company_prefs('sale_gst_default');
        }
        gst_list_bootstrap('Sales GST Type','sales_gst_type_id',$sales_gst_type,false,2);
        
        $purchase_gst_type = input_val('purchase_gst_type_id');
        if( empty($purchase_gst_type) OR is_null($purchase_gst_type) ){
            $purchase_gst_type = get_company_prefs('purchase_gst_default');
        }
        gst_list_bootstrap('Purchase GST Type','purchase_gst_type_id',$purchase_gst_type,false,3);

//         stock_item_types_list_row(_("Item Type:"), 'mb_flag', null, $fresh_item);
//         stock_units_list_row(_('Units of Measure:'), 'units', null, $fresh_item);
//         check_row(_("Editable description:"), 'editable');
//         check_row(_("Exclude from sales:"), 'no_sale');

        // stock_sales_gst_type_list_row(_("Sales GST Type:"), 'sales_gst_type_id', null, false, $new_item);
//         echo $ci->finput->inputtaxes('Sales GST Type:', 'sales_gst_type_id', input_val('sales_gst_type_id'), '2', 'row', false, null, 'fullname');
        // stock_purchase_gst_type_list_row(_("Purchase GST Type:"), 'purchase_gst_type_id', null, false, $new_item);
//         echo $ci->finput->inputtaxes('Purchase GST Type:', 'purchase_gst_type_id', input_val('purchase_gst_type_id'), '3', 'row', false, null, 'fullname');

        col_start(6);
//         table_section(2);

        $dim = get_company_pref('use_dimension');
        if ($dim >= 1) {
            fieldset_start("Departments");
            dimensions_bootstrap('Department 1','dimension_id',null, true, " ", false, 1);
//             table_section_title(_("Departments"));

//             dimensions_list_row(_("Department") . " 1", 'dimension_id', null, true, " ", false, 1);
            if ($dim > 1){
                dimensions_bootstrap('Department 2','dimension2_id',null, true, " ", false, 2);
//                 dimensions_list_row(_("Department") . " 2", 'dimension2_id', null, true, " ", false, 2);
            }

        }
        if ($dim < 1)
            hidden('dimension_id', 0);
        if ($dim < 2)
            hidden('dimension2_id', 0);

        fieldset_start("GL Accounts");
        gl_accounts_bootstrap( "Sales Account", 'sales_account');


//         table_section_title(_("GL Accounts"));

//         gl_all_accounts_list_row(_("Sales Account:"), 'sales_account', $_POST['sales_account']);

        if (! is_service($_POST['mb_flag'])) {
            gl_accounts_bootstrap( "Inventory Account", 'inventory_account');
            gl_accounts_bootstrap( "C.O.G.S. Account", 'cogs_account');
            gl_accounts_bootstrap( "Inventory Adjustments Account", 'adjustment_account');
//             gl_all_accounts_list_row(_("Inventory Account:"), 'inventory_account', $_POST['inventory_account']);
//             gl_all_accounts_list_row(_("C.O.G.S. Account:"), 'cogs_account', $_POST['cogs_account']);
//             gl_all_accounts_list_row(_("Inventory Adjustments Account:"), 'adjustment_account', $_POST['adjustment_account']);
        } else {
            gl_accounts_bootstrap( "C.O.G.S. Account", 'cogs_account');

//             gl_all_accounts_list_row(_("C.O.G.S. Account:"), 'cogs_account', $_POST['cogs_account']);
            hidden('inventory_account', $_POST['inventory_account']);
            hidden('adjustment_account', $_POST['adjustment_account']);
        }

        if (is_manufactured($_POST['mb_flag'])){
            gl_accounts_bootstrap( "Item Assembly Costs", 'assembly_account');
//             gl_all_accounts_list_row(_("Item Assembly Costs Account:"), 'assembly_account', $_POST['assembly_account']);
        }

        else
            hidden('assembly_account', $_POST['assembly_account']);

        fieldset_start("Other");
//         table_section_title(_("Other"));

        // Add image upload for New Item - by Joe
//         file_row(_("Image File (.jpg)") . ":", 'pic', 'pic');
        file_bootstrap(_("Image File"), 'pic','(jpg|png)','pic');
        // Add Image upload for New Item - by Joe
        $stock_img_link = "";
        $check_remove_image = false;
        if (isset($_POST['NewStockID']) && file_exists(company_path() . '/images/' . item_img_name($_POST['NewStockID']) . ".jpg")) {
            // 31/08/08 - rand() call is necessary here to avoid caching problems. Thanks to Peter D.
            $stock_img_link .= "<img id='item_img' alt = '[" . $_POST['NewStockID'] . ".jpg" . "]' src='" . company_path() . '/images/' . item_img_name($_POST['NewStockID']) . ".jpg?nocache=" . rand() . "'" . " height='$pic_height' border='0'>";
            $check_remove_image = true;
        } else {
            $stock_img_link .= _("No image");
        }

        //label_row("&nbsp;", $stock_img_link);
        input_label("Image", $stock_img_link);
        if ($check_remove_image)
            check_row(_("Delete Image:"), 'del_image');

        yesno_bootstrap('Item status', 'inactive', null, _('Inactive'), _('Active'));
//         record_status_list_row(_("Item status:"), 'inactive');
        row_end();

    }

    function update()
    {
        global $Ajax;

        $upload_file = $this->upload_file();
        $input_error = 0;
        if ($upload_file == 'No')
            $input_error = 1;
        if (strlen($_POST['description']) == 0) {
            $input_error = 1;
            display_error(_('The item name must be entered.'));
            set_focus('description');
        } elseif (strlen($_POST['NewStockID']) == 0) {
            $input_error = 1;
            display_error(_('The item code cannot be empty'));
            set_focus('NewStockID');
        } elseif (strstr($_POST['NewStockID'], " ") || strstr($_POST['NewStockID'], "'") || strstr($_POST['NewStockID'], "+") || strstr($_POST['NewStockID'], "\"") || strstr($_POST['NewStockID'], "&") || strstr($_POST['NewStockID'], "\t")) {
            $input_error = 1;
            display_error(_('The item code cannot contain any of the following characters -  & + OR a space OR quotes'));
            set_focus('NewStockID');
        } elseif ($this->new_item && db_num_rows(get_item_kit($_POST['NewStockID']))) {
            $input_error = 1;
            display_error(_("This item code is already assigned to stock item or sale kit."));
            set_focus('NewStockID');
        }

        if ($input_error != 1) {
            if (check_value('del_image')) {
                $filename = company_path() . '/images/' . item_img_name($_POST['NewStockID']) . ".jpg";
                if (file_exists($filename))
                    unlink($filename);
            }

            if (! $this->new_item) { /* so its an existing one */
                update_item($_POST['NewStockID'], $_POST['description'], $_POST['long_description'], $_POST['category_id'], get_post('units'), get_post('mb_flag'), $_POST['sales_account'], $_POST['inventory_account'], $_POST['cogs_account'], $_POST['adjustment_account'], $_POST['assembly_account'], $_POST['dimension_id'], $_POST['dimension2_id'], check_value('no_sale'), check_value('editable'), $_POST['sales_gst_type_id'], $_POST['purchase_gst_type_id']);
                update_record_status($_POST['NewStockID'], $_POST['inactive'], 'stock_master', 'stock_id');
                update_record_status($_POST['NewStockID'], $_POST['inactive'], 'item_codes', 'item_code');
                set_focus('stock_id');
                $Ajax->activate('stock_id'); // in case of status change
                display_notification(_("Item has been updated."));
            } else { // it is a NEW part

                add_item($_POST['NewStockID'], $_POST['description'], $_POST['long_description'], $_POST['category_id'], $_POST['units'], $_POST['mb_flag'], $_POST['sales_account'], $_POST['inventory_account'], $_POST['cogs_account'], $_POST['adjustment_account'], $_POST['assembly_account'], $_POST['dimension_id'], $_POST['dimension2_id'], check_value('no_sale'), check_value('editable'), $_POST['sales_gst_type_id'], $_POST['purchase_gst_type_id']);

                display_notification(_("A new item has been added."));
                $_POST['stock_id'] = $_POST['NewStockID'] = $_POST['description'] = $_POST['long_description'] = '';
                $_POST['no_sale'] = $_POST['editable'] = 0;
                set_focus('NewStockID');
            }
            $Ajax->activate('_page_body');
        }
    }

    function upload_file()
    {
        global $Ajax;
        $upload_file = "";
        if (isset($_FILES['pic']) && $_FILES['pic']['name'] != '') {
            $stock_id = $_POST['NewStockID'];
            $result = $_FILES['pic']['error'];
            $upload_file = 'Yes'; // Assume all is well to start off with
            $filename = company_path() . '/images';
            if (! file_exists($filename)) {
                mkdir($filename);
            }
            $filename .= "/" . item_img_name($stock_id) . ".jpg";

            // But check for the worst
            if ((list ($width, $height, $type, $attr) = getimagesize($_FILES['pic']['tmp_name'])) !== false)
                $imagetype = $type;
            else
                $imagetype = false;
                // $imagetype = exif_imagetype($_FILES['pic']['tmp_name']);
            if ($imagetype != IMAGETYPE_GIF && $imagetype != IMAGETYPE_JPEG && $imagetype != IMAGETYPE_PNG) { // File type Check
                display_warning(_('Only graphics files can be uploaded'));
                $upload_file = 'No';
            } elseif (! in_array(strtoupper(substr(trim($_FILES['pic']['name']), strlen($_FILES['pic']['name']) - 3)), array(
                'JPG',
                'PNG',
                'GIF'
            ))) {
                display_warning(_('Only graphics files are supported - a file extension of .jpg, .png or .gif is expected'));
                $upload_file = 'No';
            } elseif ($_FILES['pic']['size'] > ($max_image_size * 1024)) { // File Size Check
                display_warning(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $max_image_size);
                $upload_file = 'No';
            } elseif (file_exists($filename)) {
                $result = unlink($filename);
                if (! $result) {
                    display_error(_('The existing image could not be removed'));
                    $upload_file = 'No';
                }
            }

            if ($upload_file == 'Yes') {
                $result = move_uploaded_file($_FILES['pic']['tmp_name'], $filename);
            }
            $Ajax->activate('details');
            /* EOF Add Image upload for New Item - by Ori */
        }

        return $upload_file;
    }
}