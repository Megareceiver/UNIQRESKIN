<?php

function input_customers($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $show_inactive = false, $editkey = false)
{
    $input = customer_list($name, $selected_id, $all_option, $submit_on_change, $show_inactive, $editkey);
    form_group_bootstrap($label, $input);
}

function input_customer_branches($label, $customer_id, $name, $selected_id = null, $all_option = true, $enabled = true, $submit_on_change = false, $editkey = false)
{
    $input = customer_branches_list($customer_id, $name, $selected_id, $all_option, $enabled, $submit_on_change, $editkey);
    form_group_bootstrap($label, $input);
}

function policies_input($label, $name, $selected = null)
{
    $policy_items = array(
        '' => _("Automatically put balance on back order"),
        'CAN' => _("Cancel any quantites not delivered")
    );
    $input = array_selector($name, $selected, $policy_items)
    ;
    form_group_bootstrap($label, $input);
}

function credit_types($label, $name, $selected=null, $submit_on_change=false)
{
    $input =  array_selector($name, $selected,
        array( 'Return' => _("Items Returned to Inventory Location"),
            'WriteOff' => _("Items Written Off")),
        array( 'select_submit'=> $submit_on_change ) );

    form_group_bootstrap($label, $input);
}