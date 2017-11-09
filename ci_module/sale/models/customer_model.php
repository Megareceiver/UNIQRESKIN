<?php
class Sale_Customer_Model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function get_trans($tran_no,$tran_type=0,$stat_time='',$end_time='',$reference=''){


	    $this->db->select('trans.*, ov_amount+ov_gst+ov_freight+ov_freight_tax+ov_discount AS Total',false)->from('debtor_trans AS trans');

        $this->db->join('comments AS com','trans.type=com.type AND trans.trans_no=com.id','left')->select('com.memo_');
        $this->db->join('shippers','shippers.shipper_id=trans.ship_via','left');
        $this->db->join('debtors_master AS cust','cust.debtor_no=trans.debtor_no','left');
        $this->db->select('cust.tax_id, cust.curr_code, cust.name AS DebtorName, cust.address');

	    if ($tran_type == ST_CUSTPAYMENT || $tran_type == ST_BANKDEPOSIT) {
	        // it's a payment so also get the bank account
	        $this->db->select('bank_trans.bank_act')->where(array('bank_trans.trans_no'=>$tran_no,'bank_trans.type'=>$tran_type,'bank_trans.amount !='=>0) )->from('bank_trans');
	        $this->db->join('bank_accounts AS bank','bank.id = bank_trans.bank_act','left');
	        $this->db->select('bank.bank_name, bank.bank_account_name, bank.account_type AS BankTransType, bank.bank_curr_code, bank_trans.amount as bank_amount',false);
	    }

	    if ($tran_type == ST_SALESINVOICE || $tran_type == ST_CUSTCREDIT || $tran_type == ST_CUSTDELIVERY) {
	        // it's an invoice so also get the shipper, salestypes
	        $this->db->join('sales_types','sales_types.id = trans.tpe')->select('sales_types.sales_type, sales_types.tax_included');
	        $this->db->join('cust_branch AS branch','branch.branch_code = trans.branch_code');
	        $this->db->join('tax_groups','tax_groups.id = branch.tax_group_id ')->select('tax_groups.name AS tax_group_name, tax_groups.id AS tax_group_id');

	        $this->db->select('shippers.shipper_name, branch.*, cust.discount');

	    }

	    $this->db->where(array('trans.trans_no'=>$tran_no,'trans.type'=>$tran_type) );

	    if( $stat_time ){
	        $this->db->where( 'trans.tran_date >=',$stat_time );
	    }
	    if( $end_time ){
	        $this->db->where( 'trans.tran_date <=',$end_time );
	    }

	    if( $reference ){
	        $this->db->where( 'trans.reference',$end_time );
	    }
	    $data = $this->db->get()->row();
// bug( $this->db->last_query() );
// // bug($data);
// die;
	    return $data;

	}
}