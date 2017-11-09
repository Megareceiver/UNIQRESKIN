<?php
class Sales_Payment_Model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

    function void_customer_payment($type, $type_no){
        die('call me');
        begin_transaction();

        hook_db_prevoid($type, $type_no);
        void_bank_trans($type, $type_no, true);
        void_gl_trans($type, $type_no, true);
        void_cust_allocations($type, $type_no);
        void_customer_trans($type, $type_no);

        commit_transaction();
    }

    function get_bank_trans($type, $trans_no=null, $person_type_id=null, $person_id=null) {
        $this->db->from('bank_trans AS bt')->select('bt.*');
        $this->db->left_join('supp_trans AS st','st.type=bt.type AND st.trans_no=bt.trans_no');

        $this->db->left_join('debtor_trans AS dt','dt.type=bt.type AND dt.trans_no=bt.trans_no');
        $this->db->left_join('debtors_master debtor','debtor.debtor_no = dt.debtor_no');
        $this->db->left_join('bank_accounts AS act','act.id=bt.bank_act')->select('act.*');
        $this->db->left_join('suppliers AS supplier','supplier.supplier_id = st.supplier_id');

        $this->db->select("IFNULL(abs(dt.ov_amount), IFNULL(ABS(st.ov_amount), bt.amount)) settled_amount",false);
        $this->db->select("IFNULL(abs(dt.ov_amount/bt.amount), IFNULL(ABS(st.ov_amount/bt.amount), 1)) settle_rate",false);
        $this->db->select("IFNULL(debtor.curr_code, IFNULL(supplier.curr_code, act.bank_curr_code)) settle_curr",false);


        if ($type != null) {
            $this->db->where("bt.type",$type);
        }

        if ($trans_no != null){
            $this->db->where("bt.trans_no",$trans_no);
        }

        if ($person_type_id != null){
            $this->db->where("bt.person_type_id",$person_type_id);
        }

        if ($person_id != null){
            $this->db->where("bt.person_id",$person_id);
        }

        $data = $this->db->order_by("trans_date, bt.id")->get();
        if( !is_object($data) ){
            bug($this->db->last_query());
            display_error('query for bank transaction');
            return false;
        } else {
            return $data->result();
        }

    }

    /**
     *	Check account history to find transaction which would exceed authorized overdraft for given account.
     *	Returns null or transaction in conflict. Running balance is checked on daily basis only, to enable ID change after edition.
     *	$delta_amount - tested change in bank balance at $date.
     **/

    function check_bank_account_history($delta_amount, $bank_account, $date=null, $user=null)
    {
        if ($delta_amount >= 0 && isset($date))
            return null;	// amount increese is always safe

        $balance = $date ? get_bank_account_limit($bank_account, $date, $user) : 0;

        if (!isset($balance) && isset($date))
            return null;	// unlimited account

        if (floatcmp($balance, -$delta_amount) < 0)
            return array('amount' => $balance - $delta_amount, 'trans_date'=> $date);

        $balance += $delta_amount;

        $sql = "SELECT sum(amount) as amount, trans_date FROM ".TB_PREF."bank_trans WHERE bank_act=".db_escape($bank_account);
        if ($date)
        {
            $date = date2sql($date);
            $sql .= " AND trans_date > '$date'";
        }
        $sql .= " GROUP BY trans_date ORDER BY trans_date ASC";

        $history = db_query($sql, "cannot retrieve cash account history");

        while ($trans = db_fetch($history)) {
            $balance += $trans['amount'];
            if ($balance < 0)
                return $trans;
        }

        return null;
    }

    function check_void_bank_trans($type, $type_no){
    	$moves = $this->get_bank_trans($type, $type_no);
        if( count($moves) > 0 ) foreach ($moves AS $tran){
            if($tran->amount > 0) {
                $check = $this->check_bank_account_history(-$tran->amount, $tran->bank_act, sql2date($tran->trans_date));
                return $check==null;
            }
        }
        return true;
    	while ($trans = db_fetch($moves)) {
    	    /*
    	     * quannh update to remove opening bank
    	     */
    // 		if ($trans['amount'] > 0) { // skip transfer input part
    // 			return check_bank_account_history(-$trans['amount'], $trans['bank_act'], sql2date($trans['trans_date'])) == null;
    // 		}
        	    return check_bank_account_history(-$trans['amount'], $trans['bank_act'], sql2date($trans['trans_date'])) == null;
    	}
    	return true;
    }

}