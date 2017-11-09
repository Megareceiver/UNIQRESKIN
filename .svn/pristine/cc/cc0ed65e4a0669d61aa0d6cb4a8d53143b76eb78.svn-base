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
// Link to printing single document with bulk report template file.
// Ex. label_cell(print_document_link($myrow['order_no'], _("Print")), $type);
// or display_note(print_document_link($order_no, _("Print this order")));
// You only need full parameter list for invoices/credit notes

function print_document_link($doc_no, $link_text, $link=true, $type_no,
	$icon=false, $class='printlink', $id='', $email=0, $extra=0,$invoice_print=0)
{
	global $path_to_root, $def_print_orientation;
	include_once(ROOT . "/includes/types.inc");

	$url = $path_to_root.'/reporting/prn_redirect.php?';
	$def_orientation = (isset($def_print_orientation) && $def_print_orientation == 1 ? 1 : 0);

	switch ($type_no) {
		case ST_SALESQUOTE :
			$rep = 111;
			// from, to, currency, email, comments, orientation
			$ar = array(
				'PARAM_0' => $doc_no,
				'PARAM_1' => $doc_no,
				'PARAM_2' => '',
				'PARAM_3' => $email,
				'PARAM_4' => '',
				'PARAM_5' => $def_orientation);
			break;
		case ST_SALESORDER :
			$rep = 109;
			// from, to, currency, email, quote, comments, orientation
			$ar = array(
				'PARAM_0' => $doc_no,
				'PARAM_1' => $doc_no,
				'PARAM_2' => '',
				'PARAM_3' => $email,
				'PARAM_4' => 0,
				'PARAM_5' => '',
				'PARAM_6' => $def_orientation);
			break;
		case ST_CUSTDELIVERY :
			$rep = 110;
			// from, to, email, packing slip, comments, orientation
			$ar = array(
				'PARAM_0' => $doc_no,
				'PARAM_1' => $doc_no,
				'PARAM_2' => $email,
				'PARAM_3' => $extra,
				'PARAM_4' => '',
				'PARAM_5' => $def_orientation);
			break;
		case ST_SALESINVOICE : // Sales Invoice
		case ST_CUSTCREDIT : // Customer Credit Note
			$rep = $type_no==ST_CUSTCREDIT ? 113 : 107;
			// from, to, currency, email, paylink, comments, orientation
			$ar = array(
				'PARAM_0' => $doc_no,
				'PARAM_1' => $doc_no,
				'PARAM_2' => '',
				'PARAM_3' => $email,
				'PARAM_4' => '',
				'PARAM_5' => '',
				'PARAM_6' => $def_orientation);
			break;
		case ST_PURCHORDER :
			$rep = 209;
			// from, to, currency, email, comments, orientation
			$ar = array(
				'PARAM_0' => $doc_no,
				'PARAM_1' => $doc_no,
				'PARAM_2' => '',
				'PARAM_3' => $email,
				'PARAM_4' => '',
				'PARAM_5' => $def_orientation);
			//$url = $path_to_root.'report/pdf.php?';
			break;
		case ST_CUSTPAYMENT :
			$rep = 112;
			// from, to, currency, comments, orientation
			$ar = array(
				'PARAM_0' => $doc_no,
				'PARAM_1' => $doc_no,
				'PARAM_2' => '',
				'PARAM_3' => '',
				'PARAM_4' => $def_orientation);
			break;
		case ST_SUPPAYMENT :
			$rep = 210;
			// from, to, currency, email, comments, orientation
			$ar = array(
				'PARAM_0' => $doc_no,
				'PARAM_1' => $doc_no,
				'PARAM_2' => '',
				'PARAM_3' => $email,
				'PARAM_4' => '',
				'PARAM_5' => $def_orientation);
			break;
		case ST_WORKORDER :
			$rep = 409;
			// from, to, email, comments orientation
			$ar = array(
				'PARAM_0' => $doc_no,
				'PARAM_1' => $doc_no,
				'PARAM_2' => $email,
				'PARAM_3' => '',
				'PARAM_4' => $def_orientation);
			break;
		case  ST_GLPAYMENT:
			$rep = 801;
			$ar = array(
					'PARAM_0' => $doc_no,
					'PARAM_1' => $doc_no,
					'PARAM_2' => $email,
					'PARAM_3' => '',
					'PARAM_4' => $def_orientation);
			break;
		case  ST_GLDEPOSIT:
			$rep = 802;
			$ar = array(
					'PARAM_0' => $doc_no,
					'PARAM_1' => $doc_no,
					'PARAM_2' => $email,
					'PARAM_3' => '',
					'PARAM_4' => $def_orientation);
			break;
		case ST_BANKTRANSFER:
		case ST_JOURNAL:
			$rep = 803;
			$ar = array(
				'type' => $type_no,
				'trans_no'=>$doc_no,
				'save'=>'false'
			);
			break;
		case ST_SUPPINVOICE:
		    $rep = 211;
		    $ar = array(
		        'type' => $type_no,
		        'trans_no'=>$doc_no,

		    );
		    break;
//		default: $ar = array();
	}
	if( $invoice_print ){
	    $ar['theme'] = 'invoice';
	}
    if( isset($rep) AND isset($ar) ){
        if( $icon !== false ){
            $icon = ICON_PRINT;
        }
        return print_link($link_text, $rep, $ar, "", $icon, $class, $id);
    }

}
//
//	Universal link to any kind of report.
//
function print_link($link_text, $rep, $pars = array(), $dir = '',
	$icon=false, $class='printlink', $id='')
{
	global $pdf_debug;

// 	$path_to_root = site_url();
	$url = $dir == '' ?  '/reporting/prn_redirect.php?' : $dir;

	if( in_array($rep, array(110,111,112,201,209,210,211,109,107,113,801,802,803))){
		$url = '/report/pdf.php?';
	}
	$id = default_focus($id);
	foreach($pars as $par => $val) {
		$pars[$par] = "$par=".urlencode($val);
	}
	$pars[] = 'REP_ID='.urlencode($rep);
	$url .= implode ('&', $pars);

	if( !$icon OR strlen($icon) < 1 ){
	    $icon = 'print';
	}

	$attributes = array(
	    'target'=>'_blank',

	);

	if ($id != ''){
	    $attributes['id']=$id;
	    $id = " id='$id'";
	}

	$pars = access_string($link_text);

    if( $icon == ICON_PRINT ){
        $class = NULL;
        $icon = 'fa-file-pdf-o';
        $attributes['class'] = 'button text-success';
    }
	if( ( $icon ) AND $class != 'printlink' ){
	    if (user_graphic_links() && $icon)
	        $pars[0] = set_icon($icon, $pars[0]);
// 	    return "<a target='_blank' href='$url'$id$class $pars[1]></a>";
	    return anchor($url,'<i class="fa '.$icon.' "></i>',$attributes);
	} else {
	    return anchor($url,$pars[0],$attributes);
	}
// 	return "<a target='_blank' href='$url'$id$class $pars[1]>$pars[0]</a>";
}

?>