<?php

function supplier_list($name, $selected_id = null, $spec_option = false, $submit_on_change = false, $all = false, $editkey = false, $class_of_input=NULL)
{
    global $all_items;

    $sql = "SELECT supplier_id, supp_ref, curr_code, inactive FROM " . TB_PREF . "suppliers ";
    $mode = get_company_pref('no_supplier_list');

    if ($editkey)
        set_editor('supplier', $name, $editkey);

    $ret = combo_input($name, $selected_id, $sql, 'supplier_id', 'supp_name', array(
        'format' => '_format_add_curr',
        'order' => array(
            'supp_ref'
        ),
        'search_box' => $mode != 0,
        'type' => 1,
        'search' => array(
            "supp_ref",
            "supp_name",
            "gst_no"
        ),
        'spec_option' => $spec_option === true ? _("All Suppliers") : $spec_option,
        'spec_id' => $all_items,
        'select_submit' => $submit_on_change,
        'async' => false,
        'sel_hint' => $mode ? _('Press Space tab to filter by name fragment') : '',
        'show_inactive' => $all,
        'class'=>$class_of_input
    ));

    if ($editkey)
        $ret .= add_edit_combo('supplier');

    return $ret;
}


function supp_allocations_list_cell($name, $selected = null)
{
    global $all_items;
    echo "<td>\n";
    $allocs = array(
        $all_items => _("All Types"),
        '1' => _("Invoices"),
        '2' => _("Overdue Invoices"),
        '3' => _("Payments"),
        '4' => _("Credit Notes"),
        '5' => _("Overdue Credit Notes")
    );
    echo array_selector($name, $selected, $allocs);
    echo "</td>\n";
}

function supp_transactions_list_cell($name, $selected = null)
{
    global $all_items;
    echo "<td>\n";
    $allocs = array(
        $all_items => _("All Types"),
        '6' => _("GRNs"),
        '1' => _("Invoices"),
        '2' => _("Overdue Invoices"),
        '3' => _("Payments"),
        '4' => _("Credit Notes"),
        '5' => _("Overdue Credit Notes")
    );

    echo array_selector($name, $selected, $allocs);
    echo "</td>\n";
}