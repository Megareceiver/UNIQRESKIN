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
$page_security = 'SA_EXCHANGERATE';
$path_to_root = "../..";
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/banking.inc");

$js = "";
// if ($use_date_picker)
// 	$js .= get_js_date_picker();

$control_ci = module_control_load('exchange_rate','manage');
page(_($help_context = "Exchange Rates"), false, false, "", $js);

simple_page_mode(false);

//---------------------------------------------------------------------------------------------
function check_data($selected_id)
{
	if (!is_date($_POST['date_']))
	{
		display_error( _("The entered date is invalid."));
		set_focus('date_');
		return false;
	}
	if (input_num('BuyRate') <= 0)
	{
		display_error( _("The exchange rate cannot be zero or a negative number."));
		set_focus('BuyRate');
		return false;
	}
	if (!$selected_id && get_date_exchange_rate($_POST['curr_abrev'], $_POST['date_']))
	{
		display_error( _("The exchange rate for the date is already there."));
		set_focus('date_');
		return false;
	}
	return true;
}

//---------------------------------------------------------------------------------------------

function handle_submit()
{
	global $selected_id;

	if (!check_data($selected_id))
		return false;

	if ($selected_id != "")
	{

		update_exchange_rate($_POST['curr_abrev'], $_POST['date_'],
		input_num('BuyRate'), input_num('BuyRate'));
	}
	else
	{

		add_exchange_rate($_POST['curr_abrev'], $_POST['date_'],
		    input_num('BuyRate'), input_num('BuyRate'));
	}

	$selected_id = '';
	clear_data();
}

//---------------------------------------------------------------------------------------------

function handle_delete()
{
	global $selected_id;

	if ($selected_id == "")
		return;
	delete_exchange_rate($selected_id);
	$selected_id = '';
	clear_data();
}

//---------------------------------------------------------------------------------------------
// function edit_link($row)
// {
//   return button('Edit'.$row["id"], _("Edit"), true, ICON_EDIT);
// }

function del_link($row)
{
  return button('Delete'.$row["id"], _("Delete"), true, ICON_DELETE);
}

function display_rates($curr_code)
{

}

//---------------------------------------------------------------------------------------------

// function display_rate_edit()
// {

// }

//---------------------------------------------------------------------------------------------

function clear_data()
{
	unset($_POST['selected_id']);
	unset($_POST['date_']);
	unset($_POST['BuyRate']);
}

//---------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
	handle_submit();

//---------------------------------------------------------------------------------------------

if ($Mode == 'Delete')
	handle_delete();


$control_ci->selected_id =  $selected_id;
$control_ci->index();
//---------------------------------------------------------------------------------------------

// start_form();



// end_form();

end_page();

?>
