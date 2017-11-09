<?php if ( ! defined('BASEPATH') || !class_exists('CI_Model')) exit('No direct script access allowed');

class Maintenance_Transaction_Model extends CI_Model {

    function sale_check($page=1){
        $this->db->from('debtor_trans AS trans');
        $this->db->where("trans.trans_no NOT IN ( SELECT void.id FROM voided AS void WHERE void.type=trans.type)");

        $this->db->select('(SELECT count(*) FROM gl_trans AS gl WHERE gl.type = trans.type AND gl.type_no = trans.trans_no ) AS gl_count',false);
        $this->db->select('(SELECT count(*) FROM debtor_trans_details AS tran_item WHERE tran_item.debtor_trans_type = trans.type AND tran_item.debtor_trans_no = trans.trans_no ) AS item_count',false);

        $this->db->select('(SELECT MIN(tran_date) FROM gl_trans AS gl1 WHERE gl1.type = trans.type AND gl1.type_no = trans.trans_no LIMIT 1 ) AS gl_date_min',false);
        $this->db->select('(SELECT MAX(tran_date) FROM gl_trans AS gl2 WHERE gl2.type = trans.type AND gl2.type_no = trans.trans_no LIMIT 1 ) AS gl_date_max',false);


        $total_db = clone $this->db;
        $this->db->limit( page_padding_limit,($page-1)*page_padding_limit);
        $result = $this->db->select('trans.*')->get();

        $total_result = $total_db->get();
        if( is_object($result) ){
            return  array('items'=>$result->result(),'total'=>$total_result->num_rows);
        } else {
            display_error( _("could not get debtor trans"));
        }
    }
}