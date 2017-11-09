<?php

class Documents_Mobile_Model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

    function item($id=0){
        $row = $this->db->where('id',$id)->get('documents_bookkeepers')->row();
        return $row;
    }
    function document_items($tran_type=0,$date_from=null,$date_to=null,$status=0){

        if( is_date($date_from) ){
            $date_from_str = strtotime($date_from);
            $this->db->where('datetime >', date("Y-m-d",$date_from_str));
        }

        if( is_date($date_to) ){
            $date_to_str = strtotime($date_to);
            $this->db->where('datetime <',date("Y-m-d",strtotime("+1 day",$date_to_str)));
        }

//         if( strlen($tran_type) <1 ){
//             $tran_type = key($this->document_tran_types);
//         }

        if( strlen($tran_type) > 0 ){
            $this->db->where('tran_type',$tran_type);
        }

        if( ($status) > 0 ){
            $this->db->where('status', $status);
        }

        $this->db->select('tran_type ,id, ref, party, status,  datetime');

        $this->db->from('documents_bookkeepers');
        $this->db->order_by('datetime DESC');

        /*
         * add to check
         */
//         $this->db->where_not_in('tran_type', array('bank_payment','bank_deposit','supplier_payment','supplier_bill'));

        $db_check = clone $this->db;
        $this->db->get();
        $query = $this->db->last_query();
        $this->check_update_fields($db_check);

        return $query;
    }

    private function check_update_fields($db){
        $db->where('(ref IS NULL OR party IS NULL OR file IS NULL)');
        $db->select('data, file');
        $items = $db->get();
        if($items->num_rows() > 0) foreach ($items->result() AS $ite){
            $data = unserialize($ite->data);
            if( strlen($ite->ref) < 1 ){
                $this->db->where('id',$ite->id)->update('documents_bookkeepers',array('ref'=>$data['ref']));
            }
            if( strlen($ite->party) < 1 ){
                $party_str = $this->tran_party($ite->tran_type,$data);
                $this->db->where('id',$ite->id)->update('documents_bookkeepers',array('party'=>$party_str));
            }

            if( strlen($ite->file) < 1 AND isset($data['file']) ){
                $this->db->where('id',$ite->id)->update('documents_bookkeepers',array('file'=>$data['file']));
            }
        }
    }

    private function tran_party($tran_type=NULL,$datasubmit=array()){
        switch ($tran_type){
            case 'bank_deposit':
                $str = "BD";
                break;
            case 'bank_payment':
                $str = "BP";
                break;
            case 'bank_statement':
                $str = "BT"; break;

            case 'customer_bill':
                $str = "CB"; break;
            case 'customer_receipt':
                $str = "CR"; break;

            case 'expense_bill':
                $str = "EB"; break;
            case 'expense_payment':
                $str = "EB"; break;

            case 'supplier_bill':
            case 'supplier_payment':
                $str = NULL;
                if( isset($datasubmit['supplier_id']) ){
                    $supplier = $this->db->where('supplier_id',$datasubmit['supplier_id'])->get('suppliers')->row();
                    if( is_object($supplier) ){
                        $str = $supplier->supp_name;
                    }
                }

                break;
                //          case 'supplier_payment':
                //                 bug($datasubmit);
                //                 $str = $datasubmit['supplier_name']; break;
            default:
                $str = NULL;
        }
        return $str;
    }

    function update_posting_link($tran_type=0,$tran_no=0,$document_id=0){
        if( min($tran_type,$tran_no,$document_id) < 1 )
            return ;
        switch ($tran_type){
            case ST_SUPPINVOICE:
                $this->update_supplier_tran($tran_type,$tran_no,$document_id);
                break;
            case ST_SUPPAYMENT:
                $this->update_bank_tran($tran_type,$tran_no,$document_id);
                $this->update_supplier_tran($tran_type,$tran_no,$document_id);
                break;
            case ST_BANKPAYMENT:
            case ST_BANKDEPOSIT;
                $this->update_bank_tran($tran_type,$tran_no,$document_id);
                break;
            case ST_CUSTPAYMENT:
                $this->update_bank_tran($tran_type,$tran_no,$document_id);
                $this->update_customer_tran($tran_type,$tran_no,$document_id);
                break;
        }
        $this->db->where('id',$document_id)->update('documents_bookkeepers',array('status'=>2,'tran_no'=>$tran_no));

    }

    private function add_document_field($table=''){
        if ( !$this->db->field_exists('document_upload', $table)){
            $this->db->query(" ALTER TABLE `$table` ADD `document_upload` INT(11) NULL DEFAULT 0;");
        }
    }

    private function update_customer_tran($tran_type=0,$tran_no=0,$document_id=0){
        $this->add_document_field('debtor_trans');
        $this->db->where(array('type'=>$tran_type,'trans_no'=>$tran_no))->update('debtor_trans',array('document_upload'=>$document_id));
    }
    private function update_supplier_tran($tran_type=0,$tran_no=0,$document_id=0){
        $this->add_document_field('supp_trans');
        $this->db->where(array('type'=>$tran_type,'trans_no'=>$tran_no))->update('supp_trans',array('document_upload'=>$document_id));
    }
    private function update_bank_tran($tran_type=0,$tran_no=0,$document_id=0){
        $this->add_document_field('bank_trans');
        $this->db->where(array('type'=>$tran_type,'trans_no'=>$tran_no))->update('bank_trans',array('document_upload'=>$document_id));
    }
}