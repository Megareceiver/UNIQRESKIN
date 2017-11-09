<?php

class Supplier_Model extends CI_Model {
    function __construct(){
        parent::__construct();

    }

    function load_import_goods_items(){
        $this->db->select('tran.reference, tran.supp_reference, tran.tran_date,tran.rate, tran.trans_no');

        $this->db->where(array('tran.imported_goods'=>1,'tran.type'=>ST_SUPPINVOICE))->from('supp_trans AS tran');
        $this->db->join('supp_invoice_items AS ite','ite.supp_trans_no = tran.trans_no AND ite.supp_trans_type = tran.type');
        $this->db->select('ite.*');

        $this->db->join('suppliers AS supp','supp.supplier_id = tran.supplier_id');
        $this->db->select('supp.supp_name, supp.curr_code AS supp_curr');

        $items = $this->db->get()->result();

        return $items;
    }
}