<?php
class Sales_Customer_trans_Model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

    function gst_grouping($from,$to,$tax_id=0){
        $select = 'de.id, de.stock_id AS item_code, de.description AS item_name, de.debtor_trans_no AS trans_no, de.unit_price, de.discount_percent'
            .',trans.reference, trans.tran_date, trans.type AS order_type, saletype.tax_included, trans.rate AS curr_rate'
                .', deb.name AS customername, deb.curr_code AS currence, deb.msic, deb.debtor_no'
                    .", de.tax_type_id AS tax_id, trans.type AS order_type"
                        .", de.quantity AS quantity"
                            .", CASE de.debtor_trans_type WHEN ".ST_CUSTCREDIT." then 'CCN' ELSE 'S' END AS type, de.debtor_trans_type AS trans_type"
                                ;

        $this->db->select($select,false);

        $this->db->join('debtor_trans AS trans', 'trans.trans_no = de.debtor_trans_no AND trans.type = de.debtor_trans_type', 'left');
        $this->db->where('trans.ov_amount >',0);
        // 	    $this->trans->where_in("trans.type",array(ST_SALESINVOICE,ST_CUSTCREDIT));
        if( $from ){
            $this->db->where('trans.tran_date >=',$from);
        }
        if( $to ){
            $this->db->where('trans.tran_date <=',$to);
        }
        $this->db->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');

        $this->db->join('stock_master AS stock', 'stock.stock_id= de.stock_id', 'left');
        $this->db->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');

        $this->db->where(array('de.unit_price >'=>0,'de.quantity <>'=>0));
        $this->db->where_in("de.debtor_trans_type",array(ST_SALESINVOICE,ST_CUSTCREDIT));
        if( $tax_id ){
            $this->db->where('de.tax_type_id',$tax_id);
        } else {
            $this->db->where('de.tax_type_id IS NOT NULL');
            $this->db->where('de.tax_type_id >',0);
        }

        $this->db->order_by('de.debtor_trans_no','ASC');
        //         $this->trans->order_by('trans.tran_date','ASC');
        $items = $this->db->get('debtor_trans_details AS de')->result();

        return $items;
    }

    function gst_grouping_from_trans_tax($from,$to,$tax_id=0){
        $select = 'tax_trans.id AS id_current, de.id, de.stock_id AS item_code, tax_trans.tax_type_id, de.description AS item_name, de.debtor_trans_no AS trans_no, de.unit_price, de.discount_percent'
            .',trans.reference, trans.tran_date, trans.type AS order_type, saletype.tax_included, trans.rate AS curr_rate'
                .', deb.name AS customername, deb.curr_code AS currence'
                    .",trans.type AS order_type"
                    //             .",de.tax_type_id AS tax_id"
        .",tax_trans.tax_type_id , tax_trans.tax_type_id AS tax_id"
            .",de.quantity, de.quantity AS qty"
                .", CASE trans.type WHEN ".ST_CUSTCREDIT." then 'CCN' ELSE 'S' END AS type"
                    .""
                        ;
                        ;

        $this->db->select($select, false);

        /* not get emtpy transaction */
        $this->db->where(array('de.unit_price >'=>0,'de.quantity <>'=>0));
        $this->db->join('debtor_trans AS trans', 'trans.trans_no= tax_trans.trans_no AND trans.type = tax_trans.trans_type', 'left');
        $this->db->where('tax_trans.net_amount >',0);
        $this->db->join('debtor_trans_details AS de', 'de.debtor_trans_no = trans.trans_no AND de.debtor_trans_type = tax_trans.trans_type AND ( de.tax_type_id IS NULL OR de.tax_type_id =0 )', 'left');

        $this->db->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');
        $this->db->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');
        if( $from ){
            $this->db->where('tax_trans.tran_date >=',$from);
        }
        if( $to ){
            $this->db->where('tax_trans.tran_date <=',$to);
        }
        if( $tax_id ){
            $this->db->where('tax_trans.tax_type_id',$tax_id);
        } else {
            $this->db->where('tax_trans.tax_type_id IS NOT NULL');
            $this->db->where('tax_trans.tax_type_id >',0);
        }
        $this->db->where_in("tax_trans.trans_type",array(ST_SALESINVOICE,ST_CUSTCREDIT));

        $this->db->order_by('tax_trans.trans_no','ASC');
        $this->db->order_by('tax_trans.tran_date','ASC');
        $items = $this->db->get('trans_tax_details AS tax_trans')->result();


        return $items;
    }

    function gst_grouping_baddebt($from=null,$to=null){

        // 		$invoices = $this->trans->select()->where(array('step'=>2,'type'=>ST_SALESINVOICE))->get('bad_debts')->result();

        $select = 'de.id, de.stock_id AS item_code, de.description AS item_name, de.debtor_trans_no AS trans_no, de.unit_price, de.discount_percent'
            .',trans.reference, DATE(bad.tran_date) AS tran_date, bad.type AS order_type,'
                .' saletype.tax_included, trans.rate AS curr_rate'
                    .', deb.name AS customername, deb.curr_code AS currence, deb.msic, deb.debtor_no'
                        .", 25 AS tax_id"
                            .", de.quantity AS quantity, de.debtor_trans_type AS trans_type"
                                .", 'SBD'AS type"
                                    ;

        $this->db->select($select,false);

        $this->db->join('debtor_trans AS trans', 'trans.trans_no = bad.type_no AND trans.type = bad.type', 'left');
        $this->db->join('debtor_trans_details AS de', 'de.debtor_trans_no= trans.trans_no AND trans.type = bad.type', 'left');
        $this->db->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');
        $this->db->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');
        if( $from ){
            $this->db->where('DATE(bad.tran_date) >=',$from);
        }
        if( $to ){
            $this->db->where('DATE(bad.tran_date) <=',$to);
        }
        $this->db->where('bad.type',ST_SALESINVOICE);
        $this->db->where('bad.type_no NOT IN ( SELECT type_no FROM bad_debts WHERE step =2 AND type='.ST_SALESINVOICE.' ) ');
        $data = $this->db->group_by('bad.type_no')->get('bad_debts AS bad')->result();

        return $data;
    }
}