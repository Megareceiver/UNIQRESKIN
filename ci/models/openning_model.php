<?php
class Openning_Model {
	function __construct(){
		global $ci;
		$this->op = $ci->db;
		$this->gl_account = $ci->model('gl_account',true);
	}


	function add_gl_system($data){
		self::check_openning_gl_table();
		$fields = array('id','account','amount','tran_date');
		foreach ($data AS $field=>$val){
			if( !in_array($field, $fields) ){
				unset($data[$field]);
			}
		}
		$data['tran_date'] = date('Y-m-d',strtotime($data['tran_date']));
		$this->op->insert('opening_gl_system',$data);

		return $this->op->insert_id();

	}

	function check_openning_gl_table(){

	}

	function check_gl_trans_openningfield(){
		if ( !$this->op->field_exists('openning', 'gl_trans') ){
			$this->op->query("ALTER TABLE `gl_trans` ADD `openning` int(11);");
		}
	}



	function gl_account_get(){
		$groups_limit = $this->gl_account->openning_group();
		$groups = array();
		if( $groups_limit && !empty($groups_limit) ){
			foreach ($groups_limit AS $gr){
				$groups[] = $gr->id;
			}
		}

		$this->op->select('chart.account_code, chart.account_name, type.name, chart.inactive, type.id');
		$this->op->from('chart_master chart');

		$this->op->join('chart_types type', 'chart.account_type=type.id', 'left');
		$this->op->join('bank_accounts acc', 'chart.account_code=acc.account_code', 'left');
		$this->op->where('chart.inactive',0);
		if( !empty($groups) ){
			$this->op->where_in('chart.account_type',$groups);
		}

		$data = $this->op->get()->result();
		$result = array();

		if( $data ) { foreach ($data AS $it){
			if( !isset($result[ $it->name ]) ){
				$result[$it->name] = array();
			}
			$debit = $credit = 0;

			$openning_gl_debit = $this->op->where(array('account'=>$it->account_code,'pay_type'=>'debit'))->get('opening_gl')->row();

			if( isset($openning_gl_debit->amount) ){
				$debit = $openning_gl_debit->amount;
			}

			$openning_gl_credit = $this->op->where(array('account'=>$it->account_code,'pay_type'=>'credit'))->get('opening_gl')->row();
			if( isset($openning_gl_credit->amount) ){
				$credit = $openning_gl_credit->amount;
			}

			$result[$it->name][ $it->account_code] = array('name'=>$it->account_code.' - '.$it->account_name,'debit'=>abs($debit),'credit'=>abs($credit) );
		}}
		return $result;
	}

	function update_gl_account($data,$pay_type='create'){
		if( !isset($data['account']) ){
			return false;
		}


		$openning_gl = $this->op->where(array('account'=>$data['account'],'pay_type'=>$pay_type))->get('opening_gl')->row();

		$data['amount'] = str_replace(',','',$data['amount']);
		$openning_add = $data;
		$openning_add['pay_type'] = $pay_type;

		if( $openning_gl && isset($openning_gl->id)  ){
// 			if( $openning_gl->amount != $data['amount'] || $openning_gl->tran_date != $data['tran_date']  ){
				$data['tran_date'] = date('Y-m-d',strtotime($data['tran_date']) );
				$this->op->insert('opening_cache',array('data'=>json_encode($openning_gl) ));
				$this->op->where(array('id'=>$openning_gl->id,'pay_type'=>$pay_type))->update('opening_gl',$openning_add);
				$gl_trans_id = $openning_gl->gl_tran_id;
				$openning_id = $openning_gl->id;
// 				$this->op->where('counter',$openning_gl->gl_tran_id)->update('gl_trans',$data);
// 			}
		} else {

			$openning_add = $data;
			$openning_add['pay_type'] = $pay_type;
			$this->op->insert('opening_gl',$openning_add);
			$openning_id = $this->op->insert_id();
			$gl_trans_id = null;
		}
		$data['type'] = ST_OPENING_GL;
		$data['type_no'] = 0;

		$gl_tran_exist = $this->op->where('counter',$gl_trans_id)->get('gl_trans')->row();
		$this->op->reset();

		if( $gl_trans_id && $gl_tran_exist && isset($gl_tran_exist->counter) ){
		    $this->op->where('counter',$openning_gl->gl_tran_id)->update('gl_trans',$data);

		} else {
		    $this->op->insert('gl_trans',$data);
		    $gl_trans_id = $this->op->insert_id();
		    $this->op->where(array('id'=>$openning_id,'pay_type'=>$pay_type))->update('opening_gl',array('gl_tran_id'=>$gl_trans_id));
		}

// 		if( $data['account'] ==1200 ){
// 		    bug($this->op->last_query() );
// 		    bug($data);die;
// 		}
	}


	/*
	 * openning product / inventory
	 */
	function product_get($code=null){
		return $this->op->where('code',$code)->get('opening_product')->row();
	}
}