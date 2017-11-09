<?php

function supplier_list_cells($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $all = false, $editkey = false)
{
    if ($label != null)
        echo "<td>$label</td><td>\n";
    echo supplier_list($name, $selected_id, $all_option, $submit_on_change, $all, $editkey);
    echo "</td>\n";
}

function supplier_list_row($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $all = false, $editkey = false)
{
    echo "<tr><td class='label'>$label</td><td>";
    echo supplier_list($name, $selected_id, $all_option, $submit_on_change, $all, $editkey);
    echo "</td></tr>\n";
}

