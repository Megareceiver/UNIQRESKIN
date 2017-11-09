<?php

function item_tax_types_list_cells($label, $name, $selected_id = null,$group_tax=1)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo tax_types_list($name, $selected_id,null,false,$group_tax);
    echo "</td>\n";
}

function item_tax_types_list_row($label, $name, $selected_id = null)
{
    echo "<tr><td class='label'>$label</td>";
    item_tax_types_list_cells(null, $name, $selected_id);
    echo "</tr>\n";
}

function tax_types_list_cells($label, $name, $selected_id=null, $none_option=false,$submit_on_change=false){
    if ($label != null)
        echo "<td>$label</td>\n";
        echo "<td>";
        echo tax_types_list($name, $selected_id, $none_option, $submit_on_change);
        echo "</td>\n";
}

function tax_types_list_row($label, $name, $selected_id=null, $none_option=false,$submit_on_change=false){
    echo "<tr><td class='label'>$label</td>";
	tax_types_list_cells(null, $name, $selected_id, $none_option, $submit_on_change);
	echo "</tr>\n";
}