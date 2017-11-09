<?php
class Sales_Trans_Model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

    function get_tran($tran_type,$tran_no){
        $result = $this->db->where(array('trans_no'=>$tran_no, 'type'=>$tran_type))->get('debtor_trans');

        if( !is_object($result) ){
            check_db_error("can not get tran detail", $this->db->last_query() );
            return FALSE;
        }
        $data = $result->row();
        $data->branch_detail = $this->db->where(array('branch_code'=>$data->branch_code))->get('cust_branch')->row();
        return $data;
    }

    function remove_trans_detail($trans_type, $trans_no){
        $this->db->reset();
        if ($trans_no != 0 ){
            $this->db->delete('debtor_trans_details',array('debtor_trans_no'=>$trans_no,'debtor_trans_type'=>$trans_type));
        }
        return true;
    }

    /*
     * update write_customer_trans() in cust_trans_db.inc
     * 150513
     * quannh
     */
    function write_customer_trans($trans_type, $trans_no, $data){
        if( !isset($data['debtor_no']) )
            return false;

        if( isset($data['tran_date']) ){
            $data['tran_date'] = date2sql($data['tran_date']);
        } else {
            $data['tran_date'] = '0000-00-00';
        }

        if( isset($data['due_date']) ){
            $data['due_date'] = date2sql($data['due_date']);
        } else {
            $data['due_date'] = '0000-00-00';
        }

        if ( $trans_type == ST_BANKPAYMENT)
            $data['ov_amount'] = -abs($data['ov_amount']);

        if ( !isset($data['rate']) || $data['rate'] == 0) {
            $curr = get_customer_currency($data['debtor_no']);
            $data['rate'] = get_exchange_rate_from_home_currency($curr, $data['tran_date']);
        }


        if( !isset($data['ov_discount']) ) {
            $data['ov_discount'] = 0;
        }
        if( !isset($data['ov_gst']) ) {
            $data['ov_gst'] = 0;
        }
        if( !isset($data['ov_freight_tax']) ) {
            $data['ov_freight_tax'] = 0;
        }

        $new = 0;
        $this->db->reset();
        if( !exists_customer_trans($trans_type, $trans_no) ){
            $trans_no = get_next_trans_no($trans_type);
            $data['trans_no'] = $trans_no;
            $data['type'] = $trans_type;
            $sql = $this->db->insert('debtor_trans',$data,true );
        } else {
            $sql = $this->db->update('debtor_trans',$data,array('trans_no'=>$trans_no,'type'=>$trans_type),1,true );
        }

        db_query($sql, "The debtor transaction record could not be inserted");
        add_audit_trail($trans_type, $trans_no, $data['tran_date'], $new ? '': _("Updated."));

        return $trans_no;
    }

    function write_customer_trans_detail($trans_type, $trans_no=0,$data,$check_existed=true){
        $data['debtor_trans_type'] = $trans_type;
        if( !isset($data['discount_percent'] ) OR !$data['discount_percent'] ) {
            $data['discount_percent'] = 0;
        }
        if ( $check_existed && $trans_no != 0 && $this->debtor_trans_detail_exist($trans_no,$trans_type) ){
            return true;
        } else {
            $this->db->reset();
            $data['debtor_trans_no'] = $trans_no;
            $sql = $this->db->insert('debtor_trans_details',$data,true );
        }
        db_query($sql, "The debtor transaction detail could not be written");
        return true;
    }

    private function debtor_trans_detail_exist($trans_no,$trans_type=0,$stock_id=NULL){
        $this->db->reset();
        $this->db->where('debtor_trans_type',$trans_type);
        $this->db->where('debtor_trans_no',$trans_no);
        if( strlen($stock_id) > 1 ){
            $this->trans->where('stock_id',$stock_id);
        }
        $row = $this->db->get('debtor_trans_details')->row();

        if( $row && isset($row->id) ){
            return true;
        }
        return false;

    }

    function update_customer_trans($trans_type, $trans_no, $data){
        if( isset($data['tran_date']) ){
            $data['tran_date'] = date2sql($data['tran_date']);
        }
        if( isset($data['due_date']) ){
            $data['due_date'] = date2sql($data['due_date']);
        }
        if ( array_key_exists('rate', $data) && $data['rate'] == 0 ) {
            $curr = get_customer_currency($data['debtor_no']);
            $data['rate'] = get_exchange_rate_from_home_currency($curr, $data['tran_date']);
        }

        $this->db->reset();
        $sql = $this->db->update('debtor_trans',$data,array('trans_no'=>$trans_no,'type'=>$trans_type),1,true );
        db_query($sql, "The debtor transaction record could not be inserted");
        return $trans_no;
    }

    function update_customer_trans_detail($trans_type, $id=0,$data){
        $this->db->reset();
        if( $data ){
            foreach ($data AS $field=>$val){
                $this->db->set($field,$val,false);
            }
        }
        $sql = $this->db->update('debtor_trans_details',null,array('id'=>$id),1,true );
        db_query($sql, "The parent document detail record could not be updated");
        return true;
    }
}