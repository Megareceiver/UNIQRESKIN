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
$page_security = 'SA_SETUPCOMPANY';
$path_to_root = "..";
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/admin/db/company_db.inc");
// $js = get_js_date_picker();
$js = NULL;
$control_ci = module_control_load('company/setup','setup');


page(_($help_context = "Company Setup"),false, false, "", $js);

//-------------------------------------------------------------------------------------------------

if (isset($_POST['update']) && $_POST['update'] != ""){
   $control_ci->update();
// 	$Ajax->activate('_page_body');
// 	$Ajax->redirect('company_preferences.php');
}

//---------------------------------------------------------------------------------------------
if (get_company_pref('bcc_email') === null) {
    // available from 2.3.14, can be not defined on pre-2.4 installations
	set_company_pref('bcc_email', 'setup.company', 'varchar', 100, '');
	refresh_sys_prefs();
}
$control_ci->form();
end_page();

?>
