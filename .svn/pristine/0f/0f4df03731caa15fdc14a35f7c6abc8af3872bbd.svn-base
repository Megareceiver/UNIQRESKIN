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

include($path_to_root . "/includes/ui/allocation_cart.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
//include_once($path_to_root . "/purchasing/includes/ui/supp_alloc_ui.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);

add_js_file('js/allocate.js');

page(_($help_context = "Allocate Supplier Payment or Credit Note"), false, false, "", $js);
$conttrol_ci = module_control_load('allocate','purchases');
//--------------------------------------------------------------------------------

function clear_allocations()
{
	if (isset($_SESSION['alloc']))
	{
		unset($_SESSION['alloc']->allocs);
		unset($_SESSION['alloc']);
	}
	//session_register("alloc");
}
//--------------------------------------------------------------------------------


//--------------------------------------------------------------------------------



//--------------------------------------------------------------------------------



//--------------------------------------------------------------------------------


$conttrol_ci->form();


//--------------------------------------------------------------------------------

end_page();

?>