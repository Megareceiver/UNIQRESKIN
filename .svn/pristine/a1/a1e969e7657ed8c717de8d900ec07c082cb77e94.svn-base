<?php
class Void_Tran_Model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

    function not_voided($type, $type_no){
        $this->db->where("$type_no NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=$type )");
    }

    function voided($type, $type_no){
        $this->db->where("$type_no IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=$type )");
    }

    function get_entry($type, $type_no)
    {
        $data = $this->db->where(array('type'=>$type,'id'=>$type_no))->get('voided');
//         $sql = "SELECT * FROM ".TB_PREF."voided WHERE type=".db_escape($type)
//         ." AND id=".db_escape($type_no);
        if( !is_object($data) ){
            display_error('could not query voided transaction table');
            return false;
        } else {
            return $data->result();
        }

    }

}