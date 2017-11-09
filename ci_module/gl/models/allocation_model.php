<?php
class Gl_Allocation_Model extends CI_Model {
	function __construct(){
		parent::__construct();
// 		$this->void_model = module_model_load('tran','void');
	}

	function str_for_invoice($trans_no=NULL, $tran_type=NULL,$date_max = NULL,$talbe_alias = 'alloc_of_invoice',$allocate_table='cust_allocations'){
// 	    $tran_type = ST_SALESINVOICE;
	    $table_tran_from = $talbe_alias."_from";
	    $table_tran_void = $talbe_alias."_voided";
	    $db = clone $this->db;

	    $db->select("SUM($talbe_alias.amt) AS allo_sum")->from("$allocate_table AS $talbe_alias");
	    $db->where("$talbe_alias.trans_no_to = $trans_no AND $talbe_alias.trans_type_to = $tran_type");
// 	    $db->where("$talbe_alias.trans_no_to",$trans_no);

        if( $allocate_table=='cust_allocations' ) {
            $db->join("debtor_trans AS $table_tran_from","$table_tran_from.type = $talbe_alias.trans_type_from AND $table_tran_from.trans_no = $talbe_alias.trans_no_from");
            if( is_date($date_max) ){
                $db->where("$table_tran_from.tran_date <=",date2sql($date_max));
            }
            $db->where("$table_tran_from.trans_no NOT IN ( SELECT $table_tran_void.id FROM voided AS $table_tran_void WHERE $table_tran_void.type=$table_tran_from.type)");
        } elseif ( $allocate_table=='supp_allocations' ) {
            $db->join("supp_trans AS $table_tran_from","$table_tran_from.type = $talbe_alias.trans_type_from AND $table_tran_from.trans_no = $talbe_alias.trans_no_from");
            if( is_date($date_max) ){
                $db->where("$table_tran_from.tran_date <=",date2sql($date_max));
            }
            $db->where("$table_tran_from.trans_no NOT IN ( SELECT $table_tran_void.id FROM voided AS $table_tran_void WHERE $table_tran_void.type=$table_tran_from.type)");
        }



	    $sql = $db->query_compile();
	    return $sql;

	}

	function payment_items($trans_no=NULL, $tran_type=NULL,$date_max = NULL,$talbe_alias = 'alloc_of_invoice'){
	    $talbe_alias = 'allo';
	    $this->db->select("allo.trans_no_from AS tran_no, allo.trans_type_from AS type")->from("cust_allocations AS $talbe_alias");
	    $this->db->where("$talbe_alias.trans_no_to = $trans_no AND $talbe_alias.trans_type_to = $tran_type");
	    // 	    $db->where("$talbe_alias.trans_no_to",$trans_no);
	    $table_tran_from = $talbe_alias."_from";
	    $table_tran_void = $talbe_alias."_voided";

	    $this->db->join("debtor_trans AS $table_tran_from","$table_tran_from.type = $talbe_alias.trans_type_from AND $table_tran_from.trans_no = $talbe_alias.trans_no_from");
	    if( is_date($date_max) ){
	        $this->db->where("$table_tran_from.tran_date <=",date2sql($date_max));
	    }

	    $this->db->where("$table_tran_from.trans_no NOT IN ( SELECT $table_tran_void.id FROM voided AS $table_tran_void WHERE $table_tran_void.type=$table_tran_from.type)");
	    $result = $this->db->get();
	    if( is_object($result) ){
	        return $result->result();
	    } else {
	        bug( $this->db->last_query() ) ;
	    }

// 	    $sql = $db->query_compile();
// 	    return $sql;
	}

	function str_of_payment($trans_no=NULL, $tran_type=0,$date_max = NULL,$talbe_alias = 'alloc_for_paid'){


	    $table_tran_from = $talbe_alias."_from";
	    $table_tran_void = $talbe_alias."_voided";
	    $db = clone $this->db;

	    $db->select("SUM($talbe_alias.amt)")->from("cust_allocations AS $talbe_alias");
	    $db->where("$talbe_alias.trans_no_from = $trans_no AND $talbe_alias.trans_type_from = $tran_type");

	    $db->join("debtor_trans AS $table_tran_from","$table_tran_from.type = $talbe_alias.trans_type_to AND $table_tran_from.trans_no = $talbe_alias.trans_no_to");
	    $db->where("$table_tran_from.tran_date <=",date2sql($date_max));

	    $db->where("$table_tran_from.trans_no NOT IN ( SELECT $table_tran_void.id FROM voided AS $table_tran_void WHERE $table_tran_void.type=$table_tran_from.type)");
	    $sql = $db->query_compile();
	    return $sql;

	}



	function alloc_sum($trans_no=NULL, $tran_type=0,$date_max = NULL,$talbe_alias = 'alloc_',$invoice_type = array(ST_SALESINVOICE,ST_SUPPINVOICE)){
// 	    $trans_no = 577;
// 	    $tran_type = 10;
// 	    bug($this->str_for_invoice($trans_no,$tran_type, $date_max,$talbe_alias."for_invoice"));die;
	    $invoice_alloc_sum = "IFNULL((".$this->str_for_invoice($trans_no,$tran_type, $date_max,$talbe_alias."for_invoice")."),0)";
	    $payment_alloc_sum = "IFNULL((".$this->str_of_payment($trans_no,$tran_type, $date_max,$talbe_alias."of_paid")."),0)";
// bug($this->str_of_/payment($trans_no,$tran_type, $date_max,$talbe_alias."of_paid")); die;
	    $sql = "(IF( $tran_type IN (".implode(',', $invoice_type).") ,($invoice_alloc_sum),($payment_alloc_sum)))";
// 	    bug($sql);
	    return $sql;
	}

	function sum_str($trans_no=NULL, $tran_type=0){

	}
}