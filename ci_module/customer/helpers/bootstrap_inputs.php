<?php
function customer_list_bootstrap($label, $name, $selected_id = null, $submit_on_change = false, $editkey = false, $spec_option = true, $show_inactive = false)
{
//     $input = customer_list($name, $selected_id, $all_option, $submit_on_change, $show_inactive, $editkey, 'form-control');
    $input = customer_list($name, $selected_id = null, $spec_option , $submit_on_change, $show_inactive , $editkey );

    return strlen($label) > 0 ? form_group_bootstrap($label, $input) : $input;
}

function customer_branches_bootstrap($label, $customer_id, $name, $selected_id = null, $spec_option = true, $enabled = true, $submit_on_change = false, $editkey = false)
{
    $input = customer_branches_list($customer_id, $name, $selected_id, $spec_option, $enabled, $submit_on_change, $editkey, 'form-control');
    form_group_bootstrap($label, $input);
}