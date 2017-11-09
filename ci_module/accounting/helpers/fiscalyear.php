<?php
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

function end_fiscalyear() {
    $myrow = get_current_fiscalyear();
    if( empty($myrow) ){
        display_error( _("Could not get current fiscal year.") );
        return FALSE;
    }
    return sql2date($myrow['end']);
}


function is_date_in_fiscalyear($date, $convert=false)
{
    global $path_to_root;
    include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

    //Chaitanya
    if ($convert)
        $date2 = sql2date($date);
    else
        $date2 = $date;

    if ($_SESSION["wa_current_user"]->can_access('SA_MULTIFISCALYEARS')) // allow all open years for this one
        return is_date_in_fiscalyears($date2, false);

    $myrow = get_current_fiscalyear();
    if( empty($myrow) ){
        return false;
    }
    if ($myrow['closed'] == 1)
        return 0;

    $begin = sql2date($myrow['begin']);
    $end = sql2date($myrow['end']);
    if (date1_greater_date2($begin, $date2) || date1_greater_date2($date2, $end))
    {
        return 0;
    }
    return 1;
}

function begin_fiscalyear() {
    global $path_to_root;
    include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

    $myrow = get_current_fiscalyear();
    return sql2date($myrow['begin']);
}
