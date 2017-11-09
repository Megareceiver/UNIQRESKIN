<?php

/*
 * QuanNH add to bank voucher report 801|802|803
 */

$oldcMargin = $this->cMargin;
$this->SetCellPadding(0);
$fontSizeDefault = $this->fontSize;
$this->footerEnable = false;

// Set some constants which control header item layout
// only set them once or the PHP interpreter gets angry
if ($this->pageNumber == 1)
{
    define('COMPANY_WIDTH', 150);
    define('LOGO_HEIGHT', 30);
    define('LOGO_Y_POS_ADJ_FACTOR', 0.35);
    define('LABEL_WIDTH', 100);
    define('PAGE_NUM_WIDTH', 60);
    // 	        define('TITLE_FONT_SIZE', 14);
    define('HEADER1_FONT_SIZE', 10);
    define('HEADER2_FONT_SIZE', 9);
    define('FOOTER_FONT_SIZE', 10);
    // 	        define('FOOTER_MARGIN', 60);
}
// Set some variables which control header item layout
$companyCol = $this->endLine - COMPANY_WIDTH;
$headerFieldCol = $this->leftMargin + LABEL_WIDTH;
$pageNumCol = $this->endLine - PAGE_NUM_WIDTH;



$this->row = $this->pageHeight - $this->topMargin;

// Set the color of dividing lines we'll draw
$oldDrawColor = $this->GetDrawColor();
$this->SetDrawColor(128, 128, 128);

// Tell TCPDF that we want to use its alias system to track the total number of pages
// 	    $this->AliasNbPages();

// Footer

if ($this->footerEnable)
{

}

//
// Header
//

$logo = $this->company['coy_logo'];

if ( $logo && file_exists($logo=company_path() . "/images/" .$logo ))
{
    $logo = realpath($logo);
    list($logo_width, $logo_height, $logo_type, $logo_attr) = getimagesize($logo);
    $this->AddImage($logo, $this->leftMargin, $this->row - (LOGO_HEIGHT * LOGO_Y_POS_ADJ_FACTOR), 0, LOGO_HEIGHT);
} else {
    $this->Font ( 'bold' );
    $this->Text($this->leftMargin, $this->company['coy_name']);
    $this->Font ();
}
$this->fontSize = 16;
$this->Text(0, strtoupper($this->title), 0, 0, 0, 'right');
$this->NewLine();

$this->Line($this->row - 8, 1);

$this->fontSize = $fontSizeDefault ;
$this->Font();
if (count($this->params) > 0 )foreach ($this->params AS $k=>$var){
    if( is_string($var) ){
        $this->NewLine();
        if( is_string($k) && strlen($k) > 3 ){
            $this->Font('B');
            $this->Text($this->leftMargin, $k);
            $this->Font();
            $this->Text($this->leftMargin+100, ": $var");
        } else {
            $this->Text($this->leftMargin, $var, $headerFieldCol);
        }

    }
}


$this->Line($this->row - 8, 1);
$this->row -= ($this->lineHeight + 8);


if( is_array($this->headers) && !empty($this->headers)){
    $this->NewLine(0.8);
    $this->Text($this->leftMargin, "Begin Payment For");
    $this->Font();

    $header_height = 22;
    $this->NewLine(1.5,0,$header_height);
    $this->SetFillColor(234,234,234);
    foreach ($this->headers AS $index=>$title){
        $this->TextColHeight($index, $index + 1, $title, $header_height, $fill=1);
    }
    $this->SetFillColor(0,0,0);
    $this->NewLine(1,0,-3);
    $this->Line($this->row -4, 1);
    $this->SetDrawColor($oldDrawColor[0], $oldDrawColor[1], $oldDrawColor[2]);
    if ($this->pageNumber == 1)
    {
        $this->NewLine(1,0,-7);
    } else {
        $this->NewLine(1,0,5);
    }

}
$this->NewLine();
$this->fontSize = $fontSizeDefault;
$this->Font();
?>