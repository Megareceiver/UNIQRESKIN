<?php
class Opening_Gl_Model extends CI_Model {
    function __construct(){
//         parent::__construct();
    }
    function chart_master(){
        $data = array();
    
        $this->db->from("chart_master chart")->select("chart.account_code, chart.account_name, chart.inactive");
        $this->db->join("chart_types type","chart.account_type=type.id","LEFT")->select("type.name, , type.id");
        $this->db->join("bank_accounts acc","chart.account_code=acc.account_code","LEFT");
        $this->db->where("chart.inactive",0);
        
        $query = $this->db->get();
        bug($query);die;
    		                /* acc.account_code  IS NULL AND  */
    
//     		                if($result = db_query($sql)) {
//     		                    while ($row = db_fetch($result)) {
//     		                        if( !isset($data[ $row['name'] ]) ){
//     		                            $data[ $row['name'] ] = array();
//     		                        }
//     		                        $data[ $row['name'] ][ $row['account_code'] ] = $row['account_name'];
//     		                    }
    
//     		                }
    
//     		                return $data;
    }
    
    function openning_group(){
        $this->db->select('*, cl.class_name AS classname');
        $this->db->join('chart_class cl', 'cl.cid = type.class_id', 'left');
    
        $this->db->where_in('cl.ctype',array(CL_ASSETS,CL_LIABILITIES,CL_EQUITY));
    
        $data = $this->db->get('chart_types AS type')->result();
    
        return $data;
    }
    
    function accounts(){
        $groups_limit = $this->openning_group();
        $groups = array();
        if( $groups_limit && !empty($groups_limit) ){
            foreach ($groups_limit AS $gr){
                $groups[] = $gr->id;
            }
        }
    
        $this->db->select('chart.account_code, chart.account_name, type.name, chart.inactive, type.id');
        $this->db->from('chart_master chart');
    
        $this->db->join('chart_types type', 'chart.account_type=type.id', 'left');
        $this->db->join('bank_accounts acc', 'chart.account_code=acc.account_code', 'left');
        $this->db->where('chart.inactive',0);
        if( !empty($groups) ){
            $this->db->where_in('chart.account_type',$groups);
        }
    
        $data = $this->db->get()->result();
        $result = array();
    
        if( $data ) { foreach ($data AS $it){
            if( !isset($result[ $it->name ]) ){
                $result[$it->name] = array();
            }
            $debit = $credit = 0;
    
            $openning_gl_debit = $this->db->where(array('account'=>$it->account_code,'pay_type'=>'debit'))->get('opening_gl')->row();
    
            if( isset($openning_gl_debit->amount) ){
                $debit = $openning_gl_debit->amount;
            }
    
            $openning_gl_credit = $this->db->where(array('account'=>$it->account_code,'pay_type'=>'credit'))->get('opening_gl')->row();
            if( isset($openning_gl_credit->amount) ){
                $credit = $openning_gl_credit->amount;
            }
    
            $result[$it->name][ $it->account_code] = array('name'=>$it->account_code.' - '.$it->account_name,'debit'=>abs($debit),'credit'=>abs($credit) );
        }}
        return $result;
    }
    
    function update_gl_account($data,$pay_type='create'){
        if( !isset($data['account']) ){
            return false;
        }
    

        $openning_gl = $this->db->where(array('account'=>$data['account'],'pay_type'=>$pay_type))->get('opening_gl')->row();
    
        $data['amount'] = floatval($data['amount']);
        $openning_add = $data;
        $openning_add['pay_type'] = $pay_type;
    
        if( $openning_gl && isset($openning_gl->id)  ){
            $openning_add['tran_date'] = date2sql($data['tran_date']);
            $this->db->insert('opening_cache',array('data'=>json_encode($openning_gl) ));
            $this->db->where(array('id'=>$openning_gl->id,'pay_type'=>$pay_type))->update('opening_gl',$openning_add);
            $gl_trans_id = $openning_gl->gl_tran_id;
            $openning_id = $openning_gl->id;
        } else {
    
            $this->db->insert('opening_gl',$openning_add);
            $openning_id = $this->db->insert_id();
            $gl_trans_id = null;
        }

        $data['type'] = ST_OPENING_GL;
        $data['type_no'] = 0;
    
        $gl_tran_exist = $this->db->where('counter',$gl_trans_id)->get('gl_trans')->row();
        $this->db->reset();
    
        if( $gl_trans_id && $gl_tran_exist && isset($gl_tran_exist->counter) ){
            $this->db->where('counter',$openning_gl->gl_tran_id)->update('gl_trans',$data);
    
        } else {
            $this->db->insert('gl_trans',$data);
            $gl_trans_id = $this->db->insert_id();
            $this->db->where(array('id'=>$openning_id,'pay_type'=>$pay_type))->update('opening_gl',array('gl_tran_id'=>$gl_trans_id));
        }
    
    }
}