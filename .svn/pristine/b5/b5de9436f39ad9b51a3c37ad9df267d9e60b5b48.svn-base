<?php

function wo_types_input ($label, $name, $selected_id = null)
{
    $input = wo_types_list($name, $selected_id);
    form_group_bootstrap($label, $input);
}

function stock_manufactured_items_input ($label, $name, $selected_id = null, 
        $all_option = false, $submit_on_change = false)
{
    $input = stock_manufactured_items_list_cells(null, $name, $selected_id, 
            $all_option, $submit_on_change);
    form_group_bootstrap($label, $input);
}