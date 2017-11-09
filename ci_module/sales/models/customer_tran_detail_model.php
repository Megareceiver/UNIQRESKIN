<?php

/**
 * Created by PhpStorm.
 * User: QuanICT
 * Date: 6/26/2017
 * Time: 3:50 PM
 */
class SalesCustomerTranDetail_Model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function get_customer_trans_details($debtor_trans_type, $debtor_trans_no,$gl_item=false)
    {
        if (!is_array($debtor_trans_no))
            $debtor_trans_no = array( 0=>$debtor_trans_no );

        $this->db->from("debtor_trans_details AS de")->select("de.*, de.description As StockDescription");
        $this->db->select("de.unit_price + de.unit_tax AS FullUnitPrice",false);

        if( !$gl_item ){
            $this->db->join("stock_master AS t","t.stock_id=de.stock_id")
                ->select("t.long_description, t.units, t.mb_flag");
            ;
        }


        $this->db->where_in("de.debtor_trans_no",$debtor_trans_no);
        $this->db->where("de.debtor_trans_type",$debtor_trans_type);
        $query = $this->db->order_by("de.id")->get();

        if( !is_object($query) ){
            return db_query($this->db->last_query(), "The debtor transaction detail could not be queried");
        }
        return $query->result();

    }
}