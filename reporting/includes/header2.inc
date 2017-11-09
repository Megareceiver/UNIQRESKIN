<?php
/**********************************************************************
 Copyright (C) FrontAccounting, LLC.
Released under the terms of the GNU General Public License, GPL,
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/

/**
 * Document blueprint use following parameters set in doctext.inc:
 *
 * $Addr1,	$Addr2 - address info
 * $Payment_Terms - payment terms line
 * $Footer - footer texts
 * $this->company - company info
 * $this->title - report title
 * $this->formData - some other info
 * *
 */
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
if( !isset($company['gst_no']) ||  trim($company['gst_no']) =='' ){
	$line_col = 3;
}
for($i = 0; $i < $line_col; $i ++) {
	$this->LineTo ( $col += $width, $iline2, $col, $iline4 );
}
$this->LineTo ( $right, $iline2, $right, $iline4 );
$this->LineTo ( $this->leftMargin, $iline5, $this->leftMargin, $iline7 );
if ($this->l ['a_meta_dir'] == 'rtl') // avoid line overwrite in rtl language
	$this->LineTo ( $this->cols [$cols - 2], $iline5, $this->cols [$cols - 2], $iline7 );
else
	$this->LineTo ( $this->cols [$cols - 2] + 4, $iline5, $this->cols [$cols - 2] + 4, $iline7 );
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
// 	if( $this->company ['coy_no'] ){
// 		$this->Text ( strlen($this->company['coy_name']),$this->company ['coy_no'], $icol );

// 	}

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

	// 	$contactAddress = word_wrap($contactAddress,40);
	$company_address = wordwrap($company_address,50,"\n",true);
	//list( $one, $two ) = explode( PHP_EOL, wordwrap( $contactAddress, 25, PHP_EOL ) );

	$company_address = keepLines($company_address,4);

}
// bug($company_address);
// die('test');
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
$this->NewLine ( 3 );
$this->Text ( $mcol + 100, _ ( "Date" ) );
$this->Text ( $mcol + 180, sql2date ( $this->formData ['document_date'] ) );

$this->NewLine ();
$this->Text ( $mcol + 100, $this->formData ['document_name'] );
$this->Text ( $mcol + 180, $this->formData ['document_number'] );

if ($this->pageNumber > 1)
	$this->Text ( $this->endLine - 35, _ ( "Page" ) . ' ' . $this->pageNumber );

$this->row = $iline1 - $this->lineHeight - 10;
// $this->fontSize += 1;
// $icol=$icol+90;
$this->Text ( $ccol, $Addr1 ['title'], $icol );
$this->Text ( $mcol, $Addr2 ['title'] );
// $this->fontSize = 4;

// address1

$temp = $this->row = $this->row - $this->lineHeight - 5;
$this->Text ( $ccol, $Addr1['name'], $icol );
$this->NewLine ();

if( !empty($this->contactData) ){

	foreach ($this->contactData AS $contact){
		$name ='';
		if( $contact['name'] ){
			$name.= $contact['name'];
		}
// 		if( $contact['name2'] ){
// 			$name.= ' '.$contact['name2'];
// 		}
		if( !$name ) continue;

		$this->Text ( $ccol, 'Contact Person: '.$name, $icol ); $this->NewLine ();

		if( $contact['email'] ){
			$this->Text ( $ccol, $contact ['email'], $icol );
			$this->NewLine ();
		}

		$phone = '';
		if( $contact['phone'] ){
			$phone.= '/'.$contact['phone'];
		}
		// if( $contact['phone2'] ){
		// 	$phone.= '/'.$contact['phone2'];
		// }
		// if( $contact['fax'] ){
		// 	$phone.= '/'.$contact['fax'];
		// }
		if($phone ){
			$this->Text ( $ccol, substr($phone,1), $icol );
			$this->NewLine ();
		}
		// if( $contact['address'] ){
		// 	$this->Text ( $ccol, $contact ['address'], $icol );
		// 	$this->NewLine ();
		// }
	}

}

$contactAddress = '';
if( isset( $this->customer['address']) ){
	$contactAddress = $this->customer['address'];
}


if( trim($contactAddress) != '' ){
	$contactAddress = wordwrap($contactAddress,80,"\n",true);
	$this->TextWrapLines ( $ccol, $icol - $ccol, keepLines($contactAddress,4) );
}

// bug($contactAddress);
// bug($this->company);
// die('quannh');
//
// die('fix');

// $contactAddress = $Addr1['address'];
// bug($this->contactData); die('quannh');
// address2
$this->row = $temp;
$this->Text ( $mcol, $Addr2['name'] );
$this->NewLine ();
$delivered_address = '';

// bug($this->formData['branch']['br_address']); die;
if( isset($this->formData['branch']['br_post_address']) ) {
	$delivered_address = $this->formData['branch']['br_post_address'];
} else if( isset($this->contactData[0]['address']) ){
		$delivered_address = $this->contactData[0]['address'];
// } else if( isset($company['postal_address']) ){
// 	$delivered_address = $company['postal_address'];
} else {
	$delivered_address = $Addr2['address'];
}
// bug( $this->formData['branch']);
// bug($delivered_address);die;

if( $delivered_address != '' ){

	// 	$contactAddress = word_wrap($contactAddress,40);
	$delivered_address = wordwrap($delivered_address,40,"\n",true);
	//list( $one, $two ) = explode( PHP_EOL, wordwrap( $contactAddress, 25, PHP_EOL ) );

	$delivered_address = keepLines($delivered_address,4);
	$this->TextWrapLines ( $mcol, $this->rightMargin - $mcol, $delivered_address );

}




// Auxiliary document information
$col = $this->leftMargin;

// bug($aux_info);die;
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
$this->TextWrap ( $ccol, $this->row, $right - $ccol, $Payment_Terms );
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
$this->row = $iline7 - $this->lineHeight - 6;

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
if (in_array ( input_val('REP_ID'), array (107,110,109) ) ) {
	$this->Font ( 'nomal' );
	$style_customer_confirmation = array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(255, 128, 0));
	$this->rectangle ( $this->leftMargin+350, 85, $right - $this->leftMargin -346, 60, $style_customer_confirmation);
	$this->TextWrap ( $ccol+385, 70,null,"Customer Confirmation");
}


$this->Font ();
$temp = $iline6 - $this->lineHeight - 2;
?>
