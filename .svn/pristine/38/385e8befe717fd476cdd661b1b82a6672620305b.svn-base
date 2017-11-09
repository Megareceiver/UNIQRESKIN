<?php
class Fiscalyear_Model {
	function __construct(){
		global $ci;
		$this->db = $ci->db;
	}

	function item($id=0){
	    $this->db->reset();
	    return $data = $this->db->where('id',$id)->get('fiscal_year')->row();
	}


	function check_is_last($id=0){
	    $taget = $this->item($id);
	    $this->db->reset();

	    $prev = $this->db->where('begin >=',$taget->end)->get('fiscal_year')->row();

// 	    return $this->db->last_query();

	    return ( isset($prev->begin) ) ? false : true;
	}

	function trans_in_year($id=0){
        $year = $this->item($id);
        $count = 0;

        if( empty($year) || !isset($year->begin) ) return $count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('ord_date >='=>$year->begin,'ord_date <='=>$year->end))->get('sales_orders')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('ord_date >='=>$year->begin,'ord_date <='=>$year->end))->get('purch_orders')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('delivery_date >='=>$year->begin,'delivery_date <='=>$year->end))->get('grn_batch')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end))->get('debtor_trans')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end))->get('supp_trans')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('released_date >='=>$year->begin,'released_date <='=>''))->get('workorders')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end))->get('stock_moves')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end))->get('gl_trans')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('trans_date >='=>$year->begin,'trans_date <='=>$year->end))->get('bank_trans')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('gl_date >='=>$year->begin,'gl_date <='=>$year->end))->get('audit_trail')->row();
        $count += $data->count;

        return $count;

	}
}