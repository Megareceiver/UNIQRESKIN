<?php

function msg_success ($content = NULL, $lable = 'Success')
{
    return bootstrap_alert('success',$content,$lable);
}
function msg_info ($content = NULL, $lable = 'Note')
{
    return bootstrap_alert('info',$content,$lable);
}
function msg_danger($content = NULL, $lable = 'Error')
{
    return bootstrap_alert('danger',$content,$lable);
}

function bootstrap_alert($type='alert',$content = NULL, $lable = 'Success'){
    $html = '<div class="alert alert-'.$type.'">';
    $html .= '<strong>' . $lable . '!</strong> ';
    $html .= $content;
    $html .= '</div>';
    echo $html;
}