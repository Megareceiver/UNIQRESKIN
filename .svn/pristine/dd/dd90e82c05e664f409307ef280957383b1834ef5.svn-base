<?php
class Purchases_Supplier_trans_Model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

    function gst_grouping($from,$to,$tax_id=0, $not_in_ids = array() ){
        $select = 'inv.id, inv.supp_trans_no AS trans_no, inv.unit_price,  0 AS discount_percent'
            .',trans.reference, trans.tran_date, 0 AS tax_included, trans.type AS order_type, trans.fixed_access'
                .', supp.supp_name AS supp_name, supp.curr_code AS currence'
                    .", inv.tax_type_id AS tax_id"
                        .", CASE  trans.type WHEN ".ST_SUPPCREDIT." then 'SCN' ELSE 'P' END AS type"
                            .", IF(inv.quantity = 0, 1, inv.quantity) as quantity"
                                .", IF (trans.rate <=0, 1, trans.rate ) AS curr_rate"
                                    .",inv.gl_code"
                                        .", IF (inv.gl_code IS NOT NULL AND inv.gl_code != 0 ,inv.gl_code, inv.stock_id ) AS item_code"
                                            .", IF (inv.gl_code IS NOT NULL AND inv.gl_code != 0, (SELECT account_name FROM chart_master WHERE account_code = inv.gl_code), (SELECT description FROM stock_master WHERE stock_id = inv.stock_id) ) AS item_name"
                                                ;

        $this->db->select($select, false);

        /* not get emtpy transaction */
        $this->db->where('inv.unit_price !=',0);
        $this->db->join('supp_trans AS trans', 'trans.trans_no= inv.supp_trans_no AND trans.type=inv.supp_trans_type', 'left');
        $this->db->where('trans.ov_amount !=',0);
        $this->db->where_in("trans.type",array(ST_SUPPINVOICE,ST_SUPPCREDIT));

        if( $from ){
            $this->db->where('trans.tran_date >=',$from);
        }
        if( $to ){
            $this->db->where('trans.tran_date <=',$to);
        }

        $this->db->join('suppliers AS supp', 'supp.supplier_id= trans.supplier_id', 'left');

        if( $tax_id && $tax_id > 0 ){
            $this->db->where('inv.tax_type_id',$tax_id);
        } else if( $tax_id=='null'){
            if( is_array($not_in_ids) && !empty($not_in_ids)){
                $this->db->where_not_in('inv.id',$not_in_ids);

            }
        } else {
            $this->db->where('inv.tax_type_id IS NOT NULL');
            $this->db->where('inv.tax_type_id >',0);
        }
        $items = $this->db->get('supp_invoice_items AS inv')->result();

        return $items;
    }

    function gst_grouping_from_trans_tax($from,$to,$tax_id=0){
        $select = 'tax_trans.id AS item_id, inv.id, inv.stock_id AS item_code, inv.description AS item_name, inv.supp_trans_no AS trans_no, 0 AS discount_percent'
        // 	        .', inv.unit_price'
        // .", tax_trans.net_amount AS unit_price"
        .",inv.unit_price"
            .",trans.reference AS reference, tax_trans.tran_date, 0 AS tax_included, tax_trans.ex_rate AS curr_rate, tax_trans.trans_type AS order_type, trans.fixed_access"
                .', deb.name AS customername, deb.curr_code AS currence'
                //             .",CONCAT('P') AS type, tax_trans.tax_type_id AS tax_id, inv.tax_type_id"
        .", tax_trans.tax_type_id AS tax_id, inv.tax_type_id"
            .", inv.quantity, inv.quantity AS qty"
                .", CASE  tax_trans.trans_type WHEN ".ST_SUPPCREDIT." then 'SCN' ELSE 'P' END AS type"
                    ;

        $this->db->select($select, false);

        /* not get emtpy transaction */
        $this->db->where(array('inv.unit_price !='=>0,'inv.quantity !='=>0));
        // 	    $this->tran->join('tax_types AS tax', 'tax.id= inv.tax_type_id', 'left');
        $this->db->join('supp_trans AS trans', 'trans.trans_no= tax_trans.trans_no', 'left');
        $this->db->where('tax_trans.net_amount !=',0);
        $this->db->where_in("tax_trans.trans_type",array(ST_SUPPINVOICE,ST_SUPPCREDIT));
        // 	    $this->tran->where("tax_trans.trans_type",ST_SUPPINVOICE);

        // 	    $this->tran->join('supp_invoice_items AS inv', 'trans.trans_no= inv.supp_trans_no AND ( inv.tax_type_id IS NULL OR  inv.tax_type_id = 0) AND inv.supp_trans_type = tax_trans.trans_type', 'left');
        $this->db->join('supp_invoice_items AS inv', 'trans.trans_no= inv.supp_trans_no AND ( inv.tax_type_id IS NULL OR  inv.tax_type_id = 0) AND inv.supp_trans_type = tax_trans.trans_type', 'left');
        $this->db->join('debtors_master AS deb', 'deb.debtor_no= trans.supplier_id', 'left');

        if( $from ){
            $this->db->where('tax_trans.tran_date >=',$from);
        }
        if( $to ){
            $this->db->where('tax_trans.tran_date <=',$to);
        }
        if( $tax_id && $tax_id > 0 ){
            $this->db->where('tax_trans.tax_type_id',$tax_id);
        }else if( $tax_id=='null' ){

            $this->db->where('( inv.tax_type_id  IS NULL OR inv.tax_type_id=0) AND tax_trans.tax_type_id > 0 ');
        } else {
            $this->db->where('inv.tax_type_id  IS NOT NULL');
            $this->db->where('tax_trans.tax_type_id >',0);
        }


        $items = $this->db->group_by('inv.id')->get('trans_tax_details AS tax_trans')->result();

        return $items;
    }


    function gst_grouping_baddebt($from=null,$to=null){
        $select = 'inv.id, inv.stock_id AS item_code, inv.description AS item_name, inv.supp_trans_no AS trans_no, inv.unit_price,  0 AS discount_percent'
            .',trans.reference, trans.tran_date, 0 AS tax_included, trans.type AS order_type, trans.fixed_access'
                .', supp.supp_name AS supp_name, supp.curr_code AS currence'
                    .", inv.tax_type_id AS tax_id"
                        .", 'PBD' AS type"
                        //             .", IF(inv.quantity = 0 && trans.type=".ST_SUPPCREDIT.", 1, inv.quantity) as quantity"
        .", IF(inv.quantity = 0, 1, inv.quantity) as quantity"
            .", IF (trans.rate <=0, 1, trans.rate ) AS curr_rate"
            //             ." IF ( inv.grn_item_id<=0, 1<  )"
        // 	        .',tax.rate AS tax_rate'
        ;


        $this->db->select($select,false);

        $this->db->join('supp_trans AS trans', 'trans.trans_no= bad.type_no AND trans.type=bad.type', 'left');
        $this->db->join('supp_invoice_items AS inv', 'inv.supp_trans_no= trans.trans_no AND inv.supp_trans_type = bad.type', 'left');
        // 		$this->tran->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');
        $this->db->join('suppliers AS supp', 'supp.supplier_id= trans.supplier_id', 'left');
        if( $from ){
            $this->db->where('DATE(bad.tran_date) >=',$from);
        }
        if( $to ){
            $this->db->where('DATE(bad.tran_date) <=',$to);
        }
        $this->db->where('bad.type',ST_SUPPINVOICE);
        $this->db->where('bad.type_no NOT IN ( SELECT type_no FROM bad_debts WHERE step =2 AND type='.ST_SUPPINVOICE.' ) ');
        $data = $this->db->group_by('bad.type_no')->get('bad_debts AS bad')->result();

        return $data;
    }
}