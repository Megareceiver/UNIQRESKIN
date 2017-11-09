<?php
class Crm_Branch_Model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }
    
    function get_item($branch_code)
    {
        $this->db->from("cust_branch AS b")->select("b.*");
        $this->db->where("b.branch_code",$branch_code);
        $this->db->join('salesman AS s',"s.salesman_code = b.salesman","LEFT")->select("s.salesman_name");
        
        $query = $this->db->get();
        if (! is_object($query) or empty($query)) {
            check_db_error("Cannot retreive a customer branch", $this->db->last_query());
        } else {
            return $query->row();
        }
    }
}