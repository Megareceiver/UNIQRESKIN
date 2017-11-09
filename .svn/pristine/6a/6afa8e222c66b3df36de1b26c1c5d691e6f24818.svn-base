<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SalesReportInvoice  extends ci {
    function __construct() {
//         $ci = get_instance();
        $this->customer_trans_model = $this->model('customer_trans',true);
        $this->bank_model = $this->model('bank_account',true);
        $this->contact_model = $this->model('crm',true);
        $this->sys_model = $this->model('config',true);
        $this->sale_order_model = $this->model('sale_order',true);

        $this->report = module_control_load('report','report');
    }


    public function invoice_print(){

        $from = 		input_val('PARAM_0');
        $to = 			input_val('PARAM_1');
        $fno = explode("-", $from);
        $tno = explode("-", $to);
        $from = min($fno[0], $tno[0]);
        $to = max($fno[0], $tno[0]);

        $trans_where = array();
        $currency = 	input_val('PARAM_2');
        $email = 		input_val('PARAM_3');
        $pay_service = 	input_val('PARAM_4');
        $comments = 	input_val('PARAM_5');
        $customer = 	input_val('PARAM_6');

        $orientation = 	input_val('PARAM_7') ? 'L' : 'P' ;


        $start_date =	input_val('PARAM_8');
        if( $start_date ){
            $trans_where['tran_date >='] = date('Y-m-d',strtotime($start_date));
        }

        $end_date = 	input_val('PARAM_9');
        if( $end_date ){
            if( $start_date &&  strtotime($start_date) < strtotime($end_date))
                $trans_where['tran_date <='] = date('Y-m-d',strtotime($end_date));
        }

        $reference = input_val('PARAM_10');
        if( $reference ){
            $trans_where['reference'] = $reference;
        }


        $this->rep = $this->report->front_report(_('TAX INVOICE'),$this->invoice_report_table,ST_SALESINVOICE);

        $this->rep->SetHeaderType('TemplateInvoice');

        if( input_val('docx') ){
            return $this->docx_print($from,$to,$trans_where);
        }

        for ($i = $from; $i <= $to; $i++) {

            if (!exists_customer_trans(ST_SALESINVOICE, $i))
                continue;

            $cus_trans = $this->customer_trans_model->search_invoice(ST_SALESINVOICE,$i,$trans_where);

            if( empty($cus_trans) || !isset($cus_trans->debtor_no) ) {
                //|| ( $customer && $cus_trans->debtor_no != $customer)
                continue;
            }

           if( input_val('theme')=='invoice' ){
                $this->invoice_tax($cus_trans);
            } else {

                $this->pdf_print($cus_trans);
            }
        }

        $this->rep->End();

    }

    var $invoice_report_table = array(
        'space1'=>array(' ',2),
        'stock_id'=>array('Item Code',60),
        'description'=>array('Item Description' ,225),
        'qty'=>array('Quantity' ,300,'center'),
        'units'=>array('Units',325,'center'),
        'price'=>array('Unit Price',385,'right'),
        'discount_percent'=>array('Discount %',450,'right'),
        'total'=>array('Total',515,'right'),

    );

    private function pdf_print($tran=NULL){

        $this->rep->params = array(
            'comments' => input_val('PARAM_5'),
            'bankaccount'=>0,
            'tran_date'=>sql2date($tran->tran_date),
            'reference'=>$tran->reference,
            'payment_terms'=>$tran->payment_terms_name,
            'delivery_info'=>array(
                'Order To'=>$this->rep->print_company(),
                'Deliver To'=>$tran->DebtorName,
            ),
            'contact'=>$this->contact_model->get_branch_contacts($tran->branch_code,'invoice',$tran->debtor_no),
            'aux_info' => array (
                "Customer's Reference"          =>  $tran->cust_ref2,
                _( "Sales Person" )            =>  $this->contact_model->get_salesman($tran->salesman,'salesman_name'),
                _('Your GST no.')               =>  $tran->tax_id,
                _( "Trans No." )         =>  $tran->trans_no,
                _( "Due Date" )                =>  $tran->due_date ? sql2date($tran->due_date) : null,
            )
        );
        if( strlen($tran->address) > 0 ){
            $this->rep->params['delivery_info']['Deliver To'] .= "\n".$tran->address;
        }



        if( $tran->order_ ){
            $this->rep->params['aux_info']["Customer's Reference"] = $this->sale_order_model->get_field($tran->order_,'customer_ref');
        }

        $items = $this->customer_trans_model->trans_detail('*',array('debtor_trans_type'=>ST_SALESINVOICE,'debtor_trans_no'=>$tran->trans_no));
        if( empty($items) ){
            return;
        } else {
            $this->rep->NewPage();
            $sign = 1;
            $SubTotal = $discountTotal = $shippingTotal = $taxTotal = 0;
            $taxes = array();
            foreach ($items AS $detail){

                $line_price = $detail->unit_price * $detail->quantity;
                $Net = round2($sign * ((1 - $detail->discount_percent) * $line_price), user_price_dec());
                $discountTotal += $line_price -$Net;
                $SubTotal += $Net;

                if( $detail->tax_type_id ){
                    $tax = tax_calculator($detail->tax_type_id,$line_price,$tran->tax_included);

                    if( is_object($tax) ){
                        if( !isset($taxes[$detail->tax_type_id]) ){
                            $taxes[$detail->tax_type_id] = array('name'=>$tax->name ." (".$tax->code." ".$tax->rate."%)" ,'amount'=>0);
                        }
                        $taxes[$detail->tax_type_id]['amount'] += $tax->value;
                        $taxTotal += $tax->value;
                    }


                }


                $this->rep->TextCol(1, 2,   $detail->stock_id, -2);
				$this->rep->TextCol(2, 3,   wordwrap($detail->description,40,"\n",true), 0);
				$this->rep->TextCol(3, 4,	number_format2($sign*$detail->quantity,get_qty_dec($detail->stock_id)), -21);
				$this->rep->TextCol(4, 5,	$detail->units, -2);
				$this->rep->TextCol(5, 6,	number_total($detail->unit_price), -2);
				$this->rep->TextCol(6, 7,	number_total($detail->discount_percent*100) . "%" , -2);
				$this->rep->TextCol(7, 8,	number_total($Net));
				$this->rep->NewLine();
            }

            $this->rep->row = $this->rep->bottomMargin + 8.5 * $this->rep->lineHeight;


            $this->rep->aligns[3] = 'right';

            $this->rep->TextCol(1, 5,	$this->rep->company['curr_default'].":".price_in_words( $tran->tax_included ? $SubTotal :$SubTotal+$taxTotal ,ST_CUSTPAYMENT));

            $this->rep->Font('bold');
            $this->rep->TextCol(5, 7,	_('TOTAL INCL. GST'));
            $this->rep->TextCol(7, 8,	number_total( $tran->tax_included ? $SubTotal :$SubTotal+$taxTotal ));


            $this->rep->NewLine(-1);
            $this->rep->TextCol(3, 7,	_('TOTAL EX GST'));
            $this->rep->TextCol(7, 8,	number_total($tran->tax_included ? $SubTotal-$taxTotal: $SubTotal));


            $this->rep->Font();
            if( abs($discountTotal) != 0 ){
                $this->rep->NewLine(-1);
                $this->rep->TextCol(3, 7,	_('Discount Given'));
                $this->rep->TextCol(7, 8,	number_total($discountTotal));
            }



            if( count($taxes) > 0 ) foreach ($taxes AS $tax){
                if( abs($tax['amount']) != 0 ){
                    $this->rep->NewLine(-1);
                    $this->rep->TextCol(3, 7,	$tax['name'].' Amount');
                    $this->rep->TextCol(7, 8,	number_total($tax['amount']) );
                }
            }

            if( abs($shippingTotal) != 0 ){
                $this->rep->NewLine(-1);
                $this->rep->TextCol(3, 7,	_('Shipping'));
                $this->rep->TextCol(7, 8,	number_total($shippingTotal));
            }


            $this->rep->NewLine(-1);
            $this->rep->TextCol(3, 7,	_(' Sub-total'));
            $this->rep->TextCol(7, 8,	number_total($SubTotal));


        }




    }

    private function pdf_print2($customer_from=0,$customer_to=0,$trans_where=null){
        $pdf = get_instance()->reporting;
        $currency_default = $this->sys_model->curr_default();

        for ($i = $customer_from; $i <= $customer_to; $i++) {

            if (!exists_customer_trans(ST_SALESINVOICE, $i))
                continue;

            $sign = 1;
            $cus_trans = $this->customer_trans_model->search_invoice(ST_SALESINVOICE,$i,$trans_where);

            if( empty($cus_trans) || !isset($cus_trans->debtor_no)  ) {
                continue;
            }
            $this->bankacc = $this->bank_model->get_default_account($cus_trans->curr_code);
            $pdf->items = $this->customer_trans_model->trans_detail('*',array('debtor_trans_type'=>ST_SALESINVOICE,'debtor_trans_no'=>$i));
            if( empty($pdf->items) ){
                continue;
            }

            if( input_val('theme')=='invoice' ){
                return $this->invoice_tax($cus_trans);
            } else {

                $pdf->items_view = array(
                    'stock_id'=>array('title'=>'Item Code','w'=>15),
                    'description'=>array('title'=>'Item Description' ,'w'=>35),
                    'qty'=>array('title'=>'Quantity' ,'w'=>10,'class'=>'textcenter'),
                    'units'=>array('title'=>'Units','w'=>10,'class'=>'textright'),
                    'price'=>array('title'=>'Unit Price','w'=>15,'class'=>'textright'),
                    'discount_percent'=>array('title'=>'Discount %','w'=>15,'class'=>'textcenter'),
                );
                $pdf->content_view = 'content-invoice';
                $contacts = $this->contact_model->get_branch_contacts($cus_trans->branch_code,'invoice',$cus_trans->debtor_no);
                $pdf->order = array(
                    'curr_code'=>$cus_trans->curr_code,
                    'debtor'=>null,
                    'debtor_no'=>null,
                    'name'=>$cus_trans->DebtorName,
                    'address'=>$cus_trans->address,
                    'date'=>date('d-m-Y',strtotime($cus_trans->tran_date)),
                    'contact' => (array)$contacts,
                    'invoice_no'=>$cus_trans->reference,
                    'reference'=>$cus_trans->reference,
                    'tax_included'=>$cus_trans->tax_included,
                    'payment_terms'=>$cus_trans->payment_terms_name,
                    'shipping'=>$cus_trans->ov_freight

                );
                $pdf->order_html = get_instance()->view('reporting/order/invoice',$pdf->order,true);

                $deliveries = get_sales_parent_numbers ( ST_SALESINVOICE, $cus_trans->trans_no );
                foreach ( $deliveries as $n => $delivery ) {
                    $deliveries [$n] = get_reference ( ST_CUSTDELIVERY, $delivery );
                }

                $customer_ref = null;
                if( $cus_trans->order_ ){
                    $customer_ref = $this->sale_order_model->get_field($cus_trans->order_,'customer_ref');
                }
                $aux_info = array (
                    _ ( "Customer's Reference" ) => array('w'=>20,'val'=>$customer_ref),
                    _ ( "Sales Person" ) => 		array('w'=>20,'val'=>$this->contact_model->get_salesman($cus_trans->salesman,'salesman_name')),
                    _('Your GST no.')=>				array('w'=>20,'val'=>$cus_trans->tax_id),
                    _ ( "Our Invoice No." ) => array('w'=>20,'val'=>$cus_trans->trans_no),
                    _ ( "Due Date" ) => 			array('w'=>20,'val'=> $cus_trans->due_date ? sql2date($cus_trans->due_date) : null  ),
                );
                $pdf->author_html = get_instance()->view('reporting/aux_info',array('items'=>$aux_info),true);

                $pdf->make_report();
            }

            $limit++;
        }

    }

    /*
     * output PDF for Kastam 150919
     */
    private function invoice_tax($invoice){
        $ci = get_instance();

        if( !isset($ci->pdf) ){
            $ci->load_library('reporting');
        }
        $this->tcpdf = $ci->pdf->tcpdf;
        $this->ci = $ci;

        $this->tcpdf->startPageGroup();
        //         $this->tcpdf->SetTopMargin(0);
        $margin = 3;
        $this->tcpdf->SetMargins($margin,$margin+2,$margin, true);


        $this->tcpdf->AddPage();


        $this->tcpdf->setPrintHeader(false);

        $this->tcpdf->SetAutoPageBreak(TRUE,1);
        $pdf = $this->ci->reporting;
        //         bug($invoice);die;

        $gst_6_round = round($invoice->ov_gst,user_amount_dec());
        $data = array(
            'trans_no'=>$invoice->reference,
            'tran_date'=>$invoice->tran_date,
            'saleman'=>$invoice->salesman_name,
            'items'=>$this->ci->reporting->items,
            'gst_6'=>$gst_6_round,
            'rounding_adj'=>$invoice->ov_gst-$gst_6_round,
            'amount_total'=>$invoice->ov_amount + $gst_6_round,
            'y'=>$this->tcpdf->GetY()

        );
        $this->tcpdf->SetFont('algerian','',9);
        //         $this->ci->pdf->writeHTML('<p style=" background-color: red;" >'.$this->ci->pdf->company['name'].' bc ddd</p>');
        $this->tcpdf->Cell(0, 0, strtoupper(trim($this->ci->pdf->company['name'])), 0, 1, 'C', 0);
        //         bug($this->ci->pdf); die;
        $this->ci->pdf->UpdateY(-3);
        $this->tcpdf->SetFont('pdfacourier', '', 5);

        $this->ci->smarty->assign('content_w',95);
        $this->ci->pdf->write_view('sale_invoice/mixed_supplies_with_discounts',$data);
    }

    private function docx_print($customer_from=0,$customer_to=0,$trans_where=null){
        $report = get_instance()->reporting;


        $save_dir = check_dir(COMPANY_DIR.'/docx/');
        $file_save = "$save_dir/sale-invoice-".date("Ymd-H-i-s").".docx";
        $file_template = DOCX_REPORT_TEMP.'sale-invoice.zip';

        $PHPWord = new \PhpOffice\PHPWord\PHPWord();
//         $section = $PHPWord->createSection();

$customer_from = 1;
$customer_to = 2;
//         $counter=1;
        for ($i = $customer_from; $i <= $customer_to; $i++) {

            $section = $PHPWord->createSection();
            $template = $PHPWord->loadTemplate($file_template);
            $template->setValue('Companyname', $report->company['name']);
            $template->setValue('Companyname', $report->company['name']);
            $template->setValue('company_no', $report->company['coy_no']);
            $template->setValue('company_address', $report->company['address']);
            $template->setValue('company_phone', $report->company['phone']);
            $template->setValue('company_fax', $report->company['fax']);
            $template->setValue('company_gst', $report->company['gst_no']);

            $template->setValue('invoice_no', "");
            $section->addPageBreak();

        }


// bug($file_save);
// die('aa');
        $template->saveAs($file_save);

//         $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($PHPWord, 'Word2007');
//         $xmlWriter->save("$file_save");

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); //mime type
        header('Content-Disposition: attachment;filename="'.$file_save.'"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
//         $objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
//         $template->save('php://output');

        header('Content-Length: '.filesize($file_save));
        readfile($file_save);
    }
}