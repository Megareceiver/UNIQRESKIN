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
$path_to_root = "..";

include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/includes/session.inc");
$page_security = isset($_GET['NewPayment']) || @($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT) ? 'SA_PAYMENT' : 'SA_DEPOSIT';

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/gl/includes/ui/gl_bank_ui.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/gl/includes/gl_ui.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
global $Ajax,$ci;

$js = '';
if ( isset($use_popup_windows) )
	$js .= get_js_open_window(800, 500);
// if ( isset($use_date_picker) )
// 	$js .= get_js_date_picker();


if (isset($_GET['NewPayment'])) {
	$_SESSION['page_title'] = _($help_context = "Bank Account Payment Entry");
	create_cart(ST_BANKPAYMENT, 0);
	add_js_ufile( site_url('js/bank_actions.js') );
} else if(isset($_GET['NewDeposit'])) {
	$_SESSION['page_title'] = _($help_context = "Bank Account Deposit Entry");
	create_cart(ST_BANKDEPOSIT, 0);
} else if(isset($_GET['ModifyPayment'])) {
	$_SESSION['page_title'] = _($help_context = "Modify Bank Account Entry")." #".$_GET['trans_no'];
	create_cart(ST_BANKPAYMENT, $_GET['trans_no']);
	$_SESSION['pay_items']->modify = true;
	add_js_ufile( site_url('js/bank_actions.js') );
} else if(isset($_GET['ModifyDeposit'])) {
	$_SESSION['page_title'] = _($help_context = "Modify Bank Deposit Entry")." #".$_GET['trans_no'];
	$_SESSION['pay_items']->modify = true;
	create_cart(ST_BANKDEPOSIT, $_GET['trans_no']);
}

//$control_ci = module_control_load('tran/journal','gl');
$control_ci = module_control_load('transaction','bank');
page($_SESSION['page_title'], false, false, '', $js);

//-----------------------------------------------------------------------------------------------
check_db_has_bank_accounts(_("There are no bank accounts defined in the system."));

$control_ci->page_finish();


//--------------------------------------------------------------------------------------------------
function line_start_focus() {
	global 	$Ajax;

	$Ajax->activate('items_table');
	$Ajax->activate('footer');
	set_focus('_code_id_edit');
}

//--------------------------------------------------------------------------------------------------

function create_cart($type, $trans_no){
    global $Refs;

	if (isset($_SESSION['pay_items']))
	{
		unset ($_SESSION['pay_items']);
	}

	$cart = new items_cart($type);
	$cart->order_id = $trans_no;

	if ($trans_no) {

		$bank_trans =  module_model_load('trans','bank')->get_bank_trans($type, $trans_no);// db_fetch(get_bank_trans($type, $trans_no));

		$_POST['cheque'] = $bank_trans["cheque"];
		$_POST['bank_account'] = $bank_trans["bank_act"];
		$_POST['PayType'] = $bank_trans["person_type_id"];

		if ($bank_trans["person_type_id"] == PT_CUSTOMER)
		{
			$trans = get_customer_trans($trans_no, $type);
			$_POST['person_id'] = $trans["debtor_no"];
			$_POST['PersonDetailID'] = $trans["branch_code"];
		}
		elseif ($bank_trans["person_type_id"] == PT_SUPPLIER)
		{
			$trans = get_supp_trans($trans_no, $type);
			$_POST['person_id'] = $trans["supplier_id"];
		}
		elseif ($bank_trans["person_type_id"] == PT_MISC)
		$_POST['person_id'] = $bank_trans["person_id"];
		elseif ($bank_trans["person_type_id"] == PT_QUICKENTRY)
		$_POST['person_id'] = $bank_trans["person_id"];
		else
			$_POST['person_id'] = $bank_trans["person_id"];

		$cart->memo_ = get_comments_string($type, $trans_no);
		$cart->tran_date = sql2date($bank_trans['trans_date']);
		$_POST['trans_date'] = sql2date($bank_trans['trans_date']);
		$cart->reference = $Refs->get($type, $trans_no);

		$cart->original_amount = $bank_trans['amount'];

		$bank_trans_model = module_model_load('trans','bank');

		$items = $bank_trans_model->get_bank_tran_details($type, $trans_no);

		$ex_rate = 1;
		$_POST['tax_inclusive'] = $bank_trans['tax_inclusive'];
		if( is_array($items) && !empty($items) ) foreach ($items AS $row){
		    if (is_bank_account($row->account_code)) {
		        // date exchange rate is currenly not stored in bank transaction,
		        // so we have to restore it from original gl amounts
		        $ex_rate = $bank_trans['amount']/$row->amount;
		        //                     } elseif($row['gst']>0) {
		    } else {
		        //                         $cart->add_gl_item( $row['account'], $row['dimension_id'], $row['dimension2_id'], $row['amount'], $row['gst'] ,$bank_trans['ref'], $row['memo_']);
		        $cart->add_gl_item( $row->account_code, $row->dimension_id, $row->dimension2_id, $row->amount, $row->gst ,$bank_trans['ref'], $row->memo_);
		    }
		}

		// apply exchange rate
		foreach($cart->gl_items as $line_no => $line)
			$cart->gl_items[$line_no]->amount *= $ex_rate;

	} else {
		$cart->reference = $Refs->get_next($cart->trans_type);
		$cart->tran_date = new_doc_date();
		if (!is_date_in_fiscalyear($cart->tran_date))
			$cart->tran_date = end_fiscalyear();
	}

	$_POST['memo_'] = $cart->memo_;
	$_POST['ref'] = $cart->reference;
	$_POST['date_'] = $cart->tran_date;

	$_SESSION['pay_items'] = &$cart;
}
//-----------------------------------------------------------------------------------------------

function check_trans() {
	global $Refs;

	$input_error = 0;

	if ($_SESSION['pay_items']->count_gl_items() < 1) {
		display_error(_("You must enter at least one payment line."));
		set_focus('code_id');
		$input_error = 1;
	}

	if ($_SESSION['pay_items']->gl_items_total() == 0.0) {
		display_error(_("The total bank amount cannot be 0."));
		set_focus('code_id');
		$input_error = 1;
	}

	$limit = get_bank_account_limit($_POST['bank_account'], $_POST['date_']);

	$amnt_chg = -$_SESSION['pay_items']->gl_items_total()-$_SESSION['pay_items']->original_amount;

	if ($limit !== null && floatcmp($limit, -$amnt_chg) < 0)
	{
		display_error(sprintf(_("The total bank amount exceeds allowed limit (%s)."), price_format($limit-$_SESSION['pay_items']->original_amount)));
		set_focus('code_id');
		$input_error = 1;
	}

	//	if ($trans = check_bank_account_history($amnt_chg, $_POST['bank_account'], $_POST['date_'])) {
	//
	//		display_error(sprintf(_("The bank transaction would result in exceed of authorized overdraft limit for transaction: %s #%s on %s."),
	//			$systypes_array[$trans['type']], $trans['trans_no'], sql2date($trans['trans_date'])));
	//		set_focus('amount');
	//		$input_error = 1;
	//	}
// 	bug($_SESSION['pay_items']);

	if (!$Refs->is_valid($_POST['ref']))
	{
		display_error( _("You must enter a reference."));
		set_focus('ref');
		$input_error = 1;
	}
	elseif ( !is_new_reference($_POST['ref'], $_SESSION['pay_items']->trans_type, $_SESSION['pay_items']->order_id) )
	{
		display_error( _("The entered reference is already in use."));
		set_focus('ref');
		$input_error = 1;
	}

// die('check dupplicate REF');

	if (!is_date($_POST['date_']))
	{
		display_error(_("The entered date for the payment is invalid."));
		set_focus('date_');
		$input_error = 1;
	}
	elseif (!is_date_in_fiscalyear($_POST['date_']))
	{
		display_error(_("The entered date is not in fiscal year."));
		set_focus('date_');
		$input_error = 1;
	}

	if (get_post('PayType')==PT_CUSTOMER && (!get_post('person_id') || !get_post('PersonDetailID'))) {
		display_error(_("You have to select customer and customer branch."));
		set_focus('person_id');
		$input_error = 1;
	} elseif (get_post('PayType')==PT_SUPPLIER && (!get_post('person_id'))) {
		display_error(_("You have to select supplier."));
		set_focus('person_id');
		$input_error = 1;
	}
	if (!db_has_currency_rates(get_bank_account_currency($_POST['bank_account']), $_POST['date_'], true))
		$input_error = 1;

	if (isset($_POST['settled_amount']) && in_array(get_post('PayType'), array(PT_SUPPLIER, PT_CUSTOMER)) && (input_num('settled_amount') <= 0)) {
		display_error(_("Settled amount have to be positive number."));
		set_focus('person_id');
		$input_error = 1;
	}
	return $input_error;
}

if (isset($_POST['Process']) && !check_trans()) {


	begin_transaction();


	$_SESSION['pay_items'] = &$_SESSION['pay_items'];
	$new = $_SESSION['pay_items']->order_id == 0;
	$trans_type = $_SESSION['pay_items']->trans_type;
	$trans_no = $_SESSION['pay_items']->order_id;


	add_new_exchange_rate(get_bank_account_currency(get_post('bank_account')), get_post('date_'), input_num('_ex_rate'));

// 	$voided = $ci->db->where(array('type'=>$trans_type,'id'=>$trans_no))->get('voided')->num_rows();
//     if( $trans_no ){
//         $bank_module = module_model_load('trans','bank');
//         $bank_module->update_item_fix($trans_no,$trans_type);
//     }

    $trans = write_bank_transaction(
			$trans_type, $trans_no, $_POST['bank_account'],
			$_SESSION['pay_items'], $_POST['date_'],
			$_POST['PayType'], $_POST['person_id'], get_post('PersonDetailID'),
			$_POST['ref'],$_POST['gst'], $_POST['memo_'], true, input_num('settled_amount', null),
			input_post('tax_inclusive'),$_POST['cheque']
		);

	$trans_type = $trans[0];
	$trans_no = $trans[1];
	new_doc_date($_POST['date_']);

	$_SESSION['pay_items']->clear_items();

	unset($_SESSION['pay_items']);
	if( isset($_POST['source_ref']) ){
	    update_source_ref($trans_type,$trans_no,$_POST['source_ref']);
	}
	commit_transaction();

// 	if( $voided < 1 ){
// 	    $ci->db->where(array('type'=>$trans_type,'id'=>$trans_no))->delete('voided');
// 	}

	if( intval( $document_id = input_post('document_id')) > 0 ){
	    $mobile_model = module_model_load('mobile','documents');
	    $mobile_model->update_posting_link($trans_type,$trans_no,$document_id);
	}
	if ($new)
		meta_forward($_SERVER['PHP_SELF'], $trans_type==ST_BANKPAYMENT ?
				"AddedID=$trans_no" : "AddedDep=$trans_no");
	else
		meta_forward($_SERVER['PHP_SELF'], $trans_type==ST_BANKPAYMENT ?
				"UpdatedID=$trans_no" : "UpdatedDep=$trans_no");

}

//-----------------------------------------------------------------------------------------------

function check_item_data() {
	if (!check_num('amount',0)){
		display_warning( _("The amount entered is not a valid number or is less than zero."));
		set_focus('amount');
		return false;
	}
	if (isset($_POST['_ex_rate']) && input_num('_ex_rate') <= 0)
	{
		display_error( _("The exchange rate cannot be zero or a negative number."));
		set_focus('_ex_rate');
		return false;
	}

	return true;
}

//-----------------------------------------------------------------------------------------------

function handle_update_item() {

	$amount = ($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? 1:-1) * input_num('amount');

	if($_POST['UpdateItem'] != "" && check_item_data()){
		$_SESSION['pay_items']->update_gl_item($_POST['Index'], $_POST['code_id'], $_POST['dimension_id'], $_POST['dimension2_id'], $amount ,$_POST['gst'],null, $_POST['LineMemo']);
	}
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_delete_item($id){
	$_SESSION['pay_items']->remove_gl_item($id);
	line_start_focus();
}

//-----------------------------------------------------------------------------------------------

function handle_new_item(){
	if (!check_item_data())
		return;
	$amount = ($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? 1:-1) * input_num('amount');

	$_SESSION['pay_items']->add_gl_item($_POST['code_id'], $_POST['dimension_id'],$_POST['dimension2_id'], $amount, $_POST['gst'], null ,$_POST['LineMemo']);

	line_start_focus();
}


//-----------------------------------------------------------------------------------------------
$id = find_submit('Delete');

if ($id != -1)
	handle_delete_item($id);

if (isset($_POST['AddItem']))
	handle_new_item();

if (isset($_POST['UpdateItem']))
	handle_update_item();

if (isset($_POST['CancelItemChanges']))
	line_start_focus();

if (isset($_POST['go'])) {

	display_quick_entries($_SESSION['pay_items'], $_POST['person_id'], input_num('totamount'),
			$_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? QE_PAYMENT : QE_DEPOSIT);
	$_POST['totamount'] = price_format(0);
	$Ajax->activate('totamount');
	line_start_focus();
}
//-----------------------------------------------------------------------------------------------
if( input_val('goods_invoice') ){
    $model = $ci->model('supplier',true);
    $import_gl = $model->invoice_details(input_val('goods_invoice'),ST_SUPPINVOICE);

    if( !isset( $_SESSION['pay_items']->invoice_import) ){
        $_SESSION['pay_items']->invoice_import = array();
    }
    if( !empty($import_gl) ){ foreach ($import_gl AS $gl){
        if( $gl->gl_code && !in_array($gl->gl_code, $_SESSION['pay_items']->invoice_import)){
            $_SESSION['pay_items']->invoice_import[] = $gl->gl_code;
            $_SESSION['pay_items']->add_gl_item($gl->gl_code, 0,0, $gl->price, $gl->tax_type_id, null , $gl->memo_);
        }

    }}


}

$control_ci->index();

end_page();

?>
