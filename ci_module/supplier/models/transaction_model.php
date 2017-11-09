<?php
class Supplier_Transaction_Model extends CI_Model {
    var $aaa='bbb';

    var $exchange_diff_act = NULL;
    function __construct(){
        parent::__construct();
        $this->allocation_model = module_model_load('allocation','gl');
        $this->exchange_diff_act = get_company_pref('exchange_diff_act');
    }

    function get_transactions($supplier_id,$date_from=null,$date_to=null,$type=ST_SUPPINVOICE){

        $date_from = date2sql($date_from);
        $date_to = date2sql($date_to);
        $dec = user_amount_dec();
        if( strlen($dec) < 1 ){
            $dec = 0;
        }

        $allocated_str = "IF( tran.type = ".ST_SUPPINVOICE.",
            (SELECT SUM(alloc.amt) FROM supp_allocations AS alloc WHERE alloc.trans_type_to = tran.type AND alloc.trans_no_to = tran.trans_no "
            ." AND alloc.date_alloc <='$date_to'  "
            ."),
            tran.alloc)";
        $allocated_str = 'tran.alloc';

        $this->db->select("tran.*")->from('supp_trans AS tran');

        $total_original_str = "( ROUND(tran.ov_amount, $dec)+ROUND(tran.ov_gst,$dec)+ROUND(tran.ov_discount,$dec) )*(-1)";
        $this->db->select("$total_original_str AS amount_original",false);

        $this->db->select("( tran.type = ".ST_SUPPINVOICE." AND tran.due_date < '$date_to') AS OverDue",false);
        $this->db->select("tran.rate AS x_rate",false);

        $total_str = "( ROUND(tran.ov_amount, $dec)+ ROUND(tran.ov_gst,$dec)+ ROUND(tran.ov_discount,$dec) ) * tran.rate";
        $this->db->select("IF( $total_str < 0,-$total_str,0 ) AS debit",false);
        $this->db->select("IF( $total_str > 0,$total_str,0 ) AS credit",false);
        $this->db->select("IF( $total_str > 0, $total_str - (IF($allocated_str IS NULL,0,$allocated_str)), (IF($allocated_str IS NULL,0,$allocated_str))-ABS($total_str) ) AS outstanding",false);

        //alloc AS allocated
        $this->db->select("IF($allocated_str IS NULL,0,$allocated_str) AS allocated",false);

        /*
         * Select Foreign Exchange Gain
         */

        $this->db->select("(SELECT SUM(feg.amount) FROM gl_trans AS feg WHERE feg.type = tran.type AND feg.type_no = tran.trans_no AND feg.account = $this->exchange_diff_act)*-1 AS exchange_diff",false);

        $this->db->where('ov_amount !=',0);

        if ( strlen($supplier_id) > 0 ){
            $this->db->where('supplier_id',$supplier_id);
        }

        if( $date_from ){
            $this->db->where('tran.tran_date >=',$date_from);
        }
        if( $date_to ){
            $this->db->where('tran.tran_date <=',$date_to);
        }
        
        $this->db->left_join('gl_trans AS gl','gl.type=tran.type AND gl.type_no=tran.trans_no AND gl.account=2100 AND gl.amount <> 0')->select('gl.amount AS gl_2100');
        $this->db->group_by('tran.type, tran.trans_no');
        $data = $this->db->order_by('tran.tran_date')->get();

        return ( is_object($data) ) ? $data->result() : $this->db->last_query();

    }

    function get_open_balance($supplier_id=null,$date_to=null){
        $total = "(ov_amount + ov_gst + ov_discount)*rate";
       // $total.= "*(IF( type =  ".ST_OPENING_SUPPLIER." , -1,1 ))";
        $this->db->select("SUM(alloc) AS allocated",false);

//         $this->db->select("SUM( IF(type =  ".ST_SUPPINVOICE." OR type = ".ST_BANKDEPOSIT." , $total, 0) ) AS debit",false);
//         $this->db->select("SUM( IF( type <> ".ST_SUPPINVOICE." AND type <> ".ST_BANKDEPOSIT.", -$total, 0) ) AS credit",false);
        $this->db->select("SUM( IF($total < 0, $total, 0) ) AS debit",false);
        $this->db->select("SUM( IF($total > 0, ABS($total), 0) ) AS credit",false);
//         $this->db->select("SUM( IF($total < 0, ABS($total), 0) ) AS credit",false);


//         $this->db->select('SUM( '
//             //  .' IF(type = '.ST_SUPPINVOICE.' OR type = '.ST_BANKDEPOSIT.', ( ov_amount + ov_gst + ov_discount - alloc), (ov_amount + ov_gst + ov_discount + alloc))'
//         .'CASE supp_trans.type'
//             .' WHEN '.ST_SUPPINVOICE.' THEN '.$total.' - alloc '
//             .' WHEN '.ST_BANKDEPOSIT.' THEN '.$total.' - alloc '
//             //.' WHEN '.ST_OPENING_SUPPLIER.' THEN -alloc - ov_amount - ov_gst - ov_discount '
//             .' ELSE '.$total.' + alloc '
//         .'END'
//         .') AS outstanding',false);

        $this->db->select("SUM( $total ) AS outstanding",false);
        $this->db->select("SUM( $total ) AS balance",false);
        $this->db->where(array('tran_date <'=>date2sql($date_to),'ov_amount !='=>0));

        if( $supplier_id ){
            $this->db->where('supplier_id',$supplier_id);
        }

        $data = $this->db->order_by('supplier_id')->get('supp_trans');

        if( is_object($data) ){
            return $data->row();
        } else {
            bug( $this->db->last_query() );die;
        }

    }
}