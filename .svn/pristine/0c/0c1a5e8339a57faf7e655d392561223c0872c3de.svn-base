<?php
class Bank_trans_Model {
	function __construct(){
		global $ci;
		$this->trans = $ci->db;
	}

	function add_detail($data){
		if( is_array($data) && !empty($data) ){
			$this->trans->insert('bank_trans_detail',$data);
		}

	}

	function trans_date( $select='*', $from=null,$to=null){
		if( !$select ){ $select = '*'; }
		$this->trans->select($select);
		if( $from ){
			$this->trans->where('trans_date >=',$from);
		}
		if( $to ){
			$this->trans->where('trans_date <=',$to);
		}
// 		$this->trans->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');
// 		$this->trans->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');

		/* not get emtpy transaction */
		$this->trans->where('trans.amount <>',0);
		$this->trans->where_in("trans.type",array(ST_BANKPAYMENT,ST_BANKDEPOSIT));
		// 		$this->trans->group_by('trans.trans_no');

		$items = $this->trans->get('bank_trans AS trans')->result();

		return $items;
	}

	function trans_detail($select='*',$where=null){
		if( !$select ){ $select = '*'; }

// 		$select.=',tax.rate AS tax_rate, de.id AS item_id';

		$this->trans->select($select);
		if( $where ){
			$this->trans->where($where);
		}
		/* not get emtpy transaction */
		$this->trans->where(array('de.amount <>'=>0));
		$this->trans->join('tax_types AS tax', 'tax.id= de.tax', 'left');
// 		$this->trans->join('gl_trans AS gl', 'gl.type_no = de.trans_no AND gl.type=de.type', 'left');
// 		$this->trans->where("de.debtor_trans_type",ST_SALESINVOICE);

		$items = $this->trans->get('bank_trans_detail AS de')->result();
// 		bug( $this->trans->last_query() );

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

			$where['de.trans_no'] = $tran->trans_no;
// 			$where['de.type'] = ST_BANKPAYMENT;
			// 			$where['debtor_trans_type'] = $tran->type;

			$details = $this->trans_detail(null,$where);

			switch ($tran->person_type_id){
				case 2:
					$person = $this->trans->where('debtor_no',$tran->person_id)->get('debtors_master')->row();

					$customer = $person->name; break;
				case 3:
					$person = $this->trans->where('supplier_id',$tran->person_id)->get('suppliers')->row();
					$customer = $person->supp_name; break;
				case 4:
					$customer = 'Quick Entry'; break;
				default: $customer = 'Miscellaneous'; break;
			}



			if( $details && !empty($details)){ foreach ($details AS $detail){

				$tax = ($detail->rate/100) * $detail->amount;

// bug($detail); die;
				$price = abs($detail->amount);
				$price_base = abs($detail->amount);
				if( $tran->tax_inclusive ){
					$tax = $detail->rate/(100+$detail->rate) * $price;
					$price -= $tax;
				} else {
					$tax = ($detail->rate/100) * $price;
				}


				$data[] = (object) array(
						'id'=>$detail->id,
						'item_code'=>null,
						'item_name'=>null,
						'trans_no'=>$tran->trans_no,
						'reference'=>$tran->ref,
						'date'=>$tran->trans_date,
						'customername'=>$customer,
						'currence'=>$detail->currence,
						'curr_exc'=>$detail->currence_rate,

						'tax_included'=> $tran->tax_inclusive,
						'tax_code'=>$detail->gst_03_type,
						'tax_rate'=>$detail->rate,
						'tax_base'=>abs($tax),

						'price'=>$price,
						'price_base'=>$price,
						'type'=> ($detail->type==ST_BANKDEPOSIT) ? 'DS' : 'BP' ,
						'order_type'=>$detail->type,
				);
			}}
		}}


		return $data;
	}

	function gst_grouping($from,$to,$tax_id=0){
	    $select = 'de.id, NULL AS item_code, NULL AS item_name, de.trans_no AS trans_no, ABS(de.amount) AS unit_price, 1 AS quantity, 0 AS discount_percent'
	        .',trans.ref AS reference, trans.trans_date AS tran_date, trans.tax_inclusive AS tax_included, de.currence_rate AS curr_rate'
            .', de.currence AS currence , de.tax AS tax_id, trans.type AS order_type'
            .", CASE de.type WHEN '".ST_BANKDEPOSIT."' then 'DS' ELSE 'BP' END as type"

            .', trans.person_type_id'
            .", CASE trans.person_type_id "
                ." WHEN 2 then (select name from debtors_master where debtor_no=trans.person_id)"
                ." WHEN 3 then (select supp_name from suppliers where supplier_id=trans.person_id)"
                ." WHEN 4 then 'Quick Entry' "
                ." ELSE 'Miscellaneous' "
            ."END AS customername"
	    ;

	    $this->trans->select($select, false);

	    $this->trans->join('bank_trans AS trans', 'trans.trans_no= de.trans_no AND trans.type= de.type', 'left');
	    $this->trans->join('comments AS memo', 'memo.id=trans.trans_no AND memo.type=trans.type', 'left')->select('memo.memo_ AS comment');

	    if( $from ){
	        $this->trans->where('trans.trans_date >=',$from);
	    }
	    if( $to ){
	        $this->trans->where('trans.trans_date <=',$to);
	    }
	    $this->trans->where('trans.amount <>',0);
// 	    $this->trans->where("trans.type",ST_BANKPAYMENT);
	    $this->trans->where_in("trans.type",array(ST_BANKPAYMENT,ST_BANKDEPOSIT));


	    $this->trans->where(array('de.amount <>'=>0));
	    if( $tax_id ){
	    	$this->trans->where('de.tax',$tax_id);
	    } else {
	    	$this->trans->where('de.tax IS NOT NULL');
	    	$this->trans->where('de.tax >',0);
	    }

	    $this->trans->where('de.trans_no NOT IN (SELECT id FROM voided WHERE type=de.type )');

        $items = $this->trans->get('bank_trans_detail AS de')->result();
        if( $items ){
//             bug($items);
//             bug( $this->trans->last_query() );

        }
//         bug( $this->trans->last_query() );
//         bug( $items );die;
        return $items;
	}

	function get_bank_trans($type, $trans_no=null, $person_type_id=null, $person_id=null) {
	    $select = 'bt.*, act.*';

	    $select .=', IFNULL(abs(dt.ov_amount), IFNULL(ABS(st.ov_amount), bt.amount)) settled_amount';
	    $select .=',IFNULL(abs(dt.ov_amount/bt.amount), IFNULL(ABS(st.ov_amount/bt.amount), 1)) settle_rate';
	    $select .=',IFNULL(debtor.curr_code, IFNULL(supplier.curr_code, act.bank_curr_code)) settle_curr';


	    $this->trans->select($select,false)->from('bank_trans bt');
	    $this->trans->join('debtor_trans dt','dt.type=bt.type AND dt.trans_no=bt.trans_no','left');
	    $this->trans->join('debtors_master debtor','debtor.debtor_no = dt.debtor_no','left');
	    $this->trans->join('supp_trans st','st.type=bt.type AND st.trans_no=bt.trans_no','left');
	    $this->trans->join('suppliers supplier','supplier.supplier_id=st.supplier_id','left');
	    $this->trans->join('bank_accounts act ','act.id=bt.bank_act','left');




	    if ($type != null){
	        $this->trans->where('bt.type',($type));
	    }

	    if ($trans_no != null)
	        $this->trans->where('bt.trans_no',($trans_no));

	    if ($person_type_id != null)
	        $this->trans->where('bt.person_type_id',($person_type_id));

	    if ($person_id != null)
	        $this->trans->where('bt.person_id',($person_id));

	    $data = $this->trans->order_by('trans_date, bt.id')->get()->result();
// 	    bug($this->trans->last_query());
// 	   bug($data);die;
	       return $data;
	}
}