<?php
class VoidVoiding_Model extends CI_Model {
    function __construct(){
    }

    function do_gl($tran_no,$tran_type){
        $this->db->where(array('type_no'=>$tran_no,'type'=>$tran_type))->update('gl_trans',array('amount'=>0));
        bug($this->db->last_query());
    }
}