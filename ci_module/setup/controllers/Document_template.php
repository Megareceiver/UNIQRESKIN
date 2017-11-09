<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Document_template extends CI_Controller {

	function __construct() {
        $ci = get_instance();
    }

    function template_invoice(){
    	redirect('d_template/template_invoice');
    }
}
?>