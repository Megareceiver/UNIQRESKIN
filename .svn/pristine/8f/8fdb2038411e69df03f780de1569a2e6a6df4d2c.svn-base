<?php

function pager_link($link_text, $url, $icon = false)
{
    if (user_graphic_links() && $icon)
        $link_text = set_icon($icon, $link_text);

    return anchor($url, $link_text, 'class="button"');
}

function navi_button($name, $value, $enabled = true, $icon = false)
{
    $attributes = array(
        'class'=>'page-link navibutton',
        'type'=>'submit',
        'name'=>$name,
        'id'=>$name,
        'value'=>$value
    );

    if( !$enabled ){
        $attributes['disabled'] = 'disabled';
    }

    return "<button "._parse_attributes($attributes)." >$value</button>";
}

function navi_button_cell($name, $value, $enabled = true, $align = 'left')
{
    label_cell(navi_button($name, $value, $enabled), "align='$align'");
}
