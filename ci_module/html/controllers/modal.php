<?php
class HtmlModal  {
    function __construct() {

    }
    function dialog($content=NULL){
        module_view('model_dialog',array('content'=>$content),true, true,$module='html' );
    }
    public function view($data=null){

        module_view('model_bootstrap',$data,true, true,$module='html' );
    }

    public function view_img($data=null){

        module_view('model_bootstrap_img',$data,true, true, $module='html' );
    }

    public function confirm($data=null){

        module_view('model_bootstrap_confirm',$data,true, true, $module='html' );
    }
}