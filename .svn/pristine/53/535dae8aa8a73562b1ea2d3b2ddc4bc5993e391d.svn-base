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

if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}
elseif (isset($_POST["trans_no"]))
{
	$trans_no = $_POST["trans_no"];
}

$supp_trans = new supp_trans(ST_SUPPCREDIT);

read_supp_invoice($trans_no, ST_SUPPCREDIT, $supp_trans);

display_heading("<font color=red>" . _("SUPPLIER CREDIT NOTE") . " # " . $trans_no . "</font>");
echo "<br>";
start_table(TABLESTYLE, "width=95%");
start_row();
label_cells(_("Supplier"), $supp_trans->supplier_name, "class='tableheader2'");
label_cells(_("Reference"), $supp_trans->reference, "class='tableheader2'");
label_cells(_("Supplier's Reference"), $supp_trans->supp_reference, "class='tableheader2'");
end_row();
start_row();
label_cells(_("Invoice Date"), $supp_trans->tran_date, "class='tableheader2'");
label_cells(_("Due Date"), $supp_trans->due_date, "class='tableheader2'");
label_cells(_("Currency"), get_supplier_currency($supp_trans->supplier_id), "class='tableheader2'");
end_row();
comments_display_row(ST_SUPPCREDIT, $trans_no);
end_table(1);

$total_gl = display_gl_items($supp_trans, 3);
$total_grn = display_grn_items($supp_trans, 2);

$display_sub_tot = number_format2(-$total_gl+$total_grn,user_price_dec(),user_price_dec());

start_table(TABLESTYLE, "width=95%");
label_row(_("Sub Total"), $display_sub_tot, "align=right", "nowrap align=right width=17%");

// $tax_items = get_trans_tax_details(ST_SUPPCREDIT, $trans_no);
// display_supp_trans_tax_details($tax_items, 1);
$tax_amount = view_tax_credit_note($supp_trans,1);
$display_total = number_format2( -($total_gl+$total_grn+$tax_amount),user_price_dec());

label_row("<font color=red>" . _("TOTAL CREDIT NOTE") . "</font", "<font color=red>$display_total </font>",
	"colspan=1 align=right", "nowrap align=right");

end_table(1);

$voided = is_voided_display(ST_SUPPCREDIT, $trans_no, _("This credit note has been voided."));

if (!$voided){
	display_allocations_from(PT_SUPPLIER, $supp_trans->supplier_id, ST_SUPPCREDIT, $trans_no, -($supp_trans->ov_amount + $supp_trans->ov_gst));
}

end_page(true, false, false, ST_SUPPCREDIT, $trans_no);

?>