<?php
class BankInquiryCheck  {
    function __construct() {
        $this->db = get_instance()->db;
        $this->input = get_instance()->input;
        $this->datatable = module_control_load('datatable','html');

        $this->filters = array(
            'bank_account' => array('type'=>'BANK_ACCOUNTS','title'=>_('Account'),'value'=>0 ),
            'date_from' => array('type'=>'date','title'=>_('From'),'value'=>begin_month() ),
            'date_to' => array('type'=>'date','title'=>_('To'),'value'=>begin_month() ),
        );

        $this->filters['bank_account']['value'] = 3;
        $this->filters['date_from']['value'] = '01-07-2015';
        $this->filters['date_to']['value'] = '30-09-2015';
    }

    function index(){
        global $Ajax;
//         add_js('js/table.js');
        page("Bank Check Transaction");

        box_start();

        $this->filter_control();


//         $this->datatable->view($this->datatable_view, $this->trans_tottaly($fields),'trans_tbl');
        $Ajax->activate('trans_tbl');
        div_start("trans_tbl");
        module_view("inquiry/bank_check",array(
            'table'=>$this->datatable_view,
            'items'=>$this->trans_tottaly(),
            'date_from'=>sql2date($this->filters['date_from']['value']),
            'date_to'=>sql2date($this->filters['date_to']['value']),
//             'balance_open'=>$this->get_opening()
        ));
        div_end();

        box_end();
        end_page();
    }

    private function filter_control(){
        if( $this->input->post('SUBMIT') ){
            foreach ($this->filters AS $k=>$f){
                $this->filters[$k]['value'] = input_post($k);
            }

        }
        start_form();
        module_view('inquiry/statement-control',$this->filters);
        end_form();
    }

    var $datatable_view = array(
        'type'=>array('Type','left',9,'trans_type'),
        'trans_no'=>array('#','center',5,'tran_detail_view'),
        'ref'=>array('REF','center',8),
        'trans_date'=>array('Date','center',7,'date'),
        'debit'=>array('Debit','right',10,'number'),
        'credit'=>array('Credit','right',10,'number'),
        'gl_sum'=>array('GL Sum','right',10,'number'),
        'detail_count'=>array('Detail Count','center',5),
    );

    private function trans_tottaly(){

        $this->db->from('bank_trans AS b');
        $this->db->select('b.trans_no, b.type, b.ref, b.trans_date, b.amount');
        $this->db->select('(Select COUNT(id) FROM bank_trans_detail AS d WHERE d.type=b.type AND d.trans_no=b.trans_no ) AS detail_count',false);
        $this->db->select('(Select SUM(IF(gl.amount > 0,gl.amount,0)) FROM gl_trans AS gl WHERE gl.type=b.type AND gl.type_no=b.trans_no ) AS gl_sum',false);

        $this->db->select("IF(b.amount < 0,-b.amount ,0) AS credit",false);
        $this->db->select("IF(b.amount > 0,b.amount ,0) AS debit",false);

        if( is_date($date_from = $this->filters['date_from']['value']) ){
            $this->db->where("b.trans_date >=",date2sql($date_from));
        }
        if( is_date($date_to = $this->filters['date_to']['value']) ){
            $this->db->where("b.trans_date <=",date2sql($date_to));
        }

        $this->db->having('ABS(b.amount) <> 0',false);
//         $this->db->where_in('b.type',array(ST_BANKPAYMENT,ST_BANKDEPOSIT,ST_BANKTRANSFER));
        if( is_numeric($bank_act=$this->filters['bank_account']['value'])  && $bank_act > 0){
            $this->db->where('b.bank_act',$bank_act);
        }


        $query = $this->db->order_by('b.trans_date ASC')->get();
        return $query->result();
    }
}