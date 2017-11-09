<?php
function money_total_format($number,$returnZero = false ){

	if( $number==0 && !$returnZero){
		return null;
	} else {
		return number_format(round($number,2),2,'.',',');
	}

}
if ( ! function_exists('strtonumber')){
    function strtonumber($str){
        $str = str_replace(',', '', $str);
        $str = preg_replace("/\s+/", '', $str);
        return $str;
    }
    function clean_val($string) {
       $string = str_replace(' ', '-', $string);
       return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}



if ( ! function_exists('transaction_type_tostring')){
    function transaction_type_tostring($type){
        global $systypes_array;
        if( isset($systypes_array[$type]) ){
            return $systypes_array[$type];
        }
        return '--unknow transaction type--';
    }
}

function atdate_format(){
    global $dateformats, $dateseps;
    $format = $dateformats[$_SESSION["wa_current_user"]->prefs->date_format()];
    $sep    = $dateseps[$_SESSION["wa_current_user"]->prefs->date_sep()];
    switch ($format){
        case "MMDDYYYY":    $format = "m".$sep."d".$sep."Y"; break;
        case "DDMMYYYY":    $format = "d".$sep."m".$sep."Y"; break;
        case "YYYYMMDD":    $format = "Y".$sep."M".$sep."d"; break;
        case "MmmDDYYYY":   $format = "M".$sep."m".$sep."d".$sep."Y"; break;
        case "DDMmmYYYY":   $format = "d".$sep."M".$sep."m".$sep."Y"; break;
        case "YYYYMmmDD":   $format = "Y".$sep."M".$sep."m".$sep."d"; break;
        default : $format = "d".$sep."M".$sep."Y"; break;
    }
    return $format;
}

function atmoney_format($number=0){
    $dec = user_price_dec();
    return number_format2($number,$dec);
}




