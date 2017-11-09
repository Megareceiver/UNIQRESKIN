<?php
class Sales_Debtor_Model extends CI_Model {
    function __construct(){
        parent::__construct();
        $this->void_model = module_model_load('tran','void');
        $this->allocation_model = module_model_load('allocation','gl');
    }

    function items($debtor_no=0){
        if( $debtor_no ){
            $this->db->where('debtor_no',$debtor_no);
        }
        $result = $this->db->select('debtor_no, name, curr_code')->order_by('name ASC')->get('debtors_master');
        if( !is_object($result) ){
           display_error( _("The customers could not be retrieved"));
            return false;
        } else
            return $result->result();
    }

    function get_details($debtor_no=NULL, $to=null, $show_allocation=true,$view_customer=true){
        if ($to == null)
            $todate = date("Y-m-d");
        else
            $todate = date2sql($to);

        $past1 = get_company_pref('past_due_days');
        $past2 = 2 * $past1;

        $tran_amount = '(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount)';
        $credit_or_debit = "IF(trans.type=".ST_CUSTCREDIT." OR trans.type=".ST_CUSTPAYMENT." OR trans.type= ".ST_BANKDEPOSIT.", -1, 1)";
        // removed - debtor_trans.alloc from all summations

//         $select_alloc_invoice   = '';
//         $select_alloc_pay       = 'SELECT COUNT(allo_to_incoice.amt) FROM cust_allocations AS allo_from_pay WHERE allo_from_pay.`trans_no_from` = `trans`.`trans_no` AND allo_to_incoice.trans_type_from = trans.type';
        $tran_alloc = ($show_allocation) ? 0 : 'trans.alloc';
        $tran_amount .= " - $tran_alloc";

        $value = "IFNULL(  $credit_or_debit * ( $tran_amount ) ,0)";

        $due = "IF (trans.type=".ST_SALESINVOICE.", trans.due_date, trans.tran_date)";

//         $this->db->select("Sum($value) AS balance",false);
//         $this->db->select("Sum($credit_or_debit *trans.ov_amount) AS amount",false);
//         $this->db->select("Sum($credit_or_debit *trans.ov_gst) AS gst",false);
//         $this->db->select("Sum($credit_or_debit *trans.ov_freight) AS freight",false);
//         $this->db->select("Sum($credit_or_debit *trans.ov_freight_tax) AS freight_tax",false);
//         $this->db->select("Sum($credit_or_debit *trans.ov_discount) AS discount",false);
//         $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)) AS due",false);
//         $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)) AS overdue1",false);
//         $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)) AS overdue2",false);
        $this->db->select("trans.type, trans.trans_no, trans.ov_amount,trans.ov_gst , trans.ov_freight , trans.ov_freight_tax , trans.ov_discount, trans.alloc");
        if (!$show_allocation){
//             $this->db->where("ABS($tran_amount) > ",FLOAT_COMP_DELTA);
        }

        if( !$view_customer ){
            $this->db->select('COUNT(trans.trans_no) AS number');
            $this->db->select('trans.type')->group_by('trans.type');
            $this->db->from('debtor_trans AS trans')->where('trans.tran_date <=',$todate);
            $this->db->where('trans.type <>',ST_CUSTDELIVERY);
//             $this->db->where('trans.type',ST_SALESINVOICE);
            $this->db->order_by('trans.type ASC');
            $this->db->join('debtors_master AS master',"master.debtor_no = trans.debtor_no",'left');

            $this->db->join('gl_trans AS gl',"trans.type=gl.type AND trans.trans_no=gl.type_no AND gl.account=1200",'left');
            $this->db->select("Sum(gl.amount) AS gl_total",false);

        } else {

            $this->db->from('debtors_master AS master');
            $this->db->join('debtor_trans AS trans',"trans.debtor_no = master.debtor_no AND trans.type <> ".ST_CUSTDELIVERY,'LEFT')->where('trans.tran_date <=',$todate);
//             $this->db->group_by('master.credit_limit, master.name');
        }


        if( $debtor_no ){
            $this->db->where('master.debtor_no',$debtor_no);
        }

        $this->db->join('payment_terms AS term','term.terms_indicator=master.payment_terms','LEFT');
        $this->db->select('term.terms');


        $this->db->join('credit_status AS credit','credit.id=master.credit_status','LEFT');
        $this->db->select('credit.dissallow_invoices, credit.reason_description');
        if( $view_customer ){
//             $this->db->group_by('term.terms, term.days_before_due, term.day_in_following_month');
//             $this->db->group_by('credit.dissallow_invoices, credit.reason_description');
        }



        $this->db->select('master.name, master.curr_code, master.credit_limit');
        $this->void_model->not_voided('trans.type','trans.trans_no');
        $result = $this->db->get();
//
        if( !is_object($result) ){
            //The customer details could not be retrieved
            return false;
        }
        bug($result->result());
        bug($this->db->last_query() ); die;
        return $result->result();

    }

    function get_debtors($debtor_no=0,$limit=0,$empty_tran=false){
        if( $debtor_no ){
            $this->db->where('deb.debtor_no',$debtor_no);
            $empty_tran = true;
        }
        if ( is_numeric($limit) && $limit > 0 ){
            $this->db->limit($limit);
        }
        if( $empty_tran ){
            $this->db->left_join('debtor_trans AS tran',array('tran.debtor_no'=>'deb.debtor_no'))->group_by('deb.debtor_no');
            $this->db->where("tran.trans_no is NOT NULL");
            $this->db->select('COUNT(tran.trans_no) AS tran_number');
        }


        $result = $this->db->order_by('deb.name ASC')->select('deb.*')->get('debtors_master AS deb');
        if( is_object($result) ){
            return $result->result();
        }
//         bug($this->db->last_query());
        return NULL;

    }

    function get_transactions($debtor_no, $from, $to,$total_only=false){
        $from = date2sql($from);
        $to = date2sql($to);

        $alloc = $this->allocation_model->alloc_sum('tran.trans_no','tran.type', $to);
//         $alloc = 'tran.alloc';
//         $this->db->where('tran.trans_no',472);
        $amount = "(tran.ov_amount + tran.ov_gst + tran.ov_freight + tran.ov_freight_tax + tran.ov_discount )";

        if( !$total_only ){
            $this->db->select('tran.*');
            $this->db->select("($alloc) AS allocated",false);

            $this->db->select("$amount AS total_amount",false);
            $this->db->select("((tran.type = ".ST_SALESINVOICE.") AND tran.due_date < '$to') AS overdue",false);
        } else {

            $balance_add = "(IF(tran.type=".ST_SALESINVOICE." OR tran.type=".ST_BANKPAYMENT." ,1,-1))";
            $this->db->select("SUM(($alloc)*$balance_add) AS allocated",false);


//             $balance_add = "(IF(debtor_trans.type=".ST_SALESINVOICE." OR debtor_trans.type=".ST_BANKPAYMENT.",1,-1))";
            $this->db->select("SUM($amount*$balance_add) AS total_amount",false);
//             $this->db->where("SUM($amount*$balance_add) AS total_amount",false);
            $this->db->group_by('tran.debtor_no');

        }


        $this->db->where(array('tran.tran_date >='=>$from,'tran.tran_date <='=>$to));
        if( $debtor_no ){
            $this->db->where('tran.debtor_no',$debtor_no);
        }
        $this->db->where('tran.type <>',ST_CUSTDELIVERY);
        $this->db->where('tran.ov_amount <>',0);

        $this->void_model->not_voided('tran.type','tran.trans_no');

        $result = $this->db->order_by('tran_date ASC')->get('debtor_trans AS tran');

        if( is_object($result) ){
            return $result->result();
        }
// bug($this->db->last_query());die('sql error');
//         return NULL;
//         return db_query($sql,"No transactions were returned");
    }

    function get_transactions_old($debtorno, $from, $to, $total_only=false){
        $from = date2sql($from);
        $to = date2sql($to);

//         $allocation_model = module_model_load('allocation','gl');
//         $invoice_alloc_sum = 'debtor_trans.alloc';
//         $invoice_alloc_sum = "(".$allocation_model->str_for_invoice('debtor_trans.trans_no', 'debtor_trans.type', $to).")";
//         $payment_alloc_sum = "(".$allocation_model->str_of_payment('debtor_trans.trans_no','debtor_trans.type', $to).")";
        $alloc = $this->allocation_model->alloc_sum('debtor_trans.trans_no','debtor_trans.type', $to);


//         $alloc_str = "IFNULL(IF(debtor_trans.type=".ST_SALESINVOICE.",($invoice_alloc_sum),debtor_trans.alloc),0)";

        $amount_str = '(debtor_trans.ov_amount + debtor_trans.ov_gst + debtor_trans.ov_freight + debtor_trans.ov_freight_tax + debtor_trans.ov_discount)';

        $select = "debtor_trans.*,"
            ."$amount_str AS TotalAmount, "
            ."($alloc) AS Allocated, "
            ."((debtor_trans.type = ".ST_SALESINVOICE.") AND debtor_trans.due_date < '$to') AS OverDue";

        if( $total_only ){
            $balance_add = "(IF(debtor_trans.type=".ST_SALESINVOICE." OR debtor_trans.type=".ST_BANKPAYMENT.",1,-1))";
            $select = "SUM($amount_str*$balance_add) AS TotalAmount,"
                    ."SUM(($alloc)*$balance_add) AS Allocated"
                ;
        }

        $sql = "SELECT $select FROM debtor_trans
    	WHERE debtor_trans.tran_date >= '$from'
        	AND debtor_trans.tran_date <= '$to'
        	AND debtor_trans.debtor_no = ".db_escape($debtorno)."
        	    AND debtor_trans.type <> ".ST_CUSTDELIVERY."
	    AND ov_amount != 0
	    AND debtor_trans.`trans_no` NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=debtor_trans.type )";
        if( $total_only ){
            $sql.= " GROUP BY debtor_trans.debtor_no ";
        }
        $sql.= " ORDER BY debtor_trans.tran_date";
        $result = db_query($sql,"No transactions were returned");
//         bug($sql);
        if( $total_only ){
            return db_fetch($result);
        } else {
            while ($myrow = db_fetch($result)){
                bug($myrow);
            }
        }

    }

    function opening_balance($detor_no=0,$date_begin=NULL) {
        $tran_amount = "tran.ov_amount + tran.ov_gst + tran.ov_freight + tran.ov_freight_tax";

        $this->db->select("SUM(IF(tran.type = ".ST_SALESINVOICE." OR tran.type = ".ST_BANKPAYMENT." OR tran.type = ".ST_OPENING_CUSTOMER.",($tran_amount),0)) AS charges",false);
        $this->db->select("SUM(IF(tran.type<> ".ST_SALESINVOICE." AND tran.type <> ".ST_BANKPAYMENT." AND tran.type <> ".ST_OPENING_CUSTOMER.",($tran_amount)  * -1,0)) AS credits",false);
        $this->db->select("SUM(tran.alloc) AS allocated");

        $this->db->select("SUM(IF(tran.type = ".ST_SALESINVOICE." OR tran.type = ".ST_BANKPAYMENT." OR tran.type = ".ST_OPENING_CUSTOMER.",($tran_amount - tran.alloc),( ($tran_amount))) ) AS outstanding",false);

        $this->db->where('tran.debtor_no',$detor_no);
        $this->db->where('tran.type <>',ST_CUSTDELIVERY);
        if( is_date($date_begin) ){
            $this->db->where('tran.tran_date <',date2sql($date_begin));
        }


        $query = $this->db->group_by('tran.debtor_no')->get('debtor_trans AS tran');


        if( is_object($query) ){

            return $query->row();
        }
        bug( $this->db->last_query() );
    }

}