<?php
class BankTrans_Model extends CI_Model {
    function __construct(){
        parent::__construct();

    }

    function load_deposit_refundable($trans_no=array()){
        $this->db->reset();
        $bank_allocation_sql = $this->db->select('trans_no_from')->where('trans_type_from',ST_CUSTPAYMENT)->get('cust_allocations',null,null,true);

        $this->db->reset();
        $this->db->select('trans.trans_no, trans.ref AS reference,trans.amount, trans.not_refundable_tax');

        $this->db->where('trans.not_refundable',1);



//         $this->db->where_not_in('trans.trans_no',$bank_allocation_sql);

        $this->db->join('bank_accounts AS act','act.id=trans.bank_act','left');
        $this->db->select('act.bank_account_name, act.bank_curr_code');

        $this->db->join('debtors_master AS debtor','debtor.debtor_no=trans.person_id','left');
        $this->db->select('debtor.name AS debtor_name, debtor.curr_code AS debtor_curr');

        $data_query = $this->db->get('bank_trans AS trans',null,null,true);//->result();


        if( !$trans_no || empty($trans_no) ){
            $data_query .= " AND trans.trans_no NOT IN ($bank_allocation_sql)";
        } else if ( is_array($trans_no)){
            $data_query .= " AND trans.trans_no IN (".implode(',', $trans_no).")";
        }

        $this->db->reset();

        return $this->db->query($data_query)->result();
    }

    function total_refundable($trans_no=array()){
        if( !$trans_no OR empty($trans_no) ){
            return 0;
        }

        $this->db->select('SUM(trans.amount) AS total');
        $this->db->where('trans.not_refundable',1)->where_in('trans.trans_no',$trans_no);
        $data = $this->db->get('bank_trans AS trans')->row();

        return ( $data AND isset($data->total) ) ? $data->total : 0;
    }


    function gst_grouping($from,$to,$tax_id=0){

        $this->db->select("NULL AS item_code, 1 AS quantity, 0 AS discount_percent",false);
        $this->db->from('bank_trans_detail AS de');
        $this->db->select("de.id, de.trans_no, de.currence, trans.person_type_id");
        $this->db->select("trans.ref AS reference, trans.trans_date AS tran_date,de.currence_rate AS curr_rate");
        $this->db->select("trans.type AS order_type, trans.tax_inclusive AS tax_included, de.tax AS tax_id");

        $customer_name = "CASE trans.person_type_id";
        $customer_name.= " WHEN 2 then (select name from debtors_master where debtor_no=trans.person_id)";
        $customer_name.= " WHEN 3 then (select supp_name from suppliers where supplier_id=trans.person_id)";
        $customer_name.= " WHEN 4 then 'Quick Entry' ";
        $customer_name.= " ELSE 'Miscellaneous' ";
        $customer_name.= "END";
        $this->db->select("$customer_name AS customername",false);



        $this->db->select("CASE de.type WHEN '".ST_BANKDEPOSIT."' then 'DS' ELSE 'BP' END as type", false);


        $this->db->select(" (de.amount)*(-1) AS unit_price");


        $this->db->join('bank_trans AS trans', 'trans.trans_no= de.trans_no AND trans.type= de.type', 'left');
        $item_name = "CASE trans.person_type_id"
            ." WHEN 0 then trans.person_id"
                ." ELSE NULL "
                    ."END";

        $item_name = "trans.person_id";
        $this->db->select("$item_name AS item_name",false);

        if( $from ){
            $this->db->where('trans.trans_date >=',$from);
        }
        if( $to ){
            $this->db->where('trans.trans_date <=',$to);
        }
        $this->db->where('trans.amount <>',0);
        // 	    $this->trans->where("trans.type",ST_BANKPAYMENT);
        $this->db->where_in("trans.type",array(ST_BANKPAYMENT,ST_BANKDEPOSIT));


        $this->db->where(array('de.amount <>'=>0));
        if( $tax_id ){
            $this->db->where('de.tax',$tax_id);
        } else {
            $this->db->where('de.tax IS NOT NULL');
            $this->db->where('de.tax >',0);
        }

        $this->db->where('de.trans_no NOT IN (SELECT id FROM voided WHERE type=de.type )');

        $items = $this->db->group_by('de.id')->get()->result();

        return $items;
    }


    function get_bankID_balanceBigest($date=null){
        $this->db->select('SUM(amount) AS amount, bank_act');
        if( $date ){
            $this->db->where( "trans_date <=",date2sql($date) );
        }
        $data = $this->db->group_by('bank_act')->order_by('amount DESC')->get('bank_trans')->row();

        return ( $data && isset($data->bank_act) ) ? $data->bank_act : 0;



    }
    /*
     * replace for get_bank_trans_for_bank_account in GL/includes/db/gl_db_bank_trans.inc
     */
    function get_bank_trans_for_bank_account($bank_account, $from, $to){
        $this->db->select('t.*');
        $this->db->join('voided v','t.type=v.type AND t.trans_no=v.id','LEFT');
        $this->db->where( "t.bank_act",$bank_account);
        $this->db->where( "v.date_");
        if( $from ){
            $this->db->where( "trans_date >=",date2sql($from));
        }
        if( $to ){
            $this->db->where( "trans_date <=",date2sql($to) );
        }
        $data = $this->db->order_by('trans_date, t.id')->get('bank_trans t')->result();

        return $data;
    }

    /*
     * replace for get_balance_before_for_bank_account in GL/includes/db/gl_db_bank_trans.inc
     */
    function get_balance($bank_account, $from){
        $this->db->select('SUM(amount) AS amount');
        $this->db->where('bank_act',$bank_account);
        if( $from ){
            $this->db->where( "trans_date <",date2sql($from) );
        }
        $data = $this->db->get('bank_trans')->row();
        return ( $data && isset($data->amount) ) ? $data->amount : 0;
    }


    function get_bank_trans($type, $trans_no=null, $person_type_id=null, $person_id=null) {
        $select = 'bt.id AS banktran_id , bt.*, act.*';

        $select .=',IFNULL(abs(dt.ov_amount), IFNULL(ABS(st.ov_amount), bt.amount)) settled_amount';
        $select .=',IFNULL(abs(dt.ov_amount/bt.amount), IFNULL(ABS(st.ov_amount/bt.amount), 1)) settle_rate';
        $select .=',IFNULL(debtor.curr_code, IFNULL(supplier.curr_code, act.bank_curr_code)) settle_curr';


        $this->db->select($select,false)->from('bank_trans bt');

        $this->db->join('debtor_trans dt','dt.type=bt.type AND dt.trans_no=bt.trans_no','left');
        $this->db->join('debtors_master debtor','debtor.debtor_no = dt.debtor_no','left');
        $this->db->join('supp_trans st','st.type=bt.type AND st.trans_no=bt.trans_no','left');
        $this->db->join('suppliers supplier','supplier.supplier_id=st.supplier_id','left');
        $this->db->join('bank_accounts act ','act.id=bt.bank_act','left');



        if ($type != null){
            $this->db->where('bt.type',($type));
        }

        if ($trans_no != null)
            $this->db->where('bt.trans_no',($trans_no));

        if ($person_type_id != null)
            $this->db->where('bt.person_type_id',($person_type_id));

        if ($person_id != null)
            $this->db->where('bt.person_id',($person_id));

        $this->db->where('ABS(bt.amount) > 0');
        $data = $this->db->order_by('trans_date, bt.id')->get();

        $data_return = array();

        if( !is_object($data) ){
            check_db_error('<br>At file '.xdebug_call_file().':'.xdebug_call_line().':<br>query for bank transaction', $this->db->last_query(), false);

        } else {
            if( $data->num_rows() > 1 ){

//                 display_db_error("duplicate payment bank transaction found", $this->db->last_query(), false );
                foreach ($data->result() AS $k=>$value){

                    if( $k < $data->num_rows()-1 ){
                        $this->db->where('id',$value->banktran_id)->update('bank_trans',array('amount'=>0));
                    } elseif( $k == $data->num_rows()-1 ){
                        $data_return = (array)$value;
                    }
                }

            } else {
                $data_return = $data->row_array();
            }
        }


        return $data_return;
    }

    function get_tran($type, $trans_no=null, $person_type_id=null, $person_id=null) {
        $select = 'bt.*, act.*';

        $select .=', IFNULL(abs(dt.ov_amount), IFNULL(ABS(st.ov_amount), bt.amount)) settled_amount';
        $select .=',IFNULL(abs(dt.ov_amount/bt.amount), IFNULL(ABS(st.ov_amount/bt.amount), 1)) settle_rate';
        $select .=',IFNULL(debtor.curr_code, IFNULL(supplier.curr_code, act.bank_curr_code)) settle_curr';


        $this->db->select($select,false)->from('bank_trans bt');
        $this->db->join('debtor_trans dt','dt.type=bt.type AND dt.trans_no=bt.trans_no','left');
        $this->db->join('debtors_master debtor','debtor.debtor_no = dt.debtor_no','left');
        $this->db->join('supp_trans st','st.type=bt.type AND st.trans_no=bt.trans_no','left');
        $this->db->join('suppliers supplier','supplier.supplier_id=st.supplier_id','left');
        $this->db->join('bank_accounts act ','act.id=bt.bank_act','left');




        if ($type != null){
            $this->db->where('bt.type',($type));
        }

        if ($trans_no != null)
            $this->db->where('bt.trans_no',($trans_no));

        if ($person_type_id != null)
            $this->db->where('bt.person_type_id',($person_type_id));

        if ($person_id != null)
            $this->db->where('bt.person_id',($person_id));

        $data = $this->db->order_by('trans_date, bt.id')->get();
        // 	    bug($this->trans->last_query());
        // 	   bug($data);die;
        return $data->row();
    }

    function get_trans($where=null){
        if( empty($where) ){
            return false;
        }

        $select = 'bt.*,act.*';
        $select.=', IFNULL(debtor.curr_code, IFNULL(supplier.curr_code, act.bank_curr_code)) settle_curr ';
        $this->db->select($select,false)->from('bank_trans AS bt');
        $this->db->join('debtor_trans AS dt', 'dt.type=bt.type AND dt.trans_no=bt.trans_no', 'left');
        $this->db->join('debtors_master AS debtor', 'debtor.debtor_no = dt.debtor_no', 'left');
        $this->db->join('bank_accounts AS act', 'act.id=bt.bank_act', 'left');
        $this->db->join('supp_trans AS st', 'st.type=bt.type AND st.trans_no=bt.trans_no', 'left');
        $this->db->join('suppliers AS supplier', 'supplier.supplier_id = st.supplier_id', 'left');
        $this->db->where($where);

        $data =$this->db->group_by('trans_no')->get()->result();

        return $data;

    }

    function get_bank_tran_details($type,$tran_no){
        $this->db->where(array('tran.type'=>$type,'tran.trans_no'=>$tran_no))->from('bank_trans_detail AS tran')->select('tran.*, tran.tax AS gst');
        $this->db->left_join('gl_trans AS gl',"gl.type=tran.type AND gl.type_no=tran.trans_no AND gl.account=tran.account_code AND gl.amount <> 0 AND gl.amount=tran.amount")->select('gl.dimension2_id, gl.dimension_id, gl.memo_');

        $query = $this->db->group_by('tran.id')->get();

        if( is_object($query) && $query->num_rows > 0){
            return $query->result();
        }
    }

    function update_item_fix($tran_no,$tran_type){
        $void_module = module_model_load('voiding','void');

        $this->db->where(array('trans_no'=>$tran_no,'type'=>$tran_type))->update('bank_trans_detail',array('amount'=>0));
        $this->db->where(array('trans_no'=>$tran_no,'type'=>$tran_type))->update('bank_trans',array('amount'=>0));

        $void_module->do_gl($tran_no,$tran_type);
    }





}