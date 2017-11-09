<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PurchasesCheck {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->db = $ci->db;

    }

    function allocation_wrong(){
        $alloc_str = "(SELECT SUM(amt) FROM supp_allocations AS alloc WHERE alloc.trans_no_to = tran.trans_no AND alloc.trans_type_to = tran.type)";
        $this->db->where('(tran.alloc <> 0 OR tran.alloc IS NOT NULL)');
        $this->db->select("$alloc_str AS alloc_amt",false);
        $this->db->select('tran.*');
        $this->db->select( "IF(tran.ov_amount >0,ov_amount,0) AS debit",false);
        $this->db->select( "IF(tran.ov_amount <0,-ov_amount,0) AS credit",false);
        $this->db->where('tran.type',ST_SUPPINVOICE);
        $this->db->where("($alloc_str IS NULL AND tran.alloc > 0)");
        $items = $this->db->get('supp_trans AS tran')->result();

        $table = array(
            'type'=>array('Type','center',10,'trans_type'),
            'trans_no'=>array('#','center',10,'supp_invoice_link'),
            'reference'=>'REF',
            'supplier'=>'Supplier',
            'tran_date'=>array('Date','center',10,'date'),
            'debit'=>array('Debit','text-right',15,'number'),
            'credit'=>array('Crebit','text-right',15,'number'),
            'alloc'=>array('Allocated','text-right',15,'number'),
        );

        page('Supplier Invoice Wrong Allocations');
        module_view('table_items',array('items'=>$items,'table'=>$table),true, true,'html');
        end_page();
    }

    function payment_incorrect(){
        $type = $this->ci->uri->segment(4);

        if( $type=="customer" ){
            $items = $this->customer_payment_incorrect();
        } else {
            $items = $this->supplier_payment_incorrect();
        }


        $table = array(
            'type'=>array('Type','center',10,'trans_type'),
            'trans_no'=>array('#','center',10,'tran_detail_view'),
            'reference'=>'REF',
            'supplier'=>'Supplier',
            'trans_date'=>array('Date','center',10,'date'),
            'debit'=>array('Debit','text-right',15,'number'),
            'credit'=>array('Crebit','text-right',15,'number'),
        );

        page('Supplier Invoice Wrong Allocations');
        module_view('table_items',array('items'=>$items,'table'=>$table),true, true,'html');
        end_page();
    }

    private function supplier_payment_incorrect(){
        $alloc_str = "(SELECT SUM(amt) FROM supp_allocations AS alloc WHERE alloc.trans_no_to = tran.trans_no AND alloc.trans_type_to = tran.type)";
        $gl_trans_count = "( SELECT COUNT(gl.counter) FROM gl_trans AS gl WHERE gl.type=tran.type AND gl.type_no = tran.trans_no)";

        //         $this->db->where('(tran.alloc <> 0 OR tran.alloc IS NOT NULL)');
        //         $this->db->select("$alloc_str AS alloc_amt",false);
        $this->db->select('tran.*');
        $this->db->select( "IF(tran.amount >0,amount,0) AS debit",false);
        $this->db->select( "IF(tran.amount <0,-amount,0) AS credit",false);
        $this->db->where('tran.type',ST_SUPPAYMENT);

        $this->db->where("$gl_trans_count < 1");
        $this->db->select("$gl_trans_count AS gl_count",false);

        $items = $this->db->get('bank_trans AS tran')->result();
        if( count($items) > 0 ){
            foreach ($items AS $ite){
                $where_exist = array('trans_no'=>$ite->trans_no,'type'=>$ite->type);

                $supp_trans = $this->db->where($where_exist)->get('supp_trans');
                if( $supp_trans->num_rows < 1){
                    $bank_trans_add = array(
                        'trans_no'=>$ite->trans_no,
                        'type'=>$ite->type,
                        'reference'=>$ite->ref,
                        'tran_date'=>$ite->trans_date,
                        'ov_amount'=>$ite->amount,
                        'supplier_id'=>$ite->person_id,
                        'tax_included'=>$ite->tax_inclusive
                    );
                    $this->db->insert('supp_trans',$bank_trans_add);
                }
                $supp_tran_gl = $this->db->where(array('type_no'=>$ite->trans_no,'type'=>$ite->type))->get('gl_trans');
                if( $supp_tran_gl->num_rows < 1){
                    $this->db->query("ALTER TABLE gl_trans MODIFY openning varchar(255) null;");

                    $gl_trans = $this->ci->gl_trans;
                    $gl_trans->trans = array();
                    $gl_trans->set_value('type_no',$ite->trans_no);
                    $gl_trans->set_value('tran_date',$ite->trans_date);
                    $gl_trans->set_value('type',$ite->type);
                    $gl_trans->add_trans(2100,-$ite->amount);
                    $gl_trans->add_trans(1070,$ite->amount);
                    $gl_trans->insert_trans(null);
                }

            }
        }
        return $items;
    }

    private function customer_payment_incorrect(){
        $alloc_str = "(SELECT SUM(amt) FROM supp_allocations AS alloc WHERE alloc.trans_no_to = tran.trans_no AND alloc.trans_type_to = tran.type)";

        $gl_trans_count = "( SELECT COUNT(gl.counter) FROM gl_trans AS gl WHERE gl.amount <> 0 AND gl.type=tran.type AND gl.type_no = tran.trans_no)";

        $this->db->select('tran.*');
        $this->db->select( "IF(tran.amount >0,amount,0) AS debit",false);
        $this->db->select( "IF(tran.amount <0,-amount,0) AS credit",false);
        $this->db->where('tran.type',ST_CUSTPAYMENT);
        $this->db->where(array('tran.amount <>'=>0));

        $this->db->where("$gl_trans_count < 1");
        $this->db->select("$gl_trans_count AS gl_count",false);

        $items = $this->db->get('bank_trans AS tran')->result();
//         bug( $this->db->last_query() );
//         bug($items); die;
        if( count($items) > 0 ){
            foreach ($items AS $ite){
//                 bug($ite);die;
                $where_exist = array('trans_no'=>$ite->trans_no,'type'=>$ite->type);

                $supp_trans = $this->db->where($where_exist)->get('debtor_trans');
                if( $supp_trans->num_rows < 1){
                    $bank_trans_add = array(
                        'trans_no'=>$ite->trans_no,
                        'type'=>$ite->type,
                        'reference'=>$ite->ref,
                        'tran_date'=>$ite->trans_date,
                        'ov_amount'=>$ite->amount,
//                         'supplier_id'=>$ite->person_id,
                        'tax_included'=>$ite->tax_inclusive
                    );
                    $this->db->insert('debtor_trans',$bank_trans_add);
                }

                $tran_gl = $this->db->where(array('type_no'=>$ite->trans_no,'type'=>$ite->type,'gl.amount <>'=>0))->get('gl_trans');
//                 bug($this->db->last_query() );
//                 bug($tran_gl);
//                 bug($ite);die;
                if( is_object($tran_gl) && $tran_gl->num_rows < 1){
                    $this->db->query("ALTER TABLE gl_trans MODIFY openning varchar(255) null;");

                    $gl_trans = $this->ci->gl_trans;
                    $gl_trans->trans = array();
                    $gl_trans->set_value('type_no',$ite->trans_no);
                    $gl_trans->set_value('tran_date',$ite->trans_date);
                    $gl_trans->set_value('type',$ite->type);
                    $gl_trans->add_trans(2100,-$ite->amount);
                    $gl_trans->add_trans(1070,$ite->amount);
                    $gl_trans->insert_trans(null);
                }


//                 die('aaa');
            }
        }
    }
}