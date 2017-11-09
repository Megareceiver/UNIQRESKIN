<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BankReport {

    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->db = $ci->db;

        if( !isset($ci->pdf) ){
            $ci->load_library('reporting');
        }
        $this->tcpdf = $ci->pdf->tcpdf;
        $this->pdf = $ci->pdf;

        $this->bank_trans_model = module_model_load('trans','bank');
        $this->gl_trans_model = module_model_load('trans','gl');
    }

    function payment_print(){

        $trans_no = input_val('trans_no');
        if( !$trans_no ){
            $trans_no = input_val('PARAM_0');
        }

        $trans_type= ST_BANKPAYMENT;


        $trans = $this->bank_trans_model->get_bank_trans($trans_type, $trans_no);



        $tran_item = $trans[0];

        $html = '<table style="width: 100%;"><tr style="height: 70px"><td>'
            .( (isset($pdf->company['logo']) && $pdf->company['logo'] !='')? '<img src="' .$pdf->company['logo'].'" alt="A2000 solusion" height="50" border="0" >' : '<h2>'.$pdf->company['name'].'</h2>' )
        .'</td></tr><tr><td align="right"><h1 style="padding: 0; margin: 0;" >'.$pdf->title.'</h1></td></tr></table>';

        $this->tcpdf->header_bank_trans = $html;
        $this->tcpdf->bank_trans_data = array(
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
        		$this->tcpdf->bank_trans_data['payment_to'] = html_entity_decode($customer->debtor_ref);
        		$branch = $this->db->where(array('branch_code'=>$debtor_trans->branch_code))->get('cust_branch')->row();
        		if( $branch && isset($branch->br_name) ){
        			$this->tcpdf->bank_trans_data['payment_to'] .= ' / '.html_entity_decode($branch->branch_ref);
        		}
        		break;
        	case 3:
        		$suppliers = $this->db->where(array('supplier_id'=>$tran_item->person_id))->get('suppliers')->row();
        		if( $suppliers && isset($suppliers->supp_name) ){
        			$this->tcpdf->bank_trans_data['payment_to'] = html_entity_decode($suppliers->supp_name);
        		}
        		break;
        	default:
        		$pdf->tcpdf->bank_trans_data['payment_to'] = html_entity_decode($tran_item->person_id);
        		break;

        }

        if( $trans_type==ST_BANKDEPOSIT ){
        	$pay_to = $pdf->tcpdf->bank_trans_data['payment_to'];
        	$this->tcpdf->bank_trans_data['payment_to'] = $pdf->tcpdf->bank_trans_data['payment_from'];
        	$this->tcpdf->bank_trans_data['payment_from'] = $pay_to;
        }


        $this->tcpdf->item_table_header = '<table class="tablestyle" cellpadding=2 cellspacing=0>

        <tr>
        	<td class="tableheader textcenter" style="width: 20%;" >Account Code</td>
            <td class="tableheader textleft" style="width: 35%;" >Account Name</td>
            <td class="tableheader textright" style="width: 10%;" >Debit</td>
            <td class="tableheader textright" style="width: 10%;" >Credit</td>
            <td class="tableheader"  style="width: 25%;"  >Memo</td>
            </tr>
		</table>';

        $this->tcpdf->AddPage();

        $gl_trans_items = $this->gl_trans_model->get_gl_trans($trans_type, $tran_item->trans_no);

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
            $this->tcpdf->SetY($this->tcpdf->GetY() - 4.5);

            if( $this->tcpdf->GetY()  > $this->tcpdf->getPageHeight() - $this->margin_bottom ) {
                $this->tcpdf->AddPage();
            }
            $this->pdf->writeHTML($lineHTML);
//             die('aaa');
//             $pdf->tcpdf->writeHTML($pdf->css.$lineHTML);

        }




        $footer_h = 45;
        if( $this->tcpdf->GetY()  > $this->tcpdf->getPageHeight() -$footer_h ) {
            $this->tcpdf->AddPage();
        }

        $this->tcpdf->SetY( $this->tcpdf->getPageHeight() -$footer_h );
        $this->ci->pdf->writeHTML('<div>AMOUNT IN WORD:'.price_in_words( abs($from_trans['settled_amount']),ST_CUSTPAYMENT).'</div>');
//         $pdf->tcpdf->Write(14,'AMOUNT IN WORD: '.price_in_words( abs($from_trans['settled_amount']),ST_CUSTPAYMENT));
        $this->tcpdf->SetY( $this->tcpdf->GetY()+ 3);
        $this->ci->pdf->write_view('footer/prepared_approve_receive');

    }
}