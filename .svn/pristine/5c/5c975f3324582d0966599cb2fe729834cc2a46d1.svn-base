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

$page_security = 'SA_GLANALYTIC';
$path_to_root="../..";

// include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(800, 500);
// if ($use_date_picker)
// 	$js .= get_js_date_picker();

$control_ci = module_control_load('inquiry/journal','gl');
switch (input_val('filtertype')){
    case 1:
        get_instance()->smarty->assign('button_add_new',array('tran_type'=>ST_BANKPAYMENT,'title'=>'Add New Payment','uri'=>'gl/gl_bank.php?NewPayment=Yes'));
        break;
    case 2:
        get_instance()->smarty->assign('button_add_new',array('tran_type'=>ST_BANKDEPOSIT,'title'=>'Add New Deposit','uri'=>'gl/gl_bank.php?NewDeposit=Yes'));
        break;
    default:break;

}
page(_($help_context = "Journal Inquiry"), false, false, "", $js);

//-----------------------------------------------------------------------------------
// Ajax updates
//
if (get_post('Search')){
	$Ajax->activate('journal_tbl');
}
//--------------------------------------------------------------------------------------


if (isset($_GET['filtertype'])){
	$trans_type = $_GET['filtertype'];
} else
	$trans_type = -1;

if (!isset($_POST['filterType']))
	$_POST['filterType'] = $trans_type;

// if( $trans_type > 0 ){
// 	switch ($trans_type){
// 		case 1:
// 			echo "<div class='btnadd'><a href='".site_url('gl/gl_bank.php?NewPayment=Yes')."' class='ajaxsubmit'><img height='12' src='".site_url('themes/template/images/ok.gif')."'>Add new</a></div>";
// 			break;
// 		case 2:
// 			echo "<div class='btnadd'><a href='".site_url('gl/gl_bank.php?NewDeposit=Yes')."' class='ajaxsubmit'><img height='12' src='".site_url('themes/template/images/ok.gif')."'>Add new</a></div>";
// 			break;

// 		default:break;
// 	}
// }


function journal_pos($row){
	return $row['gl_seq'] ? $row['gl_seq'] : '-';
}

// function systype_name($dummy, $type){
// 	global $systypes_array;

// 	return ( array_key_exists( $type, $systypes_array) ? $systypes_array[$type] : null );
// }

function view_link($row)
{
    $detail_empty = false;
    switch ($row["type"]){
        case ST_BANKDEPOSIT:
        case ST_BANKPAYMENT:
            $check_detail = get_instance()->db->where(array('type'=>$row["type"],'trans_no'=>$row["type_no"]))->get('bank_trans_detail');
            if( $check_detail->num_rows() < 1 ){
                $detail_empty = true;

            }
            break;
        case ST_SALESINVOICE:

            $check_detail = get_instance()->db->where(array('debtor_trans_type'=>$row["type"],'debtor_trans_no'=>$row["type_no"]))->get('debtor_trans_details');
            if( $check_detail->num_rows() < 1 ){
                $detail_empty = true;
            }
            break;
        case ST_SUPPINVOICE:

            $check_detail = get_instance()->db->where(array('supp_trans_type'=>$row["type"],'supp_trans_no'=>$row["type_no"]))->get('supp_invoice_items');
            if( $check_detail->num_rows() < 1 ){
                $detail_empty = true;
            }
            break;

        default :
            $detail_empty = false;
            break;
    }

    $note = "";
    if( $detail_empty ){
        $note = "<span class='note_lostdata' title'Lost Detail data' ></span>";
    }



	return get_trans_view_str($row["type"], $row["type_no"]).$note;
}

function gl_link($row) {
	return get_gl_view_str($row["type"], $row["type_no"]);
}

function gl_print($row) {
	if( isset($row['type']) ){
		switch ($row['type']){
			case ST_BANKPAYMENT:

				$button = print_document_link( $row['type_no'], _("&Print This Order"), true, ST_GLPAYMENT, ICON_PRINT);
// 				$button = print_document_link($row['type_no'], _("Print"), true, ST_GLPAYMENT, ICON_PRINT);
				break;
			case  ST_BANKDEPOSIT:
				$button = print_document_link( $row['type_no'], _("&Print This Order"), true, ST_GLDEPOSIT, ICON_PRINT);
// 				print_document_link($row['trans_no']."-".$row['type'], _("Print"), true, $row['type'], ICON_PRINT);
				break;
			case  ST_BANKTRANSFER:
				$button = print_document_link( $row['type_no'], _("&Print This Order"), true, ST_BANKTRANSFER, ICON_PRINT, 'download'); break;
			case ST_JOURNAL:
				$button = print_document_link( $row['type_no'], _("&Print This Order"), true, ST_JOURNAL, ICON_PRINT, 'download'); break;
			default: $button = null; break;
		}
		return $button;
// 		return get_gl_view_str($row['type'], $row['type_no'], _("&View the GL Postings for this Payment") );

	}

}

$editors = array(
	ST_JOURNAL => "/gl/gl_journal.php?ModifyGL=Yes&trans_no=%d&trans_type=%d",
	ST_BANKPAYMENT => "/gl/gl_bank.php?ModifyPayment=Yes&trans_no=%d&trans_type=%d",
	ST_BANKDEPOSIT => "/gl/gl_bank.php?ModifyDeposit=Yes&trans_no=%d&trans_type=%d",
//	4=> Funds Transfer,
   ST_SALESINVOICE => "/sales/customer_invoice.php?ModifyInvoice=%d",
//   11=>
// free hand (debtors_trans.order_==0)
//	"/sales/credit_note_entry.php?ModifyCredit=%d"
// credit invoice
//	"/sales/customer_credit_invoice.php?ModifyCredit=%d"
//	 12=> Customer Payment,
   ST_CUSTDELIVERY => "/sales/customer_delivery.php?ModifyDelivery=%d",
//   16=> Location Transfer,
//   17=> Inventory Adjustment,
//   20=> Supplier Invoice,
//   21=> Supplier Credit Note,
//   22=> Supplier Payment,
//   25=> Purchase Order Delivery,
//   28=> Work Order Issue,
//   29=> Work Order Production",
//   35=> Cost Update,
);



$control_ci->index();

end_page();

?>
