<?php
class Company_Payment_term_Model extends CI_Model {
    function __construct(){
    }

    function item($id=0){
        $this->db->select('t.*, (t.days_before_due=0) AND (t.day_in_following_month=0) as cash_sale');
        $this->db->from('payment_terms AS t');
        $this->db->where('terms_indicator',$id);

        $item = $this->db->get();
        bug($item);die;

//         $result = db_query($sql,"could not get payment term");

    }
}