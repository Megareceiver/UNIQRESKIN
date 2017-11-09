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
$page_security = 'SA_PURCHASEPRICING';
if (!@$_GET['popup'])
	$path_to_root = "..";
else
	$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/manufacturing.inc");
include_once($path_to_root . "/includes/data_checks.inc");

$control_ci = module_control_load('manage/purchasing_data','products');
if (!@$_GET['popup'])
	page(_($help_context = "Supplier Purchasing Data"));

check_db_has_purchasable_items(_("There are no purchasable inventory items defined in the system."));
check_db_has_suppliers(_("There are no suppliers defined in the system."));

//----------------------------------------------------------------------------------------
simple_page_mode(true);
if (isset($_GET['stock_id']))
{
	$_POST['stock_id'] = $_GET['stock_id'];
}

//--------------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

   	$input_error = 0;
   	if ($_POST['stock_id'] == "" || !isset($_POST['stock_id']))
   	{
      	$input_error = 1;
      	display_error( _("There is no item selected."));
		set_focus('stock_id');
   	}
   	elseif (!check_num('price', 0))
   	{
      	$input_error = 1;
      	display_error( _("The price entered was not numeric."));
	set_focus('price');
   	}
   	elseif (!check_num('conversion_factor'))
   	{
      	$input_error = 1;
      	display_error( _("The conversion factor entered was not numeric. The conversion factor is the number by which the price must be divided by to get the unit price in our unit of measure."));
		set_focus('conversion_factor');
   	}
   	elseif ($Mode == 'ADD_ITEM' && get_item_purchasing_data($_POST['supplier_id'], $_POST['stock_id']))
   	{
      	$input_error = 1;
      	display_error( _("The purchasing data for this supplier has already been added."));
		set_focus('supplier_id');
	}
	if ($input_error == 0)
	{
     	if ($Mode == 'ADD_ITEM')
       	{
			add_item_purchasing_data($_POST['supplier_id'], $_POST['stock_id'], input_num('price',0),
				$_POST['suppliers_uom'], input_num('conversion_factor'), $_POST['supplier_description']);
    		display_notification(_("This supplier purchasing data has been added."));
       	}
       	else
       	{
       		update_item_purchasing_data($selected_id, $_POST['stock_id'], input_num('price',0),
       			$_POST['suppliers_uom'], input_num('conversion_factor'), $_POST['supplier_description']);
    	  	display_notification(_("Supplier purchasing data has been updated."));
       	}
		$Mode = 'RESET';
	}
}

//--------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	delete_item_purchasing_data($selected_id, $_POST['stock_id']);
	display_notification(_("The purchasing data item has been sucessfully deleted."));
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
}
global $Ajax;
if (isset($_POST['_selected_id_update']) )
{
	$selected_id = $_POST['selected_id'];
	$Ajax->activate('_page_body');
}

if (list_updated('stock_id'))
	$Ajax->activate('price_table');
//--------------------------------------------------------------------------------------------------

if (!isset($_POST['stock_id']))
    $_POST['stock_id'] = get_global_stock_item();

if (!@$_GET['popup']){
    start_form();
}

$control_ci->mode = $Mode;
$control_ci->selected_id = $selected_id;
if ( @$_GET['popup'] ){
    $control_ci->popup();
} else {
    $control_ci->index();
}




if (!@$_GET['popup'])
{
	end_form();
	end_page(@$_GET['popup'], false, false);
}
?>
