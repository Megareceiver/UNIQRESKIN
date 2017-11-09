<?php

$page_security = 'SA_GLSETUP';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");
include ROOT.'/includes/view.php';
include ROOT.'/admin/controller/opening_balance.php';
include ROOT.'/admin/model/opening_balance_model.php';
$model = new opening_balance_model();
$js = null;




// $type = 'inv';
if( isset($_GET['type']) ){
	$type = $_GET['type'];
} else if (isset($_POST['type'])) {
	$type = $_POST['type'];
}

$js = null;

switch ($type){
	case 'inv':
		$html_total = '<div class="total_tool" >'
				.'<div class="form-group"><label>Number of Openning Inventory</label><input type="text" class="total_inventory" value=0  disabled ></div>'
				.'<div class="form-group"><label>Total Inventory O/B VAlue in Base</label><input type="text" class="total_inventory_base" value=0 disabled ></div>'
				.'</div>';
		$js = 'openning.inventory();';
		page(_("Inventory Opening Balance"),false, false,"",$js,false,null,$html_total);

		$fields = array(
			'code'=>array('type'=>'products','title'=>_('Code'),'size'=>30),
			'name'=>array('type'=>'text','title'=>_('Product Name'),'size'=>30,'attr'=>'class="prodname "'),
// 			'description'=>array('title'=>_('Description')),
// 			'qty'=>array('input'=>'number','title'=>_('QTY'),'size'=>4),
			'cost'=>array('type'=>'number','title'=>_('Opening Cost<br>(Base Curr)'),'size'=>7),
			'qty'=>array('type'=>'number','title'=>_('Opening QTY'),'size'=>7),
			'total'=>array('type'=>'text','title'=>_('Valuation </br>(Base curr)'),'size'=>10,'attr'=>'class="number " disabled'),
		);
		$action = new openingbalance_controller($fields);

		if( isset($_POST['submit']) || isset($_POST['AddItem'])){
// 			DIE('call me');

			$action->add_inventory();
		}

		break;

	case 'cus':

		page(_($help_context = "Customer Opening Balance"));

		$fields = array(
			'customer_id'=>array('input'=>'customers','title'=>_('Customer'),'size'=>12),
			'balance'=>array('input'=>'number','title'=>_('Opening Balance'),'size'=>20),

		);
		$action = new openingbalance_controller($fields);

		if( isset($_POST['submit']) ){
			$action->add_customer();
		}

		break;
	default:
		$action = new controller();
		break;
}



$fields['type'] = array('input'=>'hidden','value'=>$type);

$action->actions();

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

$view = new view($fields);

start_form();
echo '<div id="items_table">';
switch ($type){
	case 'inv':
		$action->listview(); break;

	case 'cus':
		$action->listview_customer(); break;
	default:

		break;
}
hidden('type',$type);
//submit_center('submit', _("Process"), true, '', 'default');
echo '<center><button value="Process" id="submit" name="submit" aspect="default" type="submit" class="ajaxsubmit" ajax="false"><img height="12" src="../../themes/template/images/ok.gif"><span>Update</span></button></center>';
echo '</div>';
end_form();

//-------------------------------------------------------------------------------------------------

end_page();

?>
