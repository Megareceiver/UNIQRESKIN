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
$page_security = 'SA_PRINTERS';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('printer','manage');
page(_($help_context = "Printer Locations"));

include($path_to_root . "/admin/db/printers_db.inc");
include($path_to_root . "/includes/ui.inc");

simple_page_mode(true);
//-------------------------------------------------------------------------------------------
if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	$error = 0;

	if (empty($_POST['name']))
	{
		$error = 1;
		display_error( _("Printer name cannot be empty."));
		set_focus('name');
	}
	elseif (empty($_POST['host']))
	{
		display_notification_centered( _("You have selected printing to server at user IP."));
	}
	elseif (!check_num('tout', 0, 60))
	{
		$error = 1;
		display_error( _("Timeout cannot be less than zero nor longer than 60 (sec)."));
		set_focus('tout');
	}

	if ($error != 1)
	{
		write_printer_def($selected_id, get_post('name'), get_post('descr'),
			get_post('queue'), get_post('host'), input_num('port',0),
			input_num('tout',0));

		display_notification_centered($selected_id==-1?
			_('New printer definition has been created')
			:_('Selected printer definition has been updated'));
 		$Mode = 'RESET';
	}
}

if ($Mode == 'Delete')
{
	// PREVENT DELETES IF DEPENDENT RECORDS IN print_profiles

	if (key_in_foreign_table($selected_id, 'print_profiles', 'printer'))
	{
		display_error(_("Cannot delete this printer definition, because print profile have been created using it."));
	}
	else
	{
		delete_printer($selected_id);
		display_notification(_('Selected printer definition has been deleted'));
	}
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	$selected_id = -1;
	unset($_POST);
}
//-------------------------------------------------------------------------------------------------

$control_ci->selected_id = $selected_id;
$control_ci->mode = $Mode;
$control_ci->index();


// start_form();

// end_form();
// echo '<br>';

//-------------------------------------------------------------------------------------------------

// start_form();

// start_table(TABLESTYLE2);


// end_table(1);

// submit_add_or_update_center($selected_id == -1, '', 'both');

// end_form();

end_page();

?>
