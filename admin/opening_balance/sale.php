<?php
$page_security = 'SA_GLSETUP';
$path_to_root="../..";
include($path_to_root . "/includes/session.inc");
include ROOT.'/includes/view.php';
include ROOT.'/admin/controller/opening_sale.php';
global $ci;

if( $ci->input->get('branch') ){
	$model_cus = $ci->model('cutomer',true);
	$branches = $model_cus->branch_options($ci->input->get('branch'));

	echo json_encode($branches); die;
}

$js = null;
$js .= get_js_date_picker();

add_js_file('opening.js');

page(_("Customers Opening Balance"), false, false, "", $js);


$action = new opening_sale();

$action->actions();
$form_type = '';
if( $ci->input->get('add') ){
	$form_type = $ci->input->get('add');
}else if ( $ci->input->post('form_type') ) {
	$form_type = $ci->input->post('form_type');
}

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

$view = new view();
// bug('form type='.$form_type);

echo '<div id="items_table">';
if( $form_type=='add-item' ){

	start_form();
	$action->additem();
// 	submit_center('submit', _("Process"), true, '', 'default', false );
	echo '<center><button value="Process" id="submit" name="submit" aspect="default" type="submit" class="ajaxsubmit" ajax="false"><img height="12" src="../../themes/template/images/ok.gif"><span>Save</span></button></center>';
	hidden('form_ajax','false');
	hidden('form_type',$form_type);
	end_form();
} elseif( $form_type=='process' ){
	$action->write_opening();
} else {
	$action->listview();

// 	submit_center('submit', _("Process Sales Opening Balance"), true, '', 'default', false );
}

hidden('form_type',$form_type);

// echo '<center><button class="ajaxsubmit" type="submit"  ><span>Process</span></button></center>';

echo '</div>';


//-------------------------------------------------------------------------------------------------

end_page();