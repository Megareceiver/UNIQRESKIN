<?php
class Cutomer_Model {
	function __construct(){
		global $ci;
		$this->debtor = $ci->db;
	}

	function item_options(){
		$options = array();
		$data = $this->debtor->select('debtor_no AS id,name,curr_code')->order_by('name', 'ASC')->get('debtors_master')->result();
		if( $data ){
			foreach ($data AS $row){
				$options[$row->id] = $row->name.' - '.$row->curr_code;
			}
		}
		return $options;
	}

	function branch_options($debtor_no=0){
		$options = array();
		$data = $this->debtor->select('branch_code AS id,branch_ref AS name')->where('debtor_no',$debtor_no)->order_by('name', 'ASC')->get('cust_branch')->result();

		if( $data ){
			foreach ($data AS $row){
				$options[$row->id] = $row->name;
			}
		}
		return $options;
	}

	function get_row($debtor_no=0){
		return $this->debtor->where('debtor_no',$debtor_no)->get('debtors_master')->row();
	}

	function get_branch_row($branch_code=0){
		return $this->debtor->where('branch_code',$branch_code)->get('cust_branch')->row();
	}

	function customer_detail($debtor_no=0,$field=null){
		$data = $this->debtor->select('*')->where('debtor_no',$debtor_no)->get('debtors_master')->row();

		if($field && isset($data->$field)){
			return $data->$field;
		} else {
			return $data;
		}
	}

	function bab_deb_load($date_current='',$days_left=1,$debtor_no=null,$page=1){
        if($page > 0){
	        $page --;
	    } else {
	        $page = 0;
	    }
	    if( is_numeric($debtor_no) ){
	        $this->debtor->where('trans.debtor_no',$debtor_no);
	    }
	    if( is_date($date_current) ){
	        $date_current = strtotime($date_current);
	        if( is_numeric($days_left) && $days_left > 0 ){
	            $date = $date_current-$days_left*60*60*24;
	        }
                // bug(date('Y/m/d',$date));die;
	        if( $date > 0 ){
	            $this->debtor->where('trans.tran_date <=',date('Y/m/d',$date));
	        }
	    }
	    $this->debtor->where( array('trans.ov_amount >'=>0,'trans.type'=>ST_SALESINVOICE));
	    $this->debtor->join('debtors_master AS deb','deb.debtor_no = trans.debtor_no','left');

	    $this->debtor->select('trans.*,deb.name AS debtor_name, deb.curr_code')->from('debtor_trans AS trans');
	    $this->debtor->where("( SELECT count(*) FROM gl_trans AS gl WHERE gl.type_no= trans.trans_no AND gl.type= trans.type AND gl.openning ='') >",0);
	    $this->debtor->select("( SELECT id FROM bad_debts AS baddeb WHERE baddeb.type_no= trans.trans_no AND baddeb.type= trans.type AND step=1) AS process");
	    $this->debtor->select("( SELECT id FROM bad_debts AS baddeb WHERE baddeb.type_no= trans.trans_no AND baddeb.type= trans.type AND step=2) AS paid");

	    $tempdb = clone $this->debtor;
	    $data = $this->debtor->limit(page_padding_limit,$page*page_padding_limit)->get()->result();
// 	    bug($this->debtor->last_query());die;
        return array('items'=>$data,'total'=>$tempdb->count_all_results());
	}

	function bab_deb_6month_find(){
	    $this->debtor->where( array('trans.ov_amount >'=>0,'trans.type'=>ST_SALESINVOICE));
	    $today = new DateTime();
	    $day = $today->modify('-6 month');
	    $this->debtor->where('trans.tran_date <=',$today->format('Y/m/d'));
	    $this->debtor->join('debtors_master AS deb','deb.debtor_no = trans.debtor_no','left');
        $this->debtor->where('trans.trans_no NOT IN (SELECT type_no FROM bad_debts AS deb where deb.type= trans.type)');
	    $this->debtor->select('trans.*,deb.name AS debtor_name, deb.curr_code')->from('debtor_trans AS trans');
	    $this->debtor->where("( SELECT count(*) FROM gl_trans AS gl WHERE gl.type_no= trans.trans_no AND gl.type= trans.type AND gl.openning ='') >",0);
	    $count = $this->debtor->count_all_results();

	    return $count;
	}

}