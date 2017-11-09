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
$page_security = 'SA_SALESTRANSVIEW';
$path_to_root = "../..";

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$filterType = 0;
if( isset($_POST['filterType']) ){
	$filterType = $_POST['filterType'];
} else if ( isset($_GET['filtertype']) ){
	$filterType = $_GET['filtertype'];
}
global $ci;


if (!@$_GET['popup']){
	$js = "";
	if ($use_popup_windows)
		$js .= get_js_open_window(900, 500);

// 	if ($use_date_picker)
// 		$js .= get_js_date_picker();

	$buttonAddNew = null;
	if( $filterType !=0 ){
		switch ($filterType){
			case 1: $buttonAddNew= button_add_new('sales/sales_order_entry.php','NewInvoice=0'); break;
			case 5: $buttonAddNew= button_add_new('sales/sales_order_entry.php','NewDelivery=0');break;
			default:break;
		}
	}
	//echo $addNewButton;
	page(_($help_context = "Customer Allocation Inquiry"), isset($_GET['customer_id']), false, "", $js,false,'',$buttonAddNew);

}



//------------------------------------------------------------------------------------------------





$sales_inquiry_ci = module_control_load('inquiry/allocations','sales');

$sales_inquiry_ci->view();



//------------------------------------------------------------------------------------------------

// function systype_name123($dummy, $type)
// {
// 	global $systypes_array;

// 	return $systypes_array[$type];
// }

function order_view($row)
{
	return $row['order_']>0 ? get_customer_trans_view_str(ST_SALESORDER, $row['order_']) : NULL;
}

// function view_link($trans)
// {
//     return get_trans_view_str($trans["type"], $trans["trans_no"]);
// }

// function due_date123($row) {

// 	return	($row["type"] == ST_SALESINVOICE || $row["type"] == ST_OPENING)	? $row["due_date"] : '';
// }

// function fmt_debit123($row){
// 	$value = $row["TotalAmount"];
// 	if( $row['type']==ST_CUSTCREDIT || $row['type']==ST_CUSTPAYMENT || $row['type']==ST_BANKDEPOSIT ){
// 		$value = -$row["TotalAmount"];
// 	}
// 	return $value>=0 ? number_total($value) : '';

// }

// function fmt_credit123($row)
// {
// 	$value = !($row['type']==ST_CUSTCREDIT || $row['type']==ST_CUSTPAYMENT || $row['type']==ST_BANKDEPOSIT) ?
// 		-$row["TotalAmount"] : $row["TotalAmount"];
// 	return $value>0 ? number_total($value) : '';
// }

// function fmt_balance123($row)
// {
//     return $row["TotalAmount"] - $row["Allocated"];
// }

function check_overdue($row)
{
	return $row['OverDue'] == 1
		&& floatcmp($row["TotalAmount"], $row["Allocated"]) != 0;
}

function gl_receivable($row){
    $ci = get_instance();
    $acc=1200;
    $data = $ci->db->select('amount')->where(array('type_no'=>$row['trans_no'],'type'=>$row['type'],'account'=>$acc))->get('gl_trans')->row();

    if( abs($row["TotalAmount"]) != abs($data->amount) ){

        return '<span style="color:red">'.number_total($data->amount).'</span>';
    } else {
        return '<span style="color:blue">'.number_total($data->amount).'</span>';
    }

}
//------------------------------------------------------------------------------------------------

// echo '<div class="row"><div class="col-md-12" ><div class="portlet light"><div class=" portlet-body">';



// echo '</div></div></div></div>';

if (!@$_GET['popup']){
	end_page(@$_GET['popup'], false, false);
}
?>
