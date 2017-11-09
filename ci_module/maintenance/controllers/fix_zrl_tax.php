<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaintenanceFixZrltax {
    function __construct() {
        $this->db = get_instance()->db;
    }
    function index(){
        $this->db->from('debtor_trans AS tran');
        $this->db->join('debtor_trans_details AS d',"d.debtor_trans_type=tran.type AND d.debtor_trans_no = tran.trans_no");
        $this->db->select('d.id AS detail_id, d.tax_type_id, tran.type, tran.trans_no');

        $this->db->where_in('tran.type',array(ST_SALESINVOICE,ST_CUSTDELIVERY));

//         $this->db->where(array('d.stock_id'=>'GAS','d.tax_type_id <>'=>27,'d.unit_price <>'=>0));
        $this->db->where(array('d.tax_type_id'=>37,'d.unit_price <>'=>0));
        $trans = $this->db->get();
        if( $trans->num_rows() > 0 ) {
//             include_once(ROOT . "/sales/includes/cart_class.inc");
            foreach ( $trans->result() AS $tran){
//                 if( $tran->tax_type_id==26){
                    $this->db->where('id',$tran->detail_id)->update('debtor_trans_details',array('tax_type_id'=>27));
//                     if( $tran->type== ST_SALESINVOICE){
//                         $sale_model = module_model_load('invoice','sales');
//                         $invoice_no = $sale_model->write_invoice( new Cart($tran->type, array($tran->trans_no) ) ,true);
//                     }
//                 }
            }
        }
        bug($trans);die;
    }
}