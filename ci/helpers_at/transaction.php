<?php

if( !function_exists('get_systype_db_info') ) {
    function get_systype_db_info($type){
        switch ($type){
            case     ST_JOURNAL      : return array("".TB_PREF."gl_trans", "type", "type_no", null, "tran_date");
            case     ST_BANKPAYMENT  : return array("".TB_PREF."bank_trans", "type", "trans_no", "ref", "trans_date");
            case     ST_BANKDEPOSIT  : return array("".TB_PREF."bank_trans", "type", "trans_no", "ref", "trans_date");
            case     3               : return null;
            case     ST_BANKTRANSFER : return array("".TB_PREF."bank_trans", "type", "trans_no", "ref", "trans_date");

            case     ST_OPENING_CUSTOMER:
            case     ST_SALESINVOICE : return array("".TB_PREF."debtor_trans", "type", "trans_no", "reference", "tran_date");
            case     ST_CUSTCREDIT   : return array("".TB_PREF."debtor_trans", "type", "trans_no", "reference", "tran_date");
            case     ST_CUSTPAYMENT  : return array("".TB_PREF."debtor_trans", "type", "trans_no", "reference", "tran_date");
            case     ST_CUSTDELIVERY : return array("".TB_PREF."debtor_trans", "type", "trans_no", "reference", "tran_date");

            case     ST_LOCTRANSFER  : return array("".TB_PREF."stock_moves", "type", "trans_no", "reference", "tran_date");
            case     ST_INVADJUST    : return array("".TB_PREF."stock_moves", "type", "trans_no", "reference", "tran_date");
            case     ST_PURCHORDER   : return array("".TB_PREF."purch_orders", null, "order_no", "reference", "ord_date");

            case     ST_OPENING_SUPPLIER:
            case     ST_SUPPINVOICE  : return array("".TB_PREF."supp_trans", "type", "trans_no", "reference", "tran_date");
            case     ST_SUPPCREDIT   : return array("".TB_PREF."supp_trans", "type", "trans_no", "reference", "tran_date");
            case     ST_SUPPAYMENT   : return array("".TB_PREF."supp_trans", "type", "trans_no", "reference", "tran_date");
            case     ST_SUPPRECEIVE  : return array("".TB_PREF."grn_batch", null, "id", "reference", "delivery_date");
            case     ST_WORKORDER    : return array("".TB_PREF."workorders", null, "id", "wo_ref", "released_date");
            case     ST_MANUISSUE    : return array("".TB_PREF."wo_issues", null, "issue_no", "reference", "issue_date");
            case     ST_MANURECEIVE  : return array("".TB_PREF."wo_manufacture", null, "id", "reference", "date_");
            case     ST_SALESORDER   : return array("".TB_PREF."sales_orders", "trans_type", "order_no", "reference", "ord_date");
            case     31              : return array("".TB_PREF."service_orders", null, "order_no", "cust_ref", "date");
            case     ST_SALESQUOTE   : return array("".TB_PREF."sales_orders", "trans_type", "order_no", "reference", "ord_date");
            case	 ST_DIMENSION    : return array("".TB_PREF."dimensions", null, "id", "reference", "date_");
            case     ST_COSTUPDATE   :
            default                  :
                return array("".TB_PREF."gl_trans", "type", "type_no", null, "tran_date");

        }

        display_db_error("invalid type ($type) sent to get_systype_db_info", "", true);
    }
}


if ( !function_exists('get_next_trans_no') ) {
    function get_next_trans_no ($trans_type){
//         $ci = &get_instance();

//         $st = get_systype_db_info($trans_type);

//         if (!($st && $st[0] && $st[2])) {
//             // this is in fact internal error condition.
//             display_error('Internal error: invalid type passed to get_next_trans_no()');
//             return 0;
//         }

//         $ci->db->select('MAX('.$st[2].') AS last_no')->from(str_replace(TB_PREF, NULL, $st[0]));
//         if ($st[1] != null){
//             $ci->db->where($st[1],$trans_type);
//         }
//         $sql1 = $ci->db->query_compile();


//         // check also in voided transactions (some transactions like location transfer are removed completely)
//         $ci->db->where('type',$trans_type)->select('MAX(id) AS last_no')->from('voided');
//         $sql2 = $ci->db->query_compile();


//         $ci->db->select('MAX(last_no) AS last_no',false);
//         $result = $ci->db->get("($sql1 UNION $sql2) AS a");
//         $data = $result->row();
// //         bug($ci->db->last_query() );
// //         bug($result); die('quannh');
// //         $sql = "SELECT max(last_no) last_no FROM ($sql1 UNION $sql2) a";
// //         $result = db_query($sql,"The next transaction number for $trans_type could not be retrieved");
// //         $myrow = db_fetch_row($result);

//         return $data->last_no + 1;


        	$st = get_systype_db_info($trans_type);

        	if (!($st && $st[0] && $st[2])) {
        		// this is in fact internal error condition.
        		display_error('Internal error: invalid type passed to get_next_trans_no()');
        		return 0;
        	}
        	$sql1 = "SELECT MAX(`$st[2]`) as last_no FROM $st[0]";
        	if ($st[1] != null)
            		 $sql1 .= " WHERE `$st[1]`=".db_escape($trans_type);

            	// check also in voided transactions (some transactions like location transfer are removed completely)
            	$sql2 = "SELECT MAX(`id`) as last_no FROM ".TB_PREF."voided WHERE `type`=".db_escape($trans_type);

            	$sql = "SELECT max(last_no) last_no FROM ($sql1 UNION $sql2) a";
                $result = db_query($sql,"The next transaction number for $trans_type could not be retrieved");
                $myrow = db_fetch_row($result);
            return $myrow[0] + 1;
    }
}

