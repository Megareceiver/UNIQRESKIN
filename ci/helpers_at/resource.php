<?php

if( !function_exists('add_js_file')):
function add_js_file($filename)
{
    global $js_static;

    $search = array_search($filename, $js_static);

    if ($search === false || $search === null) { // php>4.2.0 returns null
        $js_static[] = str_replace(COMPANY_DIR, COMPANY_ASSETS, $filename);
    }
}
endif; // add_js_file

function add_js($file=NULL){
    if( strlen($file) >0 ){
        global $assets_path;
        add_js_file($assets_path."/$file");
    }

}