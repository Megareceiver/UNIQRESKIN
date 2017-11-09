<?php
// -----------------------------------------------------------------------------------------------
function gl_all_accounts_list($name, $selected_id = null, $skip_bank_accounts = false, $cells = false, $all_option = false, $submit_on_change = false, $all = false, $skip_bank_currency = false)
{
    if ($skip_bank_accounts)
        $sql = "SELECT chart.account_code, chart.account_name, type.name, chart.inactive, type.id
			FROM (" . TB_PREF . "chart_master chart," . TB_PREF . "chart_types type) " . "LEFT JOIN " . TB_PREF . "bank_accounts acc " . "ON chart.account_code=acc.account_code
				WHERE acc.account_code  IS NULL
			AND chart.account_type=type.id";
    else

        $sql = "SELECT chart.account_code, chart.account_name, type.name, chart.inactive, type.id
			FROM " . TB_PREF . "chart_master chart," . TB_PREF . "chart_types type
			WHERE chart.account_type=type.id";

    if ($skip_bank_currency) {
        $sql .= " AND chart.account_code NOT IN (SELECT b.account_code FROM bank_accounts AS b WHERE b.bank_curr_code <> '" . get_company_pref('curr_default') . "')";
    }
    $all_option = ' --Select GL Account --';


    return combo_input($name, $selected_id, $sql, 'chart.account_code', 'chart.account_name', array(
        'format' => '_format_account',
        'spec_option' => $all_option === true ? _("Use Item Sales Accounts") : $all_option,
        'spec_id' => '',
        'type' => 2,
        'order' => array(
            'type.class_id',
            'type.id',
            'account_code'
        ),
        'search_box' => $cells,
        'search_submit' => false,
        'size' => 12,
        'max' => 10,
        'cells' => true,
        'select_submit' => $submit_on_change,
        'async' => false,
        'category' => 2,
        'show_inactive' => $all,
        'data-live-search'=>true,
        'class'=>' show-tick '.get_instance()->bootstrap->input_class
    ));
}

function gl_account_types_list($name, $selected_id = null, $all_option = false, $all = true)
{
    global $all_items;

    $sql = "SELECT id, name FROM " . TB_PREF . "chart_types";

    return combo_input($name, $selected_id, $sql, 'id', 'name', array(
        'format' => '_format_account',
        'order' => array(
            'class_id',
            'id',
            'parent'
        ),
        'spec_option' => $all_option,
        'spec_id' => $all_items,
        'class' => get_instance()->bootstrap->input_class
    ));
}

function class_list($name, $selected_id = null, $submit_on_change = false)
{
    $sql = "SELECT cid, class_name FROM " . TB_PREF . "chart_class";

    return combo_input($name, $selected_id, $sql, 'cid', 'class_name', array(
        'select_submit' => $submit_on_change,
        'async' => false,
        'class' => get_instance()->bootstrap->input_class
    ));
}

function systypes_list($name, $value=null, $spec_opt=false, $submit_on_change=false, $exclude=array())
{
    global $systypes_array;

    // emove non-voidable transactions if needed
    $systypes = array_diff_key($systypes_array, array_flip($exclude));
    return array_selector($name, $value, $systypes,
        array(
            'spec_option'=> $spec_opt,
            'spec_id' => ALL_NUMERIC,
            'select_submit'=> $submit_on_change,
            'async' => false,
            'class'=>get_instance()->bootstrap->input_class
        )
    );
}