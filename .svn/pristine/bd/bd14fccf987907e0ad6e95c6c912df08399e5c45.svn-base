<?php

function quick_entry_types($label, $name, $selected_id = null, $submit_on_change = false)
{
    global $quick_entry_types;

    $input = array_selector($name, $selected_id, $quick_entry_types, array(
        'select_submit' => $submit_on_change
    ));
    form_group_bootstrap($label, $input);
}


function quick_actions($label, $name, $selected_id = null, $submit_on_change = false)
{
    global $quick_actions;

    $input = array_selector($name, $selected_id, $quick_actions, array(
        'select_submit' => $submit_on_change
    ));
    form_group_bootstrap($label, $input);
}