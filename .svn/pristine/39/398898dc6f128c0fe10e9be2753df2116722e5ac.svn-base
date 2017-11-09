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
$page_security = 'SA_DIMTRANSVIEW';
$path_to_root="../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(800, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();

if (isset($_GET['outstanding_only']) && $_GET['outstanding_only'])
{
// 	$outstanding_only = 1;
	page(_($help_context = "Search Outstanding Class"), false, false, "", $js);
}
else
{
	page(_($help_context = "Search Class"), false, false, "", $js);
}
$control_ci = module_control_load('inquiry','dimensions');

//-----------------------------------------------------------------------------------
// Ajax updates
//


//--------------------------------------------------------------------------------------


function view_link($row)
{
	return get_dimensions_trans_view_str(ST_DIMENSION, $row["id"]);
}

function sum_dimension($row)
{
	return get_dimension_balance($row['id'], $_POST['FromDate'], $_POST['ToDate']);
}

function is_closed($row)
{
	return $row['closed'] ? _('Yes') : _('No');
}

function is_overdue($row)
{
	return date_diff2(Today(), sql2date($row["due_date"]), "d") > 0;
}

// function edit_link($row)
// {
// 	//return $row["closed"] ?  '' :
// 	//	pager_link(_("Edit"),
// 	//		"/dimensions/dimension_entry.php?trans_no=" . $row["id"], ICON_EDIT);
// 	return pager_link(_("Edit"),
// 			"/dimensions/dimension_entry.php?trans_no=" . $row["id"], ICON_EDIT);
// }


$control_ci->index();
end_page();

?>
