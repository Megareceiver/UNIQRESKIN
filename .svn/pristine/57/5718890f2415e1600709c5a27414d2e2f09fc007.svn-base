<?php
class HtmlInquiryActionsSmarty{
    function __construct(){

    }

    static function inquiry_addnew_button($template=null, $params=null){
        if ( !empty($params->tpl_vars) AND array_key_exists('button_add_new', $params->tpl_vars)){
            $button_add_new = $params->tpl_vars['button_add_new']->value;

            $icon = '<i class="fa fa-plus"></i>';
            return anchor($button_add_new['uri'],$icon." ".$button_add_new['title'],'class="btn green ajaxsubmit"');
        }
    }

    static function page_button_back(){
        return anchor(get_instance()->url_back,'<i class="fa fa-rotate-left"></i>  Back','class="btn green btn_left" ');
    }
}