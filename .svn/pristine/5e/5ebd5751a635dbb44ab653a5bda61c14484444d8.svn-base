<?php


$ajax_divs = array();

function div_start($id = '', $trigger = null, $non_ajax = false, $attributes = "")
{
    global $ajax_divs;
    $attributes = _attributes_str2array($attributes);
    if( !isset($attributes['class']) ){
        $attributes['class'] = NULL;
    }
    $attributes['id'] = $id;

    if ($non_ajax) { // div for non-ajax elements
        array_push($ajax_divs, array( $id, null ));
        $attributes['class'] .= 'js_only';
        $attributes['style'] .= 'display:none';
        echo "<div "._parse_attributes($attributes).">\n";
    } else { // ajax ready div
        if( is_null($ajax_divs) ){
            $ajax_divs[]=array( $id, is_null($trigger) ? $id : $trigger );
        } else {
            array_push($ajax_divs, array( $id, is_null($trigger) ? $id : $trigger ));
        }


        echo "<div "._parse_attributes($attributes).">\n";
        ob_start();
    }
}

function div_end()
{
    global $ajax_divs, $Ajax;

    if (count($ajax_divs)) {
        $div = array_pop($ajax_divs);
        if ($div[1] !== null)
            $Ajax->addUpdate($div[1], $div[0], ob_get_flush());
        echo "</div>";
    }
}