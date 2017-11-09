<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class TaxGroupingDetails {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->page_security = 'SA_TAXREP';

        $this->model = $this->ci->module_model( NULL ,'report',true);
        $this->report = $this->ci->module_control_load('report',null,true);


    }

    function index(){
        $this->from = $this->ci->input->post('start_date');
        $this->to =  $this->ci->input->post('end_date');
        $output = ( $this->ci->input->post('ouput_pdf')) ? 'pdf': 'html';
        if( $this->ci->input->post('ouput_excel') ){
            $output = 'xlsx';
        }
        if( $this->from || $this->to ){
            set_cookie('gst_grouping_from',$this->from);
            set_cookie('gst_grouping_to',$this->to);
            $tax_filter = input_val('taxes');

            set_cookie('gst_grouping_tax',$tax_filter);

            $tax_items = $this->model->items_line_by_taxes($tax_filter,$this->from,$this->to);



            $api = $this->ci->load_library('api_membership',true);

            $tax_code = $this->ci->api_membership->get_data("tax_code",true);

            if( $output=='pdf' ){
                $this->create_pdf();
                $api = $this->ci->load_library('api_membership',true);
                $tax_code = $api->get_data("tax_code",true);
                $this->tax_code = $tax_code['options'];
                $this->pdf_build($tax_items);
            } elseif ($output=='xlsx'){
                $this->excel_download($tax_items,$tax_code['options']);
            } else {
                page(_("GST Grouping Details"));
                global $Ajax;
                $Ajax->activate('_page_body');

                $this->ci->view('gst_check/gst_grouping',array('taxes'=>$tax_items,'from'=>$this->from,'to'=>$this->to, 'tax_code'=>$tax_code['options']) );

                end_page();
            }
        } else {
            $this->form();
        }
    }

    private function form(){
        $start_date_value = get_cookie('gst_grouping_from');
//         bug($start_date_value);die;
        if( ! $start_date_value) {
            $date_first = new DateTime('now');
            $date_first->modify('first day of this month');
            $start_date_value = $date_first->format('d-m-Y');
        }

        $end_date_value = get_cookie('gst_grouping_to');
        if( ! $end_date_value) {
            $date = new DateTime('now');
            $date->modify('last day of this month');
            $end_date_value = $date->format('d-m-Y');
        }

        $this->report->fields = array(
            'taxes'=>array('value'=>get_cookie('gst_grouping_tax'),'type'=>'multitaxes','title'=>'Tax Type'),
            'start_date' => array('type'=>'qdate','title'=>_('Start Date'),'value'=>$start_date_value ),
            'end_date' => array('type'=>'qdate','title'=>_('End Date'),'value'=>$end_date_value ),
        );
        $submit = array(
            'UPDATE_ITEM'=>array('Submit',false),
            'ouput_pdf'=>'Download PDF',
            'ouput_excel'=>array('Download Excel',false)
        );
        $this->report->form('GST Grouping Details',$submit);
    }


    /*
     * BEGIN : create PDF
     */

    private function create_pdf(){
        if( !class_exists('TCPDF') ){
            require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/config/tcpdf_config.php');
            require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/tcpdf.php');

        }
        $this->pdf = new TCPDF('L', PDF_UNIT, 'A3', true, 'UTF-8', false);
        $this->pdf->SetCreator(PDF_CREATOR);
        $this->pdf->SetAuthor('Accountant Today');
        $this->pdf->SetTitle('GST Grouping Details');
        // 	    $this->pdf->SetSubject('TCPDF Tutorial');
        // 	    $this->pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $this->pdf->setPrintFooter(false);
        $this->pdf->setPrintHeader(false);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->line_h = 7;
        $this->pdf->page_w = $this->pdf->getPageWidth() -PDF_MARGIN_RIGHT - PDF_MARGIN_LEFT;
    }

    var $table = array(
        'tran_date'=>    array('title'=>'Date',    'w'=>5,'al'=>'C','col'=>'A'),
        'id'=>     array('title'=>'Trn',           'w'=>3,'al'=>'C','col'=>'B'),
        'reference'=>     array('title'=>'Ref',    'w'=>9,'al'=>'C','col'=>'C'),
        'currence'=>    array('title'=>'Curr',     'w'=>3,'al'=>'C','col'=>'D'),
        'curr_rate'=>array('title'=>'Rate',        'w'=>5,'col'=>'E'),
        'item_name'=>    array('title'=>'Item',    'w'=>17,'col'=>'F'),
        'customername'=>array('title'=>'Suppliers/Customer','w'=>7.4,'col'=>'G'),
        'price_base'=>     array('title'=>'Sales/Purchase Amt','al'=>'R','w'=>8,'col'=>'H'),
        'gst'=>     array('title'=>'GST',               'w'=>7.1,'al'=>'R','col'=>'I'),
        'sale_base'=>    array('title'=>'Sales (Base)',      'w'=>7.1,'al'=>'R','col'=>'J'),
        'sale_gst'=>array('title'=>'S.GST (Base)',      'w'=>7.1,'al'=>'R','col'=>'K'),
        'purchase_base'=>    array('title'=>'Purchase (Base)',   'w'=>7.1,'al'=>'R','col'=>'L'),
        'purchase_gst'=>array('title'=>'P.GST (Base)',      'w'=>7.1,'al'=>'R','col'=>'M'),
        'gst_pay'=>     array('title'=>'Pay/Claim',         'w'=>7.1,'al'=>'R','col'=>'N')
    );

    private function pdf_header($autoCheck=false){

        if( $this->pdf->GetY() < 278 && $autoCheck==true ){
            return;
        }
        $this->pdf->AddPage();
        // 	    GST Grouping Details
        $this->pdf->SetFont('','I',10);
        $this->pdf->Cell( $this->pdf->page_w-60, $this->pdf->line_h, 'GST Grouping Details (from '.$this->from.' to '.$this->to.')');
        $this->pdf->SetFillColor(210, 210, 210);
        $this->pdf->Cell( 60, $this->pdf->line_h, 'Page '.$this->pdf->getAliasNumPage().'/'.$this->pdf->getAliasNbPages(),0,0,'R');
        $this->pdf->Ln();

        $this->pdf->SetFont('', 'B',9); $this->pdf->SetFillColor(210, 210, 210);
        foreach ($this->table AS $header){
            $w = round2( $this->pdf->page_w/100*$header['w'] ,2 );
            $this->pdf->Cell( $w, $this->pdf->line_h*2, $header['title'], 1, 0, 'C', 1);

        }
    }

    private function draw_end_page(){
        if ($this->pdf->GetY() > 271 ){
            $this->pdf->Line(PDF_MARGIN_LEFT, $this->pdf->GetY()+$this->pdf->line_h, $this->pdf->page_w+PDF_MARGIN_LEFT, $this->pdf->GetY()+$this->pdf->line_h);
        }
        $this->pdf_header(true);
    }

    private function pdf_build($tax_items){
        $page_w  = $this->pdf->getPageWidth() -PDF_MARGIN_RIGHT - PDF_MARGIN_LEFT;
        $decimal = user_amount_dec();
        $gst_pay = 0;
        $purchase_base_amount = $sale_base_amount = $purchase_gst_amount = $sale_gst_amount = 0;
        $check_5b = array('SR','DS','AJS');
        $check_6b = array('TX','IM','TX-E43','TX-RE','AJP');
        $val_5b = $val_6b = 0;
        foreach ($tax_items AS $tax){
            $purchase_base_total = $sale_base_total = $purchase_gst_total = $sale_gst_total = 0;

            if( isset($tax->items) && count($tax->items) >0 ) {
                $tax_current = (object)array('rate'=>$tax->rate,'sales_gl_code'=>$tax->sales_gl_code,'purchasing_gl_code'=>$tax->purchasing_gl_code,'gst_03_type'=>$tax->gst_03_type,'name'=>$tax->name);

                if( $this->pdf->GetY() <= PDF_MARGIN_TOP/2 ){
                    $this->pdf_header();
                }
                $this->pdf->SetFont('', 'B',9);

                $this->pdf->Ln();
                $this->pdf->SetFillColor(224, 224, 224);
                $this->pdf->Cell( 20, $this->pdf->line_h, null,"LBT",0,'R',1);
                $this->pdf->Cell( $page_w-20, $this->pdf->line_h, $tax->name.' ('.$tax->no.' )',"RBT",0,'L',1);
                $this->draw_end_page();

                $fill = 0;
                foreach ($tax->items AS $ite){

                    $purchase_base = $sale_base = $purchase_gst = $sale_gst = 0;
                    $price = $ite->unit_price * $ite->quantity * (1-$ite->discount_percent);
                    $tax_item = tax_calculator($ite->tax_id,$price,$ite->tax_included,$tax_current);

                    if ($ite->type=='CCN'){
                        $tax_item->price = -$tax_item->price;
                        $tax_item->value = -$tax_item->value;
                    }


                    if( $ite->type=='S' || $ite->type=='DS' || $ite->type=='CCN' ){
                        $sale_base = $tax_item->price * $ite->curr_rate;
                        $sale_gst = $tax_item->value * $ite->curr_rate;
                        $gst_pay += $sale_gst;
                    } else if ($ite->type=='P' OR $ite->type=='BP' || $ite->type=='SCN' ){
                        $purchase_base = $tax_item->price * $ite->curr_rate;
                        $purchase_gst = $tax_item->value * $ite->curr_rate;
                        if( $ite->type !='BL' ) {
                            $gst_pay -= $purchase_gst;
                        }

                    }

                    // 	                if ( ($ite->type=='S' OR $ite->type=='DS' OR $ite->type=='CCN') AND $tax->sales_gl_code != 2150 ){
                    // 	                } elseif ( ($ite->type=='P' OR $ite->type=='BP' OR $ite->type=='SCN') AND $tax->purchasing_gl_code != 1300 ){
                    // 	                } else {
                    $purchase_base_total += $purchase_base;
                    $purchase_gst_total += $purchase_gst;
                    $sale_base_total += $sale_base;
                    $sale_gst_total += $sale_gst;
                    // 	                }


                    if( array_key_exists($tax->gst_03_type, $this->tax_code) ){
                        if(  in_array( $this->tax_code[$tax->gst_03_type], $check_5b) ){
                            if( ($ite->type == 'S' AND $ite->order_type !=10) OR ($ite->type == 'P' AND $ite->order_type != 20)  ) {
                                continue;
                            }
                            $val_5b += $tax_item->value;
                        }
                        if( in_array( $this->tax_code[$tax->gst_03_type], $check_6b) ){
                            if( ($ite->type == 'S' AND $ite->order_type !=10) OR ($ite->type == 'P' AND $ite->order_type !=20)) {
                                continue;
                            }
                            $val_6b += $tax_item->value*$ite->curr_rate;
                        }
                    }

                    $this->pdf_header(true);
                    $this->pdf->Ln();
                    $this->pdf->SetFillColor(250,250,250);
                    $this->pdf->SetFont(null,null,8);

                    foreach ($this->table AS $field=>$header){
                        $w = round2( $page_w/100*$header['w'] ,2 );
                        $data = null;
                        $html = false;
                        switch ($field){
                            case 'price_base':
                                $data = '<p style="line-height: 7mm;">'.number_format2($tax_item->price,$decimal).' <span style="color:#3eb34e;">'.$ite->type.'</span></p>';
                                $html = true;
                                break;
                            case 'gst': $data = number_format2($tax_item->value,$decimal);break;
                            case 'tran_date': $data = sql2date($ite->$field); break;
                            case 'sale_base':
                            case 'sale_gst':
                            case 'purchase_base':

                            case 'purchase_gst':

                            case 'gst_pay':
                                $data = number_format2($$field,$decimal);break;
                            case 'customername':
                                if( isset($ite->supp_name) ){
                                    $data = $ite->supp_name;
                                } else {
                                    $data = $ite->$field;
                                }
                                break;
                            case 'id':
                                $data = $ite->trans_no; break;
                            default:
                                if( isset($ite->$field) ){
                                    $data = $ite->$field;
                                }
                                break;
                        }

                        $align = ( array_key_exists('al', $header)) ? $header['al']: 'L';
                        $border = '';
                        if( $field=='tran_date' ){
                            $border = 'L';
                        } else if ($field=='gst_pay') {
                            $border = 'R';
                        }

                        $this->pdf->MultiCell( $w,$this->pdf->line_h, $data,$border,$align,$fill,$ln=0, $x='', $y='', $reseth=true, $stretch=0, $html);
                    }
                    $this->draw_end_page();
                    $fill=!$fill;
                } // end loop item of tax
                $this->pdf_header(true);
                $this->pdf->SetFillColor(235,235,235);
                if ( $sale_base_total != 0 ){
                    $this->pdf->Ln();
                    $this->pdf->SetFont('', 'B',9);
                    $this->pdf->Cell( 0.645* $this->pdf->page_w, $this->pdf->line_h, "Sales",'TL',0,'R',1);
                    $this->pdf->SetFont(null,null,8);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($sale_base_total,$decimal),'T',0,'R',1);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($sale_gst_total,$decimal),'T',0,'R',1);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, '0.00','T',0,'R',1);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, '0.00','T',0,'R',1);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, null,'TR',0,0,1);
                    $this->draw_end_page();
                }

                if( $purchase_base_total > 0 ){
                    $this->pdf->Ln();
                    $this->pdf->SetFont('', 'B',9);
                    $this->pdf->Cell( 0.645* $this->pdf->page_w, $this->pdf->line_h, "Purchase",'TL',0,'R',1);
                    $this->pdf->SetFont(null,null,8);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, '0.00','T',0,'R',1);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, '0.00','T',0,'R',1);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($purchase_base_total,$decimal),'T',0,'R',1);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($purchase_gst_total,$decimal),'T',0,'R',1);
                    $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, null,'TR',0,0,1);
                    $this->draw_end_page();
                }
                // 	            $this->pdf_header(true);
                $this->pdf->Ln();
                $this->pdf->SetFont('', 'B',9);
                $this->pdf->Cell( 0.645* $this->pdf->page_w, $this->pdf->line_h, $tax->name,'TL',0,'R',1);
                $this->pdf->SetFont(null,null,8);
                $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($sale_base_total,$decimal),'T',0,'R',1);
                $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($sale_gst_total,$decimal),'T',0,'R',1);
                $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($purchase_base_total,$decimal),'T',0,'R',1);
                $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($purchase_gst_total,$decimal),'T',0,'R',1);
                $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, null,'TR',0,0,1);
                //

            } // end loop $tax->item

            $sale_base_amount += $sale_base_total;
            $sale_gst_amount += $sale_gst_total;
            $purchase_base_amount += $purchase_base_total;
            $purchase_gst_amount += $purchase_gst_total;
        } // end loop $tax

        $this->pdf->Ln(); $this->pdf->SetFillColor(210, 210, 210); $this->pdf->SetFont('', 'B',9);
        $this->pdf->Cell( 0.645* $this->pdf->page_w, $this->pdf->line_h, 'GRAND TOTAL','TL',0,'R',1);
        $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($sale_base_amount,$decimal),'T',0,'R',1);
        $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($sale_gst_amount,$decimal),'T',0,'R',1);
        $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($purchase_base_amount,$decimal),'T',0,'R',1);
        $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($purchase_gst_amount,$decimal),'T',0,'R',1);
        $this->pdf->Cell( 0.071* $this->pdf->page_w, $this->pdf->line_h, number_format2($gst_pay,$decimal),'TR',0,'R',1);
        $this->draw_end_page();
        if( $val_5b > 0 ) {
            $this->pdf->Ln();
            $this->pdf->Cell( 0.858* $this->pdf->page_w, $this->pdf->line_h, '5b (SR,DS,AJS)','TL',0,'R',1);
            $this->pdf->Cell( 0.142* $this->pdf->page_w, $this->pdf->line_h, number_format2($val_5b,$decimal),'TR',0,'R',1);
            $this->draw_end_page();
        }
        if( $val_6b > 0 ) {
            $this->pdf->Ln();
            $this->pdf->Cell( 0.858* $this->pdf->page_w, $this->pdf->line_h, '6b (TX,IM,TX-E43,TX-RE,AJP)','TL',0,'R',1);
            $this->pdf->Cell( 0.142* $this->pdf->page_w, $this->pdf->line_h, number_format2($val_6b,$decimal),'TR',0,'R',1);
            $this->draw_end_page();
        }
        if( $val_5b != $val_6b ){
            $this->pdf->Ln();
            $this->pdf->Cell( 0.858* $this->pdf->page_w, $this->pdf->line_h, ($val_5b > $val_6b) ? 'GST Amount Payable' : 'GST Amount Claimable' ,'TL',0,'R',1);
            $this->pdf->Cell( 0.142* $this->pdf->page_w, $this->pdf->line_h, number_format2(abs($val_5b - $val_6b),$decimal),'TR',0,'R',1);
            $this->draw_end_page();
        }
        $this->pdf->Ln();
        $this->pdf->Cell($page_w, 0, '', 'T');

        if( in_ajax()  ){
            $fname = '/pdf_files/gst-grouping-detail.pdf';
            check_dir(COMPANY_DIR.'/pdf_files');
            $this->pdf->Output(COMPANY_DIR.$fname, 'F');
            global $Ajax;

            if (user_rep_popup())
                $Ajax->popup(COMPANY_ASSETS.'/'.$fname);
        } else {
            $this->pdf->Output('I');
        }


        // 	    header('Location: '.site_url('company/0/pdf_files/gst-grouping-detail.pdf'));
    }

    /*
     * BEGIN create excel file
     */

    function excel_download($tax_items,$tax_codes){
        $excel = $this->ci->load_library('phpexcel',true);
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->setTitle('GST Grouping Details');
        $page = $excel->getActiveSheet();
        foreach ($this->table AS $i=>$field){
            if( isset($field['col']) ){
                $taget = $field['col'].'1';
                $page->setCellValue($taget, $field['title'] );
                $page->getStyle($taget)->getFont()->setBold(true);
                $page->getStyle($taget)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $page->getColumnDimension($field['col'])->setAutoSize(true);
            }
        }
        $page->getRowDimension('1')->setRowHeight(40);
        $page->getStyle('A1:N1')
        ->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'EAEAEA')
                )
            )
        )
        //         ->getFill()
        //         ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        //         ->getStartColor()
        //         ->setARGB('F5F5F5')
        ;
        $gst = $sale_gst_amount = $sale_base_amount = 0;

        $b5_check = array('SR','SR','AJS');
        $b6_check = array('TX','IM','TX-E43','TX-RE','AJP');
        $b5 = $b6 = 0;
        $row = 2;
        $sale_base_amount = $sale_gst_amount = $purchase_base_amount = $purchase_gst_amount = 0;
        if( count($tax_items) >0 ) foreach ($tax_items AS $tax){
            if ( empty($tax->items) ) continue;

            $page->setCellValue("A$row", $tax->name.' ('.$tax->no.' )' )->getStyle("A$row")->getFont()->setBold(true);;
            $page->mergeCells("A$row:N$row");
            $page->getStyle("A$row:N$row")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'F5F5F5')
                    )
                )
            );
            $row++;

            $sale_base_total = $sale_gst_total = $purchase_base_total = $purchase_gst_total = 0;
//             bug($tax->items);die;
            foreach ($tax->items AS $o){


                $sale_gst = $sale_base = $purchase_gst = $purchase_base = $price = $tax_base = $price_base = 0;
                $price_base = $o->unit_price * $o->quantity * (1-$o->discount_percent);
                if ( $o->tax_included ){
                    $tax_base = $tax->rate/(100+$tax->rate)*$price_base;
                    $price_base = $price_base-$tax_base;
                } else {
                    $tax_base = $tax->rate*$price_base/100;
                }
                if ($o->type=='CCN'){
                    $price_base = -$price_base;
                    $tax_base = -$tax_base;
                }

                if ($o->type =='S' OR $o->type=='DS' OR $o->type=='CCN' || $o->type=='SBD'){
                    $sale_base = $price_base*$o->curr_rate;
                    $sale_gst = $tax_base*$o->curr_rate;
                    $gst = $gst + $sale_gst;
                } elseif ( $o->type=='P' OR $o->type=='BP' OR $o->type=='SCN' || $o->type=='PBD'){
                    $purchase_base = $price_base * $o->curr_rate;
                    $purchase_gst = $tax_base*$o->curr_rate;
                    if ($o->type !='BL'){
                        $gst = $gst - $purchase_gst;
                    }
                }

                $taxcode=$tax_code[$tax->gst_03_type];
                $taxcode = $taxcode.title;


                if (isset($taxcode) &&  in_array($taxcode,$b5_check) ){
                    if ( $o->type=='S' || $o->type=='DS' || $o->type=='CCN' || $o->type=='SBD' ){
                        $b5 = $b5+$sale_gst;
                    } elseif ($o->type=='P' OR $o->type=='BP' OR $o->type=='SCN' || $o->type=='PBD'){
                        $b5 = $b5+$purchase_gst;
                    }
                }

                if (isset($taxcode) &&  in_array($taxcode,$b6_check) ){
                    if ( $o->type=='S' || $o->type=='DS' || $o->type=='CCN' || $o->type=='SBD' ){
                        $b6 = $b6+$sale_gst;
                    } elseif ($o->type=='P' OR $o->type=='BP' OR $o->type=='SCN' || $o->type=='PBD'){
                        $b6 = $b6+$purchase_gst;
                    }
                }

                if (($o->type=='S' OR $o->type=='CCN') AND $tax->sales_gl_code != 2150){

                } elseif ( ($o->type=='P' OR $o->type=='SCN') AND $tax->purchasing_gl_code != 1300){

                } else{
                    $sale_base_total = $sale_base_total+$sale_base;
                    $sale_gst_total = $sale_gst_total+$sale_gst;
                    $purchase_base_total = $purchase_base_total+$purchase_base;
                    $purchase_gst_total = $purchase_gst_total+$purchase_gst;
                }


                foreach ($this->table AS $k=>$field){
                    $taget = $field['col'].$row;
                    switch ($k){
                        case 'item_name':
                            $value = $o->item_code .' - '.$o->item_name ; break;
                        case 'customername':
                            if( isset($o->customername) ){
                                $value = $o->customername;
                            } else if (isset($o->supp_name)) {
                                $value = $o->supp_name;
                            }

                            break;
                        case 'price_base':
                        case 'sale_base':
                        case 'sale_gst':
                        case 'purchase_base':
                        case 'purchase_gst':
                            $value = $$k; break;
                        case 'gst':
                            $value = $tax_base; break;
                        case 'gst_pay':
                            $value = $gst; break;
                        default:
                            $value = $o->$k;
                            break;
                    }
                    if( in_array($k, array('gst_pay','gst','purchase_gst','purchase_base','sale_gst','sale_base',price_base))){
                        $page->setCellValue($taget,number_total($value));
                    } else {
                        $page->setCellValue($taget,$value);
                    }

                }
                $row++;

            } // END loop items of tax

            if ( $sale_base_total > 0 ){
                $page->setCellValue("A$row", 'Sale' )->getStyle("A$row")->getFont()->setBold(true);
                $page->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $page->mergeCells("A$row:I$row");

                $page->getStyle("A$row:N$row")->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'F5F5F5')
                        )
                    )
                );

                $page->setCellValue("J$row", $sale_base_total );
                $page->setCellValue("K$row", $sale_gst_total );

                $row++;
            }
            if ( $purchase_base_total > 0 ){
                $page->setCellValue("A$row", 'Purchase' )->getStyle("A$row")->getFont()->setBold(true);
                $page->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $page->mergeCells("A$row:I$row");

                $page->getStyle("A$row:N$row")->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'F5F5F5')
                        )
                    )
                );

                $page->setCellValue("L$row", $purchase_base_total );
                $page->setCellValue("M$row", $purchase_gst_total );

                $row++;
            }
            $sale_base_amount += $sale_base_total;
            $sale_gst_amount += $sale_gst_total;
            $purchase_base_amount += $purchase_base_total;
            $purchase_gst_amount += $purchase_gst_total;

        } // END loop taxes

        $page->setCellValue("A$row", 'GRAND TOTAL' )->getStyle("A$row")->getFont()->setBold(true);
        $page->getStyle("A$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $page->mergeCells("A$row:I$row");

        $page->getStyle("A$row:N$row")->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'F5F5F5')
                )
            )
        );
        $page->setCellValue("J$row", $sale_base_amount );
        $page->setCellValue("K$row", $sale_gst_amount );
        $page->setCellValue("L$row", $purchase_base_amount );
        $page->setCellValue("M$row", $purchase_gst_amount );
        $page->setCellValue("N$row", $gst );



        //set cell A1 content with some text
        //         $excel->getActiveSheet()->setCellValue('A1', 'This is just some text value');
        //         //change the font size
        //         $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
        //         //make the font become bold
        //         $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        //merge cell A1 until D1
        //         $excel->getActiveSheet()->mergeCells('A1:D1');
        //set aligment to center for that merged cell (A1 to D1)
        //         $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $filename='just_some_random_name.xls'; //save our workbook as this file name

        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="GST-grouping-detail-'.date('ymd-hs').'.xls"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache

        //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
        //if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
        //- See more at: https://arjunphp.com/how-to-use-phpexcel-with-codeigniter/#sthash.ZS3iFRwV.dpuf
    }
}