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
$page_security = 'SA_QUICKENTRY';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/gl/includes/gl_db.inc");
include($path_to_root . "/includes/ui.inc");

$control_ci = module_control_load('manage/quick_entry','gl');
page(_($help_context = "Quick Entries"));

simple_page_mode(true);
simple_page_mode2(true);

function simple_page_mode2($numeric_id = true)
{
	global $Ajax, $Mode2, $selected_id2;

	$default = $numeric_id ? -1 : '';
	$selected_id2 = get_post('selected_id2', $default);
	foreach (array('ADD_ITEM2', 'UPDATE_ITEM2', 'RESET2') as $m) {
		if (isset($_POST[$m])) {
			$Ajax->activate('_page_body');
			if ($m == 'RESET2')
				$selected_id2 = $default;
			$Mode2 = $m; return;
		}
	}
	foreach (array('BEd', 'BDel') as $m) {
		foreach ($_POST as $p => $pvar) {
			if (strpos($p, $m) === 0) {
//				$selected_id2 = strtr(substr($p, strlen($m)), array('%2E'=>'.'));
				unset($_POST['_focus']); // focus on first form entry
				$selected_id2 = quoted_printable_decode(substr($p, strlen($m)));
				$Ajax->activate('_page_body');
				$Mode2 = $m;
				return;
			}
		}
	}
	$Mode2 = '';
}

function submit_add_or_update_center2($add=true, $title=false, $async=false)
{
	echo "<center>";
	if ($add)
		submit('ADD_ITEM2', _("Add new"), true, $title, $async);
	else {
		submit('UPDATE_ITEM2', _("Update"), true, $title, $async);
		submit('RESET2', _("Cancel"), true, $title, $async);
	}
	echo "</center>";
}

//-----------------------------------------------------------------------------------

function can_process()
{

	if (strlen($_POST['description']) == 0)
	{
		display_error( _("The Quick Entry description cannot be empty."));
		set_focus('description');
		return false;
	}
	$bal_type = get_post('bal_type');
	if ($bal_type == 1 && $_POST['type'] != QE_JOURNAL)
	{
		display_error( _("You can only use Balance Based together with Journal Entries."));
		set_focus('base_desc');
		return false;
	}
	if (!$bal_type && strlen($_POST['base_desc']) == 0)
	{
		display_error( _("The base amount description cannot be empty."));
		set_focus('base_desc');
		return false;
	}

	return true;
}

//-----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM')
{

	if (can_process())
	{

		if ($selected_id != -1)
		{
			update_quick_entry($selected_id, $_POST['description'], $_POST['type'],
				 input_num('base_amount'), $_POST['base_desc'], get_post('bal_type', 0));
			display_notification(_('Selected quick entry has been updated'));
		}
		else
		{
			add_quick_entry($_POST['description'], $_POST['type'],
				input_num('base_amount'), $_POST['base_desc'], get_post('bal_type', 0));
			display_notification(_('New quick entry has been added'));
		}
		$Mode = 'RESET';
	}
}

if ($Mode2=='ADD_ITEM2' || $Mode2=='UPDATE_ITEM2')
{
	if ($selected_id2 != -1)
	{
		update_quick_entry_line($selected_id2, $selected_id, $_POST['actn'], $_POST['dest_id'], input_num('amount', 0),
			$_POST['dimension_id'], $_POST['dimension2_id']);
		display_notification(_('Selected quick entry line has been updated'));
	}
	else
	{
		add_quick_entry_line($selected_id, $_POST['actn'], $_POST['dest_id'], input_num('amount', 0),
			$_POST['dimension_id'], $_POST['dimension2_id']);
		display_notification(_('New quick entry line has been added'));
	}
	$Mode2 = 'RESET2';
}

//-----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	if (!has_quick_entry_lines($selected_id))
	{
		delete_quick_entry($selected_id);
		display_notification(_('Selected quick entry has been deleted'));
		$Mode = 'RESET';
	}
	else
	{
		display_error( _("The Quick Entry has Quick Entry Lines. Cannot be deleted."));
		set_focus('description');
	}
}

if (find_submit('Edit') != -1) {
	$Mode2 = 'RESET2';
	set_focus('description');
}
if (find_submit('BEd') != -1 || get_post('ADD_ITEM2')) {
	set_focus('actn');
}

if ($Mode2 == 'BDel')
{
	delete_quick_entry_line($selected_id2);
	display_notification(_('Selected quick entry line has been deleted'));
	$Mode2 = 'RESET2';
}
//-----------------------------------------------------------------------------------
if ($Mode == 'RESET')
{
	$selected_id = -1;
	$_POST['description'] = $_POST['type'] = '';
	$_POST['base_desc']= _('Base Amount');
	$_POST['base_amount'] = price_format(0);
	$_POST['bal_type'] = 0;
}
if ($Mode2 == 'RESET2')
{
	$selected_id2 = -1;
	$_POST['actn'] = $_POST['dest_id'] = $_POST['amount'] =
		$_POST['dimension_id'] = $_POST['dimension2_id'] = '';
}
//-----------------------------------------------------------------------------------

$control_ci->mode = $Mode;
$control_ci->selected_id = $selected_id;
$control_ci->selected_id2 = $selected_id2;
$control_ci->index();



//------------------------------------------------------------------------------------

end_page();

?>