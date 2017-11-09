<?php

function item_tax_types_list($name, $selected_id = null)
{
    $sql = "SELECT id, name FROM " . TB_PREF . "item_tax_types";

    return combo_input($name, $selected_id, $sql, 'id', 'name', array(
        'order' => 'id',
        'class'=>get_instance()->bootstrap->input_class
    ));
}


function tax_types_list($name, $selected_id=null, $none_option=false, $submit_on_change=false,$group_tax=1) {
    global $ci;

    if( !$selected_id ){
        $selected_id = input_post($name);
    }
   
    if( !isset($_SESSION['taxcode'][$group_tax]) OR empty($_SESSION['taxcode'][$group_tax])){
        $item_api = get_instance()->api->get_data('taxcode/'.$group_tax);

        if( !empty($item_api) AND isset($item_api->options) ){
            $_SESSION['taxcode'][$group_tax] = (array)$item_api->options;
        }
    }
    $items = $_SESSION['taxcode'][$group_tax];


    return array_selector($name, $selected_id, $items, array(
        'select_submit' => $submit_on_change,
        'async' => false,
        'class'=>'show-tick form-control',
        'data-size'=>6,
        'data-live-search'=>true
    ));

}