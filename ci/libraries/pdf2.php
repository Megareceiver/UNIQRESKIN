<?php defined('BASEPATH') OR exit('No direct script access allowed');

class pdf2 {
	var $margin_left = 40;
	var $margin_right = 40;
	var $margin_top = 10;
	var $amount_w = 96.65;
	function __construct(){
		$this->pdf = $this->load();
		//$this->setup();
	}

	function load($mode='utf-8',$format='A4',$default_font_size=0,$default_font='',$mgl=15,$mgr=15,$mgt=0,$mgb=16,$mgh=9,$mgf=9, $orientation='P'){
	    if( !class_exists('TCPDF') ){
	        require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/config/tcpdf_config.php');
	        require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/tcpdf.php');

	    }
		return new TCPDF('p', 'mm', 'A5', true, 'UTF-8', false);
	}

	function setup(){

		$this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		// bug($pdf); die('go here');

		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor('accountattanttoday.net');
		$this->pdf->SetTitle('GST');
		$this->pdf->SetSubject('GST export');
		// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 061', PDF_HEADER_STRING);

		$this->pdf->setPrintHeader(false);
		$this->pdf->setPrintFooter(false);
		$this->pdf->SetMargins($this->margin_left, $this->margin_top , $this->margin_right, true);
		// $pdf->SetAutoPageBreak(true);
		$this->pdf->SetAutoPageBreak(TRUE, 0);

		$this->pdf->SetFont('helvetica', '', 12);

		$tagvs = array(
			'p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
			'h1' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0))

		);
		$this->pdf->setHtmlVSpace($tagvs);
		$this->pdf->setCellHeightRatio(1.25);

	}

	function report_print(){



	}
}