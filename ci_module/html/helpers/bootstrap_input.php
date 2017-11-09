<?php

function input_text_bootstrap($label, $name, $value = null, $title = null, $submit_on_change = false, $size = NULL, $max = NULL, $help = NULL)
{
//     $size = 16;

    default_focus($name);
    if (! isset($_POST[$name]) || $_POST[$name] == "") {
        if ($value)
            $_POST[$name] = $value;
        else
            $_POST[$name] = "";
    }

    $attributes = array(
        'type' => 'text',
        'name' => $name,
        'value' => $_POST[$name]
    );

    if (strlen($title) > 0) {
        $attributes['title'] = $title;
    }
    if (is_int($size)) {
        $attributes['size'] = $size;
    }
    if (is_int($max)) {
        $attributes['maxlength'] = $max;
    } else
        if ($size) {
            $attributes['maxlength'] = $size;
        }

    $attributes['class'] = 'form-control';
    if ($submit_on_change) {
        $attributes['class'] .= ' searchbox';
    }

    $html = '<input ' . _parse_attributes($attributes) . ' >';

    global $Ajax;
    $Ajax->addUpdate($name, $name, $_POST[$name]);

    form_group_bootstrap($label, $html, NULL, $help);
}

function input_text($label, $name, $value = null, $title = null, $submit_on_change = false, $size = NULL, $max = NULL, $help = NULL)
{
    return input_text_bootstrap($label, $name, $value, $title, $submit_on_change, $size, $max, $help);
}

function input_password($label, $name, $help=NULL)
{
     $attributes = array(
        'type' => 'password',
         'class'=>'form-control',
        'name' => $name,
        'value' => NULL
    );
     $input = '<input ' . _parse_attributes($attributes) . ' >';
     form_group_bootstrap($label, $input,null,$help);
}

function cust_allocations_bootstrap($label, $name, $selected = null)
{
    global $transaction_type;
    $input = array_selector($name, $selected, $transaction_type, array(
        'class' => 'form-control'
    ));

    form_group_bootstrap($label, $input);
}

function input_date_bootstrap($label, $name, $value = NULL, $disabled = false, $submit_on_change = null, $inc_days = 0, $inc_months = 0, $inc_years = 0)
{
    if (! $value) {
        if (isset($_POST[$name])) {
            $value = $_POST[$name];
        } else {
            $value = Today();
        }
    }
    if ($inc_days != 0)
        $value = add_days($value, $inc_days);
    if ($inc_months != 0)
        $value = add_months($value, $inc_months);
    if ($inc_years != 0)
        $value = add_years($value, $inc_years);

    set_post($name, $value, false);

    $input = '<input type="text" class="form-control date-picker" placeholder="" value="' . $value . '" data-date-format="' . get_instance()->dateformat . '" name="' . $name . '" >';

    $color = 'font-blue';
    $icon = '<i class="fa fa-calendar ' . $color . '"></i>';
    $input = '<div class="input-icon input-icon-sm right inputdate" >' . $icon . $input . '</div>';

    if (strlen($label) < 1)
        return $input;

    return form_group_bootstrap($label, $input);
}

function submit_bootstrap($name, $value, $title = false, $async = false,$icon=NULL)
{
    $input = submit($name, $value, false, $title, $async, $icon);

    echo form_group_bootstrap(NULL, $input);
}

function locations_bootstrap($label, $name, $selected_id = null, $submit_on_change = false, $all_option = false)
{
    $input = locations_list($name, $selected_id, $all_option, $submit_on_change, 'form-control');
    form_group_bootstrap($label, $input);
}

function input_label_bootstrap($label, $name, $value = null, $label_col=3)
{
    if (! $value AND strlen($name) > 0 ) {
        $value = input_val($name);
    }
    $input = "<span class=\"form-control-static\">$value</span>";
    form_group_bootstrap($label, $input);
}

function input_label($label, $name, $value = null){
    if (! $value AND strlen($name) > 0 ) {
        $value = input_val($name);
    }
    $input = "<span class=\"form-control-static\">$value</span>";
    form_group_bootstrap($label, $input);
}

// --------------------------------------------------------------------------------------
// Displays currency exchange rate for given date.
// When there is no exrate for today,
// gets it form ECB and stores in local database.
//
function exchange_rate_bootstrap($from_currency, $to_currency, $date_, $force_edit = false)
{
    global $Ajax, $xr_provider_authoritative;
    $readonly = false;
    if ($from_currency == $to_currency)
        return FALSE;

    $rate = get_post('_ex_rate');
    if (check_ui_refresh() || ! $rate) { // readonly or ui context changed
        $comp_currency = get_company_currency();
        if ($from_currency == $comp_currency)
            $currency = $to_currency;
        else
            $currency = $from_currency;

        $rate = get_date_exchange_rate($currency, $date_); // try local

        if ($rate)
            $readonly = true; // if we have already local exrate keep it unchanged

        /*
         * if (!$rate ) {	// retry from remote service
         *
         * $row = get_currency($currency);
         *
         * if ($row['auto_update']) // autoupdate means use remote service & store exrate on first transaction.
         * {
         * $rate = retrieve_exrate($currency, $date_);
         * if (!$rate) {
         * display_warning(sprintf(_("Cannot retrieve exchange rate for currency %s. Please adjust approximate rate if needed."), $currency));
         * } elseif ($xr_provider_authoritative) {
         * // if the remote exrate is considered authoritative we can store the rate here,
         * // otherwise exrate will be stored during transaction write
         * $readonly = true;
         * add_new_exchange_rate($currency, $date_, $rate);
         * }
         * }
         */

        if (! $rate) { // get and edit latest available
            $rate = get_exchange_rate_from_home_currency($currency, $date_);
        }

        if ($from_currency != $comp_currency)
            $rate = 1 / ($rate / get_exchange_rate_from_home_currency($to_currency, $date_));
        $Ajax->activate('_ex_rate_span');
    }

    $rate = number_format2($rate, user_exrate_dec());

    if ($force_edit || ! $readonly)
        $ctrl = '<input type="text" name="_ex_rate" size="8" maxlength="8" value="' . $rate . '">';
    else
        $ctrl = "<span id=\"_ex_rate\">$rate</span>";

    $help = "<span id=\"_ex_rate_span\" > $from_currency = 1 $to_currency </span>";
    input_text_bootstrap('Exchange Rate', '_ex_rate', $rate, null, false, NULL, NULL, $help);
    // label_row(_("Exchange Rate:"), $span = "<span style='vertical-align:top;' id='_ex_rate_span'>$ctrl $from_currency = 1 $to_currency</span>" );

    // label_row(_("Exchange Rate:"), $span = "<span style='vertical-align:top;' id='_ex_rate_span'>$ctrl $from_currency = 1 $to_currency</span>" );

    if ($force_edit || ! $readonly) {
        // label_row(_("Use Default Rate:"), '<input type="checkbox" name="ex_rate_allow" value="1" '. ( ($_POST['ex_rate_allow']==1) ? 'checked' : '' ).'>' );
    }

    $Ajax->addUpdate('_ex_rate_span', '_ex_rate_span', $help);
}

function dimensions_bootstrap($label, $name, $selected_id = null, $no_option = false, $showname = null, $showclosed = false, $showtype = 0, $submit_on_change = false)
{
    $input = dimensions_list($name, $selected_id, $no_option, $showname, $submit_on_change, $showclosed, $showtype, 'form-control');
    form_group_bootstrap($label, $input);
}

function input_textarea_bootstrap($label, $name, $value = NULL, $title = null, $rows = 0)
{
    global $Ajax;

    default_focus($name);

    if ($value == null)
        $value = (! isset($_POST[$name]) ? "" : $_POST[$name]);

    $attributes = array(
        'name' => $name
    );

    if ($rows > 0) {
        $attributes['rows'] = $rows;
    }

    if (strlen($title) > 0) {
        $attributes['title'] = $title;
    }

    $attributes['class'] = 'form-control';

    $input = '<textarea ' . _parse_attributes($attributes) . ' >' . $value . '</textarea>';

    $Ajax->addUpdate($name, $name, $value);

    if (strlen($label) > 0)
        return form_group_bootstrap($label, $input);
    return $input;
}

function input_textarea($label, $name, $value = NULL, $title = null, $rows = 0){
    return input_textarea_bootstrap($label, $name, $value , $title, $rows);
}

function shippers_bootstrap($label, $name, $selected_id = null)
{
    $input = shippers_list($name, $selected_id, 'form-control');
    return form_group_bootstrap($label, $input);
}

function currency_bootstrap($label, $name, $selected_id = null, $submit_on_change = true)
{
    $input = currencies_list($name, $selected_id, $submit_on_change, 'form-control');
    return form_group_bootstrap($label, $input,null,null,array('currname'=>$name));
}

function fiscalyear_bootstrap($label, $name, $selected_id = null, $submit_on_change = false)
{
    $input = fiscalyears_list($name, $selected_id, $submit_on_change, 'form-control');
    form_group_bootstrap($label, $input);
}

function file_bootstrap($label, $name, $help = NULL, $id = "")
{
    $attributes = array(
        'class' => 'btn btn-default',
        'type'=>'file',
        'name'=>$name,

    );
    if ($id != "") {
        $attributes['id'] = $id;
    }

    $input = '<input ' . _parse_attributes($attributes) . '>';
    form_group_bootstrap($label, $input, null, $help);
}

function security_roles_bootstrap($label, $name, $selected_id = null, $new_item = false, $submit_on_change = false, $show_inactive = false)
{
    $input_class = 'form-control';
    $input = security_roles_list($name, $selected_id, $new_item, $submit_on_change, $show_inactive, $input_class);
    form_group_bootstrap($label, $input);
}

/*
 * Sale
 */
function sale_payments_bootstrap($label, $name, $category, $selected_id = null, $submit_on_change = true)
{
    $input = sale_payment_list($name, $category, $selected_id, $submit_on_change, 'form-control');
    form_group_bootstrap($label, $input);
}

function sales_types_bootstrap($label, $name, $selected_id = null, $submit_on_change = false, $special_option = false)
{
    $input = sales_types_list($name, $selected_id, $submit_on_change, $special_option, 'form-control');
    form_group_bootstrap($label, $input,null,null,array('saletypename'=>$name));
}

function sales_persons_bootstrap($label, $name, $selected_id = null, $submit_on_change = false, $special_option = false)
{
    $input = sales_persons_list($name, $selected_id, $special_option, 'form-control');
    form_group_bootstrap($label, $input);
}

function sales_areas_bootstrap($label, $name, $selected_id = null)
{
    $input = sales_areas_list($name, $selected_id);
    form_group_bootstrap($label, $input);
}

function sales_groups_bootstrap($label, $name, $selected_id = null, $special_option = false)
{
    $input = sales_groups_list($name, $selected_id, $special_option);
    form_group_bootstrap($label, $input);
}

/*
 * Payment term
 */
function payment_terms_bootstrap($label, $name, $selected_id = null)
{
    $input = payment_terms_list($name, $selected_id);
    form_group_bootstrap($label, $input);
}

function credit_status_bootstrap($label, $name, $selected_id = null)
{
    $input = credit_status_list($name, $selected_id);
    form_group_bootstrap($label, $input);
}

/*
 * Tax
 */
function tax_groups_bootstrap($label, $name, $selected_id = null, $none_option = false, $submit_on_change = false)
{
    $input = tax_groups_list($name, $selected_id, $none_option, $submit_on_change);
    form_group_bootstrap($label, $input);
}

function category_types_bootstrap($label, $name, $selected_id = null, $filter = array(), $submit_on_change = true)
{
    $input = crm_category_types_list($name, $selected_id, $filter, $submit_on_change);
    form_group_bootstrap($label, $input);
}


/*
 *
 */
function yesno_bootstrap($label, $name, $selected_id = null, $name_yes = "", $name_no = "", $submit_on_change = false)
{
    $input = yesno_list($name, $selected_id, $name_yes, $name_no, $submit_on_change);
    form_group_bootstrap($label, $input,null,null,array('selectname'=>$name));
}

/*
 *
 */
function check_bootstrap($label, $name, $value = null, $submit_on_change = false, $title = false, $params = '',$help=NULL)
{
    $inputs = checkbox_material($name, $value, $submit_on_change, false);
    $html = "<div class=\"md-checkbox-list clearfix\">$inputs</div>";
    form_group_bootstrap($label, $html,null,$help);
}

function checkbox_material($name, $value = null, $submit_on_change = false, $show_label = FALSE)
{
    $checkbox = checkbox(NULL, $name, $value, $submit_on_change, NULL, 'md-check');
    $input = $checkbox;

    $effect = '<span></span><span class="check"></span><span class="box"></span>';
    if ($show_label != FALSE) {
        if (! is_string($show_label)) {
            $checkbox_label = str_replace(array(
                '_',
                '-'
            ), ' ', $name);
        } else {
            $checkbox_label = $show_label;
        }
        $effect .= ucwords($checkbox_label);
    }

    $input = $checkbox . '<label for="checkboxs_' . $name . '">' . $effect . '</label>';
    return '<div class="md-checkbox clearfix">' . $input . '</div>';
}

function radios_bootstrap($label, $name, $value = null, $submit_on_change = false, $inputs_val = array())
{
    if (! empty($inputs_val)) {
        $inputs = '';
        foreach ($inputs_val as $k => $t) {
            $inputs .= radio_material($name, $k, $submit_on_change, $t);
            // $inputs .= checkbox_material($name, $k, $submit_on_change, $t);
        }
    } else {
        $inputs = checkbox_material($name, $value, $submit_on_change, false);
    }

    $html = "<div class=\"md-radio-inline\">$inputs</div>";
    form_group_bootstrap($label, $html);
}

function radio_material($name, $value = null, $submit_on_change = false, $show_label = FALSE)
{
    $checkbox = radio(NULL, $name, $value, $selected = null, $submit_on_change, 'md-radiobtn');
    $input = $checkbox;

    $effect = '<span></span><span class="check"></span><span class="box"></span>';
    if ($show_label != FALSE) {
        if (! is_string($show_label)) {
            $checkbox_label = str_replace(array(
                '_',
                '-'
            ), ' ', $name);
        } else {
            $checkbox_label = $show_label;
        }
        $effect .= ucwords($checkbox_label);
    }

    $input = $checkbox . '<label for="' . "radio_$name" . "_$value" . '">' . $effect . '</label>';
    return '<div class="md-radio">' . $input . '</div>';
}



