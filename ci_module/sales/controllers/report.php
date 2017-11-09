<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SalesReport {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->db = $ci->db;
        $page_security = 'SA_CUSTPAYMREP';
        $this->report = module_control_load(null,'report');//$this->ci->module_control_load('report',null,true);
    }

    public function customer_balance(){

        $this->report->fields = array(
            'date_begin' => array('type'=>'qdate','title'=>_('Start Date'),'value'=>begin_month() ),
            'date_end' => array('type'=>'qdate','title'=>_('End Date'),'value'=>end_month() ),
            'customer'=>array('type'=>'CUSTOMER','title'=>'Customer','value'=>get_cookie('customer')),

            'currency' => array('type'=>'currency','title'=>_('Currency Filter')),
            'show_balance' => array('type'=>'checkbox','title'=>_('Show Balance') ),
            'show_zero' => array('type'=>'checkbox','title'=>_('Suppress Zeros') ),
            'comments'=> array('type'=>'TEXTBOX','title'=>'Comments' ),
            'orientation'=>array('type'=>'orientation','title'=>_('Orientation')),
        );
        $this->report->view = 'form-fromto';

//         if( have_post() ){
            $this->debtor_model = module_model_load('debtor');
            return $this->customer_balance_view();
//         }
        $this->report->form('Customer Balances');
    }

    private function customer_balance_view(){
        $data = $this->report->submit();
        $debtors_where = array();
        if( is_numeric($data['customer']) ){
            $debtors_where['debtor_no'] = $data['customer'];
        } else {
            $data['customer'] = 'All';
        }
        $data['currency'] = 'Balances in Home Currency';
        $data['show_zero'] = 'No';

        $debtors = $this->debtor_model->get_debtors($debtors_where);
        $this->customer_balance_pdf($data,$debtors);
    }

    private function customer_balance_pdf($data,$debtors){
        $convert = false;
        $this->ci->smarty->assign('pdf',$data);

        $headers = array(
            'trans_type'=>array('title'=>'Trans Type','w'=>15,'class'=>''),
            'reference'=>array('title'=>'#','w'=>10),
            'tran_date'=>array('title'=>'Date' ,'w'=>10,'class'=>'textcenter'),
            'due_date'=>array('title'=>'Due Date' ,'w'=>10,'class'=>'textcenter'),
            'charges'=>array('title'=>'Charges','w'=>14,'class'=>'textright'),
            'credit'=>array('title'=>'Credits','w'=>14,'class'=>'textright'),
            'allocated'=>array('title'=>'Allocated','w'=>14,'class'=>'textright'),
            'balance'=>array('title'=>'Balance','w'=>14,'class'=>'textright'),
        );
        $this->ci->smarty->assign('page_header',$headers);


        $pdf = new qPDF($data['orientation']);
        $pdf->header_view = 'reporting/customer_balance';
        $this->ci->smarty->assign('content_w',$pdf->width);
        $pdf->newGroup();
        $pdf->fontSize(7);
        $tcpdf = $pdf->tcpdf;

        $tcpdf->SetY($tcpdf->GetY()-5);
        if( !empty($debtors) ) foreach ($debtors AS $k=>$deb){
            if( $k > 200 ) break;


            $rate = $convert ? get_exchange_rate_from_home_currency($deb->curr_code, Today()) : 1;

            if(  $tcpdf->GetY() > $pdf->tcpdf->getPageHeight()-$pdf->margin_top -5 ){
                $pdf->AddPage();
                $tcpdf->SetY($tcpdf->GetY()-5);
            }

            $debtor_opening = $this->debtor_model->opening_balance($deb->debtor_no,$data['date_begin']);

            $title_w = $headers['trans_type']['w'] + $headers['reference']['w'] + $headers['tran_date']['w'];
            $pdf->tcpdf->MultiCell($title_w*$pdf->width/100, 5, pdf_text($deb->name), false, 'L' , 0, 0, '', '', true);

            $pdf->tcpdf->MultiCell($headers['due_date']['w']*$pdf->width/100, 5, 'Open Balance', false, 'C');
            $pdf->tcpdf->MultiCell($headers['charges']['w']*$pdf->width/100, 5, number_total( abs($debtor_opening->charges)*$rate), false, 'R');
            $pdf->tcpdf->MultiCell($headers['credit']['w']*$pdf->width/100, 5, number_total( abs($debtor_opening->credits)*$rate), false, 'R');
            $pdf->tcpdf->MultiCell($headers['allocated']['w']*$pdf->width/100, 5, number_total( abs($debtor_opening->allocated)*$rate), false, 'R');
//             $pdf->tcpdf->MultiCell($headers['balance']['w']*$pdf->width/100, 5, number_total( abs($debtor_opening->charges-$debtor_opening->credits)*$rate), false, 'R');
            $pdf->tcpdf->Ln(5);
        }

        $pdf->output();
    }


}