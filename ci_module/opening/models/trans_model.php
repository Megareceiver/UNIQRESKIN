<?php
class Opening_Trans_Model extends CI_Model {
    function __construct(){
        parent::__construct();
        $this->allocation_model = module_model_load('allocation','gl');
    }

    function balance_total($type=0,$detor_no=0){

        $this->db->select("SUM(debit-credit) AS amount",false);

        if( $type != null ){
            $this->db->where('type',$type);
        }
        if( $detor_no != null ){
            $this->db->where('customer',$detor_no);
        }
        $result = $this->db->get('opening_sale');
//         bug( $this->db->last_query() );
        if( is_object($result) ){
            if( $type ){
                $data = $result->row();
                return $data->amount;
            }
            return $result->result();
        }
    }

    function opening_customer(){
        $type = ST_OPENING_CUSTOMER;
//         $allocated_sql = $this->allocation_model->str_for_invoice("sale.trans_no",$type);
//         $this->db->reset();
        $this->db->select("sale.trans_no, sale.tran_date, sale.type, deb.name, sale.debit, sale.credit");
        $this->db->select("(sale.debit*sale.curr_rate) AS debit_base",FALSE);
        $this->db->select("(sale.credit*sale.curr_rate) AS credit_base",FALSE);
//         $this->db->select('sale.*, deb.name');
        $this->db->where(array('sale.type'=>$type))
        ->join('debtors_master AS deb', 'deb.debtor_no=sale.customer', 'left');
//         $this->db->select("($allocated_sql) AS allocation",false);
        $this->db->from('opening_sale AS sale')->group_by('sale.trans_no');

        $this->db->select("sale.credit*sale.curr_rate AS credit_base",false);
        $this->db->select("sale.debit*sale.curr_rate AS debit_base",false);

        $this->db->select('sale.id');
        $this->db->get();
        $query = $this->db->last_query();

        return $query;

    }

    function openingCustomerItem($id=0,$type=ST_OPENING_CUSTOMER){

        $table_allocated = ( $type==ST_OPENING_CUSTOMER ) ? "cust_allocations" : "supp_allocations";
        $allocated_sql = $this->allocation_model->str_for_invoice("ob.trans_no","ob.type", NULL,'alloc_of_invoice',$table_allocated);
        $this->db->reset();

        $this->db->select('ob.*');
        $this->db->select("($allocated_sql) AS allocation",false);
        //         if( $type==ST_OPENING_CUSTOMER){
        //             $this->db->join('cust_allocations AS allo','allo.trans_no_to=ob.trans_no AND allo.trans_type_to=ob.trans_no','left');
        //             $this->db->select('allo.amt AS allocation,allo.trans_no_from,allo.trans_type_from');
        //         } elseif ($type==ST_OPENING_SUPPLIER) {
        //             $this->db->join('supp_allocations AS allo','allo.trans_no_to= ob.trans_no AND allo.trans_type_to=ob.trans_no','left')->select('allo.amt AS allocation');
        //         }

        $result = $this->db->where('ob.id',$id)->group_by('ob.trans_no')->get('opening_sale AS ob');
        //         bug($this->db->last_query() );
        if( is_object($result) ){
            $data = $result->row();
            $data->payment_from = $this->allocation_model->payment_items($data->trans_no, $data->type);

            return $data;
        } else {
            bug( $this->db->last_query() ) ; die;
        }
    }


    function opening_supplier(){
        $type = ST_OPENING_SUPPLIER;
//         $this->db->select('sale.*, sup.supp_name AS name')

        $this->db->select("sale.trans_no, sale.tran_date, sale.type, sup.supp_name AS name, sale.debit, sale.credit");
        $this->db->select("(sale.debit*sale.curr_rate) AS debit_base",FALSE);
        $this->db->select("(sale.credit*sale.curr_rate) AS credit_base",FALSE);

        $this->db->from("opening_sale AS sale");
        $this->db->select("sale.credit*sale.curr_rate AS credit_base",false);
        $this->db->select("sale.debit*sale.curr_rate AS debit_base",false);

        $this->db->join('suppliers AS sup', 'sup.supplier_id=sale.customer', 'left');


        $this->db->where(array(
            'type'=>$type,
            'sup.supp_name !='=>''
        ));

        $this->db->join('supp_allocations AS allo','allo.trans_no_to=sale.trans_no AND allo.trans_type_to='.$type,'left')->select('allo.amt AS allocation');
        $this->db->select('sale.id');
        $return = $this->db->get();
//         bug($return);die;
        $query = $this->db->last_query();

        return $query;
//         $tempdb = clone $this->db;
//         $result = $this->db->limit(page_padding_limit,($page-1)*page_padding_limit)->group_by('sale.id')->get();

//         if( is_object($result) ){
//             return array('items'=>$result->result(),'total'=>$tempdb->count_all_results());
//         } else {
//             bug( $this->db->last_query() ) ; die;
//         }
    }
}