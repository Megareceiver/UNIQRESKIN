<?php
function show_dialog($div_ajax=NULL,$data){
    if( strlen($div_ajax) > 0 ){
        div_start($div_ajax);
    }
    if( isset($data['fields']) ){
        module_view('bootstrap_dialog/fields',$data,true, true,'html' );
    } elseif ( isset($data['img_src']) ){
        module_view('bootstrap_dialog/img',$data,true, true,'html' );
    } elseif ( isset($data['svg']) ){
        module_view('bootstrap_dialog/svg',$data,true, true,'html' );
    } elseif ( isset($data['content']) ){
        module_view('bootstrap_dialog/content',$data,true, true,'html' );
    }


    if( strlen($div_ajax) > 0 ){
        div_end();
        global $Ajax;
        $Ajax->activate('_dialog_span');
    }

}