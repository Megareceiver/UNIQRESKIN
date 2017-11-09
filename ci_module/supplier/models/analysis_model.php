<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
class Supplier_Analysis_Model extends CI_Model {
    var $exchange_diff_act = NULL;
    function __construct(){
        parent::__construct();
        $this->allocation_model = module_model_load('allocation','gl');
        $this->exchange_diff_act = get_company_pref('exchange_diff_act');
    }

    function get_supplier_details($supplier_id, $to=null, $all=true, $home_currency=false)
    {

        if ($to == null)
            $todate = date("Y-m-d");
        else
            $todate = date2sql($to);

        $past1 = get_company_pref('past_due_days');
        $past2 = 2 * $past1;

        $tran_amount = "trans.ov_amount + trans.ov_gst + trans.ov_discount";

        $allocated_val = "IFNULL(IF(trans.type=".ST_SUPPAYMENT." OR trans.type = ".ST_SUPPCREDIT."
                            ,(SELECT SUM(a1.amt) FROM supp_allocations AS a1 WHERE a1.trans_no_from = trans.trans_no AND a1.trans_type_from = trans.type )
                            ,(SELECT SUM(a2.amt) FROM supp_allocations AS a2 WHERE a2.trans_no_to = trans.trans_no AND a2.trans_type_to = trans.type )
                            ),0)";
//         $allocated_val = "trans.alloc";
        $all = false;
        // removed - supp_trans.alloc from all summations
        if ($all)
            $value = "($tran_amount)";
        else {

            $value = "IF (trans.type=".ST_SUPPINVOICE." OR trans.type=".ST_BANKDEPOSIT." OR trans.type =".ST_OPENING_SUPPLIER.",
            ($tran_amount - $allocated_val),
            ($tran_amount + $allocated_val))";

//             $value = "($tran_amount)";
        }



        $due = "IF (trans.type=".ST_SUPPINVOICE." OR trans.type=".ST_SUPPCREDIT.",trans.due_date,trans.tran_date)";


        $this->db->from("suppliers AS supp")
                ->select('supp.supp_name, supp.curr_code, supp.tax_group_id')
        ;

        if( $home_currency != false ){
            $this->db->select("supp.credit_limit - Sum(IFNULL(IF(trans.type=".ST_SUPPCREDIT.", -1, 1) * ($tran_amount*trans.rate),0)) as cur_credit",false);
            $this->db->select("Sum(IFNULL($value ,0)*trans.rate) AS Balance",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)*trans.rate) AS Due",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)*trans.rate) AS Overdue1",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)*trans.rate) AS Overdue2",false);
        } else {
            $this->db->select("supp.credit_limit - Sum(IFNULL(IF(trans.type=".ST_SUPPCREDIT.", -1, 1) * ($tran_amount),0)) as cur_credit",false);
            $this->db->select("Sum(IFNULL($value ,0)) AS Balance",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0)) AS Due",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past1,$value,0)) AS Overdue1",false);
            $this->db->select("Sum(IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $past2,$value,0)) AS Overdue2",false);
        }


        $this->db->join('supp_trans AS trans',"supp.supplier_id = trans.supplier_id AND trans.tran_date <= '$todate'",'LEFT');
        $this->db->join('payment_terms term',"term.terms_indicator = supp.payment_terms",'LEFT')->select('term.terms');

        $this->db->where('supp.supplier_id',$supplier_id);

        if (!$all) {
//             $sql .= "AND ABS(trans.ov_amount + trans.ov_gst + trans.ov_discount) - trans.alloc > ".FLOAT_COMP_DELTA." ";

//             $this->db->where("(ABS($tran_amount) -$allocated_val) > ".FLOAT_COMP_DELTA);
        }

        $this->db->group_by("supp.supp_name, term.terms, term.days_before_due, term.day_in_following_month");


        $supp = $this->db->get();
        if( !is_object($supp) ){
            bug($this->db->last_query());die('query error');
            check_db_error("The customer details could not be retrieved", $this->db->last_query(), false);
        } else {
            return $supp->row_array();
        }

    }

    function analysis_invoices($supplier_id, $to, $all=true)
    {
        $todate = date2sql($to);
        $PastDueDays1 = get_company_pref('past_due_days');
        $PastDueDays2 = 2 * $PastDueDays1;

        //$all = false;
        // Revomed allocated from sql
        $tran_amount = "tran.ov_amount + tran.ov_gst + tran.ov_discount";
        $allocated_val = "IFNULL( IF(tran.type=".ST_SUPPAYMENT." OR tran.type = ".ST_SUPPCREDIT."
                            ,(SELECT SUM(a1.amt) FROM supp_allocations AS a1 WHERE a1.trans_no_from = tran.trans_no AND a1.trans_type_from = tran.type )
                            ,(SELECT SUM(a2.amt) FROM supp_allocations AS a2 WHERE a2.trans_no_to = tran.trans_no AND a2.trans_type_to = tran.type )
                            ) , 0)";

        if ($all)
            $value = "($tran_amount)";
        else
            $value = "IF (tran.type=".ST_SUPPINVOICE." OR tran.type=".ST_BANKDEPOSIT." OR tran.type=".ST_OPENING_SUPPLIER." ,
    	           ($tran_amount - $allocated_val), ($tran_amount + $allocated_val))";

        $charges = "SELECT COALESCE(SUM(gl_ch.amount),0) FROM gl_trans AS gl_ch WHERE gl_ch.type_no = tran.trans_no AND gl_ch.type = tran.type AND gl_ch.account=".get_company_pref('bank_charge_act');
        $value .= "+ IF(tran.type=".ST_BANKPAYMENT.",($charges),0)";

        $due = "IF (tran.type=".ST_SUPPINVOICE." OR tran.type=".ST_SUPPCREDIT.",tran.due_date,tran.tran_date)";

        $this->db->from("supp_trans AS tran, payment_terms AS term")
            ->select("tran.type, tran.reference, tran.tran_date")
        ;
        $this->db->select("$allocated_val AS allocated",false);
        $this->db->select("$value as Balance",false);

        $this->db->select("IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= 0,$value,0) AS Due",false);
        $this->db->select("IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $PastDueDays1,$value,0) AS Overdue1",false);
        $this->db->select("IF ((TO_DAYS('$todate') - TO_DAYS($due)) >= $PastDueDays2,$value,0) AS Overdue2",false);

        $this->db->join('suppliers AS supp','supp.payment_terms = term.terms_indicator AND supp.supplier_id = tran.supplier_id');

        $this->db->where('tran.supplier_id',$supplier_id);
        $this->db->where('tran.tran_date <=',$todate);
        $this->db->where("ABS($tran_amount) >",FLOAT_COMP_DELTA);
        if (!$all){
            $this->db->where("ABS($tran_amount) > 0");
            $this->db->where("(ABS($tran_amount) - $allocated_val) > ".FLOAT_COMP_DELTA);
        }

        $this->db->order_by('tran.tran_date');

        $trans = $this->db->get();
//         bug($trans->result());
//         bug($this->db->last_query());die;
        if( !is_object($trans) ){
            bug($this->db->last_query());die;
            check_db_error("The supplier details could not be retrieved", $this->db->last_query(), true);
        } else {
            return $trans->result_array();
        }


    }
}