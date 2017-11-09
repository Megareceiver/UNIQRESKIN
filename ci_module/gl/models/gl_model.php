<?php
class Gl_GL_Model extends CI_Model {
	function __construct(){
		parent::__construct();
		$this->void_model = module_model_load('tran','void');
		$this->allocation_model = module_model_load('allocation','gl');
// 		bug($this->void_model);die;
	}

	function get_trans($tran_no=0,$tran_type=ST_JOURNAL,$where=NULL){

	    $this->db->select('gl.*');

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
// 		$trans = $this->db->group_by('gl.account')->get('gl_trans AS gl')->result();
		$trans = $this->db->get('gl_trans AS gl')->result();
// 		bug($where);
// 		bug($this->db->last_query());

		return $trans;
	}

	function get_details($account=0,$tran_type=NULL){
	    $this->db->select('SUM(gl.amount) as balance, gl.type');
	    $this->db->where('gl.account',$account);
	    $this->void_model->not_voided('gl.type','gl.type_no');

	    switch ($tran_type){
	        case ST_SALESINVOICE:
	            $this->db->where('gl.type',$tran_type);
// 	            $this->db->join('debtor_trans AS cus','cus.type=gl.type AND cus.trans_no=gl.type_no','left')->where('debtor_no IS NOT NULL');
	            break;
            default:break;
	    }

	    $result = $this->db->group_by('gl.type')->order_by('gl.type ASC')->get('gl_trans AS gl');


        if( is_object($result) ){
            $data = $result->row();
            return $data->balance;
        }
        die('error');
	}

	function get_sale_trans($account=0,$debtor_no=0,$not_in_debtor=array()){
        $this->db->select('gl.*')->from('gl_trans AS gl');
        $this->db->where('gl.account',$account);
        $this->db->join('debtor_trans AS debtor','debtor.type=gl.type AND debtor.trans_no=gl.type_no');
        if( is_array($not_in_debtor) && !empty($not_in_debtor) ){
            $this->db->where_not_in('debtor.debtor_no',$not_in_debtor);
        }

        $result = $this->db->get();
        return $result->result();
	}

	function opening($account=0,$type=null,$date=NULl){
        $this->db->select("SUM(amount) AS amount",false);
        $this->db->where('tran_date <',date2sql($date));
        if( $account ){
            $this->db->where('account',$account);
        }
        if( $type != null ){
            $this->db->where('type',$type);
        }
        $result = $this->db->get('gl_trans');
        if( is_object($result) ){
            if( $account ){
                $data = $result->row();
                return $data->amount;
            }
            return $result->result();
        }


	}


	function get_receivable($account=0,$debtor_no=0,$date=false,$show_ob=true,$sum_total=false){
	    $date = date2sql($date);

	    $alloc = $this->allocation_model->alloc_sum('trans.trans_no','trans.type', $date, 'alloc_',array(ST_SALESINVOICE,ST_OPENING_CUSTOMER));
// 	    $tran_amount = '(IF(trans.type='.ST_OPENING_CUSTOMER.',0,trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount))';
	    $tran_amount = '(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount)';

	    $credit_or_debit = "IF(trans.type=".ST_CUSTCREDIT." OR trans.type=".ST_CUSTPAYMENT." OR trans.type= ".ST_BANKDEPOSIT.", -1, 1)";
        $sum_gl_str = "SELECT IF(gl.type=".ST_OPENING_CUSTOMER.",0,SUM(gl.amount)) "
                        ."FROM gl_trans AS gl "
                        ."WHERE gl.type=trans.type AND gl.type_no=trans.trans_no AND gl.account=$account "
                        ." AND gl.type_no NOT IN ( SELECT voided.id FROM voided WHERE voided.type=gl.type )";

	    if( $sum_total ){
	        $this->db->select("SUM(($sum_gl_str)) AS gl_amount",false);
	        $this->db->select("SUM(($credit_or_debit)*$tran_amount) AS tran_amount",false);
	        $this->db->select("SUM(($alloc)*$credit_or_debit) AS tran_alloc",false);
// 	        $this->db->group_by('trans.type, trans.trans_no');
	    } else {
	        $this->db->select("($sum_gl_str) AS gl_amount",false);
	        $this->db->select("($credit_or_debit)*$tran_amount AS tran_amount",false);
	        $this->db->select("($alloc)*$credit_or_debit AS tran_alloc",false);
	        //         $this->db->select("IFNULL(($invoice_alloc_sum),0) AS invoice_alloc",false);
	        //         $this->db->select("IFNULL(($payment_alloc_sum),0) AS payment_alloc",false);
	        //         $allocate_tran = "IF(trans.type=".ST_CUSTPAYMENT." OR trans.type=".ST_CUSTCREDIT.",($payment_alloc_sum)*(-1),($invoice_alloc_sum))";
	        //         $allocate_tran = "IF(trans.type=".ST_OPENING_CUSTOMER.",0,($allocate_tran))";

	        $this->db->select('trans.tran_date, trans.type, trans.trans_no, trans.reference');
	    }




        $this->db->where("trans.tran_date <=",date2sql($date));
        $this->db->where('trans.type <>',ST_CUSTDELIVERY);
        $this->db->where('trans.type <>',ST_BANKPAYMENT);

//         if( is_array($show_ob) ){
//             $this->db->where('trans.type',ST_OPENING_CUSTOMER);
//         }elseif( !$show_ob ){
//             $this->db->where('trans.type <>',ST_OPENING_CUSTOMER);
//         }

        $this->db->join('debtors_master AS master',"master.debtor_no = trans.debtor_no",'LEFT');
//         $this->db->select('master.name AS debtor')->order_by('master.name ASC');
        if( $debtor_no ){
            $this->db->where('trans.debtor_no',$debtor_no);
        }
	    $this->void_model->not_voided('trans.type','trans.trans_no');
	    $result = $this->db->order_by('trans.tran_date ASC')->get('debtor_trans AS trans');


	    if( is_object($result) ){
	        return $sum_total ? $result->row() : $result->result();
	    } else {
	        bug($this->db->last_query()); die;
	    }
	}
}