<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Admin_Audit_trail_Model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

    function open_transactions($fromdate=NULL){
        if( !is_date($fromdate) )
            return false;

        $this->db->select('a.id, a.gl_date, a.fiscal_year');
        $this->db->join('audit_trail AS a','gl.type=a.type AND gl.type_no=a.trans_no','LEFT');
        $this->db->where('gl_date >=',date2sql($fromdate));
        $this->db->where('!ISNULL(a.gl_seq)');
        $this->db->order_by('a.fiscal_year, a.gl_date, a.id');
        $result = $this->db->get('gl_trans AS gl');
        if( !is_object($result) ){
            display_error( _("Cannot select transactions for openning"));
            return false;
        }
        if( $result->num_rows > 0 ) {
            $data = $result->result();

            $last_year = 0;
            foreach ($result->result() AS $row ){
                $this->db->update('audit_trail',array('gl_seq'=>0),array('id'=>$row->id));
            }
            //db_query($sql2, "Cannot clear journal order");
        }
        return true;
    }
}