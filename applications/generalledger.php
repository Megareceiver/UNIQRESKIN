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
		$this->application("GL", _($this->help_context = "CASH AND GL"),true,'');
		$this->add_module(_("Dashboard"), "", "gl/dashboard");
		$this->add_module(_("Operations"), '');

// 		$this->add_lapp_function(0, _("Payments"), "gl/gl_bank.php?NewPayment=Yes", 'SA_PAYMENT', MENU_TRANSACTION);
// 		$this->add_lapp_function(0, _("Deposits"), "gl/gl_bank.php?NewDeposit=Yes", 'SA_DEPOSIT', MENU_TRANSACTION);
		$this->add_lapp_function(1, _("Payments"), "gl/inquiry/journal_inquiry.php?filtertype=1", 'SA_PAYMENT', MENU_TRANSACTION,'');
		$this->add_lapp_function(1, _("Deposits"), "gl/inquiry/journal_inquiry.php?filtertype=2", 'SA_DEPOSIT', MENU_TRANSACTION,'');


		$this->add_lapp_function(1, _("Bank Account Transfers"), "gl/bank_transfer.php?", 'SA_BANKTRANSFER', MENU_TRANSACTION,'');
		$this->add_rapp_function(1, _("Journal Entry"),
			"gl/gl_journal.php?NewJournal=Yes", 'SA_JOURNALENTRY', MENU_TRANSACTION,'');
		$this->add_rapp_function(1, _("Budget Entry"),
			"gl/gl_budget.php?", 'SA_BUDGETENTRY', MENU_TRANSACTION,'');
		$this->add_rapp_function(1, _("Reconcile Bank Account"),
			"gl/bank_account_reconcile.php?", 'SA_RECONCILE', MENU_TRANSACTION,'');
// 		$this->add_rapp_function(0, _("Revenue / Costs Accruals"),
// 			"gl/accruals.php?", 'SA_ACCRUALS', MENU_TRANSACTION);

		$this->add_module(_("Inquiry"), '');

		if( defined('COUNTRY') && COUNTRY==65 ){
		    $this->add_lapp_function(2, _("GST Form 5"),"gst/form-5", 'SA_GLANALYTIC', MENU_INQUIRY,'');
		} else {
		    $this->add_lapp_function(2, _("GST Form 3"),"index.php/gst/form-3", 'SA_GLANALYTIC', MENU_INQUIRY,'');
		}

// 		if( defined('COUNTRY') && COUNTRY==60 ){
// 		    $this->add_lapp_function(1, _("Days Rules"),"sales/days_rule.php", 'SA_GLANALYTIC', MENU_INQUIRY);
// 		}

		$this->add_lapp_function(2, _("Journal"),
			"gl/inquiry/journal_inquiry.php?", 'SA_GLANALYTIC', MENU_INQUIRY,'');
		$this->add_lapp_function(2, _("GL"),
			"gl/inquiry/gl_account_inquiry.php?", 'SA_GLTRANSVIEW', MENU_INQUIRY,'');

// 		$this->add_lapp_function(1, _("GL Diagnostic Check"),
// 		    "index.php/gl/inquiry/account", 'SA_GLTRANSVIEW', MENU_INQUIRY,'list-ul');

		$this->add_lapp_function(2, _("Bank Account"),
			"gl/inquiry/bank_inquiry.php?", 'SA_BANKTRANSVIEW', MENU_INQUIRY,'');

// 		$this->add_lapp_function(1, _("Bank Diagnostic Check"),
// 		    "index.php/bank/inquiry/statement?check=1", 'SA_BANKTRANSVIEW', MENU_INQUIRY,'list-ul');


		$this->add_lapp_function(2, _("Tax"),
			"gl/inquiry/tax_inquiry.php?", 'SA_TAXREP', MENU_INQUIRY,'');
		$this->add_rapp_function(2, _("Trial Balance"),
			"gl/inquiry/gl_trial_balance.php?", 'SA_GLANALYTIC', MENU_INQUIRY,'');
		$this->add_rapp_function(2, _("Balance Sheet Drilldown"),
			"gl/inquiry/balance_sheet.php?", 'SA_GLANALYTIC', MENU_INQUIRY,'');
		$this->add_rapp_function(2, _("Profit and Loss Drilldown"),
			"gl/inquiry/profit_loss.php?", 'SA_GLANALYTIC', MENU_INQUIRY,'');

// 		$this->add_rapp_function(1, _("Check Bank Transactions"),
// 		    "index.php/bank/inquiry/check", 'SA_SALESALLOC', MENU_INQUIRY,'list-ul');


		$this->add_module(_("Reports"), '');

		$this->add_rapp_function(3, _("Bank Statement"),
			"reporting/reports_main.php?Class=5&REP_ID=601", 'SA_BANKREP', MENU_REPORT,'');
		$this->add_rapp_function(3, _("Chart of Accounts"),
			"reporting/reports_main.php?Class=6&REP_ID=701", 'SA_GLREP', MENU_REPORT,'');
		$this->add_rapp_function(3, _("List of Journal Entries"),
			"reporting/reports_main.php?Class=6&REP_ID=702", 'SA_GLREP', MENU_REPORT,'');
		$this->add_rapp_function(3, _("GL Account Transactions"),
			"reporting/reports_main.php?Class=6&REP_ID=704", 'SA_GLREP', MENU_REPORT,'');
//		$this->add_rapp_function(3, _("Annual Expense Breakdown"),
//			"reporting/reports_main.php?Class=6&REP_ID=705", 'SA_GLREP', MENU_REPORT,'');
		$this->add_rapp_function(3, _("Balance Sheet"),
			"reporting/reports_main.php?Class=6&REP_ID=706", 'SA_GLREP', MENU_REPORT,'');
		$this->add_rapp_function(3, _("Profit and Loss Statement"),
			"reporting/reports_main.php?Class=6&REP_ID=707", 'SA_GLREP', MENU_REPORT,'');
		$this->add_rapp_function(3, _("Trial Balance"),
			"reporting/reports_main.php?Class=6&REP_ID=708", 'SA_GLREP', MENU_REPORT,'');
		$this->add_rapp_function(3, _("Tax Report"),
			"reporting/reports_main.php?Class=6&REP_ID=709", 'SA_GLREP', MENU_REPORT,'');
		$this->add_rapp_function(3, _("Audit Trail"), "reporting/reports_main.php?Class=6&REP_ID=710", 'SA_GLREP', MENU_REPORT,'');

// 		$this->add_rapp_function(2, _("GST Grouping Details"), "taxes/check.php", 'SA_GLREP', MENU_REPORT);
		$this->add_rapp_function(3, _("GST Grouping Details"), "index.php/tax/grouping-details", 'SA_GLREP', MENU_REPORT,'');

		$this->add_module(_("Document Printing"), '');
		$this->add_lapp_function(4, _("Bank Payment Voucher"), "reporting/reports_bank.php?type=".ST_BANKPAYMENT, 'SA_BANKACCOUNT', MENU_MAINTENANCE,'');
		$this->add_lapp_function(4, _("Bank Deposit Voucher"), "reporting/reports_bank.php?type=".ST_BANKDEPOSIT, 'SA_BANKACCOUNT', MENU_MAINTENANCE,'');
		$this->add_lapp_function(4, _("Bank Account Transfer Voucher"), "reporting/reports_bank.php?type=".ST_BANKTRANSFER, 'SA_BANKACCOUNT', MENU_MAINTENANCE,'');
// 		$this->add_lapp_function(3, _("GL Journal Vouchers"), "reporting/reports_bank.php?type=".ST_JOURNAL, 'SA_BANKACCOUNT', MENU_MAINTENANCE,'file-pdf-o');
		$this->add_lapp_function(4, _("Bank Reconcile"), "index.php/bank/report/reconcile", 'SA_BANKACCOUNT', MENU_MAINTENANCE,'');

		$this->add_module(_("Housekeeping"), '');
		$this->add_lapp_function(5, _("Bank Accounts"),
			"gl/manage/bank_accounts.php?", 'SA_BANKACCOUNT', MENU_MAINTENANCE,'');
		$this->add_lapp_function(5, _("Quick Entries"),
			"gl/manage/gl_quick_entries.php?", 'SA_QUICKENTRY', MENU_MAINTENANCE,'');
		$this->add_lapp_function(5, _("Account Tags"),
			"admin/tags.php?type=account", 'SA_GLACCOUNTTAGS', MENU_MAINTENANCE,'');
		$this->add_lapp_function(5, "","");
		$this->add_lapp_function(5, _("Currencies"),
			"gl/manage/currencies.php?", 'SA_CURRENCY', MENU_MAINTENANCE,'');
		$this->add_lapp_function(5, _("Exchange Rates"),
			"gl/manage/exchange_rates.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE,'');

		$this->add_rapp_function(5, _("GL Accounts"),
			"gl/manage/gl_accounts.php?", 'SA_GLACCOUNT', MENU_ENTRY,'');
		$this->add_rapp_function(5, _("GL Account Groups"),
			"gl/manage/gl_account_types.php?", 'SA_GLACCOUNTGROUP', MENU_MAINTENANCE,'');
		$this->add_rapp_function(5, _("GL Account Classes"),
			"gl/manage/gl_account_classes.php?", 'SA_GLACCOUNTCLASS', MENU_MAINTENANCE,'');
// 		$this->add_rapp_function(4, "","");
		$this->add_rapp_function(5, _("Revaluation of Currency Accounts"),
			"gl/manage/revaluate_currencies.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE,'');






		$this->add_extensions();
	}
}


?>