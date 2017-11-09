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

function add_customer($CustName, $cust_ref, $address, $tax_id, $curr_code,
	$dimension_id, $dimension2_id, $credit_status, $payment_terms, $discount, $pymt_discount,
	$credit_limit, $sales_type, $notes, $cus_gst_03_type, $industry_code)
{
	$sql = "INSERT INTO ".TB_PREF."debtors_master (name, debtor_ref, address, tax_id,
		dimension_id, dimension2_id, curr_code, credit_status, payment_terms, discount,
		pymt_discount,credit_limit, sales_type, notes, customer_tax_id, msic) VALUES ("
		.db_escape($CustName) .", " .db_escape($cust_ref) .", "
		.db_escape($address) . ", " . db_escape($tax_id) . ","
		.db_escape($dimension_id) . ", "
		.db_escape($dimension2_id) . ", ".db_escape($curr_code) . ",
		" . db_escape($credit_status) . ", ".db_escape($payment_terms) . ", " . $discount . ",
		" . $pymt_discount . ", " . $credit_limit
		 .", ".db_escape($sales_type).", ".db_escape($notes) . ", ".db_escape($cus_gst_03_type) . ", ".db_escape($industry_code).")";

	db_query($sql,"The customer could not be added");
}

function update_customer($customer_id, $CustName, $cust_ref, $address, $tax_id, $curr_code,
	$dimension_id, $dimension2_id, $credit_status, $payment_terms, $discount, $pymt_discount,
	$credit_limit, $sales_type, $notes, $customer_tax_id, $industry_code)
{

	$sql = "UPDATE ".TB_PREF."debtors_master SET name=" . db_escape($CustName) . ",
		debtor_ref=" . db_escape($cust_ref) . ",
		address=".db_escape($address) . ",
		tax_id=".db_escape($tax_id) . ",
		curr_code=".db_escape($curr_code) . ",
		dimension_id=".db_escape($dimension_id) . ",
		dimension2_id=".db_escape($dimension2_id) . ",
		credit_status=".db_escape($credit_status) . ",
		payment_terms=".db_escape($payment_terms) . ",
		discount=" . $discount . ",
		pymt_discount=" . $pymt_discount . ",
		credit_limit=" . $credit_limit . ",
		sales_type = ".db_escape($sales_type) . ",
		notes=".db_escape($notes) .",
        notes=".db_escape($notes) .",
		customer_tax_id=".db_escape($customer_tax_id) ."
		WHERE debtor_no = ".db_escape($customer_id);
	db_query($sql,"The customer could not be updated");
}

function delete_customer($customer_id)
{
	begin_transaction();
	delete_entity_contacts('customer', $customer_id);

	$sql = "DELETE FROM ".TB_PREF."debtors_master WHERE debtor_no=".db_escape($customer_id);;
	db_query($sql,"cannot delete customer");
	commit_transaction();
}

function get_customer_details($customer_id, $to=null, $all=true)
{

	if ($to == null)
		$todate = date("Y-m-d");
	else
		$todate = date2sql($to);

	$past1 = get_company_pref('past_due_days');
	$past2 = 2 * $past1;
	$allocation_model = module_model_load('allocation','gl');

    $alloc = $allocation_model->alloc_sum('trans.trans_no','trans.type', $to,'alloc_',array(ST_SALESINVOICE,ST_OPENING_CUSTOMER));

	// removed - debtor_trans.alloc from all summations
	if ($all)
    	$value = "IFNULL(IF(trans.type=11 OR trans.type=12 OR trans.type= 2, -1, 1)
    		* (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount  ),0)";
    else
    	$value = "IFNULL(IF(trans.type=11 OR trans.type=12 OR trans.type=2, -1, 1)
    		* (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount - $alloc )
    	    ,0)";

	$due = "IF (trans.type=10, trans.due_date, trans.tran_date)";
    $sql = "SELECT debtors_master.name, debtors_master.curr_code, payment_terms.terms,
		debtors_master.credit_limit, credit_status.dissallow_invoices, credit_status.reason_description,

		Sum(IFNULL($value,0)) AS Balance,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)) AS Due,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)) AS Overdue1,
		Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)) AS Overdue2

		FROM debtors_master
			 LEFT JOIN debtor_trans AS trans ON trans.tran_date <= '$todate' AND debtors_master.debtor_no = trans.debtor_no AND trans.type <> 13 AND trans.trans_no NOT IN ( SELECT voided.id FROM voided WHERE voided.type=trans.type )";
//     if( !$all ){
//         $sql.= "LEFT JOIN `cust_allocations` AS allo_to ON `allo_to`.`trans_no_to` = `trans`.`trans_no` AND allo_to.trans_type_to = trans.type AND allo_to.trans_no_from NOT IN ( SELECT voided.id FROM voided WHERE voided.type=allo_to.trans_type_from ) AND allo_to.date_alloc <='$todate'"
// 			 ." LEFT JOIN `cust_allocations` AS allo_from ON `allo_from`.`trans_no_from` = `trans`.`trans_no` AND allo_from.trans_type_from = trans.type AND allo_from.trans_no_to NOT IN ( SELECT voided.id FROM voided WHERE voided.type=allo_from.trans_type_from ) AND allo_from.date_alloc <='$todate'";
//     }

   $sql.=" LEFT JOIN payment_terms ON payment_terms.terms_indicator = debtors_master.payment_terms
             LEFT JOIN credit_status ON credit_status.id = debtors_master.credit_status

		WHERE debtors_master.debtor_no = ".db_escape($customer_id)." AND trans.type <> ".ST_BANKPAYMENT;
   $sql.=" AND trans.trans_no NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=trans.type ) ";

	if (!$all)
		$sql .= "AND ABS(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount - $alloc) > ".FLOAT_COMP_DELTA." ";

	$sql .= "GROUP BY
			  debtors_master.name,
			  payment_terms.terms,
			  payment_terms.days_before_due,
			  payment_terms.day_in_following_month,
			  debtors_master.credit_limit,
			  credit_status.dissallow_invoices,
			  credit_status.reason_description";
// bug($sql);
    $result = db_query($sql,"The customer details could not be retrieved");

    $customer_record = db_fetch($result);

    return $customer_record;

}


function get_customer_name($customer_id)
{
	$sql = "SELECT name FROM ".TB_PREF."debtors_master WHERE debtor_no=".db_escape($customer_id);

	$result = db_query($sql, "could not get customer");

	$row = db_fetch_row($result);

	return $row[0];
}

function get_customer_habit($customer_id)
{
	$sql = "SELECT ".TB_PREF."debtors_master.pymt_discount,
		".TB_PREF."credit_status.dissallow_invoices
		FROM ".TB_PREF."debtors_master, ".TB_PREF."credit_status
		WHERE ".TB_PREF."debtors_master.credit_status = ".TB_PREF."credit_status.id
			AND ".TB_PREF."debtors_master.debtor_no = ".db_escape($customer_id);

	$result = db_query($sql, "could not query customers");

	return db_fetch($result);
}

function get_customer_contacts($customer_id, $action=null)
{
	$results = array();
	$res = get_crm_persons('customer', $action, $customer_id);

	while($contact = db_fetch($res)){
		if ($contact['lang'] == 'C') // Fix for improper lang in demo sql files.
			$contact['lang'] = '';
		$results[] = $contact;
	}
	return $results;
}

function get_current_cust_credit($customer_id)
{
	$custdet = get_customer_details($customer_id);

	return $custdet['credit_limit']-$custdet['Balance'];

}

function is_new_customer($id)
{
	$tables = array('cust_branch', 'debtor_trans', 'recurrent_invoices', 'sales_orders');

	return !key_in_foreign_table($id, $tables, 'debtor_no');
}

function get_customer_by_ref($reference)
{
	$sql = "SELECT * FROM ".TB_PREF."debtors_master WHERE debtor_ref=".db_escape($reference);

	$result = db_query($sql, "could not get customer");

	return db_fetch($result);
}

?>