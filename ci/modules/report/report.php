<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->page_security = 'SA_GLTRANSVIEW';
        $this->ci->page_title = 'Reports and Analysis';
        include_once(ROOT . "/includes/ui.inc");
    }

    var $fields = array();

    function form($title='Report',$buttons=NULL){
        $ci = get_instance();

        if( !$buttons ){
            $buttons = array( 'report_submit'=>"Display: $title");
        }



        page($this->ci->page_title. " | $title");
        start_form();
        $ci->temp_view('form-reports',array('fields'=>$this->fields,'submit'=>$buttons),false,'report');
        end_form();
        end_page();
    }
}