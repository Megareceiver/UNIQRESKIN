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
$page_security = 'SA_SALESPRICE';

if (!@$_GET['popup'])
	$path_to_root = "..";
else
	$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

$control_ci = module_control_load('manage/price','products');

if (!@$_GET['popup']){
    page(_($help_context = "Inventory Item Sales prices"));
}


//---------------------------------------------------------------------------------------------------

check_db_has_stock_items(_("There are no items defined in the system."));
check_db_has_sales_types(_("There are no sales types in the system. Please set up sales types befor entering pricing."));

simple_page_mode(true);
// global $Mode, $selected_id;


//---------------------------------------------------------------------------------------------------
$input_error = 0;

if (isset($_GET['stock_id']))
{
	$_POST['stock_id'] = $_GET['stock_id'];
}
if (isset($_GET['Item']))
{
	$_POST['stock_id'] = $_GET['Item'];
}

if (!isset($_POST['curr_abrev']))
{
	$_POST['curr_abrev'] = get_company_currency();
}


set_global_stock_item( input_val('stock_id') );

//----------------------------------------------------------------------------------------------------

if ( $Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	if (!check_num('price', 0))
	{
		$input_error = 1;
		display_error( _("The price entered must be numeric."));
		set_focus('price');
	}
   	elseif ($Mode == 'ADD_ITEM' && get_stock_price_type_currency($_POST['stock_id'], $_POST['sales_type_id'], $_POST['curr_abrev']))
   	{
      	$input_error = 1;
      	display_error( _("The sales pricing for this item, sales type and currency has already been added."));
		set_focus('supplier_id');
	}

	if ($input_error != 1)
	{
	    //$selected_id = $_POST['sales_type_id'];
    	//if ($selected_id != -1 )
    	if ($selected_id > 0 )
		{
			//editing an existing price
			update_item_price($selected_id, $_POST['sales_type_id'],
			$_POST['curr_abrev'], input_num('price'));

			$msg = _("This price #$selected_id has been updated.");
		}
		else
		{

			add_item_price($_POST['stock_id'], $_POST['sales_type_id'],
			    $_POST['curr_abrev'], input_num('price'));

			$msg = _("The new price has been added.");
		}
		display_notification($msg);
		$Mode = 'RESET';
	}

}

//------------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	//the link to delete a selected record was clicked
	delete_item_price($selected_id);
	display_notification(_("The selected price has been deleted."));
	$Mode = 'RESET';
}
// bug("mode = $Mode && selected_id=$selected_id");
if ($Mode == 'RESET')
{
	$selected_id = -1;
}
global $Ajax;
if (list_updated('stock_id')) {
	$Ajax->activate('price_table');
	$Ajax->activate('price_details');
}
if (list_updated('stock_id') || isset($_POST['_curr_abrev_update']) || isset($_POST['_sales_type_id_update'])) {
	// after change of stock, currency or salestype selector
	// display default calculated price for new settings.
	// If we have this price already in db it is overwritten later.
	unset($_POST['price']);
	$Ajax->activate('price_details');
}


//---------------------------------------------------------------------------------------------------


if (!isset($_POST['stock_id']))
    $_POST['stock_id'] = get_global_stock_item();


$control_ci->mode = $Mode;
$control_ci->id = $selected_id;
$control_ci->index();


if (!@$_GET['popup'])
{
	end_page(@$_GET['popup'], false, false);
}
?>
