<?php
class Gl_trans_Model {
	function __construct(){
		global $ci;
		$this->trans = $ci->db;
	}

	function count_gl_trans($trans_types=array()){
        $this->trans->select('count(counter) AS total');
        if( $trans_types ){
            if( !is_array($trans_types) ){
                $trans_types = explode(',', $trans_types);
                $this->trans->where_in('type',$trans_types);
            }
        }
        $data = $this->trans->get('gl_trans')->row();
        if( $data && $data->total > 0){
            return $data->total;
        }
        return false;
	}

	function items_trans($trans_id,$type=1){
		$this->trans->select("gl.*, cm.account_name, IF(ISNULL(refs.reference), '', refs.reference) AS reference",false);
		$this->trans->from('gl_trans AS gl');
		$this->trans->join('chart_master AS cm', 'gl.account = cm.account_code', 'left');
		$this->trans->join('refs as refs', 'gl.type=refs.type AND gl.type_no=refs.id', 'left');
		$this->trans->where('gl.type',$type);
		$this->trans->where('gl.type_no',$trans_id);
		$this->trans->where('gl.amount <>',0);
		$data = $this->trans->order_by('counter', 'ASC')->get()->result();

		return $data;
	}

	function items($where=null){
		if( empty($where) ){
			return false;
		}

		$select = "bt.tran_date AS trans_date, bt.type_no AS trans_no, IF(ISNULL(refs.reference), '', refs.reference) AS ref,null AS bank_account_name";

		$this->trans->select($select,false);
		$this->trans->from('gl_trans AS bt');
		$this->trans->join('chart_master AS cm', 'bt.account = cm.account_code', 'left');
		$this->trans->join('refs as refs', 'bt.type=refs.type AND bt.type_no=refs.id', 'left');
		$this->trans->where($where);
		$this->trans->where('bt.amount <>',0);
		$data = $this->trans->order_by('counter', 'ASC')->group_by('type_no')->get()->result();

// 		bug( $this->trans->last_query() );
		return $data;
	}



	function gl_trans_customer($data=null){
		$gl_trans = array(
			'type'=>'',
			'type_no'=>0,
			'tran_date'=>'',
			'amount'=>0,
			'person_id'=>1,
			'account'=>0,
			'memo_'=>'',
			'openning'=>'',
			'gst'=>0

		);
		//bug($data);
		if( !is_array($data) ){
			return false;
		}


		foreach ($data AS $key=>$val){
			if( array_key_exists($key,$gl_trans) ){
				$gl_trans[$key] = $val;
			}
		}

		if( isset($gl_trans['tran_date']) ){
			$gl_trans['tran_date'] = date('Y-m-d',strtotime($gl_trans['tran_date']) );
		}

		if( $gl_trans['account'] ){
			$this->trans->insert('gl_trans',$gl_trans);
// 			bug($this->trans->last_query());
			return $data['amount'];
		}
		return 0;
	}

	function void($openning_key){
		$this->trans->where('openning',$openning_key)->update('gl_trans',array('amount'=>0));
		//bug( $this->trans->last_query() );
	}

	function gl_balance($data=null){
		$gl_trans = array(
			'type'=>'',
			'type_no'=>'',
			'tran_date'=>date('d-m-Y'),
			'amount'=>0,
			'person_type_id'=>PT_CUSTOMER,
			'person_id'=>0,
			'memo_'=>null,

		);
		if( !is_array($data) ){
			return false;
		}


		foreach ($data AS $key=>$val){
			if( array_key_exists($key,$gl_trans) ){
				$gl_trans[$key] = $val;
			}
		}

// 		$gl_trans['person_type_id'] = PT_CUSTOMER;
		$data['account'] = get_company_pref('exchange_diff_act');
		if( isset($data['tran_date']) ){
			$data['tran_date'] = date('Y-m-d',strtotime($data['tran_date']) );
		}
		$this->trans->insert('gl_trans',$data);
		return $data['amount'];
// 		bug($gl_trans);die('gl blance');
	}


	function add_debtor_trans($data=null){
		bug($data);
		die('add debtor trans');
		//$this->trans->insert('debtor_trans',$data);
		//bug( $this->trans->last_query() );
		die('go here');

	}

	/*
	 * audit
	 */
	function add_audit_trail($data){
		$this->trans->insert('audit_trail',$data);
// 		die('go here');
	}

	/*
	 * update query old function
	 */

	function add_gl_trans($data=null,$currency=null){
		global $use_audit_trail;

		if( !$data['account'] ) return 0;

		$data['tran_date'] = date2sql($data['tran_date']);

		if( array_key_exists('rate', $data) && $data['rate'] >0 ){
		    $data['amount'] = round2($data['amount']*$data['rate'], user_price_dec());
		} else if ($currency != null ){

			$data['amount'] = to_home_currency($data['amount'], $currency, $data['tran_date']);
		} else {
			$data['amount'] = round2($data['amount'], user_price_dec());
		}

		if( abs($data['amount']) <= 0){
		    return 0;
		}
		if( array_key_exists('rate', $data) ){
		    unset($data['rate']);
		}

		if ( $data['dimension_id'] == null || $data['dimension_id'] < 0)
			$data['dimension_id'] = 0;
		if ( $data['dimension2_id'] == null || $data['dimension2_id'] < 0)
			$data['dimension2_id'] = 0;

		if (isset($use_audit_trail) && $use_audit_trail){
			if ( $data['memo_'] == "" || $data['memo_'] == null)
				$data['memo_'] = $_SESSION["wa_current_user"]->username;
			else
				$data['memo_'] = $_SESSION["wa_current_user"]->username . " - " . $data['memo_'];
		}
		$this->trans->reset();
		$sql = $this->trans->insert('gl_trans',$data,true );

		db_query($sql, "The GL transaction could not be inserted");
		return  $data['amount'];

	}
	function add_tax_trans_detail($data){

	    if ( !array_key_exists('trans_type', $data) || !array_key_exists('trans_no', $data) ){
	        display_error(_("DB has error!"));
	    }

	    $where = array('trans_type'=>$data['trans_type'],'trans_no'=>$data['trans_no']);
	    $this->trans->reset();
        $existed = $this->trans->where($where)->get('trans_tax_details')->row();
        $this->trans->reset();
        if( $existed && isset($existed->trans_no) ){
            $sql = $this->trans->update('trans_tax_details',$where,$data,1,true );
//             bug($sql);die('test query');
        } else {

            $sql = $this->trans->insert('trans_tax_details',$data,true );
        }



		db_query($sql, "Cannot save trans tax details");

	}

	function get_gl_trans($trans_type, $trans_no) {
        $this->trans->select("gl.* , cm.account_name, IF(ISNULL(refs.reference), '', refs.reference) AS reference",false);
        $this->trans->join('chart_master as cm','cm.account_code=gl.account');
        $this->trans->join('refs as refs','refs.type=gl.type AND refs.id=gl.type_no');

        $this->trans->where( array('gl.type'=>$trans_type,'gl.type_no'=>$trans_no,'gl.amount <>'=>0) );

        $data = $this->trans->get('gl_trans as gl')->result();
        return $data;
	}

	function search_transaction($trans_type, $trans_no,$where=null){
	    $this->trans->where( array('gl.type'=>$trans_type,'gl.type_no'=>$trans_no,'gl.amount <>'=>0) );
	    if( $where ){
	        $this->trans->where($where);
	    }
	    $data = $this->trans->get('gl_trans as gl')->result();

	    return $data;
	}
}