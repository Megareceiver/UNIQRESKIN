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
class inventory_app extends application
{
	function inventory_app()
	{
		$this->application("stock", _($this->help_context = "&Products"),true,'fa fa-cube');

		$this->add_module(_("Operations"), 'fa fa-pencil');
		$this->add_lapp_function(0, _("Inventory Location &Transfers"),
			"inventory/transfers.php?NewTransfer=1", 'SA_LOCATIONTRANSFER', MENU_TRANSACTION,'random');
		$this->add_lapp_function(0, _("Inventory &Adjustments"),
			"inventory/adjustments.php?NewAdjustment=1", 'SA_INVENTORYADJUSTMENT', MENU_TRANSACTION,'pencil-square-o');

		$this->add_module(_("Inquiry"), 'fa fa-search');
		$this->add_lapp_function(1, _("Inventory Item &Movements"),
			"inventory/inquiry/stock_movements.php?", 'SA_ITEMSTRANSVIEW', MENU_INQUIRY,'list-ul');
		$this->add_lapp_function(1, _("Inventory Item &Status"),
			"inventory/inquiry/stock_status.php?", 'SA_ITEMSSTATVIEW', MENU_INQUIRY,'list-ul');

		$this->add_module(_("Reports"), 'fa fa-file-text-o');
		$this->add_rapp_function(2, _("Inventory Valuation Report"),
			"reporting/reports_main.php?Class=2&REP_ID=301", 'SA_ITEMSTRANSVIEW', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Inventory Planning Report"),
			"reporting/reports_main.php?Class=2&REP_ID=302", 'SA_ITEMSTRANSVIEW', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Stock Check Sheets"),
			"reporting/reports_main.php?Class=2&REP_ID=303", 'SA_ITEMSTRANSVIEW', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Inventory Sales Report"),
			"reporting/reports_main.php?Class=2&REP_ID=304", 'SA_ITEMSTRANSVIEW', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("GNL Valuation Report"),
			"reporting/reports_main.php?Class=2&REP_ID=305", 'SA_ITEMSTRANSVIEW', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Inventory Purchasing Report"),
			"reporting/reports_main.php?Class=2&REP_ID=306", 'SA_ITEMSTRANSVIEW', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Inventory Movement Report"),
			"reporting/reports_main.php?Class=2&REP_ID=307", 'SA_ITEMSTRANSVIEW', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Costed Inventory Movement Report"),
			"reporting/reports_main.php?Class=2&REP_ID=308", 'SA_ITEMSTRANSVIEW', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Item Sales Summary Report"),
			"reporting/reports_main.php?Class=2&REP_ID=309", 'SA_ITEMSTRANSVIEW', MENU_REPORT,'file-text');

// 		$this->add_module(_("Document Printing"));

		$this->add_module(_("Housekeeping"), 'fa fa-gear');
		$this->add_lapp_function(3, _("&Items"),
			"inventory/manage/items.php?", 'SA_ITEM', MENU_ENTRY,'list-alt');
		$this->add_lapp_function(3, _("&Foreign Item Codes"),
			"inventory/manage/item_codes.php?", 'SA_FORITEMCODE', MENU_MAINTENANCE,'fonticons');
		$this->add_lapp_function(3, _("Sales &Kits"),
			"inventory/manage/sales_kits.php?", 'SA_SALESKIT', MENU_MAINTENANCE,'clone');
		$this->add_lapp_function(3, _("Item &Categories"),
			"inventory/manage/item_categories.php?", 'SA_ITEMCATEGORY', MENU_MAINTENANCE,'object-group');
		$this->add_lapp_function(3, _("Inventory &Locations"),
			"inventory/manage/locations.php?", 'SA_INVENTORYLOCATION', MENU_MAINTENANCE,'location-arrow');

		$this->add_lapp_function(3, _("Inventory &Movement Types"),
			"inventory/manage/movement_types.php?", 'SA_INVENTORYMOVETYPE', MENU_MAINTENANCE,'hand-pointer-o');
		$this->add_lapp_function(3, _("&Units of Measure"),
			"inventory/manage/item_units.php?", 'SA_UOM', MENU_MAINTENANCE,'tasks');
		$this->add_lapp_function(4, _("&Reorder Levels"),
			"inventory/reorder_level.php?", 'SA_REORDER', MENU_MAINTENANCE,'sort-numeric-desc');


		$this->add_lapp_function(3, _("Sales &Pricing"),
			"inventory/prices.php?", 'SA_SALESPRICE', MENU_MAINTENANCE,'dollar ');
		$this->add_lapp_function(3, _("Purchasing &Pricing"),
			"inventory/purchasing_data.php?", 'SA_PURCHASEPRICING', MENU_MAINTENANCE,'dollar ');
		$this->add_rapp_function(3, _("Standard &Costs"),
			"inventory/cost_update.php?", 'SA_STANDARDCOST', MENU_MAINTENANCE,'money');

		$this->add_extensions();
	}
}


?>