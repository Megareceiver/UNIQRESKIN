<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TaxReport {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->page_security = 'SA_TAXREP';
//         $this->ci->page_title = 'Bad Debts Processing Dashboad';
//         include_once($path_to_root . "/includes/ui.inc");

        $this->model = $this->ci->module_model( $ci->module,'report',true);
        $this->report = $this->ci->module_control_load('report',null,true);

    }
    function index(){

        return $this->taxes_report();
    }

    private function taxes_report(){
        if( $this->ci->input->post() ){
            return $this->taxes_report_download();
        }

        $start_date_value = add_months( Today(), -get_company_pref('tax_last') );
        $end_date_value = end_month($start_date_value);


        $this->report->fields = array(
            'taxes'=>array('value'=>get_cookie('gst_grouping_tax'),'type'=>'multitaxes','title'=>'Tax Type'),
            'start_date' => array('type'=>'qdate','title'=>_('Start Date'),'value'=>$start_date_value ),
            'end_date' => array('type'=>'qdate','title'=>_('End Date'),'value'=>$end_date_value ),
            'summary_only'=> array('type'=>'checkbox','title'=>_('Summary Only'),'value'=>0 ),
            'comments'=> array('type'=>'TEXTBOX','title'=>_('Comments'),'value'=>NULL ),
            'orientation'=>array('type'=>'orientation','title'=>_('Orientation'),'value'=>1),
        );

        $this->report->form('Tax Report');

    }

    function taxes_report_download(){
        $this->ci->load_library('pdf');
        $this->ci->pdf->css_files = array('aaa.css');
        $pdf = $this->ci->load_library('reporting',true);


        $from = $this->ci->input->post('start_date');
        $to = $this->ci->input->post('end_date');

        set_cookie('gst_grouping_from',$from);
        set_cookie('gst_grouping_to',$to);
        $tax_filter = input_val('taxes');
        set_cookie('gst_grouping_tax',$tax_filter);

        $from = '01-04-2015';
        $to = '30-04-2015';

        $data['period'] = $from.'-'.$to;
//         $data['fiscal_year'] = $pdf->fiscal_year;
//         $data['print_time'] = $pdf->print_time;
//         $data['host'] = $_SERVER['SERVER_NAME'];
//         $data['user'] = $_SESSION["wa_current_user"]->name;
        $data['width'] = 170;





        $header_data = array(
            'title'=>'Tax Report',
            'company'=>$pdf->company,
            'period'=>$from.' - '.$to,
            'print_time'=>date('d-m-Y'),
            'host'=>$_SERVER['SERVER_NAME'],
            'user'=>$_SESSION["wa_current_user"]->name,
            'fiscal_year'=>$pdf->pdf->fiscal_year
//             'width'=>$pdf->pdf->width - $pdf->pdf->amount_w

        );
        $pdf->tcpdf->header_tax_report = $this->ci->temp_view('report/header',$header_data,false,'tax',false);
//         bug($data['trans']); die;
//         $html = $this->ci->view('reporting/bank/transactions',$data,true);

        $pdf->tcpdf->AddPage();
//         $pdf->tcpdf->SetFont('helvetica', '', 9);
//         $pdf->tcpdf->SetAutoPageBreak(TRUE,12);

        $data['trans'] = $this->model->items_line_by_taxes($tax_filter,$from,$to);
        $line_h = 5;
        $page_w = $pdf->pdf->width - $pdf->pdf->amount_w;
        $pdf->tcpdf->SetFillColor(250,250,250);

        if( !empty($data['trans']) ) foreach ($data['trans'] AS $taxes){
            $pdf->tcpdf->SetFont(null,null,9);
            if( !empty($taxes->items) ) foreach ($taxes->items AS $ite){
                $pdf->tcpdf->Ln();
//             	$this->pdf->SetFont('', 'B',9);
            	$pdf->tcpdf->Cell( 0.1* $page_w, $line_h, "tran type",NULL,0,'R');

//             	$pdf->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($sale_base_total,$decimal),'T',0,'R',1);
//             	$pdf->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($sale_gst_total,$decimal),'T',0,'R',1);
//             	$pdf->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, '0.00','T',0,'R',1);
//             	$pdf->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, '0.00','T',0,'R',1);
//             	$pdf->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, null,'TR',0,0,1);
            }
        }

//         bug($data['trans']);die;
//         $pdf->pdf->write_view('taxes/tax_report',$data);
        $pdf->do_report();
        die('call me');
    }
}