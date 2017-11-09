<?php
$page_security = 'SA_SALESORDER';
$path_to_root = "..";
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
// include_once($path_to_root . "/includes/ui.inc");
global $ci,$Ajax;


add_js_file('opening.js');
$total_area = '';

$form_type = '';
if( $ci->input->get('add') ){
    $form_type = $ci->input->get('add');
}else if ( $ci->input->post('form_type') ) {
    $form_type = $ci->input->post('form_type');
}
$js = null;
// $js .= get_js_date_picker();
$js.='openning.ini(); openning.openning_total();';

$type = $ci->input->get('type');
if( $ci->input->post('type') ){
    $type = $ci->input->post('type');
}

switch ($type){
    case 'sale':        $page = 'Customers Opening Balance'; break;
    case 'purchase':    $page = 'Supplier Opening Balance'; break;
    case 'inventory' :  $page = 'Inventory Opening Balance'; break;
    default :break;
}

// page($page, false, false, "", $js,false,null,null,true);
page( $page, false, false, "", $js,false,null);

$Ajax->activate('_page_body');
$ci->controller_load('opening');
$ci->opening->index();

end_page();
?>