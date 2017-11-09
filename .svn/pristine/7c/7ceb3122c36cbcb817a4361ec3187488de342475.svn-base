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

    function get_customer_trans($tran_id, $tran_type,$stat_time='',$end_time='',$reference='') {
        global $go_debug;

        $this->db->from('debtor_trans AS trans');
        $this->db->select('trans.*');
        $this->db->select('trans.ov_amount+trans.ov_gst+trans.ov_freight+trans.ov_freight_tax+trans.ov_discount AS Total',false);



        $this->db->join("comments AS com","trans.type=com.type AND trans.trans_no=com.id",'LEFT')->select('com.memo_');

        $this->db->join("debtors_master cust","cust.debtor_no=trans.debtor_no",'LEFT');
        $this->db->select('cust.name AS DebtorName, cust.address,cust.curr_code,cust.tax_id');

        if ($tran_type == ST_CUSTPAYMENT || $tran_type == ST_BANKDEPOSIT) {
            // it's a payment so also get the bank account
            // Added bank_act to support Customer Payment Edit

            $this->select('bank_act');
            $this->db->join('bank_trans AS bank',"bank.trans_no =$tran_id AND bank.type=$tran_type AND bank.amount != 0",'LEFT')->select('bank.amount as bank_amount');

            $this->db->join('bank_accounts AS bankAcc',"bankAcc.id=bank.bank_act",'LEFT');
            $this->db->select('bankAcc.bank_name, bankAcc.bank_account_name, bankAcc.account_type AS BankTransType, bankAcc.bank_curr_code');
        }

        if ($tran_type == ST_SALESINVOICE || $tran_type == ST_CUSTCREDIT || $tran_type == ST_CUSTDELIVERY) {
            // it's an invoice so also get the shipper and salestype

            $this->db->join('shippers AS ship',"ship.shipper_id=trans.ship_via",'LEFT');
            $this->db->select('ship.shipper_name');

            $this->db->join('cust_branch AS branch',"branch.branch_code = trans.branch_code",'LEFT')->select('branch.*');
//             $this->db->where("branch.branch_code = trans.branch_code");

            $this->db->join("tax_groups","tax_groups.id=branch.tax_group_id",'LEFT');
            $this->db->select("tax_groups.name AS tax_group_name, tax_groups.id AS tax_group_id");

            $this->db->join("sales_types","sales_types.id = trans.tpe",'LEFT');
            $this->db->select("sales_types.sales_type, sales_types.tax_included");



        }


        $time_filter = false;
        if( $stat_time ){
            $time_filter = true;
            $this->db->where('trans.tran_date >=',date2sql($stat_time));
        }
        if( $end_time ){
            $time_filter = true;
            $this->db->where('trans.tran_date <=',date2sql($end_time));
        }

        if( $reference ){
            $end_time = false;
            $this->db->where("trans.reference",$reference);
        } elseif( !$time_filter ){
            $this->db->where( array("trans.trans_no"=>$tran_id,'trans.type'=>$tran_type) );
        }

        $tran = $this->db->get();

//         $result = db_query($sql, "Cannot retreive a debtor transaction");

        if ( $tran->num_rows() <= 0) {
            // can't return nothing
            if($go_debug)
                display_backtrace();
            //display_db_error("no debtor trans found for given params", $sql, true); exit;

        } else if ( $tran->num_rows() > 1) {
            // can't return multiple
            if($go_debug)
                display_backtrace();
            display_db_error("duplicate debtor transactions found for given params", $sql, true);
            exit;
        }

        return $tran->row_array();
    }

    /*
     * common function
     */
    function search_order($trans_type=NULL,$trans_no = NULL, $reference=NULL , $date_from =NULL, $date_to = NULL){

        $this->db->select('sorder.order_no, sorder.reference, debtor.name, branch.br_name, sorder.ord_date, sorder.delivery_date, sorder.deliver_to');
//         $this->db->select('sorder.order_no, sorder.reference, debtor.name, branch.br_name, sorder.ord_date, sorder.delivery_date');
//         $this->db->select('sorder.order_no, sorder.reference, debtor.name, branch.br_name, sorder.ord_date, sorder.delivery_date');
        $this->db->from('sales_orders as sorder');
        $this->db->join('sales_order_details as line','line.order_no=sorder.order_no AND line.trans_type=sorder.trans_type');

        $this->db->join('debtors_master as debtor','debtor.debtor_no=sorder.debtor_no');
        $this->db->join('cust_branch as branch','branch.branch_code=sorder.branch_code AND branch.debtor_no=debtor.debtor_no');

        if( $trans_type ){
            $this->db->where('sorder.trans_type',$trans_type);
        }


        if (isset($trans_no) && $trans_no != ""){
            $this->db->like('sorder.order_no',$trans_no);
        }
        if (isset($reference) && $reference != ""){
            $this->db->like('sorder.reference',$reference);
        }
        if( $date_from && is_date($date_from) ){
            $this->db->where('sorder.ord_date >=',date2sql($date_from));
        }
        if( $date_to && is_date($date_to) ){
            $this->db->where('sorder.ord_date <=',date2sql($date_to));
        }

//         $this->db->group_by('sorder.order_no, sorder.debtor_no, sorder.branch_code, sorder.customer_ref, sorder.ord_date, sorder.deliver_to');
        $db2 = clone $this->db;
        $data = array('total'=>$db2->count_all_results(),'items'=>array() );
//         bug($total->count_all_results());
        $result = $this->db->limit(page_padding_limit)->get();
        if( $result->num_rows < 1 ){

        } else {
            $data['items'] =$result->result();
        }
        return $data;

        //                 ." AND sorder.ord_date <= '$date_before'";

//         $sql = "SELECT
// 			,"
//             .($filter=='InvoiceTemplates'
//                 || $filter=='DeliveryTemplates' ?
//                 "sorder.comments, " : "sorder.customer_ref, ")

//                 ."sorder.ord_date,
// 			sorder.delivery_date,
// 			sorder.deliver_to,
// 			Sum(line.unit_price*line.quantity*(1-line.discount_percent))+freight_cost AS OrderValue,
// 			sorder.type,
// 			debtor.curr_code,
// 			Sum(line.qty_sent) AS TotDelivered,
// 			Sum(line.quantity) AS TotQuantity

// 		FROM ".TB_PREF."sales_orders as sorder, "
//         		    .TB_PREF."sales_order_details as line, "
//         		        .TB_PREF.", "
//         		            .TB_PREF."
// 			WHERE
// 			AND  = ".db_escape($trans_type)."
// 			AND
// 			AND
// 			AND ";


//         else	// ... or select inquiry constraints
//         {
//             if ($filter!='DeliveryTemplates' && $filter!='InvoiceTemplates' && $filter!='OutstandingOnly')
//             {
//                 $date_after = date2sql($from);
//                 $date_before = date2sql($to);

//                 $sql .=  " AND sorder.ord_date >= '$date_after'"
//                 ." AND sorder.ord_date <= '$date_before'";
//         }
//         }
//         if ($trans_type == ST_SALESQUOTE && !check_value('show_all'))
//             $sql .= " AND sorder.delivery_date >= '".date2sql(Today())."' AND line.qty_sent=0"; // show only outstanding, not realized quotes

//         if ($selected_customer != -1)
//             $sql .= " AND sorder.debtor_no=".db_escape($selected_customer);

//         if (isset($stock_item))
//             $sql .= " AND line.stk_code=".db_escape($stock_item);

//         if ($location)
//             $sql .= " AND sorder.from_stk_loc = ".db_escape($location);

//         if ($filter=='OutstandingOnly')
//             $sql .= " AND line.qty_sent < line.quantity";

//         elseif ($filter=='InvoiceTemplates' || $filter=='DeliveryTemplates')
//         $sql .= " AND sorder.type=1";

//         //Chaiatanya : New Filter
//         if ($customer_id != ALL_TEXT)
//             $sql .= " AND sorder.debtor_no = ".db_escape($customer_id);

//
//         return $sql;
    }

    function remove_trans_detail($trans_type, $trans_no){
        $this->db->reset();
        if ($trans_no != 0 ){
            $this->db->delete('debtor_trans_details',array('debtor_trans_no'=>$trans_no,'debtor_trans_type'=>$trans_type));
        }
        return true;
    }

    /*
     * update write_customer_trans() in cust_trans_db.inc
     * 150513
     * quannh
     */
    function write_trans($trans_type, $trans_no, $data){

        if( !isset($data['debtor_no']) )
            return false;


        if( isset($data['tran_date']) ){
            $data['tran_date'] = date2sql($data['tran_date']);
        } else {
            $data['tran_date'] = '0000-00-00';
        }


        if( isset($data['due_date']) ){
            $data['due_date'] = date2sql($data['due_date']);
        } else {
            $data['due_date'] = '0000-00-00';
        }

        if ( $trans_type == ST_BANKPAYMENT)
            $data['ov_amount'] = -abs($data['ov_amount']);

        if ( !isset($data['rate']) || $data['rate'] == 0) {
            $curr = get_customer_currency($data['debtor_no']);
            $data['rate'] = get_exchange_rate_from_home_currency($curr, $data['tran_date']);
        }


        if( !isset($data['ov_discount']) ) {
            $data['ov_discount'] = 0;
        }
        if( !isset($data['ov_gst']) ) {
            $data['ov_gst'] = 0;
        }
        if( !isset($data['ov_freight_tax']) ) {
            $data['ov_freight_tax'] = 0;
        }

        $new = 0;
        $this->db->reset();
        if( !exists_customer_trans($trans_type, $trans_no) ){
            $trans_no = get_next_trans_no($trans_type);
            $data['trans_no'] = $trans_no;
            $data['type'] = $trans_type;
            $sql = $this->db->insert('debtor_trans',$data,true );
        } else {
            $sql = $this->db->update('debtor_trans',$data,array('trans_no'=>$trans_no,'type'=>$trans_type),1,true );
        }

        db_query($sql, "The debtor transaction record could not be inserted");
        add_audit_trail($trans_type, $trans_no, $data['tran_date'], $new ? '': _("Updated."));

        return $trans_no;
    }

    function update_trans($trans_type, $trans_no, $data){

        if( isset($data['tran_date']) ){
            $data['tran_date'] = date2sql($data['tran_date']);
        }


        if( isset($data['due_date']) ){
            $data['due_date'] = date2sql($data['due_date']);
        }

        if ( array_key_exists('rate', $data) && $data['rate'] == 0 ) {
            $curr = get_customer_currency($data['debtor_no']);
            $data['rate'] = get_exchange_rate_from_home_currency($curr, $data['tran_date']);
        }

        $this->db->reset();

        $sql = $this->db->update('debtor_trans',$data,array('trans_no'=>$trans_no,'type'=>$trans_type),1,true );

        db_query($sql, "The debtor transaction record could not be inserted");

        return $trans_no;
    }

    function write_trans_detail($trans_type, $trans_no=0,$data,$check_existed=true){
        $data['debtor_trans_type'] = $trans_type;

        if( !$data['discount_percent'] ) {
            $data['discount_percent'] = 0;
        }
        if ( $check_existed && $trans_no != 0 && $this->trans_detail_exist($trans_no,$trans_type) ){
            return true;
        } else {
            $this->db->reset();
            $data['debtor_trans_no'] = $trans_no;
            $sql = $this->db->insert('debtor_trans_details',$data,true );

        }

        db_query($sql, "The debtor transaction detail could not be written");
        return true;
    }

    function update_trans_detail($trans_type, $id=0,$data){
        $this->db->reset();
        if( $data ){
            foreach ($data AS $field=>$val){
                $this->db->set($field,$val,false);
            }
        }

        $sql = $this->db->update('debtor_trans_details',null,array('id'=>$id),1,true );
        db_query($sql, "The parent document detail record could not be updated");
        return true;
    }

    private function trans_detail_exist($trans_no,$trans_type=0,$stock_id=0){
        $this->db->reset();
        $this->db->where('debtor_trans_type',$trans_type);
        $this->db->where('debtor_trans_no',$trans_no);
        $row = $this->db->get('debtor_trans_details')->row();
        if( $row && isset($row->id) ){
            return true;
        }
        return false;

    }

    function get_open_balance($debtorno, $to, $date_max=NULL){
        if($to)
            $to = date2sql($to);


        $trans_type = array();

	   $allocation_model = module_model_load('allocation','gl');
	   $alloc = $allocation_model->alloc_sum('t.trans_no','t.type', sql2date($date_max),'alloc_',array(ST_SALESINVOICE,ST_OPENING_CUSTOMER));

	   $total_amount = "((t.ov_amount + t.ov_gst + t.ov_freight + t.ov_freight_tax + t.ov_discount)*t.rate)";
       $credit_check =  "t.type = ".ST_CUSTCREDIT." OR t.type =".ST_CUSTPAYMENT." OR t.type=".ST_BANKDEPOSIT;


// 	   $this->db->select("SUM(IF(t.ov_amount > 0,$total_amount,0)) AS debit",false);
// 	   $this->db->select("SUM(IF(t.ov_amount < 0,$total_amount * -1,0)) AS credit",false);
	   $this->db->select("SUM(IF(t.type <> ".ST_CUSTCREDIT." AND t.type <> ".ST_CUSTPAYMENT." AND t.type <> ".ST_BANKDEPOSIT.",$total_amount,0)) AS debit",false);
	   $this->db->select("SUM(IF(t.type = ".ST_CUSTCREDIT." OR t.type =".ST_CUSTPAYMENT." OR t.type=".ST_BANKDEPOSIT.",$total_amount,0)) AS credit",false);

	   $this->db->select("SUM($total_amount*(IF($credit_check,-1,1)) ) AS balance",false);

// 	   $this->db->select('t.*');
	   $this->db->from("debtor_trans AS t");
	   $this->db->where('t.debtor_no',$debtorno);
	   $this->db->where("ABS(t.ov_amount) > 0");
	   $this->db->where('t.`trans_no` NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=t.type )');
// 	   $this->db->where_in('t.type',array(ST_SALESINVOICE,ST_BANKPAYMENT,ST_CUSTPAYMENT,ST_OPENING_CUSTOMER,ST_OPENING));
	   $this->db->where_not_in('t.type',array(ST_CUSTDELIVERY));
	   if( is_date($to) ){
	       $this->db->where('t.tran_date <',$to);
	   }

	   $result = $this->db->group_by('t.debtor_no')->get();


    	if( !is_object($result) ){
    	    check_db_error("No transactions were returned", $this->db->last_query() );
    	}
    	$data = $result->row();

    	if( empty($data) ){
    	    $data = (object)array('charges'=>0,'debit'=>0,'credits'=>0,'credit'=>0,'balance'=>0,'allocated'=>0,'out_standing'=>0);
    	}
    	return $data;

    }

    function get_transactions($debtorno, $from, $to){
        $from = date2sql($from);
        $to = date2sql($to);

        $allocation_model = module_model_load('allocation','gl');
        $alloc = $allocation_model->alloc_sum('tran.trans_no','tran.type', $to,'alloc_',array(ST_SALESINVOICE,ST_OPENING_CUSTOMER));

        $db = get_instance()->db;

        $db->select("($alloc) AS Allocated",FALSE);

        $db->select("tran.*")->from('debtor_trans AS tran');
        $db->select("(tran.ov_amount + tran.ov_gst + tran.ov_freight + tran.ov_freight_tax + tran.ov_discount)*tran.rate AS TotalAmount");
        $db->select("(tran.ov_amount + tran.ov_gst + tran.ov_freight + tran.ov_freight_tax + tran.ov_discount) AS amount_original");

        $db->select("( tran.type=".ST_SALESINVOICE." AND tran.due_date < '".$to."') AS OverDue ");

        if( is_date($from) ){
            $db->where("tran.tran_date >=",$from);
        }
        if( is_date($to) ){
            $db->where("tran.tran_date <=",$to);
        }

        $db->where(array('tran.debtor_no'=>$debtorno,'tran.type <>'=>ST_CUSTDELIVERY,'tran.ov_amount <>'=>0));
        $db->where("tran.`trans_no` NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=tran.type )");

        $query = $db->order_by('tran.tran_date ASC')->get();

        if( is_object($query) )
            return $query->result_array();
        else
            check_db_error("No transactions were returned", $query->result(), true);


        return db_query($db->last_query(),"No transactions were returned");
    }

}