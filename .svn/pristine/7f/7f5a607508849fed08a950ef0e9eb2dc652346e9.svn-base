<?php
class Bank_trans_Model extends CI_Model {
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
        $select = 'de.id, NULL AS item_code, NULL AS item_name, de.trans_no AS trans_no, (de.amount) AS unit_price, 1 AS quantity, 0 AS discount_percent'
            .',trans.ref AS reference, trans.trans_date AS tran_date, trans.tax_inclusive AS tax_included, de.currence_rate AS curr_rate'
                .', de.currence AS currence , de.tax AS tax_id, trans.type AS order_type'
                    .", CASE de.type WHEN '".ST_BANKDEPOSIT."' then 'DS' ELSE 'BP' END as type"

                        .', trans.person_type_id'
                            .", CASE trans.person_type_id "
                                ." WHEN 2 then (select name from debtors_master where debtor_no=trans.person_id)"
                                    ." WHEN 3 then (select supp_name from suppliers where supplier_id=trans.person_id)"
                                        ." WHEN 4 then 'Quick Entry' "
                                            ." ELSE 'Miscellaneous' "
                                                ."END AS customername"
                                                    ;

        $this->db->select($select, false);

        $this->db->join('bank_trans AS trans', 'trans.trans_no= de.trans_no AND trans.type= de.type', 'left');
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

        $items = $this->db->get('bank_trans_detail AS de')->result();

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

}