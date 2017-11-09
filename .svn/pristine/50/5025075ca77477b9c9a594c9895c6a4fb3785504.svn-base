<?php

function date_convert_timestamp($date_){
    global $dateseps;
    $dateseps_system = user_date_sep();
    $dateseps_use = $dateseps[$dateseps_system];
    if( $dateseps_use!='-' ){
        $date_ = str_replace(array("/","."," ",$dateseps_use), '-', $date_);
    }
    $date_str = explode('-', $date_);
    if( !is_array($date_str) || count($date_str) < 2 || $date_str[0] < 1 OR $date_str[1] < 1 OR $date_str[2] < 1  ){
        return NULL;
    }

    return strtotime($date_);
}
function is_date($date_){

    if( !$date_ ) return
        false;

    global $ci,$dateseps;

    $date = date_convert_timestamp($date_);

    $check = ($date == strtotime($date_) AND strlen($date) > 0 ) ? true : false;

    return $check;

//     if( $date != strtotime($date_) ) return false;

//     $date = new DateTime(date('d-m-Y',$date));

//     return checkdate($date->format('m'),$date->format('d'),$date->format('Y'));
}

function qdate_format($date=null,$format=NULL){
    if( !$format ){
        global $ci;
        $format = $ci->dateformatPHP;
    }
   // $date = date('d-m-Y',date_convert_timestamp($date));
    return date($format,date_convert_timestamp($date));
}

if ( ! function_exists('sql2date') ) { function sql2date($date_){
    //if( !is_date($date_) ) return $date_;

    global $date_system,$ci;
    //return date($ci->dateformatPHP,strtotime($date_));
    return is_date($date_) ? date($ci->dateformatPHP,date_convert_timestamp($date_)) : null;
}}

if ( ! function_exists('date2sql') ) { function date2sql($date_) {
    //if( !is_date($date_) ) return $date_;

    return is_date($date_) ? date('Y-m-d',date_convert_timestamp($date_)) : null;

} }

function Today(){
    global $ci;
    return date($ci->dateformatPHP);
}

function add_days($date, $days=0) { // accepts negative values as well

    if( !$date )
        return  NULL;

    $date = date('d-m-Y',date_convert_timestamp($date) );
    $date = new DateTime($date);

    if( $days != 0 ){
        $date->modify("+$days day");
    }

    //return date($ci->dateformatPHP,strtotime($date)+$days*360*24);
    global $ci;
    return $date->format($ci->dateformatPHP);
}

function end_month($date=null){
    global $ci;
    if( !$date ) $date = Today();
    $date = date('d-m-Y',date_convert_timestamp($date));
    $date = new DateTime($date);
	$date->modify('last day of this month');
	return $date->format($ci->dateformatPHP);
}

function add_months($date, $months) { // accepts negative values as well
    global $ci;
    if( !$date ) $date = Today();
    $date_timestamp = date_convert_timestamp($date);
    /*
        $day_old = date('d',$date_timestamp);
        $month_old = date('m',$date_timestamp);
        $year_old = date('Y',$date_timestamp);

        $month_new = $month_old + $months;

        return date($ci->dateformatPHP,strtotime("$year_old-$month_new-$day_old"));
    */
    $date = date('d-m-Y',$date_timestamp);
    $date = new DateTime($date);

    $months = intval($months);
    if( $months > 0 ){
        $months = "+ $months";
    }
    $date->modify("$months months");

    return $date->format($ci->dateformatPHP);
//     if( $date->format('m')-$months <= 12 AND $date->format('m') - $months > date('m',$date_timestamp)-$months ){
//         $date->modify('first day of this month');
//         $date->modify("-1 months");
//         $date->modify('last day of this month');

//         return $date->format($ci->dateformatPHP);
//     } else{
// //         $date->modify('last day of this month');
//         return $date->format($ci->dateformatPHP);
//     }

}

function begin_month($date=null){
    global $ci;
    if( !$date )
        $date = Today();
    $date = date('d-m-Y',date_convert_timestamp($date));
    $date = new DateTime($date);
    $date->modify('first day of this month');
    return $date->format($ci->dateformatPHP);
}

function is_date_in_fiscalyears($date, $closed=true){
    global $ci;
	$date = date2sql($date);
	$ci->db->where(array('begin <='=>$date,'end >='=>$date));

	if (!$closed){
	    $ci->db->where('closed',0);
	}
	$result = $ci->db->get('fiscal_year');

	if( !is_object($result) ){
	    display_error( _("could not get fiscal years"));
	    return false;
	}
    return $result->num_rows > 0;
}
// if( !function_exists('get_current_fiscalyear') ){
//     function get_current_fiscalyear(){
//         global $ci;

//         $data = $ci->db->get('fiscal_year',array('id',get_company_pref('f_year')))->row_array();
//         return $data;
//     }
// }

function new_doc_date($date=null) {
    if (isset($date) && $date != '')
        $_SESSION['_default_date'] = $date;

    if (!isset($_SESSION['_default_date']) || !sticky_doc_date())
        $_SESSION['_default_date'] = Today();

    return $_SESSION['_default_date'];
}

if( !function_exists('get_fiscalyear_begin_for_date') ){
    function get_fiscalyear_begin_for_date($date){
        $year = get_instance()->db->where(array('begin <='=>date2sql($date),'end >='=>date2sql($date)))->select('*')->get('fiscal_year')->row();

        if ( is_object($year) && isset($year->begin))
            return sql2date($year->begin);
        else
            return begin_fiscalyear();
    }
}

if( !function_exists('get_current_fiscalyear') ){
    function get_current_fiscalyear(){
        $ci = get_instance();
        $year = get_company_pref('f_year');
        $query = $ci->db->where('id',$year)->get('fiscal_year');
        if( is_object($query) ){
            return $query->row_array();
        } else {
            display_error( _("Could not get current fiscal year.") );
            return FALSE;
        }

    }
}


if( !function_exists('begin_fiscalyear') ){
    function begin_fiscalyear() {
        $myrow = get_current_fiscalyear();
        return sql2date($myrow['begin']);
    }

}




