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
$page_security = 'SA_SALESINVOICE';
$path_to_root = "..";
include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 600);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();

page(_($help_context = "Create and Print Recurring Invoices"), false, false, "", $js);
$control_ci = module_control_load('inquiry/recurrent_invoice','sales');

function create_recurrent_invoices($customer_id, $branch_id, $order_no, $tmpl_no, $date, $from, $to)
{
	global $Refs;

	$doc = new Cart(ST_SALESORDER, array($order_no));

	get_customer_details_to_order($doc, $customer_id, $branch_id);

	$doc->trans_type = ST_SALESORDER;
	$doc->trans_no = 0;
	$doc->document_date = $date;

	$doc->due_date = get_invoice_duedate($doc->payment, $doc->document_date);
	$doc->reference = $Refs->get_next($doc->trans_type);
	if ($doc->Comments != "")
		$doc->Comments .= "\n";
	$doc->Comments .= sprintf(_("Recurrent Invoice covers period %s - %s."), $from, add_days($to, -1));

	foreach ($doc->line_items as $line_no=>$item) {
		$line = &$doc->line_items[$line_no];
		$line->price = get_price($line->stock_id, $doc->customer_currency,
			$doc->sales_type, $doc->price_factor, $doc->document_date);
	}
	$cart = $doc;
	$cart->trans_type = ST_SALESINVOICE;
	$cart->reference = $Refs->get_next($cart->trans_type);
	$invno = $cart->write(1);
	if ($invno == -1)
	{
		display_error(_("The entered reference is already in use."));
		display_footer_exit();
	}
	update_last_sent_recurrent_invoice($tmpl_no, $to);
	return $invno;
}

function calculate_from($myrow)
{
	if ($myrow["last_sent"] == '0000-00-00')
		$from = sql2date($myrow["begin"]);
	else
		$from = sql2date($myrow["last_sent"]);
	return $from;
}

$control_ci->view();

end_page();
?>
