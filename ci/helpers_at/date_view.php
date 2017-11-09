<?php
function date_view_listview($value=""){
    if( ($value) ){
       return sql2date($value);
    }
}

function datetime_view_listview($value=""){
    if( ($value) ){
        $ci = get_instance();
       return date($ci->dateformatPHP." H:i",date_convert_timestamp($value) );
    }
}