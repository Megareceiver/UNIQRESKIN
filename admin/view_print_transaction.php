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
$page_security = 'SA_VIEWPRINTTRANSACTION';
$path_to_root = "..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/admin/db/transactions_db.inc");

include_once($path_to_root . "/reporting/includes/reporting.inc");
$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(800, 500);

$control_ci = module_control_load('transactions','maintenance');
page(_($help_context = "View or Print Transactions"), false, false, "", $js);

//----------------------------------------------------------------------------------------
function view_link($trans)
{
	if (!isset($trans['type']))
		$trans['type'] = $_POST['filterType'];
	return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

// function prt_link($row)
// {
// 	if (!isset($row['type']))
// 		$row['type'] = $_POST['filterType'];
//   	if ($row['type'] == ST_PURCHORDER || $row['type'] == ST_SALESORDER || $row['type'] == ST_SALESQUOTE ||
//   		$row['type'] == ST_WORKORDER)
//  		return print_document_link($row['trans_no'], _("Print"), true, $row['type'], ICON_PRINT);
//  	else
// 		return print_document_link($row['trans_no']."-".$row['type'], _("Print"), true, $row['type'], ICON_PRINT);
// }

// function gl_view($row)
// {
// 	if (!isset($row['type']))
// 		$row['type'] = $_POST['filterType'];
// 	return get_gl_view_str($row["type"], $row["trans_no"]);
// }

function date_view($row)
{
	return $row['trans_date'];
}

function ref_view($row)
{
	return $row['ref'];
}

// function viewing_controls()
// {
// 	display_note(_("Only documents can be printed."));

//     start_table(TABLESTYLE_NOBORDER);
// 	start_row();

// 	systypes_list_cells(_("Type:"), 'filterType', null, true);

// 	if (!isset($_POST['FromTransNo']))
// 		$_POST['FromTransNo'] = "1";
// 	if (!isset($_POST['ToTransNo']))
// 		$_POST['ToTransNo'] = "999999";

//     ref_cells(_("from #:"), 'FromTransNo');

//     ref_cells(_("to #:"), 'ToTransNo');

//     submit_cells('ProcessSearch', _("Search"), '', '', 'default');

// 	end_row();
//     end_table(1);

// }

//----------------------------------------------------------------------------------------

// function check_valid_entries()
// {
// 	if (!is_numeric($_POST['FromTransNo']) OR $_POST['FromTransNo'] <= 0)
// 	{
// 		display_error(_("The starting transaction number is expected to be numeric and greater than zero."));
// 		return false;
// 	}

// 	if (!is_numeric($_POST['ToTransNo']) OR $_POST['ToTransNo'] <= 0)
// 	{
// 		display_error(_("The ending transaction number is expected to be numeric and greater than zero."));
// 		return false;
// 	}

// 	return true;
// }



//----------------------------------------------------------------------------------------

if (isset($_POST['ProcessSearch']))
{
	if (!$control_ci->check_valid_entries())
		unset($_POST['ProcessSearch']);
	$Ajax->activate('transactions');
}

//----------------------------------------------------------------------------------------

$control_ci->index();

// start_form(false);
// 	viewing_controls();
// 	handle_search();
// end_form(2);

end_page();

?>
