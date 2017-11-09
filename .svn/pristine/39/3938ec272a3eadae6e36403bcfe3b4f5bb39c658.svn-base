<?php
class Dashboard_Widgets_Model extends CI_Model {
    function __construct(){
        parent::__construct();

    }

    function items($app=null,$uid=1){
        $this->db->where('app',$app);
        $this->db->where('user_id',$uid);

        $data = $this->db->get('dashboard_widgets')->result();
        return $data;
    }

    function debtors($date_begin=null,$date_end=null,$limit=10){

        $this->db->select("SUM((ov_amount + ov_discount) * rate * IF(trans.type = ".ST_CUSTCREDIT.", -1, 1)) AS total,d.debtor_no, d.name",false);

        $this->db->join("debtors_master AS d","trans.debtor_no=d.debtor_no");
        $this->db->where("(trans.type = ".ST_SALESINVOICE." OR trans.type = ".ST_CUSTCREDIT.")");
        if( $date_begin ){
            $this->db->where( 'tran_date >=', date2sql($date_begin) );
        }
        if( $data_end ){
            $this->db->where( 'tran_date <=', date2sql($date_end) );
        }


        $this->db->group_by('d.debtor_no')->order_by('total DESC, d.debtor_no');

        $data = $this->db->limit($limit)->get('debtor_trans AS trans')->result();
        return $data;
    }

    function sales_overdue($date_=NULL){
        $date = date2sql($date_);

        $this->db->select("trans.trans_no, trans.reference, trans.tran_date, trans.due_date");

        $this->db->select("DATEDIFF('$date', trans.due_date) AS days",false);
        $this->db->select("(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount) AS total",false);
        $this->db->select("(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount - trans.alloc) AS remainder",false);

        $this->db->join("debtors_master AS debtor","debtor.debtor_no = trans.debtor_no")->select("CONCAT(debtor.debtor_no,' ', debtor.name) AS debtor_no_name , debtor.curr_code",false);
        $this->db->join("cust_branch AS branch","trans.branch_code = branch.branch_code")->select("branch.br_name");

        $this->db->where('trans.type',ST_SALESINVOICE);
        $this->db->where("DATEDIFF('$date', trans.due_date) >",0);
        $this->db->where('(trans.ov_amount + trans.ov_gst + trans.ov_freight + trans.ov_freight_tax + trans.ov_discount - trans.alloc) >',FLOAT_COMP_DELTA);
        $data = $this->db->order_by('days DESC')->get('debtor_trans AS trans')->result();
        return $data;
    }

    function purchase_overdue($date_=NULL){
        $date = date2sql($date_);

        $this->db->select("trans.trans_no, trans.reference, trans.tran_date, trans.due_date");

        $this->db->select("DATEDIFF('$date', trans.due_date) AS days",false);
        $this->db->select("(trans.ov_amount + trans.ov_gst + trans.ov_discount) AS total",false);
        $this->db->select("(trans.ov_amount + trans.ov_gst + trans.ov_discount - trans.alloc) AS remainder",false);

        $this->db->join("suppliers AS sm","sm.supplier_id = trans.supplier_id")->select("CONCAT(sm.supplier_id,' ', sm.supp_name) AS supp_name , sm.curr_code",false);
//         $this->db->join("cust_branch AS branch","trans.branch_code = branch.branch_code")->select("branch.br_name");

        $this->db->where('trans.type',ST_SUPPINVOICE);
        $this->db->where("DATEDIFF('$date', trans.due_date) >",0);
        $this->db->where('(trans.ov_amount + trans.ov_gst + trans.ov_discount - trans.alloc) >',FLOAT_COMP_DELTA);
        $data = $this->db->order_by('days DESC')->get('supp_trans AS trans')->result();
//         bug(  $this->db->last_query() );
//         bug($data );die;
        return $data;
    }

    function suppliers($date_begin=null,$date_end=null,$limit=10){
        $this->db->select("SUM((trans.ov_amount + trans.ov_discount) * rate) AS total",false);

        $this->db->join("suppliers AS s","trans.supplier_id=s.supplier_id")->select('s.supplier_id, s.supp_name');
        $this->db->where("(trans.type = ".ST_SUPPINVOICE." OR trans.type = ".ST_SUPPINVOICE.")");
        if( $date_begin ){
            $this->db->where( 'tran_date >=', date2sql($date_begin) );
        }
        if( $date_end ){
            $this->db->where( 'tran_date <=', date2sql($date_end) );
        }


        $this->db->group_by('s.supplier_id')->order_by('total DESC, s.supplier_id');

        $data = $this->db->limit($limit)->get('supp_trans AS trans')->result();
//         bug( $this->db->last_query() );
//         bug($data);die;
        return $data;
    }

    function weekly_sales($limit=10){

        $this->db->join("cust_allocations ca","bt.type = ca.trans_type_from AND bt.trans_no = ca.trans_no_from",'INNER')->select('');
        $this->db->join("debtor_trans dt","dt.type = ca.trans_type_from AND dt.trans_no = ca.trans_no_from",'INNER')->select('');
        $this->db->join("debtors_master dm","dt.debtor_no = dm.debtor_no",'INNER')->select('');
        $this->db->join("trans_tax_details ttd","ttd.trans_type = ca.trans_type_to AND ttd.trans_no = ca.trans_no_to",'INNER');
        $this->db->select('sum(ttd.amount*ex_rate) AS tax, sum(ttd.net_amount*ex_rate) AS net_sales, sum((ttd.net_amount+ttd.amount)*ex_rate) AS gross_sales',false);
        $this->db->join("tax_types tt","tt.id = ttd.tax_type_id",'INNER')->select('');

//         $this->db->select(' cast(weekofyear(bt.trans_date) as char)) AS week_no, max(bt.trans_date) AS week_end');
//         $this->db->select('week_end, week_no, gross_sales, net_sales, tax');

        $this->db->select('max(bt.trans_date) AS week_end',false);
        $this->db->select("concat(cast(case when weekofyear(bt.trans_date) = 1 and month(bt.trans_date)=12 then year(bt.trans_date) + 1 else year(bt.trans_date) end as char),cast(weekofyear(bt.trans_date) as char)) AS week_no");

        $this->db->group_by('concat(cast(case when weekofyear(bt.trans_date) = 1 and month(bt.trans_date)=12 then year(bt.trans_date) + 1 else year(bt.trans_date) end as char),cast(weekofyear(bt.trans_date) as char))');
        $data = $this->db->limit($limit)->get('bank_trans bt')->result();

        return $data;

    }

    function weekly_purchase($limit=10){

        $this->db->join("supp_allocations sa","bt.type = sa.trans_type_from AND bt.trans_no = sa.trans_no_from",'INNER')->select('');
        $this->db->join("supp_trans st","st.type = sa.trans_type_from AND st.trans_no = sa.trans_no_from",'INNER')->select('');
        $this->db->join("suppliers sm","st.supplier_id = sm.supplier_id",'INNER')->select('');
//         $this->db->join("trans_tax_details ttd","ttd.trans_type = ca.trans_type_to AND ttd.trans_no = ca.trans_no_to",'INNER');
        $this->db->select('SUM(st.ov_gst*rate) AS tax, SUM((st.ov_amount)*rate) AS gross_sales',false);
//         $this->db->join("tax_types tt","tt.id = ttd.tax_type_id",'INNER')->select('');

        //         $this->db->select(' cast(weekofyear(bt.trans_date) as char)) AS week_no, max(bt.trans_date) AS week_end');
        //         $this->db->select('week_end, week_no, gross_sales, net_sales, tax');

        $this->db->select('max(st.tran_date) AS week_end',false);
        $this->db->select("concat(cast(case when weekofyear(bt.trans_date) = 1 and month(bt.trans_date)=12 then year(bt.trans_date) + 1 else year(bt.trans_date) end as char),cast(weekofyear(bt.trans_date) as char)) AS week_no");

        $this->db->group_by('concat(cast(case when weekofyear(bt.trans_date) = 1 and month(bt.trans_date)=12 then year(bt.trans_date) + 1 else year(bt.trans_date) end as char),cast(weekofyear(bt.trans_date) as char))');

        $data = $this->db->limit($limit)->get('bank_trans bt')->result();
//         bug($this->db->last_query() );
//         bug( $data );die;

        return $data;

    }

    function daily_sales($limit=10){

        $this->db->select('max(trans_date) AS `Week End`',false);
        $this->db->select('concat(cast(case when weekofyear(trans_date) = 1 and month(trans_date)=12 then year(trans_date) + 1 else year(trans_date) end as char),cast(weekofyear(trans_date) as char)) `Week No`',false);
        $this->db->select('sum(case when weekday(trans_date)=0 then gross_output else 0 end) Monday',false);
        $this->db->select('sum(case when weekday(trans_date)=1 then gross_output else 0 end) Tuesday',false);
        $this->db->select('sum(case when weekday(trans_date)=2 then gross_output else 0 end) Wednesday',false);
        $this->db->select('sum(case when weekday(trans_date)=3 then gross_output else 0 end) Thursday',false);
        $this->db->select('sum(case when weekday(trans_date)=4 then gross_output else 0 end) Friday',false);
        $this->db->select('sum(case when weekday(trans_date)=5 then gross_output else 0 end) Saturday',false);
        $this->db->select('sum(case when weekday(trans_date)=6 then gross_output else 0 end) Sunday',false);
        $this->db->select('FROM (SELECT bt.trans_date trans_date,  sum((ttd.net_amount+ttd.amount)*ex_rate) gross_output');

    }

    function stock_items($date_begin=NULL, $date_end=NULL, $limit=10, $item_type = 'manuf'){
        $this->db->select('SUM((trans.unit_price * trans.quantity) * d.rate) AS total, s.stock_id, s.description, SUM(trans.quantity) AS qty',false);
        $this->db->join('stock_master AS s','trans.stock_id=s.stock_id');
        $this->db->join('debtor_trans AS d','trans.debtor_trans_type=d.type AND trans.debtor_trans_no=d.trans_no');

        $this->db->where("(d.type = ".ST_SALESINVOICE." OR d.type = ".ST_CUSTCREDIT.")");
        if( $item_type=='' ){
            $this->db->where('s.mb_flag','M');
        }

        if( $date_begin ){
            $this->db->where( 'tran_date >=', date2sql($date_begin) );
        }
        if( $date_end ){
            $this->db->where( 'tran_date <=', date2sql($date_end) );
        }

        $this->db->group_by('s.stock_id')->order_by('total DESC, s.stock_id');
        $data = $this->db->limit($limit)->get('debtor_trans_details AS trans')->result();
        return $data;
    }

    function gl_return($date_begin=NULL, $date_end=NULL, $limit=10){
        $this->db->select('SUM(gl.amount) AS total, c.class_name, c.ctype',false);

        $this->db->join('chart_master AS a','a.account_code = gl.account');
        $this->db->join('chart_types AS t','t.id=a.account_type');
        $this->db->join('chart_class AS c','c.cid=t.class_id');


        if( $date_begin ){
            $this->db->where( "IF(c.ctype > 3, tran_date >= ".date2sql($date_begin).", tran_date >= '0000-00-00')" );
        }
        if( $date_end ){
            $this->db->where( 'tran_date <=', date2sql($date_end) );
        }

        $this->db->group_by('c.cid')->order_by('c.cid');
        $data = $this->db->limit($limit)->get('gl_trans AS gl')->result();

        return $data;

    }

    function bankBalances($date=null){
        $this->db->select('bank_act, bank_account_name, SUM(amount) balance',false);
        $this->db->join('bank_accounts ba','bt.bank_act = ba.id','INNER');
        $this->db->where( 'inactive <>',1);
        if( $date ){
            $this->db->where( 'trans_date <=', date2sql($date) );
        }
        $this->db->group_by('bank_act, bank_account_name')->order_by('bank_account_name');
        $data = $this->db->get('bank_trans bt')->result();
        return $data;

    }

    function dailyBankBalances($bank_act=0,$days_past=15,$days_future=15){
        $data = $this->db->select('SUM(amount) AS amount, bank_act')->where( "trans_date < now() - INTERVAL ".$days_past." DAY")->group_by('bank_act')->order_by('amount DESC')->get('bank_trans')->row();


        $this->db->select('bank_act, bank_account_name, trans_date, SUM(amount) AS amount',false);
        $this->db->join('bank_accounts ba','bt.bank_act = ba.id','INNER');

        $this->db->where( "trans_date < now() - INTERVAL ".$days_past." DAY");
        $this->db->where( "trans_date < now() + INTERVAL ".$days_future." DAY");

        if( $data && isset($data->bank_act) ){
            $this->db->where( 'bt.bank_act', $data->bank_act );
            $this->db->group_by('trans_date');
        }

        $data = $this->db->order_by('amount DESC')->get('bank_trans bt')->result();

        return $data;
//         $sql1 = $this->db->query_compile();

//         bug($sql);
//         die('callme');
    }
}