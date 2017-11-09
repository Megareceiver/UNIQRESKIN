<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Admin_Fiscalyear_Model extends CI_Model {
    function __construct(){
        parent::__construct();


    }

    function add($data = array()) {
        $data['begin'] = date2sql( $data['begin'] );
        $data['end'] = date2sql( $data['end'] );

        $this->db->insert('fiscal_year',$data);
        $id = $this->db->insert_id();

        $current_year = get_current_fiscalyear();
        if( !$current_year ){
            update_company_prefs(array('f_year'=>$id));
        }
        log_add('fiscal_year',1,$id);
        if( !$id ){
            display_error( _("could not add fiscal year"));
            return false;
        }
        return $id;
    }

    function update($id,$data=array()){
        if( !$id || !is_array($data) )
            return false;
        $this->db->update('fiscal_year',$data,array('id'=>$id));
//         bug($this->db->last_query());
        return true;

    }

    function items(){
        $this->db->select("IF(closed = 1, 'Yes', 'No' ) AS closed_text",false);
        $result = $this->db->select('fiscal_year.*')->order_by('begin')->get('fiscal_year');
        if( $result->num_rows > 0 ){
            return $result->result();
        } else {
            return array();
        }
    }

    function check_years_before($date, $closed=false){
        if (!$closed) {
            $this->db->where('closed',0);
        }

        $result = $this->db->where('begin <',date2sql($date))->get('fiscal_year');
        if( is_object($result) ){
            return ( count($result->result()) > 0);
        } else {
            display_error( _("could not check fiscal years before"));
        }
        return false;
    }


    function get_fiscalyear($id=0) {
        if( !$id ) {
            display_error( _("could not get fiscal year without ID"));
            return false;
        }

        $result = $this->db->where('id',$id)->get('fiscal_year');

        if( $result->num_rows > 0 ){
            return $result->row();
        } else {
            display_error( _("could not get fiscal year"));
        }
    }

    function close_year($year_id=0) {
	   $retained_earnings_act  = get_company_pref('retained_earnings_act');
	   $profit_loss_year_act   = get_company_pref('profit_loss_year_act');

	   if (get_gl_account($retained_earnings_act) == false || get_gl_account($profit_loss_year_act) == false){
	       display_error(_("The Retained Earnings Account or the Profit and Loss Year Account has not been set in System and General GL Setup"));
	       return false;
	   }

	   if (!is_account_balancesheet($retained_earnings_act) || is_account_balancesheet($profit_loss_year_act)) {
	       display_error(_("The Retained Earnings Account should be a Balance Account or the Profit and Loss Year Account should be an Expense Account (preferred the last one in the Expense Class)"));
	       return false;
        }

        $year_select = $this->get_fiscalyear($year_id);

        if( !$year_select || !isset($year_select->end ) ){
            display_error(_("Must select Fiscal Year"));
            return false;
        } else {
            $date_end = $year_select->end;
        }
//         $to = $myrow->end;

        //retrieve total balances from balance sheet accounts
        $this->db->select('SUM(amount) AS sum_amount',false)->from('gl_trans AS gl');
        $this->db->join('chart_master AS acc','acc.account_code = gl.account','INNER');
        $this->db->join('chart_types AS acc_type','acc_type.id = acc.account_type','INNER');

        $this->db->join('chart_class AS acc_class','acc_class.cid = acc_type.class_id','INNER');
        $this->db->where(array('acc_class.ctype >='=>CL_ASSETS,'acc_class.ctype <='=>CL_EQUITY));

        $result = $this->db->where('gl.tran_date <=',$date_end )->get();


        $balance = 0;
        if( is_object($result) ){
            $data = $result->row();
            $balance = floatval($data->sum_amount);
        } else {
            display_error( _("The total balance could not be calculated"));
            return;
        }

        if( $balance != 0 ){
            $trans_type = ST_JOURNAL;
    		$trans_id = get_next_trans_no($trans_type);

    		$gl_trans = new gl_trans();
            $gl_trans->set_value('type',$trans_type);
            $gl_trans->set_value('type_no',$trans_id);
            $gl_trans->set_value('tran_date',$date_end);

            $gl_trans->add_trans($retained_earnings_act,-$balance,0,0,_("Closing Year"),$rate=1);
            $gl_trans->add_trans($profit_loss_year_act, +$balance,0,0,_("Closing Year"),$rate=1);

            $gl_trans->do_gl_trans();


        }
        return true;

    }

    function open_year($id=0){
        $row = $this->get_fiscalyear($id);
        if( $row->begin && is_date($row->begin) ){
            $audit_trail_model = module_model_load('audit_trail','admin',true);
            $audit_trail_model->open_transactions($row->begin);
        }
    }

    function in_fiscalyears($date=NULL,$closed=0){
        if( !$date )
            return false;

        $date = date2sql($date);
        $result = $this->db->where(array('end >='=>$date,'begin <='=>$date,'closed'=>intval($closed)))->get('fiscal_year')->row();

        return count($result) > 0 ? true : false;;
    }

    function check_begin_end_date($from, $to) {
        $data = $this->db->select('MAX(end) AS max, MIN(begin) AS min',false)->get('fiscal_year')->row();

        if( count($data) < 1 || !$data->max || $data->min )
            return true;

        $max = add_days($data->max, 1);
        $min = add_days($data->min, -1);
        return (strtotime($max) === strtotime($from) || strtotime($min) === strtotime($to) );

    }


    function check_is_last($id=0){

        $year = $this->get_fiscalyear($id);
        $this->db->reset();
        $year_last = $this->db->select('MAX(end) AS end',false)->get('fiscal_year')->row();
        $year_first = $this->db->select('MIN(begin) AS begin',false)->get('fiscal_year')->row();

        return ( $year->end == $year_last->end || $year->begin == $year_first->begin ) ? true : false;
    }

    function trans_in_year($id=0){
        $year = $this->get_fiscalyear($id);
        $this->db->reset();
        $count = 0;

        if( empty($year) || !isset($year->begin) ) return $count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('ord_date >='=>$year->begin,'ord_date <='=>$year->end))->get('sales_orders')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('ord_date >='=>$year->begin,'ord_date <='=>$year->end))->get('purch_orders')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('delivery_date >='=>$year->begin,'delivery_date <='=>$year->end))->get('grn_batch')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end))->get('debtor_trans')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end))->get('supp_trans')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('released_date >='=>$year->begin,'released_date <='=>''))->get('workorders')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end))->get('stock_moves')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end))->get('gl_trans')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('trans_date >='=>$year->begin,'trans_date <='=>$year->end))->get('bank_trans')->row();
        $count += $data->count;

        $this->db->reset();
        $data = $this->db->select('COUNT(*) AS count')->where(array('gl_date >='=>$year->begin,'gl_date <='=>$year->end))->get('audit_trail')->row();
        $count += $data->count;

        return $count;

    }


    function delete($id=0,$remove_in_year=false) {
        if( !$id ) {
            display_error( _("could not delete fiscal year without ID"));
            return false;
        }
        $remove_in_year = true;

        $ref = _("Open Balance");
        $year = $this->get_fiscalyear($id);
        $to = $year->end;

        /*
         * remove Sale Order
         */
        $this->db->select('order_no, trans_type');
        if( $remove_in_year ){
            $this->db->where(array('ord_date >='=>$year->begin,'ord_date <='=>$year->end));
        } else {
            $this->db->where('ord_date <=',$year->end);
        }
        $this->db->where('type <>',1); // don't take the templates
        $sale_order = $this->db->get('sales_orders');

        if( !is_object($sale_order) ){
            display_error(_("Could not retrieve sales orders") );
        } elseif( $sale_order->num_rows > 0 ) {
            foreach ($sale_order->result() AS $order){
                $this->db->select('SUM(qty_sent) AS qty_sent, SUM(quantity) AS quantity',false);
                $this->db->where(array('order_no' => $order->order_no,'trans_type' => $order->trans_type));
                $sales_order_details = $this->db->get('sales_order_details');
                if( !is_object($sales_order_details) ){
                    display_error(_("Could not retrieve sales order details") );
                } else {
                    $sales_order_detail_check = $sales_order_details->row();
                    if( $sales_order_detail_check->qty_sent == $sales_order_detail_check->quantity){
                        $this->db->delete('sales_order_details', array('order_no' => $order->order_no, 'trans_type' => $order->trans_type));
                        $this->db->delete('sales_orders', array('order_no' => $order->order_no, 'trans_type' => $order->trans_type));
                        $this->delete_attachments_and_comments($order->trans_type, $order->order_no);
                    }
                }
            }
        }

        /*
         * remove Purchase Order
         */
        $this->db->select('order_no');
        $this->where_date($year,'ord_date');
        $purchase_order = $this->db->get('purch_orders');

        if( !is_object($purchase_order) ){
            display_error(_("Could not retrieve purchase orders") );
        } elseif( $purchase_order->num_rows > 0 ) {
            foreach ($purchase_order->result() AS $order){
                $this->db->select('SUM(quantity_ordered) AS qty_sent, SUM(quantity_received) AS quantity',false);
                $purchase_order_details = $this->db->where(array('order_no' => $order->order_no))->get('purch_order_details');
                if( !is_object($purchase_order_details) ){
                    display_error(_("Could not retrieve purchase order details") );
                } else {
                    $purchase_order_detail_check = $purchase_order_details->row();
                    if( $purchase_order_detail_check->qty_sent == $purchase_order_detail_check->quantity){
                        $this->db->delete('purch_order_details', array('order_no' => $order->order_no));
                        $this->db->delete('purch_orders', array('order_no' => $order->order_no));
                        $this->delete_attachments_and_comments(ST_PURCHORDER, $order->order_no);
                    }
                }
            }
        }


        /*
         * remove GL_batch
         */
        $this->where_date($year,'delivery_date');
        $grn_batch = $this->db->select('id')->get('grn_batch');

        if( !is_object($grn_batch) ){
            display_error(_("Could not retrieve grn batch") );
        } elseif( $grn_batch->num_rows > 0 ) {
            foreach ($grn_batch->result() AS $item){
                $this->db->delete('grn_items', array('grn_batch_id' => $item->id));
                $this->db->delete('grn_batch', array('id' => $item->id));
                $this->delete_attachments_and_comments(25, $item->id);
            }
        }



        /*
         * remove customer trans
         */
        $this->db->select('trans_no, type');
        $this->db->where('(ov_amount + ov_gst + ov_freight + ov_freight_tax + ov_discount) = alloc');
        if( $remove_in_year ){
            $this->db->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end));
        } else {
            $this->db->where('tran_date <=',$year->end);
        }

        $debtor_trans = $this->db->get('debtor_trans');
        if( !is_object($debtor_trans) ){
            display_error(_("Could not retrieve debtor trans") );
        } elseif( $debtor_trans->num_rows > 0 ) {
            foreach ($debtor_trans->result() AS $tran){
                if( $tran->type== ST_SALESINVOICE ){
                    $deliveries = get_sales_parent_numbers($tran->type, $tran->trans_no);
                    foreach ($deliveries as $delivery) {
                        $this->db->delete('debtor_trans_details', array('debtor_trans_no' => $delivery,'debtor_trans_type'=>ST_CUSTDELIVERY));
                        $this->db->delete('debtor_trans', array('trans_no' => $delivery,'type'=>ST_CUSTDELIVERY));
                        delete_attachments_and_comments(ST_CUSTDELIVERY, $delivery);
                    }
                }
                $this->db->delete('cust_allocations', array('trans_no_from' => $tran->trans_no,'trans_type_from'=>$tran->type));
                $this->db->delete('debtor_trans_details', array('debtor_trans_no' => $tran->trans_no,'debtor_trans_type'=>$tran->type));
                $this->db->delete('debtor_trans', array('trans_no' => $tran->trans_no,'type'=>$tran->type));
                $this->delete_attachments_and_comments($tran->type, $tran->trans_no);
            }
        }


        /*
         * remove supp_trans
         */
        $this->db->select('trans_no, type');
        $this->db->where('ABS(ov_amount + ov_gst + ov_discount) = alloc');
        if( $remove_in_year ){
            $this->db->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end));
        } else {
            $this->db->where('tran_date <=',$year->end);
        }

        $supp_trans = $this->db->get('supp_trans');
        if( !is_object($supp_trans) ){
            display_error(_("Could not retrieve supp trans") );
        } elseif( $supp_trans->num_rows > 0 ) {
            foreach ($supp_trans->result() AS $tran){
                $this->db->delete('supp_allocations', array('trans_no_from' => $tran->trans_no,'trans_type_from'=>$tran->type));
                $this->db->delete('supp_invoice_items', array('supp_trans_no' => $tran->trans_no,'supp_trans_type'=>$tran->type));
                $this->db->delete('supp_trans', array('trans_no' => $tran->trans_no,'type'=>$tran->type));
                $this->delete_attachments_and_comments($tran->type, $tran->trans_no);
            }

        }


        /*
         * remove workorders
         */
        $this->db->select('id')->where('closed',1);
        if( $remove_in_year ){
            $this->db->where(array('released_date >='=>$year->begin,'released_date <='=>$year->end));
        } else {
            $this->db->where('released_date <=',$year->end);
        }

        $workorders = $this->db->get('workorders');
        if( !is_object($workorders) ){
            display_error(_("Could not retrieve workorders") );
        } elseif( $workorders->num_rows > 0 ) {
            foreach ($workorders->result() AS $tran){
                $wo_issues = $this->db->select('issue_no')->where('workorder_id',$tran->id)->get('wo_issues');
                if( $wo_issues->num_rows > 0 ) foreach ($wo_issues->result() AS $issue){
                    $this->db->delete('wo_issue_items', array('issue_id' => $issue->issue_no));
                }
                delete_attachments_and_comments(ST_MANUISSUE, $tran->id);
                $this->db->delete('wo_issues', array('workorder_id' => $tran->id));
                $this->db->delete('wo_manufacture', array('workorder_id' => $tran->id));
                $this->db->delete('wo_requirements', array('workorder_id' => $tran->id));
                $this->db->delete('workorders', array('id' => $tran->id));
                $this->delete_attachments_and_comments(ST_WORKORDER, $tran->id);
            }

        }

        /*
         * remove stock_moves
         */
        $this->db->select('loc_code, stock_id, SUM(qty) AS qty, SUM(qty*standard_cost) AS std_cost',false);
        $this->db->group_by('loc_code, stock_id');
        if( $remove_in_year ){
            $this->db->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end));
        } else {
            $this->db->where('tran_date <=',$year->end);
        }
        $stock_moves = $this->db->get('stock_moves');
        if( !is_object($stock_moves) ){
            display_error(_("Could not retrieve stock_moves") );
        } elseif( $stock_moves->num_rows > 0 ) {
            foreach ($stock_moves->result() AS $tran){
                $this->db->where(array('loc_code'=>$tran->loc_code,'stock_id'=>$tran->stock_id));
                if( $remove_in_year ){
                    $this->db->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end));
                } else {
                    $this->db->where('tran_date <=',$year->end);
                }
                $this->db->delete('stock_moves');

                $stock_move_add = array(
                    'stock_id'=>$tran->stock_id,
                    'loc_code'=>$tran->loc_code,
                    'tran_date'=>$year->end,
                    'reference'=>$ref,
                    'qty'=>$tran->qty,
                    'standard_cost'=>($tran->qty == 0) ? 0 : number_total($tran->std_cost / $tran->qty)
                );
                $this->db->insert('stock_moves',$stock_move_add);

            }

        }


         /*
         * remove GL trans
         */
        $this->db->select('account, SUM(amount) AS amount',false);
        if( $remove_in_year ){
            $this->db->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end));
        } else {
            $this->db->where('tran_date <=',$year->end);
        }
        $gl_trans = $this->db->group_by('account')->get('gl_trans');
        if( !is_object($gl_trans) ){
            display_error(_("Could not retrieve gl trans") );
        } elseif( $gl_trans->num_rows > 0 ) {
            $trans_no = get_next_trans_no(ST_JOURNAL);
            $new = false;
            foreach ($gl_trans->result() AS $tran){
                $this->db->where('account',$tran->account);
                if( $remove_in_year ){
                    $this->db->where(array('tran_date >='=>$year->begin,'tran_date <='=>$year->end));
                } else {
                    $this->db->where('tran_date <=',$year->end);
                }
                $this->db->delete('gl_trans');

                if (is_account_balancesheet($tran->account)){
                    $gl_tran_add = array(
                        'type'=>ST_JOURNAL,
                        'type_no'=>$trans_no,
                        'tran_date'=>$year->end,
                        'account'=>$tran->account,
                        'memo_'=>$ref,
                        'amount'=>$tran->amount
                    );
                    $this->db->insert('gl_trans',$gl_tran_add);
                    $new = true;
                }

            }

            if ($new){
                global $Refs;
                $reference = $Refs->get_next(ST_JOURNAL);
                $Refs->save(ST_JOURNAL, $trans_no, $reference);
                add_audit_trail(ST_JOURNAL, $trans_no, sql2date($year->end));
            }
        }


        /*
         * remove bank_trans
         */
        $this->db->select('bank_act, SUM(amount) AS amount',false);
        $this->where_date($year,'trans_date');
        $bank_trans = $this->db->group_by('bank_act')->get('bank_trans');
        if( !is_object($bank_trans) ){
            display_error(_("Could not retrieve bank trans") );
        } elseif( $bank_trans->num_rows > 0 ) {
            foreach ($bank_trans->result() AS $tran){
                $this->where_date($year,'trans_date');
                $this->db->where('bank_act',$tran->bank_act)->delete('bank_trans');

                $bank_tran_add = array(
                    'type'=>0,
                    'trans_no'=>0,
                    'trans_date'=>$year->end,
                    'bank_act'=>$tran->bank_act,
                    'ref'=>$ref,
                    'amount'=>$tran->amount
                );
                $this->db->insert('bank_trans',$bank_tran_add);
            }

        }


        /*
         * remove comments
         */
        $this->db->select('type, id');
        $this->db->where_not_in('type', array(ST_SALESQUOTE,ST_SALESORDER,ST_PURCHORDER) );
        $comments = $this->db->get('comments');
        if( !is_object($comments) ){
            display_error(_("Could not retrieve comments") );
        } elseif( $comments->num_rows > 0 ) {
            foreach ($comments->result() AS $tran){
                $gl_trans = $this->db->where(array('type'=>$tran->type,'type_no'=>$tran->id))->get('gl_trans');
                if( is_object($gl_trans) && $gl_trans->num_rows > 0){ // if no link, then delete comments
                    $this->db->delete('comments',array('type'=>$tran->type,'type_no'=>$tran->id));
                }
            }
        }


        /*
         * remove refs table
         */
        $this->db->select('type, id');
        $this->db->where_not_in('type', array(ST_SALESQUOTE,ST_SALESORDER,ST_PURCHORDER) );
        $refs = $this->db->get('refs');
        if( !is_object($refs) ){
            display_error(_("Could not retrieve refs") );
        } elseif( $refs->num_rows > 0 ) {
            foreach ($comments->result() AS $tran){
                $where = array('type'=>$tran->type,'type_no'=>$tran->id);
                $gl_trans = $this->db->where($where)->get('gl_trans');
                if( is_object($gl_trans) && $gl_trans->num_rows > 0){ // if no link, then delete refs
                    $this->db->delete('refs',$where);
                }
            }
        }




        $this->where_date($year,'date_');
        $this->db->delete('voided');

        $this->where_date($year,'tran_date');
        $this->db->delete('trans_tax_details');

        $this->where_date($year,'date_');
        $this->db->delete('exchange_rates');

        $this->where_date($year,'tran_date');
        $this->db->delete('budget_trans');

        $this->where_date($year,'gl_date');
        $this->db->delete('audit_trail');

        $this->db->delete('fiscal_year',array('id'=>$year->id));
        return true;

    }

    private function where_date($year,$field=NULL){
        if( !isset($year->end) || !isset($year->begin) || !$field )
            return;

        $remove_in_year = true;

        if( $remove_in_year ){
            $this->db->where(array("$field >="=>$year->begin,"$field <="=>$year->end));
        } else {
            $this->db->where("$field <=",$year->end);
        }

    }

    private function delete_attachments_and_comments($type_no, $trans_no) {
        $this->db->reset();
        $where = array('type_no'=>$type_no,'trans_no'=>$trans_no);
        $this->db->where($where);
        $attachments = $this->db->get('attachments');
        if( !is_object($attachments) ){
            display_error(_("Could not retrieve attachments type=$type_no") );
        } elseif( $attachments->num_rows > 0 ) {
            $delflag = false;
            foreach ($attachments->result() AS $ite){
                $delflag = true;
                $dir =  company_path(). "/attachments";
                if (file_exists($dir."/".$ite->unique_name)){
                    unlink($dir."/".$ite->unique_name);
                }
            }
            if ($delflag) {
                $this->db->delete('attachments',$where);
            }
        }
        $this->db->delete('comments',array('type'=>$type_no,'id'=>$trans_no));
        $this->db->delete('refs',array('type'=>$type_no,'id'=>$trans_no));
    }


}
