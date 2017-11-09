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
$page_security = 'SA_GLSETUP';
global $ci;
$path_to_root="..";
include($path_to_root . "/includes/session.inc");
// $js = get_js_date_picker();

page(_($help_context = "System and General GL Setup"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/admin/db/company_db.inc");

$gst_registration_date = array(
    'gst_default_code'=>array('title'=>'Default GST Code','value'=>null),
    'gst_start_date'=>array('title'=>'GST Start Date','value'=>''),
    'maximum_claimable_currency'=>array('value'=>''),
    'maximum_claimable_input_tax'=>array('value'=>''),
    'custom_duty'=>array('value'=>''),


);
$control_ci = module_control_load('system/general_ledger','setup');
// function set_post_value($fields=array(),$row=array()){
//     if( is_array($fields) && is_array($row)){

//     }
// }

//-------------------------------------------------------------------------------------------------

function can_process(){

	if (!check_num('po_over_receive', 0, 100)){
		display_error(_("The delivery over-receive allowance must be between 0 and 100."));
		set_focus('po_over_receive');
		return false;
	}

	if (!check_num('po_over_charge', 0, 100)){
		display_error(_("The invoice over-charge allowance must be between 0 and 100."));
		set_focus('po_over_charge');
		return false;
	}

	if (!check_num('past_due_days', 0, 100)){
		display_error(_("The past due days interval allowance must be between 0 and 100."));
		set_focus('past_due_days');
		return false;
	}

	$grn_act = get_company_pref('grn_clearing_act');

	if ((get_post('grn_clearing_act') != $grn_act) && db_num_rows(get_grn_items(0, '', true))){
		display_error(_("Before GRN Clearing Account can be changed all GRNs have to be invoiced"));
		$_POST['grn_clearing_act'] = $grn_act;
		set_focus('grn_clearing_account');
		return false;
	}

	if (!is_account_balancesheet(get_post('retained_earnings_act')) || is_account_balancesheet(get_post('profit_loss_year_act'))){
		display_error(_("The Retained Earnings Account should be a Balance Account or the Profit and Loss Year Account should be an Expense Account (preferred the last one in the Expense Class)"));
		return false;
	}
	return true;
}

//-------------------------------------------------------------------------------------------------

if (isset($_POST['submit']) && can_process()){
    $submit_fields = array( 'retained_earnings_act', 'profit_loss_year_act',
		'debtors_act', 'pyt_discount_act', 'creditors_act', 'freight_act',
		'exchange_diff_act', 'bank_charge_act', 'default_sales_act', 'default_sales_discount_act',
        'rounding_difference_act',
		'default_prompt_payment_act', 'default_inventory_act', 'default_cogs_act',
		'default_adj_act', 'default_inv_sales_act', 'default_assembly_act', 'legal_text',
//         'cash_sales_invoice',
		'past_due_days', 'default_workorder_required', 'default_dim_required',
		'default_delivery_required', 'grn_clearing_act',
		'baddeb_sale_reverse','baddeb_sale_tax_reverse','baddeb_purchase_reverse','baddeb_purchase_tax_reverse',
		'baddeb_purchase_tax','baddeb_sale_tax',
		'allow_negative_stock'=> 0, 'accumulate_shipping'=> 0,
		'po_over_receive' => 0.0, 'po_over_charge' => 0.0, 'default_credit_limit'=>0.0,
            'purchase_gst_default'=>null,
            'sale_gst_default'=>null
    );
    foreach ($gst_registration_date AS $field=>$val) {
        if( !isset($submit_fields[$field]) ){
            $submit_fields[] = $field;
        }
    }
	update_company_prefs( get_post($submit_fields));
	display_notification(_("The general GL setup has been updated."));
} /* end of if submit */

//-------------------------------------------------------------------------------------------------
$control_ci->index();

end_page();

?>
