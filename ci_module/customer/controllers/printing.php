<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CustomerPrinting {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->page_security = 'SA_TAXREP';
        $this->model = $this->ci->module_model( $ci->module,'report',true);
        $this->report = $this->ci->module_control_load('report',null,true);

    }

    function statements(){
        $this->bank_model = $this->ci->model('bank_account',true);
        $this->contact_model = $this->ci->model('crm',true);

//         $_POST['customer'] = 8;
//         $_POST['show_allocated'] = 1;

//         $_POST['start_date'] = '1-1-2016';
//         $_POST['end_date'] = '31-08-2016';
//         $_POST['report_type'] = 'period';

        if( $this->ci->input->post() ) {
            return $this->statement_export_pdf();
        }
        $report_type = $this->ci->finput->array2options(array('outstanding'=>'Only Outstanding','period'=>'Outstanding With Period Transaction'));


        $this->report->fields = array(
            'customer'=>array('value'=>'','type'=>'CUSTOMER','title'=>'Customer','value'=>get_cookie('customer')),
            'start_date' => array('type'=>'qdate','title'=>_('Start Date'),'value'=>begin_month() ),
            'end_date' => array('type'=>'qdate','title'=>_('End Date'),'value'=>end_month() ),
            'currency' => array('type'=>'currency','title'=>_('Currency Filter') ),
            'report_type' => array('type'=>'options','title'=>_('Report Type'),'options'=>$report_type ),
            'show_allocated' => array('type'=>'checkbox','title'=>_('Show Also Allocated') ),
            'email'=> array('type'=>'checkbox','title'=>_('Email Customers') ),
            'comments'=> array('type'=>'TEXTBOX','title'=>'Comments' ),
            'orientation'=>array('type'=>'orientation','title'=>_('Orientation')),
        );

        $this->report->form('Print Statements');
    }

    private function statement_export_pdf(){
        $print_model = $this->ci->module_model(null,'print',true);
	    $trans_where = array();
	    $customer = 			input_val('customer');

	    set_cookie('customer',$customer);

	    $currency = 			input_val('currency');
	    $show_also_allocated = 	input_val('show_allocated') ? true : false ;
	    $email = 				input_val('email');
	    $comments = 			input_val('comments');
	    $orientation = 			input_val('orientation')=='landscape' ? 'L' : 'P' ;
	    $start_date = 			input_val('start_date');

	    $report_type = input_val('report_type') == 'period' ? 'period' : 'outstanding';

	    if( $start_date ){
	        $trans_where['tran_date >='] = date2sql($start_date);
	    } else {
	        $start_date = date('Y-m-d');
	    }

	    $end_date = 			input_val('end_date');
	    if( $end_date ){
	        $trans_where['tran_date <='] = date2sql($end_date);
	    } else {
	        $end_date = Now();
	    }

	    if( !$customer ){
	        $debtor_where = array();
	    } else {
	        $debtor_where = array('debtor_no'=>$customer);
	    }
// 	    $this->ci->db->limit(1000);
	    $debtors = $this->ci->db->where($debtor_where)->order_by('name')->get('debtors_master')->result();
	    if( count($debtors) > 500 ){
	        display_error(_("out of range - user needs to select customer for printing "));
	        return;
	    }

	    $print_model->trans_type = array(ST_SALESINVOICE,ST_OPENING_CUSTOMER,ST_CUSTCREDIT,ST_CUSTPAYMENT);

	    if( !method_exists($this->ci, 'qpdf') ){
	        $this->ci->load_library('qpdf');
	    }

        $this->pdf = new qPDF($orientation);
        if( $debtors AND count($debtors) > 0 ){foreach ($debtors AS $deb){


	        $trans_where['debtor_no'] = $deb->debtor_no;
	        $show_all = ($report_type=='period') ? true : false;

	        $items = array();
	        $items = $print_model->customer_open_balance($deb->debtor_no,$start_date,$end_date);
	        $invoices = $print_model->customer_outstanding($trans_where,$show_all);
	        $items = array_merge($items,$invoices);

	        usort($items, function($a,$b){ return strtotime($a->tran_date)-strtotime($b->tran_date);} );


// 	        $pdf->bankacc = $this->bank_model->get_default_account($deb->curr_code);
	        if( $show_also_allocated ){
	            $this->pdf->table_items = array(
	                'tran_date'=>array('title'=>'Date','w'=>15,'class'=>'textcenter'),
	                'deb_type'=>array('title'=>'' ,'w'=>5,'class'=>'textcenter'),
	                'reference'=>array('title'=>'Ref','w'=>15),
	                'trans_type'=>array('title'=>'Description' ,'w'=>20),
	                'debit'=>array('title'=>'Debit','w'=>15,'class'=>'textright'),
	                'credit'=>array('title'=>'Credit','w'=>15,'class'=>'textright'),
	                'allocated'=>array('title'=>'Allocated','w'=>15,'class'=>'textright'),
	            );
	        } else {
	            $this->pdf->table_items = array(
	                'tran_date'=>array('title'=>'Date','w'=>15,'class'=>'textcenter'),
	                'deb_type'=>array('title'=>'' ,'w'=>5,'class'=>'textcenter'),
	                'reference'=>array('title'=>'Ref','w'=>15),
	                'trans_type'=>array('title'=>'Description' ,'w'=>25),
	                'debit'=>array('title'=>'Debit','w'=>20,'class'=>'textright'),
	                'credit'=>array('title'=>'Credit','w'=>20,'class'=>'textright'),
	            );
	        }


	        $this->pdf->contacts = $this->contact_model->get_customer_contact($deb->debtor_no,'invoice');

	        $order = array(
	            'debtor'=>$deb->name,
	            'debtor_no'=>$deb->debtor_no,
	            'name'=>$this->pdf->contacts->name,
	            //'date'=>date('d-m-Y',strtotime($pdf->items[0]->tran_date)-24*60*60),
	            'date'=>$end_date,
	            'contact' => (array)$this->pdf->contacts
	        );
	        $this->ci->smarty->assign('order',$order);


	        $balanceByMonth = $print_model->get_statement_detail($deb->debtor_no,$end_date, $show_all);
	        $this->make_statement_pdf($items,$balanceByMonth,$show_also_allocated,$report_type);


	    }}
	    $this->pdf->output();

    }

    private function make_statement_pdf($items=NULL,$balanceByMonth=NULL,$show_allocated=false,$report_type='period'){
        $this->pdf->header_view = 'printing/statement_header';
        $this->pdf->newGroup();
        $this->pdf->fontSize(9);

        $balance = $outstanding = 0;
        $footer_h = 230;

		foreach ($items AS $ite) {

		    if( !is_object($ite) || !isset($ite->trans_type) )
		        continue;

		    if(  $this->pdf->tcpdf->GetY() > $footer_h-5 ){
		        $this->pdf->tcpdf->SetY($footer_h);
		        $this->pdf->write_view('footer/statement', array('balance'=>$balance,'details'=>$balanceByMonth,'balance_word'=>$this->pdf->currency_default->currency.': '.price_in_words($balance,ST_CUSTPAYMENT)) );
		        $this->pdf->tcpdf->AddPage();
		    }
		    $row_data = array();

		    if( $ite->trans_type == ST_SALESINVOICE && isset($ite->credit_inv) ){
		        $ite->Allocated = $ite->credit_inv;
		    }


		    $debit = $credit = $amount = $allocated = 0;
		    $new_page = false;
		    $this->pdf->check_add_page();
		    $amount = $ite->total;

            if( $amount == 0 ) continue;


	        switch ($ite->trans_type){
	            case ST_SALESINVOICE:
	                $debit = $amount;
	                $allocated = $ite->credit_of_inv + $ite->payment_of_inv;
	                break;
                case ST_CUSTPAYMENT:
                    $credit = $amount;
                    $allocated = -$ite->payment_to_inv;
                    break;
	            case ST_CUSTCREDIT:
	                $credit = $amount;
	                $allocated = -$ite->credit_to_inv;
	                break;
	            case ST_OPENING:
	                if( $amount >0 ){
	                    $debit = $amount;
	                    $credit = 0;
	                } else {
	                    $credit = $amount;
	                    $debit = 0;
	                }
	                $allocated = $ite->allocated;
	                break;
                default:

                    $debit = $amount;
                    break;
	        }

	        $outstanding += ( $debit - $credit ) - $allocated;
	        $balance += ( $debit - $credit );


	        $show_also_allocated = 	input_val('show_allocated') ? true : false ;
	        if( $show_also_allocated ){
	            $balance -= $allocated;
	        }


	        /*
	         * show face value view for debit/credit
	         */
	        if( !$show_allocated && $allocated !=0 && $report_type !='period' ){
	            if( $debit > 0 ){
	                $debit -= $allocated;
	            }
	            if( $credit > 0 ){
	                $credit -= abs($allocated);
	            }

	        }

            foreach ($this->pdf->table_items AS $field => $title){
                $width = $title['w']*($this->pdf->width - $this->pdf->amount_w)/100;
                $align = 'L';
                switch ($field){
                    case 'deb_type':
                        $txt = null;
                        if( $ite->trans_type !=ST_OPENING && $ite->trans_type !=ST_OPENING_CUSTOMER){
                            $txt = ($ite->trans_type == ST_SALESINVOICE) ? 'IN': 'OR';
                        }
                        $align = 'C';
                        break;
                    case 'debit':
                    case 'credit':
                    case 'allocated':
                        $txt = abs( $$field );
                        $txt = ( floatval($txt) != 0 ) ? number_total($txt) : NULL;
                        $align = 'R';
                        break;
                    case 'trans_type':
                        $txt = ($ite->trans_type==ST_OPENING) ? 'Balance B/F' : tran_name($ite->$field);
                        break;
                    case 'tran_date':
                        $txt = sql2date($ite->$field);
                        $align = 'C';
                        break;
                    default:
                        $txt = null;
                        if( isset($ite->$field) ){
                            $txt = $ite->$field;
                        }
                        break;

                }
                $this->pdf->tcpdf->MultiCell($width, 5, $txt, false, $align , 0, 0, '', '', true);
            }
            $this->pdf->tcpdf->MultiCell($this->pdf->amount_w, 5, number_total($balance), false, 'R' , 0, 0, '', '', true);
            $this->pdf->tcpdf->Ln(5);
		}

		if(  $this->pdf->tcpdf->GetY() > $footer_h ){
		    $this->pdf->tcpdf->AddPage();
		}
        $this->pdf->tcpdf->SetY($footer_h);

		$this->pdf->write_view('footer/statement', array('balance'=>$balance,'details'=>$balanceByMonth,'balance_word'=>$this->pdf->currency_default->currency.': '.price_in_words($balance,ST_CUSTPAYMENT),'curr_symbol'=>$this->pdf->currency_default->curr_symbol) );


	 }


}