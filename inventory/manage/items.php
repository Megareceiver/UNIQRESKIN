<?php

/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

$page_security = 'SA_ITEM';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

// bug($_POST);die;
$control_ci = module_control_load('manage/product','products');


$js = "";
if ($use_popup_windows)
    $js .= get_js_open_window(900, 500);
// if ($use_date_picker)
    // 	$js .= get_js_date_picker();
page(_($help_context = "Items"), @$_REQUEST['popup'], false, "", $js);

$user_comp = user_company();

//------------------------------------------------------------------------------------

if (isset($_GET['stock_id'])){
	$_POST['stock_id'] = $_GET['stock_id'];
}
$stock_id = -1;

if (list_updated('stock_id')) {

	$_POST['NewStockID'] = $stock_id = get_post('stock_id');

	$Ajax->activate('details');
	$Ajax->activate('controls');
// 	clear_data();
}

if (get_post('cancel')) {
	$_POST['NewStockID'] = $stock_id = $_POST['stock_id'] = '';
    clear_data();
	set_focus('stock_id');
	$Ajax->activate('_page_body');
}
if (list_updated('category_id') || list_updated('mb_flag') ) {
	$Ajax->activate('details');
}

$control_ci->check_data_requirement();

function clear_data() {
	unset($_POST['long_description']);
	unset($_POST['description']);
	unset($_POST['sales_gst_type_id']);
	unset($_POST['purchase_gst_type_id']);
	unset($_POST['category_id']);
	unset($_POST['units']);
	unset($_POST['mb_flag']);
	unset($_POST['NewStockID']);
	unset($_POST['dimension_id']);
	unset($_POST['dimension2_id']);
	unset($_POST['no_sale']);
}

//------------------------------------------------------------------------------------

if (isset($_POST['addupdate'])) {
    $control_ci->update();

}

if (get_post('clone')) {
	unset($_POST['stock_id']);
	$stock_id = '';
	unset($_POST['inactive']);
	set_focus('NewStockID');
	$Ajax->activate('_page_body');
}

//------------------------------------------------------------------------------------

function check_usage($stock_id, $dispmsg=true) {
	$msg = item_in_foreign_codes($stock_id);

	if ($msg != '')	{
		if($dispmsg) display_error($msg);
		return false;
	}
	return true;
}

//------------------------------------------------------------------------------------

if (isset($_POST['delete']) && strlen($_POST['delete']) > 1) {

	if (check_usage($_POST['NewStockID'])) {

		$stock_id = $_POST['NewStockID'];
		delete_item($stock_id);
		$filename = company_path().'/images/'.item_img_name($stock_id).".jpg";
		if (file_exists($filename))
			unlink($filename);
		display_notification(_("Selected item has been deleted."));
		$_POST['stock_id'] = '';
		clear_data();
		set_focus('stock_id');
		$control_ci->new_item = true;
		$Ajax->activate('_page_body');
	}
}
// $control_ci->new_item = $new_item;
$control_ci->index();
end_page(@$_REQUEST['popup']);
?>
