<?php

/*
 *
 */
function stock_items_list_cells($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $all = false, $editkey = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo stock_items_list($name, $selected_id, $all_option, $submit_on_change, array(
        'cells' => true,
        'show_inactive' => $all,
        'class'=>get_instance()->bootstrap->input_class
    ), $editkey);
}

/*
 *
 */
function stock_item_types_list_row($label, $name, $selected_id = null, $enabled = true)
{
    global $stock_types;

    echo "<tr>";
    if ($label != null)
        echo "<td class='label'>$label</td>\n";
    echo "<td>";
    $selected_id = input_post($name);
    if (! $selected_id) {
        $selected_id = "D";
    }
    echo array_selector($name, $selected_id, $stock_types, array(
        'select_submit' => true,
        'disabled' => ! $enabled
    ));
    echo "</td></tr>\n";
}

/*
 *
 */
// ------------------------------------------------------------------------------------
function stock_units_list_row($label, $name, $value = null, $enabled = true)
{
    $result = get_all_item_units();
    echo "<tr>";
    if ($label != null)
        echo "<td class='label'>$label</td>\n";
    echo "<td>";

    while ($unit = db_fetch($result))
        $units[$unit['abbr']] = $unit['name'];

    echo array_selector($name, $value, $units, array(
        'disabled' => ! $enabled
    ));

    echo "</td></tr>\n";
}

/*
 *
 */
function sales_items_list_cells($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $editkey = false)
{
    if ($editkey)
        set_editor('item', $name, $editkey);

    if ($label != null)
        echo "<td>$label</td>\n";

    echo sales_items_list($name, $selected_id, $all_option, $submit_on_change, '', array(
        'cells' => true
    ));
}

function sales_kits_list($name, $selected_id = null, $all_option = false, $submit_on_change = false)
{
    return sales_items_list($name, $selected_id, $all_option, $submit_on_change, 'kits', array(
        'cells' => false,
        'editable' => false
    ));
}

function sales_local_items_list_row($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false)
{
    echo "<tr>";
    if ($label != null)
        echo "<td class='label'>$label</td>\n";
    echo "<td>";
    echo sales_items_list($name, $selected_id, $all_option, $submit_on_change, 'local', array(
        'cells' => false,
        'editable' => false
    ));
    echo "</td></tr>";
}

/*
 *
 */

// -----------------------------------------------------------------------------------------------
function stock_categories_list_cells($label, $name, $selected_id = null, $spec_opt = false, $submit_on_change = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo stock_categories_list($name, $selected_id, $spec_opt, $submit_on_change);
    echo "</td>\n";
}

function stock_categories_list_row($label, $name, $selected_id = null, $spec_opt = false, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    stock_categories_list_cells(null, $name, $selected_id, $spec_opt, $submit_on_change);
    echo "</tr>\n";
}

/*
 *
 */
function movement_types_list_cells($label, $name, $selected_id = null)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo movement_types_list($name, $selected_id);
    echo "</td>\n";
}

function movement_types_list_row($label, $name, $selected_id = null)
{
    echo "<tr><td class='label'>$label</td>";
    movement_types_list_cells(null, $name, $selected_id);
    echo "</tr>\n";
}

/*
 *
 */
function stock_costable_items_list($name, $selected_id = null, $all_option = false, $submit_on_change = false)
{
    return stock_items_list($name, $selected_id, $all_option, $submit_on_change, array(
        'where' => array(
            "mb_flag!='D'"
        )
    ), false, 'form-control');
}

function stock_costable_items_list_cells($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false)
{
    echo "<td>";
    echo input_text(NULL, '_' . $name . '_edit');
    echo "</td>";
    echo '<td>';
    echo stock_items_list($name, $selected_id, $all_option, $submit_on_change, array(
        'where' => array(
            "mb_flag!='D'"
        ),
        'cells' => false
    ), false, 'form-control');
    echo '</td>';
}


