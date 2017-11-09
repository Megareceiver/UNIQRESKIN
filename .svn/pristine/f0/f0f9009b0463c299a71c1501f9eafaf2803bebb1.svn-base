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
class dimensions_app extends application
{
	function dimensions_app()
	{
		$dim = get_company_pref('use_dimension');
		$this->application("proj", _($this->help_context = "Class"), $dim,'fa fa-random');

		if ($dim > 0)
		{
			$this->add_module(_("Transactions"),'fa fa-files-o');
			$this->add_lapp_function(0, _("Class &Entry"), "dimensions/dimension_entry.php?", 'SA_DIMENSION', MENU_ENTRY,'fa fa-list-ul');
			$this->add_lapp_function(0, _("&Outstanding Class"), "dimensions/inquiry/search_dimensions.php?outstanding_only=1", 'SA_DIMTRANSVIEW', MENU_TRANSACTION,'fa fa-list-ol');

			$this->add_module(_("Inquiries and Reports"),'fa fa-file-text-o');
			$this->add_lapp_function(1, _("Class"), "dimensions/inquiry/search_dimensions.php?", 'SA_DIMTRANSVIEW', MENU_INQUIRY,'fa fa-list-ol');
			$this->add_rapp_function(1, _("Class &Reports"), "reporting/reports_main.php?Class=4&REP_ID=501", 'SA_DIMENSIONREP', MENU_REPORT,'fa fa-file-text-o');

			$this->add_module(_("Maintenance"),'fa fa-gear');
			$this->add_lapp_function(2, _("Class &Tags"), "admin/tags.php?type=dimension", 'SA_DIMTAGS', MENU_MAINTENANCE,'fa fa-list-ol');

			$this->add_extensions();
		}
	}
}

?>