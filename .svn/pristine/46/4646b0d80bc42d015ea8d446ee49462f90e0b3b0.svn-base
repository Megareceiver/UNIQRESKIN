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
$page_security = 'SA_STANDARDCOST';
if (!@$_GET['popup'])
	$path_to_root = "..";
else
	$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/includes/manufacturing.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

$control_ci = module_control_load('manage/cost','products');
if (!@$_GET['popup'])
{
	$js = "";
	if ($use_popup_windows)
		$js .= get_js_open_window(900, 500);
	page(_($help_context = "Inventory Item Cost Update"), false, false, "", $js);
}
global $Ajax;
//--------------------------------------------------------------------------------------

check_db_has_costable_items(_("There are no costable inventory items defined in the system (Purchased or manufactured items)."));

if (isset($_GET['stock_id']))
{
	$_POST['stock_id'] = $_GET['stock_id'];
}

//--------------------------------------------------------------------------------------
if (isset($_POST['UpdateData']))
{

	$old_cost = get_standard_cost($_POST['stock_id']);

   	$new_cost = input_num('material_cost') + input_num('labour_cost')
	     + input_num('overhead_cost');

   	$should_update = true;

	if (!check_num('material_cost') || !check_num('labour_cost') ||
		!check_num('overhead_cost'))
	{
		display_error( _("The entered cost is not numeric."));
		set_focus('material_cost');
   	 	$should_update = false;
	}
	elseif ($old_cost == $new_cost)
	{
   	 	display_error( _("The new cost is the same as the old cost. Cost was not updated."));
   	 	$should_update = false;
	}

   	if ($should_update)
   	{
		$update_no = stock_cost_update($_POST['stock_id'],
		    input_num('material_cost'), input_num('labour_cost'),
		    input_num('overhead_cost'),	$old_cost);

        display_notification(_("Cost has been updated."));

        if ($update_no > 0)
        {
    		display_notification(get_gl_view_str(ST_COSTUPDATE, $update_no, _("View the GL Journal Entries for this Cost Update")));
        }

   	}
}

if (list_updated('stock_id'))
	$Ajax->activate('cost_table');
//-----------------------------------------------------------------------------------------

if (!@$_GET['popup'])
	start_form();



if (!isset($_POST['stock_id']))
	$_POST['stock_id'] = get_global_stock_item();

$control_ci->index();


// submit_center('UpdateData', _("Update"), true, false, 'default');

if (!@$_GET['popup'])
{
	end_form();
	end_page(@$_GET['popup'], false, false);
}
?>
