<?php
class Sales_Customer_Model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

//     function item_options(){
//         $options = array();
//         $data = $this->db->select('debtor_no AS id,name,curr_code')->order_by('name', 'ASC')->get('debtors_master')->result();
//         if( $data ){
//             foreach ($data AS $row){
//                 $options[$row->id] = $row->name.' - '.$row->curr_code;
//             }
//         }
//         return $options;
//     }

    function debtor_id_first(){
        $debtor = $this->db->select('debtor_no')->order_by('name', 'ASC')->get('debtors_master')->row();
        return is_object($debtor) ? $debtor->debtor_no : 0;
    }

    function get_customer_name($customer_id=0)
    {
        $result = $this->db->select('name')->where('debtor_no',$customer_id)->get('debtors_master');

        if( !is_object($result) ){
            check_db_error("could not get customer", $this->db->last_query() );
        }
        return $result->row()->name;
    }

    function get_customers($customer_id=0){

        if(  strlen($customer_id) > 0 && is_numeric($customer_id) && $customer_id > 0 ){
            $this->db->where('debtor_no',$customer_id);
        }
        $result = $this->db->select('debtor_no, name, curr_code')
                    ->order_by("name ASC")
                    ->get('debtors_master');

        if( !is_object($result) ){
            check_db_error("The customers could not be retrieved", $this->db->last_query() );
        }
        return $result->result_array();

    }

    function get_detail($customer_id=0){

        if(  strlen($customer_id) > 0 && is_numeric($customer_id) && $customer_id > 0 ){
            $this->db->where('debtor_no',$customer_id);
        }
        $result = $this->db->select('*')
        ->order_by("name ASC")
        ->get('debtors_master');

        if( !is_object($result) ){
            check_db_error("The customers could not be retrieved", $this->db->last_query() );
        }
        $data = $result->row();


        return $data;


    }
}