<?php
function smarty_function_text2img($params,$content,$template=null, &$repeat=null){
    $text = ( isset($params['text']) )?$params['text']:'No Image';
    $backcolor = ( isset($params['backcolor']) )?$params['backcolor']:'ffffff';
    $textcolor = ( isset($params['textcolor']) )?$params['textcolor']:'222222';
    $maxHeight = ( isset($params['maxHeight']) )?$params['maxHeight']: 0;
    $alt = ( isset($params['alt']) )?$params['alt']: NULL;

    $file_name = $backcolor.'_'.$textcolor.'_'.url_title($text,'_').'.png';
    $img_dir = ROOT.'/tmp/image-text';
    if( is_dir($img_dir) ){
        check_dir($img_dir);
    }

    if( !file_exists($img_dir."/".$file_name) ){
        if( !class_exists('TIC') ){
            require_once BASEPATH.'thirdparty/tic/tic.php';
        }
        $img =  TIC::factory()->setPadding(5)->setFontSize(14);

        $img->setText( urldecode($text) );

        $img->setBgColor("#$backcolor");
        $img->setFontColor("#$textcolor");
        $img->create($img_dir."/".$file_name);
    }

    return '<img src="' .site_url("/tmp/image-text/$file_name"). '" alt="' . $alt . '" class="img-responsive" style="'.( $maxHeight >0 ? "max-height: ".$maxHeight."px; " : NULL).'" />';

}