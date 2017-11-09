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
$page_security = 'SA_CRMCATEGORY';
$path_to_root = '..';
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/db/crm_contacts_db.inc");

$control_ci = module_control_load('category','crm');
page(_($help_context = "Contact Categories"));

include($path_to_root . "/includes/ui.inc");

simple_page_mode(true);

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	$input_error = 0;

	if (strlen($_POST['description']) == 0)
	{
		$input_error = 1;
		display_error(_("Category description cannot be empty."));
		set_focus('description');
	}

	if ($input_error != 1)
	{
    	if ($selected_id != -1)
    	{
    		update_crm_category($selected_id, get_post('type'), get_post('subtype'),
    			get_post('name'), get_post('description'));
			$note = _('Selected contact category has been updated');
    	}
    	else
    	{
    		add_crm_category(get_post('type'), get_post('subtype'), get_post('name'),
    			get_post('description'));
			$note = _('New contact category has been added');
    	}

		display_notification($note);
		$Mode = 'RESET';
	}
}

function key_in_crm_contacts($id) // extra function for testing foreign concatenated key. Joe 02.09.2013.
{
	$row = get_crm_category($id);
	$sql = "SELECT COUNT(*) FROM ".TB_PREF."crm_contacts WHERE type='".$row['type']."' AND action='".$row['action']."'";
	$result = db_query($sql, "check relations for crm_contacts failed");
	$contacts = db_fetch($result);
	return $contacts[0];
}

if ($Mode == 'Delete')
{
	$cancel_delete = 0;

	if (key_in_crm_contacts($selected_id))
	{
		$cancel_delete = 1;
		display_error(_("Cannot delete this category because there are contacts related to it."));
	}
	if ($cancel_delete == 0)
	{
		delete_crm_category($selected_id);

		display_notification(_('Category has been deleted'));
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}

//-------------------------------------------------------------------------------------------------
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
