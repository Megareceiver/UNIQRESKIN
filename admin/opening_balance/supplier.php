<?php
$page_security = 'SA_GLSETUP';
$path_to_root="../..";
include($path_to_root . "/includes/session.inc");
include ROOT.'/includes/view.php';
include ROOT.'/admin/controller/opening_sale.php';
global $ci;

$model_supp = $ci->model('supplier',true);
$model_sys = $ci->model('config',true);
if( $ci->input->get('supp') ){
	$supplier_id = $ci->input->get('supp');

	$currency = $model_supp->supplier_detail($supplier_id,'curr_code');
	if( is_string($currency) ){
		$data['curr'] = $currency;
	} else {
		$data['curr'] = '';
	}

	$data['curr_rate'] = $model_sys->exchange_rate_get($data['curr'],$ci->input->get('date'));
	$currency_base = $model_sys->curr_default();
	$data['curr_base'] = $currency_base->curr_abrev;

	echo json_encode($data); die;
}

if( $ci->input->get('curr') ){
	$data['curr'] = '';
	if( is_string($ci->input->get('curr')) ){
		$data['curr'] = $ci->input->get('curr');
	}

	$data['curr_rate'] = $model_sys->exchange_rate_get($data['curr'],$ci->input->get('date'));
	$currency_base = $model_sys->curr_default();
	$data['curr_base'] = $currency_base->curr_abrev;

	echo json_encode($data); die;
}

$form_type = '';
if( $ci->input->get('add') ){
	$form_type = $ci->input->get('add');
}else if ( $ci->input->post('form_type') ) {
	$form_type = $ci->input->post('form_type');
}
// add_js_file('opening.js');
$js = null;
$js .= get_js_date_picker();
// $js.='load_currency();';
$js.='openning.openning_total(); openning.ini();';


$total_area = '';
if( !$form_type ){
	$total_area = '<div class="btnadd" style="top: 5px; width: 410px; background-color: #fff; padding-left: 10px;" >'
			.'<div class="line" ><span>Number of Opening Transactions</span> <input type="text" class="total_trans number" disabled="" > </div>'
			.'<div class="line" ><span>Total Customer Debit in Base</span> <input type="text" class="total_debit number" disabled="" > </div>'
					.'<div class="line" ><span>Total Customer Credit in Base</span> <input type="text" class="total_credit number" disabled="" > </div>'
							.'</div>';
}

page(_("Supplier Opening Balance"), false, false, "", $js,false,null,$total_area);


$action = new opening_sale();

$action->actions();


include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

$view = new view();
// bug('form type='.$form_type);

echo '<div id="items_table">';
if( $form_type=='add-item' || $form_type=='view-item' ){
	start_form();
	$action->additem('purchase');
	//if( $form_type=='add-item' ){
// 		echo '<center><button value="Process" id="submit" name="submit" aspect="default" type="submit" class="ajaxsubmit" ajax="false"><img height="12" src="../../themes/template/images/ok.gif"><span>Save</span></button></center>';
	//}
	submit_center('submit', _("Save"), true, '', 'default');
	hidden('form_ajax','false');
	hidden('form_type',$form_type);
	end_form();
} elseif( $form_type=='add-payment' ){
		start_form();
		$action->add_deposit('purchase');
		echo '<center><button value="Process" id="submit" name="submit" aspect="default" type="submit" class="ajaxsubmit" ajax="false"><img height="12" src="../../themes/template/images/ok.gif"><span>Save</span></button></center>';
		hidden('form_ajax','false');
		hidden('form_type',$form_type);
		end_form();
} else {
	$action->listview('purchase');

// 	submit_center('submit', _("Process Sales Opening Balance"), true, '', 'default', false );
}

hidden('form_type',$form_type);

// echo '<center><button class="ajaxsubmit" type="submit"  ><span>Process</span></button></center>';

echo '</div>';


//-------------------------------------------------------------------------------------------------

end_page();