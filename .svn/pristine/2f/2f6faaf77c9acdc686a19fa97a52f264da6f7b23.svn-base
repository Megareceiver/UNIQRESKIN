<?php

class ImportExcel
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        start_form(TRUE);

        // -----------------------------------------------------------------------------------
        box_start('Upload Excel file to import data into system');
        file_bootstrap('Data file', 'excelfile', anchor('company/Data_Migration_Template.xls', 'File template & Guide'));

        box_footer_start(NULL, NULL, false);
        submit('ADD_ITEM', _("Upload New File"), 'both', _('Submit changes'));
        box_footer_end();
        box_end();

        box_start();

        $list_view = array();

        if (get_instance()->db->table_exists('import_products') && get_instance()->db->count_all('import_products') > 0) {
            $list_view['product'] = array(
                _('Products'),
                1
            );
        }

        if (get_instance()->db->table_exists('import_supplier') && get_instance()->db->count_all('import_supplier') > 0) {
            $list_view['supplier'] = array(
                _('Suppliers'),
                1
            );
        }

        if (get_instance()->db->table_exists('import_customer') && get_instance()->db->count_all('import_customer') > 0) {
            $list_view['customer'] = array(
                _('Customer'),
                1
            );
        }

        if (! empty($list_view)) {
            tabs_bootstrap('tabs', $list_view);
        }

        switch (get_post('_tabs_sel')) {
            case 'product':
                $this->products();
                break;
            case 'supplier':
                show_data_supplier();
                break;
            case 'customer':
                $this->customers();
                break;
            default:
                break;
        }
        if (! empty($list_view)) {
            tabbed_content_end();
        }

        box_footer_start(NULL, NULL, true);
        box_footer_end();
        box_end();

        end_form();
    }

    private function products()
    {
        global $ci, $import;

        $page = 0;
        if (isset($_GET['page'])) {
            $page = $_GET['page'] - 1;
        }

        $limit = 50;
        if (isset($_POST['add_product']) && $_POST['add_product']) {
            $products = $_POST['product'];
            if ($products && count($products) > 0) {
                foreach ($products as $pro) {
                    $import->add_product($pro);
                }
            }
        }

        $field = array(
            'stock_id' => 'Product Code',

            // 'stock_id'=>'Product Code',
            'description' => 'Product Name',
            'category_id' => 'Category'
        );

        $html = '<table class="tablestyle table table-striped table-bordered table-hover" >';
        if ($field) {
            $button = '<tr class="notitle" ><td colspan=2 >' . submit('add_product', _("Import Product"), false, _('Submit changes')) . $ci->db->count_all('import_products') . '</td><td colspan=3></td></tr>';

            $html .= '<thead>' . $button . '<tr>';
            $html .= '<td class="center" ><input type="checkbox" name="checkall" ></td>';
            foreach ($field as $name => $title) {
                $html .= "<td >$title</td>";
            }
            $html .= "<td></td>";
            $html .= '</tr></thead>';
        }

        $items = $ci->db->select('id,stock_id, description, category_id')
            ->get('import_products', $limit, $limit * $page)
            ->result();

        if ($items && count($items) > 0) {
            $html .= '<tbody>';
            foreach ($items as $ite) {

                $duplicate = $ci->db->where('stock_id', $ite->stock_id)
                    ->get('stock_master')
                    ->row();

                $html .= '<tr class="' . (($duplicate && isset($duplicate->stock_id)) ? 'red' : null) . '">';
                $html .= '<td class="center" ><input type="checkbox" name="product[]" value="' . $ite->id . '" ></td>';
                foreach ($field as $name => $title) {
                    $value = $ite->$name;
                    $html .= "<td >$value</td>";
                }
                $html .= '<td >' .
                // .anchor('#',set_icon(ICON_EDIT,'Remove'),' class="row_edit" ')
                anchor('admin/import.php?remove_product=' . $ite->id, set_icon(ICON_DELETE, 'Remove'), ' class="row_remove" ') . '</td>';

                $html .= '</tr>';
            }

            $_GET['_tabs_sel'] = 'product';

            $html .= '<tfoot><tr>' . '<td colspan=5 ></td></tr>' . paging_control_row(5, $limit, $page, $ci->db->count_all('import_products'), false) . '</tfoot>';

            $html .= '</tbody>';
        } else {
            header("Location: /admin/import.php");
        }
        echo $html;
    }


    function customers(){
        global $ci,$import;
        $limit = 50;

        $page = 0;
        if( isset($_GET['page'] ) ){
            $page = $_GET['page']-1;
        }


        if( isset($_POST['add_customer']) && $_POST['add_customer'] ){
            $supplier = $_POST['customer'];
            if( $supplier && count($supplier) > 0 ){
                foreach ($supplier AS $pro){
                    $import->add_customer($pro);
                }
            }


        }
        $field = array(
            'debtor_ref'=>'Short Name',
            'name'=>'Customer Name',
            //'stock_id'=>'Product Code',
            'tax_id'=>'GST No.',

        );

        $html = '<table class="tablestyle table table-striped table-bordered table-hover table-responsive" >';
        if( $field ){
            $button = '<tr class="notitle" ><td colspan=2 >'.submit('add_customer', _("Import Customer"), false, _('Submit changes')).$ci->db->count_all('import_customer').'</td><td colspan=3></td></tr>';

            $html.= '<thead>'.$button.'<tr>';

            //$html.= '<thead><tr><td>'.submit('add_customer', _("Import Customer"), false, _('Submit changes')).$ci->db->count_all('import_customer').'</td><td colspan=5></td></tr><tr>';


            $html.='<td class="center" ><input type="checkbox" name="checkall" ></td>';
            foreach ($field AS $name=>$title){
                $html.="<td >$title</td>";
            }
            $html.="<td></td>";
            $html.= '</tr></thead>';
        }

        $items = $ci->db->select('id,debtor_ref, name, curr_code,tax_id')->get('import_customer',$limit,$limit*$page )->result();


        if( $items && count($items) > 0){
            $html.= '<tbody>';
            foreach ($items AS $ite){
                $duplicate = $ci->db->where('debtor_ref',$ite->debtor_ref)->get('debtors_master')->row();
                // bug($duplicate);
                $html.= '<tr class="'.( ($duplicate && isset($duplicate->debtor_no)) ? 'red' : null) .'">';
                $html.='<td class="center" ><input type="checkbox" name="customer[]" value="'.$ite->id.'" ></td>';

                foreach ($field AS $name=>$title){
                    $value = $ite->$name;
                    $html.="<td >$value</td>";
                }
                $html.='<td class="text-center">'
                    //.anchor('#',set_icon(ICON_EDIT,'Remove'),' class="row_edit" ')
                .anchor('admin/import.php?remove_customer='.$ite->id,set_icon(ICON_DELETE,'Remove'),' class="row_remove button" ')
                .'</td>';

                $html.= '</tr>';
            }



            $_GET['_tabs_sel'] = 'customer';
            $html.= '<tfoot><tr>'
                .'<td colspan=5 ></td></tr>'
                    .paging_control_row(5,$limit,$page, $ci->db->count_all('import_customer'),false)
                    .'</tfoot>';

            $html.= '</tbody>';
        }  else {
            header("Location: /admin/import.php");
        }

        $html.= '</table>';
        echo $html;
    }
}