<?php
class Tax_Model {
	function __construct(){
		global $ci;
		$this->tax = $ci->db;
	}

	function tax_type_items(){
		$select = " *, 0 AS amount";
		$this->tax->select($select,false)->from('tax_types');
		$data = $this->tax->get()->result();
		return $data;
	}

	function item_options($use_for=0){
		$options = array();
		$this->tax->select('id,name')->order_by('name', 'ASC');
		if($use_for){
			$this->tax->where('use_for',$use_for);
		}
		$data = $this->tax->get('tax_types')->result();;
		if( $data ){
			foreach ($data AS $row){
				$options[$row->id] = $row->name;
			}
		}
		return $options;
	}

	function get_row($id=0){
		$this->tax->reset();
		$this->tax->where('tax.id',$id);
		$this->tax->join('chart_master AS chart1','tax.sales_gl_code = chart1.account_code','left');
		$this->tax->join('chart_master AS chart2','tax.purchasing_gl_code = chart2.account_code','left');
		$this->tax->select('tax.*,chart1.account_name AS SalesAccountName, chart2.account_name AS PurchasingAccountName');
		$data = $this->tax->get('tax_types AS tax')->row();

		return $data;
	}


	function item_type_code($id){

	    $tax = tax($id);
	    $gl_acc = $this->tax->where('id',$id)->get('tax_types')->row();

	    if( empty($tax) ){
	        $tax = (object)array('rate'=>0);
	    }
	    if( is_object($gl_acc) && isset($gl_acc->id) ){

	        $tax->sales_gl_code = $gl_acc->sales_gl_code;
	        $tax->purchasing_gl_code = $gl_acc->purchasing_gl_code;
	    } else {
	        $data = (array($tax));
	        unset($data['country']);
	        unset($data['no']);
	        $data['sales_gl_code'] = 2150;
	        $data['purchasing_gl_code'] = 1300 ;
	        $this->tax->insert('tax_types',$data);

	        $tax->sales_gl_code = $data['sales_gl_code'];
	        $tax->purchasing_gl_code = $data['purchasing_gl_code'];
	    }

// 		$tax = $this->get_row($id);
// 		if ( $tax ){
// 			if (($startPos = strpos($tax->name, '(')) !== false && ($endPos = strpos($tax->name, ')', $startPos+=strlen('('))) !== false) {
// 				$tax->type_code = substr($tax->name, $startPos, $endPos-$startPos);
// 			}
// 		}
		return $tax;
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
                if( !isset($taxes[$k]->$kk) ) $taxes[$k]->$kk = 0;
                $taxes[$k]->$kk += $tax_trans_items->$kk;
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
	    $this->tax->select($select,false);
	    $this->tax->join('trans_tax_details taxrec','taxrec.tax_type_id=ttype.id','left');
	    $this->tax->where( array('taxrec.tran_date >='=>date2sql($from),'taxrec.tran_date <='=>date2sql($to) ) );
	    $this->tax->where('taxrec.trans_type !=',ST_CUSTDELIVERY);
	    if( $tax_id ){
	        $this->tax->where('ttype.id',$tax_id);
	    }
	    $data = $this->tax->group_by('ttype.id')->get('tax_types ttype')->row();

	    return $data;
	}

	function get_summary_from_supplier($from,$to,$tax_id,$tax_rate=1){
	    $select = 'SUM(inv.unit_price*inv.quantity) AS amount, SUM(inv.unit_price*inv.quantity*'.($tax_rate/100).') AS net_output  ';
// 	    $select = 'trans.ov_amount, trans.ov_gst, inv.unit_price*inv.quantity*trans.rate AS tax_calcu, trans.rate, inv.* ';
	    $this->tax->select($select,false);
	    $this->tax->where('inv.tax_type_id',$tax_id);


	    $this->tax->join('supp_trans AS trans', 'trans.trans_no= inv.supp_trans_no AND trans.type=inv.supp_trans_type', 'left');
	    $this->tax->where('trans.ov_amount !=',0);
	    $this->tax->where( array('trans.tran_date >='=>date2sql($from),'trans.tran_date <='=>date2sql($to) ) );

        $data = $this->tax->get('supp_invoice_items AS inv')->row();
        return $data;
	}

	function get_summary_from_debtor($from,$to,$tax_id,$tax_rate=1){
	    $select = 'SUM(de.unit_price*de.quantity) AS amount, SUM(de.unit_price*de.quantity*'.($tax_rate/100).') AS net_input  ';
	    // 	    $select = 'trans.ov_amount, trans.ov_gst, inv.unit_price*inv.quantity*trans.rate AS tax_calcu, trans.rate, inv.* ';
	    $this->tax->select($select,false);
	    $this->tax->where('de.tax_type_id',$tax_id);


	    $this->tax->join('debtor_trans AS trans', 'trans.trans_no = de.debtor_trans_no AND trans.type = de.debtor_trans_type', 'left');
	    $this->tax->where('trans.ov_amount !=',0);
	    $this->tax->where( array('trans.tran_date >='=>date2sql($from),'trans.tran_date <='=>date2sql($to) ) );

	    $data = $this->tax->get('debtor_trans_details AS de')->row();
	    return $data;
	}


}