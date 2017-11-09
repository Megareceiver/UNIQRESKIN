<?php

$this->row = $this->pageHeight - $this->topMargin;

$upper = $this->row - 2 * $this->lineHeight;
$lower = $this->bottomMargin + 8 * $this->lineHeight;
$iline1 = $upper - 7 * $this->lineHeight;
$iline2 = $iline1 - 8 * $this->lineHeight;
$iline3 = $iline2 - 1.5 * $this->lineHeight;
$iline4 = $iline3 - 1.5 * $this->lineHeight;
$iline5 = $iline4 - 3 * $this->lineHeight;
$iline6 = $iline5 - 1.5 * $this->lineHeight;
$iline7 = $lower;
$right = $this->pageWidth - $this->rightMargin;
$width = ($right - $this->leftMargin) / 5;
$icol = $this->pageWidth / 2;
$ccol = $this->cols [0] + 4;
$c2col = $ccol + 60;
$ccol2 = $icol / 2;
$mcol = $icol + 8;
$mcol2 = $this->pageWidth - $ccol2;
$cols = count ( $this->cols );
$this->SetDrawColor ( 205, 205, 205 );
$this->Line ( $iline1, 3 );
$this->SetDrawColor ( 128, 128, 128 );
$this->Line ( $iline1 );
$this->rectangle ( $this->leftMargin, $iline2, $right - $this->leftMargin, $iline2 - $iline3, "F", null, array ( 222,231,236) );

$this->Line ( $iline2 );
$this->Line ( $iline3 );
$this->Line ( $iline4 );

$this->rectangle ( $this->leftMargin, $iline5, $right - $this->leftMargin, $iline5 - $iline6, "F", null, array (
        222,
        231,
        236
) );

$this->Line ( $iline5 );
$this->Line ( $iline6 );
$this->Line ( $iline7 );
$this->LineTo ( $this->leftMargin, $iline2, $this->leftMargin, $iline4 );
$col = $this->leftMargin;

$line_col = 4;
// if( !isset($company['gst_no']) ||  trim($company['gst_no']) =='' ){
//  $line_col = 3;
// }
for($i = 0; $i < $line_col; $i ++) {
    $this->LineTo ( $col += $width, $iline2, $col, $iline4 );
}

$this->LineTo ( $right, $iline2, $right, $iline4 );
$this->LineTo ( $this->leftMargin, $iline5, $this->leftMargin, $iline7 );
if ($this->l ['a_meta_dir'] == 'rtl') { // avoid line overwrite in rtl language
    $this->LineTo ( $this->cols [$cols - 2], $iline5, $this->cols [$cols - 2], $iline7 );
} else {
    $this->LineTo ( $this->cols [$cols - 2] + 4, $iline5, $this->cols [$cols - 2] + 4, $iline7 );
}


$this->LineTo ( $right, $iline5, $right, $iline7 );

// bug($this);die;
// Company Logo
$this->NewLine ();
$logo = company_path () . "/images/" . $this->company ['coy_logo'];
if ($this->company ['coy_logo'] != '' && file_exists ( $logo )) {
    $this->AddImage ( $logo, $ccol, $this->row, 0, 40 );
    $this->NewLine ();
    //$this->Font ( 'bold' );
    $this->Font ( 'bold' );
    $this->Text ( $ccol,$this->company['coy_name'], $icol );
//  if( $this->company ['coy_no'] ){
//      $this->Text ( strlen($this->company['coy_name']),$this->company ['coy_no'], $icol );

//  }

} else {
    $this->fontSize += 4;
    $this->Font ( 'bold' );
    $this->Text ( $ccol, $this->company ['coy_name'], $icol );
    $this->Font ();
    $this->fontSize -= 4;

}
// $this->Text($ccol, $this->company['coy_name']);
// Document title
$this->SetTextColor ( 190, 190, 190 );
$this->fontSize += 10;
$this->Font ( 'bold' );
$this->TextWrap ( $mcol, $this->row, $this->pageWidth - $this->rightMargin - $mcol - 20, $this->title, 'right' );
$this->Font ();
$this->fontSize -= 10;
$this->NewLine ();
$this->SetTextColor ( 0, 0, 0 );
$adrline = $this->row;
// $icol+100;
// $icol= $icol + 90;
// Company data
// $this->TextWrapLines($ccol, $icol, 'aaaa');
// bug($this);
// bug($Addr1);die;
if (in_array ( input_val('REP_ID'), array (107,110) ) ) {
    // $this->TextWrapLines ( $ccol, $icol, $this->company['coy_name'] );
    // $this->TextWrapLines ( $ccol, $icol, $this->formData['deliver_to'] );
}


$company_address = trim($this->company['postal_address']);
if( $company_address != '' ){
    $company_address = wordwrap($company_address,50,"\n",true);
    $company_address = keepLines($company_address,4);
}
$this->TextWrapLines( $ccol, $icol, $company_address );
$this->Font ( 'italic' );

// bug($this->company); die;
if (@$this->company ['phone']) {
    $this->Text ( $ccol, _ ( "Phone" ), $c2col );
    $this->Text ( $c2col, $this->company ['phone'], $mcol );
    $this->NewLine ();
}


if (@$this->company ['fax']) {
    $this->Text ( $ccol, _ ( "Fax" ), $c2col );
    $this->Text ( $c2col, $this->company ['fax'], $mcol );
    $this->NewLine ();
}
if (@$this->company ['email']) {
    $this->Text ( $ccol, _ ( "Email" ), $c2col );

    $url = "mailto:" . $this->company ['email'];
    $this->SetTextColor ( 0, 0, 255 );
    $this->Text ( $c2col, $this->company ['email'], $mcol );
    $this->SetTextColor ( 0, 0, 0 );
    $this->addLink ( $url, $c2col, $this->row, $mcol, $this->row + $this->lineHeight );

    $this->NewLine ();
}
if (@$this->company ['gst_no']) {
    $this->Text ( $ccol, _ ( "Our GST No." ), $c2col );
    $this->Text ( $c2col, $this->company ['gst_no'], $mcol );
    $this->NewLine ();
}

/*
if (@$this->formData ['domicile']) {
    $this->Text ( $ccol, _ ( "Domicile" ), $c2col );
    $this->Text ( $c2col, $this->company ['domicile'], $mcol );
    $this->NewLine ();
}
*/

$this->Font ();
$this->row = $adrline;
$this->NewLine ( 2 );

$this->Text ( $mcol + 100, _ ( "Date" ) );
if( isset($this->params['tran_date']) ){
    $this->Text ( $mcol + 160, $this->params['tran_date'] );
}


$this->NewLine ();

switch ($this->tran_type){
    case ST_SALESORDER:
        $tran_no_title = 'Order No';break;
    case ST_SALESINVOICE:
        $tran_no_title = 'Invoice No';break;
    default:
        $tran_no_title = 'Tran No'; break;
}
$this->Text ( $mcol + 100, $tran_no_title );
if( isset($this->params['reference']) ){
    $this->Text ( $mcol + 160, $this->params['reference'] );
}

$this->NewLine ();
$this->Text ( $mcol + 100, ( "Page" ) );
$this->Text ( $mcol + 160, $this->pageNumber );

$this->row = $iline1 - $this->lineHeight - 10;


if( isset($this->params['delivery_info']) && is_array($delivery_info = $this->params['delivery_info']) && count($delivery_info) >0 ){
    $delivery_keys = array_keys($delivery_info);
    $this->Text ( $ccol, $delivery_keys[0], $icol );
    $this->Text ( $mcol, $delivery_keys[1] );

    $temp = $this->row = $this->row - $this->lineHeight - 5;
    $order_address = wordwrap($delivery_info[$delivery_keys[0]],40,"\n",true);
    $order_address = keepLines($order_address,4);
    $this->TextWrapLines ( $ccol, $this->rightMargin - $icol, $order_address );
    $order_address_line = explode("\n", $order_address);
    if( !empty($order_address_line) ){
        $this->NewLine (-count($order_address_line));
    }
//     $this->Text ( $ccol, $delivery_info[$delivery_keys[0]], $icol );

    

    $delivered_address = $delivery_info[$delivery_keys[1]];
    if( $delivery_keys[1] == "Deliver To" AND strlen($delivered_address) < 1  ){
        $delivered_address = $this->print_contact();
    }
    
    if( strlen($delivered_address) > 0 ){
        
//         $this->NewLine (-$delivery_line);
        $delivered_address = wordwrap($delivered_address,40,"\n",true);
        //list( $one, $two ) = explode( PHP_EOL, wordwrap( $contactAddress, 25, PHP_EOL ) );
        $delivered_address = keepLines($delivered_address,4);
        $this->TextWrapLines ( $mcol, $this->rightMargin - $icol, $delivered_address );
    }


}

// Auxiliary document information
$col = $this->leftMargin;
if( isset($this->params['aux_info']) && is_array($aux_info = $this->params['aux_info']) && count($aux_info) >0 )
foreach ( $aux_info as $info_header => $info_content ) {

    $this->row = $iline2 - $this->lineHeight - 1;
    $this->TextWrap ( $col, $this->row, $width, $info_header, 'C' );

    $this->row = $iline3 - $this->lineHeight - 1;
    $this->TextWrap ( $col, $this->row, $width, $info_content, 'C' );
    $col += $width;
}


// Payment terms
$this->row -= (2 * $this->lineHeight);
$this->Font ( 'italic' );
$this->TextWrap ( $ccol, $this->row, $right - $ccol, "Payment Terms : ".$this->params['payment_terms'] );
$this->Font ();

// Line headers
$this->row = $iline5 - $this->lineHeight - 1;
$this->Font ( 'bold' );
$count = count ( $this->headers );

$this->cols [$count] = $right - 3;
for($i = 0; $i < $count; $i ++) {
    if (in_array ( input_val('REP_ID'), array (
            107,
            110
    ) ) && $this->headers [$i] == 'Price') {
        $this->TextCol ( $i, $i + 1, '', - 2 );
    } else {
        $this->TextCol ( $i, $i + 1, $this->headers [$i], - 2 );
    }
}

$this->Font ();



// Footer
$this->Font ( 'italic' );
// $this->row = $iline7 - $this->lineHeight - 6;
// $this->TextWrap ( $ccol, $this->row, $right - $ccol, "aaa", 'C' );
/*
foreach ( $Footer as $line => $txt ) {
    if (! is_numeric ( $line )) // title => link
{
        $this->fontSize -= 2;
        $this->TextWrap ( $ccol, $this->row, $right - $ccol, $line, 'C' );
        $this->row -= $this->lineHeight;
        $this->SetTextColor ( 0, 0, 255 );
        $this->TextWrap ( $ccol, $this->row, $right - $ccol, $txt, 'C' );
        $this->SetTextColor ( 0, 0, 0 );
        $this->addLink ( $txt, $ccol, $this->row, $this->pageWidth - $this->rightMargin, $this->row + $this->lineHeight );
        $this->fontSize += 2;
    } else
        $this->TextWrap ( $ccol, $this->row, $right - $ccol, $txt, 'C' );
    $this->row -= $this->lineHeight;
}
*/
// if (in_array ( input_val('REP_ID'), array (107,110,109) ) ) {
//  $this->Font ( 'nomal' );
//  $style_customer_confirmation = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(255, 128, 0));
//  $this->rectangle ( $this->leftMargin+350, 85, $right - $this->leftMargin -346, 60, $style_customer_confirmation);
//  $this->TextWrap ( $ccol+385, 70,null,"Customer Confirmation");
// }





    $block_height = 70;
    $footer_height = 0;
    $footerRow = $block_height + 40;


    $block_width = ($this->pageWidth - $this->leftMargin - $this->rightMargin) / 3 - 10;

    $footerRow -= $this->lineHeight;
    $this->addTextWrap(
        $this->leftMargin, $footerRow - $block_height,
        $width = $block_width,
        $height= $block_height,
        NULL,NULL,1 );

    $this->addTextWrap(
        $this->leftMargin, $footerRow - $block_height,
        $width = $block_width,
        $height= $this->lineHeight +10,
        "Prepared By","C" );


    $this->addTextWrap(
        $this->leftMargin + $block_width + 15, $footerRow - $block_height,
        $width = $block_width,
        $height= $block_height,
        NULL,NULL,1 );

    $this->addTextWrap(
        $this->leftMargin + $block_width + 15, $footerRow - $block_height,
        $width = $block_width,
        $height= $this->lineHeight +10,
        "Approved By",
        "C"
    );

    $this->addTextWrap(
        $this->leftMargin + $block_width*2 + 30, $footerRow - $block_height,
        $width = $block_width,
        $height= $block_height,
        NULL,NULL,1 );

    $this->addTextWrap(
        $this->leftMargin + $block_width*2 + 30, $footerRow - $block_height,
        $width = $block_width,
        $height= $this->lineHeight +10,
        "Received By",
        "C"
    );


$this->Font ();
$temp = $iline6 - $this->lineHeight - 2;
$this->NewLine(2);
?>