<?php
class BankReportPayment  {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->input = $ci->input;
        $this->db = $ci->db;

        $this->bank_transaction_model = $ci->model('bank_transaction',true);
        $this->gl_trans_model = module_model_load('trans','gl');

//         $this->bank_trans_model = $ci->model('bank_trans',true);
        $this->bank_trans_model = module_model_load('trans','bank');
        $this->customer_model = module_model_load('customer','sales');
        $this->sale_tran_model = module_model_load('trans','sales');
        $this->supplier_model = module_model_load('supplier','purchases');


        $this->report = module_control_load('report','report');

    }


    var $payment_report_table = array(
        'account'      =>array( 'Account Code'      ,90 ,'center'),
        'account_name' =>array( 'Account Name',230 ,'left'),
        'debit'     =>array( 'Debits'       ,300 ,'right'),
        'credit'    =>array( 'Credits'      ,415 ,'right'),
        'memo'      =>array( 'Memo',525,'center'),
    );

    function payment_print($pdf,$trans_type=ST_BANKDEPOSIT){
        $orientation = "P";

        $trans_no = input_val('trans_no');
        if( strlen($trans_no) < 1 ){
            $trans_no = input_val('PARAM_0');
        }

        $where = array();

        $start_date = input_val("start_date");
        if($start_date){
            $where['bt.trans_date >='] = date2sql($start_date);
        }
        $end_date = input_val("end_date");
        if($end_date){
            $where['bt.trans_date <='] = date2sql($end_date);
        }
        $account = input_val('account');
        if($account){
            $where['bt.bank_act'] = $account;
        }


        switch ($trans_type){
            case ST_BANKPAYMENT:
                $report_title = 'Bank Payment Voucher';
                break;
            case ST_BANKDEPOSIT:
                $report_title = 'Bank Deposit Voucher';
                break;
            case ST_BANKTRANSFER:
                $report_title = 'Bank Transfer Voucher';
                break;
            default:$report_title = NULL;break;
        }



        $this->rep = $this->report->front_report(_($report_title),$this->payment_report_table);
        $this->rep->SetHeaderType('BankVoucher');
        $this->rep->fontSize = 11;
        $this->footer_height = 70 + $this->rep->lineHeight;

        if( $trans_no  ){
            $tran = $this->bank_trans_model->get_tran($trans_type, $trans_no);

            $this->item_print($tran);
        } elseif( !empty($where) ){
            $trans = $this->bank_trans_model->get_trans($where);
            if( count($trans) >0 ) foreach ($trans AS $tran){
                $this->item_print($tran);
            }
        }

        $this->rep->End();

    }

    private function item_print($tran=NULL){
        if( !is_object($tran) )
            return;

        $payment_to = "";
        $payment_from = $tran->bank_account_name;

        switch ($tran->person_type_id) {
            case 2:
                $sale_tran = $this->sale_tran_model->get_tran($tran->type,$tran->trans_no);
                $customer = $this->customer_model->get_detail($sale_tran->debtor_no);
                if( isset($customer->debtor_ref) ){
                    $payment_to = trim($customer->debtor_ref);
                }
                if( isset($sale_tran->branch_detail->branch_ref) ){
                    if( strlen($payment_to) > 0 ){
                        $payment_to .= "/";
                    }
                    $payment_to .= $sale_tran->branch_detail->branch_ref;
                }
                break;
            case 3:
                $supplier = $this->supplier_model->get_detail($tran->person_id);
                if( is_object($supplier) && isset($supplier->supp_name) ){
                    $payment_to = $supplier->supp_name;
                }
                break;
            default:
                $payment_to= $tran->person_id;
                break;
        }

        if( $tran->type==ST_BANKDEPOSIT ){
            $pay_change = $payment_to;
            $payment_to = $payment_from;
            $payment_from = $pay_change;
        }

        $this->rep->params = array("",
            "Date" => sql2date($tran->trans_date),
            'TRANSACTION NO'=>"#".$tran->trans_no,
            'REFERENCE NO'=>$tran->ref,
            'PAYMENT FROM'=>html_entity_decode($payment_from),
            'PAYMENT TO'=>html_entity_decode($payment_to),
            'Cheque Number'=>html_entity_decode($tran->cheque)

        );

// for($i=0;$i<32;$i++) :
        $gl_trans = $this->gl_trans_model->get_gl_trans($tran->type, $tran->trans_no);
        if( count($gl_trans) <1 )
            return;


        $currency_originer = $tran->bank_curr_code;
        $exc_rate = get_exchange_rate_from_home_currency($currency_originer, $tran->trans_date);

        $this->rep->NewPage();
        $total_amount = 0;
        if( count($gl_trans) > 0 ) foreach ($gl_trans AS $row){
            $this->rep->NewLine();
            $debit = $credit = 0;
            if( $row->amount >0 ){
                $total_amount += $row->amount;
            }

            $col = 0;
            foreach ($this->payment_report_table AS $k=>$val){
                $txt = NULL;
                switch ($k){
                    case 'debit':
                        $number = ( $row->amount > 0 ) ? $row->amount : 0;

                        $txt = $number > 0 ? number_total($number/$exc_rate) : null;
                        break;
                    case 'credit':
                        $number = ( $row->amount <0 ) ? abs($row->amount) : 0;
                        $txt = $number > 0 ? number_total($number/$exc_rate) : null;
                        break;
                    case 'memo':
                        $txt = (isset($row->memo_)) ? $row->memo_ : NULL;
                        break;
                    default:
                        if( isset($row->$k) ){
                            $txt = $row->$k;
                        }
                        break;
                }
                $this->rep->TextCol($col, $col += 1, $txt);

            }
        }
// endfor;
        if( $this->rep->row <= $this->rep->bottomMargin + $this->footer_height ){
            $this->rep->NewPage();
        }
        $this->report_footer($total_amount);
    }

    var $footer_height = 0;
    function report_footer($total_amount=0){


        $block_height = 70;

        $footerRow = $this->rep->bottomMargin + $this->footer_height;

        $this->rep->TextWrap($this->rep->leftMargin,$footerRow,$this->rep->pageWidth ,_('AMOUNT IN WORD:').price_in_words( $total_amount ,ST_CUSTPAYMENT));

        $block_width = ($this->rep->pageWidth - $this->rep->leftMargin - $this->rep->rightMargin) / 3 - 10;

        $footerRow -= $this->rep->lineHeight;
        $this->rep->addTextWrap(
            $this->rep->leftMargin, $footerRow - $block_height,
            $width = $block_width,
            $height= $block_height,
            NULL,NULL,1 );

        $this->rep->addTextWrap(
            $this->rep->leftMargin, $footerRow - $block_height,
            $width = $block_width,
            $height= $this->rep->lineHeight +10,
            "Prepared By","C" );


        $this->rep->addTextWrap(
            $this->rep->leftMargin + $block_width + 15, $footerRow - $block_height,
            $width = $block_width,
            $height= $block_height,
            NULL,NULL,1 );

        $this->rep->addTextWrap(
            $this->rep->leftMargin + $block_width + 15, $footerRow - $block_height,
            $width = $block_width,
            $height= $this->rep->lineHeight +10,
            "Approved By",
            "C"
        );

        $this->rep->addTextWrap(
            $this->rep->leftMargin + $block_width*2 + 30, $footerRow - $block_height,
            $width = $block_width,
            $height= $block_height,
            NULL,NULL,1 );

        $this->rep->addTextWrap(
            $this->rep->leftMargin + $block_width*2 + 30, $footerRow - $block_height,
            $width = $block_width,
            $height= $this->rep->lineHeight +10,
            "Received By",
            "C"
        );
    }

    private function item_print2(){


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

            if( $pdf->tcpdf->GetY()  > $pdf->tcpdf->getPageHeight() - $this->ci->pdf->margin_bottom ) {
                $pdf->tcpdf->AddPage();
            }
            $this->ci->pdf->writeHTML($lineHTML);


        }


        $footer_h = 45;
        if( $pdf->tcpdf->GetY()  > $pdf->tcpdf->getPageHeight() -$footer_h ) {
            $pdf->tcpdf->AddPage();
        }

        $pdf->tcpdf->SetY( $pdf->tcpdf->getPageHeight() -$footer_h );
        $this->ci->pdf->writeHTML('<div>AMOUNT IN WORD:'.price_in_words( 0,ST_CUSTPAYMENT).'</div>');
        //         $pdf->tcpdf->Write(14,'AMOUNT IN WORD: '.price_in_words( abs($from_trans['settled_amount']),ST_CUSTPAYMENT));
        $pdf->tcpdf->SetY( $pdf->tcpdf->GetY()+ 3);
        $this->ci->pdf->write_view('footer/prepared_approve_receive');
        //         $pdf->tcpdf->writeHTML($pdf->css.$this->ci->view('reporting/footer/prepared_approve_receive',null,true));

    }

}