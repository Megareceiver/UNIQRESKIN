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
$page_security = 'SA_WORKORDERENTRY';
$path_to_root = "..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/manufacturing.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();
page(_($help_context = "Work Order Entry"), false, false, "", $js);
$conttrol_ci = module_control_load('WorkOrder/entry','manufacturing');


function safe_exit()
{
	global $path_to_root;

	hyperlink_no_params("", _("Enter a new work order"));
	hyperlink_no_params("search_work_orders.php", _("Select an existing work order"));
	
	display_footer_exit();
}

//-------------------------------------------------------------------------------------


function can_process()
{
	global $selected_id, $SysPrefs, $Refs;

	if (!isset($selected_id))
	{
    	if (!$Refs->is_valid($_POST['wo_ref']))
    	{
    		display_error(_("You must enter a reference."));
			set_focus('wo_ref');
    		return false;
    	}

    	if (!is_new_reference($_POST['wo_ref'], ST_WORKORDER))
    	{
    		display_error(_("The entered reference is already in use."));
			set_focus('wo_ref');
    		return false;
    	}
	}

	if (!check_num('quantity', 0))
	{
		display_error( _("The quantity entered is invalid or less than zero."));
		set_focus('quantity');
		return false;
	}

	if (!is_date($_POST['date_']))
	{
		display_error( _("The date entered is in an invalid format."));
		set_focus('date_');
		return false;
	}
	elseif (!is_date_in_fiscalyear($_POST['date_']))
	{
		display_error(_("The entered date is not in fiscal year."));
		set_focus('date_');
		return false;
	}
	// only check bom and quantites if quick assembly
	if (!($_POST['type'] == WO_ADVANCED))
	{
        if (!has_bom($_POST['stock_id']))
        {
        	display_error(_("The selected item to manufacture does not have a bom."));
			set_focus('stock_id');
        	return false;
        }

		if ($_POST['Labour'] == "")
			$_POST['Labour'] = price_format(0);
    	if (!check_num('Labour', 0))
    	{
    		display_error( _("The labour cost entered is invalid or less than zero."));
			set_focus('Labour');
    		return false;
    	}
		if ($_POST['Costs'] == "")
			$_POST['Costs'] = price_format(0);
    	if (!check_num('Costs', 0))
    	{
    		display_error( _("The cost entered is invalid or less than zero."));
			set_focus('Costs');
    		return false;
    	}

        if (!$SysPrefs->allow_negative_stock())
        {
        	if ($_POST['type'] == WO_ASSEMBLY)
        	{
        		// check bom if assembling
                $result = get_bom($_POST['stock_id']);

            	while ($bom_item = db_fetch($result))
            	{

            		if (has_stock_holding($bom_item["ResourceType"]))
            		{

                		$quantity = $bom_item["quantity"] * input_num('quantity');

                        if (check_negative_stock($bom_item["component"], -$quantity, $bom_item["loc_code"], $_POST['date_']))
                		{
                			display_error(_("The work order cannot be processed because there is an insufficient quantity for component:") .
                				" " . $bom_item["component"] . " - " .  $bom_item["description"] . ".  " . _("Location:") . " " . $bom_item["location_name"]);
							set_focus('quantity');
        					return false;
                		}
            		}
            	}
        	}
        	elseif ($_POST['type'] == WO_UNASSEMBLY)
        	{
        		// if unassembling, check item to unassemble
                if (check_negative_stock($_POST['stock_id'], -input_num('quantity'), $_POST['StockLocation'], $_POST['date_']))
        		{
        			display_error(_("The selected item cannot be unassembled because there is insufficient stock."));
					return false;
        		}
        	}
    	}
     }
     else
     {
    	if (!is_date($_POST['RequDate']))
    	{
			set_focus('RequDate');
    		display_error( _("The date entered is in an invalid format."));
    		return false;
		}
		//elseif (!is_date_in_fiscalyear($_POST['RequDate']))
		//{
		//	display_error(_("The entered date is not in fiscal year."));
		//	return false;
		//}
    	if (isset($selected_id))
    	{
    		$myrow = get_work_order($selected_id, true);

    		if ($_POST['units_issued'] > input_num('quantity'))
    		{
				set_focus('quantity');
    			display_error(_("The quantity cannot be changed to be less than the quantity already manufactured for this order."));
        		return false;
    		}
    	}
	}

	return true;
}

//-------------------------------------------------------------------------------------
$conttrol_ci->index();

end_page();

?>