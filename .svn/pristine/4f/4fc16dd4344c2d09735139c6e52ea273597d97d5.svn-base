<?php
// use PhpOffice\PhpWord\Writer\PDF\TCPDF;
defined('BASEPATH') OR exit('No direct script access allowed');



if( get_instance()->front_report != true ) :



if( !class_exists('TCPDF') ){
    require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/config/tcpdf_config.php');
    require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/tcpdf.php');

}

class pdf {
    var $margin_left = 12;
    var $margin_right = 12;
    var $margin_bottom = 12;
    var $margin_top = 15;
    var $amount_w = 27.25;
    var $line_h = 5;
    var $file = null;
    public $email = false;
    function __construct($orientation='P', $unit='mm', $format='A4'){
        global $ci;
        $this->ci = $ci;
        if( input_val('theme')=='invoice' ){
            $format = 'A6';
        }
        $this->tcpdf = new APDF($orientation,$unit, $format,$unit, 'UTF-8');

        $this->tcpdf->css = $this->css = '<style>'.file_get_contents(ROOT.'/report/css/reporting.css').'</style>';

        $this->tcpdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $this->tcpdf->SetCreator(PDF_CREATOR);
        $this->tcpdf->SetAuthor('accountattanttoday.net');
        $this->tcpdf->SetTitle('GST');
        $this->tcpdf->SetSubject('GST export');
        // 		$this->tcpdf->setPrintHeader(false);
        $this->tcpdf->setPrintFooter(false);
        $this->tcpdf->SetMargins($this->margin_left, $this->margin_top , $this->margin_right, true);
        // 		$this->tcpdf->SetAutoPageBreak(TRUE, 0);
        $this->tcpdf->SetAutoPageBreak(TRUE,12);

        $this->tcpdf->SetFont('helvetica', '', 12);

        $tagvs = array(
            'p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
            'h1' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
        );
        $this->tcpdf->setHtmlVSpace($tagvs);
        $this->tcpdf->setCellHeightRatio(1.25);
        $this->tcpdf->SetCellPadding(0);
        $this->width = $this->tcpdf->getPageWidth()-$this->margin_right - $this->margin_left;
        $this->page_height = $this->tcpdf->getPageHeight()-$this->margin_top;
        $this->page_limit_bottom = $this->tcpdf->getPageHeight()-$this->margin_top - $this->margin_bottom;
        //         $this->file->footer = true;
        //         $this->file->content_height = $this->file->height;
        // 		set_time_limit(0);
        $this->ci->smarty->assign('content_w',$this->width - $this->amount_w);
        $this->ci->smarty->assign('amount_w',$this->amount_w);

        $this->company_info();
    }


    function check_add_page(){

        if( $this->tcpdf->GetY() > $this->tcpdf->getPageHeight()-$this->margin_top - $this->margin_bottom ){
            $this->tcpdf->AddPage();
            $this->cell_max_CurrentY = 0;
        }
        if( $this->tcpdf->GetY() < $this->cell_max_CurrentY ){
            $this->tcpdf->SetY($this->cell_max_CurrentY);
            //             $this->tcpdf->Ln();
        }

    }

    private function company_info(){
        $company = get_company_pref();

        $logo = company_path () . "/images/" . $company['coy_logo'];
        if ( $company['coy_logo'] && file_exists( $logo ) ){
            $this->company['logo'] = $logo;
        }

        $this->company['name'] = $company['coy_name'] ? trim($company['coy_name']) : 'A2000 Solusion';
        $this->company['address'] = null;
        if( isset($company['postal_address']) ){
            $address = trim($company['postal_address']);
            $address = str_replace("\n",'<br>',$address);
            $this->company['address'] = $address;
        }
        if( isset($company['phone']) ){
            $this->company['phone'] = trim($company['phone']);
        }
        if( isset($company['fax']) ){
            $this->company['fax'] = trim($company['fax']);
        }
        if( isset($company['email']) ){
            $this->company['email'] = trim($company['email']);
        }
        if( isset($company['gst_no']) ){
            $this->company['gst_no'] = trim($company['gst_no']);
        }

        if( isset($company['coy_no']) ){
            $this->company['coy_no'] = trim($company['coy_no']);
        }

        $this->ci->smarty->assign('company',$this->company);

        //$taxes = $this->ci->api_membership->get_data('taxdetail',true);
        $taxes = api_get('taxdetail',true);
        $this->ci->smarty->assign('taxes',$taxes);

        include_once(ROOT . "/admin/db/fiscalyears_db.inc");
        $year = get_current_fiscalyear();
        if ($year['closed'] == 0)
            $how = _("Active");
        else
            $how = _("Closed");
        $this->fiscal_year = sql2date($year['begin']) . " - " . sql2date($year['end']) . "  " . "(" . $how . ")";
        $this->print_time = Today() . '   ' . Now();
    }

    function writeHTML($html){
        $this->tcpdf->writeHTML(trim($html).$this->tcpdf->css);
    }

    function writeHTMLCell($w, $h, $x, $y, $html=''){
        $this->tcpdf->writeHTMLCell($w, $h, $x, $y, $this->tcpdf->css.$html);
    }

    function write_view($smarty_view,$data=null){
        if( !isset($data['content_w']) ) {
            $data['content_w'] = $this->width - $this->amount_w;
            $data['amount_w'] = $this->amount_w;
        }


        $html = $this->ci->view("reporting/$smarty_view", $data ,true);
        //         bug($this->tcpdf->getPageWidth());
        // bug($data);die;
        $this->tcpdf->writeHTML( trim($html).$this->tcpdf->css);
    }

    function UpdateY($int_val){
        $this->tcpdf->SetY( $this->tcpdf->GetY() + intval($int_val) );
    }



    var $cell_max_CurrentY = 0;
    function cell($w=0,$txt=null,$align='L',$percent=true, $fill=false,$cell_h=0,$new_line=0){
        if( $percent ){
            $w = ($this->tcpdf->getPageWidth()-$this->margin_left-$this->margin_right)*($w/100);
        }
        $txt_h = $this->tcpdf->getStringHeight($w, $txt, true, true, array('T' => 0, 'R' => 0, 'B' => 0, 'L' => 0), $border=false);
        if( !$cell_h ){
            $cell_h = $this->line_h;
        }

        if( $txt_h/$cell_h >1 ){
            $cell_h = $this->line_h * intval($txt_h/$this->line_h);
            $this->cell_max_CurrentY = $this->tcpdf->GetY() + $cell_h*1.5;
        }
        $pdf_y = $this->tcpdf->GetY();
        $this->tcpdf->MultiCell($w, $cell_h, $txt,$border=0, $align, $fill, $new_line);
        if( $this->tcpdf->GetY() > $pdf_y ){
            $this->cell_max_CurrentY += $this->line_h;
        }

        //         if( $this->tcpdf->GetY() > $this->cell_max_CurrentY ){
        //             $this->cell_max_CurrentY  = $this->tcpdf->GetY();
        //         }
        /*
         * MultiCell($w, $h, $txt, $border=0, $align='L', $fill=false, $ln=0, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M', $fitcell=true)
         * Cell ($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
         */
    }

    function line($type=1,$new_line=true){
        if( $type==1 ){
            $w = 0.2;
        } elseif( $type==2 ){
            $w = 0.5;
        }
        if($new_line){
            $this->tcpdf->Ln();
        }

        $this->tcpdf->Line( $this->margin_left, $this->tcpdf->GetY(),$this->tcpdf->getPageWidth()-$this->margin_right, $this->tcpdf->GetY() , array('width' =>$w,'color' => array(128, 128, 128)));
    }

}


class APDF extends TCPDF{
    var $talbe_header = null;
    var $talbe_number_header = null;
    var $margin = array(0,0,0,0);
    var $table_header_order = null;
    var $table_header_items = null;
    var $line_befor_content = true;
    var $line_begin_page = false;
    public function Header(){
        global $ci;

        if( $this->talbe_header ){
            $set_header_height = ( $this->GetY() < $this->tMargin ) ? true : false;
            //             bug($this->tMargin);die;
            $this->SetY($ci->pdf->margin_top);
//                          $this->SetY($this->GetY()+$this->tMargin);
            if( $this->line_begin_page ){
                $this->SetDrawColor(128, 128, 128);
                $this->Line( $this->lMargin, $this->GetY(),$this->getPageWidth()-$this->rMargin, $this->GetY(), array('width' =>0.7,'color' => array(128, 128, 128)));
                $this->SetY($this->GetY() + 1);
            }
            $this->writeHTML($this->css.$this->talbe_header);

            if( isset($this->table_header_info) && $this->table_header_info ) {
                $this->writeHTMLCell($this->getPageWidth()/3, null,$this->getPageWidth()/2, $this->GetY() - 17, $this->css.$this->table_header_info);

                $this->SetY($this->GetY()+17);
            }

            if ($this->line_befor_content){
//                 $this->SetY($this->GetY()-7);
                $this->Line( $this->lMargin, $this->GetY(),$this->getPageWidth()-$this->rMargin, $this->GetY(), array('width' =>1.5,'color' => array(205, 205, 205)));
                $this->Line( $this->lMargin, $this->GetY(),$this->getPageWidth()-$this->rMargin, $this->GetY(), array('width' =>0.5,'color' => array(128, 128, 128)));

            }

            if( isset($this->table_header_order) && $this->table_header_order ){
                $this->writeHTML($this->css.$this->table_header_order);
            }
            if( isset($this->table_header_author) && $this->table_header_author ){
//                 bug($this->table_header_author);die;
                $this->writeHTML($this->css.$this->table_header_author);
            }
            if( isset($this->table_header_payment_terms) ){
                $this->SetY($this->GetY()-5);
                $this->writeHTML($this->css.$this->table_header_payment_terms);
            }

            if( $this->table_header_items ){
                $this->writeHTML($this->css.'<div class="container wrapper">'.$this->table_header_items.'</div>');
            }
            if( $set_header_height ){
                $this->SetTopMargin($this->GetY());
            }

        } else if ($this->header_bank_trans) {
            $this->SetY($this->GetY()+10);
            $this->SetFillColor(255, 255, 255);
            $this->writeHTML($this->css.$this->header_bank_trans);
            $this->Line( $this->lMargin, $this->GetY(),$this->getPageWidth()-$this->rMargin, $this->GetY() , array('width' =>0.5,'color' => array(128, 128, 128)));

            $this->add_bank_pdf_info('DATE',sql2date($this->bank_trans_data['trans_date']));
            $this->add_bank_pdf_info('TRANSACTION NO','#'.$this->bank_trans_data['trans_no']);
            $this->add_bank_pdf_info('REFERENCE NO',$this->bank_trans_data['ref']);

            //             if( $this->bank_trans_data['payee'] )
                //             {
                //                 $this->add_bank_pdf_info('PAYEE',$this->bank_trans_data['payee']);
                //             }
            //             else {
            //                 $this->add_bank_pdf_info('PAYEER',$this->bank_trans_data['payeer']);
            //             }
            $this->add_bank_pdf_info('PAYMENT FROM',$this->bank_trans_data['payment_from']);
            $this->add_bank_pdf_info('PAYMENT TO',html_entity_decode($this->bank_trans_data['payment_to']));
            $this->add_bank_pdf_info('Cheque Number',$this->bank_trans_data['cheque']);

            $this->Line( $this->lMargin, $this->GetY()+1,$this->getPageWidth()-$this->rMargin, $this->GetY()+1 , array('width' =>0.5,'color' => array(128, 128, 128)));
            $this->Write(15,'BEING PAYMENT FOR');
            $this->SetY($this->GetY()+13);
            $this->writeHTML($this->css.$this->item_table_header);
            $this->SetTopMargin($this->GetY());
        }
    }

    private function add_bank_pdf_info($title,$content){
        $this->SetY( $this->GetY()+2 );
        $this->SetFont('','B');
        $this->MultiCell(50, 0, $title, 0, 'L', 1, 2,  $this->GetX() , $this->GetY());
        $this->SetFont('');
        $this->MultiCell(0, 0, ': '.$content, 0, 'L', 0, 1, $this->GetX() ,$this->GetY()-5.5, true, 0);
    }


}
ENDIF;
