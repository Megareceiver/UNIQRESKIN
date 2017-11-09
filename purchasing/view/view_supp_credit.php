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
$page_security = 'SA_SUPPTRANSVIEW';
$path_to_root = "../..";

include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");

function view_tax_credit_note($supp_trans,$columns){

    $taxes = array();

    if( count($supp_trans->grn_items) ){ foreach ($supp_trans->grn_items AS $grn){

        $price = ( abs($grn->this_quantity_inv) - abs($grn->prev_quantity_inv) )*$grn->chg_price;
        $tax = tax_calculator($grn->supplier_tax_id, $price,$supp_trans->tax_included);
        if( $tax && isset($tax->id) ){
            if( !array_key_exists($tax->id, $taxes) ){
                $taxes[$tax->id] = array('name'=>$tax->name,'rate'=>$tax->rate,'value'=>0);
            }
            $taxes[$tax->id]['value'] -= $tax->value;
        }

    }}

    if( count($supp_trans->gl_codes) ){ foreach ($supp_trans->gl_codes AS $gl_code){
        $tax = tax_calculator($gl_code->supplier_tax_id, $gl_code->amount ,$supp_trans->tax_included);
        if( $tax && isset($tax->id) ){
            if( !array_key_exists($tax->id, $taxes) ){
                $taxes[$tax->id] = array('name'=>$tax->name,'rate'=>$tax->rate,'value'=>0);
            }
            $taxes[$tax->id]['value'] += $tax->value;
        }

    }}

    $total = 0;
    foreach ($taxes AS $ta){
        if ($supp_trans->tax_included){
            label_row(_("Included") . " " . $ta['name'] . " (" . $ta['rate'] . "%) " . ": -".number_format2($ta['value'],user_price_dec()), '', "colspan=$columns align=right", "align=right");
        } else {
            label_row( $ta['name']."(".$ta['rate']."%)",'-'.number_format2($ta['value'],user_price_dec()), "colspan=$columns align=right", "align=right");
        }
        $total +=$ta['value'];
        return $total;

    }

    return $total;
}

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(900, 500);
page(_($help_context = "View Supplier Credit Note"), true, false, "", $js);

$conttrol_ci = module_control_load('detail/credit','purchases');
$conttrol_ci->view();

end_page(true, false, false, ST_SUPPCREDIT, $trans_no);

?>