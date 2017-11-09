<?php
class Bank_Model {
	function __construct(){
		global $ci;
// 		bug(parent);
		$this->bank = $ci->db;
	}

	function item_options(){
		$options = array();
		$data = $this->bank->select('id,bank_account_name,bank_curr_code')->order_by('bank_account_name', 'ASC')->get('bank_accounts')->result();

		$options[0] = " -- All Bank Accounts-- ";
		if( $data ){
			foreach ($data AS $row){
				$options[$row->id] = $row->bank_account_name.' - '.$row->bank_curr_code;
			}
		}
		return $options;
	}

	function items($where=null){
		if( empty($where) ){
			return false;
		}

		$select = 'bt.*,act.*';
		$select.=', IFNULL(debtor.curr_code, IFNULL(supplier.curr_code, act.bank_curr_code)) settle_curr ';
		$this->bank->select($select,false)->from('bank_trans AS bt');
		$this->bank->join('debtor_trans AS dt', 'dt.type=bt.type AND dt.trans_no=bt.trans_no', 'left');
		$this->bank->join('debtors_master AS debtor', 'debtor.debtor_no = dt.debtor_no', 'left');
		$this->bank->join('bank_accounts AS act', 'act.id=bt.bank_act', 'left');
		$this->bank->join('supp_trans AS st', 'st.type=bt.type AND st.trans_no=bt.trans_no', 'left');
		$this->bank->join('suppliers AS supplier', 'supplier.supplier_id = st.supplier_id', 'left');
		$this->bank->where($where);

		$data =$this->bank->group_by('trans_no')->get()->result();

// 		bug( $this->bank->last_query() );
		return $data;

	}

	function bank_accounts_default($currency=null,$default=0){
		$this->bank->select('account_code')->where('dflt_curr_act',$default);

		if( $currency ){
			$this->bank->where('bank_curr_code',$currency);
		}
		$data = $this->bank->get('bank_accounts')->row();

		$code = null;

		if( !isset($data->account_code) ){
			$code = self::bank_accounts_default(null);
		} else {
			$code =$data->account_code;
		}


		if( !$code ){
			$code = self::bank_accounts_default($currency,0);
		}

		if( !$code ){
			$code = self::bank_accounts_default(null,0);
		}
// bug($this->bank->last_query());
		return $code;
	}

	function item($where=null){
	    if( !$where ){
	        return null;
	    }
	    return $this->bank->where($where)->get('bank_accounts')->row();;

	}
}