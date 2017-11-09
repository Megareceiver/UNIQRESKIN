<?php
class GST_Tax_Model extends CI_Model {
    function __construct(){
        parent::__construct();

    }

    function get_row($id=0){
        $this->db->reset();
        $this->db->where('tax.id',$id);
        $this->db->join('chart_master AS chart1','tax.sales_gl_code = chart1.account_code','left');
        $this->db->join('chart_master AS chart2','tax.purchasing_gl_code = chart2.account_code','left');
        $this->db->select('tax.*,chart1.account_name AS SalesAccountName, chart2.account_name AS PurchasingAccountName');
        $data = $this->db->get('tax_types AS tax')->row();

        return $data;
    }


    function tax_type_items(){
        $select = " *, 0 AS amount";
        $this->db->select($select,false)->from('tax_types');
        $data = $this->db->get()->result();
        return $data;
    }

    function get_summary($from,$to){
        $taxes = $this->tax_type_items();

        $fields_view = array('net_output','net_input','payable','collectible');
        foreach ($taxes AS $k=>$taxe){
            if( $taxe->rate <= 0 ) {
                unset( $taxes[$k] );
                continue;
            }
            $tax_trans_items = $this->get_summary_from_trans_tax($from, $to,$taxe->id);
            $supplier_trans = $this->get_summary_from_supplier($from, $to,$taxe->id,$taxe->rate);
            $customer_trans = $this->get_summary_from_debtor($from, $to,$taxe->id,$taxe->rate);

            foreach ($fields_view AS $kk){
                if( !isset($taxes[$k]->$kk) )
                    $taxes[$k]->$kk = 0;
                if( is_object($tax_trans_items) ){
                    $taxes[$k]->$kk += $tax_trans_items->$kk;
                }

            }
            $taxes[$k]->net_output += $supplier_trans->net_output;
            $taxes[$k]->payable += $supplier_trans->amount;
            $taxes[$k]->net_input -= $customer_trans->net_input;
            $taxes[$k]->collectible -= $customer_trans->amount;

            $taxes[$k]->name =  $taxes[$k]->name.' '. $taxes[$k]->rate.'%';

        }

        // 	    bug($taxes); die('test');
        return $taxes;

    }


    function get_summary_from_trans_tax($from,$to,$tax_id){
        $select = 'taxrec.rate, ttype.id, ttype.name';
        $select.=", CONCAT(ttype.name,' ',ttype.rate,'%') AS type_name";

        $select .= ',SUM( '
            .'IF(taxrec.trans_type='.ST_CUSTCREDIT.' || taxrec.trans_type='.ST_SUPPINVOICE.' || taxrec.trans_type='.ST_JOURNAL.',-1,1) * '
                .'IF(taxrec.trans_type='.ST_BANKDEPOSIT.' || taxrec.trans_type='.ST_SALESINVOICE.' || (taxrec.trans_type='.ST_JOURNAL .' AND amount<0) || taxrec.trans_type='.ST_CUSTCREDIT.', net_amount*ex_rate,0)'
                    .') AS net_output';
        $select .= ",SUM( "
            ."IF(taxrec.trans_type=".ST_CUSTCREDIT." || taxrec.trans_type=".ST_SUPPINVOICE." || taxrec.trans_type=".ST_JOURNAL.",-1,1) * "
                ."IF(taxrec.trans_type=".ST_BANKDEPOSIT." || taxrec.trans_type=".ST_SALESINVOICE." || (taxrec.trans_type=".ST_JOURNAL ." AND amount<0) || taxrec.trans_type=".ST_CUSTCREDIT.", amount*ex_rate,0) "
                    .") AS payable,";
        $select .= ",SUM( "
            ."IF(taxrec.trans_type=".ST_CUSTCREDIT." || taxrec.trans_type=".ST_SUPPINVOICE." || taxrec.trans_type=".ST_JOURNAL.",-1,1) * "
                ."IF(taxrec.trans_type=".ST_BANKDEPOSIT." || taxrec.trans_type=".ST_SALESINVOICE." || (taxrec.trans_type=".ST_JOURNAL ." AND amount<0)  || taxrec.trans_type=".ST_CUSTCREDIT.", 0, net_amount*ex_rate) "
                    .") AS net_input,";
        $select .= ",SUM( "
            ."IF(taxrec.trans_type=".ST_CUSTCREDIT." || taxrec.trans_type=".ST_SUPPINVOICE." || taxrec.trans_type=".ST_JOURNAL.",-1,1) * "
                ."IF(taxrec.trans_type=".ST_BANKDEPOSIT." || taxrec.trans_type=".ST_SALESINVOICE." || (taxrec.trans_type=".ST_JOURNAL ." AND amount<0)  || taxrec.trans_type=".ST_CUSTCREDIT.", 0, amount*ex_rate)"
                    .") AS collectible,";
        $this->db->select($select,false);
        $this->db->join('trans_tax_details taxrec','taxrec.tax_type_id=ttype.id','left');
        $this->db->where( array('taxrec.tran_date >='=>date2sql($from),'taxrec.tran_date <='=>date2sql($to) ) );
        $this->db->where('taxrec.trans_type !=',ST_CUSTDELIVERY);
        if( $tax_id ){
            $this->db->where('ttype.id',$tax_id);
        }
        $data = $this->db->group_by('ttype.id')->get('tax_types ttype');

        return $data->row();
    }

    function get_summary_from_supplier($from,$to,$tax_id,$tax_rate=1){
        $select = 'SUM(inv.unit_price*inv.quantity) AS amount, SUM(inv.unit_price*inv.quantity*'.($tax_rate/100).') AS net_output  ';
        // 	    $select = 'trans.ov_amount, trans.ov_gst, inv.unit_price*inv.quantity*trans.rate AS tax_calcu, trans.rate, inv.* ';
        $this->db->select($select,false);
        $this->db->where('inv.tax_type_id',$tax_id);


        $this->db->join('supp_trans AS trans', 'trans.trans_no= inv.supp_trans_no AND trans.type=inv.supp_trans_type', 'left');
        $this->db->where('trans.ov_amount !=',0);
        $this->db->where( array('trans.tran_date >='=>date2sql($from),'trans.tran_date <='=>date2sql($to) ) );

        $data = $this->db->get('supp_invoice_items AS inv')->row();
        return $data;
    }

    function get_summary_from_debtor($from,$to,$tax_id,$tax_rate=1){
        $select = 'SUM(de.unit_price*de.quantity) AS amount, SUM(de.unit_price*de.quantity*'.($tax_rate/100).') AS net_input  ';
        // 	    $select = 'trans.ov_amount, trans.ov_gst, inv.unit_price*inv.quantity*trans.rate AS tax_calcu, trans.rate, inv.* ';
        $this->db->select($select,false);
        $this->db->where('de.tax_type_id',$tax_id);


        $this->db->join('debtor_trans AS trans', 'trans.trans_no = de.debtor_trans_no AND trans.type = de.debtor_trans_type', 'left');
        $this->db->where('trans.ov_amount !=',0);
        $this->db->where( array('trans.tran_date >='=>date2sql($from),'trans.tran_date <='=>date2sql($to) ) );

        $data = $this->db->get('debtor_trans_details AS de')->row();
        return $data;
    }
}