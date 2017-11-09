<?php

function stock_items_bootstrap($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $all = false, $editkey = false)
{
    $input = stock_items_list($name, $selected_id, $all_option, $submit_on_change, array(
        'cells' => false,
        'show_inactive' => $all,
        'class'=>'show-tick form-control',
        'data-size'=>6,
        'data-live-search'=>true
    ), $editkey);
//     $input .= hidden("_$name".'_edit',null,false);

//     $input .= '<input name="_stock_id_edit" id="_stock_id_edit" size="10" maxlength="50" value="" class="combo ui-autocomplete-input" rel="stock_id" autocomplete="off" title="" type="text">';

    $input = "<div class=\"input_stocks\">$input</div>";

    form_group_bootstrap($label, $input);
}

function stock_categories($label, $name, $selected_id = null, $spec_opt = false, $submit_on_change = false)
{
    $input = stock_categories_list($name, $selected_id, $spec_opt, $submit_on_change);
    form_group_bootstrap($label, $input);
}

function stock_item_types($label, $name, $selected_id = null, $enabled = true)
{
    global $stock_types;

    $selected_id = input_post($name);
    if (! $selected_id) {
        $selected_id = "D";
    }

    $input = array_selector($name, $selected_id, $stock_types, array(
        'select_submit' => true,
        'disabled' => ! $enabled
    ));

    form_group_bootstrap($label, $input);
}


// ------------------------------------------------------------------------------------
function stock_units($label, $name, $value = null, $enabled = true)
{
    $result = get_all_item_units();

    while ($unit = db_fetch($result))
        $units[$unit['abbr']] = $unit['name'];

    $input = array_selector($name, $value, $units, array(
        'disabled' => ! $enabled
    ));

    form_group_bootstrap($label, $input);
}

/*
 *
 */
function sales_items($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $type = '', $opts = array())
{
    $input = sales_items_list($name, $selected_id, $all_option, $submit_on_change,$type,$opts);
    form_group_bootstrap($label, $input);
}

/*
 *
 */
function sales_kits($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false){
    $input = sales_kits_list($name, $selected_id , $all_option , $submit_on_change );
    form_group_bootstrap($label, $input);
}


function sales_local_items($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false)
{

    $input =  sales_items_list($name, $selected_id, $all_option, $submit_on_change, 'local', array(
        'cells' => false,
        'editable' => false
    ));
    form_group_bootstrap($label, $input);
}

/*
 *
 */
function movement_types($label, $name, $selected_id = null)
{
    $input = movement_types_list($name, $selected_id);
    form_group_bootstrap($label, $input);
}