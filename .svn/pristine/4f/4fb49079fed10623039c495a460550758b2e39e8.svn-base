<?php
class Purch_order_Model {
	function __construct(){
		global $ci;
		$this->po = $ci->db;
	}

	function search($where=null){
		$select = 'o.*, supp.supp_name, local.location_name, local.phone AS local_phone, local.email AS local_email, term.terms AS payment_terms_name'
				.', supp.address, supp.contact, supp.tax_group_id, supp.supp_account_no, supp.tax_included, supp.gst_no AS tax_id, supp.curr_code, supp.payment_terms'

						;

		$this->po->select($select)->from('purch_orders AS o');
		$this->po->join('suppliers AS supp', 'supp.supplier_id= o.supplier_id', 'left');
		$this->po->join('locations AS local', 'local.loc_code = o.into_stock_location', 'left');
		$this->po->join('payment_terms AS term', 'term.terms_indicator=supp.payment_terms', 'left');
// 		bug($where);
		if( $where ){
			foreach ($where AS $field=>$val){
				if ( $this->po->field_exists($field, 'purch_orders')){
					$this->po->where("o.$field",$val);
				} else {
					$this->po->where("$field",$val);
				}
			}

		}
		$data = $this->po->get()->row();
// 		bug( $this->order->last_query() );die;
		if( $data ){
			$data->items = $this->order_details($data->order_no);
		}


		return $data;

	}

	function order_details($order_number=0){
		$this->po->select('o.*, pro.units, pro.long_description ,o.quantity_ordered AS qty, o.unit_price AS price');
		$this->po->join('stock_master AS pro', 'o.item_code= pro.stock_id', 'left');


		$this->po->where('o.order_no',$order_number);

		$items = $this->po->order_by('o.po_detail_item')->get('purch_order_details AS o')->result();
		return $items;
// 		bug( $this->order->last_query() ); die;
	}

	function read_po($order_no, &$order, $open_items=false){
		$result = $this->read_po_header($order_no, $order);

		if ($result)
			$this->read_po_items($order_no, $order, $open_items);
	}

	function read_po_header($order_no, &$cart) {
		$this->po->select('po.*,supp.supp_name, supp.tax_group_id, supp.curr_code, loc.location_name')->from('purch_orders AS po');
		$this->po->join('suppliers AS supp','supp.supplier_id=po.supplier_id','left');
		$this->po->join('locations AS loc','loc.loc_code=po.into_stock_location','left');
		$this->po->where('po.order_no',$order_no);

		$data = $this->po->get()->row();
		if ($data){
			$cart->order_no = $data->order_no;
			$cart->set_supplier($data->supplier_id, $data->supp_name, $data->curr_code,$data->tax_group_id, $data->tax_included);
			$cart->credit = get_current_supp_credit($cart->supplier_id);
			$cart->orig_order_date = sql2date($data->ord_date);
			$cart->comments = nl2br($data->comments);
			$cart->location = $data->into_stock_location;
			$cart->supp_ref = $data->requisition_no;
			$cart->reference = $data->reference;
			$cart->delivery_address = $data->delivery_address;
			return true;
		}
		return false;
	}

	function read_po_items($order_no, &$cart, $open_items=false){
		$this->po->select('poitem.*, sto.units');
		$this->po->join('stock_master AS sto','sto.stock_id=poitem.item_code');
		$this->po->where('poitem.order_no',$order_no);
		if( $open_items ){
			$this->po->where('poitem.quantity_ordered > poitem.quantity_received');
		}
		$items = $this->po->order_by('poitem.po_detail_item')->get('purch_order_details AS poitem')->result();
		if ($items && count($items) > 0){
			foreach ($items AS $row){

				$data = get_purchase_data($cart->supplier_id, $row->item_code);
				if ($data !== false){
					if ($data['supplier_description'] != "")
						$row->description = $data['supplier_description'];
				}

				$add = $cart->add_to_order($row->tax_type_id, $row->po_detail_item, $row->item_code, $row->quantity_ordered,$row->description,$row->unit_price,$row->units, sql2date($row->delivery_date),$row->qty_invoiced, $row->quantity_received,$row->po_detail_item);
// 				if ($add) {
// 							$newline = &$order->line_items[$order->lines_on_order-1];
// 							$newline->po_detail_rec = $row->po_detail_item;
// 							$newline->standard_cost = $row->std_cost_unit;
// 							/*Needed for receiving goods and GL interface */
// 							// set for later GRN edition
// 							//	   	    	$newline->receive_qty = $newline->quantity - $newline->qty_dispatched;
// 				}
			} /* line po from purchase order details */
		} //end of checks on returned data set
	}
}

