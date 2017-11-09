<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaintenanceOpeningClean {
    function __construct() {
        $this->db = get_instance()->db;
    }

    function customer(){

        $ob = $this->db->from("opening_sale AS ob")->get();

        $debtor_ob = $supplier_ob = array();
        if($ob->num_rows() > 0) foreach ($ob->result() AS $o){

            $trans = $this->db->where('trans_no',$o->trans_no)
                    //->where_in('type',array(ST_SALESINVOICE,ST_OPENING_CUSTOMER) )
                    ->where('type',$o->type )
                    ->get("debtor_trans");


            if( $trans->num_rows() == 1 ){
                $tran = $trans->row();
                if( $tran->type==ST_SALESINVOICE ){
                    $this->db->where(array('trans_no'=>$tran->trans_no,'type'=>$tran->type))->update("debtor_trans",array('type'=>ST_OPENING_CUSTOMER));

                }

                if( $tran->type==ST_OPENING_CUSTOMER ){
                    $debtor_ob[] = $o->trans_no;
                }

                if( $tran->type==ST_OPENING_SUPPLIER ){
                    $supplier_ob[] = $o->trans_no;
                }



            } elseif( $trans->num_rows() > 1 ){
                $this->db->where(array('trans_no'=>$o->trans_no,'type <>'=>ST_OPENING_CUSTOMER))->delete("debtor_trans");
            }

        }

        $this->db->where_not_in('trans_no',$debtor_ob)
                ->where('type',ST_OPENING_CUSTOMER)
            ->delete("debtor_trans");

        $this->db->where_not_in('trans_no',$supplier_ob)
        ->where('type',ST_OPENING_SUPPLIER)
        ->delete("supp_trans");


    }
}