<?php

function to_home_currency($amount, $currency_code, $date_){

    $ex_rate = get_exchange_rate_to_home_currency($currency_code, $date_);

    return round2($amount / $ex_rate,  user_price_dec());
}

function curr_default(){
    return get_company_pref("curr_default");
}