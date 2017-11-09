<?php

function number_list_cells($label, $name, $selected, $from, $to, $no_option = false)
{
    if ($label != null)
        label_cell($label);
    echo "<td>\n";
    echo number_list($name, $selected, $from, $to, $no_option);
    echo "</td>\n";
}

function number_list_row($label, $name, $selected, $from, $to, $no_option = false)
{
    echo "<tr><td class='label'>$label</td>";
    echo number_list_cells(null, $name, $selected, $from, $to, $no_option);
    echo "</tr>\n";
}

/*
 *
 */
function languages_list_cells($label, $name, $selected_id = null, $all_option = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo languages_list($name, $selected_id, $all_option);
    echo "</td>\n";
}

function languages_list_row($label, $name, $selected_id = null, $all_option = false)
{
    echo "<tr><td class='label'>$label</td>";
    languages_list_cells(null, $name, $selected_id, $all_option);
    echo "</tr>\n";
}