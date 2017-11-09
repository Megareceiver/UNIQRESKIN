<?php
class Gl_bank_trans_Model {
	function __construct(){
		global $ci;
		$this->bank = $ci->db;
	}

	function get_bank_trans($type, $trans_no=null, $person_type_id=null, $person_id=null,$where=null){
        $select = 'bt.*, act.*,
		IFNULL(abs(dt.ov_amount), IFNULL(ABS(st.ov_amount), bt.amount)) settled_amount,
		IFNULL(abs(dt.ov_amount/bt.amount), IFNULL(ABS(st.ov_amount/bt.amount), 1)) settle_rate,
		IFNULL(debtor.curr_code, IFNULL(supplier.curr_code, act.bank_curr_code)) settle_curr ';

        $this->bank->select($select,false);

        $this->bank->join('debtor_trans AS dt',"dt.type=bt.type AND dt.trans_no = bt.trans_no",'left',false);
        $this->bank->join('debtors_master AS debtor','debtor.debtor_no = dt.debtor_no','left');
        $this->bank->join('supp_trans AS st','st.type=bt.type AND st.trans_no = bt.trans_no','left');
        $this->bank->join('suppliers AS supplier','supplier.supplier_id = st.supplier_id','left');
        $this->bank->join('bank_accounts AS act','act.id=bt.bank_act','left');
        if( $type ){
            $this->bank->where('bt.type',($type));
        }
        if( $trans_no ){
            $this->bank->where('bt.trans_no',($trans_no));
        }
        if( $person_type_id ){
            $this->bank->where('bt.person_type_id',($person_type_id));
        }
        if( $person_id ){
            $this->bank->where('bt.person_id',($person_id));
        }
        if( is_array($where) ){
            $this->bank->where($where);
        }
        $data = $this->bank->order_by('bt.trans_date, bt.id')->get('bank_trans AS bt')->result();
        return $data;
	}
}