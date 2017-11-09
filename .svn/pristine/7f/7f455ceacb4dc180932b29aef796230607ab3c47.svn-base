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
class setup_app extends application
{
	function setup_app()
	{
		$this->application("system", _($this->help_context = "Setup"),true,'fa fa-gears');

		$this->add_module(_("Company"),'fa fa-fax');
		$this->add_lapp_function(0, _("Import Data"),"admin/import.php?", 'SA_SETUPCOMPANY', MENU_SETTINGS,'cloud-download');
		$this->add_lapp_function(0, _("&Company Setup"),"admin/company_preferences.php?", 'SA_SETUPCOMPANY', MENU_SETTINGS,'gear');
		$this->add_lapp_function(0, _("&User Accounts Setup"),
			"admin/users.php?", 'SA_USERS', MENU_SETTINGS,'male');
		$this->add_lapp_function(0, _("&Access Setup"),
			"admin/security_roles.php?", 'SA_SECROLES', MENU_SETTINGS,'user-secret');
		$this->add_lapp_function(0, _("&Display Setup"),
			"admin/display_prefs.php?", 'SA_SETUPDISPLAY', MENU_SETTINGS,'desktop');
		$this->add_lapp_function(0, _("&Forms Setup"),
			"admin/forms_setup.php?", 'SA_FORMSETUP', MENU_SETTINGS,'check-square-o');
		$this->add_rapp_function(0, _("&Taxes"),
			"taxes/tax_types.php?", 'SA_TAXRATES', MENU_MAINTENANCE,'share-square-o');
		//$this->add_rapp_function(0, _("Tax &Groups"),
		//	"taxes/tax_groups.php?", 'SA_TAXGROUPS', MENU_MAINTENANCE);
		//$this->add_rapp_function(0, _("Item Ta&x Types"),
		//	"taxes/item_tax_types.php?", 'SA_ITEMTAXTYPE', MENU_MAINTENANCE);
		$this->add_rapp_function(0, _("System and &General GL Setup"),
			"admin/gl_setup.php?", 'SA_GLSETUP', MENU_SETTINGS,'sitemap');
// 		$this->add_rapp_function(0, _("&Fiscal Years"), "admin/fiscalyears.php?", 'SA_FISCALYEARS', MENU_MAINTENANCE);
		$this->add_rapp_function(0, _("&Fiscal Years"), "admin/fiscal-years", 'SA_FISCALYEARS', MENU_MAINTENANCE,'thumb-tack');

// 		$this->add_rapp_function(0, _("&Print Profiles"),
// 			"admin/print_profiles.php?", 'SA_PRINTPROFILE', MENU_MAINTENANCE);
		if( config_ci('mobile_document')){
// 		    $this->add_rapp_function(0, _("MSIC code"), "admin/msic.php", 'SA_PRINTPROFILE', MENU_MAINTENANCE);
		    $this->add_rapp_function(0, _("Expense Type"),"admin/expense-type", 'SA_PRINTPROFILE', MENU_MAINTENANCE,"cloud-upload");
		    $this->add_rapp_function(0, _("Revenue Type"),"admin/revenue-type", 'SA_PRINTPROFILE', MENU_MAINTENANCE,"cloud-download");

		}

		$this->add_module(_("Miscellaneous"), 'fa fa-star');
		$this->add_lapp_function(1, _("Pa&yment Terms"),
			"admin/payment_terms.php?", 'SA_PAYTERMS', MENU_MAINTENANCE,'file-word-o');
		$this->add_lapp_function(1, _("Shi&pping Company"),
			"admin/shipping_companies.php?", 'SA_SHIPPING', MENU_MAINTENANCE,'truck');

		$this->add_rapp_function(1, _("&Points of Sale"), "sales/manage/sales_points.php?", 'SA_POSSETUP', MENU_MAINTENANCE,'money');
		$this->add_rapp_function(1, _("&Printers"), "admin/printers.php?", 'SA_PRINTERS', MENU_MAINTENANCE,'print');
		$this->add_rapp_function(1, _("Contact &Categories"), "admin/crm_categories.php?", 'SA_CRMCATEGORY', MENU_MAINTENANCE,'phone-square');



		$this->add_module(_("Maintenance"), 'fa fa-gear');
		$this->add_lapp_function(2, _("&Void a Transaction"),"admin/void_transaction.php?", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE,'trash');
		$this->add_lapp_function(2, _("View or &Print Transactions"),
			"admin/view_print_transaction.php?", 'SA_VIEWPRINTTRANSACTION', MENU_MAINTENANCE,'search-plus');
		$this->add_lapp_function(2, _("&Attach Documents"),
			"admin/attachments.php?filterType=20", 'SA_ATTACHDOCUMENT', MENU_MAINTENANCE,'chain');
		//$this->add_lapp_function(2, _("Repost Transaction"),"sales/repost.php", 'SA_ATTACHDOCUMENT', MENU_MAINTENANCE);

		$this->add_lapp_function(2, _("Audit Trail"),
				"admin/audit-trail", 'SA_VIEWPRINTTRANSACTION', MENU_MAINTENANCE,'search');
		if( config_ci('kastam')){
		    $this->add_lapp_function(2, _("Fix Posting"),"gl/fix_posting.php", 'SA_ATTACHDOCUMENT', MENU_MAINTENANCE,'edit ');
		    $this->add_lapp_function(2, _("Bad debt"),"admin/bad_deb.php", 'SA_ATTACHDOCUMENT', MENU_MAINTENANCE,'trash-o');
		}

// 		$this->add_rapp_function(2, _("Install/Activate &Extensions"), "admin/inst_module.php?", 'SA_CREATEMODULES', MENU_MAINTENANCE);

		$this->add_module(_("Opening Balances"), 'fa fa-hourglass-half');
		$this->add_lapp_function(3, _("Bank Account"), "maintenance/opening/bank", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE,'bank ');
		$this->add_lapp_function(3, _("System GL Accounts"),"opening/gl", 'SA_VIEWPRINTTRANSACTION', MENU_MAINTENANCE,'briefcase');
		$this->add_lapp_function(3, _("Inventory"),"admin/opening.php?type=inventory", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE,'building-o');
		$this->add_lapp_function(3, _("Customer"),"opening/customer", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE,'group');
		$this->add_lapp_function(3, _("Supplier"),"opening/supplier", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE,'group');
// 		$this->add_lapp_function(3, _("Customer"),"admin/opening_balance_items.php?type=cus", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE);
// 		$this->add_lapp_function(3, _("Supllier"),"admin/opening_balance_items.php?type=sup", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE);
		/*
		$this->add_lapp_function(2, _("System &Diagnostics"),
			"admin/system_diagnostics.php?", 'SA_SOFTWAREUPGRADE', MENU_SYSTEM);

		$this->add_rapp_function(2, _("&Backup and Restore"),
			"admin/backups.php?", 'SA_BACKUP', MENU_SYSTEM);
		$this->add_rapp_function(2, _("Create/Update &Companies"),
			"admin/create_coy.php?", 'SA_CREATECOMPANY', MENU_UPDATE);
		$this->add_rapp_function(2, _("Install/Update &Languages"),
			"admin/inst_lang.php?", 'SA_CREATELANGUAGE', MENU_UPDATE);
		$this->add_rapp_function(2, _("Install/Activate &Extensions"),
			"admin/inst_module.php?", 'SA_CREATEMODULES', MENU_UPDATE);
		$this->add_rapp_function(2, _("Install/Activate &Themes"),
			"admin/inst_theme.php?", 'SA_CREATEMODULES', MENU_UPDATE);
		$this->add_rapp_function(2, _("Install/Activate &Chart of Accounts"),
			"admin/inst_chart.php?", 'SA_CREATEMODULES', MENU_UPDATE);
		$this->add_rapp_function(2, _("Software &Upgrade"),
			"admin/inst_upgrade.php?", 'SA_SOFTWAREUPGRADE', MENU_UPDATE);
*/
		$this->add_extensions();
	}
}


?>