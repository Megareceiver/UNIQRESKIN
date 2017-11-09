<?php
class pdf_smarty {
    static $smarty, $ci;
    function __construct(){
        $ci = get_instance();
        if( isset($ci->smarty) ){
            self::$smarty = $ci->smarty;
        }
        self::$ci = $ci;
    }




    static function pdf_column_w($template,$params){
        if( isset($template['content_w']) ){
            $content_w = $template['content_w'];
        } elseif( isset($params->tpl_vars['content_w']->value) ) {
            $content_w = $params->tpl_vars['content_w']->value;
        } else {
//             $content_w = floatval( self::$smarty->getTemplateVars('content_w') );
        }
//         $content_w = 1;
        if( $content_w <= 0 ){
            return;
        }

        $percent = ( isset($template['w']) )?$template['w']:0;


        if( $percent > 0 ){
            return ($content_w*$percent/100).'mm';
        }


    }
    static function money_words($template=null){
        $number = ( isset($template['num']) )?$template['num']:null;
        $currency_code = ( isset($template['curr_code']) )?$template['curr_code']:null;

        if( $number ){
            return ( $currency_code?"<b>$currency_code :</b>":null ).price_in_words($number, ST_CUSTPAYMENT);
        }
    }



    static function date_format($template){
        global $dateformats,$dateseps;
        $date_system = user_date_format();
        $dateseps_system = user_date_sep();

        $dateformat = $dateformats[$date_system];
        $dateseps_use = $dateseps[$dateseps_system];
        $format = 'd-m-Y';

        switch ($dateformat){
            case 'MMDDYYYY': $format="m$dateseps_use"."d$dateseps_use"."Y"; break;
            case 'DDMMYYYY': $format="d$dateseps_use"."m$dateseps_use"."Y"; break;
            case 'YYYYMMDD': $format="Y$dateseps_use"."m$dateseps_use"."d"; break;
            case 'MmmDDYYYY': $format="M$dateseps_use"."d$dateseps_use"."Y"; break;
            case 'DDMmmYYYY': $format="D$dateseps_use"."M$dateseps_use"."Y"; break;
            case 'YYYYMmmDD': $format="Y$dateseps_use"."M$dateseps_use"."D"; break;
            default: $format = 'd-m-Y'; break;

        }
        $time = ( isset($template['time']) )?$template['time']: date($format);
        if( $time !='0000-00-00' )
            return date($format,strtotime($time));
    }

    function datetime_format($template){
    	global $dateformats,$dateseps;
    	$date_system = user_date_format();
    	$dateseps_system = user_date_sep();

    	$dateformat = $dateformats[$date_system];
    	$dateseps_use = $dateseps[$dateseps_system];
    	$format = 'd-m-Y';

    	switch ($dateformat){
    		case 'MMDDYYYY': $format="m$dateseps_use"."d$dateseps_use"."Y"; break;
    		case 'DDMMYYYY': $format="d$dateseps_use"."m$dateseps_use"."Y"; break;
    		case 'YYYYMMDD': $format="Y$dateseps_use"."m$dateseps_use"."d"; break;
    		case 'MmmDDYYYY': $format="M$dateseps_use"."d$dateseps_use"."Y"; break;
    		case 'DDMmmYYYY': $format="D$dateseps_use"."M$dateseps_use"."Y"; break;
    		case 'YYYYMmmDD': $format="Y$dateseps_use"."M$dateseps_use"."D"; break;
    		default: $format = 'd-m-Y'; break;

    	}
    	$format.= ' H:i:s';
    	$time = ( isset($template['time']) )?$template['time']: date($format);
    	if( $time !='0000-00-00' )
    		return date($format,strtotime($time));
    }

    static function number_format($template=null){
        $number = ( isset($template['num']) )?$template['num']:null;
        $zero = ( isset($template['zero']) )?$template['zero']: true ;
        $absolute = ( isset($template['absolute']) )?$template['absolute']: false ;
        $dec = ( isset($template['amount']) && $template['amount']==1) ? user_amount_dec() : user_price_dec();
        if( isset($template['dec']) && is_numeric($template['dec'])  ){
            $dec = $template['dec'];
        }
        if( !$zero && $number == 0 ){
            return null;
        } else
            if( $absolute ){
            $number = abs($number);
        }
            return number_format2($number,$dec);
    }

    static function credit_debit_exchange($template){
        $number = ( isset($template['num']) )? floatval($template['num']) : 0;
        $field = ( isset($template['field']) )?$template['field']: 0;
        $zero = ( isset($template['zero']) )?$template['zero']: true ;
        $dec = user_amount_dec();

        if( $field=='debit' &&  $number > 0){
            return number_format2($number,$dec);
        } elseif ($field=='credit' &&  $number < 0) {
            return number_format2(abs($number),$dec);
        }

    }

    static function money_exchange($template){
        $number = ( isset($template['num']) )? floatval($template['num']) : 0;
        $zero = ( isset($template['zero']) )?$template['zero']: false ;


        if( $number != 0 || $zero ){
            return number_format2($number,user_amount_dec());
        }

    }

    static function days_diff($template){
        if( !isset($template['begin']) ){
            return 0;
        } else {
            $end =  isset($template['end']) ? $template['end'] : Today();

            $days = strtotime($end) - strtotime($template['begin']);
            return intval($days/(60*60*24));
        }
    }
}