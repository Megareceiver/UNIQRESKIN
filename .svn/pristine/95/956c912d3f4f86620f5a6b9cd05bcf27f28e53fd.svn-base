<?php if ( ! defined('BASEPATH') ) exit( 'No direct script access allowed' );

class CI_page {
    var $css = array();
    var $js = array();
    var $theme = 'default';
    function __construct() {
        global $theme;
        $this->theme = $theme;
        $this->add_css("style.css");
//         $this->add_css("bootstrap/css/bootstrap.css");
//         $this->common_value();

    }
    public function add_css($file=NULL){
        if( substr($file,0,2) == '//' ){
            $proxy = '//';
            $file = substr($file,2);
        } else {
            $proxy = NULL;
        }
        $file = str_replace("//", '/', trim($file));
        $this->css[] = $proxy.$file;
    }


}

