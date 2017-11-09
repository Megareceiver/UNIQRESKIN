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
$page_security = 'SA_GLACCOUNTGROUP';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('manage/group','gl');
page(_($help_context = "GL Account Groups"));

include($path_to_root . "/gl/includes/gl_db.inc");
include($path_to_root . "/includes/ui.inc");

simple_page_mode(false);
//-----------------------------------------------------------------------------------

function can_process($selected_id)
{
	if (strlen(trim($_POST['id'])) == 0)
	{
	    display_error( _("The account group id cannot be empty."));
	    set_focus('id');
	    return false;
	}
	if (strlen(trim($_POST['name'])) == 0)
	{
		display_error( _("The account group name cannot be empty."));
		set_focus('name');
		return false;
	}
	$type = get_account_type(trim($_POST['id']));
	if ($type && ($type['id'] != $selected_id))
	{
		display_error( _("This account group id is already in use."));
		set_focus('id');
		return false;
	}

	//if (strcmp($_POST['id'], $_POST['parent']) == 0)
	if ($_POST['id'] === $_POST['parent'])
	{
		display_error(_("You cannot set an account group to be a subgroup of itself."));
		return false;
	}

	return true;
}

//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	if (can_process($selected_id))
	{

    	if ($selected_id != "")
    	{
    		if (update_account_type($_POST['id'], $_POST['name'], $_POST['class_id'], $_POST['parent'], $_POST['old_id']))
				display_notification(_('Selected account type has been updated'));
    	}
    	else
    	{
    		if (add_account_type($_POST['id'], $_POST['name'], $_POST['class_id'], $_POST['parent'])) {
				display_notification(_('New account type has been added'));
			}
    	}
		$Mode = 'RESET';
	}
}

//-----------------------------------------------------------------------------------

function can_delete($type)
{
	if ($type == "")
		return false;

	if (key_in_foreign_table($type, 'chart_master', 'account_type'))
	{
		display_error(_("Cannot delete this account group because GL accounts have been created referring to it."));
		return false;
	}

	if (key_in_foreign_table($type, 'chart_types', 'parent'))
	{
		display_error(_("Cannot delete this account group because GL account groups have been created referring to it."));
		return false;
	}

	return true;
}


//-----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{

	if (can_delete($selected_id))
	{
		delete_account_type($selected_id);
		display_notification(_('Selected account group has been deleted'));
	}
	$Mode = 'RESET';
}
if ($Mode == 'RESET')
{
 	$selected_id = "";
	$_POST['id']  = $_POST['name']  = '';
	unset($_POST['parent']);
	unset($_POST['class_id']);
}
//-----------------------------------------------------------------------------------

$control_ci->selected_id = $selected_id;
$control_ci->mode = $Mode;
$control_ci->index();


end_page();

?>
