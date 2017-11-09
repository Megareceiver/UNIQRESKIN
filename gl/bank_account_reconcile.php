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
/* Author Rob Mallon */
$page_security = 'SA_RECONCILE';
$path_to_root = "..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(800, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();

add_js_file('js/reconcile.js');

page(_($help_context = "Reconcile Bank Account"), false, false, "", $js);
$control_ci = module_control_load('reconcile','bank');

check_db_has_bank_accounts(_("There are no bank accounts defined in the system."));

function check_date() {
	if (!is_date(get_post('reconcile_date'))) {
		display_error(_("Invalid reconcile date format"));
		set_focus('reconcile_date');
		return false;
	}
	return true;
}
//
//	This function can be used directly in table pager
//	if we would like to change page layout.
//
function rec_checkbox($row)
{
	$name = "rec_" .$row['id'];
	$hidden = 'last['.$row['id'].']';
	$value = $row['reconciled'] != '';

// save also in hidden field for testing during 'Reconcile'
	return checkbox(null, $name, $value, true, _('Reconcile this transaction'))
		. hidden($hidden, $value, false);
}


function trans_view($trans)
{
	return get_trans_view_str($trans["type"], $trans["trans_no"]);
}

// function gl_view($row)
// {
// 	return get_gl_view_str($row["type"], $row["trans_no"]);
// }


function fmt_person($row)
{
	return payment_person_name($row["person_type_id"],$row["person_id"]);
}

$update_pager = false;
function update_data(){
	global $Ajax, $update_pager;

	unset($_POST["beg_balance"]);
	unset($_POST["end_balance"]);
	$Ajax->activate('summary');
	$update_pager = true;
}


// function prt_link($row){
//   	if ( in_array($row['type'],array(ST_CUSTPAYMENT,ST_SUPPAYMENT,ST_BANKPAYMENT,ST_BANKDEPOSIT)) )
// 		return print_document_link($row['trans_no']."-".$row['type'], _("Print Receipt"), true, $row['type'], ICON_PRINT);
// }


$control_ci->form();

end_page();

?>