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

$sales_inquiry_ci = module_control_load('inquiry/transactions','sales');
global $ci;


if (!@$_GET['popup']){
	$js = "";
	if ($use_popup_windows)
		$js .= get_js_open_window(900, 500);

	switch (input_val('filterType')){
		case 1:
		    get_instance()->smarty->assign('button_add_new',array('tran_type'=>ST_SALESINVOICE,'title'=>'Add New Invoice','uri'=>'sales/sales_order_entry.php?NewInvoice=0'));
		    break;
		case 5:
		    get_instance()->smarty->assign('button_add_new',array('tran_type'=>ST_SALESINVOICE,'title'=>'Add New Delivery','uri'=>'sales/sales_order_entry.php?NewDelivery=0'));
		    break;
		default:break;
	}
	page(_($help_context = "Customer Transactions"), isset($_GET['customer_id']), false, "", $js,false);

}


//------------------------------------------------------------------------------------------------

function order_view($row)
{
	return $row['order_']>0 ? get_customer_trans_view_str(ST_SALESORDER, $row['order_']) : NULL;
}

// function trans_view_customer($trans)
// {
// 	return get_trans_view_str($trans["type"], $trans["trans_no"]);
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

$sales_inquiry_ci->view();

if (!@$_GET['popup']){
	end_form();
	end_page(@$_GET['popup'], false, false);
}
?>
