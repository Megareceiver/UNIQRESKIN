<?php
class Gl_Trans_Model extends CI_Model {
    function __construct(){
        parent::__construct();
        $this->void_model = module_model_load('tran','void');
    }

    function add_gl_trans($data=null,$currency=null,$return_sql=false){
        global $use_audit_trail;

        if( !$data['account'] )
            return false;

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

        $this->db->reset();
        if( !$return_sql ){
            $sql = $this->db->insert('gl_trans',$data,true );
            db_query($sql, "The GL transaction could not be inserted");
        } else {
            $this->db->insert('gl_trans',$data);
        }

        return  $data['amount'];

    }

    function add_tax_trans_detail($data){

        if ( !array_key_exists('trans_type', $data) || !array_key_exists('trans_no', $data) ){
            display_error(_("DB has error!"));
        }

        $where = array('trans_type'=>$data['trans_type'],'trans_no'=>$data['trans_no']);
        $this->db->reset();
        $existed = $this->db->where($where)->get('trans_tax_details')->row();
        $this->db->reset();
        if( $existed && isset($existed->trans_no) ){
            $sql = $this->db->update('trans_tax_details',$where,$data,1,true );
        } else {
            $sql = $this->db->insert('trans_tax_details',$data,true );
        }
        db_query($sql, "Cannot save trans tax details");

    }

    function get_gl_trans_from_to($date_from=NULL, $date_to=NULL, $account, $dimension=0, $dimension2=0, $person_type_id=0,$person_id=0) {
//         $from = date2sql($date_from);
//         $to = date2sql($date_to);

        $this->db->select('SUM(gl.amount) AS total',false)->from('gl_trans AS gl');
        $this->db->where('gl.account',$account);

        if( is_date($date_from) ){
            $this->db->where('gl.tran_date >=',date2sql($date_from));
            if( is_date($date_to) ){
                $this->db->where('gl.tran_date <=',date2sql($date_to));
            }
        } else {
            if( is_date($date_to) ){
                $this->db->where('gl.tran_date <',date2sql($date_to));
            }
        }

        if ($dimension != 0){
            $this->db->where('gl.dimension_id',$dimension<0?0:db_escape($dimension) );
        }
        if ($dimension2 != 0){
            $this->db->where('gl.dimension2_id',$dimension2<0?0:db_escape($dimension2));
        }

        if( $person_type_id ){
            $this->db->where('gl.person_type_id',$person_type_id);
        }
        if( $person_id ){
            $this->db->where('gl.person_id',$person_id);
        }

        $this->void_model->not_voided('gl.type','gl.type_no');

        $result = $this->db->get();
        if( is_object($result) ){

            $data  = $result->row();
            return $data->total;
        }


    }

    function get_transactions($from_date, $to_date, $trans_no=0,
		$account=null, $dimension=0, $dimension2=0, $filter_type=null,
		$amount_min=null, $amount_max=null,$voied=false){


        $this->db->select('gl.*')->from('gl_trans AS gl');
        $this->db->where('gl.amount <>',0);
        if( !$voied ){
            $this->void_model->not_voided('gl.type','gl.type_no');
        } else {
            $this->void_model->voided('gl.type','gl.type_no');
        }

//         $this->void_model->voided('gl.type','gl.type_no');


        if ($account != null){
            $this->db->where('gl.account',$account);
        }
        $this->db->select("IF(gl.amount >0,gl.amount,0) AS debit",false);
        $this->db->select("IF(gl.amount <0,-gl.amount,0) AS credit",false);

        if ($trans_no > 0){
//             $sql .= " AND ".TB_PREF."gl_trans.type_no LIKE ".db_escape('%'.$trans_no);
            $this->db->like('gl.type_no',$trans_no);
        }

        if( is_date($from_date) ){
            $this->db->where('gl.tran_date >=',date2sql($from_date));
        }
        if( is_date($to_date) ){
            $this->db->where('gl.tran_date <=',date2sql($to_date));
        }

        $this->db->left_join('chart_master AS chart',"chart.account_code=gl.account")->select("chart.account_name");

        if ($dimension != 0){
            $this->db->where('gl.dimension_id', $dimension<0 ? 0: $dimension );
        }
        if ($dimension2 != 0) {
            $this->db->where('gl.dimension2_id', $dimension2<0 ? 0: $dimension2 );
        }
        if ($filter_type != null AND is_numeric($filter_type)) {
            $this->db->where('gl.type', $filter_type);
        }
        if ($amount_min != null) {
            $this->db->where('ABS(gl.amount) >=', abs($amount_min) );
        }
        if ($amount_max != null) {
            $this->db->where('ABS(gl.amount) <=', abs($amount_max) );
        }
//         $this->db->group_by('gl.counter');

        // 	$sql .= " AND gl_trans.type NOT IN (".ST_OPENING_CUSTOMER.",".ST_OPENING_SUPPLIER.")";
//         $this->db->group_by('gl.type_no,gl.type');
//         $this->db->where('gl.type',ST_SUPPAYMENT);
//         $this->db->where(array('gl.type'=>22,'gl.type_no'=>86));
//         $this->db->select("SUM(gl.amount)",false)->group_by('gl.type');
        $data = $this->db->order_by("gl.tran_date, gl.counter")->get();


        if( !is_object($data) ){
            display_error('The transactions for could not be retrieved');
            return false;
        } else {

//             bug($data->result());
//             bug($this->db->last_query());die;

            return $data->result();
        }
    }

    function get_balance($account, $dimension, $dimension2, $from, $to, $from_incl=true, $to_incl=true) {
        $this->db->select('SUM(IF(gl.amount >= 0, gl.amount, 0)) as debit',false);
        $this->db->select('SUM(IF(gl.amount < 0, -gl.amount, 0)) as credit',false);
        $this->db->select('SUM(gl.amount) as balance',false);
        $this->db->from('gl_trans AS gl');

        $this->db->left_join('chart_master AS chart',"chart.account_code=gl.account");
        $this->db->left_join('chart_types AS ctype',"ctype.id=chart.account_type");
        $this->db->left_join('chart_class AS clas',"clas.cid=ctype.class_id");


        $this->db->where('gl.amount <>',0);
        $this->void_model->not_voided('gl.type','gl.type_no');

        if ($account != null) {
//             $sql .= " account=".db_escape($account)." AND";
            $this->db->where('gl.account',$account);
        }
        if ($dimension != 0){
            $this->db->where('gl.dimension_id', $dimension<0 ? 0: $dimension );
        }
        if ($dimension2 != 0) {
            $this->db->where('gl.dimension2_id', $dimension2<0 ? 0: $dimension2 );
        }


        if ($from_incl) {
            $this->db->where('gl.tran_date >=',date2sql($from));
//             $sql .= " tran_date >= '$from_date'  AND";
        }
        else {
//             $sql .= " tran_date > IF(ctype>0 AND ctype<".CL_INCOME.", '0000-00-00', '$from_date') AND";
            $this->db->where("gl.tran_date > IF(ctype>0 AND ctype<".CL_INCOME.", '0000-00-00', '".date2sql($from)."')");
        }

//         $to_date = date2sql($to);
        if ($to_incl) {
//             $sql .= " tran_date <= '$to_date' ";
            $this->db->where('gl.tran_date <=',date2sql($to));
        }
        else {
//             $sql .= " tran_date < '$to_date' ";
            $this->db->where('gl.tran_date <',date2sql($to));
        }

//         $this->db->where_not_in('gl.type',array(95,96));
//         $sql .= ' AND gl_trans.type NOT IN (95,96)';
        $data = $this->db->get();

//         if( $account==1060 ){
//             bug($this->db->last_query());
//         }
        if( !is_object($data) ){
            display_error('No general ledger accounts were returned');
            return false;
        } else {
            $row = $data->row_array();
            $default = array('debit'=>0,'credit'=>0,'balance'=>0);
            return array_merge($default,$row);
        }

    }


    function get_gl_trans($trans_type, $trans_no) {
        $this->db->select("gl.* , cm.account_name, IF(ISNULL(refs.reference), '', refs.reference) AS reference",false);
        $this->db->join('chart_master as cm','cm.account_code=gl.account','LEFT');
        $this->db->join('refs as refs','refs.type=gl.type AND refs.id=gl.type_no','LEFT');

        $this->db->where( array('gl.type'=>$trans_type,'gl.type_no'=>$trans_no,'gl.amount <>'=>0) );

        $data = $this->db->get('gl_trans as gl')->result();
        return $data;
    }

    function void($openning_key){
        $this->db->where('openning',$openning_key)->update('gl_trans',array('amount'=>0));
    }

    /*
     * audit
     */
    function add_audit_trail($data){
        $this->db->insert('audit_trail',$data);
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
            $this->db->insert('gl_trans',$gl_trans);
            // 			bug($this->trans->last_query());
            return $data['amount'];
        }
        return 0;
    }


}