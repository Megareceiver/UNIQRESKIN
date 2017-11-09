<?php
if( !function_exists('api_get') ):
function api_get($uri="",$array_return=false){
    $ci = get_instance();
    $data = $ci->api->get_data($uri,$array_return);
    return $data;
}
endif;