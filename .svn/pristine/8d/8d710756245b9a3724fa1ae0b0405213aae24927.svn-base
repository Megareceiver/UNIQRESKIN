<?php
/**
 * Created by PhpStorm.
 * User: QuanICT
 * Date: 6/27/2017
 * Time: 4:07 PM
 */
class PurchasesDetailTransaction
{

    var $tran_no = 0;
    var $tran_type = ST_PURCHORDER;
    var $cart = NULL;

    function __construct()
    {
        //$this->cart =  new supp_trans($this->tran_type);
    }

    function grn_items_box( $mode=0,$show_header=true) {
        $ret = true;
        // if displaying in form, and no items, exit
        if (($mode == 2  || $mode == 3) && count($this->cart->grn_items) == 0)
            return 0;



        $heading2 = "";

        if ($mode == 1) {
            if ($this->cart->trans_type == ST_SUPPINVOICE){
                $heading = _("Items Received Yet to be Invoiced");
                if ($_SESSION["wa_current_user"]->can_access('SA_GRNDELETE')){
                    // Added 2008-10-18 by Joe Hunt. Only admins can remove GRNs
                    $heading2 = _("WARNING! Be careful with removal. The operation is executed immediately and cannot be undone !!!");
                }
            } else {
                $heading = _("Delivery Item Selected For Adding To A Supplier Credit Note");
            }
        } else {
            if ($this->cart->trans_type == ST_SUPPINVOICE)
                $heading = _("Received Items Charged on this Invoice");
            else
                $heading = _("Received Items Credited on this Note");
        }


        box_start($heading);

        start_table();
        if ($mode == 1) {
            if ($this->cart->trans_type == ST_SUPPCREDIT && !isset($_POST['invoice_no']))
            {
                echo "</td>";
                date_cells(_("Received between"), 'receive_begin', "", null, -30, 0, 0, "valign=middle");
                date_cells(_("and"), 'receive_end', '', null, 1, 0, 0, "valign=middle");
                submit_cells('RefreshInquiry', _("Search"),'',_('Refresh Inquiry'), true);
                echo "<td>";
            }

            if ($heading2 != "") {
                display_note($heading2, 0, 0, "class='overduefg'");
            }
            echo "</td><td width=10% align='right'>";
            submit('InvGRNAll', _("Add All Items"), true, false,true);
        }

        end_table();


        div_start('grn_items');
        start_table(TABLESTYLE, "width=95%");
        if ($mode == 1) {
            $th = array(_("Delivery"), _("P.O."), _("Item"), _("Description"),
                _("Received On"), _("Quantity Received"), _("Quantity Invoiced"),
                _("Qty Yet To Invoice"), $this->cart->tax_included ? _("Price after Tax") : _("Price before Tax"),
                _("Tax"),
                _("Total"));
            if (($this->cart->trans_type == ST_SUPPINVOICE) && $_SESSION["wa_current_user"]->can_access('SA_GRNDELETE')){
                // Added 2008-10-18 by Joe Hunt. Only admins can remove GRNs
                $th[] = "";
                $th[] = "";
            }



            if ($this->cart->trans_type == ST_SUPPCREDIT){
                $th[8] = _("Amt Yet To Credit");
                $th[9] = _("GST");
                $th[10] = _("Total");
                $th[11] = '';
            }
        } else
            $th = array(_("Delivery"), _("Item"), _("Description"), _("Quantity"), _("Price"),_("Tax"), _("Line Value"));

        table_header($th);
        $total_grn_value = $total_grn_return = 0;
        $i = $k = 0;

        if (count($this->cart->grn_items) > 0) {
            foreach ($this->cart->grn_items as $entered_grn) {
                alt_table_row_color($k);
                $grn_batch = get_grn_batch_from_item($entered_grn->id);

                label_cell(get_trans_view_str(ST_SUPPRECEIVE,$grn_batch));
                if ($mode == 1) {
                    $row = get_grn_batch($grn_batch);
                    label_cell($row['purch_order_no']); // PO
                }
                label_cell($entered_grn->item_code);
                label_cell($entered_grn->item_description);
                $dec = get_qty_dec($entered_grn->item_code);
                if ($mode == 1) {
                    label_cell(sql2date($row['delivery_date']));
                    qty_cell($entered_grn->qty_recd, false, $dec);
                    qty_cell($entered_grn->prev_quantity_inv, false, $dec);
                }
                qty_cell(abs($entered_grn->this_quantity_inv), true, $dec);
                amount_decimal_cell($entered_grn->chg_price);

// 			$tax = get_tax_type($entered_grn->supplier_tax_id);
// 			$tax_include = tax_calcula($tax['rate'],$entered_grn->chg_price,$entered_grn->tax_included);
                $entered_grn_tax = tax_calculator($entered_grn->supplier_tax_id,$entered_grn->chg_price*$entered_grn->this_quantity_inv,$this->cart->tax_included);
// 			amount_cell(round2(tax_calcula($tax['rate'],$entered_grn->chg_price,$supplier->tax_included),user_price_dec()) , true);
                amount_cell(round2($entered_grn_tax->value,user_price_dec())  , true);


                //amount_cell( round2(($entered_grn_tax->price+$entered_grn_tax->value) * abs($entered_grn->this_quantity_inv )  , user_price_dec()), true);
                amount_cell( $entered_grn_tax->price+$entered_grn_tax->value, true);

// 			amount_cell( round2($entered_grn->chg_price * abs($entered_grn->this_quantity_inv), user_price_dec()), true);

                if ($mode == 1) {
                    delete_button_cell("Delete" . $entered_grn->id, _("Edit"), _('Edit document line'));
                    if (($this->cart->trans_type == ST_SUPPINVOICE) && $_SESSION["wa_current_user"]->can_access('SA_GRNDELETE'))
                        label_cell("");
                }
                end_row();

//     		$total_grn_value += round2($entered_grn->chg_price * abs($entered_grn->this_quantity_inv),  user_price_dec());
                $total_grn_value += $entered_grn_tax->price+$entered_grn_tax->value;
                $total_grn_return += round2($entered_grn->chg_price * abs($entered_grn->this_quantity_inv),  user_price_dec());


                $i++;
                if ($i > 15) {
                    $i = 0;
                    table_header($th);
                }
            }
        }
        if ($mode == 1) {
            $ret = display_grn_items_for_selection($supp_trans, $k);
        }
        $colspan = count($th) - 1;


        label_row(_("Total"), price_format($total_grn_value), "colspan=$colspan align=right", "nowrap align=right",0);

        if (!$ret) {
            start_row();
            echo "<td colspan=".(count($th)).">";
            if ($this->cart->trans_type == ST_SUPPINVOICE)
                display_note(_("There are no outstanding items received from this supplier that have not been invoiced by them."), 0, 0);
            else {
                display_note(_("There are no received items for the selected supplier that have been invoiced."));
                display_note(_("Credits can only be applied to invoiced items."), 0, 0);
            }
            echo "</td>";
            end_row();
        }
        end_table(1);
        div_end();

//        box_end();

        //return $total_grn_value;
        return floatval($total_grn_return);
    }

    function tax_credit_note($columns){

        $taxes = array();

        if( count($this->cart->grn_items) ){ foreach ($this->cart->grn_items AS $grn){

            $price = ( abs($grn->this_quantity_inv) - abs($grn->prev_quantity_inv) )*$grn->chg_price;
            $tax = tax_calculator($grn->supplier_tax_id, $price,$this->cart->tax_included);
            if( $tax && isset($tax->id) ){
                if( !array_key_exists($tax->id, $taxes) ){
                    $taxes[$tax->id] = array('name'=>$tax->name,'rate'=>$tax->rate,'value'=>0);
                }
                $taxes[$tax->id]['value'] -= $tax->value;
            }

        }}

        if( count($this->cart->gl_codes) ){ foreach ($this->cart->gl_codes AS $gl_code){
            $tax = tax_calculator($gl_code->supplier_tax_id, $gl_code->amount ,$this->cart->tax_included);
            if( $tax && isset($tax->id) ){
                if( !array_key_exists($tax->id, $taxes) ){
                    $taxes[$tax->id] = array('name'=>$tax->name,'rate'=>$tax->rate,'value'=>0);
                }
                $taxes[$tax->id]['value'] += $tax->value;
            }

        }}

        $total = 0;
        foreach ($taxes AS $ta){

            if ($this->cart->tax_included){
                label_row(_("Included") . " " . $ta['name'] . " (" . $ta['rate'] . "%) " . ": -".number_format2($ta['value'],user_price_dec()), '', "colspan=$columns align=right", "align=right");
            } else {
                //$ta['value'] = -$ta['value'];
                label_row( $ta['name']."(".$ta['rate']."%)",number_format2($ta['value'],user_price_dec()), "colspan=$columns align=right", "align=right");
            }
            $total +=$ta['value'];


        }

        return -$total;
    }
}