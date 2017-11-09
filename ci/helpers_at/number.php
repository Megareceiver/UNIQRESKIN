<?php


//--------------------------------------------------------------------------------------
//
//	Simple English version of number to words conversion.
//
function _number_to_words($number)
{
    $Bn = floor($number / 1000000000); /* Billions (giga) */
    $number -= $Bn * 1000000000;
    $Gn = floor($number / 1000000);  /* Millions (mega) */
    $number -= $Gn * 1000000;
    $kn = floor($number / 1000);     /* Thousands (kilo) */
    $number -= $kn * 1000;
    $Hn = floor($number / 100);      /* Hundreds (hecto) */
    $number -= $Hn * 100;
    $Dn = floor($number / 10);       /* Tens (deca) */
    $n = $number % 10;               /* Ones */

    $res = "";

    if ($Bn)
        $res .= _number_to_words($Bn) . " Billion";
    if ($Gn)
        $res .= (empty($res) ? "" : " ") . _number_to_words($Gn) . " Million";
    if ($kn)
        $res .= (empty($res) ? "" : " ") . _number_to_words($kn) . " Thousand";
    if ($Hn)
        $res .= (empty($res) ? "" : " ") . _number_to_words($Hn) . " Hundred";

    $ones = array("", "One", "Two", "Three", "Four", "Five", "Six",
        "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",
        "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen",
        "Nineteen");
    $tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty",
        "Seventy", "Eighty", "Ninety");

    if ($Dn || $n)
    {
        if (!empty($res))
            $res .= " and ";
        if ($Dn < 2)
            $res .= $ones[$Dn * 10 + $n];
        else
        {
            $res .= $tens[$Dn];
            if ($n)
                $res .= "-" . $ones[$n];
        }
    }

    if (empty($res))
        $res = "zero";
    return $res;
}


function price_in_words($amount, $document=0) {
    global $ci;
    $price = hook_price_in_words($amount, $document);

    if ($price)
        return $price;

    // Only usefor Remittance and Receipts as default
    if (!($document == ST_SUPPAYMENT || $document == ST_CUSTPAYMENT || $document == ST_CHEQUE))
        return "";

    $negative = false;
    if( $amount < 0){
        $negative = true;
        $amount = abs($amount);
    }
    if ($amount < 0 || $amount > 999999999999)
        return "";
    $dec = user_price_dec();


    $frac = null;
    $money_name ='';
    if ($dec > 0){
        $divisor = pow(10, $dec);
        $number_dec = round2($amount - floor($amount), $dec) * $divisor;
        if( $number_dec > 0 ){
            $sys_model = $ci->model('config',true);
            $currency_default = $sys_model->curr_default();

            if( $currency_default->hundreds_name ){
                $frac = ' and '.$currency_default->hundreds_name.' ';
                $money_name = $currency_default->currency.' ';
            } else {
                $frac = " "._("point")." ";
            }



            $frac .=_number_to_words(intval($number_dec));
        }
    }

    return _number_to_words(intval($amount)) . $frac .( $negative? ' negative': '' )._(' Only');
}


/*
 Returns number formatted according to user setup and using $decimals digits after dot
 (defualt is 0). When $decimals is set to 'max' maximum available precision is used
 (decimals depend on value) and trailing zeros are trimmed.
 */
function number_format2($number, $decimals=0)
{
    global $thoseps, $decseps;
    $tsep = $thoseps[$_SESSION["wa_current_user"]->prefs->tho_sep()];
    $dsep = $decseps[$_SESSION["wa_current_user"]->prefs->dec_sep()];
    $number = str_replace($tsep, NULL, $number);
    $number = str_replace($dsep, ",", $number);
    
    $number = floatval($number);

    //return number_format($number, $decimals, $dsep,	$tsep);
    if($decimals==='max')
        $dec = 15 - floor(log10(abs($number)));
    else {
        $delta = ($number < 0 ? -.0000000001 : .0000000001);
        $number += $delta;
        $dec = $decimals;
    }
    $num = number_format($number, $dec, $dsep, $tsep);
    if( ABS($num) == 0 ){
        $num = 0;
    }
    return $decimals==='max' ? rtrim($num, '0') : $num;
}

function str2numeric($number=NULL){
    if( is_null($number) ){
        return 0;
    }
    global $thoseps, $decseps;
    $tsep = $thoseps[$_SESSION["wa_current_user"]->prefs->tho_sep()];
    $dsep = $decseps[$_SESSION["wa_current_user"]->prefs->dec_sep()];
    $number = str_replace($tsep, NULL, $number);
    $number = str_replace($dsep, ",", $number);
    $number = floatval($number);
    return $number;
}