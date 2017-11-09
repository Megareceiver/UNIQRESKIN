<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaintenanceUpdate {
    function __construct() {
        $ci = get_instance();
        $this->db = $ci->db;
        $this->ref = $ci->ref;

    }

    function do_fix(){
        $update_methods = get_class_methods($this);

        foreach ($update_methods AS $method){
            $check = new ReflectionMethod($this, $method);

            if($check->isPublic()){

            } elseif($check->isPrivate()){
                $this->$method();
            } else{
            }
        }

//         $this->OpeningClean();


//         $this->restore_customer_trans();

    }

    private  function change_opening_type(){
        $this->db->where('type',ST_SALESINVOICE)->update('opening_sale',array('type'=>ST_OPENING_CUSTOMER));
        $this->db->where('type',ST_SUPPINVOICE)->update('opening_sale',array('type'=>ST_OPENING_SUPPLIER));
    }
    private function supplier_ob(){
        $ov_amount = "(ob.debit-ob.credit)";
        $this->db->where(array('ob.type'=>ST_OPENING_SUPPLIER));
        $this->db->join('supp_trans AS tran','tran.type=ob.type AND tran.trans_no = ob.trans_no','LEFT');
        $this->db->select("ob.type, ob.trans_no, tran.ov_amount, $ov_amount AS ob_amount");
        $this->db->where("tran.ov_amount <> $ov_amount");
        $result = $this->db->get('opening_sale AS ob');

        if( is_object($result) && $result->num_rows > 0 ) foreach ($result->result() AS $ite){
//             die('check fix supplier ob');
            if( $ite->ov_amount = $ite->ob_amount * (-1) ){
                $this->db->where(array('trans_no'=>$ite->trans_no,'type'=>$ite->type))->update('supp_trans',array('ov_amount'=>$ite->ob_amount));
            }
        }

    }

    private function remove_supplier_opening_trans(){
        $supp_trans = $this->db->where('type',ST_OPENING_SUPPLIER)->get('supp_trans')->result();
        foreach ($supp_trans AS $tran){

            $ob_check = $this->db->where(array('type'=>$tran->type,'trans_no'=>$tran->trans_no))->get('opening_sale')->row();
            if( !isset($ob_check->id) ){
                $this->db->where(array('type'=>$tran->type,'trans_no'=>$tran->trans_no))->delete('supp_trans');
                $this->ci->db->where(array('type'=>$tran->type,'type_no'=>$tran->trans_no))->delete('gl_trans');
            }
        }

    }
    private function restore_gl_fix(){

        $items = $this->db->where_in('type',array(ST_SUPPAYMENT,ST_CUSTPAYMENT) )->get('data_incorrect');

        if( is_object($items) && $items->num_rows > 0 ) foreach ($items->result() AS $ite){
            if( $ite->type==ST_SUPPAYMENT ){
                $tran = $this->db->where(array('type'=>$ite->type,'trans_no'=>$ite->trans_no))->get("supp_trans")->row();
            } elseif ($ite->type==ST_CUSTPAYMENT){
                $tran = $this->db->where(array('type'=>$ite->type,'trans_no'=>$ite->trans_no))->get("debtor_trans")->row();
            }

            if( is_object($tran) && trim($tran->reference) !="" ){
                $data = (array)json_decode($ite->data);
                if( count($data) > 1 ){
                    $this->db->insert($ite->table,$data);
                    $this->db->delete('data_incorrect', array('id' => $ite->id));
                }
            }

        }


    }
    public function opening_gl_fix($type=null,$trans_no=0,$gl_counter=0){

        if( $type < 0 || $trans_no < 1 ) return false;

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


        $gl_tran = $this->db->where('counter',$gl_counter)->get("gl_trans")->row_array();
        $store = array(
            'data'=>json_encode($gl_tran),
            'table'=>'gl_trans',
            'type'=>$type,
            'trans_no'=>$trans_no
        );

        $this->db->insert('data_incorrect',$store);
        $this->db->delete('gl_trans', array('counter' => $gl_counter));
        return true;

    }
    private function update_reference($type=ST_CUSTPAYMENT){
        $customer_payment = $this->db->where( array('type'=>$type,'reference'=>"") )->order_by('id ASC')->get('refs');

        if( $customer_payment->num_rows >0 ) foreach ($customer_payment->result() AS $ite){
            $next_ref = $this->ref->get_next($type);

            $this->db->where(array('trans_no'=>$ite->id,'type'=>$ite->type))->update('debtor_trans',array('reference'=>$next_ref));
            $this->db->where(array('id'=>$ite->id,'type'=>$ite->type))->update('refs',array('reference'=>$next_ref));
            $this->ref->save($ite->type,$ite->id,$next_ref);
        }

        $bank_trans = $this->db->where( array('type'=>$type,'ref'=>"") )->order_by('trans_no ASC')->get('bank_trans');
        if( $bank_trans->num_rows >0 ) foreach ($bank_trans->result() AS $ite){
            $ref = $this->ref->get($ite->type,$ite->trans_no);
            if( $ref ){
                $this->db->where(array('trans_no'=>$ite->trans_no,'type'=>$ite->type))->update('bank_trans',array('ref'=>$ref));
            }
        }

        $this->db->where_in('tran.type', array(ST_SUPPAYMENT,ST_SUPPINVOICE,ST_SUPPCREDIT) );
        $this->db->left_join('refs AS ref', 'ref.id=tran.trans_no AND ref.type=tran.type' )->select('ref.reference AS ref_reference, ref.id AS ref_id');
        $this->db->where("(ref.reference='' OR ref.reference IS NULL)");
        $supplier_trans = $this->db->order_by('tran.trans_no ASC')->select('tran.*')->get('supp_trans AS tran');

        if( $supplier_trans->num_rows >0 ) foreach ($supplier_trans->result() AS $ite){
            if( $ite->ref_id=="" ){
                $this->db->insert('refs', array('id'=>$ite->trans_no,'type'=>$ite->type,'reference'=>$ite->reference) );
            }
        }

        $this->db->where_in('tran.type', array(ST_CUSTPAYMENT,ST_SALESINVOICE,ST_CUSTCREDIT) );
        $this->db->left_join('refs AS ref', 'ref.id=tran.trans_no AND ref.type=tran.type' )->select('ref.reference AS ref_reference, ref.id AS ref_id');
        $this->db->where("(ref.reference='' OR ref.reference IS NULL)");
        $customer_trans = $this->db->order_by('tran.trans_no ASC')->select('tran.*')->get('debtor_trans AS tran');
        if( $customer_trans->num_rows >0 ) foreach ($customer_trans->result() AS $ite){
            if( $ite->ref_id=="" ){
                $this->db->insert('refs', array('id'=>$ite->trans_no,'type'=>$ite->type,'reference'=>$ite->reference) );
            }
        }

        $this->db->where_in('tran.type', array(ST_BANKPAYMENT,ST_BANKDEPOSIT,ST_BANKTRANSFER) );
        $this->db->left_join('refs AS ref', 'ref.id=tran.trans_no AND ref.type=tran.type' )->select('ref.reference AS ref_reference, ref.id AS ref_id');
        $this->db->where("(ref.reference='' OR ref.reference IS NULL)");
        $bank_trans = $this->db->order_by('tran.trans_no ASC')->select('tran.*')->get('bank_trans AS tran');

        if( $bank_trans->num_rows >0 ) foreach ($bank_trans->result() AS $ite){
            if( $ite->ref_id=="" && $ite->ref != "" ){
                $this->db->insert('refs', array('id'=>$ite->trans_no,'type'=>$ite->type,'reference'=>$ite->ref) );
            }
        }

    }

    private function updateSupplierOB(){
        $ob_item = $this->db->where('type',ST_OPENING_SUPPLIER)->get('opening_sale AS OB');
        if( is_object($ob_item) && $ob_item->num_rows > 0 ) foreach ($ob_item->result() AS $ob){
            if ( floatval($ob->credit) > 0 ){
                $amount = $ob->credit;
            } else if ( floatval($ob->debit) > 0 ){
                $amount = -$ob->debit;
            }
            $where_tran = array('trans_no'=>$ob->trans_no,'type'=>$ob->type);
            $this->db->where($where_tran)->update('supp_trans',array('ov_amount'=>$amount));
        }
//         bug($ob_item);die;
    }

    private function customer_payment(){
        $this->db->where('tran.type',ST_CUSTPAYMENT)->from('bank_trans AS tran');
        $this->db->left_join('gl_trans AS gl',array('gl.type'=>'tran.type','gl.type_no'=>'tran.trans_no'));
        $this->db->select('tran.*');
        $this->db->where('tran.person_type_id <> gl.person_type_id');

        $this->db->select('gl.person_id AS person_id_gl, gl.person_type_id AS person_type_id_gl');

        $trans = $this->db->get();
        if( $trans->num_rows > 0 ){
            foreach ($trans->result() AS $row){
                //             if( !in_array($row->person_type_id, array(PT_MISC,PT_QUICKENTRY,PT_WORKORDER,PT_CUSTOMER,PT_SUPPLIER)) ){
                if( in_array($row->person_type_id_gl, array(PT_CUSTOMER)) ){

                    $this->db->where(array('type'=>$row->type,'trans_no'=>$row->trans_no))
                    ->update('bank_trans',array('person_type_id'=>$row->person_type_id_gl,'person_id'=>$row->person_id_gl));

                    unset($row->person_type_id_gl);
                    unset($row->person_id_gl);
                    $store = array(
                        'data'=>json_encode($row),
                        'table'=>'bank_trans',
                        'type'=>$row->type,
                        'trans_no'=>$row->trans_no
                    );

                    $this->db->insert('data_incorrect',$store);
                }
            }
        }
    }

    private function customer_ref2(){

        if ( !$this->db->field_exists('cust_ref2', 'debtor_trans')){
            $this->db->query("ALTER TABLE debtor_trans ADD `cust_ref2` varchar(50) NULL; ");

        }
    }

    private function updateKastam201610(){
        if ( !$this->db->field_exists('imported_goods_invoice', 'bank_trans')){
            $this->db->query(" ALTER TABLE `bank_trans` ADD `imported_goods_invoice` varchar(255) NULL DEFAULT NULL;");
        }

        if ( !$this->db->field_exists('msic', 'stock_category')){
            $this->db->query(" ALTER TABLE `stock_category` ADD `msic` CHAR(10) NULL; ");
        }
    }

    private function OpeningClean(){
        $maintenance = module_control_load("opening_clean",'maintenance');
        $maintenance->customer();
    }

    private function expense(){
        $expense = module_model_load("expense",'admin');
        $expense->create_table();
    }

    private function modbile_document(){
        if ( !$this->db->field_exists('imei', 'users')){
            $this->db->query(" ALTER TABLE `users` ADD `imei` varchar(100) NULL DEFAULT NULL;");
        }
        if ( !$this->db->field_exists('permit', 'supp_trans')){
            $this->db->query(" ALTER TABLE `supp_trans` ADD `permit` varchar(50) NULL DEFAULT NULL;");
        }
    }

    function restore_customer_trans(){
        $fix = module_control_load('issues_fix','bank');
//         $fix = module_control_load('issues_fix','sales');
        $fix->backDB = get_instance()->load_db(array('dbname'=>'at_lwind16-161022'),false,true);
        $fix->customer_supplier_payment(true);

//         $fix->restore_customer_invoice(true);
bug($fix);die;
//         bug($query->num_rows());
//         bug($query->result());
//         bug($trans_lost);
//         die;

    }



//     private function check_bank_duplicate(){
//         $bank_fix = module_control_load('issues_fix','bank');
//         $bank_fix->check_bank_duplicate();
//     }
}