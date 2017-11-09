<?php
class gl_trans extends ci {
	function __construct(){
		$this->default_value();
	}

	private function default_value(){
		$this->tran_date = date('d-m-Y');
		$this->due_date = date('d-m-Y');

		$this->reference = null;

		$this->ov_amount = 0;
		$this->ov_gst = 0;
		$this->ov_discount = 0;
	}

	var $trans = array();
	var $value = array('type'=>null,'type_no'=>null,'tran_date'=>null,'person_type_id'=>0,'person_id'=>0);
	function set_value($field,$value=null){
		if( is_array($field) ){
			foreach ($field AS $fil=>$val){
				if( array_key_exists($fil,$this->value) ){
					$this->value[$fil] = $val;
				}
			}
		} else {
			if( array_key_exists($field,$this->value) ){
				$this->value[$field] = $value;
			}
		}

	}

	function add_trans($account=null,$amount=0,$dim=0,$dim2=0,$memo='',$rate=0){
		$this->trans[] = array(
			'type'=>$this->value['type'],
			'type_no'=>$this->value['type_no'],
			'tran_date'=>$this->value['tran_date'],
			'person_type_id'=>$this->value['person_type_id'],
			'person_id'=>$this->value['person_id'],
			'account'=>$account,
			'amount'=>$amount,
			'dimension_id'=>$dim,
			'dimension2_id'=>$dim2,
		    'memo_'=>$memo,
		    'rate'=>$rate
		);
	}

	function do_gl_trans(){
	    $gl_trans_model = load_module_model('trans',true,'gl');

        if( !empty($this->trans) ) foreach ($this->trans AS $tran){
            $gl_trans_model->add_gl_trans($tran);
        }
	}

	function balance($amount=0){
        return array(
            'type'=>$this->value['type'],
            'type_no'=>$this->value['type_no'],
            'tran_date'=>$this->value['tran_date'],
            'person_type_id'=>$this->value['person_type_id'],
            'person_id'=>$this->value['person_id'],
            'account'=>get_company_pref('exchange_diff_act'),
            'amount'=>$amount,
            'dimension_id'=>0,
            'dimension2_id'=>0,
        );
	}

	function insert_trans($openning_key=null){
		//$gl_trans_model = parent::model('gl_trans',true);
	    $gl_trans_model = module_model_load('trans','gl');
		if( $openning_key ){
		    $gl_trans_model->void($openning_key);
		}



		if( !empty($this->trans) ){
			$trans_item = $this->trans[0];
			$audit = array(
					'type'=>$trans_item['type'],
					'trans_no'=>$trans_item['type_no'],
					'gl_date'=>$trans_item['tran_date'],
					'fiscal_year'=>get_company_pref('f_year'),
// 					'gl_seq'=>0

			);
			$gl_trans_model->add_audit_trail($audit);

			foreach ($this->trans AS $trans){
				$trans['openning'] = $openning_key;
				$gl_trans_model->gl_trans_customer($trans);
			}
		}

	}

	/*
	 * create customer tranesion
	 */
	function write_customer_trans($type,$amount=0,$openning){
		$gl_trans_model = self::model('gl_trans',true);

		$data = array(
			'trans_no'=>$openning->trans_no,
			'type'=>$type,
			'debtor_no'=>$openning->customer,
			'branch_code'=>$openning->branch,
			'tran_date'=>$openning->tran_date,
			'due_date'=>$openning->tran_date,
			'reference'=>$openning->ref,
			'tpe'=>'1',
			'ov_amount'=>round2($amount, user_price_dec()),
			'rate'=>1,
		);
// bug( $data );
// die('befor insert debtor trans');
		$gl_trans_model->add_debtor_trans($data);


	}

	function customer($debtor_no=1,$branch=0){
		$customer_model = self::model('cutomer',true);
		$product_model = self::model('product',true);
		$config_model = self::model('config',true);

		$customer = $customer_model->get_row($debtor_no);

		$this->debtor_no = $customer->debtor_no;



		$this->ex_rate = 1;
		$this->ship_via = 1; // Default Shipping Company
		$this->dimension_id = 0;
		$this->dimension2_id = 0;
		$this->ov_freight = 0;
		$this->ov_freight_tax = 0;
		$this->sales_account = null;
		$this->ov_discount = 0;
		$this->branch = null;
		$this->receivables_account = null;


		if($branch ){
			$branch = $customer_model->get_branch_row($branch);

			if( isset($branch->sales_account) ){
				$this->sales_account = $branch->sales_account;
			}
			$this->receivables_account = $branch->receivables_account;



		}

		if( !$this->sales_account ){
			$this->sales_account = $config_model->get_sys_pref_val('default_sales_act');
		}


		if( !$this->receivables_account ){
			$this->receivables_account = $config_model->get_sys_pref_val('debtors_act');
		}
	}

	function supplier($debtor_no=1){
		$product_model = self::model('product',true);
		$config_model = self::model('config',true);
		$supplier_model = self::model('supplier',true);



	}

	function product($stock_id=0){
		$config_model = self::model('config',true);
		$this->inventory_account = $config_model->get_sys_pref_val('default_inventory_act');
		$this->amount = 0;
		foreach ($this->items as $itm) {
			$this->amount += round(($itm->quantity * $itm->unit_price * (1 - $itm->discount_percent)), user_price_dec());
		}
// 		$stock_gl_code = get_stock_gl_code($order_line->stock_id);
// 		bug($stock_gl_code);die('get supplier model');
	}

	function sale_items_total_dispatch(){
// 		bug($this->items);
		foreach ($this->items as $itm) {
			$this->ov_amount += round(($itm->quantity * $itm->unit_price * (1 - $itm->discount_percent)), user_price_dec());
		}
	}

	function tax_calculate(){
		$tax_model = self::model('tax',true);
		foreach ($this->items as $itm) {
			$tax =$tax_model->get_row($itm->tax_type_id);
			if( $tax ){
				$this->ov_gst+= round( $tax->rate* $itm->quantity * $itm->unit_price/100 , user_price_dec());
			}

		}
	}

	function discount_calculate(){
		foreach ($this->items as $itm) {

			if( intval($itm->discount_percent) > 0 ){
				$this->ov_discount+= round(  (1-$itm->discount_percent/100) * $itm->quantity * $itm->unit_price , user_price_dec());
			}

		}
	}
}