<?php

function pos_list($label, $name, $selected_id = null, $spec_option = false, $submit_on_change = false)
{
    $sql = "SELECT id, pos_name, inactive FROM " . TB_PREF . "sales_pos";

    default_focus($name);

    $input = combo_input($name, $selected_id, $sql, 'id', 'pos_name', array(
        'select_submit' => $submit_on_change,
        'async' => true,
        'spec_option' => $spec_option,
        'spec_id' => - 1,
        'class' => get_instance()->bootstrap->input_class,
        'order' => array(
            'pos_name'
        )
    ));

    form_group_bootstrap($label, $input);
}

function print_profiles($label, $name, $selected_id = null, $spec_opt = false, $submit_on_change = true)
{
    $sql = "SELECT profile FROM " . TB_PREF . "print_profiles" . " GROUP BY profile";
    $result = db_query($sql, 'cannot get all profile names');
    $profiles = array();
    while ($myrow = db_fetch($result)) {
        $profiles[$myrow['profile']] = $myrow['profile'];
    }

    $input = array_selector($name, $selected_id, $profiles, array(
        'select_submit' => $submit_on_change,
        'spec_option' => $spec_opt,
        'spec_id' => '',
        'class' => get_instance()->bootstrap->input_class
    ));

    form_group_bootstrap($label, $input);
}

// --------------------------------------------------------------------------------------
// Displays currency exchange rate for given date.
// When there is no exrate for today,
// gets it form ECB and stores in local database.
//
function exchange_rate_display($from_currency, $to_currency, $date_, $force_edit = false)
{
    global $Ajax, $xr_provider_authoritative;

    $readonly = false;

    if ($from_currency != $to_currency) {
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
             * quannh remove Exchange Rate
             */
            if (! $rate && 0 == 1) { /* retry from remote service */
                // if (!$rate ) {
                $row = get_currency($currency);

                if ($row['auto_update']) // autoupdate means use remote service & store exrate on first transaction.
{
                    $rate = retrieve_exrate($currency, $date_);
                    if (! $rate) {
                        display_warning(sprintf(_("Cannot retrieve exchange rate for currency %s. Please adjust approximate rate if needed."), $currency));
                    } elseif ($xr_provider_authoritative) {
                        // if the remote exrate is considered authoritative we can store the rate here,
                        // otherwise exrate will be stored during transaction write
                        $readonly = true;
                        add_new_exchange_rate($currency, $date_, $rate);
                    }
                }
            }

            if (! $rate) { // get and edit latest available
                $rate = get_exchange_rate_from_home_currency($currency, $date_);
            }
            if ($from_currency != $comp_currency)
                $rate = 1 / ($rate / get_exchange_rate_from_home_currency($to_currency, $date_));
            $Ajax->activate('_ex_rate_span');
        }

        $rate = number_format2($rate, user_exrate_dec());

        if ($force_edit || ! $readonly)
            $ctrl = "<input type=\"text\" name=\"_ex_rate\" size=\"8\" maxlength=\"8\" value=\"$rate\">";
        else
            $ctrl = "<span id=\"_ex_rate\">$rate</span>";

        input_label_bootstrap( _("Exchange Rate"), null, $span = "<span style='vertical-align:top;' id='_ex_rate_span'>$ctrl $from_currency = 1 $to_currency</span>");

        if ($force_edit || ! $readonly) {
            input_label_bootstrap(_("Use Default Rate"),null, '<input type="checkbox" name="ex_rate_allow" value="1" ' . ((input_post('ex_rate_allow') == 1) ? 'checked' : '') . '>');
        }

        $Ajax->addUpdate('_ex_rate_span', '_ex_rate_span', $span);
    }
}


function input_array_selector($label,$name, $selected_id, $items, $options = null){

    $input = array_selector($name, $selected_id, $items, array(

        'class' => get_instance()->bootstrap->input_class
    ));

    form_group_bootstrap($label, $input);
}