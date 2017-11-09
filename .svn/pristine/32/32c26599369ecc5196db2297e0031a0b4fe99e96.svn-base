<?php
class Customer_trans_Model {
	function __construct(){
		global $ci;
		$this->trans = $ci->db;
	}



	function search($where=array(),$show_also_allocated=false,$where_in=array()){
		$select = "*, type AS trans_type , alloc AS Allocated,  (ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) AS TotalAmount";
		$select.= ", (CASE when (type = ".ST_SALESINVOICE." OR type = ".ST_BANKPAYMENT." OR type = ".ST_OPENING_CUSTOMER." OR type = ".ST_OPENING.") THEN 'IN' ELSE 'OR' END) as deb_type";

		$this->trans->select($select,false);
		if( $where ){
		    $this->trans->where($where);
		}

		if( $where_in ){
		    $this->trans->where_in('type',$where_in);
		}


		$this->trans->where('ABS(ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) >','1e-6',false);
		$data = $this->trans->order_by('tran_date','ASC')->get('debtor_trans')->result();
// 		bug($this->trans->last_query() ); die;
		return $data;

	}

	function get_statement_detail($customer_id, $date=null, $type_select=array()){

        $data = array();
        $data[0] = $this->get_statement_by_month($customer_id,$date);
        for ($i=1; $i < 11; $i ++ ){
            $time_check = add_months($date,-$i);
            $data[$i] = $this->get_statement_by_month($customer_id,add_months($date,-$i));
        }
        $over_11_months = add_months($date,-11);
        $data[11] = $this->get_statement_by_month($customer_id,$over_11_months,false);
		return $data;

	}

	function get_statement_by_month($customer_id,$date=null,$month_check=true){

	    if( !$date ) return 0;


//         $value = 'IFNULL( IF(trans.type='.ST_CUSTCREDIT.' OR trans.type='.ST_CUSTPAYMENT.' OR trans.type='.ST_BANKDEPOSIT.', -1, 1) '
//                 .'*'
//                 .' (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount - trans.alloc),0)';


// 	    $value = 'IF(trans.type='.ST_CUSTCREDIT.' OR trans.type='.ST_CUSTPAYMENT.' OR trans.type='.ST_BANKDEPOSIT.', -1, 1) '
// 	                    .'*'
// 	                    .' (trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount - trans.alloc)';

	    $value = '(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount - trans.alloc)';

	    $select = "Sum( $value ) AS amount";

		$this->trans->select($select,false);
// 		$this->trans->from('debtors_master AS deb');
$this->trans->from('debtor_trans AS trans');


		    if( $month_check ){
		        $date = new DateTime( $date );
		        $this->trans->where('MONTH(trans.tran_date)', intval($date->format('m')) );
		        $this->trans->where('YEAR(trans.tran_date)', intval($date->format('Y')) );
		    } else {

		        $this->trans->where('trans.tran_date >=',sql2date($date));
		    }

		$this->trans->where(array('trans.debtor_no'=>$customer_id,'trans.ov_amount !='=>0) );
		$this->trans->where_in('trans.type',array(ST_SALESINVOICE,ST_OPENING_CUSTOMER,ST_OPENING_CUSTOMER));

		#$this->trans->group_by('deb.name, deb.credit_limit, terms.terms, terms.days_before_due, terms.day_in_following_month, sta.dissallow_invoices, sta.reason_description');

		$data = $this->trans->get()->row();
// bug($this->trans->last_query()); die;

		return ( $data && isset($data->amount) )? $data->amount : 0;
	}

	function search_invoice($trans_type,$trans_id,$where=array()){
		$select = " trans.*, "
					."ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount AS Total,"
					."cust.name AS DebtorName, cust.address, "
					."cust.curr_code, cust.tax_id, com.memo_, term.terms AS payment_terms_name, sale_type.tax_included"
					.",trans.reference AS ref, cust.debtor_ref "
						;
		$from = 'debtor_trans AS trans, debtors_master AS cust';

		$where_default = array('trans.type'=>$trans_type,'trans.trans_no'=>$trans_id,'trans.debtor_no = cust.debtor_no'=>NULL);


		switch ($trans_type){
			case ST_CUSTPAYMENT:
			case ST_BANKDEPOSIT:
				$select .= ",bank_act, bank_accounts.bank_name,"
						."bank_accounts.bank_account_name, "
						."bank_accounts.account_type AS BankTransType, bank_accounts.bank_curr_code, "
						."bank_trans.amount as bank_amount";

				$from .=', bank_trans, bank_accounts';
				$where_default['bank_trans.trans_no'] = $trans_id;
				$where_default['bank_trans.type'] = $trans_type;
				$where_default['bank_trans.amount !='] = 0;
				$where_default['bank_accounts.id = bank_trans.bank_act']=NULL;

				break;
			case ST_SALESINVOICE:
			case ST_CUSTCREDIT:
			case ST_CUSTDELIVERY:
				$select .= ", shippers.shipper_name, sales_types.sales_type, sales_types.tax_included, "
						." branch.*, cust.discount, tax_groups.name AS tax_group_name, man.salesman_name, "
						." tax_groups.id AS tax_group_id ";
				$from .=', sales_types, cust_branch AS branch, tax_groups';

				$this->trans->join('salesman AS man','man.salesman_code=branch.salesman','left');
				$where_default['sales_types.id = trans.tpe']=NULL;
				$where_default['branch.branch_code = trans.branch_code']=NULL;
				$where_default['branch.tax_group_id = tax_groups.id']=NULL;
				break;
		}



		$this->trans->select($select,false);
		$this->trans->join('comments AS com', 'trans.type=com.type AND trans.trans_no=com.id', 'left');
		$this->trans->join('shippers', 'shippers.shipper_id=trans.ship_via', 'left');
		$this->trans->join('payment_terms AS term', 'term.terms_indicator=trans.payment_terms', 'left');
		$this->trans->join('sales_types AS sale_type', 'sale_type.id=trans.tpe', 'left');

		$this->trans->where($where_default);
		if( is_array($where) && !empty($where) ){ foreach ($where AS $wh=>$wh_val){
			$this->trans->where('trans.'.$wh,$wh_val);
		}}

		$data = $this->trans->get($from)->row();
		return $data;
	}

	function trans_date( $select='*', $from=null,$to=null){
		if( !$select ){ $select = '*'; }
		$this->trans->select($select);
		if( $from ){
			$this->trans->where('tran_date >=',$from);
		}
		if( $to ){
			$this->trans->where('tran_date <=',$to);
		}
		$this->trans->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');
		$this->trans->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');

		/* not get emtpy transaction */
		$this->trans->where('trans.ov_amount >',0);
		$this->trans->where("trans.type",ST_SALESINVOICE);

		$items = $this->trans->get('debtor_trans AS trans')->result();
		return $items;
	}

	function trans_detail($select='*',$where=null,$trans_type=ST_SALESINVOICE){
		if( !$select ){ $select = '*'; }

		//$select.=',tax.rate AS tax_rate, de.id AS item_id';
		$select.=',tax.rate AS tax_rate, de.id AS item_id, stock.units, de.quantity AS qty, de.unit_price AS price, de.tax_type_id';
		$select.=", IF ( de.description <> '', de.description, pro.description) AS description";
		$select.=", IF ( de.description <> '', '', pro.long_description) AS long_description";


		$this->trans->select($select,false);
		if( $where && is_array($where) ) { foreach ( $where AS $wh=>$val){
			$this->trans->where("de.".$wh,$val);
		}}
		/* not get emtpy transaction */
// 		$this->trans->where(array('de.unit_price >'=>0,'de.quantity <>'=>0));
		$this->trans->where(array('de.quantity <>'=>0));
		$this->trans->join('tax_types AS tax', 'tax.id= de.tax_type_id', 'left');
 		$this->trans->where("de.debtor_trans_type",$trans_type);
		$this->trans->join('stock_master AS stock', 'stock.stock_id= de.stock_id', 'left');

		$this->trans->join('stock_master AS pro', 'pro.stock_id= de.stock_id', 'left');

		$items = $this->trans->get('debtor_trans_details AS de')->result();
// 		bug($this->trans->last_query());die;

		return $items;

	}
	function trans_detail_bydate($from=null,$to=null,$where=null,$trans_where=null){
		$data = array();
		if( !is_array($where) ){
			$where = array();
		}
		$trans = $this->trans_date(null,$from,$to);

		if( $trans ){ foreach ($trans AS $tran){
// 			bug('get item for tran='.$tran->trans_no);

			$where['debtor_trans_no'] = $tran->trans_no;
// 			$where['debtor_trans_type'] = $tran->type;

			$details = $this->trans_detail(null,$where);
			if( $details && !empty($details)){ foreach ($details AS $detail){
// 				bug($where);
				$price = $detail->unit_price * $detail->quantity * (1-$detail->discount_percent);
				if( $tran->tax_included ){
					$tax = $detail->tax_rate/(100+$detail->tax_rate) * $price;
					$price -= $tax;
// 					$tax = $detail->tax_rate/(100+$detail->tax_rate)*$detail->unit_price*$detail->quantity*( 1- $detail->discount_percent);
				} else {
					$tax = ($detail->tax_rate/100) * $price;
				}


				$data[] = (object) array(
					'id'=>$detail->item_id,
					'item_code'=>$detail->stock_id,
					'item_name'=>$detail->description,
					'trans_no'=>$detail->debtor_trans_no,
					'reference'=>$tran->reference,
					'date'=>$tran->tran_date,
					'customername'=>$tran->name,
					'currence'=>$tran->curr_code,
					'curr_exc'=>$tran->rate,

					'tax_included'=> $tran->tax_included,
					'tax_code'=>$detail->gst_03_type,
					'tax_rate'=>$detail->rate,
					'tax_base'=>$tax,

					'price'=>$detail->unit_price,
					'price_base'=>$price,
					'type'=>'S',
					'order_type'=>$tran->type,
				);
			}}
		}}
		return $data;
	}

	function gst_grouping($from,$to,$tax_id=0){
	    $select = 'de.id, de.stock_id AS item_code, de.description AS item_name, de.debtor_trans_no AS trans_no, de.unit_price, de.discount_percent'
	        .',trans.reference, trans.tran_date, trans.type AS order_type, saletype.tax_included, trans.rate AS curr_rate'
            .', deb.name AS customername, deb.curr_code AS currence, deb.msic, deb.debtor_no'
            .", de.tax_type_id AS tax_id, trans.type AS order_type"
            .", de.quantity AS quantity"
           .", CASE de.debtor_trans_type WHEN ".ST_CUSTCREDIT." then 'CCN' ELSE 'S' END AS type, de.debtor_trans_type AS trans_type"
	    ;

	    $this->trans->select($select,false);

        $this->trans->join('debtor_trans AS trans', 'trans.trans_no = de.debtor_trans_no AND trans.type = de.debtor_trans_type', 'left');
	    $this->trans->where('trans.ov_amount >',0);
// 	    $this->trans->where_in("trans.type",array(ST_SALESINVOICE,ST_CUSTCREDIT));
	    if( $from ){
	        $this->trans->where('trans.tran_date >=',$from);
	    }
	    if( $to ){
	        $this->trans->where('trans.tran_date <=',$to);
	    }
	    $this->trans->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');

	    $this->trans->join('stock_master AS stock', 'stock.stock_id= de.stock_id', 'left');
	    $this->trans->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');

	    $this->trans->where(array('de.unit_price >'=>0,'de.quantity <>'=>0));
	    $this->trans->where_in("de.debtor_trans_type",array(ST_SALESINVOICE,ST_CUSTCREDIT));
	    if( $tax_id ){
	    	$this->trans->where('de.tax_type_id',$tax_id);
	    } else {
	    	$this->trans->where('de.tax_type_id IS NOT NULL');
	    	$this->trans->where('de.tax_type_id >',0);
	    }

		$this->trans->order_by('de.debtor_trans_no','ASC');
//         $this->trans->order_by('trans.tran_date','ASC');
	    $items = $this->trans->get('debtor_trans_details AS de')->result();

	    return $items;
	}

	function gst_grouping_from_trans_tax($from,$to,$tax_id=0){
	    $select = 'tax_trans.id AS id_current, de.id, de.stock_id AS item_code, tax_trans.tax_type_id, de.description AS item_name, de.debtor_trans_no AS trans_no, de.unit_price, de.discount_percent'
	        .',trans.reference, trans.tran_date, trans.type AS order_type, saletype.tax_included, trans.rate AS curr_rate'
            .', deb.name AS customername, deb.curr_code AS currence'
            .",trans.type AS order_type"
//             .",de.tax_type_id AS tax_id"
           .",tax_trans.tax_type_id , tax_trans.tax_type_id AS tax_id"
           .",de.quantity, de.quantity AS qty"
            .", CASE trans.type WHEN ".ST_CUSTCREDIT." then 'CCN' ELSE 'S' END AS type"
            .""
	    ;
	                    ;

        $this->trans->select($select, false);

        /* not get emtpy transaction */
        $this->trans->where(array('de.unit_price >'=>0,'de.quantity <>'=>0));
        // 	    $this->tran->join('tax_types AS tax', 'tax.id= inv.tax_type_id', 'left');
        $this->trans->join('debtor_trans AS trans', 'trans.trans_no= tax_trans.trans_no AND trans.type = tax_trans.trans_type', 'left');
        $this->trans->where('tax_trans.net_amount >',0);
        //$this->trans->where_in("tax_trans.trans_type",array(ST_SALESINVOICE,ST_CUSTCREDIT));
        $this->trans->join('debtor_trans_details AS de', 'de.debtor_trans_no = trans.trans_no AND de.debtor_trans_type = tax_trans.trans_type AND ( de.tax_type_id IS NULL OR de.tax_type_id =0 )', 'left');

		$this->trans->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');
        $this->trans->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');
        if( $from ){
            $this->trans->where('tax_trans.tran_date >=',$from);
        }
        if( $to ){
            $this->trans->where('tax_trans.tran_date <=',$to);
        }
        if( $tax_id ){
        	$this->trans->where('tax_trans.tax_type_id',$tax_id);
        } else {
        	$this->trans->where('tax_trans.tax_type_id IS NOT NULL');
        	$this->trans->where('tax_trans.tax_type_id >',0);
        }
        $this->trans->where_in("tax_trans.trans_type",array(ST_SALESINVOICE,ST_CUSTCREDIT));


        //$this->trans->join('debtors_master AS deb', 'deb.debtor_no= trans.supplier_id', 'left');

        $this->trans->order_by('tax_trans.trans_no','ASC');
        $this->trans->order_by('tax_trans.tran_date','ASC');
        $items = $this->trans->get('trans_tax_details AS tax_trans')->result();


        return $items;
	}

	function gst_grouping_baddebt($from=null,$to=null){

// 		$invoices = $this->trans->select()->where(array('step'=>2,'type'=>ST_SALESINVOICE))->get('bad_debts')->result();

		$select = 'de.id, de.stock_id AS item_code, de.description AS item_name, de.debtor_trans_no AS trans_no, de.unit_price, de.discount_percent'
		.',trans.reference, DATE(bad.tran_date) AS tran_date, bad.type AS order_type,'
		.' saletype.tax_included, trans.rate AS curr_rate'
		.', deb.name AS customername, deb.curr_code AS currence, deb.msic, deb.debtor_no'
		.", 25 AS tax_id"
		.", de.quantity AS quantity, de.debtor_trans_type AS trans_type"
		.", 'SBD'AS type"
		;

		$this->trans->select($select,false);

		$this->trans->join('debtor_trans AS trans', 'trans.trans_no = bad.type_no AND trans.type = bad.type', 'left');
		$this->trans->join('debtor_trans_details AS de', 'de.debtor_trans_no= trans.trans_no AND trans.type = bad.type', 'left');
		$this->trans->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');
		$this->trans->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');
		if( $from ){
			$this->trans->where('DATE(bad.tran_date) >=',$from);
		}
		if( $to ){
			$this->trans->where('DATE(bad.tran_date) <=',$to);
		}
		$this->trans->where('bad.type',ST_SALESINVOICE);
		$this->trans->where('bad.type_no NOT IN ( SELECT type_no FROM bad_debts WHERE step =2 AND type='.ST_SALESINVOICE.' ) ');
		$data = $this->trans->group_by('bad.type_no')->get('bad_debts AS bad')->result();

		return $data;
	}



	function items_view($from=null,$to=null,$where=null){
		$select = "de.stock_id AS item_code, de.description as item_name, de.debtor_trans_no AS trans_no, trans.reference ,trans.tran_date AS date, deb.name AS customername, deb.curr_code AS currence, 'S' AS type, IFNULL(TRUNCATE(exch.rate_buy,2),1) AS curr_exc";
		$select.=', (de.unit_price * de.quantity) AS total ';
// 		$select.=', (de.unit_price * tax.rate * de.quantity / 100) AS tax, tax.rate AS tax_rate';
		$select.=', tax.rate AS tax_rate, de.unit_price, tax.gst_03_type AS tax_code, saletype.tax_included';

		$this->trans->select($select,false)->from('debtor_trans_details AS de');
		$this->trans->join('debtor_trans AS trans', 'trans.trans_no=de.debtor_trans_no', 'left');
		$this->trans->join('tax_types AS tax', 'tax.id= de.tax_type_id', 'left');
// 		$this->trans->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');
// 		$this->trans->join('exchange_rates AS exch', 'exch.curr_code= deb.curr_code AND exch.date_ <=trans.due_date', 'left');
// 		$this->trans->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');

// 		$this->trans->join('tax_types as tax', 'tax.id = de.tax_type_id', 'left');
// 		$this->db->join('tax_types as tax', 'tax.id = tran.tax_type_id', 'left');
// 		if( $from ){
// 			$this->trans->where( 'trans.tran_date >=',date('Y-m-d',strtotime($from)) );
// 		}

// 		if( $to ){
// 			$this->trans->where( 'trans.tran_date <=',date('Y-m-d',strtotime($to)) );
// 		}

		if( $where ){
			$this->trans->where($where);
		}
		$this->trans->where('trans.ov_amount >',0);
		$this->trans->where('de.unit_price >',0);
		$this->trans->where('de.quantity >',0);



		$this->trans->where_in('trans.type',array(10,13));
// 		$this->trans->group_by('de.debtor_trans_no');
		$order = $this->trans->get()->result();
		return $order;

	}



	function update($data=array(),$type='',$trans_no=0){
		if( !$this->check_exists($type, $trans_no) ){
			$trans_no = get_next_trans_no($type);
		}
	}

	function check_exists($type, $trans_no){
		$exist = false;
		$this->trans->get('trans_no');
		$this->trans->where('type',$type)->where('trans_no',trans_no);
		$data = $this->trans->get('debtor_trans')->row();
		if($data && isset($data->trans_no) ){
			$exist = true;
		}
		return $exist;
	}

	function trans_no_new($type){
		$trans = $this->trans->select_max('trans_no')->where('type',$type)->get('debtor_trans')->row();
		$trans_no = $trans->trans_no + 1;
		return $trans_no;
	}

	/*
	 * update write_customer_trans() in cust_trans_db.inc
	 * 150513
	 * quannh
	 */
	function write_customer_trans($trans_type, $trans_no, $data){
// 	    bug('go to write_customer_trans');
		if( !isset($data['debtor_no']) )
			return false;


		if( isset($data['tran_date']) ){
			$data['tran_date'] = date2sql($data['tran_date']);
		} else {
			$data['tran_date'] = '0000-00-00';
		}


		if( isset($data['due_date']) ){
			$data['due_date'] = date2sql($data['due_date']);
		} else {
			$data['due_date'] = '0000-00-00';
		}

		if ( $trans_type == ST_BANKPAYMENT)
			$data['ov_amount'] = -abs($data['ov_amount']);

		if ( !isset($data['rate']) || $data['rate'] == 0) {
			$curr = get_customer_currency($data['debtor_no']);
			$data['rate'] = get_exchange_rate_from_home_currency($curr, $data['tran_date']);
		}


		if( !isset($data['ov_discount']) ) {
			$data['ov_discount'] = 0;
		}
		if( !isset($data['ov_gst']) ) {
			$data['ov_gst'] = 0;
		}
		if( !isset($data['ov_freight_tax']) ) {
			$data['ov_freight_tax'] = 0;
		}
// 		if( !isset($data['alloc']) ) {
// // 			$data['alloc'] = 0;
// 		}

		$new = 0;
		$this->trans->reset();
		if( !exists_customer_trans($trans_type, $trans_no) ){
			$trans_no = get_next_trans_no($trans_type);
			$data['trans_no'] = $trans_no;
			$data['type'] = $trans_type;
			$sql = $this->trans->insert('debtor_trans',$data,true );
		} else {
			$sql = $this->trans->update('debtor_trans',$data,array('trans_no'=>$trans_no,'type'=>$trans_type),1,true );
		}
// bug($sql);
		db_query($sql, "The debtor transaction record could not be inserted");
		add_audit_trail($trans_type, $trans_no, $data['tran_date'], $new ? '': _("Updated."));

		return $trans_no;
	}

	function update_customer_trans($trans_type, $trans_no, $data){

		if( isset($data['tran_date']) ){
			$data['tran_date'] = date2sql($data['tran_date']);
		}


		if( isset($data['due_date']) ){
			$data['due_date'] = date2sql($data['due_date']);
		}

		if ( array_key_exists('rate', $data) && $data['rate'] == 0 ) {
			$curr = get_customer_currency($data['debtor_no']);
			$data['rate'] = get_exchange_rate_from_home_currency($curr, $data['tran_date']);
		}

		$this->trans->reset();

		$sql = $this->trans->update('debtor_trans',$data,array('trans_no'=>$trans_no,'type'=>$trans_type),1,true );

		db_query($sql, "The debtor transaction record could not be inserted");

		return $trans_no;
	}


	function write_customer_trans_detail($trans_type, $trans_no=0,$data,$check_existed=true){

		$data['debtor_trans_type'] = $trans_type;

        if( !$data['discount_percent'] ) {
            $data['discount_percent'] = 0;
        }
		if ( $check_existed && $trans_no != 0 && $this->debtor_trans_detail_exist($trans_no,$trans_type) ){
		    return true;
// 		    $this->trans->reset();
// 			$sql = $this->trans->update('debtor_trans_details',$data, array('debtor_trans_no'=>$trans_no),1,true );

		} else {
		    $this->trans->reset();
		    $data['debtor_trans_no'] = $trans_no;
			$sql = $this->trans->insert('debtor_trans_details',$data,true );

		}

		db_query($sql, "The debtor transaction detail could not be written");
		return true;
	}

	private function debtor_trans_detail_exist($trans_no,$trans_type=0,$stock_id=0){
		$this->trans->reset();
		$this->trans->where('debtor_trans_type',$trans_type);
		$this->trans->where('debtor_trans_no',$trans_no);
// 		$this->trans->where('stock_id',$stock_id);
		$row = $this->trans->get('debtor_trans_details')->row();

		if( $row && isset($row->id) ){
			return true;
		}
		return false;

	}

	function update_customer_trans_detail($trans_type, $id=0,$data){
		$this->trans->reset();
		if( $data ){
			foreach ($data AS $field=>$val){
				$this->trans->set($field,$val,false);
			}
		}

		$sql = $this->trans->update('debtor_trans_details',null,array('id'=>$id),1,true );
// bug($sql);
		db_query($sql, "The parent document detail record could not be updated");
		return true;
	}

	function get_open_balance($debtorno, $to){
	    if($to)
	        $to = date2sql($to);

	    $select = "SUM(IF(t.type = ".ST_SALESINVOICE." OR t.type = ".ST_BANKPAYMENT.", (t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount), 0)) AS charges,
    	SUM(IF(t.type <> ".ST_SALESINVOICE." AND t.type <> ".ST_BANKPAYMENT.", (t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount) * -1, 0)) AS credits,
		SUM(t.alloc) AS Allocated,
		SUM(IF(t.type = ".ST_SALESINVOICE." OR t.type = ".ST_BANKPAYMENT.", (t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount - t.alloc), ((t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount) * -1 + t.alloc))) AS OutStanding";

	    $this->trans->select($select);
	    $this->trans->where(array('t.debtor_no'=>$debtorno,'t.type <>'=>ST_CUSTDELIVERY));

// 		FROM ".TB_PREF."debtor_trans t

		if($to) {
		    $this->trans->where('t.tran_date <',date2sql($to));
		}
		  $sql .= " GROUP BY debtor_no";

	    $result = db_query($sql,"No transactions were returned");
	    return db_fetch($result);
	}

	function remove_trans_detail($trans_type, $trans_no){
	    $this->trans->reset();
	    if ($trans_no != 0 ){
            $this->trans->delete('debtor_trans_details',array('debtor_trans_no'=>$trans_no,'debtor_trans_type'=>$trans_type));
// 			$this->trans->where(array('debtor_trans_no'=>$trans_no,'debtor_trans_type'=>$trans_type))->delete('debtor_trans_details');
		}

// 		db_query($sql, "The debtor transaction detail could not be written");
		return true;
	}

	/*
	 * use for print PDF
	 */
	function get_customer_tran($trans_type,$trans_id,$stat_time='',$end_time='',$reference=''){
	    $this->trans->select("trans.*,"
	        ."ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount AS Total,"
            ."cust.name AS DebtorName, cust.address, cust.curr_code, cust.tax_id, "
            ."com.memo_, term.terms AS payment_terms_name",false);




	   $this->trans->join('comments AS com', 'trans.type=com.type AND trans.trans_no=com.id', 'left');
	   $this->trans->join('shippers AS ship', 'ship.shipper_id=trans.ship_via', 'left');
	   $this->trans->join('debtors_master AS cust', 'cust.debtor_no=trans.debtor_no', 'left');
	   $this->trans->join('payment_terms AS term', 'term.terms_indicator=trans.payment_terms', 'left');

	   if ($trans_type == ST_CUSTPAYMENT || $trans_type == ST_BANKDEPOSIT) {
	       // it's a payment so also get the bank account
	       // Chaitanya : Added bank_act to support Customer Payment Edit
	       $this->trans->select("bank_act, bank.bank_name, bank.bank_account_name, bank.account_type AS BankTransType, bank.bank_curr_code, btran.amount as bank_amount");

	       // it's a payment so also get the bank account
	       $this->trans->join('bank_trans AS ', 'bank_trans.amount != 0', 'left');
	       $this->trans->where( array('bank_trans.trans_no'=>$trans_id,'bank_trans.type'=>$trans_type) );
	       $this->trans->join('bank_accounts AS ', 'bank_accounts.id=bank_trans.bank_act', 'left');
	   } else if ($trans_type == ST_SALESINVOICE || $trans_type == ST_CUSTCREDIT || $trans_type == ST_CUSTDELIVERY) {
	       // it's an invoice so also get the shipper and salestype
	       $this->trans->select("ship.shipper_name, saletype.sales_type, saletype.tax_included, "
	           ."branch.*, "
	           ."cust.discount, "
	           ."taxgroup.name AS tax_group_name, "
	           ."taxgroup.id AS tax_group_id ");

	       // it's an invoice so also get the shipper, salestypes
	       $this->trans->join('sales_types AS saletype', 'saletype.id = trans.tpe', 'left');
	       $this->trans->join('cust_branch AS branch', 'branch.branch_code = trans.branch_code', 'left');
	       $this->trans->join('tax_groups AS taxgroup', 'taxgroup.id=branch.tax_group_id', 'left');


	   }

	   $this->trans->where( array('trans.trans_no'=>$trans_id,'trans.type'=>$trans_type) );

	   $data = $this->trans->get('debtor_trans AS trans')->row();

	   return $data;
	}

	function get_customer_trans_details($trans_type, $trans_no){
	    if (!is_array($trans_no))
	        $trans_no = array( $trans_no );


	    $this->trans->select('debtran.*, debtran.unit_price AS price, pro.long_description, pro.units, pro.mb_flag');

	    if( $trans_type == ST_CUSTCREDIT ){
            $this->trans->select('debtran.quantity AS qty',false);
	    } else {
	        $this->trans->select('debtran.quantity AS qty');
	    }

	    $this->trans->join('stock_master AS pro', 'pro.stock_id=debtran.stock_id', 'left');
	    $this->trans->where('debtran.debtor_trans_type',$trans_type);
	    $this->trans->where_in('debtran.debtor_trans_no',$trans_no);

	    $this->trans->where('debtran.quantity <>',0);
	    $data = $this->trans->get('debtor_trans_details AS debtran')->result();

	    return $data;


	}

}