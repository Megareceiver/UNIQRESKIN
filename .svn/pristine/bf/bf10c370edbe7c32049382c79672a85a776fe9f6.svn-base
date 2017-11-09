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
/* Definition of the Supplier Transactions class to hold all the information for an accounts payable invoice or credit note
*/

include_once($path_to_root . "/taxes/tax_calc.inc");

class supp_trans
{

	var $grn_items; /*array of objects of class grn_item using the id as the pointer */
	var $gl_codes; /*array of objects of class gl_codes using a counter as the pointer */
	var $supplier_id;
	var $supplier_name;
	var $terms;
	var $ex_rate;

	var $tax_description;
	var $tax_group_id;
	var $tax_included;

	var $trans_type;	// invoice or credit

	var $Comments;
	var $tran_date;
	var $due_date;

	var $fixed_access = 0;
	var $supp_reference;
	var $reference;
	var $ov_amount;
	var $ov_gst_amount = 0;
	var $ov_tax = array();
	var $ov_discount;
	var $ov_gst;
	var $gl_codes_counter=0;
	var $credit = 0;
	var $tax_overrides = array();		// array of taxes manually inserted during sales invoice entry
    var $reason = null;
	function supp_trans($trans_type){
		$this->trans_type = $trans_type;
		/*Constructor function initialises a new Supplier Transaction object */
		$this->grn_items = array();
		$this->gl_codes = array();
	}

	function add_grn_to_trans($supplier_tax_id, $grn_item_id, $po_detail_item, $item_code, $item_description,
		$qty_recd, $prev_quantity_inv, $this_quantity_inv, $order_price, $chg_price,
		$std_cost_unit, $gl_code)
	{
		$this->grn_items[$grn_item_id] = new grn_item($supplier_tax_id, $grn_item_id, $po_detail_item,
			$item_code, $item_description, $qty_recd, $prev_quantity_inv, $this_quantity_inv,
			$order_price, $chg_price, $std_cost_unit, $gl_code, $this->tax_included);

		unset($this->tax_overrides); // cancel tax overrides after cart change
		return 1;
	}

	function add_gl_codes_to_trans($supplier_tax_id, $gl_code, $gl_act_name, $gl_dim, $gl_dim2, $amount, $memo_)
	{
		$this->gl_codes[$this->gl_codes_counter] = new gl_codes($supplier_tax_id, $this->gl_codes_counter,
			$gl_code, $gl_act_name, $gl_dim, $gl_dim2, $amount, $memo_);
		$this->gl_codes_counter++;
		unset($this->tax_overrides); // cancel tax overrides after cart change
		return 1;
	}

	function remove_grn_from_trans($grn_item_id) {
		unset($this->tax_overrides); // cancel tax overrides after cart change
	    unset($this->grn_items[$grn_item_id]);
	}
	function remove_gl_codes_from_trans($gl_code_counter) {
		unset($this->tax_overrides); // cancel tax overrides after cart change
		unset($this->gl_codes[$gl_code_counter]);
	}

	function is_valid_trans_to_post()
	{
		return (count($this->grn_items) > 0 || count($this->gl_codes) > 0 ||
			($this->ov_amount != 0) || ($this->ov_discount > 0));
	}

	function clear_items(){
		unset($this->grn_items);
		unset($this->gl_codes);
		$this->ov_amount = $this->ov_discount = $this->supplier_id = $this->ov_gst_amount = 0;
		$this->grn_items = $this->ov_tax =$this->gl_codes= array();
	}

    function get_taxes($tax_group_id=null, $shipping_cost=0, $gl_codes=true)
    {
    	$items = array();
    	$prices = array();

        if ($tax_group_id == null)
        	$tax_group_id = $this->tax_group_id;

		// preload the taxgroup
		$tax_group = get_tax_group_items_as_array($tax_group_id);

        foreach ($this->grn_items as $ln_itm)
        {
        	$items[] = $ln_itm->item_code;
        	$prices[] =round( ($ln_itm->this_quantity_inv * $ln_itm->chg_price),
				user_price_dec());
        }

        if ($tax_group_id == null)
        	$tax_group_id = $this->tax_group_id;
        $taxes = get_tax_for_items($items, $prices, $shipping_cost, $tax_group_id,
        	$this->tax_included);

		if (isset($this->tax_overrides))
			foreach($this->tax_overrides as $id => $value) // add values entered manually
			{
				$taxes[$id]['Override'] = $value;
			}

		// Taxes included in gl_codes table have exact value, but count to overrides as well.
		// Therefore when we want to now taxes only for items (gl_codes==false),
		// we have to subtract gl_taxes from override values.
		foreach ($this->gl_codes as $gl_code)
		{
			$index = is_tax_account($gl_code->gl_code);
			if ($index !== false)
			{
				if ($gl_codes)
					$taxes[$index]['Value'] += $gl_code->amount;
				elseif (isset($this->tax_overrides))
					$taxes[$index]['Override'] -= $gl_code->amount;
			}
		}

        return $taxes;
    }
//TUANVT7
    function get_taxes_tran_new($tax_group_id=null, $shipping_cost=0, $gl_codes=true)
    {
    	$items = array();
    	$prices = array();
    	$tax = array();

        foreach ($this->grn_items as $ln_itm) {
        	$tax[] = $ln_itm->supplier_tax_id;
        	$items[] = $ln_itm->item_code;
        	$prices[] =round( ($ln_itm->this_quantity_inv * $ln_itm->chg_price),
				user_price_dec());
        }

		$taxes = get_tax_for_items_new_purchases($tax, $items, $prices, $shipping_cost,
		  null, $this->tax_included,  null);

		if (isset($this->tax_overrides))
		foreach($this->tax_overrides as $id => $value) // add values entered manually
		{
			$taxes[$id]['Override'] = $value;
		}

		foreach ($this->gl_codes as $gl_code)
		{
			$index = is_tax_account($gl_code->gl_code);
			if ($index !== false)
			{
				if ($gl_codes)
					$taxes[$index]['Value'] += $gl_code->amount;
				elseif (isset($this->tax_overrides))
					$taxes[$index]['Override'] -= $gl_code->amount;
			}
		}

        return $taxes;
    }
	//
	//	Returns total invoice amount without taxes.
	//
    function get_total_taxfree($tax_group_id=null)
    {
    	$total = 0;

		// preload the taxgroup !
		if ($tax_group_id != null)
			$tax_group = get_tax_group_items_as_array($tax_group_id);
		else
			$tax_group = null;

		foreach ($this->grn_items as $ln_itm)
    		$total += round(($ln_itm->this_quantity_inv * $ln_itm->taxfree_charge_price($tax_group_id, $tax_group)),
			 user_price_dec());

   		foreach ($this->gl_codes as $gl_line)
   		{
   			if (!is_tax_account($gl_line->gl_code))
   				$total += $gl_line->amount;
   		}
   		return $total;
    }

	function get_items_total() {
		$total = 0;
		$this->ov_amount = $this->ov_gst_amount = 0;
		foreach ($this->grn_items as $ln_itm){

		    $item_tax = tax_calculator($ln_itm->supplier_tax_id,$ln_itm->this_quantity_inv * $ln_itm->chg_price, $this->tax_included);
		    $this->ov_gst_amount += $item_tax->value;
		    $this->ov_amount += $item_tax->price;
		}


		foreach ($this->gl_codes as $gl_line) {
   			if (!is_tax_account($gl_line->gl_code) ){
   			    $item_tax = tax_calculator($gl_line->supplier_tax_id,$gl_line->amount, $this->tax_included);


   			    $this->ov_gst_amount += $item_tax->value;
   			    $this->ov_amount += $item_tax->price;
   			}

		}

		return $this->ov_amount;
	}


	function show_tax_footer($colspan=1){
        $total = 0;
	    foreach ($this->gl_codes as $ln_itm){
            $tax = tax_calculator($ln_itm->supplier_tax_id,$ln_itm->amount,$this->tax_included);
            if( !$this->tax_included ){
                $total+=$tax->value;
                label_row( $tax->name ,number_format2($tax->value,user_price_dec()), "colspan=$colspan align=right", "align=right ");
            } else {
                label_row( $tax->name .' '.number_format2($tax->value,user_price_dec()),null, "colspan=$colspan align=right", "align=right ");
            }
	    }
	    return $total;
	}
} /* end of class defintion */

class grn_item {

/* Contains relavent information from the purch_order_details as well to provide in cached form,
all the info to do the necessary entries without looking up ie additional queries of the database again */

	var $id;
	var $po_detail_item;
	var $item_code;
	var $item_description;
	var $qty_recd;
	var $prev_quantity_inv;
	var $this_quantity_inv;
	var $order_price;
	var $chg_price;
	var $std_cost_unit;
	var $gl_code;
	var $tax_included;
	var $supplier_tax_id;

	function grn_item ($supplier_tax_id, $id, $po_detail_item, $item_code, $item_description, $qty_recd,
		$prev_quantity_inv, $this_quantity_inv, $order_price, $chg_price,
		$std_cost_unit, $gl_code, $tax_included)
	{

		$this->supplier_tax_id = $supplier_tax_id;
		$this->id = $id;
		$this->po_detail_item = $po_detail_item;
		$this->item_code = $item_code;
		$this->item_description = $item_description;
		$this->qty_recd = $qty_recd;
		$this->prev_quantity_inv = $prev_quantity_inv;
		$this->this_quantity_inv = $this_quantity_inv;
		$this->order_price =$order_price;
		$this->chg_price = $chg_price;
		$this->std_cost_unit = $std_cost_unit;
		$this->gl_code = $gl_code;
		$this->tax_included = $tax_included;
	}

	function full_charge_price($tax_group_id, $tax_group=null)
	{
		return get_full_price_for_item($this->item_code,
		  $this->chg_price, $tax_group_id, $this->tax_included, $tax_group);
	}

	function taxfree_charge_price($tax_group_id, $tax_group=null)
	{
		return get_tax_free_price_for_item($this->item_code, $this->chg_price,
		  $tax_group_id, $this->tax_included, $tax_group);
	}
}


class gl_codes
{

	var $Counter;
	var $gl_code;
	var $gl_act_name;
	var $gl_dim;
	var $gl_dim2;
	var $amount;
	var $memo_;
	var $supplier_tax_id;

	function gl_codes ($supplier_tax_id, $Counter, $gl_code, $gl_act_name, $gl_dim, $gl_dim2, $amount, $memo_)
	{

	/* Constructor function to add a new gl_codes object with passed params */
		$this->supplier_tax_id = $supplier_tax_id;
		$this->Counter = $Counter;
		$this->gl_code = $gl_code;
		$this->gl_act_name = $gl_act_name;
		$this->gl_dim = $gl_dim;
		$this->gl_dim2 = $gl_dim2;
		$this->amount = $amount;
		$this->memo_= $memo_;
	}
}

?>
