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
$page_security = 'SA_ITEMSSTATVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

if (!@$_GET['popup'])
{
	if (isset($_GET['stock_id'])){
		page(_($help_context = "Inventory Item Status"), true);
	} else {
		page(_($help_context = "Inventory Item Status"));
	}
}



include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/manufacturing.inc");
include_once(ROOT . "/includes/data_checks.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
$control_ci = module_control_load('stock/status','products');
// $bootstrap = get_instance()->bootstrap;


//----------------------------------------------------------------------------------------------------
if (!@$_GET['popup'])
    start_form();

$control_ci->view();


if (!@$_GET['popup'])
{
	end_form();
	end_page(@$_GET['popup'], false, false);
}

?>
