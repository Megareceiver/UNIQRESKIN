<?php
/*
 * Stock Items
 */
function stock_items_list($name, $selected_id = null, $all_option = false, $submit_on_change = false, $opts = array(), $editkey = false, $class_of_input = NULL)
{
    global $all_items;

    $sql = "SELECT stock_id, s.description, c.description, s.inactive, s.editable
			FROM " . TB_PREF . "stock_master s," . TB_PREF . "stock_category c WHERE s.category_id=c.category_id";

    if ($editkey)
        set_editor('item', $name, $editkey);

    $ret = combo_input($name, $selected_id, $sql, 'stock_id', 's.description', array_merge(array(
        'format' => '_format_stock_items',
        'spec_option' => $all_option === true ? _("All Items") : $all_option,
        'spec_id' => $all_items,
        'search_box' => true,
        'search' => array(
            "stock_id",
            "c.description",
            "s.description"
        ),
        'search_submit' => get_company_pref('no_item_list') != 0,
        'size' => 10,
        'select_submit' => $submit_on_change,
        'category' => 2,
        'order' => array(
            'c.description',
            'stock_id'
        ),
        'class' => $class_of_input
    ), $opts));


    if ($editkey)
        $ret .= add_edit_combo('item');
    return $ret;
}

function _format_stock_items($row)
{
    return (user_show_codes() ? ($row[0] . "&nbsp;-&nbsp;") : "") . $row[1];
}

/*
 *
 */
function stock_categories_list($name, $selected_id = null, $spec_opt = false, $submit_on_change = false, $class_of_input = NULL)
{
    $sql = "SELECT category_id, description, inactive FROM " . TB_PREF . "stock_category";
    return combo_input($name, $selected_id, $sql, 'category_id', 'description', array(
        'order' => 'category_id',
        'spec_option' => $spec_opt,
        'spec_id' => - 1,
        'select_submit' => $submit_on_change,
        'async' => true,
        'class' => get_instance()->bootstrap->input_class
    ));
}

/*
 * Select item via foreign code.
 */
function sales_items_list($name, $selected_id = null, $all_option = false, $submit_on_change = false, $type = '', $opts = array())
{
    global $all_items;
    // all sales codes
    $sql = "SELECT i.item_code, i.description, c.description, count(*)>1 as kit,
			 i.inactive, if(count(*)>1, '0', s.editable) as editable
			FROM
			" . TB_PREF . "stock_master s,
			" . TB_PREF . "item_codes i
			LEFT JOIN
			" . TB_PREF . "stock_category c
			ON i.category_id=c.category_id
			WHERE i.stock_id=s.stock_id";

    if ($type == 'local') { // exclude foreign codes
        $sql .= " AND !i.is_foreign";
    } elseif ($type == 'kits') { // sales kits
        $sql .= " AND !i.is_foreign AND i.item_code!=i.stock_id";
    }
    $sql .= " AND !i.inactive AND !s.inactive AND !s.no_sale";
    $sql .= " GROUP BY i.item_code";


    return combo_input($name, $selected_id, $sql, 'i.item_code', 'c.description', array_merge(array(
        'format' => '_format_stock_items',
        'spec_option' => $all_option === true ? _("All Items") : $all_option,
        'spec_id' => $all_items,
        'search_box' => true,
        'search' => array(
            "i.item_code",
            "c.description",
            "i.description"
        ),
        'search_submit' => get_company_pref('no_item_list') != 0,
        'size' => 15,
        'select_submit' => $submit_on_change,
        'category' => 2,
        'order' => array(
            'c.description',
            'i.item_code'
        ),
        'editable' => 30,
        'max' => 255,
//         'class' => ,
        'class'=>'show-tick '.get_instance()->bootstrap->input_class,
        'data-size'=>6,
        'data-live-search'=>true,
        'attr' => ' data-live-search="true" data-live-search-style="begins" '
    ), $opts));
}

function movement_types_list($name, $selected_id = null)
{
    $sql = "SELECT id, name FROM " . TB_PREF . "movement_types";
    return combo_input($name, $selected_id, $sql, 'id', 'name', array());
}
