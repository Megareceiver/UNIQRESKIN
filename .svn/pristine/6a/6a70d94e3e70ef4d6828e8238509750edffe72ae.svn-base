<?php

class Gl_Accounts_Model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }


    function get_gl_accounts($from = null, $to = null, $type = null)
    {
        $this->db->select('acc.*')->from('chart_master AS acc');
        $this->db->left_join('chart_types AS type','type.id = acc.account_type')->select('type.name AS AccountTypeName');

        if ($from != null){
            $this->db->where('acc.account_code >=',$from);
        }

        if ($to != null){
            $this->db->where('acc.account_code <=',$to);
        }

        if ($type != null){
            $this->db->where('acc.account_type',$type);
        }

        $result = $this->db->order_by('acc.account_code ASC')->get();

        if( !is_object($result) ){
            check_db_error('could not get gl accounts', $this->db->last_query());
        } else {
            return $result->result_array();
        }
    }
}