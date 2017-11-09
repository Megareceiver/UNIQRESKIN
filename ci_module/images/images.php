<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Images {
    function __construct() {
        $this->ci = get_instance();
//         $this->img = load_class('tic', 'thirdparty/tic',null);
        if( !class_exists('TIC') ){
            require_once BASEPATH.'thirdparty/tic/tic.php';
        }
    }

    function index(){


        $img =  TIC::factory()->setPadding(5)->setFontSize(14);

        $text = $this->ci->uri->segment(4);
        if( !$text ){
            $text = 'No Images';
        }
        $img->setText( urldecode($text) );

        if( $this->ci->uri->segment(2) ){
            $img->setBgColor('#'.$this->ci->uri->segment(2));
        }
        if( $this->ci->uri->segment(3) ){
            $img->setFontColor('#'.$this->ci->uri->segment(3));
        }


        $img->create(true);


    }
}