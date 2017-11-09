<?php
class Sale_order_Model {
	function __construct(){
		global $ci;
		$this->order = $ci->db;
	}

	function items_view($from=null,$to=null,$where=null){
		$this->order->select('so.*,ode.*, tax.f3_box, tax.rate, deb.name AS customername, deb.curr_code AS currence')->from('sales_orders AS so');
		$this->order->join('sales_order_details AS ode', 'ode.order_no=so.order_no', 'left');
		$this->order->join('tax_types AS tax', 'tax.id= ode.tax_type_id', 'left');
		$this->order->join('debtors_master AS deb', 'deb.debtor_no= so.debtor_no', 'left');

		if( $from ){
			$this->order->where('so.ord_date >=',$from);
		}

		if( $to ){
			$this->order->where('so.ord_date <=',$to);
		}

		if( $where ){
			$this->order->where($where);
		}
		$this->order->where_in('so.trans_type',array(10,13));
// 		$this->order->group_by('so.order_no');
		$order = $this->order->get()->result();
		return $order;

	}

	function get_order( $order_no, $trans_type,$where= null ){
		$select = "sorder.*, loc.location_name, term.terms AS terms_name, branch.salesman "
		  .", cust.name,  cust.curr_code,  cust.address, cust.discount, cust.tax_id"
		  .", stype.sales_type, stype.id AS sales_type_id, stype.tax_included, stype.factor "
		  		.", ship.shipper_name, tax_group.name AS tax_group_name , tax_group.id AS tax_group_id ";
		// 		$from = 'sales_orders AS sorder, debtors_master AS cust';

		$this->order->select($select,false)->from('sales_orders AS sorder');
		$this->order->join('shippers AS ship', 'ship.shipper_id = sorder.ship_via', 'left');
		$this->order->join('cust_branch AS branch', 'branch.branch_code = sorder.branch_code', 'left');
		$this->order->join('tax_groups AS tax_group', 'tax_group.id = branch.tax_group_id', 'left');
		$this->order->join('debtors_master AS cust', 'sorder.debtor_no = cust.debtor_no', 'left');
		$this->order->join('locations AS loc', 'loc.loc_code = sorder.from_stk_loc', 'left');
		$this->order->join('sales_types AS stype', 'stype.id = sorder.order_type', 'left');
		$this->order->join('payment_terms AS term', 'term.terms_indicator = sorder.payment_terms', 'left');
		// 		$this->order->join('cust_branch AS branch', 'branch.branch_code = sorder.branch_code', 'left');





		$this->order->where(array('sorder.trans_type'=>$trans_type,'sorder.order_no'=>$order_no));

		if( $where && !empty($where) ){
			$this->order->where($where);
		}

		$data = $this->order->get()->row();
// 				bug( $this->order->last_query() );
// 				bug($data);

		return $data;

	}

	function get_order_details($order_no, $trans_type,$amount_zero=true){
		$this->order->select("de.id, de.order_no, de.stk_code, de.unit_price, de.discount_percent, de.qty_sent as qty_done"
				.", de.description, de.quantity AS qty, de.unit_price AS price, de.tax_type_id"

				.", sto.units, sto.long_description, sto.mb_flag, sto.material_cost +  sto.labour_cost +  sto.overhead_cost AS standard_cost"
		);
		$this->order->join('stock_master AS sto','de.stk_code = sto.stock_id');
		if( $order_no ){
			$this->order->where('de.order_no',$order_no);
		}
		if( $trans_type ){
			$this->order->where('de.trans_type',$trans_type);
		}
		if( $amount_zero ){
			$this->order->where(array('de.quantity >'=>0,'de.unit_price >'=>0));
		}
		$data = $this->order->order_by('de.id')->get('sales_order_details AS de')->result();
                return $data;
	}

	function get_receipt($tran_no=0,$type=null,$where=null){

		$select = '*, term.terms AS terms_name';
		$select.=", ( tran.ov_amount + tran.ov_gst + tran.ov_freight + tran.ov_freight_tax) AS Total";
		$select.=", tran.ov_discount, deb.name AS DebtorName, deb.debtor_ref, deb.curr_code, deb.payment_terms, deb.tax_id AS tax_id, deb.address";

		$this->order->select($select);
		$this->order->join('debtors_master AS deb','deb.debtor_no = tran.debtor_no');
		$this->order->join('payment_terms AS term', 'term.terms_indicator = deb.payment_terms', 'left');
		if( $tran_no ){
			$this->order->where('tran.trans_no',$tran_no);
		}
		if( $type ){
			$this->order->where('tran.type',$type);
		}
		if( is_array($where) && !empty($where) ){
			$this->order->where($where);
		}
		$data = $this->order->get('debtor_trans AS tran')->row();
// 		bug( $this->order->last_query() );

		return $data;

	}

	function get_allocations_for_receipt($debtor_id, $type, $trans_no){

		$select = "trans.type AS trans_type, trans.trans_no, trans.reference,trans.tran_date as  tran_date, trans.alloc,trans.due_date as due_date,  trans.version, trans.debtor_no"
			.", (ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount) AS price"
			.", (ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount- alloc ) AS line_total"
			.", deb.name AS DebtorName,	deb.curr_code, deb.address,"
			.", amt, trans.reference, (ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount- alloc ) AS  left_alloc"
		;

// 		$select = 'trans.*';
		$this->order->select($select,false);
		$this->order->where('alloc.trans_no_from',$trans_no);
		$this->order->where('alloc.trans_type_from',$type);
		$this->order->where('trans.debtor_no',$debtor_id);
		$this->order->join('debtors_master AS deb','trans.debtor_no= deb.debtor_no');
		$this->order->join('cust_allocations as alloc','trans.trans_no=alloc.trans_no_to AND trans.type=alloc.trans_type_to');

		$data = $this->order->order_by('trans_no')->get('debtor_trans as trans')->result();
	      //  bug( $this->order->last_query() );die;

		return $data;
	}


        function get_allocations_for_receipt_CP($debtor_id, $type, $trans_no){

		$select = "trans.type, trans.trans_no, trans.reference, trans.tran_date, trans.alloc, trans.due_date,  trans.version, trans.debtor_no"
			.", (ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount) AS total"
			.", (ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount- alloc ) AS left_alloc"
			.", deb.name AS DebtorName,	deb.curr_code, deb.address,"
			.", amt, trans.reference"
		;

		$this->order->select($select);
                $this->order->join('debtors_master AS deb','trans.debtor_no= deb.debtor_no');
		$this->order->join('cust_allocations as alloc','trans.trans_no=alloc.trans_no_to AND trans.type=alloc.trans_type_to');
		$this->order->where('alloc.trans_no_from',$trans_no);
		$this->order->where('alloc.trans_type_from',$type);
		$this->order->where('trans.debtor_no',$debtor_id);


		$data = $this->order->order_by('trans_no')->get('debtor_trans as trans')->result();
	       // bug( $this->order->last_query() );die;

		return $data;
	}

	function get_field($order_no,$field='*'){
           $data = $this->order->where('order_no',$order_no)->get('sales_orders')->row();
            // bug( $this->order->last_query() );die;
           if( isset($data->$field) ){
               return $data->$field;
           }
           return $data;
	}

        // chieu 17/8


        function get_receipt_CP($type, $trans_no,$stat_time='',$end_time='',$reference='')
        {
                $select ='*,term.terms AS terms_name';
                $select.=",( tran.ov_amount + tran.ov_gst + tran.ov_freight + tran.ov_freight_tax) AS Total,";
                $select.='tran.ov_discount,';
                $select.='deb.name AS DebtorName, deb.debtor_ref, deb.curr_code, deb.payment_terms,';
                $select.='deb.tax_id AS gst_no, deb.address';

                $this->order->select($select);
                $this->order->join('debtors_master AS deb','deb.debtor_no = tran.debtor_no');
                $this->order->join('payment_terms AS term', 'term.terms_indicator = deb.payment_terms', 'left');
        		if( $type ){
        			$this->order->where('tran.type',$type);
        		}

                if( $trans_no ){
        			$this->order->where('tran.trans_no',$trans_no);
        		}

                if( $stat_time ){
                        $this->order->where('tran.tran_date >=',$stat_time);
                }

                if( $end_time ){
                        $this->order->where('tran.tran_date <=',$end_time);
                }

                if( $reference ){
                        $this->order->where('tran.reference',$reference);
                }
                $data = $this->order->get('debtor_trans AS tran')->row();

                // $query = $this->order->last_query();bug($query);die;
                // bug($data);die;
		return $data;


         }

}