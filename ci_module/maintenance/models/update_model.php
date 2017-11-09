<?php if ( ! defined('BASEPATH') || !class_exists('CI_Model')) exit('No direct script access allowed');

class Maintenance_Update_Model extends CI_Model {
    function remove_gl($type=null,$trans_no=0){

        if( $type < 0 || $trans_no < 1 ) return false;

        switch ($type){
            case ST_CUSTPAYMENT:
                $tran = $this->db->where(array('type'=>$type,'trans_no'=>$trans_no))->get("debtor_trans")->row();
                break;
            case ST_SUPPAYMENT:
                $tran = $this->db->where(array('type'=>$type,'trans_no'=>$trans_no))->get("supp_trans")->row();
                break;
            default:
                $tran = null;

        }
        if( is_object($tran) )
            return false;


        $gl_where = array('type'=>$type,'type_no'=>$trans_no);
        $gl_tran = $this->db->where($gl_where)->get("gl_trans")->row_array();
        $store = array(
            'data'=>json_encode($gl_tran),
            'table'=>'gl_trans',
            'type'=>$type,
            'trans_no'=>$trans_no
        );


        if( !$this->db->table_exists('data_incorrect') ){
            $this->db->query("CREATE TABLE `data_incorrect` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `table` char(50) NOT NULL,
              `type` int(5) NOT NULL,
              `trans_no` int(11) NOT NULL,
              `data` text,
              `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `comment` text,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;");
        }

        $this->db->insert('data_incorrect',$store);
        $this->db->delete('gl_trans', $gl_where);
        return true;
    }
}