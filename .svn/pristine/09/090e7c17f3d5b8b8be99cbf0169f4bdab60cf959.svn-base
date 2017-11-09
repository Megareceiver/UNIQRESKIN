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
$page_security = 'SA_SALESALLOC';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
page(_($help_context = "Customer Allocations"), false, false, "", $js);

$conttrol_ci = module_control_load('allocate','sales');

$conttrol_ci->main();


//--------------------------------------------------------------------------------
// function systype_name($dummy, $type)
// {
// 	global $systypes_array;

// 	return $systypes_array[$type];
// }

function trans_view($trans){
	return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

// function alloc_link($row){
// 	return pager_link(_("Allocate"),
// 		"/sales/allocations/customer_allocate.php?trans_no="
// 			.$row["trans_no"] . "&trans_type=" . $row["type"]. "&debtor_no=" . $row["debtor_no"], ICON_ALLOC);
// }

function amount_left($row){
	return price_format($row["Total"]-$row["alloc"]);
}

function check_settled($row){
	return $row['settled'] == 1;
}



end_page();
?>