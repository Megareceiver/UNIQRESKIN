<?php
$path_to_root = "..";
    include_once($path_to_root . "/includes/db_pager.inc");
    include_once($path_to_root . "/includes/session.inc");
    include_once($path_to_root . "/includes/date_functions.inc");
    include_once($path_to_root . "/includes/ui.inc");
    include_once($path_to_root . "/gl/includes/gl_db.inc");
    $trans_no = $_GET['PARAM_0'];

    $result = get_bank_trans(ST_BANKDEPOSIT, $trans_no);
    $from_trans = db_fetch($result);
    switch ($_GET['REP_ID']) {
    	case 801:
    		$trans_type = ST_BANKPAYMENT;
    		echo "<title>Bank Payment Voucher</title>"; break;
    		//                 break;
    		//             case ST_CUSTPAYMENT:
    		//                 echo "<title>Customer Payment Voucher</title>";
    		//                 break;
    		//             case ST_SUPPAYMENT:
    		//                 echo "<title>Supplier Payment Voucher</title>";
    		//                 break;
    		//             case ST_WORKORDER:
    		//                 echo "<title>Work Order Voucher</title>";
    		//                 break;
    	case 802:
    		$trans_type = ST_BANKDEPOSIT;
    		echo "<title>Bank Deposit Voucher</title>";
    		break;
    }

    //Customer payment voucher
//    $receipt = get_customer_trans($trans_id, ST_CUSTPAYMENT);
//    //bug($receipt);die;

//SonDang
//


        $company_currency = get_company_currency();
//        bug($company_currency);die;

        $show_currencies = false;

        if ($from_trans['bank_curr_code'] != $from_trans['settle_curr'])
        {
                $show_currencies = true;
        }

//    $result_gl = get_gl_trans($_GET['type_id'], $_GET['trans_no']);
    //bug(db_fetch($result));die;

        $receipt = get_customer_trans($trans_no, $trans_type);

        $company = get_company_pref();




	$html = '';
	$html.= '<div class="content1"><table style="width: 100%;"><tr style="height: 70px"><td>';

	$logo = company_path () . "/images/" . $company['coy_logo'];
	if ( file_exists ( $logo ) ){
		$html.= '<img src="'.$logo.'" width=250>';
	} else {
		$html.='<h2>'.$company['coy_name'].'</h2>';
	}
	$html.='</td></tr><tr><td align="right"><h1 style="padding: 0; margin: 0;">RECEIPT VOUCHER</h1></td></tr></table></div>';

    $html.='<div class="content2">
            <table width=100% cellpadding=2 cellspacing=0 >
                <tr>
                    <td class="textbold" style="width: 25%;">DATE</td>
                    <td style="width: 1%;">:</td>
                    <td >'.sql2date($from_trans['trans_date']).'</td>
                </tr>
                <tr>
                    <td class="textbold">TRANSACTION NO</td>
                    <td>:</td>
                    <td>#'.$trans_no.'</td>
                </tr>
                <tr>
                    <td class="textbold">REFERENCE NO</td>
                    <td>:</td>
                    <td>'.$from_trans['ref'].'</td>
                </tr>
                <tr>
                    <td class="textbold">PAYER</td>
                    <td>:</td>
                    <td>'.$company['coy_name'].'</td>
                </tr>
                <tr>
                    <td class="textbold">PAYMENT FROM</td>
                    <td>:</td>
                    <td>'.$from_trans['bank_account_name'].'</td>
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
                        <td class="tableheader" >Debit</td>
                        <td class="tableheader" >Credit</td>
                        <td class="tableheader"  >Memo</td>
                    </tr>
                    </thead>
                    <tbody>';
        $credit = $debit = 0;
		$result3 = get_gl_trans($trans_type, $trans_no);
		$total = 0;
		while ($row = db_fetch($result3)) {

			$amount =$row['amount'];
			$credit = $debit = '&nbsp;';
			if($amount > 0 ){
				$debit = money_format('%i', $row['amount']);
				$total += $amount;
			} else if ( $amount < 0 ){
				$credit = abs(money_format('%i', $row['amount']));
			}
			$html.='<tr class="evenrow">
                        <td class="textleft" >'.$row['account'].'</td>
                        <td class="textleft" align="left" >'.$row['account_name'].'</td>
                        <td>'.$debit.'</td>
                        <td>'.$credit.'</td>
                        <td>'.$row['memo_'].'</td>
                    </tr>';
		}

		$html.='</tbody><tfoot>
                    <tr class="totalrow">
                        <td colspan="2" class="textright" >TOTAL AMOUNT PAID: </td>
                        <td colspan="3" class="textleft"  >'.money_format('%i', $total).'</td>
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
		$mpdf = $ci->pdf->load();
		//$ci->pdf->AddPage('','','1','i','on');

		$stylesheet = file_get_contents(ROOT.'/themes/'.user_theme().'/css/bank_inquiry.css');
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML($html,2);
		$mpdf->Output('payment-deposit.pdf','I');

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
