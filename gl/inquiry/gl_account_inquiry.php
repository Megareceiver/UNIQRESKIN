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
$page_security = 'SA_GLTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");


include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");

$js = '';
set_focus('account');
if ($use_popup_windows)
	$js .= get_js_open_window(800, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();

page(_($help_context = "General Ledger Inquiry"), false, false, '', $js);

$control_ci = module_control_load('inquiry/account','gl');

// $_POST['TransFromDate'] = input_val('from');
// if( !isset($_POST['TransFromDate']) ){
//     $_POST['TransFromDate'] = input_val('TransFromDate');
// }
// // $_POST['TransToDate'] = input_val('to');
// if( !isset($_POST['TransToDate']) ){
//     $_POST['TransToDate'] = input_val('TransToDate');
// }

$control_ci->view();
// gl_inquiry_controls();



//----------------------------------------------------------------------------------------------------

end_page();

?>
