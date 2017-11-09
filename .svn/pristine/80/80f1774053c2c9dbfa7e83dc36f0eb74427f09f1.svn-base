<?php
class Gl_GL_Model extends CI_Model {
	function __construct(){
		parent::__construct();
	}

	function get_trans($tran_no=0,$tran_type=ST_JOURNAL,$where=NULL){

	    $this->db->from('gl_trans AS gl')->select('gl.*, SUM(DISTINCT  gl.amount) AS amount');

	    $this->db->join('chart_master AS cm','gl.account = cm.account_code','left')->select('cm.account_name');

	    $this->db->join('refs AS refs','refs.type=gl.type AND refs.id=gl.type_no','left');
	    $this->db->select("IF(ISNULL(refs.reference), '', refs.reference) AS reference",false);

	    $this->db->where('gl.amount !=',0);

	    if( $tran_no ){
	        $this->db->where("( (gl.type= $tran_type AND gl.type_no = $tran_no ) OR (gl.type= ".ST_BADDEB." AND gl.type_no IN (SELECT id FROM bad_debts WHERE type=$tran_type AND type_no=$tran_no)) )");
	    } else {
	        $this->db->where("( (gl.type= $tran_type ) OR (gl.type= ".ST_BADDEB." AND gl.type_no IN (SELECT id FROM bad_debts WHERE type=$tran_type)) )");
	    }

	    if( $where ){
	        $this->db->where($where);
	    }
	    $this->db->order_by('gl.counter');
		$trans = $this->db->group_by('gl.account')->get()->result();
// 		bug($where);
// 		bug($this->db->last_query());

		return $trans;
	}
}