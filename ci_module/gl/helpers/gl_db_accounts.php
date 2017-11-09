<?php
function get_gl_account($code,$return_object=false)
{
    $query = get_instance()->db
        ->select('*')
        ->from('chart_master')
        ->where('account_code',$code)
        ->get();

    if( $return_object ){
        return $query->row();
    }
    $sql = get_instance()->db->last_query();
    $result = db_query($sql, "could not get gl account");
    return db_fetch($result);
}