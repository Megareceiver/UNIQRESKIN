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
$page_security = 'SA_SETUPDISPLAY';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

$control_ci = module_control_load('system/display','setup');
page(_($help_context = "Display Setup"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/admin/db/company_db.inc");

//-------------------------------------------------------------------------------------------------

if (isset($_POST['setprefs']))
{
	if (!is_numeric($_POST['query_size']) || ($_POST['query_size']<1))
	{
		display_error($_POST['query_size']);
		display_error( _("Query size must be integer and greater than zero."));
		set_focus('query_size');
	} else {
// 		$_POST['theme'] = clean_file_name($_POST['theme']);
// 		$chg_theme = user_theme() != $_POST['theme'];
		$chg_lang = $_SESSION['language']->code != $_POST['language'];
		$chg_date_format = user_date_format() != $_POST['date_format'];
		$chg_date_sep = user_date_sep() != $_POST['date_sep'];

		set_user_prefs(get_post(
			array('prices_dec','amount_dec', 'qty_dec', 'rates_dec', 'percent_dec',
			'date_format', 'date_sep', 'tho_sep', 'dec_sep', 'print_profile',
			'theme', 'page_size', 'language', 'startup_tab',
			'show_gl' => 0, 'show_codes'=> 0, 'show_hints' => 0,
			'rep_popup' => 0, 'graphic_links' => 0, 'sticky_doc_date' => 0,
			'query_size' => 10.0)));

		if ($chg_lang)
			$_SESSION['language']->set_language($_POST['language']);
			// refresh main menu

		flush_dir(company_path().'/js_cache');

// 		if ($chg_theme && $allow_demo_mode)
// 			$_SESSION["wa_current_user"]->prefs->theme = $_POST['theme'];
// 		if ($chg_theme || $chg_lang || $chg_date_format || $chg_date_sep)
// 			meta_forward($_SERVER['PHP_SELF']);


		if ($allow_demo_mode)
			display_warning(_("Display settings have been updated. Keep in mind that changed settings are restored on every login in demo mode."));
		else
			display_notification_centered(_("Display settings have been updated."));
	}
}

$control_ci->index();

end_page();

?>