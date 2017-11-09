<?php
class Purchase_cart {
	function __construct(){
		global $ci;

		$this->ini();
		$this->po_model = $ci->model('purch_order',true);
		$this->ci = $ci;
	}

	function taxes_load(){
		$tax_model = $this->ci->model('tax',true);

		$taxes = $tax_model->tax_type_items();
		if( $taxes && count($taxes) >0 ){ foreach ($taxes AS $tax){
			$this->taxes[$tax->id] = $tax;
		}}

	}

	private function ini(){
		$this->trans_type = null;
		$this->line_items = null;
		$this->curr_code = null;
		$this->supp_ref = null;
		$this->fixed_access = null;
		$this->delivery_address = null;
		$this->comments = null;
		$this->location = null;
		$this->supplier_id = null;
		$this->supplier_name = null;
		$this->orig_order_date = null;
		$this->due_date = null;
		$this->order_no = null; /*Only used for modification of existing orders otherwise only established when order committed */
		$this->line_total = 0;
		$this->lines_on_order = 0;
		$this->credit = null;
		$this->tax_group_id = null;
		$this->tax_group_array = null; // saves db queries
		$this->tax_included = null; // type of prices
		$this->terms = null;
		$this->ex_rate = null;

		$this->reference = null;
		$this->tax_overrides = array();		// array of taxes manually inserted during sales invoice entry (direct invoice)
// 		$this->supplier_tax_id = null;
	}

	var $amount_total = 0;
	var $tax_total = 0;
	var $discount_total = 0;
	var $invoice_total = 0;
	function calculator_total(){
		if( !$this->taxes ){
			$this->taxes_load();
		}

		$this->amount_total = $this->tax_total = $this->discount_total = 0;

		foreach ($this->line_items as $itm){

			$tax = tax_calculator($itm->tax_id,$itm->price*$itm->quantity,$this->tax_included,$this->taxes[$itm->tax_id]);
			$this->amount_total+= $tax->price;
			$this->tax_total+= $tax->value;
			$this->invoice_total+= ( $tax->value + $tax->price );
// 			$total += $ln_itm->this_quantity_inv * $ln_itm->chg_price;
		}


// 		foreach ($this->gl_codes as $gl_line) {
// 			if (!is_tax_account($gl_line->gl_code) || $this->tax_included)
// 				$total += $gl_line->amount;
// 		}
		return $this->amount_total;

	}

	function load(){

	}

	function set_supplier($supplier_id, $supplier_name, $curr_code, $tax_group_id, $tax_included) {

		$this->supplier_id = $supplier_id;
		$this->supplier_name = $supplier_name;
		$this->curr_code = $curr_code;
		$this->tax_group_id = $tax_group_id;
		$this->tax_included = $tax_included;
		$this->ov_amount = 0;
// 		$this->tax_group_array = get_tax_group_items_as_array($tax_group_id);
	}

	function add_to_order($supplier_tax_id, $line_no, $stock_id, $qty, $item_descr, $price, $uom, $req_del_date, $qty_inv, $qty_recd,$grn_item_id=0){

		if ( isset($qty) && $qty != 0){
			$line = array(
				'line_no'=>$line_no,
				'tax_id'=>$supplier_tax_id,
				'stock_id'=>$stock_id,
				'quantity'=>$qty,
				'price'=>$price,
// 				'$uom'=>$uom,
				'req_del_date'=>$req_del_date,
				'qty_inv'=>$qty_inv,
				'qty_received'=>$qty_recd,

				'grn_item_id'=>$grn_item_id,
				'receive_qty'=>0,
				'standard_cost'=>0
			);
			$item_data = get_item($stock_id);
			if ($item_data){
				$line+=array(
					'descr_editable'=>$item_data['editable'],
					'item_description'=>$item_data['description'],
					'units'=>$item_data['units'],
					'descr_editable'=>$item_data['editable'],
				);
			}
			if($item_descr){
				$line['item_description'] = $item_descr;
			}
			if($line_no){
				$this->line_items[$line_no] = (object)$line;
			} else {
				$this->line_items[] = (object)$line;
			}


			$this->line_total++;
			$this->lines_on_order++;
			return 1;
		}
		return 0;
	}

	function invoice($trans_no=0){
		$this->trans_type = ST_SUPPINVOICE;
		$this->order_no = $trans_no;
		if( $this->order_no ){
			$this->po_model->read_po($this->order_no, $this);
		}
		$this->tran_date = $this->orig_order_date;

		$orderDate = new_doc_date();
		if (!is_date_in_fiscalyear($orderDate))
			$orderDate = end_fiscalyear();
		$this->due_date = $this->orig_order_date = $orderDate;

		$this->fixed_access = 0;
		if( is_array($this->line_items) && count($this->line_items) >0 ){
			foreach ($this->line_items AS $line){
// 			    bug($line);die;
				$grn = array(
						'line_no'=>$line->line_no,
						'tax_id'=>$line->tax_id,
						'id'=>$line->grn_item_id,
// 						'po_detail_item'=>$line->item_description,
						'item_code'=> $line->stock_id,
						'item_description'=>$line->item_description,
						'qty_recd'=>$line->quantity,
						'prev_quantity_inv'=>0,
						'this_quantity_inv'=>$line->receive_qty,
						'order_price'=>$line->price,
						'chg_price'=>$line->price,
						'std_cost_unit'=>true,
						'gl_code'=>get_standard_cost($line->stock_id),
// 						'tax_included'=>'',
				);
				if( $line->grn_item_id ){
					$this->grn_items[$line->grn_item_id] = (object)$grn;
				} else {
					$this->grn_items[] = (object)$grn;
				}

				$this->ov_amount += $line->receive_qty * $line->price;
			}
		}
		$this->taxes_load();
	}
}