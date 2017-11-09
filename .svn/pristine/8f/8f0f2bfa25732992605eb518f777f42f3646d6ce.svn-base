<?php

class Purchases_Trans_Model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    function get_supp_trans($trans_no, $trans_type = -1, $returnArray = false)
    {
        $sql = "SELECT " . TB_PREF . "supp_trans.*, (" . TB_PREF . "supp_trans.ov_amount+" . TB_PREF . "supp_trans.ov_gst+" . TB_PREF . "supp_trans.ov_discount) AS Total,
		" . TB_PREF . "suppliers.supp_name AS supplier_name, " . TB_PREF . "suppliers.curr_code AS curr_code ";

        // if ($trans_type == ST_SUPPAYMENT || $trans_type == ST_BANKPAYMENT)
        // {
        // // it's a payment so also get the bank account
        // $sql .= ", ".TB_PREF."bank_accounts.bank_name, ".TB_PREF."bank_accounts.bank_account_name, ".TB_PREF."bank_accounts.bank_curr_code,
        // ".TB_PREF."bank_accounts.account_type AS BankTransType, ".TB_PREF."bank_trans.amount AS bank_amount,
        // ".TB_PREF."bank_trans.ref ";
        // }
        $this->db->select('tran.*');
        $this->db->select('(tran.ov_amount+tran.ov_gst+tran.ov_discount) AS Total', false);
        $this->db->from('supp_trans AS tran');
        $this->db->left_join('suppliers AS supp', 'supp.supplier_id = tran.supplier_id');
        $this->db->select('supp.supp_name AS supplier_name, supp.curr_code AS curr_code ');
        // $sql .= " FROM ".TB_PREF."supp_trans, ".TB_PREF."suppliers ";

        if ($trans_type == ST_SUPPAYMENT || $trans_type == ST_BANKPAYMENT) {
            // it's a payment so also get the bank account
            // $sql .= ", ".TB_PREF."bank_trans, ".TB_PREF."bank_accounts";
            $this->db->left_join('bank_trans AS bank', "bank.type= tran.type AND bank.trans_no= tran.trans_no");
            $this->db->select('bank.amount AS bank_amount, bank.ref');

            $this->db->left_join('bank_accounts AS bankacc', 'bankacc.id=bank.bank_act');
            $this->db->select('bankacc.bank_name, bankacc.bank_account_name, bankacc.bank_curr_code, bankacc.account_type AS BankTransType');
            // $sql .= " AND ".TB_PREF."bank_trans.trans_no =".db_escape($trans_no)."
            // AND ".TB_PREF."bank_trans.type=".db_escape($trans_type)."
            // AND ".TB_PREF."bank_accounts.id=".TB_PREF."bank_trans.bank_act ";
        }

        $this->db->where('tran.trans_no', $trans_no);

        // $sql .= " WHERE ".TB_PREF."supp_trans.trans_no=".db_escape($trans_no)."
        // AND ".TB_PREF."supp_trans.supplier_id=".TB_PREF."suppliers.supplier_id";

        if ($trans_type > 0) {
            // $sql .= " AND ".TB_PREF."supp_trans.type=".db_escape($trans_type);
            $this->db->where('tran.type', $trans_type);
        }
        $result = $this->db->get();

        $data = NULL;
        if (! is_object($result)) {
            display_db_error("Cannot retreive a supplier transaction", $this->db->last_query(), true);
        } elseif ($result->num_rows == 0) {
            // can't return nothing
            bug($this->db->last_query());
            die('null');
            display_db_error("no supplier trans found for given params", $this->db->last_query(), true);
        } elseif ($result->num_rows > 1) {
            // can't return multiple
            bug($this->db->last_query());
            die('duplicate');
            display_db_error("duplicate supplier transactions found for given params", $this->db->last_query(), true);
        } else {
            $data = $returnArray ? $result->row_array() : $result->row();
        }
        // bug($result);die('quannh');
        return $data;
    }

    function update_supp_trans($data)
    {
        if (! isset($data['type']))
            return false;

        $new = ($data['trans_no'] == 0);
        $date_ = $data['tran_date'];
        $data['tran_date'] = date2sql($date_);

        $data['due_date'] = ($data['due_date'] == "") ? '0000-00-00' : date2sql($data['due_date']);

        if ($new)
             $data['trans_no'] = get_next_trans_no($data['type']);

        if (floatval($data['rate']) == 0) {
            $curr = get_supplier_currency($data['supplier_id']);
            $data['rate'] = get_exchange_rate_from_home_currency($curr, $date_);
        }

        $sql = $this->db->insert('supp_trans', $data, true);

        db_query($sql, "Can not update supplier transaction");
        add_audit_trail($data['type'], $data['trans_no'], $date_);

        return $data['trans_no'];
    }
}