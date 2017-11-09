<?php
class BankInquiryStatement  {
    function __construct() {
        $this->db = get_instance()->db;
        $this->input = get_instance()->input;

        $this->filters = array(
            'bank_account' => array('type'=>'BANK_ACCOUNTS','title'=>'Account','value'=>0 ),
            'date_from' => array('type'=>'date','title'=>'From','value'=>'' ),
            'date_to' => array('type'=>'date','title'=>'To','value'=>'' ),
        );

        if( array_key_exists('check', $_GET) ){
            $this->filters['check'] = array('type'=>'check','title'=>'','value'=>1 );
            $this->check = 1;
        } else {
            $this->check = input_val('check_incorrect');
        }
    }

    function index(){
//         add_js('js/table.js');
        page("Bank Statement");
        $this->statement();

        global $Ajax;
        $Ajax->activate('trans_tbl');

        div_start("trans_tbl");
        box_start();
        module_view("inquiry/statement",array(
            'table'=>$this->datatable_headers,
            'items'=>$this->get_bank_trans($this->filters),
            'date_from'=>sql2date($this->filters['date_from']['value']),
            'date_to'=>sql2date($this->filters['date_to']['value']),
            'balance_open'=>$this->get_opening()
        ));
        box_footer_start();
        box_footer_end();
        box_end();
        div_end();
        end_page();
    }

    private function statement(){

        if( $this->input->post('SUBMIT') ){
            foreach ($this->filters AS $k=>$f){
                $this->filters[$k]['value'] = input_post($k);
            }
        }

        start_form();
        box_start();
        module_view('inquiry/statement-control',$this->filters);
        box_end();
        end_form();
    }

    var $datatable_headers = array(
        'type'=>array('Type','left',10,'trans_type'),
        'trans_no'=>array('#','center',6,'tran_detail_view'),
        'ref'=>array('Reference','left',10),
        'trans_date'=>array('Date','center',9,'date'),
        'debit'=>array('Debit','right',10,'number'),
        'credit'=>array('Credit','right',10,'number'),
        'balance'=>array('Balance','right',10,'number'),

        //'balance_exc'=>array('Balance Exchange','right',20),
        //'rate'=>array('Exchange Rate','right',20),
        //         'item'=>array('Person/Item','left',NULL,NULL),

        'item'=>array('Person/Item','left',NULL,NULL),
        'memo'=>array('Memo'),
    );

    private function get_bank_trans($fields){
        if( $this->check ){
            $check_debtor_tran_exist = "SELECT COUNT(*) FROM debtor_trans AS deb WHERE deb.trans_no=b.trans_no AND deb.type=b.type AND deb.ov_amount <>0";
            $check_supp_tran_exist = "SELECT COUNT(*) FROM supp_trans AS supp WHERE supp.trans_no=b.trans_no AND supp.type=b.type AND supp.ov_amount <>0";

            $check_exist = "CASE b.type"

//                 ." when ".ST_BANKPAYMENT." then ($check_bank_tran_exist)"
//                 ." when ".ST_BANKDEPOSIT." then ($check_bank_tran_exist)"
//                 ." when ".ST_BANKTRANSFER." then ($check_bank_tran_exist)"

                ." when ".ST_CUSTPAYMENT." then ($check_debtor_tran_exist)"
                ." when ".ST_SUPPAYMENT." then ($check_supp_tran_exist)"
                ." ELSE 0 END"
                    ;
                    $this->db->select("$check_exist AS tran_exist",false);

                    $this->db->having('(tran_exist <> 1 AND b.type NOT IN ('.ST_BANKPAYMENT.','.ST_BANKDEPOSIT.','.ST_BANKTRANSFER.'))');

        }

        if( is_numeric($bank_act=$fields['bank_account']['value'])  && $bank_act > 0){
            $this->db->where('b.bank_act',$bank_act);
        } else {
            $this->db->reset();
            return NULL;
        }

        $this->db->from('bank_trans AS b');
        $this->db->select('b.person_type_id, b.person_id');

        $this->db->select('b.person_type_id, b.person_id, b.trans_no, b.type, b.ref, b.trans_date, b.amount');
        $this->db->join('bank_accounts AS acc','acc.id=b.bank_act','LEFT')->select('acc.bank_curr_code AS curr_code');

        $this->db->select('b.trans_no, b.type, b.ref, b.trans_date, b.amount');

        $this->db->select("IF(b.amount < 0,b.amount ,0) AS credit",false);
        $this->db->select("IF(b.amount > 0,b.amount ,0) AS debit",false);

        if( is_date($date_from = $fields['date_from']['value']) ){
            $this->db->where("b.trans_date >=",date2sql($date_from));
        }
        if( is_date($date_to = $fields['date_to']['value']) ){
            $this->db->where("b.trans_date <=",date2sql($date_to));
        }

        $this->db->having('ABS(b.amount) <> 0',false);

        $query = $this->db->order_by('b.trans_date , b.id')->get();

        if( !is_object($query) ){
            check_db_error("The DB error.", $this->db->last_query());
            return array();
        } else {
            return $query->result();
        }

    }

    private function get_opening(){

        $this->db->select("SUM(amount) AS amount",false);
        $this->db->where('bank_act',$this->filters['bank_account']['value']);
        $this->db->where("trans_date < ",date2sql( $this->filters['date_from']['value']) );

        $result = $this->db->get('bank_trans');
        if( !is_object($result) ){
            check_db_error("The starting balance on hand could not be calculated", $this->db->last_query());
        } else {
            $data = $result->row();
            return (is_object($data) AND isset($data->amount) ) ? $data->amount : 0;
        }
    }
}