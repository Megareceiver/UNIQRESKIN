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
$page_security = 'SA_DIMENSION';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");
include_once(ROOT . "/includes/date_functions.inc");
include_once(ROOT . "/includes/manufacturing.inc");
include_once(ROOT . "/includes/data_checks.inc");
include_once(ROOT . "/admin/db/tags_db.inc");
include_once(ROOT . "/dimensions/includes/dimensions_db.inc");
include_once(ROOT . "/dimensions/includes/dimensions_ui.inc");

$js = "";
// if ($use_date_picker)
// 	$js .= get_js_date_picker();
page(_($help_context = "Department Entry"), false, false, "", $js);
$control_ci = module_control_load('entry','dimensions');

// function safe_exit()
// {
// 	global $path_to_root;

// 	hyperlink_no_params("", _("Enter a &new department"));
// 	echo "<br>";
// 	hyperlink_no_params($path_to_root . "/dimensions/inquiry/search_dimensions.php", _("&Select an existing department"));

// 	display_footer_exit();
// }

//-------------------------------------------------------------------------------------

function can_process()
{
	global $selected_id, $Refs;

	if ($selected_id == -1)
	{

    	if (!$Refs->is_valid($_POST['ref']))
    	{
    		display_error( _("The department reference must be entered."));
			set_focus('ref');
    		return false;
    	}

    	if (!is_new_reference($_POST['ref'], ST_DIMENSION))
    	{
    		display_error(_("The entered reference is already in use."));
			set_focus('ref');
    		return false;
    	}
	}

	if (strlen($_POST['name']) == 0)
	{
		display_error( _("The department name must be entered."));
		set_focus('name');
		return false;
	}

	if (!is_date($_POST['date_']))
	{
		display_error( _("The date entered is in an invalid format."));
		set_focus('date_');
		return false;
	}

	if (!is_date($_POST['due_date']))
	{
		display_error( _("The required by date entered is in an invalid format."));
		set_focus('due_date');
		return false;
	}

	return true;
}

//-------------------------------------------------------------------------------------
$control_ci->index();
//-------------------------------------------------------------------------------------

end_page();

?>
