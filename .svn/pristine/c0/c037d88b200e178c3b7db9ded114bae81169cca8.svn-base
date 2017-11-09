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
class general_ledger_app extends application
{
	function general_ledger_app()
	{
		$this->application("GL", _($this->help_context = "&Cash and GL"),true,'fa fa-line-chart');

		$this->add_module(_("Operations"), 'fa fa-pencil');

// 		$this->add_lapp_function(0, _("&Payments"), "gl/gl_bank.php?NewPayment=Yes", 'SA_PAYMENT', MENU_TRANSACTION);
// 		$this->add_lapp_function(0, _("&Deposits"), "gl/gl_bank.php?NewDeposit=Yes", 'SA_DEPOSIT', MENU_TRANSACTION);
		$this->add_lapp_function(0, _("&Payments"), "gl/inquiry/journal_inquiry.php?filtertype=1", 'SA_PAYMENT', MENU_TRANSACTION,'bank');
		$this->add_lapp_function(0, _("&Deposits"), "gl/inquiry/journal_inquiry.php?filtertype=2", 'SA_DEPOSIT', MENU_TRANSACTION,'money');


		$this->add_lapp_function(0, _("Bank Account &Transfers"), "gl/bank_transfer.php?", 'SA_BANKTRANSFER', MENU_TRANSACTION,'exchange');
		$this->add_rapp_function(0, _("&Journal Entry"),
			"gl/gl_journal.php?NewJournal=Yes", 'SA_JOURNALENTRY', MENU_TRANSACTION,'pencil');
		$this->add_rapp_function(0, _("&Budget Entry"),
			"gl/gl_budget.php?", 'SA_BUDGETENTRY', MENU_TRANSACTION,'pencil');
		$this->add_rapp_function(0, _("&Reconcile Bank Account"),
			"gl/bank_account_reconcile.php?", 'SA_RECONCILE', MENU_TRANSACTION,'align-justify');
// 		$this->add_rapp_function(0, _("Revenue / &Costs Accruals"),
// 			"gl/accruals.php?", 'SA_ACCRUALS', MENU_TRANSACTION);

		$this->add_module(_("Inquiry"), 'fa fa-search');

		if( defined('COUNTRY') && COUNTRY==65 ){
		    $this->add_lapp_function(1, _("GST Form 5"),"gst/form-5", 'SA_GLANALYTIC', MENU_INQUIRY,'list-ul');
		} else {
		    $this->add_lapp_function(1, _("GST Form 3"),"index.php/gst/form-3", 'SA_GLANALYTIC', MENU_INQUIRY,'list-ul');
		}

// 		if( defined('COUNTRY') && COUNTRY==60 ){
// 		    $this->add_lapp_function(1, _("Days Rules"),"sales/days_rule.php", 'SA_GLANALYTIC', MENU_INQUIRY);
// 		}

		$this->add_lapp_function(1, _("&Journal"),
			"gl/inquiry/journal_inquiry.php?", 'SA_GLANALYTIC', MENU_INQUIRY,'list-ul');
		$this->add_lapp_function(1, _("GL"),
			"gl/inquiry/gl_account_inquiry.php?", 'SA_GLTRANSVIEW', MENU_INQUIRY,'list-ul');

// 		$this->add_lapp_function(1, _("GL Diagnostic Check"),
// 		    "index.php/gl/inquiry/account", 'SA_GLTRANSVIEW', MENU_INQUIRY,'list-ul');

		$this->add_lapp_function(1, _("Bank Account"),
			"gl/inquiry/bank_inquiry.php?", 'SA_BANKTRANSVIEW', MENU_INQUIRY,'list-ul');

// 		$this->add_lapp_function(1, _("Bank Diagnostic Check"),
// 		    "index.php/bank/inquiry/statement?check=1", 'SA_BANKTRANSVIEW', MENU_INQUIRY,'list-ul');


		$this->add_lapp_function(1, _("Ta&x"),
			"gl/inquiry/tax_inquiry.php?", 'SA_TAXREP', MENU_INQUIRY,'list-ul');
		$this->add_rapp_function(1, _("Trial &Balance"),
			"gl/inquiry/gl_trial_balance.php?", 'SA_GLANALYTIC', MENU_INQUIRY,'list-ul');
		$this->add_rapp_function(1, _("Balance &Sheet Drilldown"),
			"gl/inquiry/balance_sheet.php?", 'SA_GLANALYTIC', MENU_INQUIRY,'list-ul');
		$this->add_rapp_function(1, _("&Profit and Loss Drilldown"),
			"gl/inquiry/profit_loss.php?", 'SA_GLANALYTIC', MENU_INQUIRY,'list-ul');

// 		$this->add_rapp_function(1, _("Check Bank Transactions"),
// 		    "index.php/bank/inquiry/check", 'SA_SALESALLOC', MENU_INQUIRY,'list-ul');


		$this->add_module(_("Reports"), 'fa fa-file-text-o');

		$this->add_rapp_function(2, _("Bank Statement"),
			"reporting/reports_main.php?Class=5&REP_ID=601", 'SA_BANKREP', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Chart of Accounts"),
			"reporting/reports_main.php?Class=6&REP_ID=701", 'SA_GLREP', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("List of Journal Entries"),
			"reporting/reports_main.php?Class=6&REP_ID=702", 'SA_GLREP', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("GL Account Transactions"),
			"reporting/reports_main.php?Class=6&REP_ID=704", 'SA_GLREP', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Annual Expense Breakdown"),
			"reporting/reports_main.php?Class=6&REP_ID=705", 'SA_GLREP', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Balance Sheet"),
			"reporting/reports_main.php?Class=6&REP_ID=706", 'SA_GLREP', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Profit and Loss Statement"),
			"reporting/reports_main.php?Class=6&REP_ID=707", 'SA_GLREP', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Trial Balance"),
			"reporting/reports_main.php?Class=6&REP_ID=708", 'SA_GLREP', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Tax Report"),
			"reporting/reports_main.php?Class=6&REP_ID=709", 'SA_GLREP', MENU_REPORT,'file-text');
		$this->add_rapp_function(2, _("Audit Trail"), "reporting/reports_main.php?Class=6&REP_ID=710", 'SA_GLREP', MENU_REPORT,'file-text');

// 		$this->add_rapp_function(2, _("GST Grouping Details"), "taxes/check.php", 'SA_GLREP', MENU_REPORT);
		$this->add_rapp_function(2, _("GST Grouping Details"), "index.php/tax/grouping-details", 'SA_GLREP', MENU_REPORT,'file-text');

		$this->add_module(_("Document Printing"), 'fa fa-print');
		$this->add_lapp_function(3, _("Bank Payment Voucher"), "reporting/reports_bank.php?type=".ST_BANKPAYMENT, 'SA_BANKACCOUNT', MENU_MAINTENANCE,'file-pdf-o');
		$this->add_lapp_function(3, _("Bank Deposit Voucher"), "reporting/reports_bank.php?type=".ST_BANKDEPOSIT, 'SA_BANKACCOUNT', MENU_MAINTENANCE,'file-pdf-o');
		$this->add_lapp_function(3, _("Bank Account Transfer Voucher"), "reporting/reports_bank.php?type=".ST_BANKTRANSFER, 'SA_BANKACCOUNT', MENU_MAINTENANCE,'file-pdf-o');
// 		$this->add_lapp_function(3, _("GL Journal Vouchers"), "reporting/reports_bank.php?type=".ST_JOURNAL, 'SA_BANKACCOUNT', MENU_MAINTENANCE,'file-pdf-o');
		$this->add_lapp_function(3, _("Bank Reconcile"), "index.php/bank/report/reconcile", 'SA_BANKACCOUNT', MENU_MAINTENANCE,'file-pdf-o');

		$this->add_module(_("Housekeeping"), 'fa fa-gear');
		$this->add_lapp_function(4, _("Bank &Accounts"),
			"gl/manage/bank_accounts.php?", 'SA_BANKACCOUNT', MENU_MAINTENANCE,'bank');
		$this->add_lapp_function(4, _("&Quick Entries"),
			"gl/manage/gl_quick_entries.php?", 'SA_QUICKENTRY', MENU_MAINTENANCE,'pencil-square-o');
		$this->add_lapp_function(4, _("Account &Tags"),
			"admin/tags.php?type=account", 'SA_GLACCOUNTTAGS', MENU_MAINTENANCE,'tag');
		$this->add_lapp_function(4, "","");
		$this->add_lapp_function(4, _("&Currencies"),
			"gl/manage/currencies.php?", 'SA_CURRENCY', MENU_MAINTENANCE,'dollar');
		$this->add_lapp_function(4, _("&Exchange Rates"),
			"gl/manage/exchange_rates.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE,'exchange');

		$this->add_rapp_function(4, _("&GL Accounts"),
			"gl/manage/gl_accounts.php?", 'SA_GLACCOUNT', MENU_ENTRY,'book');
		$this->add_rapp_function(4, _("GL Account &Groups"),
			"gl/manage/gl_account_types.php?", 'SA_GLACCOUNTGROUP', MENU_MAINTENANCE,'th-large');
		$this->add_rapp_function(4, _("GL Account &Classes"),
			"gl/manage/gl_account_classes.php?", 'SA_GLACCOUNTCLASS', MENU_MAINTENANCE,'columns');
// 		$this->add_rapp_function(4, "","");
		$this->add_rapp_function(4, _("&Revaluation of Currency Accounts"),
			"gl/manage/revaluate_currencies.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE,'rotate-right');






		$this->add_extensions();
	}
}


?>