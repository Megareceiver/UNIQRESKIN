<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//class Sale extends CI_Controller {
class Bank_transactions extends ci {
	function __construct() {
		global $ci;
		$this->ci = $ci;
		$this->db = $ci->db;

		$this->bank_transaction_model = $this->model('bank_transaction',true);
		$this->gl_trans_model = $this->model('gl_trans',true);
		$this->bank_trans_model = $this->model('bank_trans',true);
		$this->customer_model = $this->model('cutomer',true);

	}

	function statement_print($pdf){
	    $acc = $pdf->inputVal('PARAM_0');
    	$from = $pdf->inputVal('PARAM_1');
    	$to = $pdf->inputVal('PARAM_2');
    	$zero = $pdf->inputVal('PARAM_3');
    	$comments = $pdf->inputVal('PARAM_4');
    	$orientation = $pdf->inputVal('PARAM_5')? 'L' : 'P';
    	$destination = $pdf->inputVal('PARAM_6');

    	$account = get_bank_account($acc);
    	$act = $account['bank_account_name']." - ".$account['bank_curr_code']." - ".$account['bank_account_number'];

    	$prev_balance = $this->bank_transaction_model->get_bank_balance_to($from, $account["id"]);
    	$trans = $this->bank_transaction_model->get_bank_transactions($from, $to, $account['id']);

    	$data['title'] = 'Bank Statement';
    	$data['company'] = $pdf->company;
    	$data['bank_account'] = $account['bank_account_name'];
    	if( array_key_exists('bank_curr_code', $account) ){
    	    $data['bank_account'].='-'.$account['bank_curr_code'];
    	}
    	if( array_key_exists('bank_account_number', $account) && $account['bank_account_number'] ){
    	    $data['bank_account'].='-'.$account['bank_account_number'];
    	}
    	$data['opening'] = $prev_balance;
//     	bug($prev_balance);die;
    	$data['period'] = $from.'-'.$to;
    	$data['fiscal_year'] = $pdf->fiscal_year;
    	$data['print_time'] = $pdf->print_time;
    	$data['host'] = $_SERVER['SERVER_NAME'];
    	$data['user'] = $_SESSION["wa_current_user"]->name;
        $data['width'] = 160;

    	$data['tables'] = array(
    	    'type'=>array('title'=>'Type','w'=>15),
    	    'trans_no'=>array('title'=>'#' ,'w'=>10),
    	    'ref'=>array('title'=>'Reference' ,'w'=>15),
    	    'trans_date'=>array('title'=>'Date' ,'w'=>10),
    	    'person'=>array('title'=>'Person/Item','w'=>20),
//     	    'debit'=>array('title'=>'Debit','w'=>13,'class'=>'textright'),
//     	    'credit'=>array('title'=>'Credit','w'=>13,'class'=>'textright'),
//     	    'balance'=>array('title'=>'Balance','w'=>13,'class'=>'textright'),
    	);
    	$data['trans'] = $trans;

    	$html = $this->ci->view('reporting/bank/transactions',$data,true);

    	$pdf->tcpdf->AddPage();
    	$pdf->tcpdf->SetFont('helvetica', '', 9);
//     	$pdf->tcpdf->SetMargins(12, 12, 12, true);
    	$pdf->tcpdf->SetAutoPageBreak(TRUE,12);


    	$pdf->tcpdf->writeHTML($pdf->css.$html);

	}

    function payment_print($pdf,$trans_type=ST_BANKDEPOSIT){

        if( $pdf->inputVal('trans_no') ){
            $trans_no = $pdf->inputVal('trans_no');
        } else {
            $trans_no = $pdf->inputVal('PARAM_0');
        }

        if( !$trans_no  ){

        }

        $trans = $this->bank_trans_model->get_bank_trans($trans_type, $trans_no);

        $tran_item = $trans[0];

        $html = '<table style="width: 100%;"><tr style="height: 70px"><td>'
            .( (isset($pdf->company['logo']) && $pdf->company['logo'] !='')? '<img src="' .$pdf->company['logo'].'" alt="A2000 solusion" height="50" border="0" >' : '<h2>'.$pdf->company['name'].'</h2>' )
        .'</td></tr><tr><td align="right"><h1 style="padding: 0; margin: 0;" >'.$pdf->title.'</h1></td></tr></table>';

        $pdf->tcpdf->header_bank_trans = $html;
        $pdf->tcpdf->bank_trans_data = array(
            'trans_date'=>$tran_item->trans_date,
            'trans_no'=>$tran_item->trans_no,
            'ref'=>$tran_item->ref,
//             'payee'=>null,

            'payment_from'=>$tran_item->bank_account_name,
        	'payment_to'=>null,

            'cheque'=>$tran_item->cheque,
        );

        switch ($tran_item->person_type_id) {
        	case 2:
        		$debtor_trans = $this->db->where(array('trans_no'=>$tran_item->trans_no, 'type'=>$trans_type))->get('debtor_trans')->row();
        		$customer = $this->customer_model->customer_detail($debtor_trans->debtor_no);
        		$pdf->tcpdf->bank_trans_data['payment_to'] = html_entity_decode($customer->debtor_ref);
        		$branch = $this->db->where(array('branch_code'=>$debtor_trans->branch_code))->get('cust_branch')->row();
        		if( $branch && isset($branch->br_name) ){
        			$pdf->tcpdf->bank_trans_data['payment_to'] .= ' / '.html_entity_decode($branch->branch_ref);
        		}
        		break;
        	case 3:
        		$suppliers = $this->db->where(array('supplier_id'=>$tran_item->person_id))->get('suppliers')->row();
        		if( $suppliers && isset($suppliers->supp_name) ){
        			$pdf->tcpdf->bank_trans_data['payment_to'] = html_entity_decode($suppliers->supp_name);
        		}
        		break;
        	default:
        		$pdf->tcpdf->bank_trans_data['payment_to'] = html_entity_decode($tran_item->person_id);
        		break;

        }

        if( $trans_type==ST_BANKDEPOSIT ){
        	$pay_to = $pdf->tcpdf->bank_trans_data['payment_to'];
        	$pdf->tcpdf->bank_trans_data['payment_to'] = $pdf->tcpdf->bank_trans_data['payment_from'];
        	$pdf->tcpdf->bank_trans_data['payment_from'] = $pay_to;
        }


        $pdf->tcpdf->item_table_header = '<table class="tablestyle" cellpadding=2 cellspacing=0>

        <tr>
        	<td class="tableheader textcenter" style="width: 20%;" >Account Code</td>
            <td class="tableheader textleft" style="width: 35%;" >Account Name</td>
            <td class="tableheader textright" style="width: 10%;" >Debit</td>
            <td class="tableheader textright" style="width: 10%;" >Credit</td>
            <td class="tableheader"  style="width: 25%;"  >Memo</td>
            </tr>
		</table>';
// 		if( $trans_type== ST_BANKPAYMENT ){

//             $pdf->tcpdf->bank_trans_data['payee'] = payment_person_name($from_trans['person_type_id'], $from_trans['person_id']);
//         } else {
//             $pdf->tcpdf->bank_trans_data['payeer'] = $pdf->company['name'];
// //             $this->add_bank_pdf_info($pdf->tcpdf,'PAYEER',$pdf->company['name']);
//         }

//         $this->add_bank_pdf_info($pdf->tcpdf,'PAYMENT FROM',$from_trans['bank_account_name']);
//         $this->add_bank_pdf_info($pdf->tcpdf,'Cheque Number',$from_trans['cheque']);

//         $pdf->tcpdf->Line( $pdf->margin_left, $pdf->tcpdf->GetY()+1,$pdf->tcpdf->getPageWidth()-$pdf->margin_right, $pdf->tcpdf->GetY()+1, array('width' =>0.5,'color' => array(128, 128, 128)));

//         $pdf->tcpdf->Write(15,'BEING PAYMENT FOR');
        $pdf->tcpdf->AddPage();

        $gl_trans_items = $this->gl_trans_model->get_gl_trans($trans_type, $tran_item->trans_no);
//         for($i=1;$i<10;$i++){
//             $gl_trans_items = array_merge($gl_trans_items,$gl_trans_items);
//         }

//         $gl_trans = $this->ci->view('reporting/bank_payment_items', array('gl_trans'=>$gl_trans_items) ,true);
//         $pdf->tcpdf->SetY( $pdf->tcpdf->GetY()+12 );
//         $pdf->tcpdf->writeHTML($pdf->css.$gl_trans);

        foreach ($gl_trans_items AS $tran){
            $debit = $credit = 0;

            if ($tran->amount > 0) {
                $debit = $tran->amount;
            } else {
                $credit = abs($tran->amount);
            }


            $lineHTML = '<table cellpadding="3" ><tr>
	        	<td class="textcenter" style="width: 20%;">'.$tran->account.'</td>
	        	<td style="width: 35%;">'.$tran->account_name.'</td>
	        	<td class="textright" style="width: 10%;">'.number_format2($debit,user_amount_dec()).'</td>
	        	<td class="textright" style="width: 10%;">'.number_format2($credit,user_amount_dec()).'</td>
	        	<td style="width: 25%;" >'.$tran->memo_.'</td>
	            </tr></table>';
            $pdf->tcpdf->SetY($pdf->tcpdf->GetY() - 4.5);

            if( $pdf->tcpdf->GetY()  > $pdf->tcpdf->getPageHeight() - $pdf->margin_bottom ) {
                $pdf->tcpdf->AddPage();
            }
            $this->ci->pdf->writeHTML($lineHTML);


        }




        $footer_h = 45;
        if( $pdf->tcpdf->GetY()  > $pdf->tcpdf->getPageHeight() -$footer_h ) {
            $pdf->tcpdf->AddPage();
        }

        $pdf->tcpdf->SetY( $pdf->tcpdf->getPageHeight() -$footer_h );
        $this->ci->pdf->writeHTML('<div>AMOUNT IN WORD:'.price_in_words( abs($from_trans['settled_amount']),ST_CUSTPAYMENT).'</div>');
//         $pdf->tcpdf->Write(14,'AMOUNT IN WORD: '.price_in_words( abs($from_trans['settled_amount']),ST_CUSTPAYMENT));
        $pdf->tcpdf->SetY( $pdf->tcpdf->GetY()+ 3);
        $this->ci->pdf->write_view('footer/prepared_approve_receive');
//         $pdf->tcpdf->writeHTML($pdf->css.$this->ci->view('reporting/footer/prepared_approve_receive',null,true));



    }

    function print_bank_trans($pdf,$trans_type=ST_BANKDEPOSIT,$from_trans){
        $data = array('company'=>$pdf->company,'title'=>$pdf->title);

        $data['trans_date'] =sql2date($from_trans['trans_date']);
        $data['trans_no'] = $from_trans['trans_no'];
        $data['reference'] = $from_trans['ref'];
        $data['payee'] = '';
        $data['payment_from'] = $from_trans['bank_account_name'];
        $data['cheque'] = $from_trans['cheque'];
        // bug($from_trans);die;
        //         $data['gl_trans'] = $this->gl_trans_model->get_gl_trans($trans_type, $data['trans_no']);

//         $pdf->tcpdf->SetFillColor(255, 255, 255);
        //         bug($data);die;

        $html = '<table style="width: 100%;"><tr style="height: 70px"><td>'
            .( (isset($pdf->company['logo']) && $pdf->company['logo'] !='')? '<img src="' .$pdf->company['logo'].'" alt="A2000 solusion" height="50" border="0" >' : '<h2>'.$pdf->company['name'].'</h2>' )
            .'</td></tr><tr><td align="right"><h1 style="padding: 0; margin: 0;" >'.$pdf->title.'</h1></td></tr></table>';

        $pdf->tcpdf->header_bank_trans = $html;
        $pdf->tcpdf->bank_trans_data = $data;
//         $pdf->tcpdf->writeHTML($pdf->css.$html);
//         $pdf->tcpdf->Line( $pdf->margin_left, $pdf->tcpdf->GetY(),$pdf->tcpdf->getPageWidth()-$pdf->margin_right, $pdf->tcpdf->GetY() -2, array('width' =>0.5,'color' => array(128, 128, 128)));

//         $this->add_bank_pdf_info($pdf->tcpdf,'DATE',sql2date($from_trans['trans_date']));
//         $this->add_bank_pdf_info($pdf->tcpdf,'TRANSACTION NO','#'.$from_trans['trans_no']);
//         $this->add_bank_pdf_info($pdf->tcpdf,'REFERENCE NO',$from_trans['ref']);
//         if( $trans_type== ST_BANKPAYMENT ){
//             $this->add_bank_pdf_info($pdf->tcpdf,'PAYEE',payment_person_name($from_trans['person_type_id'], $from_trans['person_id']));
//         } else {
//             $this->add_bank_pdf_info($pdf->tcpdf,'PAYEER',$pdf->company['name']);
//         }

        if( $trans_type== ST_BANKPAYMENT ){

            $pdf->tcpdf->bank_trans_data['payee'] = payment_person_name($from_trans['person_type_id'], $from_trans['person_id']);
            //             $this->add_bank_pdf_info($pdf->tcpdf,'PAYEE',);
        } else {
            $pdf->tcpdf->bank_trans_data['payeer'] = $pdf->company['name'];
            //             $this->add_bank_pdf_info($pdf->tcpdf,'PAYEER',$pdf->company['name']);
        }

//         $this->add_bank_pdf_info($pdf->tcpdf,'PAYMENT FROM',$from_trans['bank_account_name']);
//         $this->add_bank_pdf_info($pdf->tcpdf,'Cheque Number',$from_trans['cheque']);

//         $pdf->tcpdf->Line( $pdf->margin_left, $pdf->tcpdf->GetY()+3,$pdf->tcpdf->getPageWidth()-$pdf->margin_right, $pdf->tcpdf->GetY()+1, array('width' =>0.5,'color' => array(128, 128, 128)));

//         $pdf->tcpdf->Write(15,'BEING PAYMENT FOR');




        $pdf->tcpdf->item_table_header = '<table class="tablestyle" cellpadding=2 cellspacing=0>

        <tr>
        	<td class="tableheader textcenter" style="width: 20%;" >Account Code</td>
            <td class="tableheader textleft" style="width: 35%;" >Account Name</td>
            <td class="tableheader textright" style="width: 10%;" >Debit</td>
            <td class="tableheader textright" style="width: 10%;" >Credit</td>
            <td class="tableheader"  style="width: 25%;"  >Memo</td>
            </tr>
		</table>';
        //         $pdf->tcpdf->writeHTML($pdf->css.$html);


        //
        //         $pdf->tcpdf->Line( $pdf->margin_left, $pdf->tcpdf->GetY(),$pdf->tcpdf->getPageWidth()-$pdf->margin_right, $pdf->tcpdf->GetY() , array('width' =>0.5,'color' => array(128, 128, 128)));

        //         $this->add_bank_pdf_info($pdf->tcpdf,'DATE',sql2date($from_trans['trans_date']));
        //         $this->add_bank_pdf_info($pdf->tcpdf,'TRANSACTION NO','#'.$from_trans['trans_no']);
        //         $this->add_bank_pdf_info($pdf->tcpdf,'REFERENCE NO',$from_trans['ref']);


        $pdf->tcpdf->AddPage();
//         $gl_trans = $this->ci->view('reporting/bank_payment_items', array('gl_trans'=>$this->gl_trans_model->get_gl_trans($trans_type, $data['trans_no'])) ,true);
        $gl_trans_items = $this->gl_trans_model->get_gl_trans($trans_type, $data['trans_no']);
        foreach ($gl_trans_items AS $tran){
            $debit = $credit = 0;

            if ($tran->amount > 0) {
                $debit = $tran->amount;
            } else {
                $credit = abs($tran->amount);
            }


            $lineHTML = '<table><tr>
	        	<td class="textcenter" style="width: 20%;">'.$tran->account.'</td>
	        	<td style="width: 35%;">'.$tran->account_name.'</td>
	        	<td class="textright" style="width: 10%;">'.number_format2($debit,user_amount_dec()).'</td>
	        	<td class="textright" style="width: 10%;">'.number_format2($credit,user_amount_dec()).'</td>
	        	<td style="width: 25%;" >'.$tran->memo_.'</td>
	            </tr></table>';
            $pdf->tcpdf->SetY($pdf->tcpdf->GetY() - 4.5);

            if( $pdf->tcpdf->GetY()  > $pdf->tcpdf->getPageHeight() - $pdf->margin_bottom ) {
                $pdf->tcpdf->AddPage();
            }
            $pdf->tcpdf->writeHTML($pdf->css.$lineHTML);

        }
//         $pdf->tcpdf->SetY( $pdf->tcpdf->GetY()+12 );
//         $pdf->tcpdf->writeHTML($pdf->css.$gl_trans);



        $footer_h = 45;
        if( $pdf->tcpdf->GetY()  > $pdf->tcpdf->getPageHeight() -$footer_h ) {
            $pdf->tcpdf->AddPage();
        }

        $pdf->tcpdf->SetY( $pdf->tcpdf->getPageHeight() -$footer_h );
        $pdf->tcpdf->Write(15,'AMOUNT IN WORD: '.price_in_words( abs($from_trans['settled_amount']),ST_CUSTPAYMENT));
        $pdf->tcpdf->SetY( $pdf->tcpdf->GetY()+ 13);
        $pdf->tcpdf->writeHTML($pdf->css.$this->ci->view('reporting/footer/prepared_approve_receive',null,true));
    }

    private function add_bank_pdf_info($pdf,$title,$content){
        $pdf->SetY( $pdf->GetY()+2 );
        $pdf->SetFont('','B');
        $pdf->MultiCell(50, 0, $title, 0, 'L', 1, 2,  $pdf->GetX() , $pdf->GetY());
        $pdf->SetFont('');
        $pdf->MultiCell(0, 0, ': '.$content, 0, 'L', 0, 1, $pdf->GetX() ,$pdf->GetY()-5.5, true, 0);
	}

	function payments_print($pdf){
	    die('go here bank_transactions 310');
	    $model = $this->ci->model('bank',true);
	    $gl_model = $this->ci->model('gl_trans',true);

	    $rep_type = 0;
       	$rep_type = $pdf->inputVal('type');

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
// bug('$trans_type='.$trans_type);

       	$where = array('bt.type'=>$trans_type);

       	if($pdf->inputVal('start_date')){
       	    $where['bt.trans_date >='] = $pdf->inputVal('start_date');
       	}
       	if($pdf->inputVal('end_date')){
       	    $where['bt.trans_date <='] = $pdf->inputVal('end_date');
       	}
       	if($pdf->inputVal('account')){
       	    $where['bt.bank_act'] = $pdf->inputVal('account');
       	}

       	if($pdf->inputVal('trans_no')){
       	    $where['bt.trans_no'] = $pdf->inputVal('trans_no');
       	}
       	if($pdf->inputVal('trans_no')){
       	    $where['bt.trans_no'] = $pdf->inputVal('trans_no');
       	}

       	if($pdf->inputVal('ref')){
       	    $where['bt.ref'] = $pdf->inputVal('ref');
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

       	$limit = 1;
       	if( $trans && count($trans) >0 ){ foreach ($trans AS $tran){
//             if ( $limit > 3 ) break;
       	    $receipt = get_customer_trans($tran->trans_no, $trans_type);

       	    self::print_bank_trans($pdf,$trans_type,(array)$tran);
       	    $limit++;

//        	    bug($tran); die;
       	}}
//        	$pdf->tcpdf->Output('I');
//        	die('iam here');
//        	die('go here');
       	$pdf->do_report(true,true);
//        	die('go here');

	}

}