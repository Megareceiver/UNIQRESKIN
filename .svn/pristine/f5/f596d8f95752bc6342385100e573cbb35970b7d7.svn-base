<?php
class Customer_Print_Model extends CI_Model {
    function __construct(){
        parent::__construct();

    }

    var $trans_type = array(ST_SALESINVOICE,ST_OPENING_CUSTOMER,ST_OPENING_CUSTOMER,ST_CUSTCREDIT);
    var $amount_select = NULL;
    private function statement_select($debtor_id=0){
        $trans_mount = 'trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount';
        $trans_mount .=' +(IF (trans.type='.ST_OPENING_CUSTOMER.' OR trans.type='.ST_OPENING.' , 0, -1)) * trans.alloc';
//         $trans_mount .=' +(IF (trans.type='.ST_CUSTCREDIT.', -1, 0)) * trans.alloc';
        $select = "SUM( (".$trans_mount.")*trans.rate*(IF (trans.type=".ST_CUSTCREDIT." OR trans.type=".ST_CUSTPAYMENT.",-1,1)) ) AS total";

        $this->db->select($select,false)->from('debtor_trans AS trans');
        $this->db->where("trans.trans_no NOT IN ( SELECT void.id FROM voided AS void WHERE void.type=trans.type)");


        if ( $debtor_id ){
            $this->db->where('trans.debtor_no',$debtor_id);
        }

        if( $this->trans_type && !empty($this->trans_type) ){
            $this->db->where_in('trans.type',$this->trans_type);
        }
    }
    function customer_open_balance($debtor_id=0,$end_date=NULL,$type_select=null){
        $this->statement_select($debtor_id);
        $this->db->where('trans.tran_date <',date2sql($end_date));
        $data = $this->db->get()->row();
        return ($data && isset($data->total) ) ? array((object)array('trans_type'=>ST_OPENING,'total'=>$data->total) ): array();
    }

    function customer_outstanding($where=array(),$all=false){
        $this->db->select('trans.trans_no,trans.tran_date, trans.reference, trans.type AS trans_type');

        $tran_total = 'trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount';
        $this->db->select("($tran_total ) AS total",false);

        $select_credit= '(SELECT SUM(credit.amt) FROM cust_allocations AS credit WHERE credit.trans_type_to=trans.type AND credit.trans_no_to=trans.trans_no AND credit.trans_type_from='.ST_CUSTCREDIT.')';
        $this->db->select("$select_credit AS credit_of_inv",false);

        $select_credit_for_invoice= '(SELECT SUM(credit.amt) FROM cust_allocations AS credit WHERE credit.trans_type_from=trans.type AND credit.trans_no_from=trans.trans_no)';
        $this->db->select("IF(trans.type=".ST_CUSTCREDIT.",$select_credit_for_invoice,0) AS credit_to_inv",false);

        $select_paid = '(SELECT SUM(paid.amt) FROM cust_allocations AS paid WHERE paid.trans_no_to=trans.trans_no AND paid.trans_type_to=trans.type AND paid.trans_type_from='.ST_CUSTPAYMENT.')';
        $this->db->select("$select_paid AS payment_of_inv",false);

        $select_paid_for_invoice= '(SELECT SUM(credit.amt) FROM cust_allocations AS credit WHERE credit.trans_type_from=trans.type AND credit.trans_no_from=trans.trans_no)';
        $this->db->select("IF(trans.type=".ST_CUSTPAYMENT.",$select_paid_for_invoice,0) AS payment_to_inv",false);

//         $this->db->select("($tran_total ) AS TotalAmount, IF ( trans.type=".ST_CUSTPAYMENT.",0,0) AS Allocated, trans.type",false);
//         $this->db->select("(CASE when (trans.type = ".ST_SALESINVOICE." OR trans.type = ".ST_OPENING_CUSTOMER." OR trans.type = ".ST_OPENING.") THEN 'IN' ELSE 'OR' END) as deb_type",false);


        if( $where ){
            $this->db->where($where);
        }

        if( $this->trans_type && !empty($this->trans_type) ){
//             $trans_type = $this->trans_type;
            $this->db->where_in('trans.type',$this->trans_type);
        }
        $this->db->where("trans.trans_no NOT IN ( SELECT void.id FROM voided AS void WHERE void.type=trans.type)");


//         $this->db->join('cust_allocations AS payment_inv',"payment_inv.trans_no_to=trans.trans_no AND payment_inv.trans_type_to=trans.type AND payment_inv.trans_type_from=".ST_CUSTPAYMENT,'LEFT');
//         $this->db->select("payment_inv.amt AS payment_inv",false);


        if ( !$all ) {

            /*
             * Not show invoice full allocate
             */

//             $this->db->where("IF(alloc_inv.amt IS NULL, 0, alloc_inv.amt) < ($tran_total)");

            /*
             * not show invoice full paid
             */
            $this->db->where("IF(trans.type=".ST_SALESINVOICE.",trans.alloc, 0) < ($tran_total)");
//             $this->db->where("IF(payment_inv.amt IS NULL, 0, payment_inv.amt) < ($tran_total)");
//             $this->db->where("$select_paid < ($tran_total)");

            /*
             * Not show Payment for invoice
            */
            $this->db->join('cust_allocations AS payment_to',"payment_to.trans_no_from=trans.trans_no AND payment_to.trans_type_from=".ST_CUSTPAYMENT,'LEFT');
            $this->db->where("payment_to.amt IS NULL");

            $this->db->where("IF(trans.type=".ST_CUSTCREDIT.",trans.alloc, 0) < ($tran_total)");

        }


        $data = $this->db->order_by('trans.tran_date ASC , trans.trans_no')->group_by('trans.trans_no')->get('debtor_trans AS trans')->result();
//         bug($data); bug($this->db->last_query() ); die;

        return $data;
    }



//     function payment_outstanding($customer_id,$date_from=null,$date_to=null,$all=false,$show_also_allocated=false){

//         $select = "tran.trans_date AS tran_date, 'OR' AS deb_type, tran.ref AS reference, tran.type AS trans_type, tran.amount AS TotalAmount";
//         if( !$show_also_allocated ){
//             $select = "tran.trans_date AS tran_date, 'OR' AS deb_type, tran.ref AS reference, tran.type AS trans_type, tran.amount - tran.alloc AS TotalAmount";
//         }
//         $this->db->select($select,false)->from('bank_trans AS tran');
//         if( is_date($date_from) ){
//             $this->db->where('tran.trans_date >=',date2sql($date_from));
//         }
//         if( is_date($date_to) ){
//             $this->db->where('tran.trans_date <=',date2sql($date_to));
//         }
//         $this->db->where('tran.type',ST_CUSTPAYMENT);
//         if ( $all != true ) {
//             $this->db->where("(SELECT count(alloc.id) FROM cust_allocations alloc WHERE alloc.trans_no_from = tran.trans_no AND alloc.trans_type_from=".ST_CUSTPAYMENT.") <",1);
//         }
//         $this->db->where("tran.trans_no NOT IN ( SELECT void.id FROM voided AS void WHERE void.type=tran.type)");



//         $this->db->where('tran.person_type_id',$customer_id);
//         $data = $this->db->group_by('tran.trans_no')->order_by('tran.trans_date','ASC')->get()->result();
//         return $data;
//     }

    function get_statement_detail($customer_id, $date=null, $show_all=false){

        $data = array();

        $data[0] = $this->get_statement_by_month($customer_id,$date,$show_all);

        for ($i=1; $i < 11; $i ++ ){
            $time_check = add_months($date,-$i);
            $data[$i] = $this->get_statement_by_month($customer_id,add_months($date,-$i),$show_all);
        }
        $over_11_months = add_months($date,-11);
        $data[11] = $this->get_statement_by_month($customer_id,$over_11_months,$show_all);

        return $data;

    }

    function get_statement_by_month($customer_id,$date=null,$show_all=false){

        if( !$date ) return 0;
        $this->statement_select($customer_id);

//         $this->db->where('trans.tran_date <=', date2sql($date));
        $this->db->where('MONTH(trans.tran_date)', date('m',strtotime($date)) );
        $this->db->where('YEAR(trans.tran_date)', date('Y',strtotime($date)) );
        $data = $this->db->get()->row();
//         bug($data);
//         die($this->db->last_query() );
//         $this->db->select('SUM(credit.alloc) AS total')->where_in('trans.type',ST_SALESINVOICE)->where(array('trans.debtor_no'=>$customer_id,'trans.tran_date <='=>date2sql($date)));
//         $this->db->join('cust_allocations alloc','alloc.trans_no_to = trans.trans_no AND alloc.trans_type_to=trans.type');
//         $this->db->join('debtor_trans credit','credit.trans_no = alloc.trans_no_from AND credit.type='.ST_CUSTCREDIT);
//         $payment_credit = $this->db->get('debtor_trans AS trans')->row();

        $total = ( $data && isset($data->total) )? $data->total : 0;
//         if( ( $payment_credit && isset($payment_credit->total) ) ){
//             $total += $payment_credit->total;
//         }
// die($total);
        return $total;
    }
}