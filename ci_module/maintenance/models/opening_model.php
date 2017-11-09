<?php
class Maintenance_Opening_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->allocation_model = module_model_load('allocation','gl');
	}

	function openingCustomer($page=1){
	    $type = ST_OPENING_CUSTOMER;
	    $allocated_sql = $this->allocation_model->str_for_invoice("sale.trans_no",$type);
	    $this->db->reset();
	    $this->db->select('sale.*, deb.name')
	       ->where(array('sale.type'=>$type))
	       ->join('debtors_master AS deb', 'deb.debtor_no=sale.customer', 'left');
	    $this->db->select("($allocated_sql) AS allocation",false)->from('opening_sale AS sale')->group_by('sale.trans_no');

	    $this->db->select("sale.credit*sale.curr_rate AS credit_base",false);
	    $this->db->select("sale.debit*sale.curr_rate AS debit_base",false);

	    $tempdb = clone $this->db;
// 	    $result = $this->db->limit(page_padding_limit,($page-1)*page_padding_limit)->group_by('sale.id')->get();
        $result = $this->db->limit(page_padding_limit,($page-1)*page_padding_limit)->get();

        if( is_object($result) ){
            return array('items'=>$result->result(),'total'=>$tempdb->get()->num_rows);
        } else {
            bug( $this->db->last_query() ) ; die;
        }
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

	function openingSupplier($page=1){
	    $type = ST_OPENING_SUPPLIER;
	    $this->db->select('sale.*, sup.supp_name AS name')->from("opening_sale AS sale");
	    $this->db->select("sale.credit*sale.curr_rate AS credit_base",false);
	    $this->db->select("sale.debit*sale.curr_rate AS debit_base",false);

	    $this->db->join('suppliers AS sup', 'sup.supplier_id=sale.customer', 'left');


	    $this->db->where(array(
	           'type'=>$type,
	           'sup.supp_name !='=>''
	    ));

	    $this->db->join('supp_allocations AS allo','allo.trans_no_to=sale.trans_no AND allo.trans_type_to='.$type,'left')->select('allo.amt AS allocation');

	    $tempdb = clone $this->db;
	    $result = $this->db->limit(page_padding_limit,($page-1)*page_padding_limit)->group_by('sale.id')->get();

	    if( is_object($result) ){
	        return array('items'=>$result->result(),'total'=>$tempdb->count_all_results());
	    } else {
	        bug( $this->db->last_query() ) ; die;
	    }
	}



}