<?php
class GST_Form_3_Model extends CI_Model {
    function __construct(){
        parent::__construct();

    }

    private function get_msic_code($stock_id_field="de.stock_id"){
        $this->db->join('stock_master AS stock', "stock.stock_id= $stock_id_field", 'left');
        $this->db->join('stock_category AS stockCate', 'stockCate.category_id= stock.category_id', 'left');
        //         $this->db->select("deb.msic");
        $this->db->select("stockCate.msic");
    }


    /*
     * sale transactions
     */

    function sale_trans($from,$to,$tax_id=0){

        $this->db->from("debtor_trans_details AS de");
        $this->db->select("de.id, de.unit_price, de.discount_percent");
        $this->db->select("
            de.stock_id AS item_code,
            de.description AS item_name,
            de.debtor_trans_no AS trans_no,
            de.quantity AS quantity,
            de.tax_type_id AS tax_id
            ");

        $this->db->select("CASE de.debtor_trans_type WHEN ".ST_CUSTCREDIT." then 'CCN' ELSE 'S' END AS type, de.debtor_trans_type AS trans_type",false);

        $this->db->join('debtor_trans AS trans', 'trans.trans_no = de.debtor_trans_no AND trans.type = de.debtor_trans_type', 'left');
        $this->db->select("trans.reference, trans.tran_date, trans.type AS order_type, trans.rate AS curr_rate,  trans.type AS order_type");

        $this->db->where('trans.ov_amount >',0);
        // 	    $this->db->where_in("trans.type",array(ST_SALESINVOICE,ST_CUSTCREDIT));
        if( $from ){
            $this->db->where('trans.tran_date >=',$from);
        }
        if( $to ){
            $this->db->where('trans.tran_date <=',$to);
        }
        $this->db->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');
        $this->db->select("deb.name AS customername, deb.curr_code AS currence, deb.debtor_no");
        $this->db->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');
        $this->db->select("saletype.tax_included");

        $this->get_msic_code("de.stock_id");

        $this->db->where(array('de.unit_price >'=>0,'de.quantity <>'=>0));
        $this->db->where_in("de.debtor_trans_type",array(ST_SALESINVOICE,ST_CUSTCREDIT));
        if( $tax_id ){
            $this->db->where('de.tax_type_id',$tax_id);
        } else {
            $this->db->where('de.tax_type_id IS NOT NULL');
            $this->db->where('de.tax_type_id >',0);
        }

        $this->db->order_by('de.debtor_trans_no','ASC');
        //         $this->db->order_by('trans.tran_date','ASC');
        $query = $this->db->get();
        if( !is_object($query) ){
            check_db_error('<br> DB error ', $this->db->last_query());
            return array();
        }

        return $query->result();
    }

    function sale_trans_from_trans_tax($from,$to,$tax_id=0){

            $this->db->from("trans_tax_details AS tax_trans");
            $this->db->select("tax_trans.tax_type_id, tax_trans.id AS id_current");
            $this->db->select("tax_trans.tax_type_id , tax_trans.tax_type_id AS tax_id");


            // 	    $this->db->join('tax_types AS tax', 'tax.id= inv.tax_type_id', 'left');
            $this->db->join('debtor_trans AS trans', 'trans.trans_no= tax_trans.trans_no AND trans.type = tax_trans.trans_type', 'left');
            $this->db->select("trans.reference, trans.tran_date, trans.type AS order_type, trans.rate AS curr_rate, trans.type AS order_type");
            $this->db->select("CASE trans.type WHEN ".ST_CUSTCREDIT." then 'CCN' ELSE 'S' END AS type", false);


            $this->db->where('tax_trans.net_amount >',0);
            //$this->db->where_in("tax_trans.trans_type",array(ST_SALESINVOICE,ST_CUSTCREDIT));
            $this->db->join('debtor_trans_details AS de', 'de.debtor_trans_no = trans.trans_no AND de.debtor_trans_type = tax_trans.trans_type AND ( de.tax_type_id IS NULL OR de.tax_type_id =0 )', 'left');

            /* not get emtpy transaction */
            $this->db->where(array('de.unit_price >'=>0,'de.quantity <>'=>0));

            $this->db->select("de.id, de.unit_price, de.discount_percent");
            $this->db->select("
                de.stock_id AS item_code,
                de.description AS item_name,
                de.debtor_trans_no AS trans_no,
                de.quantity,
                de.quantity AS qty
            ");
            $this->get_msic_code("de.stock_id");

            $this->db->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left');
            $this->db->select(" deb.name AS customername, deb.curr_code AS currence");
            $this->db->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');
            $this->db->select("saletype.tax_included");

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


            //$this->db->join('debtors_master AS deb', 'deb.debtor_no= trans.supplier_id', 'left');

            $this->db->order_by('tax_trans.trans_no','ASC');
            $this->db->order_by('tax_trans.tran_date','ASC');
            $items = $this->db->get();
            if( !is_object($items) ){
                bug($this->db->last_query());
                die;
            }

            return $items->result();
    }


    function sale_trans_baddebt($from=null,$to=null){

        // 		$invoices = $this->db->select()->where(array('step'=>2,'type'=>ST_SALESINVOICE))->get('bad_debts')->result();


        $this->db->select("25 AS tax_id, 'SBD'AS type",false);

        $this->db->from("bad_debts AS bad")
                    ->select("bad.type AS order_type, DATE(bad.tran_date) AS tran_date");



        $this->db->join('debtor_trans AS trans', 'trans.trans_no = bad.type_no AND trans.type = bad.type', 'left')
                    ->select("trans.reference, trans.rate AS curr_rate");

        $this->db->join('debtor_trans_details AS de', 'de.debtor_trans_no= trans.trans_no AND trans.type = bad.type', 'left');
        $this->db->select("
            de.id, de.unit_price, de.discount_percent,
            de.quantity AS quantity,
            de.debtor_trans_type AS trans_type,
            de.stock_id AS item_code,
            de.description AS item_name,
            de.debtor_trans_no AS trans_no
        ");

        $this->db->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left')
                    ->select("saletype.tax_included");

        $this->db->join('debtors_master AS deb', 'deb.debtor_no= trans.debtor_no', 'left')
                    ->select("deb.name AS customername, deb.curr_code AS currence, deb.debtor_no");

        $this->get_msic_code("de.stock_id");

        if( $from ){
            $this->db->where('DATE(bad.tran_date) >=',$from);
        }
        if( $to ){
            $this->db->where('DATE(bad.tran_date) <=',$to);
        }
        $this->db->where('bad.type',ST_SALESINVOICE);
        $this->db->where('bad.type_no NOT IN ( SELECT type_no FROM bad_debts WHERE step =2 AND type='.ST_SALESINVOICE.' ) ');
        $data = $this->db->group_by('bad.type_no')->get()->result();

        return $data;
    }


    /*
     * Purchase Transactions
     */

    function purchase_trans($from,$to,$tax_id=0,$not_in_ids = array() ){

        $this->db->select("0 AS tax_included, 0 AS discount_percent", FALSE);

        $this->db->from("supp_invoice_items AS inv")
                ->select("inv.id, inv.gl_code, inv.supp_trans_no AS trans_no, inv.unit_price,  inv.tax_type_id AS tax_id");
        $this->db->select("IF(inv.quantity = 0, 1, inv.quantity) as quantity",FALSE);
        $this->db->select("IF (inv.gl_code IS NOT NULL AND inv.gl_code != 0 ,inv.gl_code, inv.stock_id ) AS item_code",FALSE);
        $this->db->select("IF (
                        inv.gl_code IS NOT NULL AND inv.gl_code != 0,
                        (SELECT account_name FROM chart_master WHERE account_code = inv.gl_code),
                        (SELECT description FROM stock_master WHERE stock_id = inv.stock_id)
            ) AS item_name",FALSE);
        $this->get_msic_code("inv.stock_id");



        $this->db->join('supp_trans AS trans', 'trans.trans_no= inv.supp_trans_no AND trans.type=inv.supp_trans_type', 'left')
                ->select("trans.reference, trans.tran_date, trans.type AS order_type, trans.fixed_access")
        ;
        $this->db->select("CASE trans.type WHEN ".ST_SUPPCREDIT." then 'SCN' ELSE 'P' END AS type",FALSE);
        $this->db->select("IF (trans.rate <=0, 1, trans.rate ) AS curr_rate",FALSE);

        $this->db->where('trans.ov_amount !=',0);
        $this->db->where_in("trans.type",array(ST_SUPPINVOICE,ST_SUPPCREDIT));

        if( $from ){
            $this->db->where('trans.tran_date >=',$from);
        }
        if( $to ){
            $this->db->where('trans.tran_date <=',$to);
        }

        $this->db->join('suppliers AS supp', 'supp.supplier_id= trans.supplier_id', 'left')
                    ->select("supp.supp_name AS supp_name, supp.curr_code AS currence");


        /* not get emtpy transaction */
        $this->db->where('inv.unit_price !=',0);

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


        $query = $this->db->get();
        if( is_object($query) ){
            return $query->result();
        } else {
            bug($this->db->last_query() );die;
        }
    }

    function purchase_trans_from_trans_tax($from,$to,$tax_id=0){


        $this->db->select(" 0 AS discount_percent, 0 AS tax_included", FALSE);

        $this->db->select("tax_trans.tax_type_id AS tax_id, inv.tax_type_id");

        $this->db->from("trans_tax_details AS tax_trans")
                ->select("tax_trans.id AS item_id, tax_trans.ex_rate AS curr_rate, tax_trans.trans_type AS order_type, tax_trans.tran_date");

        $this->db->select("CASE tax_trans.trans_type WHEN ".ST_SUPPCREDIT." then 'SCN' ELSE 'P' END AS type",FALSE);





        $this->db->join('supp_trans AS trans', 'trans.trans_no= tax_trans.trans_no', 'left')
                    ->select("trans.reference AS reference, trans.fixed_access");

// 	    $this->db->join('supp_invoice_items AS inv', 'trans.trans_no= inv.supp_trans_no AND ( inv.tax_type_id IS NULL OR  inv.tax_type_id = 0) AND inv.supp_trans_type = tax_trans.trans_type', 'left');
        $this->db->join('supp_invoice_items AS inv', 'trans.trans_no= inv.supp_trans_no AND ( inv.tax_type_id IS NULL OR  inv.tax_type_id = 0) AND inv.supp_trans_type = tax_trans.trans_type', 'left')
                    ->select("inv.id, inv.unit_price, inv.stock_id AS item_code, inv.description AS item_name, inv.supp_trans_no AS trans_no")
                    ->select("inv.quantity, inv.quantity AS qty");
        $this->get_msic_code("inv.stock_id");

        $this->db->join('debtors_master AS deb', 'deb.debtor_no= trans.supplier_id', 'left')
                    ->select("deb.name AS customername, deb.curr_code AS currence");

        /* not get emtpy transaction */
        $this->db->where(array('inv.unit_price !='=>0,'inv.quantity !='=>0));

        $this->db->where('tax_trans.net_amount !=',0);
        $this->db->where_in("tax_trans.trans_type",array(ST_SUPPINVOICE,ST_SUPPCREDIT));

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


        $query = $this->db->group_by('inv.id')->get();

        if( is_object($query) ){
            return $query->result();
        } else {
            bug($this->db->last_query() );die;
        }
    }

    function purchase_trans_baddebt($from=null,$to=null){

        $this->db->from("bad_debts AS bad");
        $this->db->select("0 AS discount_percent, 0 AS tax_included, 'PBD' AS type",false);

        $this->db->join('supp_trans AS trans', 'trans.trans_no= bad.type_no AND trans.type=bad.type', 'left')
                ->select("trans.reference, trans.tran_date, trans.type AS order_type, trans.fixed_access")
                ->select(" IF (trans.rate <=0, 1, trans.rate ) AS curr_rate",FALSE)
        ;
        $this->db->join('supp_invoice_items AS inv', 'inv.supp_trans_no= trans.trans_no AND inv.supp_trans_type = bad.type', 'left')
                ->select("inv.id, inv.stock_id AS item_code, inv.description AS item_name, inv.supp_trans_no AS trans_no, inv.unit_price, inv.tax_type_id AS tax_id")
                ->select("IF(inv.quantity = 0, 1, inv.quantity) as quantity",FALSE)
        ;
        // 		$this->db->join('sales_types as saletype', 'saletype.id = trans.tpe', 'left');
        $this->db->join('suppliers AS supp', 'supp.supplier_id= trans.supplier_id', 'left')
                ->select("supp.supp_name AS supp_name, supp.curr_code AS currence");

        if( $from ){
            $this->db->where('DATE(bad.tran_date) >=',$from);
        }
        if( $to ){
            $this->db->where('DATE(bad.tran_date) <=',$to);
        }
        $this->db->where('bad.type',ST_SUPPINVOICE);
        $this->db->where('bad.type_no NOT IN ( SELECT type_no FROM bad_debts WHERE step =2 AND type='.ST_SUPPINVOICE.' ) ');
        $data = $this->db->group_by('bad.type_no')->get()->result();

        return $data;
    }


    /*
     * Bank Transactions
     */
    function bank_trans($from,$to,$tax_id=0){
        $select = 'de.id, NULL AS item_code, NULL AS item_name, de.type AS trans_type, de.trans_no AS trans_no, ABS(de.amount) AS unit_price, 1 AS quantity, 0 AS discount_percent'
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
        $this->db->select('trans.imported_goods_invoice, de.account_code',false);
        $this->db->join('bank_trans AS trans', 'trans.trans_no= de.trans_no AND trans.type= de.type', 'left');

        if( $from ){
            $this->db->where('trans.trans_date >=',$from);
        }
        if( $to ){
            $this->db->where('trans.trans_date <=',$to);
        }
        $this->db->where('trans.amount <>',0);
        // 	    $this->db->where("trans.type",ST_BANKPAYMENT);
        $this->db->where_in("trans.type",array(ST_BANKPAYMENT,ST_BANKDEPOSIT));


        $this->db->where(array('de.amount <>'=>0));
        if( $tax_id ){
            $this->db->where('de.tax',$tax_id);
        } else {
            $this->db->where('de.tax IS NOT NULL');
            $this->db->where('de.tax >',0);
        }

        $this->db->where('de.trans_no NOT IN (SELECT id FROM voided WHERE type=de.type )');

        $query = $this->db->get('bank_trans_detail AS de');
        if( !is_object($query) ){
            //             bug($items);
                        bug( $this->db->last_query() ); die;

        }
        //         bug( $this->db->last_query() );
        //         bug( $items );die;
        return $query->result();
    }
}