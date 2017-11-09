<?php
function bank_account_types($label, $name, $selected_id = null)
{
    $input = bank_account_types_list($name, $selected_id);
    form_group_bootstrap($label, $input,null,null,array('banktypename'=>$name));
}

function tags_list($label, $name, $height, $type, $mult = false, $all = false, $spec_opt = false)
{
    $input = tag_list($name, $height, $type, $mult, $all, $spec_opt);
    form_group_bootstrap($label, $input);
}

function payment_person_types($label, $name, $selected_id=null, $related=null)
{
    $input = payment_person_types_list($name, $selected_id, $related);
	form_group_bootstrap($label, $input);
}

function bank_accounts($label, $name, $selected_id = null, $submit_on_change = false)
{
    $input = bank_accounts_list($name, $selected_id, $submit_on_change);
    form_group_bootstrap($label, $input);
}

function input_quick_entries($label, $name, $selected_id = null, $type, $submit_on_change = false)
{
    $input = quick_entries_list($name, $selected_id, $type, $submit_on_change);
    form_group_bootstrap($label, $input);
}

function bank_reconciliations($label,$account, $name, $selected_id=null, $submit_on_change=false, $special_option=false){
    $input = bank_reconciliation_list($account, $name, $selected_id, $submit_on_change, $special_option);
    form_group_bootstrap($label, $input);
}