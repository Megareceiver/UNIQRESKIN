<?php defined('BASEPATH') OR exit('No direct script access allowed');

class qPDF {
    var $margin_left = 12;
    var $margin_right = 12;
    var $margin_bottom = 12;
    var $margin_top = 12;
    var $amount_w = 27.25;
    var $file = null;
    public $email = false;
    private $ci = NULL;
    function __construct($orientation='P', $format='A4', $unit='mm'){
        $this->ci = get_instance();
        if( input_val('theme')=='invoice' ){
            $format = 'A6';
        }

        $this->tcpdf = new TCPDF2($orientation,$unit,$format);

        $this->tcpdf->css = $this->css = '<style>'.file_get_contents(ROOT.'/report/css/reporting.css').'</style>';

        $this->tcpdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $this->tcpdf->SetCreator(PDF_CREATOR);
        $this->tcpdf->SetAuthor('accountattanttoday.net');
        $this->tcpdf->SetTitle('GST');
        $this->tcpdf->SetSubject('GST export');
        // 		$this->tcpdf->setPrintHeader(false);
        $this->tcpdf->setPrintFooter(false);
//         $this->tcpdf->SetMargins($this->margin_left, $this->margin_top , $this->margin_right, true);
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
//         $this->page_height = $this->tcpdf->getPageHeight()-$this->margin_top;
//         $this->page_limit_bottom = $this->tcpdf->getPageHeight()-$this->margin_top - $this->margin_bottom;
        $this->tcpdf->SetMargins($this->tcpdf->margin_left,$this->tcpdf->margin_top,true);
//         bug($this->tcpdf->margin_top);die;

        $this->ci->smarty->assign('content_w',$this->width - $this->amount_w);
        $this->ci->smarty->assign('amount_w',$this->amount_w);

        $this->company_info();
    }

    function check_add_page(){
        if( $this->tcpdf->GetY() > $this->tcpdf->getPageHeight()-$this->margin_top - $this->margin_bottom ){
            $this->tcpdf->AddPage();
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

//         $taxes = $this->ci->api_membership->get_data('taxdetail',true);
//         bug($taxes);die;
//         $this->ci->smarty->assign('taxes',$taxes);

        include_once(ROOT . "/admin/db/fiscalyears_db.inc");
        $year = get_current_fiscalyear();
        if ($year['closed'] == 0)
            $how = _("Active");
        else
            $how = _("Closed");
        $this->fiscal_year = sql2date($year['begin']) . " - " . sql2date($year['end']) . "  " . "(" . $how . ")";
        $this->ci->smarty->assign('fiscal_year',$this->fiscal_year);

        $this->print_time = date($this->ci->dateformatPHP.' h:i');
        $this->ci->smarty->assign('print_time',$this->print_time);

        $sys_model = $this->ci->model('config',true);
        $this->currency_default = $sys_model->curr_default();





//         $pdf->currency_name = $currency_default->currency;
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
        $this->tcpdf->writeHTML( trim($html).$this->tcpdf->css);
    }

    function UpdateY($int_val){
        $this->tcpdf->SetY( $this->tcpdf->GetY() + intval($int_val) );
    }

    function newGroup(){
        $this->ci->smarty->assign('page_number',$this->tcpdf->getPageNumGroupAlias().'/'.$this->tcpdf->getPageGroupAlias());
        if( isset($this->header_view) && $this->header_view ){
            $this->tcpdf->header_html = $this->ci->temp_view($this->header_view,NULL,false,$this->ci->module,false);
        }
        if( $this->table_items && is_array($this->table_items) ){
            $this->tcpdf->header_html .= $this->ci->temp_view('table_items',array('table'=>$this->table_items),false,'reporting',false);
        }
//         echo $this->css;
//         bug($this->tcpdf->header_html);die;


//
        $this->tcpdf->startPageGroup();
        $this->tcpdf->AddPage();
    }

    function AddPage(){
        $this->tcpdf->AddPage();
    }

    function font($family=null, $style=null, $size=0){
        $this->tcpdf->SetFont($family, $style, $size);
    }
    function fontSize($size=1){
        $this->tcpdf->SetFontSize($size);
    }

    var $header_html = NULL;
    function output(){


        global $Ajax;

        if( $this->tcpdf->getPage() < 1 ){
            display_error(_("No data"));
            return;
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
//                 bug($this->tcpdf->header_html);die;
                $this->tcpdf->Output('I');
            }
        }

    }


}
if( !class_exists('TCPDF') ){
    require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/config/tcpdf_config.php');
    require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/tcpdf.php');

}


class TCPDF2 extends TCPDF{
//     var $talbe_header = null;
    var $header_html = NULL;
    var $talbe_number_header = null;
//     var $margin = array(0,0,0,0);
    var $margin_top =12;
    var $margin_bottom =12;
    var $margin_left =12;
    var $margin_right =12;

    var $table_header_order = null;
    var $table_header_items = null;
    var $line_befor_content = true;
    var $line_begin_page = false;


    function Header(){

//         $this->SetY( $this->GetY() + 10 );

//         $this->Line( $this->lMargin, $this->GetY(),$this->getPageWidth()-$this->rMargin, $this->GetY(), array('width' =>0.7,'color' => array(128, 128, 128)));

        if( $this->header_html ){
//             bug($this->header_html);die('check header');
            $set_header_height = ( $this->GetY() < $this->tMargin ) ? true : false;
            $this->SetY($this->margin_top);

//             if( $this->line_begin_page ){
//                 $this->SetDrawColor(128, 128, 128);
//                 $this->Line( $this->lMargin, $this->GetY(),$this->getPageWidth()-$this->rMargin, $this->GetY(), array('width' =>0.7,'color' => array(128, 128, 128)));
//                 $this->SetY($this->GetY() + 1);
//             }
            $this->writeHTML($this->css.$this->header_html);
            if( $set_header_height ){
                $this->SetTopMargin($this->GetY());
            }

        }
    }



}