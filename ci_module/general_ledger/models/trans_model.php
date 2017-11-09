<?php

class GeneralLedger_Trans_Model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        // $this->void_model = module_model_load('tran','void');
    }

    function get_gl_balance_from_to($from_date, $to_date, $account, $dimension = 0, $dimension2 = 0)
    {
        $this->db->select("SUM(amount) AS sum")->from("gl_trans");
        $this->db->where("account", $account);

        if ($from_date != "")
            $this->db->where("tran_date >", date2sql($from_date));
        if ($to_date != "")
            $this->db->where("tran_date <", date2sql($to_date));
        if ($dimension != 0)
            $this->db->where("dimension_id", ($dimension < 0 ? 0 : db_escape($dimension)));
        if ($dimension2 != 0)
            $this->db->where("dimension2_id", ($dimension2 < 0 ? 0 : db_escape($dimension2)));

        $result = $this->db->get();
        if (! is_object($result)) {
            check_db_error(_("The starting balance for account $account could not be calculated"), $this->db->last_query(), $exit = false);
        } else {
            return $result->row()->sum;
        }
    }

    function get_gl_transactions($from_date, $to_date, $trans_no = 0, $account = null, $dimension = 0, $dimension2 = 0, $filter_type = null, $amount_min = null, $amount_max = null)
    {
        global $show_voided_gl_trans;

        $from = date2sql($from_date);
        $to = date2sql($to_date);

        $this->db->select("gl.*")->from('gl_trans AS gl');
        $this->db->left_join("chart_master AS c","c.account_code = gl.account")->select('c.account_name');

        $this->db->left_join("voided AS v","v.id = gl.type_no AND v.type = gl.type")->select('c.account_name');
        $this->db->where("ISNULL(v.date_)");

        if( is_date($from_date) ){
            $this->db->where("gl.tran_date >=",date2sql($from_date));
        }
        if( is_date($to_date) ){
            $this->db->where("gl.tran_date <=",date2sql($to_date));
        }

        if (isset($show_voided_gl_trans) && $show_voided_gl_trans == 0) {
            $this->db->where("gl.amount <> 0");
        }

        $trans_no = 12;
        if ($trans_no > 0) {
            $this->db->like("gl.type_no",$trans_no);
        }

        if ($account != null) {
            $this->db->where("gl.account",$account);
        }

        if ($dimension != 0) {
            $this->db->where("gl.dimension_id", $dimension < 0 ? 0 : $dimension);
        }
        if ($dimension2 != 0) {
            $this->db->where("gl.dimension2_id", $dimension2 < 0 ? 0 : $dimension2);
        }

        if ($filter_type != null and is_numeric($filter_type)) {
            $this->db->where("gl.type", $filter_type);
        }

        if ($amount_min != null) {
            $this->db->where("ABS(gl.amount) >=", abs($amount_min));
        }

        if ($amount_max != null) {
            $this->db->where("ABS(gl.amount) <=", abs($amount_max));
        }
        $result = $this->db->order_by("tran_date, counter")->get();

        bug($this->db->last_query());
        bug($result);

        die;
        // bug($sql);
        return db_query($sql, "The transactions for could not be retrieved");
    }
}