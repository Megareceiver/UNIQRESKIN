<?php
function smarty_function_tempFunction($params,$content,$template=null, &$repeat=null){
    $func = ( isset($params['func']) )?$params['func']:null;
    $item = ( isset($params['item']) )?$params['item']:null;

    if( !$func ) return;

    $plugins_functions = $content->registered_plugins['function'];

    if( array_key_exists($func, $plugins_functions) ){
        $plugins = $plugins_functions[$func];

        return call_user_func($plugins[0], $item);
    }

}