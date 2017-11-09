<?php
class Supplier_trans_Model {
	function __construct(){
		global $ci;
		$this->tran = $ci->db;
	}


	function trans_date( $select='*', $from=null,$to=null){
		if( !$select ){ $select = '*'; }
		$this->tran->select($select);
		if( $from ){
			$this->tran->where('tran_date >=',$from);
		}
		if( $to ){
			$this->tran->where('tran_date <=',$to);
		}
		$this->tran->join('debtors_master AS deb', 'deb.debtor_no= trans.supplier_id', 'left');
// 		$this->tran->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');

		/* not get emtpy transaction */
		$this->tran->where('trans.ov_amount >',0);
		$this->tran->where("trans.type",ST_SUPPINVOICE);

		$items = $this->tran->group_by('trans.trans_no')->get('supp_trans AS trans')->result();
		// 		bug( $this->tran->last_query() );
		return $items;
	}

	function trans_detail($select='*',$where=null){
		if( !$select ){ $select = '*'; }

		$select.=',tax.rate AS tax_rate, inv.id AS item_id';

		$this->tran->select($select);
		if( $where ){
			$this->tran->where($where);
		}
		/* not get emtpy transaction */
		$this->tran->where(array('inv.unit_price >'=>0,'inv.quantity >'=>0));
		$this->tran->join('tax_types AS tax', 'tax.id= inv.tax_type_id', 'left');

		$items = $this->tran->get('supp_invoice_items AS inv')->result();
// 				bug( $this->tran->last_query() );
		return $items;

	}
	function trans_detail_bydate($from=null,$to=null,$where=null,$trans_where=null){
		$data = array();
		if( !is_array($where) ){
			$where = array();
		}
		$trans = $this->trans_date(null,$from,$to);

		if( $trans ){ foreach ($trans AS $tran){
			$where['supp_trans_no'] = $tran->trans_no;
			$details = $this->trans_detail(null,$where);
			if( $details && !empty($details)){ foreach ($details AS $detail){
// bug($detail);
				$price = $detail->unit_price * $detail->quantity;
				$tax = ($detail->tax_rate/100) * $price;

				$data[] = (object) array(
						'id'=>$detail->item_id,
						'item_code'=>$detail->stock_id,
						'item_name'=>$detail->description,
						'trans_no'=>$detail->supp_trans_no,
						'reference'=>$tran->reference,
						'date'=>$tran->tran_date,
						'customername'=>$tran->name,
						'currence'=>$tran->curr_code,
						'curr_exc'=>$tran->rate,

						'tax_included'=> 0,
						'tax_code'=>$detail->gst_03_type,
						'tax_rate'=>$detail->rate,
						'tax_base'=>$tax,

						'price'=>$detail->unit_price,
						'price_base'=>$price,
						'type'=>'P',
						'order_type'=>$tran->type,
				);

// 				bug($details); die('what is this');
// 				bug($data);
			}}
		}}
		return $data;
	}

	function gst_grouping($from,$to,$tax_id=0,$not_in_ids = array() ){

	    $select = 'inv.id, inv.supp_trans_no AS trans_no, inv.unit_price,  0 AS discount_percent'
	        .',trans.reference, trans.tran_date, 0 AS tax_included, trans.type AS order_type, trans.fixed_access'
            .', supp.supp_name AS supp_name, supp.curr_code AS currence'
            .", inv.tax_type_id AS tax_id"
            .", CASE  trans.type WHEN ".ST_SUPPCREDIT." then 'SCN' ELSE 'P' END AS type"
            .", IF(inv.quantity = 0, 1, inv.quantity) as quantity"
            .", IF (trans.rate <=0, 1, trans.rate ) AS curr_rate"
            .",inv.gl_code"
			.", IF (inv.gl_code IS NOT NULL AND inv.gl_code != 0 ,inv.gl_code, inv.stock_id ) AS item_code"
            .", IF (inv.gl_code IS NOT NULL AND inv.gl_code != 0, (SELECT account_name FROM chart_master WHERE account_code = inv.gl_code), (SELECT description FROM stock_master WHERE stock_id = inv.stock_id) ) AS item_name"
            ;

	    $this->tran->select($select, false);

	    /* not get emtpy transaction */
	    $this->tran->where('inv.unit_price !=',0);
	    $this->tran->join('supp_trans AS trans', 'trans.trans_no= inv.supp_trans_no AND trans.type=inv.supp_trans_type', 'left');
// 	    $this->tran->where('trans.ov_amount !=',0);
	    $this->tran->where_in("trans.type",array(ST_SUPPINVOICE,ST_SUPPCREDIT));

	    if( $from ){
	        $this->tran->where('trans.tran_date >=',$from);
	    }
	    if( $to ){
	        $this->tran->where('trans.tran_date <=',$to);
	    }

	    $this->tran->join('suppliers AS supp', 'supp.supplier_id= trans.supplier_id', 'left');
	    $this->tran->join('comments AS memo', 'memo.id=trans.trans_no AND memo.type=trans.type', 'left')->select('memo.memo_ AS comment');

		if( $tax_id && $tax_id > 0 ){
			$this->tran->where('inv.tax_type_id',$tax_id);
		} else if( $tax_id=='null'){
		    if( is_array($not_in_ids) && !empty($not_in_ids)){
		        $this->tran->where_not_in('inv.id',$not_in_ids);

		    }
		} else {
			$this->tran->where('inv.tax_type_id IS NOT NULL');
			$this->tran->where('inv.tax_type_id >',0);
		}
	    $items = $this->tran->get('supp_invoice_items AS inv')->result();
	    return $items;
	}

	function gst_grouping_from_trans_tax($from,$to,$tax_id=0){
	    $select = 'tax_trans.id AS item_id, inv.id, inv.stock_id AS item_code, inv.description AS item_name, inv.supp_trans_no AS trans_no, 0 AS discount_percent'
// 	        .', inv.unit_price'
            // .", tax_trans.net_amount AS unit_price"
	    	.",inv.unit_price"
	        .",trans.reference AS reference, tax_trans.tran_date, 0 AS tax_included, tax_trans.ex_rate AS curr_rate, tax_trans.trans_type AS order_type, trans.fixed_access"
            .', deb.name AS customername, deb.curr_code AS currence'
//             .",CONCAT('P') AS type, tax_trans.tax_type_id AS tax_id, inv.tax_type_id"
            .", tax_trans.tax_type_id AS tax_id, inv.tax_type_id"
            .", inv.quantity, inv.quantity AS qty"
            .", CASE  tax_trans.trans_type WHEN ".ST_SUPPCREDIT." then 'SCN' ELSE 'P' END AS type"
	    ;

	    $this->tran->select($select, false);

	    /* not get emtpy transaction */
	    $this->tran->where(array('inv.unit_price !='=>0,'inv.quantity !='=>0));
	    // 	    $this->tran->join('tax_types AS tax', 'tax.id= inv.tax_type_id', 'left');
	    $this->tran->join('supp_trans AS trans', 'trans.trans_no= tax_trans.trans_no', 'left');
	    $this->tran->where('tax_trans.net_amount !=',0);
	    $this->tran->where_in("tax_trans.trans_type",array(ST_SUPPINVOICE,ST_SUPPCREDIT));
// 	    $this->tran->where("tax_trans.trans_type",ST_SUPPINVOICE);

// 	    $this->tran->join('supp_invoice_items AS inv', 'trans.trans_no= inv.supp_trans_no AND ( inv.tax_type_id IS NULL OR  inv.tax_type_id = 0) AND inv.supp_trans_type = tax_trans.trans_type', 'left');
	    $this->tran->join('supp_invoice_items AS inv', 'trans.trans_no= inv.supp_trans_no AND ( inv.tax_type_id IS NULL OR  inv.tax_type_id = 0) AND inv.supp_trans_type = tax_trans.trans_type', 'left');
	    $this->tran->join('debtors_master AS deb', 'deb.debtor_no= trans.supplier_id', 'left');

	    if( $from ){
	        $this->tran->where('tax_trans.tran_date >=',$from);
	    }
	    if( $to ){
	        $this->tran->where('tax_trans.tran_date <=',$to);
	    }
	    if( $tax_id && $tax_id > 0 ){
	    	$this->tran->where('tax_trans.tax_type_id',$tax_id);
	    }else if( $tax_id=='null' ){

    	    $this->tran->where('( inv.tax_type_id  IS NULL OR inv.tax_type_id=0) AND tax_trans.tax_type_id > 0 ');
	    } else {
	    	$this->tran->where('inv.tax_type_id  IS NOT NULL');
	    	$this->tran->where('tax_trans.tax_type_id >',0);
	    }


	    $items = $this->tran->group_by('inv.id')->get('trans_tax_details AS tax_trans')->result();
// 	    if( $tax_id=='null' ){
// 	        bug( $this->tran->last_query() );die;
// 	    }

	    return $items;
	}


	function gst_grouping_baddebt($from=null,$to=null){
		 $select = 'inv.id, inv.stock_id AS item_code, inv.description AS item_name, inv.supp_trans_no AS trans_no, inv.unit_price,  0 AS discount_percent'
	        .',trans.reference, trans.tran_date, 0 AS tax_included, trans.type AS order_type, trans.fixed_access'
            .', supp.supp_name AS supp_name, supp.curr_code AS currence'
            .", inv.tax_type_id AS tax_id"
            .", 'PBD' AS type"
//             .", IF(inv.quantity = 0 && trans.type=".ST_SUPPCREDIT.", 1, inv.quantity) as quantity"
            .", IF(inv.quantity = 0, 1, inv.quantity) as quantity"
            .", IF (trans.rate <=0, 1, trans.rate ) AS curr_rate"
//             ." IF ( inv.grn_item_id<=0, 1<  )"
// 	        .',tax.rate AS tax_rate'
            ;


		$this->tran->select($select,false);

		$this->tran->join('supp_trans AS trans', 'trans.trans_no= bad.type_no AND trans.type=bad.type', 'left');
		$this->tran->join('supp_invoice_items AS inv', 'inv.supp_trans_no= trans.trans_no AND inv.supp_trans_type = bad.type', 'left');
// 		$this->tran->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');
		$this->tran->join('suppliers AS supp', 'supp.supplier_id= trans.supplier_id', 'left');
		if( $from ){
			$this->tran->where('DATE(bad.tran_date) >=',$from);
		}
		if( $to ){
			$this->tran->where('DATE(bad.tran_date) <=',$to);
		}
		$this->tran->where('bad.type',ST_SUPPINVOICE);
		$this->tran->where('bad.type_no NOT IN ( SELECT type_no FROM bad_debts WHERE step =2 AND type='.ST_SUPPINVOICE.' ) ');
		$data = $this->tran->group_by('bad.type_no')->get('bad_debts AS bad')->result();

		return $data;
	}

	function items_view($from=null,$to=null,$where=null){
		$select = "supp.stock_id AS item_code, supp.description AS item_name, supp.supp_trans_no AS trans_no, trans.reference ,trans.tran_date AS date, supplier.supp_name AS customername, supplier.curr_code AS currence, 'P' AS type, IFNULL(TRUNCATE(exch.rate_buy,2),1) AS curr_exc";
		$select.=', (supp.unit_price * supp.quantity) AS total ';
		$select.=', (supp.unit_price * tax.rate * supp.quantity / 100) AS tax, tax.rate AS tax_rate, tax.gst_03_type AS tax_code, trans.tax_included, tax.gst_03_type AS tax_code ';

		$this->tran->select($select,false)->from('supp_invoice_items AS supp');
		$this->tran->join('supp_trans AS trans', 'trans.trans_no=supp.supp_trans_no', 'left');
		$this->tran->join('tax_types AS tax', 'tax.id= supp.tax_type_id', 'left');
		$this->tran->join('suppliers AS supplier', 'supplier.supplier_id= trans.supplier_id', 'left');
		$this->tran->join('exchange_rates AS exch', 'exch.curr_code= supplier.curr_code AND exch.date_ <=trans.due_date', 'left');


		if( $from ){
			$this->tran->where('trans.tran_date >=',$from);
		}

		if( $to ){
			$this->tran->where('trans.tran_date <=',$to);
		}

		if( $where ){
			$this->tran->where($where);
		}

		$this->tran->where('trans.ov_amount >',0);
		$this->tran->where('supp.unit_price >',0);
		$this->tran->where('supp.quantity >',0);


// 		$this->tran->where_in('trans.type',array(10,13));
// 		$this->tran->group_by('supp.supp_trans_no');
		$order = $this->tran->get()->result();
// 		bug( $this->tran->last_query() );die;
// 		bug( $this->tran->last_query() );
		return $order;

	}

	function trans_no_new($type){
		$trans = $this->tran->select_max('trans_no')->where('type',$type)->get('supp_trans')->row();
		$trans_no = $trans->trans_no + 1;
		return $trans_no;
	}

	function get_tran($type, $trans_no){
		return $this->tran->where(array('type'=>$type,'trans_no'=>$trans_no))->get('supp_trans')->row();
	}
	function update_supp_trans($type, $trans_no,$data=null){
		if( !is_array($data) || count($data) < 1 || !$type ){
			return false;
		}

		$tran_existed = null;
		if( $trans_no ){
			$tran_existed = $this->get_tran($type, $trans_no);
		}

		$this->tran->reset();
		if( $tran_existed && isset($tran_existed->trans_no) ){
			$sql = $this->tran->update('supp_trans',$data,array('type'=>$type,'trans_no'=>$trans_no),1,true );

		} else {
			$data['type'] = $type;
			$data['trans_no'] = $trans_no;
			$sql = $this->tran->insert('supp_trans',$data,true );
		}

		db_query($sql, "Cannot insert a supplier transaction record");
        return $trans_no;


	}


	function get_transactions($supplier_id,$date_from=null,$date_to=null,$type=ST_SUPPINVOICE){
	    $date_from = date2sql($date_from);
	    $date_to = date2sql($date_to);

	    $select = 'tran.*, (tran.ov_amount+tran.ov_gst+tran.ov_discount) AS total_amount';
	    $select.= ", ( tran.type = ".ST_SUPPINVOICE." AND tran.due_date < '$date_to') AS OverDue";
	    $this->tran->select($select,false)->from('supp_trans AS tran');
	    //alloc AS allocated
	    $this->tran->select("IF(tran.type=".ST_SUPPINVOICE.",
	           (SELECT SUM(alloc.amt) FROM supp_allocations AS alloc WHERE alloc.trans_type_to = tran.type AND alloc.trans_no_to=tran.trans_no AND alloc.date_alloc <='$date_to' ),
	            tran.alloc ) AS allocated",false);
	    $this->tran->where('ov_amount !=',0);

	    $this->tran->where('supplier_id',$supplier_id);
	    if( $date_from ){
	        $this->tran->where('tran_date >=',$date_from);
	    }
	    if( $date_to ){
	        $this->tran->where('tran_date <=',$date_to);
// 	        $this->tran->where('due_date <',$date_to);
	    }
	    $data = $this->tran->order_by('tran_date')->get()->result();
// 	    bug( $this->tran->last_query() );die;
        return $data;
	}

	function get_open_balance($supplier_id,$date_to=null){
	    $select = ' SUM(  alloc ) AS allocated ';

	    $select .=', SUM( IF(type =  '.ST_SUPPINVOICE.' OR type = '.ST_BANKDEPOSIT.' , ( ov_amount + ov_gst + ov_discount), 0) ) AS debit';
	    $select .=', SUM( IF( type <> '.ST_SUPPINVOICE.' AND type <> '.ST_BANKDEPOSIT.', -(ov_amount + ov_gst + ov_discount), 0) ) AS credit';
	    $select .=', SUM( '
	      //  .' IF(type = '.ST_SUPPINVOICE.' OR type = '.ST_BANKDEPOSIT.', ( ov_amount + ov_gst + ov_discount - alloc), (ov_amount + ov_gst + ov_discount + alloc))'
	      .'CASE supp_trans.type'
            .' WHEN '.ST_SUPPINVOICE.' THEN ov_amount + ov_gst + ov_discount - alloc '
            .' WHEN '.ST_BANKDEPOSIT.' THEN ov_amount + ov_gst + ov_discount - alloc '
            .' WHEN '.ST_OPENING_SUPPLIER.' THEN -alloc - ov_amount - ov_gst - ov_discount '
            .' ELSE ov_amount + ov_gst + ov_discount + alloc '
          .'END'
        .') AS outstanding';



	    $data = $this->tran->select($select,false)->where(array('tran_date <'=>date2sql($date_to),'supplier_id'=>$supplier_id,'ov_amount !='=>0))->order_by('supplier_id')->get('supp_trans');
	    if( is_object($data) ){
	        return $data->row();
	    } else {
	        bug( $this->tran->last_query() );die;
	    }

	}
}