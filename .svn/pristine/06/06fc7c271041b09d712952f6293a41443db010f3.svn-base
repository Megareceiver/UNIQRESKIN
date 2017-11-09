<?php
class PurchasesInquiryCheck  {
    function __construct() {
        $this->db = get_instance()->db;
        $this->input = get_instance()->input;
        $this->datatable = module_control_load('datatable','html');
    }

    function index(){
        global $Ajax;

        $fields = array(
            'debtor_no' => array('type'=>'SUPPLIER','title'=>_('Supplier'),'value'=>0 ),
            'date_from' => array('type'=>'date','title'=>_('From'),'value'=>begin_month() ),
            'date_to' => array('type'=>'date','title'=>_('To'),'value'=>end_month() ),
        );

        if( $this->input->post('SUBMIT') ){
            foreach ($fields AS $k=>$f){
                $fields[$k]['value'] = input_post($k);
            }

            $Ajax->activate('trans_tbl');

        }

        add_js('js/table.js');
        page("Supplier Check Transaction");
        box_start();

        start_form();
        module_view('inquiry/filter-control',$fields);
        end_form();


        $this->trans_tottaly($fields);

        $this->datatable->view($this->datatable_view, $this->trans_tottaly($fields),'trans_tbl');

        box_footer();
        box_end();
        end_page();
    }

    var $datatable_view = array(
        'type'=>array('Type','left',9,'trans_type'),
        'trans_no'=>array('#','center',5,'tran_detail_view'),
        'reference'=>array('REF','center',8),
        'tran_date'=>array('Date','center',7,'date'),
        'debit'=>array('Debit','right',10,'number'),
        'credit'=>array('Credit','right',10,'number'),
        'gl_sum'=>array('GL Sum','right',10,'number'),
        'detail_count'=>array('Detail Count','center',5),
    );


    private function trans_tottaly($fields){

        $this->db->from('supp_trans AS tran');
        $this->db->select('tran.trans_no, tran.type, tran.reference, tran.tran_date, tran.ov_amount',false);

        $detail_count = "(Select COUNT(id) FROM supp_invoice_items AS d WHERE d.supp_trans_type=tran.type AND d.supp_trans_no=tran.trans_no)";
        $allocated_count = "(Select COUNT(id) FROM supp_allocations AS allo WHERE allo.trans_type_from=tran.type AND allo.trans_no_from=tran.trans_no)";

        $this->db->select("IF(tran.type=".ST_SUPPAYMENT.",$allocated_count,$detail_count) AS detail_count",false);


        $this->db->select('(Select SUM(IF(gl.amount > 0,gl.amount,0)) FROM gl_trans AS gl WHERE gl.type=tran.type AND gl.type_no=tran.trans_no ) AS gl_sum',false);

        $this->db->select("IF(tran.ov_amount < 0,tran.ov_amount + tran.ov_gst ,0) AS credit",false);
        $this->db->select("IF(tran.ov_amount > 0,tran.ov_amount + tran.ov_gst ,0) AS debit",false);

        if( is_date($date_from = $fields['date_from']['value']) ){
            $this->db->where("tran.tran_date >=",date2sql($date_from));
        }
        if( is_date($date_to = $fields['date_to']['value']) ){
            $this->db->where("tran.tran_date <=",date2sql($date_to));
        }

        $this->db->having('ABS(tran.ov_amount) <> 0',false);
//         $this->db->where_in('tran.type',array(ST_SALESINVOICE,ST_CUSTCREDIT,ST_CUSTDELIVERY));
        if( is_numeric($debtor_no=$fields['debtor_no']['value'])  && $debtor_no > 0){
            $this->db->where('tran.debtor_no',$debtor_no);
        }


        $query = $this->db->order_by('tran.tran_date ASC')->get();


        return $query;
    }
}