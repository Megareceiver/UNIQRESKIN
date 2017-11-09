<?php
$page_security = 'SA_GLSETUP';
$path_to_root="../..";
include($path_to_root . "/includes/session.inc");
include ROOT.'/includes/view.php';
include ROOT.'/admin/controller/opening_sale.php';
global $ci,$Ajax;
$Ajax->activate('_page_body');

$form_type = '';
if( $ci->input->get('add') ){
	$form_type = $ci->input->get('add');
}else if ( $ci->input->post('form_type') ) {
	$form_type = $ci->input->post('form_type');
}

if( $ci->input->get('cus') ){
	$customer_id = $ci->input->get('cus');

	$model_cus = $ci->model('cutomer',true);
	$model_sys = $ci->model('config',true);
	$branches = $model_cus->branch_options($customer_id);
	$json['branches'] = $branches;

	$currency = $model_cus->customer_detail($customer_id,'curr_code');

	if( is_string($currency) ){
		$json['curr'] = $currency;
	} else {
		$json['curr'] = '';
	}

	$json['curr_rate'] = $model_sys->exchange_rate_get($json['curr'],$ci->input->get('date'));
	$currency_base = $model_sys->curr_default();
 	$json['curr_base'] = $currency_base->curr_abrev;

	echo json_encode($json); die;
}

$js = null;
$js .= get_js_date_picker();
$js.='openning.ini(); openning.openning_total();';

add_js_file('opening.js');
$total_area = '';
if( !$form_type ){
	$total_area = '<div class="btnadd" style="top: 5px; width: 410px; background-color: #fff; padding-left: 10px;" >'
		.'<div class="line" ><span>Number of Opening Transactions</span> <input type="text" class="total_trans number" disabled="" > </div>'
		.'<div class="line" ><span>Total Customer Debit in Base</span> <input type="text" class="total_debit number" disabled="" > </div>'
			.'<div class="line" ><span>Total Customer Credit in Base</span> <input type="text" class="total_credit number" disabled="" > </div>'
		.'</div>';
}


page(_("Customers Opening Balance"), false, false, "", $js,false,null,$total_area);


$action = new opening_sale();

// $action->actions();


include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

$view = new view();
// bug('form type='.$form_type);

echo '<div id="items_table">';
if( $form_type=='add-item' || $form_type=='view-item' ){
	start_form();
	$action->additem('sale');
// 	if( $form_type=='add-item' ){
// 		echo '<center><button value="Process" id="submit" name="submit" aspect="default" type="submit" class="ajaxsubmit" ajax="0" ><img height="12" src="../../themes/template/images/ok.gif"><span>Save</span></button></center>';
// 	}
		submit_center('submit', _("Save"), true, '', 'default');

// 	hidden('form_ajax','false');
	hidden('form_type',$form_type);
	end_form();
} elseif( $form_type=='add-deposit' ){
		start_form();
		$action->add_deposit();

// 		hidden('form_ajax','false');
		hidden('form_type',$form_type);
		end_form();
} else if ($form_type=='remove'){
    $action->remove($ci->input->get('id'));
} else {
	$action->listview('sale');

// 	submit_center('submit', _("Process Sales Opening Balance"), true, '', 'default', false );
}

hidden('form_type',$form_type);

// echo '<center><button class="ajaxsubmit" type="submit"  ><span>Process</span></button></center>';

echo '</div>';


//-------------------------------------------------------------------------------------------------

end_page();