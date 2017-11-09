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
$page_security = 'SA_SUPPTRANSVIEW';
$path_to_root="../..";
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

get_instance()->smarty->assign('button_add_new',array('tran_type'=>ST_PURCHORDER,'title'=>'Add New Purchase Order','uri'=>'purchasing/po_entry_items.php?NewOrder=Yes'));

if (!@$_GET['popup'])
{
	$js = "";
	if ($use_popup_windows)
		$js .= get_js_open_window(900, 500);

	page(_($help_context = "Search Purchase Orders"), false, false, "", $js);
}

$inquiry_ci = module_control_load('inquiry/purchase_order','purchases');

//---------------------------------------------------------------------------------------------
function trans_view($trans)
{
	return get_trans_view_str(ST_PURCHORDER, $trans["order_no"]);
}

//---------------------------------------------------------------------------------------------

if (!@$_GET['popup'])
    start_form();

$inquiry_ci->view();

if (!@$_GET['popup'])
{
	end_form();
	end_page();
}
?>
