<?php
class Crm_Model {
	function __construct(){
		global $ci;
		$this->crm = $ci->db;
	}

	function get_supplier_contacts($supplier_id=0,$action='order'){
		$data = $this->get_persons('supplier', $action, $supplier_id);
		if( !$data || count($data) <1 ){
			$data =  $this->get_persons('supplier', 'general', $supplier_id);
		}

		if( !$data || count($data) <1 ){
		    $data =  $this->get_persons('supplier', null, $supplier_id);
		}

		return $data;



		//return $this->get_persons('supplier', $action, $supplier_id);
	}

	function get_customer_contact($supplier_id=0,$action='order'){
		$data = $this->get_customer_contacts($supplier_id,$action);
		if( is_array($data) ){
			return $data[0];
		} else {
			return array();
		}

	}

	function get_customer_contacts($supplier_id=0,$action='order'){
		$data = $this->get_persons('customer', $action, $supplier_id);
		if( !$data || count($data) <1 ){
			return $this->get_persons('customer', 'general', $supplier_id);
		} else {
			return $data;
		}

	}

	function get_branch_contacts($branch_code,$action='invoice',$debtor_no,$default=true){
		$defs = array('cust_branch.'.$action, 'customer.'.$action, 'cust_branch.general','customer.general');

		$this->crm->select("p.*, r.action, r.type, CONCAT(r.type,'.',r.action) as ext_type",false);
		$this->crm->from('crm_persons AS p, crm_contacts AS r');
		$this->crm->where('r.person_id=','p.id',false);

		$where_cust = "( r.type='cust_branch' AND r.entity_id=".db_escape($branch_code).")";


		if( $debtor_no ){
			$where_cust .= " OR ( r.type='customer' AND r.entity_id =".db_escape($debtor_no) .")";
		}
		$this->crm->where("( $where_cust ) ");

		if( $action ){
			$where_action = "r.action=".db_escape($action)."";
			if( $default ){
				$where_action .= " OR r.action='general'";
// 				$this->crm->or_where("r.action","'general'",false);
			}
			$this->crm->where("( $where_action ) ");

		}
		$data = $this->crm->get()->result_array();
		if ($data && $default) {
			foreach($defs as $type) {
				if ($n = array_search_value($type, $data, 'ext_type')) return $n;
			}
			return null;
		}
		return $data;
	}

	function get_persons($type=null, $action=null, $entity=null, $person=null, $unique=false){
		$this->crm->select('t.*, p.*, r.id as contact_id');
		$this->crm->from('crm_persons p, crm_categories t');
// 		$this->crm->join('crm_categories t', '', 'left');
		$this->crm->join('crm_contacts r', 'r.type=t.type AND r.action=t.action AND r.person_id=p.id', 'left');

		if( $type ){
			$this->crm->where('t.type',$type);
		}
		if( $action ){
			$this->crm->where('t.action',$action);
		}

		if( $entity ){
			$this->crm->where('r.entity_id',$entity);
		}
		if( $person ){
			$this->crm->where('r.person_id',$person);
		}

		if( $unique ){
			$this->crm->group_by('person_id');
		} else {
			$this->crm->group_by('contact_id');
		}

		$data = $this->crm->get()->result();
// bug($this->crm->last_query());


		return $data;
	}

	function get_salesman($id=0,$select='*'){
		if( !$select || $select =='' ){
			$select = '*';
		}
		$this->crm->select($select);
		$data = $this->crm->where('salesman_code',$id)->get('salesman')->row();
		if ( !empty($data) ){
			if( $select!='*' && count($data) ==1 ){
				return $data->$select;
			}
			return $data;
		}
		return null;
	}

        function get_crm_persons($type=null, $action=null, $entity=null, $person=null, $unique=false)
        {
            $select="t.*,p.*, r.id as contact_id";
            $this->crm->select($select);
            $this->crm->join('crm_contacts as r ', 'r.person_id=p.id', 'left');
            $this->crm->join('crm_categories as t ', 'r.type=t.type and r.action=t.action', 'left');
           if( $type ) {$this->crm->where('t.type',$type);}
           if( $action ) {$this->crm->where('t.action',$action);}
           if( $entity ) {$this->crm->where('r.entity_id',$entity);}
           if( $person ) {$this->crm->where('r.person_id',$person);}
           if( $unique ) {$this->crm->group_by('person_id',$unique);}
           else{$this->crm->group_by('person_id','contact_id');}


           $data = $this->crm->get('crm_persons AS p')->row();
           //bug( $this->crm->last_query());die;
           // bug( $data);die;
            return $data;

        }
}