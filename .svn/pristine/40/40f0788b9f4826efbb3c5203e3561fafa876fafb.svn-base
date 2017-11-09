<?php
/*
 Retrieve value of POST variable(s).
 For $name passed as array $dflt is not used,
 default values can be passed as values with non-numeric keys instead.
 If some field have user formatted numeric value, pass float default value to
 convert automatically to POSIX.
 */
function get_post($name, $dflt='')
{
    if (is_array($name)) {
        $ret = array();
        foreach($name as $key => $dflt)
            if (!is_numeric($key)) {
                $ret[$key] = is_float($dflt) ? input_num($key, $dflt) : get_post($key, $dflt);
            } else {
                $ret[$dflt] = get_post($dflt, null);
            }
            return $ret;
    } else
        return is_float($dflt) ? input_num($name, $dflt) :
        ((!isset($_POST[$name]) || $_POST[$name] === '') ? $dflt : $_POST[$name]);
}

function has_post(){
    return get_instance()->input->post();
}

function set_post($name,$value=NULL,$update_old_data=true){
    if( !$update_old_data AND isset($_POST[$name])){
        return FALSE;
    }
    $_POST[$name] = $value;
}