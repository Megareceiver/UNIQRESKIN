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
$page_security = 'SA_BUDGETENTRY';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

add_js_file('budget.js');

page(_($help_context = "Budget Entry"));
$control_ci = module_control_load('budget','gl');

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");


check_db_has_gl_account_groups(_("There are no account groups defined. Please define at least one account group before entering accounts."));

$control_ci->form();
end_page();

?>
