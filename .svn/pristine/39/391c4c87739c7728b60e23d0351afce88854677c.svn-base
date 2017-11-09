<?php if ( ! defined('BASEPATH') || !class_exists('CI_Model')) exit('No direct script access allowed');
class Sale_Trans_Model  extends CI_Model {

    public function get_parent_lines($trans_type, $trans_no, $lines=true, $sql_return=false){

        $partype = get_parent_type($trans_type);
        $par_tbl = 'debtor_trans_details';
        $par_no = 'parent.debtor_trans_no';
        if( $partype == ST_SALESORDER ){
            $par_tbl = 'sales_order_details';
            $par_no = 'parent.order_no';

        }

    	$this->db->select('parent.*');
    	$this->db->join('debtor_trans_details AS trans','trans.src_id=parent.id','LEFT');
    	$this->db->where('trans.debtor_trans_type',$trans_type);
    	$this->db->where('trans.debtor_trans_no',$trans_no);

    	if (!$lines){
    	    $this->db->group_by($par_no);
    	}
        $result = $this->db->order_by($par_no)->get("$par_tbl AS parent");

        if( !is_object($result) ){
            display_error(_("can't retrieve child trans") );
            return false;
        }
//         bug( $this->db->last_query() );
        if( $sql_return ){
            return $this->db->last_query();
        }
        return $result->result_array();
    }

}