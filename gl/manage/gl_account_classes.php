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
$page_security = 'SA_GLACCOUNTCLASS';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('manage/classes','gl');
page(_($help_context = "GL Account Classes"));

include($path_to_root . "/gl/includes/gl_db.inc");
include($path_to_root . "/includes/ui.inc");

simple_page_mode(false);
//-----------------------------------------------------------------------------------

function can_process()
{
	global $use_oldstyle_convert;

	if (strlen(trim($_POST['id'])) == 0)
	{
		display_error( _("The account class ID cannot be empty."));
		set_focus('id');
		return false;
	}
	if (strlen(trim($_POST['name'])) == 0)
	{
		display_error( _("The account class name cannot be empty."));
		set_focus('name');
		return false;
	}
	if (isset($use_oldstyle_convert) && $use_oldstyle_convert == 1)
		$_POST['Balance'] = check_value('Balance');
	return true;
}

//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	if (can_process())
	{

    	if ($selected_id != "")
    	{
    		if(update_account_class($selected_id, $_POST['name'], $_POST['ctype']))
				display_notification(_('Selected account class settings has been updated'));
    	}
    	else
    	{
    		if(add_account_class($_POST['id'], $_POST['name'], $_POST['ctype'])) {
				display_notification(_('New account class has been added'));
				$Mode = 'RESET';
			}
    	}
	}
}

//-----------------------------------------------------------------------------------

function can_delete($selected_id)
{
	if ($selected_id == "")
		return false;
	if (key_in_foreign_table($selected_id, 'chart_types', 'class_id'))
	{
		display_error(_("Cannot delete this account class because GL account types have been created referring to it."));
		return false;
	}

	return true;
}


//-----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{

	if (can_delete($selected_id))
	{
		delete_account_class($selected_id);
		display_notification(_('Selected account class has been deleted'));
	}
	$Mode = 'RESET';
}

//-----------------------------------------------------------------------------------
if ($Mode == 'RESET')
{
	$selected_id = "";
	$_POST['id']  = $_POST['name']  = $_POST['ctype'] =  '';
}
//-----------------------------------------------------------------------------------


$control_ci->selected_id = $selected_id;
$control_ci->mode = $Mode;

$control_ci->index();



// start_form();

//-----------------------------------------------------------------------------------

// start_table(TABLESTYLE2);


// end_table(1);

// submit_add_or_update_center($selected_id == "", '', 'both');

// end_form();

//------------------------------------------------------------------------------------

end_page();

?>
