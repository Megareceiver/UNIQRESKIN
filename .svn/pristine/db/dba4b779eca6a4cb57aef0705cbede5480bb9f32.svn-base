<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
class Sales_Analysis_Model extends CI_Model {
    var $exchange_diff_act = NULL;
    function __construct(){
        parent::__construct();
        $this->allocation_model = module_model_load('allocation','gl');
        $this->exchange_diff_act = get_company_pref('exchange_diff_act');
    }

    function get_customer_details($debtor_no, $to=null, $all=true, $home_currency=false)
    {

        if ($to == null)
            $todate = date("Y-m-d");
        else
            $todate = date2sql($to);

        $past1 = get_company_pref('past_due_days');
        $past2 = 2 * $past1;

        $tran_amount = "trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount";

        $allocated_val = "IFNULL(IF(trans.type=".ST_CUSTPAYMENT." OR trans.type = ".ST_CUSTCREDIT."
                            ,(SELECT SUM(a1.amt) FROM supp_allocations AS a1 WHERE a1.trans_no_from = trans.trans_no AND a1.trans_type_from = trans.type )
                            ,(SELECT SUM(a2.amt) FROM supp_allocations AS a2 WHERE a2.trans_no_to = trans.trans_no AND a2.trans_type_to = trans.type )
                            ),0)";
//         $allocated_val = "trans.alloc";
        $all = false;
        // removed - supp_trans.alloc from all summations
        if ($all)
            $value = "($tran_amount)";
        else {

            $value = "IF (trans.type=".ST_SALESINVOICE."  OR trans.type =".ST_OPENING_CUSTOMER.",
            ($tran_amount - $allocated_val),
            ($tran_amount + $allocated_val))";
        }

        $value = "($value * (IF (trans.type=".ST_CUSTCREDIT." OR trans.type=".ST_CUSTPAYMENT." OR trans.type=".ST_BANKDEPOSIT.",-1,1) ))";

        $due = "IF (trans.type=".ST_SUPPINVOICE." ,trans.due_date,trans.tran_date)";
        $this->db->from("debtors_master AS deb")->select('deb.name, deb.curr_code');

        if( $home_currency != false ){
            $this->db->select("deb.credit_limit - Sum(IFNULL(IF(trans.type=".ST_CUSTCREDIT.", -1, 1) * ($tran_amount*trans.rate),0)) as cur_credit",false);
            $this->db->select("Sum(IFNULL($value ,0)*trans.rate) AS Balance",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)*trans.rate) AS Due",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)*trans.rate) AS Overdue1",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)*trans.rate) AS Overdue2",false);
        } else {
            $this->db->select("deb.credit_limit - Sum(IFNULL(IF(trans.type=".ST_CUSTCREDIT.", -1, 1) * ($tran_amount),0)) as cur_credit",false);
            $this->db->select("Sum(IFNULL($value ,0)) AS Balance",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)) AS Due",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)) AS Overdue1",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)) AS Overdue2",false);
        }


        $this->db->join('debtor_trans AS trans',"deb.debtor_no = trans.debtor_no AND trans.tran_date <= '$todate' AND trans.type <> ".ST_CUSTDELIVERY."",'LEFT');
        $this->db->join('payment_terms term',"term.terms_indicator = deb.payment_terms",'LEFT')->select('term.terms');
        $this->db->join('credit_status credit',"credit.id = deb.credit_status",'LEFT')->select('credit.dissallow_invoices, credit.reason_description');

        $this->db->where('deb.debtor_no',$debtor_no);
        $this->db->where('trans.type <>',ST_BANKPAYMENT);
//         $this->db->where(" trans.trans_no NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=trans.type )");

        if (!$all) {
//             $sql .= "AND ABS(trans.ov_amount + trans.ov_gst + trans.ov_discount) - trans.alloc > ".FLOAT_COMP_DELTA." ";

//             $this->db->where("(ABS($tran_amount) -$allocated_val) > ".FLOAT_COMP_DELTA);
        }

        $this->db->group_by("deb.name,
			  term.terms,
			  term.days_before_due,
			  term.day_in_following_month,
			  deb.credit_limit,
			  credit.dissallow_invoices,
			  credit.reason_description");


        $supp = $this->db->get();
        if( !is_object($supp) ){
            bug($this->db->last_query());die('query error');
            check_db_error("The customer details could not be retrieved", $this->db->last_query(), false);
        } else {
            return $supp->row_array();
        }

    }

    function analysis_invoices($debtor_no, $to, $all=true)
    {
        $todate = date2sql($to);
        $PastDueDays1 = get_company_pref('past_due_days');
        $PastDueDays2 = 2 * $PastDueDays1;

        $all = false;
        // Revomed allocated from sql
        $tran_amount = "tran.ov_amount + tran.ov_gst + tran.ov_freight + tran.ov_freight_tax + tran.ov_discount";
        $allocated_val = "IFNULL( IF(tran.type=".ST_CUSTPAYMENT." OR tran.type = ".ST_CUSTCREDIT."
                            ,(SELECT SUM(a1.amt) FROM supp_allocations AS a1 WHERE a1.trans_no_from = tran.trans_no AND a1.trans_type_from = tran.type )
                            ,(SELECT SUM(a2.amt) FROM supp_allocations AS a2 WHERE a2.trans_no_to = tran.trans_no AND a2.trans_type_to = tran.type )
                            ) , 0)";

        if ($all)
            $value = "($tran_amount)";
        else
            $value = "IF (tran.type=".ST_SALESINVOICE." OR tran.type=".ST_OPENING_CUSTOMER." ,
    	           ($tran_amount - $allocated_val), ($tran_amount + $allocated_val))";

        $due = "IF (tran.type=".ST_SALESINVOICE." ,tran.due_date,tran.tran_date)";

        $this->db->from("debtor_trans AS tran")
        ->select("tran.type, tran.reference, tran.tran_date, tran.rate")
        ;
        $this->db->select("$allocated_val AS allocated",false);

        $value = "($value * (IF (tran.type=".ST_CUSTCREDIT." OR tran.type=".ST_CUSTPAYMENT." OR tran.type=".ST_BANKDEPOSIT.",-1,1) ))";

        $this->db->select("$value as Balance",false);
        $this->db->select("IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0) AS Due",false);
        $this->db->select("IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $PastDueDays1,$value,0) AS Overdue1",false);
        $this->db->select("IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $PastDueDays2,$value,0) AS Overdue2",false);

        $this->db->join('debtors_master AS deb','deb.debtor_no = tran.debtor_no');
        $this->db->where('tran.debtor_no',$debtor_no);
        $this->db->where('tran.tran_date <=',$todate);
        $this->db->where("ABS($tran_amount) >",FLOAT_COMP_DELTA);

        $this->db->where_not_in("tran.type",array(ST_CUSTDELIVERY,ST_BANKPAYMENT));

        if (!$all){
            $this->db->where("ABS($tran_amount) > 0");
            $this->db->where("(ABS($tran_amount) -$allocated_val) > ".FLOAT_COMP_DELTA);
        }

        $this->db->order_by('tran.tran_date');

        $trans = $this->db->get();
//         bug($trans->result());
//         bug($this->db->last_query());die;
        if( !is_object($trans) ){
            bug($this->db->last_query());die;
            check_db_error("The customer details could not be retrieved", $this->db->last_query(), true);
        } else {
            return $trans->result_array();
        }


    }
}