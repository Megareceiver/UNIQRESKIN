<?php


//------------------------------------------------------------------------------
//    Seek for _POST variable with $prefix.
//    If var is found returns variable name with prefix stripped,
//    and null or -1 otherwise.
//
function find_submit($prefix, $numeric = true)
{
    foreach ($_POST as $postkey => $postval) {
        if (strpos($postkey, $prefix) === 0) {
            $id = substr($postkey, strlen($prefix));
            return $numeric ? (int) $id : $id;
        }
    }
    return $numeric ? - 1 : null;
}

function post_edit($str = NULL)
{
    $inputs = $_POST;
    $input_filter = array();

    $id = 0;
    if (! empty($inputs))
        foreach ($inputs as $key => $value) {
            if (strpos($key, $str) === 0) {
                $id = $value;
                if ($value) {
                    $id = str_replace($str, NULL, $key);
                }
            }
        }
    if( !is_numeric($id) ){
        $id = NULL;
    }
    return $id;
}

if (! function_exists('input_val')) {

    function input_val($name)
    {
        global $ci;
        $val = NULL;
        if ($ci->input->post($name)) {
            $val = $ci->input->post($name);
        } else
            if ($ci->input->get($name)) {
                $val = $ci->input->get($name);
            }
        return $val;
    }
}

if (! function_exists('input_post')) {

    function input_post($name)
    {
        global $ci;
        $val = NULL;
        if ($ci->input->post($name)) {
            $val = $ci->input->post($name);
        }
        return $val;
    }
}

if (! function_exists('input_get')) {

    function input_get($name)
    {
        global $ci;
        $val = NULL;

        if ($ci->input->get($name)) {
            $val = $ci->input->get($name);
        }
        return $val;
    }
}