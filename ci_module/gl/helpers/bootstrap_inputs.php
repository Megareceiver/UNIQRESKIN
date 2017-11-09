<?php

function gl_accounts_bootstrap($label, $name, $selected_id = null, $skip_bank_accounts = false, $cells = false, $all_option = false, $submit_on_change = false, $all = false)
{
    $input = gl_all_accounts_list($name, $selected_id, $skip_bank_accounts, $cells, $all_option, $submit_on_change, $all);
    form_group_bootstrap($label, $input,null,null,array('glname'=>$name));
}

function gl_account_types($label, $name, $selected_id = null, $all_option = false, $all = false)
{
    $input = gl_account_types_list($name, $selected_id, $all_option, $all);
    form_group_bootstrap($label, $input);
}

function classes_list($label, $name, $selected_id = null, $submit_on_change = false)
{
    $input = class_list($name, $selected_id, $submit_on_change);
    form_group_bootstrap($label, $input);
}

function class_types_list($label, $name, $selected_id = null, $submit_on_change = false)
{
    global $class_types;

    $input = array_selector($name, $selected_id, $class_types, array(
        'select_submit' => $submit_on_change,
        'class' => get_instance()->bootstrap->input_class
    ));
    form_group_bootstrap($label, $input);
}

function journal_types_list($label, $name, $value = null, $submit_on_change = false)
{
    global $systypes_array;

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

    $input =  array_selector($name, $value, $items, array(
        'spec_option' => _("All"),
        'spec_id' => - 1,
        'select_submit' => $submit_on_change,
        'async' => false
    ));
    form_group_bootstrap($label, $input);
}