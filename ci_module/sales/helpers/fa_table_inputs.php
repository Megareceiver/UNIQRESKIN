<?php

function customer_list_cells($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $show_inactive = false, $editkey = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td nowrap>";
    echo customer_list($name, $selected_id, $all_option, $submit_on_change, $show_inactive, $editkey);
    echo "</td>\n";
}

function customer_list_row($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $show_inactive = false, $editkey = false)
{
    echo "<tr><td class='label a'>$label</td><td nowrap>";
    echo customer_list($name, $selected_id, $all_option, $submit_on_change, $show_inactive, $editkey);
    echo "</td>\n</tr>\n";
}

function cust_allocations_list_cells($label, $name, $selected = null)
{
    global $all_items, $transaction_type;
    if ($label != null)
        label_cell($label);
    echo "<td>\n";
    echo array_selector($name, $selected, $transaction_type);
    echo "</td>\n";
}

/*
 *
 */
function sales_groups_list_cells($label, $name, $selected_id = null, $special_option = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo sales_groups_list($name, $selected_id, $special_option);
    echo "</td>\n";
}

function sales_groups_list_row($label, $name, $selected_id = null, $special_option = false)
{
    echo "<tr><td class='label'>$label</td>";
    sales_groups_list_cells(null, $name, $selected_id, $special_option);
    echo "</tr>\n";
}

/*
 *
 */
function customer_branches_list_cells($label, $customer_id, $name, $selected_id = null, $all_option = true, $enabled = true, $submit_on_change = false, $editkey = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo customer_branches_list($customer_id, $name, $selected_id, $all_option, $enabled, $submit_on_change, $editkey);
    echo "</td>\n";
}

function customer_branches_list_row($label, $customer_id, $name, $selected_id = null, $all_option = true, $enabled = true, $submit_on_change = false, $editkey = false)
{
    echo "<tr><td class='label'>$label</td>";
    customer_branches_list_cells(null, $customer_id, $name, $selected_id, $all_option, $enabled, $submit_on_change, $editkey);
    echo "</tr>";
}


function policy_list_cells($label, $name, $selected=null)
{
    if ($label != null)
        label_cell($label);
    echo "<td>\n";
    echo array_selector($name, $selected,
        array( '' => _("Automatically put balance on back order"),
            'CAN' => _("Cancel any quantites not delivered")) );
    echo "</td>\n";
}

function policy_list_row($label, $name, $selected=null)
{
    echo "<tr><td class='label'>$label</td>";
    policy_list_cells(null, $name, $selected);
	echo "</tr>\n";
}



function credit_type_list_cells($label, $name, $selected=null, $submit_on_change=false)
{
    if ($label != null)
        label_cell($label);
    echo "<td>\n";
    echo array_selector($name, $selected,
        array( 'Return' => _("Items Returned to Inventory Location"),
            'WriteOff' => _("Items Written Off")),
        array( 'select_submit'=> $submit_on_change ) );
    echo "</td>\n";
}

function credit_type_list_row($label, $name, $selected=null, $submit_on_change=false)
{
    echo "<tr><td class='label'>$label</td>";
    credit_type_list_cells(null, $name, $selected, $submit_on_change);
    echo "</tr>\n";
}
