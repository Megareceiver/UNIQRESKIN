<?php

if( !function_exists("user_company") ){
    function user_company()
    {
        global $def_coy;
        return isset($_SESSION["wa_current_user"]) ? $_SESSION["wa_current_user"]->company : $def_coy;
    }
}

if( !function_exists("user_pos") ){
    function user_pos()
    {
        return $_SESSION["wa_current_user"]->pos;
    }
}

if( !function_exists("user_language") ){
    function user_language()
    {
        return $_SESSION["wa_current_user"]->prefs->language();
    }
}

function price_format($number=0) {
    return number_format2($number, $_SESSION["wa_current_user"]->prefs->price_dec());
}

?>