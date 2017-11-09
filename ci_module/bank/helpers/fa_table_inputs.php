<?php

function bank_account_types_list_cells($label, $name, $selected_id = null)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo bank_account_types_list($name, $selected_id);
    echo "</td>\n";
}

function bank_account_types_list_row($label, $name, $selected_id = null)
{
    echo "<tr><td class='label'>$label</td>";
    bank_account_types_list_cells(null, $name, $selected_id);
    echo "</tr>\n";
}

/*
 *
 */
function tag_list_cells($label, $name, $height, $type, $mult = false, $all = false, $spec_opt = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>\n";
    echo tag_list($name, $height, $type, $mult, $all, $spec_opt);
    echo "</td>\n";
}

function tag_list_row($label, $name, $height, $type, $mult = false, $all = false, $spec_opt = false)
{
    echo "<tr><td class='label'>$label</td>";
    tag_list_cells(null, $name, $height, $type, $mult, $all, $spec_opt);
    echo "</tr>\n";
}

/*
 * Payment Person types
 */
function payment_person_types_list_cells($label, $name, $selected_id = null, $related = null)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo payment_person_types_list($name, $selected_id, $related);
    echo "</td>\n";
}

function payment_person_types_list_row($label, $name, $selected_id = null, $related = null)
{
    echo "<tr><td class='label'>$label</td>";
    payment_person_types_list_cells(null, $name, $selected_id, $related);
    echo "</tr>\n";
}

/*
 *
 */
function bank_accounts_list_cells($label, $name, $selected_id = null, $submit_on_change = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo bank_accounts_list($name, $selected_id, $submit_on_change);
    echo "</td>\n";
}

function bank_accounts_list_row($label, $name, $selected_id = null, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    bank_accounts_list_cells(null, $name, $selected_id, $submit_on_change);
    echo "</tr>\n";
}

/*
 *
 */
function quick_entries_list_cells($label, $name, $selected_id = null, $type, $submit_on_change = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo quick_entries_list($name, $selected_id, $type, $submit_on_change);
    echo "</td>";
}

function quick_entries_list_row($label, $name, $selected_id = null, $type, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    quick_entries_list_cells(null, $name, $selected_id, $type, $submit_on_change);
    echo "</tr>\n";
}



