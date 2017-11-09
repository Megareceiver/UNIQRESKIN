<?php

function wo_types_list($name, $selected_id=null)
{
    global $wo_types_array;

    return array_selector($name, $selected_id, $wo_types_array,
            array( 'select_submit'=> true, 'async' => true ) );
}

function wo_types_list_row($label, $name, $selected_id=null)
{
    echo "<tr><td class='label'>$label</td><td>\n";
    echo wo_types_list($name, $selected_id);
    echo "</td></tr>\n";
}

//------------------------------------------------------------------------------------

function stock_manufactured_items_list($name, $selected_id=null,
        $all_option=false, $submit_on_change=false)
{
    return stock_items_list($name, $selected_id, $all_option, $submit_on_change,
            array('where'=>array("mb_flag= 'M'")));
}

function stock_manufactured_items_list_cells($label, $name, $selected_id=null,
        $all_option=false, $submit_on_change=false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
        echo "<td>";
        echo stock_manufactured_items_list($name, $selected_id, $all_option, $submit_on_change);
        echo "</td>\n";
}

function stock_manufactured_items_list_row($label, $name, $selected_id=null,
        $all_option=false, $submit_on_change=false)
{
    echo "<tr><td class='label'>$label</td>";
    stock_manufactured_items_list_cells(null, $name, $selected_id, $all_option, $submit_on_change);
    echo "</tr>\n";
}