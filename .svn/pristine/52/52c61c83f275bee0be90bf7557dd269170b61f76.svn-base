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
$page_security = 'SA_USERS';
$path_to_root = "..";
include_once($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('user','admin');
page(_($help_context = "Users"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/admin/db/users_db.inc");

simple_page_mode(true);
//-------------------------------------------------------------------------------------------------

function can_process()
{

	if (strlen($_POST['user_id']) < 4)
	{
		display_error( _("The user login entered must be at least 4 characters long."));
		set_focus('user_id');
		return false;
	}

	if ($_POST['password'] != "")
	{
    	if (strlen($_POST['password']) < 4)
    	{
    		display_error( _("The password entered must be at least 4 characters long."));
			set_focus('password');
    		return false;
    	}

    	if (strstr($_POST['password'], $_POST['user_id']) != false)
    	{
    		display_error( _("The password cannot contain the user login."));
			set_focus('password');
    		return false;
    	}
	}

	return true;
}

//-------------------------------------------------------------------------------------------------

if (($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') && check_csrf_token())
{

	if (can_process())
	{
    	if ($selected_id != -1)
    	{
    		update_user_prefs($selected_id,
    			get_post(array('user_id', 'real_name', 'phone', 'email', 'role_id', 'language',
					'print_profile', 'rep_popup' => 0, 'pos','imei')));

    		if ($_POST['password'] != "")
    			update_user_password($selected_id, $_POST['user_id'], md5($_POST['password']));

    		display_notification_centered(_("The selected user has been updated."));
    	}
    	else
    	{
    		add_user($_POST['user_id'], $_POST['real_name'], md5($_POST['password']),
				$_POST['phone'], $_POST['email'], $_POST['role_id'], $_POST['language'],
				$_POST['print_profile'], check_value('rep_popup'), $_POST['pos'], $_POST['imei']);
			$id = db_insert_id();
			// use current user display preferences as start point for new user
			$prefs = $_SESSION['wa_current_user']->prefs->get_all();

			update_user_prefs($id, array_merge($prefs, get_post(array('print_profile',
				'rep_popup' => 0, 'language'))));

			display_notification_centered(_("A new user has been added."));
    	}
		$Mode = 'RESET';
	}
}

//-------------------------------------------------------------------------------------------------

if ($Mode == 'Delete' && check_csrf_token())
{
	$cancel_delete = 0;
    if (key_in_foreign_table($selected_id, 'audit_trail', 'user'))
    {
        $cancel_delete = 1;
        display_error(_("Cannot delete this user because entries are associated with this user."));
    }
    if ($cancel_delete == 0)
    {
    	delete_user($selected_id);
    	display_notification_centered(_("User has been deleted."));
    } //end if Delete group
    $Mode = 'RESET';
}

//-------------------------------------------------------------------------------------------------
if ($Mode == 'RESET')
{
 	$selected_id = -1;
	$sav = get_post('show_inactive', null);
	unset($_POST);	// clean all input fields
	$_POST['show_inactive'] = $sav;
}

$control_ci->selected_id = $selected_id;
$control_ci->mode = $Mode;
$control_ci->index();


// start_form();

//-------------------------------------------------------------------------------------------------
// start_table(TABLESTYLE2);

// end_table(1);

// submit_add_or_update_center($selected_id == -1, '', 'both');

// end_form();
end_page();
?>
