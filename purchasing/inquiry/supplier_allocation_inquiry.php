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
$page_security = 'SA_SUPPLIERALLOC';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include($path_to_root . "/includes/session.inc");

include($path_to_root . "/purchasing/includes/purchasing_ui.inc");
$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);

$inquiry_ci = module_control_load('inquiry/allocation','purchases');

page(_($help_context = "Supplier Allocation Inquiry"), false, false, "", $js);



//------------------------------------------------------------------------------------------------



//------------------------------------------------------------------------------------------------
function check_overdue($row)
{
	return ($row['TotalAmount']>$row['Allocated']) &&
		$row['OverDue'] == 1;
}


function view_link($trans)
{
	return get_trans_view_str($trans["type"], $trans["trans_no"]);
}


//------------------------------------------------------------------------------------------------

$inquiry_ci->view();

end_page();
?>
