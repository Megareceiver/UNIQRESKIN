<?php
function cash_accounts_list($label, $name, $selected_id=null, $submit_on_change=false)
{
    $sql = "SELECT ".TB_PREF."bank_accounts.id, bank_account_name, bank_curr_code, inactive
		FROM ".TB_PREF."bank_accounts
		WHERE ".TB_PREF."bank_accounts.account_type=".BT_CASH;

        $input =  combo_input($name, $selected_id, $sql, 'id', 'bank_account_name',
            array(
            'format' => '_format_add_curr',
            'select_submit'=> $submit_on_change,
		'async' => true,
                'class'=>get_instance()->bootstrap->input_class
	) );
    form_group_bootstrap($label, $input);
}