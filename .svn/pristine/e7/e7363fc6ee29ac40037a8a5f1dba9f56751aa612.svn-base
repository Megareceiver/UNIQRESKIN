<?php
class GstForm5  {
    function __construct() {
        $ci = get_instance();

        $this->tax_model = $ci->model('tax',true);
	    $this->cus_trans_model = $ci->model('customer_trans',true);
	    $this->customer_model = $ci->model('cutomer',true);
	    $this->supplier_model = $ci->model('supplier',true);
	    $this->supp_trans_model = $ci->model('supplier_trans',true);
	    $this->bank_trans_model = $ci->model('bank_trans',true);
	    $this->db = $ci->db;
	    $this->company_data = get_company_prefs();

	    $this->report = module_control_load('report','report');

	    global $assets_path;
	    add_css_source("$assets_path/metronic/css/form5.css");
    }

    function index(){

        $this->date_from = input_post('start_date');
        if( $this->date_from ){
            $this->date_from = date('Y-m-d',strtotime($this->date_from) );
        }
        $this->date_to =  input_post('end_date');
        if( $this->date_to ){
            $this->date_to = date('Y-m-d',strtotime($this->date_to) );
        }

        $output = (input_post('ouput_xml')) ? 'xml': 'pdf';
        if( input_val('ouput_text') ){
            $output = 'tap';
        }

        if( $this->date_from || $this->date_to ){
            set_cookie('form5_from',$this->date_from);
            set_cookie('form5_to',$this->date_to);

            if( $output=='xml' ){
                return $this->iaf_output();
            } else if ($output=='tap') {
                return $this->tap_output();
            }

            return $this->from5_output();
        } else {
            $this->form();
        }

    }

    private function form(){

        $date_to = get_cookie('form5_to');
        if( $date_to ){
            $date_to = new DateTime($date_to);
        } else {
            $date_to = new DateTime('now');
            $date_to->modify('last day of this month');
        }

        $date_from = get_cookie('form5_from');
        if( !$date_from ){
            $date_from = new DateTime('1-1-2015');
        } else {
            $date_from = new DateTime($date_from);
        }

        $this->report->fields = array(
            'start_date' => array('type'=>'DATE','title'=>_('Start Date'),'value'=>$date_from->format('d-m-Y') ),
            'end_date' => array('type'=>'DATE','title'=>_('End Date'),'value'=>$date_to->format('d-m-Y') ),
            // 	        'bug' => array('type'=>'HIDDEN','value'=>1 ),
        );
        $submit = array(
            'UPDATE_ITEM'=>array('View GST Form 5',false),
            'ouput_xml'=>array('Export GAF',false),
//             'ouput_text'=>array('Export TAP',false)
        );

        $this->report->form('GST Form 5',$submit,4);


    }

    private function iaf_output(){

        $data = array('purchase_xml'=>null,'supply_xml'=>null,'company'=>$this->company_data,'created'=>date('Y-m-d'),'date_from'=>$this->date_from,'date_to'=>$this->date_to );

        // 	    $PurchaseTotal = $GSTTotal = $line = 0;

        $this->supplier = (object) array('line'=>0,'amount'=>0,'gst'=>0);
        $supplier_trans = $this->purchase_trans();

        if( $supplier_trans && count($supplier_trans) >0 ){
            $data['purchase_xml'] .= $this->xml_tran_line($supplier_trans,'supplier');
        }
        $supplier_payment_trans = $this->bank_trans(0,ST_BANKPAYMENT);

        if( $supplier_payment_trans && count($supplier_payment_trans) >0 ){
            $data['supply_xml'] .= $this->xml_tran_line($supplier_payment_trans,'supplier');
        }

        $data['purchase_xml'] = '<Purchase PurchaseTotalSGD="'.number_total($this->supplier->amount).'" GSTTotalSGD="'.number_total($this->supplier->gst).'" TransactionCountTotal="'.$this->supplier->line.'">'.$data['purchase_xml'].'</Purchase>';

        $SupplyTotal = $GSTTotal = $line = 0;
        $this->supply = (object) array('line'=>0,'amount'=>0,'gst'=>0);

        $debtor_trans = $this->sale_trans();

        if( $debtor_trans && count($debtor_trans) >0 ){
            $data['supply_xml'] .= $this->xml_tran_line($debtor_trans,'supply');
        }
        $debtor_deposit_trans = $this->bank_trans(0,ST_BANKDEPOSIT);
        // bug($debtor_deposit_trans);die;
        if( $debtor_deposit_trans && count($debtor_deposit_trans) >0 ){
            $data['supply_xml'] .= $this->xml_tran_line($debtor_deposit_trans,'supply');
        }

        $data['supply_xml'] = '<Supply SupplyTotalSGD="'.number_total($this->supply->amount).'" GSTTotalSGD="'.number_total($this->supply->gst).'" TransactionCountTotal="'.$this->supply->line.'">'.$data['supply_xml'].'</Supply>';

        /*
         * BEGIN show GL transactions
         */
        $gl_trans = $this->gl_trans();
        $line = 0; $TotalDebit = $TotalCredit = 0;
        $Balance = array();

        if( $gl_trans && count($gl_trans) > 0 ){ foreach ($gl_trans AS $gl){
            $line ++;
            $linedata = array('gl'=>$gl,'line'=>$line,'debit'=>0,'credit'=>0);
            if( !isset($Balance[$gl->account]) ){
                $openning = $this->db->select('SUM(amount) AS total',false)->from('gl_trans')->where(array('amount !='=>0,'account'=>$gl->account,'tran_date <='=>$this->date_from))->get()->row();
                $Balance[$gl->account] = floatval($openning->total);

                $linedata_open = $linedata;
                $linedata_open['balance'] = $Balance[$gl->account];
                $data['gl_xml'] .= module_view('gst_form5/gl_openning',$linedata_open,true);
            }
            $Balance[$gl->account] += $gl->amount;
            $linedata['balance'] = $Balance[$gl->account];

            if( $gl->amount >0 ) {
                $linedata['debit'] = abs($gl->amount);
                $TotalDebit += abs($gl->amount);
            } else if ($gl->amount <0 ) {
                $linedata['credit'] = abs($gl->amount);
                $TotalCredit -= abs($gl->amount);
            }

            $data['gl_xml'] .= module_view('gst_form5/gl',$linedata,true);
        }}
        $data['gl_xml'] = '<GLData TotalDebit="'.number_format2($TotalDebit, user_amount_dec()).'" TotalCredit="'.number_format2($TotalCredit, user_amount_dec()).'" TransactionCountTotal="'.$line.'" GLTCurrency="SGD">'.$data['gl_xml'].'</GLData>';
        /*
         * END show GL transactions
         */

        // 	    $data['purchase_xml'] = $data['supply_xml'] = null;
        $xml = module_view('gst_form5/gst-form5-xml',$data,true);

        header('Content-type:text/xml');
        header('Content-disposition:attachment;filename="GSTForm5.xml"');
        echo $xml;
    }

    function xml_tran_line($items,$type='gl'){
        if( empty($items) || !is_array($items) ) return NULL;

        $item_showed = array();
        $xml = NULL;
        foreach ($items AS $inv){

            $linedata = array(
                'line_no'=>0,
                'reference'=>$inv->reference,
                'com_name'=>NULL,
                'gst_no'=>$inv->gst_no,
                'country'=>NULL,
                'tran_date'=>sql2date($inv->tran_date),
                'product_desc'=>NULL,
                'currency_code'=> ( $inv->currency_code != $this->company_data['curr_default'] )? $inv->currency_code : 'XXX',
                'price_curr'=>0,
                'tax_curr'=>0,
            );

            switch ($type){
                case 'supply':
                    $obj = $this->supply;
                    $linedata['com_name'] = $inv->debtor_name;
                    $linedata['product_desc'] = ( $inv->type== ST_SALESINVOICE) ? $inv->item_memo: $inv->long_description;

                    break;
                case 'supplier':
                    $obj = $this->supplier;
                    $linedata['supp_name'] = $inv->supp_name;
                    $linedata['product_desc'] = ( $inv->type== ST_SUPPINVOICE) ? $inv->item_memo: $inv->item_description;
                    break;
                default:break;
            }
            // 	        bug($inv);
            // 	        bug($item_showed);

            if( isset($inv->supp_trans_no) && !in_array($inv->supp_trans_no, $item_showed) ){
                $inv_item_line = 1;
                $item_showed[] = $inv->supp_trans_no;
            }elseif ( isset($inv->debtor_trans_no) && !in_array($inv->debtor_trans_no, $item_showed) ){
                $inv_item_line = 1;
                $item_showed[] = $inv->debtor_trans_no;
            }elseif ( isset($inv->trans_no) && !in_array($inv->trans_no, $item_showed) ){
                $inv_item_line = 1;
                $item_showed[] = $inv->trans_no;

            } else {
                $inv_item_line++;
            }
            $obj->line ++;

            $tax = tax_calculator($inv->tax_type_id,$inv->quantity*$inv->unit_price*$inv->rate,$inv->tax_included);
            $linedata['price'] = $tax->price;
            $linedata['tax_value'] = $tax->value;

            $linedata['tax_code'] = $tax->code;
            $linedata['line_no'] = $inv_item_line;

            $linedata['country'] = $inv->charge_to_address;

            if( $inv->currency_code != $this->company_data['curr_default'] ){
                $tax_currency = tax_calculator($inv->tax_type_id,$inv->quantity*$inv->unit_price,$inv->tax_included);
                $linedata['price_curr'] = $tax_currency->price;
                $linedata['tax_curr'] = $tax_currency->value;
            }

            $obj->amount += $tax->price;
            $obj->gst += $tax->value;
            // 	        bug($linedata);
            switch ($type){
                case 'supply':
                    $xml .= module_view('gst_form5/supply',$linedata,true); break;
                case 'supplier':
                    $xml .= module_view('gst_form5/purchase',$linedata,true); break;
                default:break;
            }

        }

        // die('aa');
        return $xml;
    }

    private function xml_iaf_sale($items=array(),$line_no = 0,$company_data){
        $invoice_showed = array();
        $xml = NULL;
        $SupplyTotal = $GSTTotal = 0;
        foreach ($items AS $inv){
            $line ++;

            if( !in_array($inv->debtor_trans_no, $invoice_showed) ){
                $line_no = 1;
                $invoice_showed[] = $inv->debtor_trans_no;
            } else {
                $line_no ++;
            }
            $linedata = array(
                'inv'=>$inv,
                'line'=>$line,
                'price_curr'=>0,
                'tax_curr'=>0,
                'curr_default'=>$company_data['curr_default'],
                'line_no'=>$line_no
            );


            $tax = tax_calculator($inv->tax_type_id,$inv->quantity*$inv->unit_price*$inv->rate,$inv->tax_included);
            $linedata['price'] = $tax->price;
            $linedata['tax_value'] = $tax->value;
            $linedata['tax_code'] = $tax->code;
            if( $inv->currency_code != $linedata['curr_default'] ){
                $tax_currency = tax_calculator($inv->tax_type_id,$inv->quantity*$inv->unit_price,$inv->tax_included);
                $linedata['price_curr'] = $tax_currency->price;
                $linedata['tax_curr'] = $tax_currency->value;
            }

            $SupplyTotal += ($inv->quantity*$inv->unit_price);
            $GSTTotal += $tax->value;

            $xml .= $this->ci->view('reporting/gst_form5/supply',$linedata,true);
        }
        return array('xml'=>$xml,'total'=>$PurchaseTotal,'gst'=>$GSTTotal);
    }

    private function xml_iaf_purchase($items,$line_no=0,$company_data){
        $invoice_showed = array();
        $PurchaseTotal = $GSTTotal = 0;
        $xml = NULL;
        foreach ($items AS $supp){
            if( !in_array($supp->supp_trans_no, $invoice_showed) ){
                $line_no = 1;
                $invoice_showed[] = $supp->supp_trans_no;
            } else {
                $line_no ++;
            }

            $line ++;
            $linedata = array('supp'=>$supp,'line'=>$line,'curr_default'=>$company_data['curr_default'],'line_no'=>$line_no);

            $tax = tax_calculator($supp->tax_type_id,$supp->item_qty*$supp->unit_price*$supp->rate,$supp->tax_included);
            $linedata['price'] = $tax->price;
            $linedata['tax_value'] = $tax->value;
            $linedata['tax_code'] = $tax->code;

            $PurchaseTotal += ($supp->item_qty*$supp->unit_price);
            $GSTTotal += $tax->value;
            if( $supp->currency_code != $linedata['curr_default'] ){
                $tax_currency = tax_calculator($supp->tax_type_id,$supp->item_qty*$supp->unit_price,$supp->tax_included);
                $linedata['price_curr'] = $tax_currency->price;
                $linedata['tax_curr'] = $tax_currency->value;
            } else {
                $linedata['price_curr'] = null;
                $linedata['tax_curr'] = null;
            }

            $xml .= $this->ci->view('reporting/gst_form5/purchase',$linedata,true);
        }
        return array('xml'=>$xml,'total'=>$PurchaseTotal,'gst'=>$GSTTotal);
    }

    private function purchase_trans($tax_id=0){
        $this->db->reset();
        $this->db->select('inv.* , tran.*, IF(inv.grn_item_id = 0, 1, inv.quantity) AS item_qty,  supp.supp_name, supp.gst_no, supp.curr_code AS currency_code',false);

        $this->db->select("IF(inv.grn_item_id = 0, 1, inv.quantity) AS quantity",false);

        $this->db->where(array('ov_amount !='=>0,'tran.tran_date >='=>($this->date_from),'tran.tran_date <='=>($this->date_to) ));
        $this->db->where_in('tran.type',array(ST_SUPPINVOICE,ST_SUPPCREDIT,ST_SUPPAYMENT,ST_SUPPRECEIVE));
        $this->db->join('supp_trans AS tran','inv.supp_trans_no=tran.trans_no AND inv.supp_trans_type=tran.type','left');
        $this->db->join('suppliers AS supp','supp.supplier_id=tran.supplier_id','left')
        ->select('NULL AS charge_to_address, NULL AS item_description',false)
        ;

        // 	    $this->db->join('stock_master AS stock','stock.stock_id=inv.stock_id','left');
        // 	    $this->db->select('stock.long_description');

        $this->db->join('stock_master AS pro','pro.stock_id= inv.stock_id','left');
        $this->db->select(' IF(inv.grn_item_id = 0, inv.memo_ , pro.long_description )  AS item_description', false);
        $this->db->join('comments AS memo','memo.type=tran.type AND memo.id=tran.trans_no','left');
        $this->db->select('IF(tran.type = 20, memo.memo_, NULL )  AS item_memo', false);

        // 	    $this->db->join('source_reference as source', 'source.trans_type = inv.supp_trans_type AND source.trans_no = inv.supp_trans_no', 'left');
        // 	    $this->db->select('source.reference AS source_ref');

        if( $tax_id ){
            $this->db->where('inv.tax_type_id',$tax_id);
        }
        $this->db->limit(20);
        $supplier_trans = $this->db->get('supp_invoice_items AS inv')->result();
        // bug( $this->db->last_query() ); die;
        return $supplier_trans;
    }
    private function sale_trans($tax_id=0){
        $this->db->reset();
        $this->db->select('inv.*, tran.*, inv.quantity AS item_qty , deb.name AS debtor_name, deb.tax_id AS gst_no, deb.curr_code AS currency_code, deb.address AS charge_to_address');
        $this->db->where(array('ov_amount !='=>0,'tran.tran_date >='=>($this->date_from),'tran.tran_date <='=>($this->date_to)));
        // 	    $this->db->where_in('tran.type',array(ST_SALESINVOICE,ST_CUSTCREDIT,ST_CUSTPAYMENT,ST_CUSTDELIVERY));
        $this->db->where_in('tran.type',array(ST_SALESINVOICE,ST_CUSTCREDIT));
        $this->db->join('debtor_trans AS tran','inv.debtor_trans_no=tran.trans_no AND inv.debtor_trans_type=tran.type','left');
        $this->db->join('debtors_master AS deb','deb.debtor_no=tran.debtor_no','left');

        $this->db->join('stock_master AS stock','stock.stock_id=inv.stock_id','left');
        $this->db->select('stock.long_description');

        // 	    $this->db->join('shippers AS ship','ship.shipper_id=trans.ship_via','left');
        // 	    $this->db->get();

        $this->db->join('sales_types as saletype', 'saletype.id = tran.tpe', 'left');
        $this->db->select('saletype.tax_included');

        // 	    $this->db->join('source_reference as source', 'source.trans_type = inv.debtor_trans_type AND source.trans_no = inv.debtor_trans_no', 'left');
        // 	    $this->db->select('source.reference AS source_ref');

        // 	    $this->db->select('IF(tran.type = 10, SELECT memo_ FROM comments WHERE type=tran.type AND id=tran.trans_no , NULL )  AS item_memo', false);
        $this->db->join('comments AS memo','memo.type=tran.type AND memo.id=tran.trans_no','left');
        $this->db->select('IF(tran.type = tran.type, memo.memo_, NULL )  AS item_memo', false);

        if( $tax_id ){
            $this->db->where('inv.tax_type_id',$tax_id);
        }
        $debtor_trans = $this->db->get('debtor_trans_details AS inv')->result();

        return $debtor_trans;
    }
    private function gl_trans(){
        $this->db->reset();
        $this->db->select('gl.*, acc.account_name')->from('gl_trans AS gl');
        $this->db->join('chart_master AS acc','acc.account_code=gl.account', 'left');
        $this->db->select(" case gl.person_type_id
                    when '3' then (SELECT supp_name FROM suppliers WHERE supplier_id = gl.person_id)
                    when '2' then (SELECT name FROM debtors_master WHERE debtor_no = gl.person_id)
                end as user_trans_name",false);

        $this->db->join('comments AS note','note.type = gl.type AND note.id = gl.type_no', 'left');
        $this->db->select("IF(gl.memo_  > '' , gl.memo_ , note.memo_ ) AS memo ",false);
        //         $this->db->where('gl.account',1050);

        //         $this->db->select("IF(gl.amount < 0, ABS(gl.amount), 0) AS credit",false);
        //         $this->db->select("IF(gl.amount > 0, ABS(gl.amount), 0) AS debit",false);

        $this->db->where(array('gl.amount !='=>0,'gl.tran_date >='=>$this->date_from,'gl.tran_date <='=>$this->date_to));
        //         $this->db->where('gl.type',20);
        $this->db->join('refs AS ref','ref.type=gl.type AND ref.id = gl.type_no', 'left');

        $this->db->select("ref.reference AS tran_reference");

        $this->db->join('source_reference as source', 'source.trans_type = gl.type AND source.trans_no = gl.type_no', 'left');
        $this->db->select('source.reference AS source_ref');


        $gl_trans = $this->db->order_by('gl.account ASC')->get()->result();
        //         bug( $this->db->last_query()  );die;

        return $gl_trans;
    }

    private function bank_trans($tax_id=0,$tran_type=0){

        $this->db->reset();
        $this->db->select('1 AS item_qty, 1 AS quantity',false);
        $this->db->select('bt.trans_no, bt.type ,inv.currence AS currency_code, inv.tax AS tax_type_id, inv.tax , ABS(inv.amount) AS unit_price, inv.currence_rate AS rate');
        $this->db->from('bank_trans_detail AS inv');
        $this->db->where('bt.amount !=',0);
        $this->db->select('0 AS tax_inclusive, 0 AS tax_included , ABS(inv.amount) AS amount',false);
        if( $tax_id ){
            $this->db->where('inv.tax',$tax_id);
        }


        $this->db->join('bank_trans AS bt','bt.trans_no = inv.trans_no AND bt.type=inv.type','left');
        $this->db->select('bt.ref AS reference, bt.trans_date AS tran_date');
        if( $tran_type ){
            $this->db->where('bt.type',$tran_type);
        }
        $this->db->where(array('bt.trans_date >='=>($this->date_from),'bt.trans_date <='=>($this->date_to)));

        $this->db->join('debtor_trans AS dt','dt.type=bt.type AND dt.trans_no=bt.trans_no','left');

        $this->db->join('debtors_master AS debtor','debtor.debtor_no = dt.debtor_no','left');
        if( $tran_type==ST_BANKDEPOSIT ){
            $this->db->select('debtor.name AS debtor_name, debtor.tax_id AS gst_no, debtor.address AS charge_to_address','left');
        }

        $this->db->join('supp_trans AS st','st.type=bt.type AND st.trans_no=bt.trans_no','left');

        if( $tran_type==ST_BANKPAYMENT ){
            $this->db->join('suppliers AS supplier','supplier.supplier_id = st.supplier_id','left');
            $this->db->select('supplier.supp_name , supplier.gst_no, supplier.address AS charge_to_address');

        }
        // 	    $this->db->join('source_reference as source', 'source.trans_type = inv.type AND source.trans_no = inv.trans_no', 'left');
        // 	    $this->db->select('source.reference AS source_ref');

        $this->db->select('NULL AS charge_to_address, NULL AS item_description, NULL AS long_description',false);

        $items = $this->db->get()->result();
        // 	    bug( $this->db->last_query() );

        return $items;
    }

    function from5_output(){
        $data = array(
            'f1'=>0,'f2'=>0,'f3'=>0,'f4'=>0,'f5'=>0,'f6'=>0,'f7'=>0,'f8'=>0,'f9'=>0,'f13'=>0,
            'company'=>get_company_prefs(),'created'=>date('Y-m-d'),'date_from'=>sql2date($this->date_from),'date_to'=>sql2date($this->date_to)
        );

        $js = null;
        page(_('GST FORM 5 (By GST Type)'), false, false, "", $js);

        $taxes = taxes_items();

        if ( $taxes && !empty($taxes) ) foreach ($taxes AS $tax){
            if( $tax->use_for == 3 ){ // Purchase
                $trans = $this->purchase_trans($tax->id);
            } else if ($tax->use_for == 2){
                $trans = $this->sale_trans($tax->id);
            }


            if( $trans && !empty($trans) ) {

                foreach ($trans AS $tran){

                    $tax_item = tax_calculator($tran->tax_type_id,$tran->item_qty*$tran->unit_price*$tran->rate,$tran->tax_included,$tax);
                    switch ($tax->f5){
                        case 1:
                            $data['f1'] += $tax_item->price;
                            $data['f6'] += $tax_item->value;
                            break;
                        case 2:
                            $data['f2'] += $tax_item->price;
                            $data['f6'] += $tax_item->value;
                            break;
                        case 3:
                            $data['f3'] += $tax_item->price;
                            $data['f6'] += $tax_item->value;
                            break;
                        case 5:
                            $data['f5'] += $tax_item->price;
                            $data['f7'] += $tax_item->value;
                            // 	                         bug($tax_item);
                            // 	                         bug($data['f5']);
                            break;
                        case 9:
                            $data['f9'] += $tax_item->price;
                            break;
                        default: break;
                    }
                }
            }

            $bank_trans =  $trans = $this->bank_trans($tax->id);
            // 	        bug($bank_trans);
            if( $bank_trans && !empty($bank_trans)){ foreach ( $bank_trans AS $tran){
                $tax_item = tax_calculator($tran->tax,$tran->item_qty*$tran->unit_price*$tran->rate,$tran->tax_inclusive,$tax);
                switch ($tax->f5){
                    case 1:
                        $data['f1'] += $tax_item->price;
                        $data['f6'] += $tax_item->value;
                        break;
                    case 2:
                        $data['f2'] += $tax_item->price;
                        $data['f6'] += $tax_item->value;
                        break;
                    case 3:

                        $data['f3'] += $tax_item->price;
                        $data['f6'] += $tax_item->value;
                        break;
                    case 5:
                        $data['f5'] += $tax_item->price;
                        $data['f7'] += $tax_item->value;
                        //                          bug($tax_item);
                        //                          bug($data['f5']);
                        break;
                    case 9:
                        $data['f9'] += $tax_item->price;
                        break;
                    default: break;
                }
            }}

        }
        // 	    die('end here');
        $data['f8'] = $data['f6'] - $data['f7'];
        box_start();
        module_view('gst_form5/form5',$data);
        box_footer();
        box_end();
        end_page();
    }
}