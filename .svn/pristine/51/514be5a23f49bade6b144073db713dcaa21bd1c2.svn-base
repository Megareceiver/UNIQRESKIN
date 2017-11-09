<?php

class Sales_Detail_Model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_trans($tran_no, $tran_type, $stat_time = '', $end_time = '', $reference = '')
    {
        global $go_debug;
        $this->db->from("debtor_trans AS trans")
            ->select("trans.*")
            ->select("(trans.ov_amount+trans.ov_gst+trans.ov_freight+trans.ov_freight_tax+trans.ov_discount) AS Total", false);
        
        $this->db->where("trans.trans_no", $tran_no);
        $this->db->where("trans.type", $tran_type);
        
        $this->db->join("comments AS com", "trans.type=com.type AND trans.trans_no=com.id", "LEFT")->select("com.memo_");
        
        $this->db->join("debtors_master AS cust", "trans.debtor_no=cust.debtor_no", "LEFT")->select("cust.name AS DebtorName, cust.address, cust.curr_code, cust.tax_id");
        
        switch ($tran_type) {
            case ST_CUSTPAYMENT:
            case ST_BANKDEPOSIT:
                $this->db->join("bank_trans AS b_tran", "b_tran.trans_no = trans.trans_no AND b_tran.type=trans.type", "LEFT")
                    ->where("b_tran.amount != 0")
                    ->select("b_tran.bank_act, b_tran.amount as bank_amount");
                
                $this->db->join("bank_accounts AS b_acc", "b_acc.id = b_tran.bank_act", "LEFT")
                    ->select("b_acc.bank_name, b_acc.bank_account_name, b_acc.bank_curr_code")
                    ->select("b_acc.account_type AS BankTransType");
                break;
            case ST_SALESINVOICE:
            case ST_CUSTCREDIT:
            case ST_CUSTDELIVERY:
                $this->db->select("cust.discount");
                
                $this->db->join("sales_types AS s_type", "s_type.id = trans.tpe", "LEFT")->select("s_type.sales_type, s_type.tax_included");
                
                $this->db->join("cust_branch AS branch", "branch.branch_code = trans.branch_code", "LEFT")->select("branch.*");
                
                $this->db->join("tax_groups AS tax", "tax.id = branch.tax_group_id", "LEFT")->select("tax.name AS tax_group_name, tax.id AS tax_group_id ");
                
                $this->db->join("shippers AS shipp", "shipp.shipper_id=trans.ship_via", "LEFT")->select("shipp.shipper_name");
                break;
        }
        
        if ($reference) {
            $this->db->where("trans.reference", reference);
        } else {
            if ($stat_time) {
                $this->db->where("trans.tran_date >=", $stat_time);
            }
            if ($end_time) {
                $this->db->where("trans.tran_date <=", $end_time);
            }
        }
        
        $query = $this->db->get();
        if (! is_object($query) or empty($query)) {
            check_db_error("Cannot retreive a debtor transaction", $this->db->last_query());
        } elseif ($query->num_rows() < 1) {
            check_db_error("no debtor trans found for given params", $this->db->last_query());
        } elseif ($query->num_rows() > 1) {
            return $query->row();
            // check_db_error("duplicate debtor transactions found for given params", $this->db->last_query());
        }
        return $query->row();
    }

    function sales_order_header($tran_no, $tran_type, $start_date = null, $end_date = null, $reference = 0, $show_error = true)
    {
        $this->db->from("sales_orders sorder")->select("sorder.*");
        $this->db->join("cust_branch branch","branch.branch_code = sorder.branch_code","LEFT");
        
        $this->db->join("shippers ship","ship.shipper_id = sorder.ship_via","LEFT")
                 ->select("ship.shipper_name");
        
        $this->db->join("debtors_master cust","sorder.debtor_no = cust.debtor_no","LEFT")
                 ->select("cust.name, cust.curr_code, cust.address, cust.discount, cust.tax_id");
        ;
        $this->db->join("sales_types stype","stype.id=sorder.order_type","LEFT")
                 ->select("stype.sales_type, stype.id AS sales_type_id, stype.tax_included, stype.factor");
        
        $this->db->join("tax_groups tax_group","branch.tax_group_id = tax_group.id","LEFT")
                 ->select("tax_group.name AS tax_group_name, tax_group.id AS tax_group_id");
        
        $this->db->join("locations loc","loc.loc_code = sorder.from_stk_loc","LEFT")
                 ->select("loc.location_name");
        
        $this->db->where("sorder.trans_type",$tran_type);
        $this->db->where("sorder.order_no",$tran_no);
        
        if (isset($reference) && $reference) {
            $this->db->where("sorder.reference",$reference);
        } else {
            if (isset($start_date) && $start_date) {
                $this->db->where("sorder.ord_date >=",date2sql($start_date));
            }
            
            if (isset($end_date) && $end_date) {
                $this->db->where("sorder.ord_date <=",date2sql($end_date));
            }
        }

        
        $query = $this->db->get();
        if (! is_object($query) or empty($query)) {
            check_db_error("order Retreival", $this->db->last_query());
        } elseif ($query->num_rows() < 1) {
            display_warning("You have missing or invalid sales document in database (type:tran_type, number:$tran_no).");
        } elseif ($query->num_rows() > 1) {
//             return $query->row();
            check_db_error("You have duplicate document in database: (type:$tran_type, number:$tran_no).", $this->db->last_query());
        }
        return $query->row();

    }
    
    function get_tran_details($tran_type,$tran_no, $return_query=false)
    {
        if (!is_array($tran_no)){
            $tran_no = array( 0=>$tran_no );
        }
        $this->db->from("debtor_trans_details AS d")->select("d.*, d.description As StockDescription");
        $this->db->select("d.unit_price + d.unit_tax AS FullUnitPrice",false);
        $this->db->where("d.debtor_trans_type",$tran_type);
        $this->db->where_in("d.debtor_trans_no",$tran_no);
        
        $this->db->join("stock_master AS s","s.stock_id=d.stock_id","LEFT");
        $this->db->select("s.long_description, s.units, s.mb_flag");
        
        $query = $this->db->order_by("d.id")->get();
        
        if (! is_object($query) or empty($query)) {
            check_db_error("The debtor transaction detail could not be queried", $this->db->last_query());
        }
        return $return_query ? $this->db->last_query() : $query->result();
    }
}