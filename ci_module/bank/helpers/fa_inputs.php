<?php

function bank_account_types_list($name, $selected_id = null)
{
    global $bank_account_types;

    return array_selector($name, $selected_id, $bank_account_types);
}

/*
 *
 */
function tag_list($name, $height, $type, $multi = false, $all = false, $spec_opt = false)
{
    // Get tags
    // global $path_to_root;
    include_once (ROOT . "/admin/db/tags_db.inc");

    $results = get_tags($type, $all);

    while ($tag = db_fetch($results))
        $tags[$tag['id']] = $tag['name'];

    if (! isset($tags)) {
        $tags[''] = $all ? _("No tags defined.") : _("No active tags defined.");
        $spec_opt = false;
    }
    return array_selector($name, null, $tags, array(
        'multi' => $multi,
        'height' => $height,
        'spec_option' => $spec_opt,
        'spec_id' => - 1,
        'class' => get_instance()->bootstrap->input_class
    ));
}

/*
 *
 */
function payment_person_types_list($name, $selected_id = null, $submit_on_change = false)
{
    global $payment_person_types;

    $items = array();
    foreach ($payment_person_types as $key => $type) {
        if ($key != PT_WORKORDER)
            $items[$key] = $type;
    }
    return array_selector($name, $selected_id, $items, array(
        'select_submit' => $submit_on_change
    ));
}

// -----------------------------------------------------------------------------------------------
function bank_accounts_list($name, $selected_id = null, $submit_on_change = false, $spec_option = false)
{
    $sql = "SELECT " . TB_PREF . "bank_accounts.id, bank_account_name, bank_curr_code, inactive
		FROM " . TB_PREF . "bank_accounts";

    return combo_input($name, $selected_id, $sql, 'id', 'bank_account_name', array(
        'format' => '_format_add_curr',
        'select_submit' => $submit_on_change,
        'spec_option' => $spec_option,
        'spec_id' => '',
        'async' => false,

        'data-live-search'=>true,
        'class'=>' show-tick '.get_instance()->bootstrap->input_class
//         'class' => $bootstrap->input_class
    ));
}

function bank_balance_label($bank_acc, $parms = '')
{
    $to = add_days(Today(), 1);
    $bal = get_balance_before_for_bank_account($bank_acc, $to);

    $anchor_attributes = array(
        'target' => '_blank',
        'onclick' => "javascript:openWindow(this.href,this.target); return false;"
    );
    if ($bal < 0) {
        $anchor_attributes['class'] = 'redfg';
    }
    $bal_val_str = anchor("gl/inquiry/bank_inquiry.php?bank_account=$bank_acc", price_format($bal), $anchor_attributes);

    input_label_bootstrap('Bank Balance', NULL, $bal_val_str);
    // label_row(_(":"), "<a target='_blank' " . ($bal < 0 ? 'class="redfg"' : '') . "href='" . site_url() . "/gl/inquiry/bank_inquiry.php?bank_account=" . $bank_acc . "'" . " onclick=\"javascript:openWindow(this.href,this.target); return false;\" >&nbsp;" . price_format($bal) . "</a>", $parms);
}

/*
 *
 */
function quick_entries_list($name, $selected_id = null, $type = null, $submit_on_change = false)
{
    $where = false;
    $sql = "SELECT id, description FROM " . TB_PREF . "quick_entries";
    if ($type != null)
        $sql .= " WHERE type=$type";

    return combo_input($name, $selected_id, $sql, 'id', 'description', array(
        'spec_id' => '',
        'order' => 'description',
        'select_submit' => $submit_on_change,
        'async' => false
    ));
}

/*
 *
 */
function bank_reconciliation_list($account, $name, $selected_id = null, $submit_on_change = false, $special_option = false)
{
    $sql = "SELECT reconciled, reconciled FROM " . TB_PREF . "bank_trans
		WHERE bank_act=" . db_escape($account) . " AND reconciled IS NOT NULL
		GROUP BY reconciled";
    return combo_input($name, $selected_id, $sql, 'id', 'reconciled', array(
        'spec_option' => $special_option,
        'format' => '_format_date',
        'spec_id' => '',
        'select_submit' => $submit_on_change
    ));
}
