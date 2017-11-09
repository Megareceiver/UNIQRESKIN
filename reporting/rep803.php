<?php
global $Ajax;

// if( !method_exists($ci,'Bank_Model') ){
$model = $ci->model('bank',true);
$gl_model = $ci->model('gl_trans',true);
// }

$save = true;

if( $ci->input->get('save')==='false'  ){
	$save = false;
}

if( $ci->input->post('type') ){
	$rep_type = $ci->input->post('type');
} else if( $ci->input->get('type') ) {
	$rep_type = $ci->input->get('type');
} else {
	$rep_type = 0;
}
// bug('rep type='.$rep_type);die;

switch ($rep_type) {
	case ST_BANKPAYMENT:
		$trans_type = ST_BANKPAYMENT;
		$page_title = 'PAYMENT VOUCHER';
		break;
	case ST_BANKDEPOSIT:
		$trans_type = ST_BANKDEPOSIT;
		$page_title = "Receipt Voucher";
		break;
	case ST_BANKTRANSFER:
		$trans_type = ST_BANKTRANSFER;
		$page_title = "Bank Transfer Voucher";
		break;
	case ST_JOURNAL:
		$trans_type = ST_JOURNAL;
		$page_title = "Journal Voucher";
		break;
}

$where = array('bt.type'=>$trans_type);

if($ci->input->post('start_date')){
	$where['bt.trans_date >='] = $ci->input->post('start_date');
}
if($ci->input->post('end_date')){
	$where['bt.trans_date <='] = $ci->input->post('end_date');
}
if($ci->input->post('account')){
	$where['bt.bank_act'] = $ci->input->post('account');
}

if($ci->input->post('trans_no')){
	$where['bt.trans_no'] = $ci->input->post('trans_no');
}
if($ci->input->get('trans_no')){
	$where['bt.trans_no'] = $ci->input->get('trans_no');
}

if($ci->input->post('ref')){
	$where['bt.ref'] = $ci->input->post('ref');
}


if( $trans_type== ST_JOURNAL){
	if( isset($where['bt.trans_no']) ){
		$where['bt.type_no'] = $where['bt.trans_no'];
		unset($where['bt.trans_no']);
	}
	if( isset($where['bt.trans_date >=']) ){
		$where['bt.tran_date >='] = $where['bt.trans_date >='];
		unset($where['bt.trans_date >=']);
	}
	if( isset($where['bt.trans_date <=']) ){
		$where['bt.tran_date <='] = $where['bt.trans_date <='];
		unset($where['bt.trans_date <=']);
	}

	if( isset($where['bt.ref']) ){
		$where['refs.reference'] = $where['bt.ref'];
		unset($where['bt.ref']);
	}



	$trans = $gl_model->items($where);
} else {
	$trans = $model->items($where);
}

// bug($trans);die('quannh');

$company = get_company_pref();

$mpdf = $ci->pdf->load();
$mpdf->AddPage();

$stylesheet = file_get_contents(ROOT.'/themes/'.user_theme().'/css/bank_inquiry.css');

$mpdf->WriteHTML($stylesheet,1);

if( $trans && count($trans) >0 ){ foreach ($trans AS $tran){
	$html = '';
	$show_currencies = false;
	if ($tran->bank_curr_code != $tran->settle_curr) {
		$show_currencies = true;
	}

	$receipt = get_customer_trans($tran->trans_no, $trans_type);



	$html.= '<div class="content1"><table style="width: 100%;"><tr style="height: 70px"><td>';

	$logo = company_path () . "/images/" . $company['coy_logo'];
	if ( file_exists ( $logo ) ){
		$html.= '<img src="'.$logo.'" height=50>';
	} else {
		$html.='<h2>'.$company['coy_name'].'</h2>';
	}
	$html.='</td></tr><tr><td align="right"><h1 style="padding: 0; margin: 0;">'.$page_title.'</h1></td></tr></table></div>';

	$html.='<div class="content2">
	<table width=100% cellpadding=2 cellspacing=0 >
	<tr> <td class="textbold" style="width: 25%;">DATE</td><td style="width: 1%;">:</td><td >'.sql2date($tran->trans_date).'</td> </tr>
	<tr> <td class="textbold">TRANSACTION NO</td><td>:</td><td>#'.$tran->trans_no.'</td> </tr>
	<tr>
	<td class="textbold">REFERENCE NO</td>
	<td>:</td>
	<td>'.$tran->ref.'</td>
	</tr>
	<tr> <td class="textbold">PAYER</td> <td>:</td> <td>'.$company['coy_name'].'</td> </tr>
	<tr>
	<td class="textbold">PAYMENT FROM</td>
	<td>:</td>
	<td>'.$tran->bank_account_name.'</td>
	</tr>
	</table>
	</div>';

	$html.='<div class="content3" id="maincontent" >
	<p style="text-decoration: underline;">BEING PAYMENT FOR:</p>
	<br>
	<table class="tablestyle" width=100% cellpadding=2 cellspacing=0>
	<thead>
	<tr>
	<td class="tableheader" style="width: 20%;" >Account Code</td>
	<td class="tableheader" >Account Name</td>
	<td class="tableheader" style="width: 25%;">Debit</td>
	<td class="tableheader" style="min-width: 25%;" >Credit</td>
	<td class="tableheader"  >Memo</td>
	</tr>
	</thead>
	<tbody>';

	$credit = $debit = 0;
	//$result3 = get_gl_trans($trans_type, $trans_no);
	$total = 0;
	$trans_detail = $gl_model->items_trans($tran->trans_no,$trans_type);

	foreach ($trans_detail AS $trans_item){

// 		$amount =$row['amount'];
		$credit = $debit = '&nbsp;';
		if($trans_item->amount > 0 ){
			$debit = money_format('%i', $trans_item->amount);
			$total += $trans_item->amount;
		} else if ( $trans_item->amount < 0 ){
			$credit = abs(money_format('%i', $trans_item->amount));
		}
		$html.='<tr class="evenrow">
		<td class="textleft" >'.$trans_item->account.'</td>
		<td class="textleft" align="left" >'.$trans_item->account_name.'</td>
		<td>'.$debit.'</td>
		<td>'.$credit.'</td>
		<td class="textleft" align="left" >'.$trans_item->memo_.'</td>
		</tr>';
	}

	$html.='</tbody><tfoot>
	<tr class="totalrow">
	<td colspan="2" class="textright" align="right" >TOTAL AMOUNT PAID: </td>
	<td colspan="3" class="textleft" align="left"  >'.money_format('%i', $total).'</td>
	</tr>
	</tfoot></table>
	</div>
	<div class="amount"> AMOUNT IN WORD: '.price_in_words($total,ST_CUSTPAYMENT).'</div>
	<div class="content4">
	<div id="received_by"><p>RECEIVED BY</p></div>
	<div id="authorized_signator">
	<p align="right">'.$company['coy_name'].'</p>
	<p class="line" ></p>
	<p class="bottom" >Authorized Signatory</p>
	</div>
	</div>';
	//$html.='<p style="page-break-after:always"></p>';
	//$mpdf->AddPage();
//
	$mpdf->WriteHTML($html);
// 	bug($tran);	 die('iam go here');
}} else {
	display_error(_("Report is empty!"));
	exit;
}

if( $save ){
	$filename = company_path(). '/pdf_files/report-'.time().'.pdf';
	$mpdf->Output($filename);
	$Ajax->popup($filename);
} else {
	$mpdf->Output();
}


?>

<html>
     <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <link rel="stylesheet" href="../themes/<?php echo user_theme()?>/css/bank_inquiry.css">
     </head>
     <body>
		<?php echo $html;?>
     </body>
</html>
