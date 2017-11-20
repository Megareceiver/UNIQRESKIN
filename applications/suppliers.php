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
class suppliers_app extends application
{
	function suppliers_app()
	{
		$this->application("AP", _($this->help_context = "PURCHASES"),true,'');

		$this->add_module(_("Dashboard"),'', "/purchases/dashboard");

		$this->add_module(_("Operations"), '');
		//$this->add_lapp_function(1, _("Purchase Order Entry"),
		//	"purchasing/po_entry_items.php?NewOrder=Yes", 'SA_PURCHASEORDER', MENU_TRANSACTION);
		$this->add_lapp_function(1, _("Purchase Orders"), "purchasing/inquiry/po_search_completed.php?", 'SA_SUPPTRANSVIEW', MENU_INQUIRY,'');
		$this->add_lapp_function(1, _("Direct GRN"), "purchasing/inquiry/supplier_inquiry.php?filtertype=6", 'SA_GRN', MENU_TRANSACTION,'');
// 		$this->add_lapp_function(0, _("Direct Invoice"),"purchasing/po_entry_items.php?NewInvoice=Yes", 'SA_SUPPLIERINVOICE', MENU_TRANSACTION);
		$this->add_lapp_function(1, _("Direct Invoice"), "purchasing/inquiry/supplier_inquiry.php?filtertype=1", 'SA_SUPPLIERINVOICE', MENU_TRANSACTION ,'');

		$this->add_rapp_function(1, _("Supplier Invoices"),"purchasing/inquiry/supplier_inquiry.php?filtertype=2", 'SA_SUPPLIERINVOICE', MENU_TRANSACTION,'');
		$this->add_rapp_function(1, _("Payments to Suppliers"),
			"purchasing/supplier_payment.php?", 'SA_SUPPLIERPAYMNT', MENU_TRANSACTION,'');
// 		$this->add_rapp_function(0, "","");

		$this->add_rapp_function(1, _("Supplier Credit Notes"),
			"purchasing/supplier_credit.php?New=1", 'SA_SUPPLIERCREDIT', MENU_TRANSACTION,'');
		$this->add_rapp_function(1, _("Allocate Supplier Payments or Credit Notes"),
			"purchasing/allocations/supplier_allocation_main.php?", 'SA_SUPPLIERALLOC', MENU_TRANSACTION,'');

		if( config_ci('kastam')){
		    $this->add_lapp_function(1, _("Bad Debt Processing"), "admin/bad_deb.php?type=supplier", 'SA_SUPPLIERALLOC', MENU_TRANSACTION,'');
		}


		$this->add_module(_("Inquiry"), '');
		$this->add_lapp_function(2, _("Supplier Transaction"),
			"purchasing/inquiry/supplier_inquiry.php?", 'SA_SUPPTRANSVIEW', MENU_INQUIRY,'');
		$this->add_lapp_function(2, "","");
		$this->add_lapp_function(2, _("Supplier Allocation"),
			"purchasing/inquiry/supplier_allocation_inquiry.php?", 'SA_SUPPLIERALLOC', MENU_INQUIRY,'');
// 		$this->add_rapp_function(1, _("Supplier and Purchasing Reports"), "reporting/reports_main.php?Class=1", 'SA_SUPPTRANSVIEW', MENU_REPORT);

// 		$this->add_lapp_function(1, _("Check Transactions"),
// 		    "index.php/purchases/inquiry/check", 'SA_SALESALLOC', MENU_INQUIRY,'list-ul');


		$this->add_module(_("Reports"), '');
		$this->add_lapp_function(3, _("Supplier Ledger"),
			"reporting/reports_main.php?Class=1&REP_ID=201", 'SA_CUSTOMER', MENU_ENTRY,'');

// 		$this->add_lapp_function(2, _("Aged Supplier Analyses"),
// 			"reporting/reports_main.php?Class=1&REP_ID=202", 'SA_CUSTOMER', MENU_ENTRY);

		$this->add_lapp_function(3, _("Age Supplier Analysis"),
		    "reporting/reports_main.php?Class=1&REP_ID=202", 'SA_CUSTOMER', MENU_ENTRY,'');

		$this->add_lapp_function(3, _("Payment Report"),
			"reporting/reports_main.php?Class=1&REP_ID=203", 'SA_CUSTOMER', MENU_ENTRY,'');
		$this->add_lapp_function(3, _("Outstanding GRNs Report"),
			"reporting/reports_main.php?Class=1&REP_ID=204", 'SA_CUSTOMER', MENU_ENTRY,'');
		$this->add_lapp_function(3, _("Supplier Detail Listing"),
			"reporting/reports_main.php?Class=1&REP_ID=205", 'SA_CUSTOMER', MENU_ENTRY,'');

		$this->add_module(_("Document Printing"), '');
		$this->add_lapp_function(4, _("Print Purchase Orders"),
			"reporting/reports_main.php?Class=1&REP_ID=209", 'SA_CUSTOMER', MENU_ENTRY,'');
		$this->add_lapp_function(4, _("Print Remittances"),
			"reporting/reports_main.php?Class=1&REP_ID=210", 'SA_CUSTOMER', MENU_ENTRY,'');

		$this->add_module(_("Housekeeping"), '');
		$this->add_lapp_function(5, _("Suppliers Maintenance"),"purchasing/manage/suppliers.php?", 'SA_SUPPLIER', MENU_ENTRY,'');


		$this->add_extensions();
	}
}


?>