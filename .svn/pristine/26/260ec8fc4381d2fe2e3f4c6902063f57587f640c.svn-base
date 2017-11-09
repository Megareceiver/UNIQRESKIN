<?php

function customer_list($name, $selected_id = null, $spec_option = false, $submit_on_change = false, $show_inactive = false, $editkey = false, $class_of_input = NULL)
{
    global $all_items;

    $sql = "SELECT debtor_no, debtor_ref, curr_code, inactive FROM " . TB_PREF . "debtors_master ";

    $mode = get_company_pref('no_customer_list');

    if ($editkey)
        set_editor('customer', $name, $editkey);

    $hit = _('Select customer');
    $hit = '';

    if( strlen($class_of_input) < 1 ){
        $class_of_input = get_instance()->bootstrap->input_class;
    }

    $ret = combo_input($name, $selected_id, $sql, 'debtor_no', 'debtor_ref',

    array(
        'format' => '_format_add_curr',
        'order' => array(
            'debtor_ref'
        ),
        'search_box' => $mode != 0,
        'type' => 1,
        'size' => 20,
        'search' => array(
            "debtor_ref",
            "name",
            "tax_id"
        ),
        'spec_option' => $spec_option === true ? _("All Customers") : $spec_option,
        'spec_id' => $all_items,
        'select_submit' => $submit_on_change,
        'async' => false,
//         'sel_hint' => $mode ? _('Press Space tab to filter by name fragment; F2 - entry new customer') : $hit,
        'show_inactive' => $show_inactive,
//         'class' => $class_of_input
    ));

    if ($editkey)
        $ret .= add_edit_combo('customer');
    return $ret;
}

// -----------------------------------------------------------------------------------------------
// Payment type selector for current user.
//
function sale_payment_list($name, $category, $selected_id = null, $submit_on_change = true, $class_of_input = NULL)
{
    $sql = "SELECT terms_indicator, terms, inactive FROM " . TB_PREF . "payment_terms";

    if ($category == PM_CASH) // only cash
        $sql .= " WHERE days_before_due=0 AND day_in_following_month=0";
    if ($category == PM_CREDIT) // only delayed payments
        $sql .= " WHERE days_before_due!=0 OR day_in_following_month!=0";

    if( strlen($class_of_input) < 1 ){
        $class_of_input = get_instance()->bootstrap->input_class;
    }

    return combo_input($name, $selected_id, $sql, 'terms_indicator', 'terms', array(
        'select_submit' => $submit_on_change,
        'async' => true,
        'class' => $class_of_input
    ));
}

function sale_payment_list_cells($label, $name, $category, $selected_id = null, $submit_on_change = true)
{
    if ($label != null)
        echo "<td class='label'>$label</td>\n";
    echo "<td>";

    echo sale_payment_list($name, $category, $selected_id, $submit_on_change);

    echo "</td>\n";
}

/*
 * Sale Types
 * ----------------------------------------------------------------------------------------------------
 */
function sales_types_list($name, $selected_id = null, $submit_on_change = false, $special_option = false, $class_of_input = NULL)
{
    $sql = "SELECT id, sales_type, inactive FROM " . TB_PREF . "sales_types";

    return combo_input($name, $selected_id, $sql, 'id', 'sales_type', array(
        'spec_option' => $special_option === true ? _("All Sales Types") : $special_option,
        'spec_id' => 0,
        'select_submit' => $submit_on_change,
        'class' => $class_of_input
    ));
    // 'async' => false,

}

function sales_types_list_cells($label, $name, $selected_id = null, $submit_on_change = false, $special_option = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo sales_types_list($name, $selected_id, $submit_on_change, $special_option);
    echo "</td>\n";
}

function sales_types_list_row($label, $name, $selected_id = null, $submit_on_change = false, $special_option = false)
{
    echo "<tr><td class='label'>$label</td>";
    sales_types_list_cells(null, $name, $selected_id, $submit_on_change, $special_option);
    echo "</tr>\n";
}

/*
 *
 */
function sales_groups_list($name, $selected_id = null, $special_option = false)
{
    $sql = "SELECT id, description, inactive FROM " . TB_PREF . "groups";
    return combo_input($name, $selected_id, $sql, 'id', 'description', array(
        'spec_option' => $special_option === true ? ' ' : $special_option,
        'order' => 'description',
        'spec_id' => 0,
        'class' => get_instance()->bootstrap->input_class
    ));
}

/*
 *
 */

function customer_branches_list($customer_id, $name, $selected_id = null, $spec_option = true, $enabled = true, $submit_on_change = false, $editkey = false,$class_of_input=NULL)
{
    global $all_items;

    $sql = "SELECT branch_code, branch_ref FROM " . TB_PREF . "cust_branch
		WHERE debtor_no=" . db_escape($customer_id) . " ";

    if ($editkey)
        set_editor('branch', $name, $editkey);

    $where = $enabled ? array(
        "disable_trans = 0"
    ) : array();

    if( strlen($class_of_input) < 1 ){
        $class_of_input = get_instance()->bootstrap->input_class;
    }

    $ret = combo_input($name, $selected_id, $sql, 'branch_code', 'branch_ref', array(
        'where' => $where,
        'order' => array(
            'branch_ref'
        ),
        'spec_option' => $spec_option === true ? _('All branches') : $spec_option,
        'spec_id' => $all_items,
        'select_submit' => $submit_on_change,
        'sel_hint' => _('Select customer branch'),
        'class'=>$class_of_input
    ));
    if ($editkey) {
        $ret .= add_edit_combo('branch');
    }
    return $ret;
}