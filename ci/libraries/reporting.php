<?php
class reporting {
	var $css = null;


	function __construct($front_report=false){
		global $ci;
		$this->ci = $ci;
        if ( !$front_report && in_array(input_val('REP_ID'), array(801,802,803,102,107,109,110,112,113,209,210,211,601)) ){
            $this->ci->front_report = $front_report = true;
        }
        if( $front_report != true ){
            if( !isset($ci->pdf) ){
                $ci->load_library('pdf');
            }

            if( isset($ci->pdf) ){

                $this->tcpdf = $ci->pdf->tcpdf;
                $this->pdf = $ci->pdf;
                $ci->controller();
            }

            self::company_get();
            $this->bank_model = $this->ci->model('bank_account',true);
            $this->contact_model = $this->ci->model('crm',true);
            $this->config_model = $this->ci->model('config',true);
        }
	}

	function get_items($rep_type){

	    $this->report_type = $rep_type;
	    $this->content_view = 'content';

	    switch ($rep_type) {
	        case 101:
	            $customer_balance = module_control_load('report/customer_balance','sales');
	            $customer_balance->balance_print();
	            break;
            case 102:
                $this->title = 'Aged Customer Analysis';
                $aged_analysis = module_control_load('report/aged_analysis','sales');
                $aged_analysis->aged_analysis_print();

                break;
	        case 107:
	            $sale_invoice_report = module_control_load('report/invoice','sales');
	            $this->title = 'TAX INVOICE';
	            $sale_invoice_report->invoice_print();
	            break;
            case 108: /* debtor statement */
                $this->title = 'Statement Of Account';
                $this->ci->customer->statement_prints($this);
                break;
	        case 109:
	            $this->title = 'Sales Order';
	            $sale_order = module_control_load('report/order','sales');
	            $sale_order->order_print($this);
	            break;
            case 110:
                $this->title = 'DELIVERY NOTE';
                $sale_delivery = module_control_load('report/delivery','sales');
                $sale_delivery->delivery_print($this);
                break;
            case 111:
                $this->title = 'SALES QUOTATION';
                $sale_quotation = module_control_load('report/quotation','sales');
                $sale_quotation->quotation_print($this);
                break;
	        case 112:
	            $this->title = 'PAYMENT';
	            $sale_payment = module_control_load('report/payment','sales');
	            $sale_payment->customer_payment($this);
	            break;
            case 113:
                $this->title = 'CREDIT NOTE';
                $sale_credit = module_control_load('report/credit','sales');
                $sale_credit->cn_sale_print($this);
                break;
            case 201:
                $this->title = 'Supplier Balances';
                $supplier_balance = module_control_load('report/balance','supplier');
                $supplier_balance->balance_print();
                break;
            case 202:
                $this->title = 'Aged Supplier Analysis';
                $aged_analysis = module_control_load('report/aged_analysis','supplier');
                $aged_analysis->aged_analysis_print();

                break;
            case 209: // Purchase Order
                $this->title = 'PURCHASE ORDER';
                $purchase_order = module_control_load('report/order','purchases');
                $purchase_order->order_print();
                break;
            case 210:
                $this->title = 'REMITTANCE';
//                 $this->ci->purchase->credit_print($this);
                $purchase_credit = module_control_load('report/payment','purchases');
                $purchase_credit->payment_print();
                break;
            case 211:
                $this->title = 'Supplier Invoice';
//                 $returnPDF = $this->ci->purchase->invoice_print();
                $purchase_invoice = module_control_load('report/invoice','purchases');
                $purchase_invoice->invoice_print();
                break;

	        case 601:
	            $this->title = 'Bank Statement';
// 	            $this->ci->bank_transactions->statement_print($this);
	            $bank_report = module_control_load('report/statement','bank');
	            $bank_report->statement_print($this);
	            break;
	        case ST_BANKDEPOSIT:
	            $this->title = "Receipt Voucher";
	            break;
	        case ST_BANKTRANSFER:
	            $this->title = "Bank Transfer Voucher";
	            break;
	        case ST_JOURNAL:
	            $this->title = "Journal Voucher";
	            break;


            case 802:
                $this->title = 'RECEIPT VOUCHER';
                $bank_report = module_control_load('report/payment','bank');
                $bank_report->payment_print($this,ST_BANKDEPOSIT);
                break;
            case 801:
//                 die('reporting library 801');
            	$this->title = 'PAYMENT VOUCHER';
            	$bank_report = module_control_load('report/payment','bank');
                $bank_report->payment_print($this,ST_BANKPAYMENT);
                break;
            case 803:
                $this->title = 'Journal Voucher';
                $bank_report = module_control_load('report/payment','bank');
                $bank_report->payment_print($this,ST_BANKTRANSFER);
//                 $this->ci->bank_transactions->payment_print($this,ST_BANKTRANSFER);
                break;

	    }

	    if( !$this->ci->front_report ){
	        $this->ci->smarty->assign('title',$this->title);

	        if( isset($returnPDF) && $returnPDF===TRUE && !$docx ){
	            $this->make_report();
	        }
	    }



	}

	function inputVal($name=''){
		if( $this->ci->input->post($name) )
                {
			return $this->ci->input->post($name);
		}
                else if ( $this->ci->input->get($name) ){
			return $this->ci->input->get($name);
		}
                else {
			return NULL;
		}
	}

	var $company = array();

	function company_get(){
	    if( isset($this->pdf->company) ){
	        $this->company = $this->pdf->company;
	    }
	    $year = get_current_fiscalyear();
	    if ($year['closed'] == 0)
	        $how = _("Active");
	    else
	        $how = _("Closed");
	    $this->fiscal_year = sql2date($year['begin']) . " - " . sql2date($year['end']) . "  " . "(" . $how . ")";
	    $this->print_time = Today() . '   ' . Now();

	}

	function do_report($returnFile=true,$ajax=false){
	    if( get_instance()->front_report == true ){
	        return;
	    }
	    global $Ajax;
	    $docx = input_val('docx');

	    if( $docx ){

	    } else {

	        if( $this->tcpdf->getPage() < 1 ){
	            display_error(_("No data"));

	        } else {

	            $this->tcpdf->lastPage();
	            ob_end_clean();
	            check_dir(COMPANY_DIR.'/pdf_files/');

	            if( in_ajax()  ){ // || !$this->pdf->email
	                $file = '/pdf_files/report-'.time().'.pdf';
	                $this->tcpdf->Output(COMPANY_DIR.$file,'F');
	                // 		    if( $this->pdf->email ){
	                //                 $this->sendmail(ROOT.$file);
	                // 		    } else {

	                //$Ajax->redirect(site_url($file));
	                $Ajax->popup(COMPANY_ASSETS.'/'.$file);
	                // 		    }
	                // 	      die;
	            } else {

	                $this->tcpdf->Output('I');
	            }
	        }
	    }




	}

	function sendmail($file){
	    $sent = $try = 0;
	    $emails = "";
	    if(!$subject)
	        $subject = $this->formData['document_name'] . ' '. $this->formData['document_number'];
	    foreach($contactData as $contact) {

	        if (!isset($contact['email']))
	            continue;
	        $emailtype = true;
	        $this->SetLang($contact['lang']);

	        require_once($path_to_root . "/reporting/includes/class.mail.inc");
	        $mail = new email(str_replace(",", "", $this->company['coy_name']),
	            $this->company['email']);
	        $mail->charset = $this->encoding;

	        $to = str_replace(",", "", $contact['name'].' '.$contact['name2'])
	        ." <" . $contact['email'] . ">";
	        $msg = _("Dear") . " " . $contact['name2'] . ",\n\n"
	            . _("Attached you will find ") . " " . $subject ."\n\n";

	        if (isset($this->formData['payment_service']))
	        {
	            $amt = number_format($this->formData['document_amount'], user_price_dec());
	            $service = $this->formData['payment_service'];
	            $url = payment_link($service, array(
	                'company_email' => $this->company['email'],
	                'amount' => $amt,
	                'currency' => $this->formData['curr_code'],
	                'comment' => $this->title . " " . $this->formData['document_number']
	            ));
	            if ($url)
	                $msg.= _("You can pay through"). " $service: $url\n\n";
	        }

	        $msg .= _("Kindest regards") . "\n\n";
	        $sender = $this->user . "\n" . $this->company['coy_name'] . "\n" . $this->company['postal_address'] . "\n" . $this->company['email'] . "\n" . $this->company['phone'];
	        $mail->to($to); $try++;
	        $mail->subject($subject);
	        $mail->text($msg . $sender);
	        $mail->attachment($fname);
	        $emails .= " " . $contact['email'];
	        if ($mail->send()) $sent++;
	    } // foreach contact
	    unlink($fname);
	    $this->SetLang(user_language());
	    if (!$try) {
	        display_warning(sprintf(_("You have no email contact defined for this type of document for '%s'."), $this->formData['recipient_name']));
	    } elseif (!$sent)
	    display_warning($this->title . " " . $this->formData['document_number'] . ". "
	        . _("Sending document by email failed") . ". " . _("Email:") . $emails);
	    else
	        display_notification($this->title . " " . $this->formData['document_number'] . " "
	            . _("has been sent by email to destination.") . " " . _("Email:") . $emails);
	}


	var $page_num = array('num'=>0,'total'=>0);

	function make_report(){

		$headerData = array('company'=>$this->company);
		if( isset($this->order) ){
		    $headerData['order'] = $this->order;
		}

		$headerData['title'] = $this->title;
		$headerData['title_h'] = 60.5;
		$headerData['page_number'] = $this->tcpdf->getPageNumGroupAlias().'/'.$this->tcpdf->getPageGroupAlias();

		$this->ci->smarty->assign('content_w',$this->ci->pdf->width - $this->pdf->amount_w);

        switch ($this->report_type){
            case 110:
                $this->tcpdf->table_header_items = $this->ci->view('reporting/header/item-header-delivery',array('table'=>$this->items_view),true);
                $item_line_view = 'item/item-line-delivery';
                break;
            case 210:
                $this->tcpdf->table_header_items = $this->ci->view('reporting/item/header_credit_note',array('table'=>$this->items_view),true);
                $item_line_view = 'item/line';
                break;
            default:
                $amount_title = 'Amount';
                if( $this->report_type==112 ){
                    $amount_title = 'This Allocation';
                }
                $this->tcpdf->table_header_items = $this->ci->view('reporting/item/header',array('table'=>$this->items_view,'amount_title'=>$amount_title),true);
                $item_line_view = 'item/line';
                break;
        }


		$this->tcpdf->talbe_header = $this->ci->view('reporting/header',$headerData,true);
		$this->tcpdf->table_header_info = $this->ci->view('reporting/header_title',$headerData,true);


		if( $this->order_html ){
		    $this->tcpdf->table_header_order = $this->order_html;
		}

		/* begin refecence */
		if( isset($this->author_html) ){
		    $this->tcpdf->table_header_author = $this->author_html;
		}

		/* begin content */
// 		$contentData = array('table'=>$this->items_view,'items'=>$this->items,'amount_w'=>$this->ci->pdf->amount_w);

		if( isset($this->order['payment_terms']) ){
		    $this->tcpdf->table_header_payment_terms ='<p class="paymen_terms" >Payment Terms: '.$this->order['payment_terms'].'</p>';
		}




	    $this->tcpdf->startPageGroup();
        $this->tcpdf->AddPage();

		if( $this->report_type==210 || $this->report_type==112 ){
		    $this->tcpdf->SetY( $this->tcpdf->GetY()-6.7);
		    $this->pdf->write_view('item/line_first_creditnote', $line_data);

		}

		foreach ($this->items AS $ite) {
		    $line_class= null;
		    $new_page = false;

		    $lines_arr = preg_split('/\n|\r/',$ite->long_description);
		    $num_newlines = count($lines_arr) + 1;

		    if( $this->tcpdf->GetY()+$num_newlines*5> $this->tcpdf->getPageHeight() - $this->pdf->margin_bottom ) {
		        $this->footer_close();
		    }

		    $this->tcpdf->SetY( $this->tcpdf->GetY()-6.7);

		    if( $this->tcpdf->GetY()  < $this->tcpdf->getPageHeight()/2 ) {
		        $line_class = null;
		    }
		    if( isset($ite->gl_code) ){
		    	$ite->qty = 1;
		    }
		    $line_data = array('table'=>$this->items_view,'item'=>$ite,'line_class'=>$line_class);
		    $line_data['y'] = $this->tcpdf->GetY();
		    $line_data['h']=  $this->tcpdf->getPageHeight();

		    $this->pdf->write_view($item_line_view, $line_data);

            if( $this->report_type== 112 ) {
                $this->order['left_alloc'] += $ite->left_alloc;
            }
		}
		/* footer */
// 		if( $this->file->footer ){
            $this->footer();
// 		}

	}



	var $balance = 0;
	function make_statement_report(){

        $this->ci->smarty->assign('content_w',$this->pdf->width - $this->pdf->amount_w);

		$data = array();
		$data['table'] = $this->items_view;
		$data['page_number'] = $this->tcpdf->getPageNumGroupAlias().'/'.$this->tcpdf->getPageGroupAlias();
		$data['title'] = $this->title;
		if( isset($this->contacts) && isset($this->contacts->address) ){
		    $data['address'] = $this->contacts->address;
		}

		$this->tcpdf->talbe_header = $this->ci->view('reporting/header/statement',$data,true);
		$this->tcpdf->table_header_items = $this->ci->view('reporting/header/table-statement',array('table'=>$this->items_view),true);;
		$this->tcpdf->line_befor_content = false;
		$this->tcpdf->startPageGroup();
		$this->tcpdf->AddPage();
		$balance = 0;

// 		for ($i=1;$i<5;$i++)
		foreach ($this->items AS $ite) {
		    $debit = $credit = 0;
		    $new_page = false;
		    if( $this->tcpdf->GetY() > $this->tcpdf->getPageHeight() - $this->pdf->margin_bottom) {
		        $this->tcpdf->AddPage();
		    }
		    $line_data = array('table'=>$this->items_view,'item'=>$ite,'line_class'=>$line_class);
		    $line_data['y'] = $this->tcpdf->GetY();
		    $line_data['h']=  $this->tcpdf->getPageHeight();

		    if (isset($ite->deb_type) ){

		        if ($ite->deb_type=='IN') {
		            if( isset($ite->credit_amount) && $ite->credit_amount > 0 ){
		                $debit += $ite->credit_amount;
		                $balance += ($ite->credit_amount);
		            } else {
		                $debit += $ite->TotalAmount;
		                $balance += ($ite->TotalAmount);
		            }

                } else {
                    $credit += $ite->TotalAmount;
                    $balance -= $ite->TotalAmount;
                }

                if( isset($ite->Allocated) ){
                    $balance += $ite->Allocated*( $ite->deb_type=='IN' ? -1 : 1  );
                }

            } elseif (isset($ite->balance)) {
                $balance += $ite->balance;
                $debit += $ite->balance;
            }

            if( $credit <0 ){

                $debit+= abs($credit);
                $credit = 0;
            }

            if( $debit <0 ){
                $credit+= abs($debit);
                $debit = 0;
            }
            $line_data['credit'] = $credit;
            $line_data['debit'] = $debit;
            $line_data['balance'] = $balance;

		    $this->tcpdf->SetY( $this->tcpdf->GetY()-3);
		    $this->pdf->write_view('item/line-statement', $line_data );

		}

        if( $this->tcpdf->GetY() > 240 ){
            $this->tcpdf->AddPage();
        }

        $this->tcpdf->SetY(240);
		$this->pdf->write_view('footer/statement', array('balance'=>$balance,'details'=>$this->customer_details,'balance_word'=>$this->currency_name.': '.price_in_words($balance,ST_CUSTPAYMENT)) );
	}

	function make_report_invoice(){
	    $headerData = array('company'=>$this->company);
	    $headerData['order'] = $this->order;
	    $headerData['title'] = $this->title;

	    $headerData['title_h'] = 60.5;


// 	    $this->header_html =
// 	    $this->tcpdf->writeHTML($this->css.$this->header_html);
	    $this->tcpdf->talbe_header = $this->ci->view('reporting/header',$headerData,true);

	    $this->tcpdf->table_header_info = $this->ci->view('reporting/header_title',$headerData,true);

		$item_line_h = 5.7;


		if( $this->order_html ){
		    $this->tcpdf->table_header_order = $this->order_html;
// 			$this->tcpdf->writeHTML($this->css.$this->order_html);
		}
		/* begin refecence */
		if( isset($this->author_html) ){
		    $this->tcpdf->table_header_author = $this->author_html;
// 			$this->tcpdf->writeHTML($this->css.$this->author_html);
		}
		/* begin content */
		$contentData = array('table'=>$this->items_view,'items'=>$this->items,'amount_w'=>$this->amount_w);
		if( isset($this->order['payment_terms']) ){
			$this->tcpdf->SetY( $this->tcpdf->GetY()-5);
// 			$this->tcpdf->writeHTML($this->css.'<p class="paymen_terms" >Payment Terms: '.$this->order['payment_terms'].'</p>');
		}



// 		$line_data['content_w'] =  $this->file->width - $this->amount_w;
		if( $this->report_type==110 ){
		    $this->tcpdf->table_header_items = $this->ci->view('reporting/header/item-header-delivery',array('table'=>$this->items_view,'content_w'=>$this->ci->pdf->width - $this->ci->pdf->amount_w),true);

		} else {
		    $this->tcpdf->table_header_items = $this->ci->view('reporting/sale_invoice/item-header',array('table'=>$this->items_view,'content_w'=>$this->ci->pdf->width - $this->ci->pdf->amount_w),true);
		}

		$this->tcpdf->SetTopMargin(105);

		$this->tcpdf->AddPage();
// bug($this->items);die;
// 		$this->tcpdf->SetY( $this->tcpdf->GetY()-1.1);
// 		for( $i=1; $i<4;$i++ )
		foreach ($this->items AS $ite){
			$line_class= null;
// 			$item = $this->ci->view('reporting/sale_invoice/item-line',array('table'=>$this->items_view,'item'=>$ite),true);
			$new_page = false;

			$lines_arr = preg_split('/\n|\r/',$ite->long_description);
			$num_newlines = count($lines_arr) + 1;

			if( $this->tcpdf->GetY()+$num_newlines*7 > $this->tcpdf->getPageHeight() - $this->margin_bottom ) {
			    $this->footer_close();
			}

			$this->tcpdf->SetY( $this->tcpdf->GetY()-6.7);

			$line_data = array('table'=>$this->items_view,'item'=>$ite,'line_class'=>$line_class,'amount_w'=>$this->ci->pdf->amount_w);
			$line_data['content_w'] =  $this->ci->pdf->width - $this->ci->pdf->amount_w;
			$line_data['y'] = $this->tcpdf->GetY();
			$line_data['h']=  $this->tcpdf->getPageHeight();

			if( $this->report_type==110 ){
			    $view_file = 'item/item-line-delivery';
			} else {
			    $view_file = 'sale_invoice/item-line';
			}
			$this->pdf->write_view($view_file,$line_data);
		}

		/*
		 * print footer
		 */


		$amount_total = $sub_total =  0;
		foreach ($this->items AS $ite){
			$price = $ite->price*$ite->qty * (1-$ite->discount_percent);
			$tax_amount = 0;
			if( $ite->tax_type_id ){
				$tax = tax_calculator($ite->tax_type_id,$price,$this->order['tax_included']);
				if( $tax->gst_type ){
					if( !isset($this->taxes_gst[$tax->gst_type]  ) ){
						$this->taxes_gst[$tax->gst_type] = array('value'=>0,'name'=>$this->get_tax_type_name($tax->gst_type) );
					}
					$this->taxes_gst[$tax->gst_type]['value'] += $tax->value;
					$price = $tax->price ;
					$tax_amount = $tax->value ;
				}
			}
			$sub_total += $price;
			$amount_total += $tax_amount + $price;
		}

		if( $this->order['shipping'] > 0 ){
			$amount_total += $this->order['shipping'];
		}
		$footer_h = count($this->taxes_gst)*$item_line_h + 70;

		$footer_param = array(
			'taxes'=>$this->taxes_gst,
			'amount_total'=>$amount_total,
			'sub_total'=>$sub_total,
			'amount_w'=>$this->amount_w,
			'bank_account'=>( isset($this->bankacc->bank_account_number) ) ? $this->bankacc->bank_account_number : null,
			'border_top'=>false,
			'height'=> $footer_h,
			'y_current'=>  $this->file->y_current,
			'p_height'=>$this->file->height,
			'shipping'=>$this->order['shipping'],
			'legal_text'=> $this->config_model->get_sys_pref_val('legal_text'),
		    'order'=>$this->order

		);

		$footer_param['height'] = $this->tcpdf->getPageHeight() - $footer_h - $this->tcpdf->GetY();
		if( $this->tcpdf->getPageHeight() < $this->tcpdf->GetY() + $footer_h ) {

			$this->tcpdf->SetY( $this->tcpdf->GetY()-6.7 );

			$footer_blank = $this->ci->view('reporting/footer/blank',array('amount_w'=>$this->amount_w,'height'=>$this->tcpdf->getPageHeight() - $this->tcpdf->GetY() - 12, 'type'=>$this->report_type ) ,true);
			$this->tcpdf->writeHTML($this->css.$footer_blank);
			$this->tcpdf->AddPage();
			$this->tcpdf->writeHTML($this->css.$item_header);

		}


		$footer_param['height'] = $this->tcpdf->getPageHeight() - $footer_h - $this->tcpdf->GetY() ;
		$footer_param['content_w'] =  $this->pdf->width - $this->pdf->amount_w;

		if( in_array($this->report_type,array('107',110)) ){
		    $footer_param['total_words'] = price_in_words($amount_total, ST_CUSTPAYMENT);
		}
		if( $this->report_type==110 ){
		    $footer_param['height']+=17;
		    $item_total = $this->ci->view('reporting/footer/delivery',$footer_param,true);
	    } elseif( $this->report_type==111 ) {
	        $footer_param['height'] -= 7;
	        $item_total = $this->ci->view('reporting/footer/quotation',$footer_param,true);

		} else {

		    $item_total = $this->ci->view('reporting/footer/invoice',$footer_param,true);
		}

		$this->tcpdf->SetY( $this->tcpdf->GetY()-8 );
		$this->pdf->writeHTML($item_total);

	}

	private function get_tax_type_name($gst_type=0){
		$tax_type = $this->ci->api->get_data("tax_code",true);
		$tax_type = $tax_type['options'];
		if( isset($tax_type[$gst_type]) ) return $tax_type[$gst_type];
		return null;
	}

	private function footer_close(){

	    $data = array(
	        'amount_w'=>$this->pdf->amount_w,
	        'height'=>$this->tcpdf->getPageHeight() - $this->tcpdf->GetY() - 12 + 6.5,
            'type'=>$this->report_type
	    );

	    $data['content_w'] =  $this->pdf->width - $this->pdf->amount_w;

	    $footer_blank = $this->ci->view('reporting/footer/blank',$data ,true);

	    $this->tcpdf->SetY( $this->tcpdf->GetY()-9);
	    $this->pdf->writeHTML($footer_blank);
	    $this->tcpdf->AddPage();
	}

	private function footer($table_header=null){

        $footer = $this->footer_html();
//         bug($footer['height']);
//         bug('current Y='.$this->tcpdf->GetY());
//         bug('margin bottom='.$this->pdf->margin_bottom);
// bug($footer['height'] + $this->pdf->margin_bottom + $this->tcpdf->GetY());
// bug($this->tcpdf->getPageHeight());
// die('qq');
            $footer_max_y =$this->tcpdf->getPageHeight() - $footer['height'] - $this->pdf->margin_bottom;
		if( $footer_max_y < $this->tcpdf->GetY()){

			$this->footer_close();
			$this->tcpdf->SetY($this->tcpdf->GetY()-7 );
// 			$this->tcpdf->SetY($footer_max_y );
		    $footer = $this->footer_html();
// 		    $footer['height'] = $this->tcpdf->getPageHeight() - $this->tcpdf->GetY();

			$this->pdf->writeHTMLCell($this->pdf->width, null, $this->pdf->margin_left, $this->tcpdf->GetY(), $footer['html']);
		} else {
			$this->pdf->writeHTMLCell($this->pdf->width, null, $this->pdf->margin_left, $this->tcpdf->GetY()-7, $footer['html']);
		}
	}

	private function footer_html($height_full=false){
	    $current_y = $this->tcpdf->GetY();
	    $table_row_height = 0.7;
	    $this->taxes_gst = array();
	    $amount_total = $sub_total = $discount =  0;

	    foreach ($this->items AS $ite){

	    	if( isset($ite->gl_code) ){
	    		$price = $ite->price;
	    	} else {
	    		$price = $ite->price*$ite->qty* (1-$ite->discount_percent);
	    	}

	        $tax_amount = 0;
	        if( $ite->tax_type_id ){
	            $tax_detail = api_get('taxdetail/'.$ite->tax_type_id);
	            $tax = tax_calculator($ite->tax_type_id,$price,$this->order['tax_included'],$tax_detail);
	            if( $tax->gst_type ){
	                if( !isset($this->taxes_gst[$tax->gst_type]  ) ){
	                    $this->taxes_gst[$tax->gst_type] = array('value'=>0,'name'=>$tax_detail->no.' '.$tax_detail->rate.'%' );
	                }
	                $this->taxes_gst[$tax->gst_type]['value'] += $tax->value;
	                $price = $tax->price ;
	                $tax_amount = $tax->value ;
	            }
	        }
	        $sub_total += $price;

	        $amount_total += $ite->price*$ite->qty;
	        if( !$this->order['tax_included'] ){
	            $amount_total += $tax_amount;
	        }
	        $discount += $ite->discount_percent*$ite->price*$ite->qty;
	    }

	    if( $this->order['shipping'] > 0 ){
	        $amount_total += $this->order['shipping'];
	    }
	    $footer_h = (count($this->taxes_gst)+4)*$table_row_height + 21;
	        $sub_total += $this->order['shipping_total'];

	    if( isset($this->order['shipping_total']) ){
	    }
	    $footer_param = array(
	        'taxes'=>$this->taxes_gst,
            'discount'=>$discount,
	        'sub_total'=>$sub_total,
	        'amount_w'=>$this->pdf->amount_w,
	        'bank_account'=>( isset($this->bankacc->bank_account_number) ) ? $this->bankacc->bank_account_number : null,
	        'border_top'=>false,
	        'height'=> $footer_h,
	        'y_current'=>  $current_y,
	        'p_height'=>$this->pdf->height,
	        'shipping'=>$this->order['shipping'],
	        'legal_text'=> $this->config_model->get_sys_pref_val('legal_text'),
	        'width' => $this->pdf->width,
	        'type'=>$this->report_type,
	        'order'=>$this->order
	    );

	    $footer_param['height'] = $this->tcpdf->getPageHeight() - $this->tcpdf->GetY() - $this->pdf->margin_bottom -$footer_h ;
// 	    $footer_param['height'] = $this->pdf->page_height - $this->tcpdf->GetY() - $this->pdf->margin_bottom ;


	    if( in_array($this->report_type,array('113')) ){
	        $footer_param['total_words'] = price_in_words($amount_total, ST_CUSTPAYMENT);
	    }

	    switch ($this->report_type) {
	        case 107:

	            $footer_param['amount_total']=$amount_total-$discount;
	            $footer_param['height'] -=  45;
	            $item_total = $this->ci->view('reporting/footer/invoice',$footer_param,true); break;


	        case 109:
	            $item_total = $this->ci->view('reporting/invoice_footer',$footer_param,true); break;
	            break;
	        case 110:
	            $footer_param['height'] -= 15;
	            $footer_param['amount_total']= $amount_total;
	            $item_total = $this->ci->view('reporting/footer/delivery',$footer_param,true); break;
	        case 111:
	            $footer_param['height'] -= 50;
	            $item_total = $this->ci->view('export/total',$footer_param,true); break;
	        case 112:
	            $footer_param['height'] -= 35;
//                 $footer_param['width'] = $this->pdf->width-$this->pdf->amount_w;
//                 $footer_param['order'] = $this->order;
//                 $footer_param['total_words'] = price_in_words($this->order['total_receipt'], ST_CUSTPAYMENT);
	            $item_total = $this->ci->view('reporting/footer/receipt',$footer_param,true);
                break;
            case 113:
                $footer_param['creditnote_total']= $amount_total-$discount;
                $footer_param['height'] -=  38;
                $item_total = $this->ci->view('reporting/footer/invoice',$footer_param,true); break;

	       case 209:
	            $footer_param['amount_total']= $amount_total;
	            $footer_param['height'] -= 38;
	            if( $height_full ) {
	                $footer_param['height'] += $current_y - 25;
	                $footer_param['border_top'] = true;
	            }
	            $item_total = $this->ci->view('reporting/footer/purchase_order',$footer_param,true);
	            $footer_h +=15;

	            break;
            case 210:
                $footer_param['height'] -= 25;
                 $item_total = $this->ci->view('reporting/footer/Pusc_footer_210',$footer_param,true); break;

            case 211:
//                 $footer_h += 25;
                $footer_param['height'] -= 25;
//                 $footer_h =$footer_param['height'];
                $footer_param['amount_total']= $amount_total;
	            $item_total = $this->ci->view('reporting/invoice_footer',$footer_param,true); break;
	            break;
	        default:
                $footer_param['height'] -=  19;
                if( $height_full ) {
                    $footer_param['height'] += $current_y - 20;
                    $footer_param['border_top'] = true;
                }


	            $item_total = $this->ci->view('export/total',$footer_param,true); break;
	    }
// 	    bug($footer_param['height']);die;
	    return array('html'=>$item_total,'height'=>$footer_h);

	}

// 	private function purchase_order(){
// 	}



	public function bank_trans_out(){
		$this->tcpdf->AddPage();
// 		bug($data);die;
		$this->tcpdf->lastPage();
	}


    function report_front_params($params_array = NULL){
        $cols = array(0);
        $headers = array();
        $aligns = array();
        if( count($params_array) > 0 ) while ( list ($key, $val) = each ($params_array)){
            if( count($val[0]) > 0 ){
                $headers[] = $val[0];
                $cols[] = $val[1];
                $aligns[] = isset($val[2]) ? $val[2] : 'left';
            }

        }
        return array($headers, $cols, $aligns);


    }


}