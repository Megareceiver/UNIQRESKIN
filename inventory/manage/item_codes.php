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
$page_security = 'SA_FORITEMCODE';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/manufacturing.inc");
include_once($path_to_root . "/includes/data_checks.inc");

$control_ci = module_control_load('manage/item_code','products');

page(_($help_context = "Foreign Item Codes"));
check_db_has_purchasable_items(_("There are no inventory items defined in the system."));
simple_page_mode(true);
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
   	elseif (!input_num('quantity'))
   	{
      	$input_error = 1;
      	display_error( _("The price entered was not positive number."));
		set_focus('quantity');
   	}
   	elseif ($_POST['description'] == '')
   	{
      	$input_error = 1;
      	display_error( _("Item code description cannot be empty."));
		set_focus('description');
   	}
	elseif($selected_id == -1)
	{
		$kit = get_item_kit($_POST['item_code']);
    	if (db_num_rows($kit)) {
		  	$input_error = 1;
    	  	display_error( _("This item code is already assigned to stock item or sale kit."));
			set_focus('item_code');
		}
   	}

	if ($input_error == 0)
	{
     	if ($Mode == 'ADD_ITEM')
       	{
			add_item_code($_POST['item_code'], $_POST['stock_id'],
				$_POST['description'], $_POST['category_id'], $_POST['quantity'], 1);

    		display_notification(_("New item code has been added."));
       	} else
       	{
			update_item_code($selected_id, $_POST['item_code'], $_POST['stock_id'],
				$_POST['description'], $_POST['category_id'], $_POST['quantity'], 1);

    	  	display_notification(_("Item code has been updated."));
       	}
		$Mode = 'RESET';
	}
}

//--------------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	delete_item_code($selected_id);

	display_notification(_("Item code has been sucessfully deleted."));
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST);
}

if (list_updated('stock_id'))
	$Ajax->activate('_page_body');

//--------------------------------------------------------------------------------------------------
$control_ci->mode = $Mode;

$control_ci->selected_id = $selected_id;
$control_ci->index();

end_page();

?>
