<?php

function gl_all_accounts_list_cells( $name, $selected_id = null, $skip_bank_accounts = false, $all_option = false, $submit_on_change = false, $all = false)
{
    get_instance()->bootstrap->input_only = true;

//
//     echo '<input type="text" name='$search_box' id='$search_box' size='" . $opts['size'] . "' maxlength='" . $opts['max'] . "' value='$txt' class='$class ".$opts['class']."' rel='$name' autocomplete='off' title='" . $opts['box_hint'] . "'" . (! fallback_mode() && ! $by_id ? " style=display:none;" : '') . ">';
    echo "<td>";
    echo input_text(NULL, '_' . $name . '_edit');
    echo "</td>";
    echo "<td>";
    echo gl_all_accounts_list($name, $selected_id, $skip_bank_accounts, false, $all_option, $submit_on_change, $all);
    echo "</td>\n";

    get_instance()->bootstrap->input_only = false;
}

function gl_all_accounts_list_row($label, $name, $selected_id = null, $skip_bank_accounts = false, $cells = false, $all_option = false)
{
    echo "<tr><td class='label'>$label</td>";
    gl_all_accounts_list_cells(null, $name, $selected_id, $skip_bank_accounts, $cells, $all_option);
    echo "</tr>\n";
}

function gl_account_types_list_cells($label, $name, $selected_id = null, $all_option = false, $all = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo gl_account_types_list($name, $selected_id, $all_option, $all);
    echo "</td>\n";
}

function gl_account_types_list_row($label, $name, $selected_id = null, $all_option = false, $all = false)
{
    echo "<tr><td class='label'>$label</td>";
    gl_account_types_list_cells(null, $name, $selected_id, $all_option, $all);
    echo "</tr>\n";
}

// -----------------------------------------------------------------------------------------------
function class_list_cells($label, $name, $selected_id = null, $submit_on_change = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo class_list($name, $selected_id, $submit_on_change);
    echo "</td>\n";
}

function class_list_row($label, $name, $selected_id = null, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    class_list_cells(null, $name, $selected_id, $submit_on_change);
    echo "</tr>\n";
}

function class_types_list_row($label, $name, $selected_id = null, $submit_on_change = false)
{
    global $class_types;

    echo "<tr><td class='label'>$label</td><td>";
    echo array_selector($name, $selected_id, $class_types, array(
        'select_submit' => $submit_on_change
    ));
    echo "</td></tr>\n";
}

/*
 *
 */
function systypes_list_cells($label, $name, $value = null, $submit_on_change = false, $exclude = array())
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo systypes_list($name, $value, false, $submit_on_change, $exclude);
    echo "</td>\n";
}

function systypes_list_row($label, $name, $value = null, $submit_on_change = false, $exclude = array())
{
    echo "<tr><td class='label'>$label</td>";
    systypes_list_cells(null, $name, $value, $submit_on_change, $exclude);
    echo "</tr>\n";
}

/*
 *
 */
function journal_types_list_cells($label, $name, $value = null, $submit_on_change = false)
{
    global $systypes_array;

    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";

    $items = $systypes_array;

    // exclude quotes, orders and dimensions
    foreach (array(
        ST_PURCHORDER,
        ST_SALESORDER,
        ST_DIMENSION,
        ST_SALESQUOTE,
        ST_LOCTRANSFER
    ) as $excl)
        unset($items[$excl]);

    echo array_selector($name, $value, $items, array(
        'spec_option' => _("All"),
        'spec_id' => - 1,
        'select_submit' => $submit_on_change,
        'async' => false
    ));
    echo "</td>\n";
}


