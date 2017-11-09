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
$page_security = 'SA_SUPPTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$inquiry_ci = module_control_load('inquiry/transactions','purchases');

if (!@$_GET['popup']){
	$js = "";
	if ($use_popup_windows)
		$js .= get_js_open_window(900, 500);


	$buttonAddNew = null;
	$filterType = input_val('filtertype');

	switch ($filterType){
		case 1:
		    get_instance()->smarty->assign('button_add_new',array('tran_type'=>ST_SUPPINVOICE,'title'=>'Add New Invoice','uri'=>'purchasing/po_entry_items.php?NewInvoice=Yes'));
			break;
		case 2:
		    get_instance()->smarty->assign('button_add_new',array('tran_type'=>ST_SUPPINVOICE,'title'=>'Add New Overdue Invoice','uri'=>'purchasing/supplier_invoice.php?New=1'));
			break;
		case 6:
		    get_instance()->smarty->assign('button_add_new',array('tran_type'=>ST_SUPPINVOICE,'title'=>'Add New GRNs','uri'=>'purchasing/po_entry_items.php?NewGRN=Yes'));
		    break;

		default:break;
	}

	page(_($help_context = "Supplier Inquiry"), isset($_GET['supplier_id']), false, "", $js,false);
}



//------------------------------------------------------------------------------------------------

function trans_view_supplier($trans)
{
	return trans_view_anchor($trans["type"], $trans["trans_no"]);
}

function gl_payable($row){
    $ci = get_instance();
    $acc=2100;
    $data = $ci->db->select('amount')->where(array('type_no'=>$row['trans_no'],'type'=>$row['type'],'account'=>$acc))->get('gl_trans')->row();

    if( abs($row["TotalAmount"]) != abs($data->amount) ){

        return '<span style="color:red">'.$data->amount.'</span>';
    } else {
        return '<span style="color:blue">'.$data->amount.'</span>';
    }

}

// function prt_link($row){
//   	if ( $row['type'] == ST_SUPPAYMENT || $row['type'] == ST_BANKPAYMENT || $row['type'] == ST_SUPPCREDIT )
//  		return print_document_link($row['trans_no']."-".$row['type'], _("Print Remittance"), true, ST_SUPPAYMENT, ICON_PRINT);
//   	elseif ($row['type'] == ST_SUPPINVOICE){
//   	    return print_document_link($row['trans_no'], _("Print Remittance"), true, $row['type'], ICON_PRINT);
//   	}
// }

function check_overdue($row) {
	return $row['OverDue'] == 1
		&& (abs($row["TotalAmount"]) - $row["Allocated"] != 0);
}
//------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------



if (!@$_GET['popup'])
    start_form();

$inquiry_ci->view();

if (!@$_GET['popup'])
{
	end_form();
	end_page(@$_GET['popup'], false, false);
}
?>
