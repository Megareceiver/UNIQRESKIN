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
$page_security = 'SA_SALESMAN';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");

$control_ci = module_control_load('manager/person','sales');
page(_($help_context = "Sales Persons"));

simple_page_mode(true);
//------------------------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

	if (strlen($_POST['salesman_name']) == 0)
	{
		$input_error = 1;
		display_error(_("The sales person name cannot be empty."));
		set_focus('salesman_name');
	}
	$pr1 = check_num('provision', 0,100);
	if (!$pr1 || !check_num('provision2', 0, 100)) {
		$input_error = 1;
		display_error( _("Salesman provision cannot be less than 0 or more than 100%."));
		set_focus(!$pr1 ? 'provision' : 'provision2');
	}
	if (!check_num('break_pt', 0)) {
		$input_error = 1;
		display_error( _("Salesman provision breakpoint must be numeric and not less than 0."));
		set_focus('break_pt');
	}
	if ($input_error != 1)
	{
    	if ($selected_id != -1)
    	{
    		/*selected_id could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
			update_salesman($selected_id, $_POST['salesman_name'], $_POST['salesman_phone'], $_POST['salesman_fax'],
				$_POST['salesman_email'], input_num('provision'), input_num('break_pt'), input_num('provision2'));
    	}
    	else
    	{
    		/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new Sales-person form */
			add_salesman($_POST['salesman_name'], $_POST['salesman_phone'], $_POST['salesman_fax'],
				$_POST['salesman_email'], input_num('provision'), input_num('break_pt'), input_num('provision2'));
    	}

    	if ($selected_id != -1)
			display_notification(_('Selected sales person data have been updated'));
		else
			display_notification(_('New sales person data have been added'));
		$Mode = 'RESET';
	}
}
if ($Mode == 'Delete')
{
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'debtors_master'

	if (key_in_foreign_table($selected_id, 'cust_branch', 'salesman'))
	{
		display_error(_("Cannot delete this sales-person because branches are set up referring to this sales-person - first alter the branches concerned."));
	}
	else
	{
		delete_salesman($selected_id);
		display_notification(_('Selected sales person data have been deleted'));
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
//------------------------------------------------------------------------------------------------

$control_ci->id = $selected_id;
$control_ci->mode = $Mode;
$control_ci->index();



end_page();

?>
