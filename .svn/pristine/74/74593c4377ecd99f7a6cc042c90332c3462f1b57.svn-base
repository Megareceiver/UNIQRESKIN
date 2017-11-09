<?php

/*
 Update main or gl company setup.
 */
function update_company_prefs( $params, $pref = TB_PREF ){
    global $ci;
    $model = $ci->model('common',true);
    foreach($params as $name => $value) {
        $model->update( array('value'=>$value),'sys_prefs',array('name'=>$name));
        // update cached value
        $_SESSION['SysPrefs']->prefs[$name] = $value;
    }
    return true;
}


function get_company_prefs($tbpref = TB_PREF) {
    return get_company_pref(null, $tbpref);
}


/*
 Get company preferences. Returns cached values from global variable SysPrefs
 or retrieved from database if SysPrefs values are not set.
 $prefs can be preference name, array of names, or null for all preferences.

 */
function get_company_pref($prefs = null, $tbpref = null) {

    global $SysPrefs, $db_version, $ci;
    static $cached;
    // retrieve values from db once a request. Some values can't be cached between requests
    // to ensure prefs integrity for all usrs (e.g. gl_close_date).

    if (!$cached || !isset($_SESSION['SysPrefs'])) { // cached preferences

        $_SESSION['SysPrefs'] = new sys_prefs();

        if (!isset($tbpref))
            $tbpref = TB_PREF;

        $result = $ci->db->select('name, value')->get('sys_prefs');

        if(!is_object($result))
            return null;
        if( $result->num_rows >0 ) foreach ($result->result() AS $pref ){
            $_SESSION['SysPrefs']->prefs[$pref->name] = $pref->value;
        }
        $SysPrefs = &$_SESSION['SysPrefs'];
        // update current db status for info in log file
//         $SysPrefs->db_ok = $SysPrefs->prefs['version_id'] == $db_version;
        $SysPrefs->db_ok = true;
        $cached = true;
    }

    $all = $_SESSION['SysPrefs']->prefs;

    if (!$prefs){
        return $all;
    } elseif (is_string($prefs)){
        return @$all[$prefs];
    }

    $ret = array();
    foreach($prefs as $name)
        $ret[$name] = $all[$name];

    return $ret;
}