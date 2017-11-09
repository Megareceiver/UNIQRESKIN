<?php
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
    define('PAGE_NUM_WIDTH', 60);
    // 	        define('TITLE_FONT_SIZE', 14);
    define('HEADER1_FONT_SIZE', 10);
    define('HEADER2_FONT_SIZE', 9);
    define('FOOTER_FONT_SIZE', 10);
    // 	        define('FOOTER_MARGIN', 60);
}
// Set some variables which control header item layout
$companyCol = $this->endLine - COMPANY_WIDTH;
$header_col1 = $this->leftMargin + 70;
$header_col2 = $this->leftMargin + 350;
$pageNumCol = $this->endLine - PAGE_NUM_WIDTH;

$this->SetDrawColor(128, 128, 128);

$this->row = $this->pageHeight - $this->topMargin;

$this->Text($this->leftMargin, strtoupper($this->title));
$this->Text($header_col2, $this->company['coy_name']);
$this->NewLine(1.8);
$this->Text($this->leftMargin, "Print Out Date");
$this->Text($header_col1, Today() . '   ' . Now());
$this->Text($header_col2, $this->host);
$this->NewLine();

$this->Text($this->leftMargin, "Fiscal Year");
$this->Text($header_col1, $this->fiscal_year);
$this->Text($header_col2, $_SESSION["wa_current_user"]->name);
$this->NewLine();
$this->Text($this->leftMargin, "Period");
$this->Text($header_col1, $this->params['period']);
$this->Text($header_col2, "Page : ".$this->pageNumber);

$this->NewLine();
$this->Text($this->leftMargin, "Bank Account");
$this->Text($header_col1, $this->params['bank_account']);

if( is_array($this->headers) && !empty($this->headers)){


    $header_height = 22;
    $this->NewLine(1.5,0,$header_height);
//     $this->SetFillColor(234,234,234);
    foreach ($this->headers AS $index=>$title){
        $this->TextColHeight($index, $index + 1, $title, $header_height, $fill=0);
    }
//     $this->SetFillColor(0,0,0);
    $this->NewLine(1,0,-8);

    $this->Line($this->row -4, 1);
//     $this->SetDrawColor($oldDrawColor[0], $oldDrawColor[1], $oldDrawColor[2]);
    if ($this->pageNumber == 1)
    {
        $this->NewLine(1,0,4);
    } else {
        $this->NewLine(1,0,5);
    }

}
$this->NewLine();
$this->fontSize = $fontSizeDefault;
$this->Font();
?>