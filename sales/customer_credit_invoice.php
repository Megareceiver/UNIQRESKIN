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
//---------------------------------------------------------------------------
//
//	Entry/Modify Credit Note for selected Sales Invoice
//

$page_security = 'SA_SALESCREDITINV';
$path_to_root = "..";

include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/manufacturing.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($use_popup_windows) {
	$js .= get_js_open_window(900, 500);
}


if (isset($_GET['ModifyCredit'])) {
	$_SESSION['page_title'] = sprintf(_("Modifying Credit Invoice # %d."), $_GET['ModifyCredit']);
	$help_context = "Modifying Credit Invoice";
	processing_start();
} elseif (isset($_GET['InvoiceNumber'])) {
	$_SESSION['page_title'] = _($help_context = "Credit all or part of an Invoice");
	processing_start();
}

page($_SESSION['page_title'], false, false, "", $js);
$conttrol_ci = module_control_load('tran/credit','sales');
//-----------------------------------------------------------------------------


//-----------------------------------------------------------------------------

function can_process()
{
	global $Refs;

	if (!is_date($_POST['CreditDate'])) {
		display_error(_("The entered date is invalid."));;
		set_focus('CreditDate');
		return false;
	} elseif (!is_date_in_fiscalyear($_POST['CreditDate']))	{
		display_error(_("The entered date is not in fiscal year."));
		set_focus('CreditDate');
		return false;
	}

    if ($_SESSION['Items']->trans_no==0) {
		if (!$Refs->is_valid($_POST['ref'])) {
			display_error(_("You must enter a reference."));;
			set_focus('ref');
			return false;
		}

    }
	if (!check_num('ChargeFreightCost', 0)) {
		display_error(_("The entered shipping cost is invalid or less than zero."));;
		set_focus('ChargeFreightCost');
		return false;
	}
	if (!check_quantities()) {
		display_error(_("Selected quantity cannot be less than zero nor more than quantity not credited yet."));
		return false;
	}
	return true;
}

//-----------------------------------------------------------------------------


function check_quantities()
{
	$ok =1;
	foreach ($_SESSION['Items']->line_items as $line_no=>$itm) {
		if ($itm->quantity == $itm->qty_done) {
			continue; // this line was fully credited/removed
		}
		if (isset($_POST['Line'.$line_no])) {
			if (check_num('Line'.$line_no, 0, $itm->quantity)) {
				$_SESSION['Items']->line_items[$line_no]->qty_dispatched =
				  input_num('Line'.$line_no);
			}
			else {
				$ok = 0;
			}
	  	}

		if (isset($_POST['Line'.$line_no.'Desc'])) {
			$line_desc = $_POST['Line'.$line_no.'Desc'];
			if (strlen($line_desc) > 0) {
				$_SESSION['Items']->line_items[$line_no]->item_description = $line_desc;
			}
	  	}
	}
	return $ok;
}

//-----------------------------------------------------------------------------
$conttrol_ci->form();


end_page();

?>
