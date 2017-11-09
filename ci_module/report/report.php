<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
if( !class_exists('Report') ) :
class Report {
    function __construct() {
        global $ci;
        global $session;
        $this->ci = $ci;
        $this->bootstrap = $ci->bootstrap;
        $this->page_security = 'SA_GLTRANSVIEW';
        $this->ci->page_title = 'Reports and Analysis';
        include_once(ROOT . "/includes/ui.inc");
        if( !method_exists($this->ci, 'qpdf') ){
            $this->ci->load_library('qpdf');
        }

        // $this->template_invoice = $this->ci->module_model($this->ci->module,'template_invoice',true);
    }

    var $fields = array();
    var $view = 'form-reports';
    function form($title='Report',$buttons=NULL,$area_width=6){
        $ci = get_instance();

        if( !$buttons ){
            //$buttons = array( 'report_submit'=>"Download: $title");
        }
        if( !module_view_file_exist($this->view,'report') ){
            $this->view = 'form-reports';
        }

        page($this->ci->page_title. " | $title");
        if($title == 'Print Statements'){
            echo "<a class='btn green ajaxsubmit' href=".site_url().'report/report/manage_template/statements'." >Manage Template Statements</a>";
        }else if($title == 'Bank Reconcile'){
            echo "<a class='btn green ajaxsubmit' href=".site_url().'report/report/manage_template/reconcile'." >Manage Template Bank Reconcile</a>";
        }
//         $this->bootstrap->box_start();        
        start_form($multi=false, $dummy=false, $action=get_instance()->uri->uri_string());
        $ci->temp_view($this->view,array('fields'=>$this->fields,'submit'=>$buttons,'area_width'=>$area_width),false,'report');
        box_start();
        box_footer_start();
        if($title == 'Print Statements'){
            echo "<button class='btn green' type='button' id='btnstatements' onclick='' stye='float:right;'>PDF : Display $title</button>";
        }else if($title == 'Bank Reconcile'){
            echo "<button class='btn green' type='button' id='btnreconcile' onclick='' stye='float:right;'>PDF : Display $title</button>";
        }
        box_footer_end();
        box_end();
		end_form();
        end_page();
    }

    function submit(){
        $data = array();
        if( !empty($this->fields) ) foreach ($this->fields AS $name=>$f){
            $data[$name] = $f['value'];
            if( input_val($name) ){
                $data[$name] = input_val($name);
            }
            if( $f['type']=='orientation' ){
                $data[$name] = ($data[$name] && $data[$name]=='L') ? 'L' : 'P';
            }
        }
        return $data;
    }
    function view(){
    }

    var $report_params = array("");

    function front_report($page_title=NULL,$tableHeader=array(), $tran_type=NULL ){
        $path_to_root = ROOT;
        $destination = input_val('report_type');
        if( !$destination ){
            $destination = $destination=='excel'? true : false;
        }

        $orientation = input_val('orientation');
        if( !$orientation ){
            $orientation = 'P';
        }else{
            $orientation = ($orientation = 'orientation') ? 'P' :'L';
        }

        if ($destination)
            include_once(ROOT . "/reporting/includes/excel_report.inc");
        else
            include_once(ROOT . "/reporting/includes/pdf_report.inc");



        $rep = new FrontReport($page_title, str3_function_name($page_title), user_pagesize(), 9, $orientation);
        $rep->tran_type = $tran_type;
        list ($headers, $cols, $aligns) = $this->report_front_params($tableHeader);

        if ($orientation == 'L')
            recalculate_cols($cols);
//         $rep->Font();
        $rep->Info($this->report_params, $cols, $headers, $aligns);
        $this->rep = $rep;
        return $this->rep;
    }

    function report_front_params($params_array = NULL){
        $cols = array(0);
        $headers = array();
        $aligns = array();
        if( count($params_array) > 0 ) while ( list ($key, $val) = each ($params_array)){
            if( count($val[0]) > 0 ){
                $headers[] = $val[0];
                $cols[] = $val[1];
                $aligns[] = isset($val[2]) ? $val[2] : 'left';
            }

        }
        return array($headers, $cols, $aligns);


    }

    var $discount, $shipping, $subTotal, $taxTotal, $leftAllocate = 0;
    var $taxes = array();
    var $type = 0;
    function invoice_footer($name=""){
        $this->rep->TextCol(1, 5,	$this->rep->company['curr_default'].":".price_in_words( $this->subTotal + $this->taxTotal , ST_CHEQUE));
        $max_col = count($this->rep->cols)-2;

        $this->rep->Font('bold');
        if( in_array($this->type, array(ST_SUPPAYMENT)) ) {
            $this->rep->TextCol($max_col-2, $max_col,	_("TOTAL $name"));
        } else {
            $this->rep->TextCol($max_col-2, $max_col,	_("TOTAL $name INCL. GST"));

        }
        $this->rep->TextCol($max_col, $max_col+1,	number_total( $this->subTotal + $this->taxTotal ));



        //             $this->rep->NewLine(-1);
        //             $this->rep->TextCol(3, 7,	_('TOTAL ORDER EX GST'));
        //             $this->rep->TextCol(7, 8,	number_total($tran->tax_included ? $SubTotal-$taxTotal: $SubTotal));


        $this->rep->Font();
        if( in_array($this->type, array(ST_SUPPAYMENT)) ) {
//             $this->rep->TextCol(1, 5,	$this->rep->company['curr_default'].":".price_in_words( $Total ,ST_CUSTPAYMENT));

            $this->rep->NewLine(-1);
            $this->rep->TextCol($max_col-2, $max_col,	_('Left to Allocate'));
            $this->rep->TextCol($max_col, $max_col+1,	number_total($this->leftAllocate));

            $this->rep->NewLine(-1);
            $this->rep->TextCol($max_col-2, $max_col,	_('Total Allocated'));
            $this->rep->TextCol($max_col, $max_col+1,	number_total($this->subTotal-$this->leftAllocate));

        }

        if( abs($this->discount) != 0 ){
            $this->rep->NewLine(-1);
            $this->rep->TextCol($max_col-2, $max_col, _('Total Discount'));
            $this->rep->TextCol($max_col, $max_col+1, number_total($this->discount));
        }



        if( count($this->taxes) > 0 ) foreach ($this->taxes AS $tax){
            if( abs($tax['amount']) != 0 ){
                $this->rep->NewLine(-1);
                $this->rep->TextCol($max_col-2, $max_col,	$tax['name']);
                $this->rep->TextCol($max_col, $max_col+1, number_total($tax['amount']) );
            }
        }

        if( abs($this->shipping) != 0 ){
            $this->rep->NewLine(-1);
            $this->rep->TextCol($max_col-2, $max_col,	_('Shipping'));
            $this->rep->TextCol($max_col, $max_col+1,number_total($this->shipping));
        }

        if( in_array($this->type, array(ST_SALESORDER, ST_SALESINVOICE)) ) {
            $this->rep->NewLine(-1);
            $this->rep->TextCol($max_col-2, $max_col,	_(' Sub-total'));
            $this->rep->TextCol(7, 8,	number_total($this->subTotal));
        }


    }

    // -----------------------------------------------------------------------------------------------------
    //DOCUMENT PRINTING DATA FUNCTION TAMBAHAN

    public function print_invoice(){
        $this->customer_trans_model = $this->ci->model('customer_trans',true);
        $this->contact_model = $this->ci->model('crm',true);

        $from = input_val("PARAM_0");
        $to = input_val("PARAM_1");
        $fno = explode("-", $from);
        $tno = explode("-", $to);
        $from = min($fno[0], $tno[0]);
        $to = max($fno[0], $tno[0]);

        $trans_where = array();
        $datact = array();
        $dataitems = array();
        $datainput = array();
        $tesing = array();

        $currency = input_val("PARAM_2");
        $email = input_val("PARAM_3");
        $pay_service = input_val("PARAM_4");
        $comments = input_val("PARAM_5");
        $customer = input_val("PARAM_6");
        $orientation = input_val("PARAM_7");

        $start_date = input_val("PARAM_8");
        if( $start_date ){
            $trans_where['tran_date >='] = date('Y-m-d',strtotime($start_date));
        }

        $end_date = input_val("PARAM_9");
        if( $end_date ){
            if( $start_date &&  strtotime($start_date) < strtotime($end_date))
                $trans_where['tran_date <='] = date('Y-m-d',strtotime($end_date));
        }

        $reference = input_val("PARAM_10");
        if( $reference ){
            $trans_where['reference'] = $reference;
        }
        $ind = 0;
        $pge =1;

        for ($i = $from; $i <= $to; $i++) {
            
            $cus_trans = $this->customer_trans_model->search_invoice(ST_SALESINVOICE,$i,$trans_where);
            array_push($datact, $cus_trans);

            if (empty($datact[$ind])){

            }else{
                $items = $this->customer_trans_model->trans_detail('*',array('debtor_trans_type'=>ST_SALESINVOICE,'debtor_trans_no'=>$datact[$ind]->trans_no));
                array_push($dataitems, $items);
                $sign = 1;
                $SubTotal = $discountTotal = $shippingTotal = $taxTotal = 0;
                $taxes = array();
                $detaildata = array();
                $products = array();
                $name = "";
                $amount = 0;
                $in = $ind;

                if (empty($dataitems[$in])){
                    $detaildata = array(
                            'stock_id' => "-",
                            'descriptionn' => "-",
                            'quantity' => 0,
                            'units' => 0,
                            'unit_price' => 0,
                            'discount_percent' => "0%",
                            'Net' => 0,
                        );
                        array_push($products, $detaildata);
                }else{
                    foreach ($dataitems[$in] AS $detail){

                    $line_price = $detail->unit_price * $detail->quantity;
                    $Net = round2($sign * ((1 - $detail->discount_percent) * $line_price), user_price_dec());
                    $discountTotal += $line_price -$Net;
                    $SubTotal += $Net;
                    $pre = '<w:p><w:r><w:t>';
                    $post = '</w:t></w:r></w:p>';

                    if( $detail->tax_type_id ){
                        $tax = tax_calculator($detail->tax_type_id,$line_price,$datact[$ind]->tax_included);

                        if( is_object($tax) ){
                            if( !isset($taxes[$detail->tax_type_id]) ){
                                $taxes[$detail->tax_type_id] = array('name'=>$tax->name ." (".$tax->code." ".$tax->rate."%)" ,'amount'=>0);
                            }
                            $taxes[$detail->tax_type_id]['amount'] += $tax->value;
                            $taxTotal += $tax->value;
                        }
                    }
                        $detaildata = array(
                            'stock_id' => $detail->stock_id,
                            'descriptionn' => $detail->description,
                            'quantity' => $sign*$detail->quantity,get_qty_dec($detail->stock_id),
                            'units' => $detail->units,
                            'unit_price' => number_total($detail->unit_price),
                            'discount_percent' => ($detail->discount_percent * 100 ."%"),
                            'Net' => number_total($Net),
                        );
                        array_push($products, $detaildata);
                    }
                }

                //---memanggil template name yang di pakai
                $templatePath = $this->ci->db->select("template_path")
                        ->from('files_template_invoice')
                        ->where('is_used', 1)
                        ->get()->row();

                foreach ($taxes AS $tax){
                    $name = $tax['name'].' Amount';
                    $amount = number_total($tax['amount']);
                }

                if ($datact[$ind]->cust_ref2 == null){
                    $cusref = "";
                }else{
                    $cusref = $datact[$ind]->cust_ref2;
                }

                $page = array(
                    "template" => $templatePath->template_path,
                    "tran_date" => sql2date($datact[$ind]->tran_date),
                    "reference" => $datact[$ind]->reference,
                    "page"  => $pge,
                    "payment_terms" => $datact[$ind]->payment_terms_name,
                    "delivery_from" => $datact[$ind]->DebtorName,
                    "address_from" => $datact[$ind]->address,
                    "delivery_to" =>  $datact[$ind]->DebtorName,
                    "address_to" => $datact[$ind]->address,
                    "customer_ref" => $cusref,
                    "salesman_name" => $this->contact_model->get_salesman($datact[$ind]->salesman,'salesman_name'),
                    "tax_id" => $datact[$ind]->tax_id,
                    "trans_no" => $datact[$ind]->trans_no,
                    "due_date" => $datact[$ind]->due_date ? sql2date($datact[$ind]->due_date) : null,
                    "products" => $products,
                    "currency" => $currency .":". price_in_words( $datact[$ind]->tax_included ? $SubTotal :$SubTotal+$taxTotal ,ST_CUSTPAYMENT),
                    "subtotal" => number_total($SubTotal),
                    "amount_name" => $name,
                    "amount"    => $amount,
                    "shipping"  => number_total($shippingTotal),
                    "totalex" => number_total($datact[$ind]->tax_included ? $SubTotal :$SubTotal+$taxTotal),
                    "totalinc" => number_total($datact[$ind]->tax_included ? $SubTotal-$taxTotal: $SubTotal),
                );
                // array_push($datainput, $page);
                $dataprint['data'] = $page;

                // array_push($tesing, $dataprint);
                array_push($tesing, $products);

            }
            $ind++;
            $pge++;
        }
        // header('location: '.$file_hasil);// download file\
        echo json_encode($tesing);
    }

    // -------------- Credit Note
    public function print_creditnote(){
        $this->customer_trans_model = $this->ci->model('customer_trans',true);
        $this->contact_model = $this->ci->model('crm',true);
        $from =       input_val('PARAM_0');
        $to =         input_val('PARAM_1');
        $fno = explode("-", $from);
        $tno = explode("-", $to);
        $from = min($fno[0], $tno[0]);
        $to = max($fno[0], $tno[0]);
        if (!$from || !$to) return;

        $datapush = array();
        $detailpush = array();
        // $detailforeach = array();
        $upprint = array();
        $ind = 0;
        $pge =1;
        $trans_where = array();
        $currency =   input_val('PARAM_2');
        $email =      input_val('PARAM_3');
        $paylink =    input_val('PARAM_4');
        $comments =   input_val('PARAM_5');
        $orientation =input_val('PARAM_6') ? 'L' : 'P' ;

        $start_date =   input_val('PARAM_7');
        if( $start_date ){
            $trans_where['trans.tran_date >='] = date('Y-m-d',strtotime($start_date));
        }

        $end_date =     input_val('PARAM_8');
        if( $end_date ){
            if( $start_date &&  strtotime($start_date) < strtotime($end_date))
                $trans_where['trans.tran_date <='] = date('Y-m-d',strtotime($end_date));
        }
        $reference =    input_val('PARAM_9');
        if( $reference ){
            $trans_where['trans.reference'] = $reference;
        }
        $limit = 1;

        for ($i = $from; $i <= $to; $i++) {
            $detailforeach = array();

            $trans = $this->customer_trans_model->get_customer_tran(ST_CUSTCREDIT,$i,$trans_where);
            array_push($datapush, $trans);

            if(empty($datapush[$ind])) {
                // -------------
            }else{
                $items =  $this->customer_trans_model->get_customer_trans_details(ST_CUSTCREDIT, $datapush[$ind]->trans_no);
                // $items = null;
                array_push($detailpush, $items);
                $sign = 1;
                $SubTotal = $discountTotal = $shippingTotal = $taxTotal = 0;
                $taxes = array();
                $namee = "";
                $amounte = 0;
                $in = $ind;

                if($detailpush[$in] == null){
                    $detaildata = array(
                            'stk_code' => "-",
                            'descriptionn' => "-",
                            'quantity' => 0,
                            'units' => 0,
                            'unit_price' => 0,
                            'discount_percent' => "0%",
                            'Net' => 0,
                        );
                    array_push($detailforeach, $detaildata);
                }else{
                    foreach ($detailpush[$in] AS $detail){
                        $line_price = $detail->unit_price * $detail->quantity;
                        $Net = round2($sign * ((1 - $detail->discount_percent) * $line_price), user_price_dec());
                        $discountTotal += $line_price -$Net;
                        $SubTotal += $Net;

                        if( $detail->tax_type_id ){
                            $tax = tax_calculator($detail->tax_type_id,$line_price,$tran->tax_included);

                            if( is_object($tax) ){
                                if( !isset($taxes[$detail->tax_type_id]) ){
                                    $taxes[$detail->tax_type_id] = array('name'=>$tax->name ." (".$tax->code." ".$tax->rate."%)" ,'amount'=>0);
                                }
                                $taxes[$detail->tax_type_id]['amount'] += $tax->value;
                                $taxTotal +=$tax->value;
                            }
                        }
                        // ---------------
                        $detaildata = array(
                            'stk_code' => $detail->stock_id,
                            'descriptionn' => $detail->description,
                            'quantity' => $sign*$detail->quantity,get_qty_dec($detail->stock_id),
                            'units' => $detail->units,
                            'unit_price' => number_total($detail->unit_price),
                            'discount_percent' => number_total($detail->discount_percent*100) . "%",
                            'Net' => number_total($Net),
                        );
                        array_push($detailforeach, $detaildata);
                    }
                }
                
                // --------------
                //---memanggil template name yang di pakai
                $templatePath = $this->ci->db->select("template_path")
                        ->from('files_template_credit_note')
                        ->where('is_used', 1)
                        ->get()->row();

                foreach ($taxes AS $tax){
                    $namee = $tax['name'].' Amount';
                    $amounte = number_total($tax['amount']);
                }

                if ($trans->cust_ref2 == null){
                    $cusref = "";
                }else{
                    $cusref = $datapush[$ind]->cust_ref2;
                }

                $page = array(
                    "template" => $templatePath->template_path,
                    "tran_date" => sql2date($datapush[$ind]->tran_date),
                    "reference" => $datapush[$ind]->reference,
                    "page"  => $pge,
                    "payment_terms" => $datapush[$ind]->payment_terms_name,
                    "order_to" => $datapush[$ind]->DebtorName,
                    "order_address" => $datapush[$ind]->address,
                    "delivery_to" =>  "",
                    "address_to" => "",
                    "customer_ref" => $cusref,
                    "sales_person" => $this->contact_model->get_salesman($datapush[$ind]->salesman,'salesman_name'),
                    "tax_id" => $datapush[$ind]->tax_id,
                    "order_no" => $datapush[$ind]->trans_no,
                    "delivery_date" => $datapush[$ind]->due_date ? sql2date($datapush[$ind]->due_date) : null,
                    "detail" => $detailforeach,
                    "currency" => $currency .":". price_in_words( $SubTotal ,ST_CUSTPAYMENT),
                    "subtotal" => number_total($SubTotal),
                    "amount_name" => $namee,
                    "amount"    => $amounte,
                    "shipping"  => number_total($shippingTotal),
                    "totalinc" => number_total($datapush[$ind]->tax_included ? $SubTotal-$taxTotal: $SubTotal),
                );
                $dataprint['data'] = $page;
                array_push($upprint, $detailforeach);

            }
            $pge++;
            $ind++;
        }
        echo json_encode($upprint);
    }

    //--------------------Statement Print
    public function print_statements(){}

    // ------------------------- Delivery Note
    public function print_deliverynote(){
        $this->customer_trans_model = $this->ci->model('customer_trans',true);
        $this->sale_order_model = $this->ci->model('sale_order',true);
        $this->contact_model = $this->ci->model('crm',true);

        $from = input_val('PARAM_0');
        $to = input_val('PARAM_1');

        if (! $from || ! $to)
            return;
        $fno = explode ( "-", $from );
        $tno = explode ( "-", $to );
        $from = min ( $fno [0], $tno [0] );
        $to = max ( $fno [0], $tno [0] );

        $trans_where = array();
        $datadeliv = array();
        $email =    input_val('PARAM_2');
        $packing_slip =  input_val('PARAM_3');
        $comments =     input_val('PARAM_4');
        $orientation =  input_val('PARAM_5') ? 'L' : 'P' ;

        $start_date =   input_val('PARAM_6');
        if( is_date($start_date) ){
            $trans_where['tran_date >='] = date('Y-m-d',strtotime($start_date));
        }

        $end_date = input_val('PARAM_7');
        if( is_date($end_date) ){
            $trans_where['tran_date <='] = date2sql($end_date);
        }

        $reference = input_val('PARAM_8');
        if( $reference ){
            $trans_where['reference'] = $reference;
        }

        $pge =1;
        $ind =0;
        $trans_deliv = array();
        $detail_td = array();
        $updata = array();

        for ($i = $from; $i <= $to; $i++) {
            $cus_trans_deliv = $this->customer_trans_model->search_invoice(ST_CUSTDELIVERY,$i,$trans_where);
            array_push($trans_deliv, $cus_trans_deliv);

            if(empty($trans_deliv[$ind])) {
                // --------------
            }else{
                $tran_type = ST_CUSTDELIVERY;

                $items_deliv = $this->customer_trans_model->trans_detail('*',array('debtor_trans_type'=>$tran_type,'debtor_trans_no'=>$trans_deliv[$ind]->trans_no),$tran_type);
                array_push($detail_td, $items_deliv);

                $sign = 1;
                $SubTotal = $discountTotal = $shippingTotal = $taxTotal = 0;
                $taxes = array();
                $detaildata = array();
                $products = array();
                $in = $ind;

                if(empty($detail_td[$ind])){
                     $detaildata = array(
                        'stock_id' => "",
                        'descriptionn' => "-",
                        'quantity' => 0,
                        'units' => 0,
                    );
                    array_push($products, $detaildata);
                }else{
                    foreach ($items_deliv AS $detail){
                        $line_price = $detail->unit_price * $detail->quantity;
                        $Net = round2($sign * ((1 - $detail->discount_percent) * $line_price), user_price_dec());
                        $discountTotal += $line_price -$Net;
                        $SubTotal += $Net;

                        if( $detail->tax_type_id ){
                            $tax = tax_calculator($detail->tax_type_id,$line_price,$cus_trans_deliv->tax_included);

                            if( is_object($tax) ){
                                if( !isset($taxes[$detail->tax_type_id]) ){
                                    $taxes[$detail->tax_type_id] = array('name'=>$tax->name ." (".$tax->code." ".$tax->rate."%)" ,'amount'=>0);
                                }
                                $taxes[$detail->tax_type_id]['amount'] += $tax->value;
                                $taxTotal += $tax->value;
                            }

                        }
                        // -----------------
                        $detaildata = array(
                            'stock_id' => $detail->stock_id,
                            'descriptionn' => $detail->description,
                            'quantity' => $sign*$detail->quantity,get_qty_dec($detail->stock_id),
                            'units' => $detail->units,
                        );
                        array_push($products, $detaildata);
                    }
                }
                    
                // ----------------
                //---memanggil template name yang di pakai
                $templatePath = $this->ci->db->select("template_path")
                        ->from('files_template_deliveries')
                        ->where('is_used', 1)
                        ->get()->row();

                foreach ($taxes AS $tax){
                    $name = $tax['name'].' Amount';
                    $amount = number_total($tax['amount']);
                }

                $cusref = $this->sale_order_model->get_field($trans_deliv[$ind]->order_,'customer_ref');

                $page = array(
                    "template" => $templatePath->template_path,
                    "tran_date" => sql2date($trans_deliv[$ind]->tran_date),
                    "reference" => $trans_deliv[$ind]->reference,
                    "page"  => $pge,
                    "payment_terms" => $trans_deliv[$ind]->payment_terms_name,
                    "delivery_to" =>  $trans_deliv[$ind]->DebtorName,
                    "address_to" => $trans_deliv[$ind]->address,
                    "customer_ref" => $cusref,
                    "salesman_name" => $this->contact_model->get_salesman($trans_deliv[$ind]->salesman,'salesman_name'),
                    "tax_id" => $trans_deliv[$ind]->tax_id,
                    "trans_no" => $trans_deliv[$ind]->trans_no,
                    "due_date" => $trans_deliv[$ind]->due_date ? sql2date($trans_deliv[$ind]->due_date) : null,
                    "products" => $products,
                );
                // array_push($datainput, $page);
                $datadeliv['data'] = $page;
                array_push($updata, $datadeliv);

            }
            $ind++;
            $pge++;
            // -------------------
            }
        // --------------------
        echo json_encode($updata);
    }

    // -------------Sales Order
    public function print_salesorder(){
        $this->sale_order_model = $this->ci->model('sale_order',true);
        $this->contact_model = $this->ci->model('crm',true);

        $order_where = array();
        $orderpush = array();
        $orderdetail = array();
        $uporder = array();
        $from =         input_val('PARAM_0');
        $to =           input_val('PARAM_1');

        if (!$from || !$to) return;

        $max_id = max($from,$to);
        $min_id = min($from,$to);
        $from = $min_id;
        $to = $max_id;

        $currency =     input_val('PARAM_2');
        $email =        input_val('PARAM_3');
        $print_as_quote = input_val('PARAM_4');
        $comments = input_val('PARAM_5');
        $orientation = input_val('PARAM_6') ? 'L' : 'P';

        $start_date = input_val('PARAM_7');
        if( $start_date ){
            $order_where['sorder.ord_date >='] = date('Y-m-d',strtotime($start_date));
        }

        $end_date =     input_val('PARAM_8');
        if( $end_date ){
            $order_where['sorder.ord_date <='] = date('Y-m-d',strtotime($end_date));
        }

        $reference = input_val('PARAM_9');
        if( $reference ){
            $order_where['sorder.reference'] = $reference;

        }

        $ind=0;
        $pge=1;

        for ($i = $from; $i <= $to; $i++) {

            $order = $this->sale_order_model->get_order($i, ST_SALESORDER,$order_where);
            array_push($orderpush, $order);
            // --------------
            if(empty($orderpush[$ind])){
                // ---------------------
            }else{
                $items = $this->sale_order_model->get_order_details($orderpush[$ind]->order_no,ST_SALESORDER,false);
                array_push($orderdetail, $items);

                $in = $ind;
                $sign = 1;
                $name = "";
                $amount = 0;
                $detailpush = array();
                $taxes = array();
                $subTotal = $discount = $shippingTotal = $taxTotal = 0;

                if (empty($items)) {
                    // -----------------
                    // $tes = "dddd";
                }else{
                    // $tes = "ada";
                    foreach ($items AS $detail){
            
                        $line_price = $detail->unit_price * $detail->qty;
                        $Net = number_total($sign * ((1 - $detail->discount_percent) * $line_price));
                        $discount += strtonumber($line_price) - strtonumber($Net);

                        if( $detail->tax_type_id ){

                            $tax = tax_calculator($detail->tax_type_id,$line_price,$orderpush[$ind]->tax_included);
                            if( is_object($tax) ){

                                if( !isset($taxes[$detail->tax_type_id]) ){
                                    $taxes[$detail->tax_type_id] = array('name'=>$tax->name ." (".$tax->code." ".$tax->rate."%)" ,'amount'=>0);
                                }
                                $taxes[$detail->tax_type_id]['amount'] += $tax->value;
                                $subTotal += $tax->price;
                                $taxTotal += $tax->value;
                            } else {
                                $subTotal += $Net;
                            }
                        }else {
                            $subTotal += $Net;
                        }

                        $detaildata = array(
                            'stock_id' => $detail->stk_code,
                            'descriptionn' => $detail->description,
                            'quantity' => number_format2($sign*$detail->qty,get_qty_dec($detail->stk_code)),
                            'units' => $detail->units,
                            'unit_price' => number_total($detail->unit_price),
                            'discount_percent' => number_total($detail->discount_percent*100) . "%",
                            'Net' => number_total($Net),
                        );
                        array_push($detailpush, $detaildata);
                    }

                }
                //-------------- 
                $templatePath = $this->ci->db->select("template_path")
                    ->from('files_template_sales_order')
                    ->where('is_used', 1)
                    ->get()->row();

                foreach ($taxes AS $tax){
                    $name = $tax['name'].' Amount';
                    $amount = number_total($tax['amount']);
                }

                $page = array(
                    "template" => $templatePath->template_path,
                    "tran_date" => sql2date($orderpush[$ind]->ord_date),
                    "reference" => $orderpush[$ind]->reference,
                    "page"  => $pge,
                    "payment_terms" => $orderpush[$ind]->terms_name,
                    "delivery_to" => $orderpush[$ind]->deliver_to,
                    "address_to" => $orderpush[$ind]->delivery_address,
                    "customer_ref" => "",
                    "sales_person" => $this->contact_model->get_salesman($orderpush[$ind]->salesman,'salesman_name'),
                    "tax_id" => $orderpush[$ind]->tax_id,
                    "order_no" => $orderpush[$ind]->order_no,
                    "due_date" => $orderpush[$ind]->delivery_date ? sql2date($orderpush[$ind]->delivery_date) : null,
                    "detail" => $detailpush,
                    "currency" => $currency .":". price_in_words( $subTotal + $taxTotal , ST_CHEQUE),
                    "amount_name" => $name,
                    "amount"    => $amount,
                    "totalinc" => number_total($subTotal + $taxTotal),
                );
                $dataprint['data'] = $page;
                array_push($uporder, $dataprint);
            }
        $ind++;
        $pge++;
        }
        // ------------
        echo json_encode($uporder);
    }

    // ----------- Sales Quotation
    public function print_salesquot(){
        $this->sale_order_model = $this->ci->model('sale_order',true);
        $this->contact_model = $this->ci->model('crm',true);

        $from =         input_val('PARAM_0');
        $to =           input_val('PARAM_1');

        if (! $from || ! $to)
            return;
        $fno = explode ( "-", $from );
        $tno = explode ( "-", $to );
        $from = min ( $fno [0], $tno [0] );
        $to = max ( $fno [0], $tno [0] );


        $trans_where = array();
        $currency =     input_val('PARAM_2');
        $email =        input_val('PARAM_3');
        $comments =     input_val('PARAM_4');
        $orientation =  input_val('PARAM_5') ? 'L' : 'P' ;

        $start_date =   input_val('PARAM_6');
        if( is_date($start_date) ){
            $trans_where['sorder.ord_date >='] = date('Y-m-d',strtotime($start_date));
        }

        $end_date =     input_val('PARAM_7');
        if( is_date($end_date) ){
            $trans_where['sorder.ord_date <='] = date('Y-m-d',strtotime($end_date));
        }

        $reference = input_val('PARAM_8');
        if( $reference ){
            $trans_where['sorder.reference'] = $reference;
        }

        $limit = 1;
        $ind=0;
        $pge=1;
        $quotdata = array();
        $quotdetail = array();
        // $detailup = array();
        $dataup = array();
        $quotprint = array();

        for ($i = $from; $i <= $to; $i++) {
            // unset($detailup);
            $detailup = array();
            $cus_trans = $this->sale_order_model->get_order($i, ST_SALESQUOTE,$trans_where);
            array_push($quotdata, $cus_trans);

            if(empty($quotdata[$ind])){
                // ------------
            }else{
                // ------------quotdata
                $items = $this->sale_order_model->get_order_details($quotdata[$ind]->order_no,ST_SALESQUOTE,false);
                array_push($quotdetail, $items);

                $in = $ind;
                $this->taxes_gst = array();
                $amount_total = $sub_total = $discount =  0;

                if (empty($items)) {
                    $detailpush = array(
                        'stk_code'=> "-",
                        'description'=>  "-",
                        'qty'=> 0,
                        'units'=> 0,
                        'price'=> 0,
                        'discount_percent'=> "0%",
                        'Net' => 0,
                    );
                    array_push($detailup, $detailpush);
                }else{
                    foreach ($items AS $detail) {
                        
                        if( isset($detail->gl_code) ){
                            $price = $detail->price;
                        } else {
                            $price = $detail->price*$detail->qty* (1-$detail->discount_percent);
                        }

                        $tax_amount = 0;
                        if( $detail->tax_type_id ){
                            $tax_detail = api_get('taxdetail/'.$detail->tax_type_id);
                            $tax = tax_calculator($detail->tax_type_id,$price,$quotdata[$ind]->tax_included,$tax_detail);
                            if( $tax->gst_03_type ){
                                if( !isset($this->taxes_gst[$tax->gst_03_type]  ) ){
                                    $this->taxes_gst[$tax->gst_03_type] = array('value'=>0,'name'=>$tax_detail->no.' '.$tax_detail->rate.'%' );
                                }
                                $this->taxes_gst[$tax->gst_03_type]['value'] += $tax->value;
                                $price = $tax->price ;
                                $tax_amount = $tax->value ;
                            }
                        }
                        $sub_total = $price;

                        $amount_total += $detail->price*$detail->qty;
                        if( !$quotdata[$ind]->tax_included ){
                            $amount_total += $tax_amount;
                        }
                        $discount += $detail->discount_percent*$detail->price*$detail->qty;

                        $detailpush = array(
                            'stk_code'=> $detail->stk_code,
                            'description'=>  $detail->description,
                            'qty'=> number_format($detail->qty),
                            'units'=> $detail->units,
                            'price'=> $detail->price,
                            'discount_percent'=> $detail->discount_percent*100 . "%",
                            'Net' => $sub_total,
                        );
                        array_push($detailup, $detailpush);
                    }
                }

                $sub_total = $quotdata[$ind]->freight_cost;

                $templatePath = $this->ci->db->select("template_path")
                    ->from('files_template_sales_quotation')
                    ->where('is_used', 1)
                    ->get()->row();

                $page = array(
                    "template" => $templatePath->template_path,
                    "tran_date" => sql2date($quotdata[$ind]->ord_date),
                    "reference" => $quotdata[$ind]->reference,
                    "page"  => $pge,
                    "payment_terms" => $quotdata[$ind]->terms_name,
                    "change_to" => $quotdata[$ind]->deliver_to,
                    "name" => $quotdata[$ind]->name,
                    "delivery_to" => $quotdata[$ind]->deliver_to,
                    "delivery_addres" => $quotdata[$ind]->delivery_address,
                    "customer_ref" => $quotdata[$ind]->customer_ref,
                    "sales_person" => $this->contact_model->get_salesman($cus_trans->salesman,'salesman_name'),
                    "tax_id" => $quotdata[$ind]->tax_id,
                    "order_no" => $quotdata[$ind]->order_no,
                    "delivery_date" => $quotdata[$ind]->delivery_date ? sql2date($quotdata[$ind]->delivery_date) : null,
                    "detail" => $detailup,
                    "currency" => "",
                    "subtotal" => number_format($sub_total),
                    "shipping" => number_format($quotdata[$ind]->freight_cost),
                    "totalex" => number_format($sub_total),
                    "totalinc" => number_format($quotdata[$ind]->total),
                );
                $dataprint['data'] = $page;
                array_push($quotprint, $dataprint);
            }
            $ind++;
            $pge++;
        }
        echo json_encode($quotprint);
    }

    // ------------Payment Recipts
    public function print_payment(){
        $this->sale_order_model = $this->ci->model('sale_order',true);


        $from =         input_val('PARAM_0');
        $to =           input_val('PARAM_1');
        $currency =     input_val('PARAM_2');
        $comments =     input_val('PARAM_3');
        $orientation =  input_val('PARAM_4');

        $start_date =   input_val('PARAM_5');
        if( !is_date($start_date) ){
            $start_date = null;
        } else {
            $start_date = date('Y-m-d',strtotime($start_date));
        }

        $end_date = input_val('PARAM_6');
        if( !is_date($end_date) ){
            $end_date = null;
        } else {
            $end_date = date('Y-m-d',strtotime($end_date));
        }

        $reference = input_val('PARAM_7');

        if (!$from || !$to)
            return;
        $orientation = ($orientation ? 'L' : 'P');
        $dec = user_price_dec();

        $fno = explode("-", $from);
        $tno = explode("-", $to);
        $from = min($fno[0], $tno[0]);
        $to = max($fno[0], $tno[0]);

        $paymentdata = array();
        $paymentdetail = array();
        $ind = 0;
        $pge =1;
        // $detailup = array();
        $dataup = array();
        $paymentprint = array();

        for ($i = $from; $i <= $to; $i++) {
            $detailup = array();

            if ($fno[0] == $tno[0])
                $types = array($fno[1]);
            else
                $types = array(ST_BANKDEPOSIT, ST_CUSTPAYMENT);

            foreach ($types as $j) {
                $order = $this->sale_order_model->get_receipt_CP($j, $i,$start_date,$end_date,$reference);
                array_push($paymentdata, $order);
                
                $in = $ind;

                if(empty($paymentdata[$ind])){
                    // --------
                }else{
                    $items = $this->sale_order_model->get_allocations_for_receipt($paymentdata[$ind]->debtor_no,$paymentdata[$ind]->type,$paymentdata[$ind]->trans_no);
                    array_push($paymentdetail, $items);
                    $Total = $discountTotal = $left_alloc = 0;

                    if(empty($items)){
                        $detail = array(
                            'trans_type'=> "",
                            'reference'=>  "",
                            'tran_date'=> "",
                            'due_date'=> "",
                            'price'=> "",
                            'left_alloc'=> "",
                            'Net' => "",
                        );
                        array_push($detailup, $detail);
                    }else{
                        foreach ($items AS $detail) {
                            $Total += $detail->price;
                            $left_alloc += $detail->left_alloc;
                            $detail = array(
                            'trans_type'=> tran_name($detail->trans_type),
                            'reference'=>  $detail->reference,
                            'tran_date'=> sql2date($detail->tran_date),
                            'due_date'=> sql2date($detail->due_date),
                            'price'=> number_total($detail->price),
                            'left_alloc'=> number_total($detail->left_alloc),
                            'Net' => number_total($detail->price-$detail->left_alloc),
                            );
                        array_push($detailup, $detail);
                        }
                    }
                    // -------------------
                    $templatePath = $this->ci->db->select("template_path")
                    ->from('files_template_receipts')
                    ->where('is_used', 1)
                    ->get()->row();

                    if ($paymentdata[$ind]->curr_code == null){
                        $cusref = "";
                    }else{
                        $cusref = $paymentdata[$ind]->curr_code;
                    }

                    $page = array(
                        "template" => $templatePath->template_path,
                        "tran_date" => sql2date($paymentdata[$ind]->tran_date),
                        "reference" => $paymentdata[$ind]->reference,
                        "page"  => $pge,
                        "payment_terms" => $paymentdata[$ind]->terms_name,
                        "delivery_to" => $paymentdata[$ind]->name,
                        "delivery_addres" => $paymentdata[$ind]->address,
                        "customer_ref" => $paymentdata[$ind]->cust_ref2,
                        "salesman_name" => "",
                        "gst_no" => $paymentdata[$ind]->gst_no,
                        "trans_no" => $paymentdata[$ind]->trans_no,
                        "due_date" => $paymentdata[$ind]->tran_date ? sql2date($paymentdata[$ind]->tran_date) : null,
                        "detail" => $detailup,
                        "currency" => $cusref .": ".price_in_words( $Total ,ST_CUSTPAYMENT),
                        "subtotal" => number_total($Total-$left_alloc),
                        "totallo" => number_total($left_alloc),
                        "totdiscount" => number_total($discountTotal),
                        "totreciept" => number_total($Total),
                    );
                    $dataprint['data'] = $page;
                    array_push($paymentprint, $dataprint);
                }

            }
            // --------------
            $ind++;
            $pge++;
        }
        echo json_encode($paymentprint);
    }

    // ------------- purchase order
    public function print_purchaseorder(){
        $this->purchase_model = $this->ci->model('purch_order',true);

        $from =         input_val('PARAM_0');
        $to =           input_val('PARAM_1');

        $max_id = max($from,$to);
        $min_id = min($from,$to);
        $from = $min_id;
        $to = $max_id;

        if (!$from || !$to)
            return;

        $query_where = array();

        $currency =     input_val('PARAM_2');
        $email =        input_val('PARAM_3');

        if( $email ){
            $this->pdf->email = true;

        }

        $comments =     input_val('PARAM_4');
        $orientation =  input_val('PARAM_5') ? 'L' : 'P';

        $start_date =   input_val('PARAM_6');
        if( is_date($start_date) ){
            $query_where['o.ord_date >='] = date('Y-m-d',strtotime($start_date));
        }

        $end_date =     input_val('PARAM_7');
        if( is_date($end_date) ){
            $query_where['o.ord_date <='] = date('Y-m-d',strtotime($end_date));
        }

        $reference =    input_val('PARAM_8');
        if( $reference ){
            $query_where['reference'] = $reference;
        }

        $orderdata = array();
        $orderprint = array();
        $ind = 0;
        $pge = 1;

        for ($i = $from; $i <= $to; $i++) {

            $query_where['order_no'] = $i;
            $order = $this->purchase_model->search($query_where);
            array_push($orderdata, $order);

            if (empty($orderdata[$ind])) {
                // ---------
            }else{

                $templatePath = $this->ci->db->select("template_path")
                    ->from('files_template_purchase_orders')
                    ->where('is_used', 1)
                    ->get()->row();

                if ($orderdata[$ind]->curr_code == null){
                    $cusref = "";
                }else{
                    $cusref = $orderdata[$ind]->curr_code;
                }

                $page = array(
                        "template" => $templatePath->template_path,
                        "tran_date" => sql2date($orderdata[$ind]->ord_date),
                        "reference" => $orderdata[$ind]->reference,
                        "page"  => $pge,
                        "payment_terms" => $orderdata[$ind]->payment_terms_name,
                        "order_to" => $orderdata[$ind]->supp_name,
                        "order_addres" => $orderdata[$ind]->address,
                        "supplier_ref" => $orderdata[$ind]->requisition_no,
                        "salesman_name" => "",
                        "gst_no" => $orderdata[$ind]->tax_id,
                        "due_date" => $orderdata[$ind]->ord_date ? sql2date($orderdata[$ind]->ord_date) : null,
                        "self_bill" =>"",
                        "detail" => $orderdata[$ind]->items,
                        "currency" => $cusref .": ".price_in_words( $orderdata[$ind]->total ,ST_CUSTPAYMENT),
                        "subtotal" => $orderdata[$ind]->total,
                        "totorder" => $orderdata[$ind]->total,
                    );
            $dataprint['data'] = $page;
            array_push($orderprint, $dataprint);
            $pge++;
            }
        $ind++;
        }

        echo json_encode($orderprint);
    }

    //------------ Remittance
    public function print_remittance(){
        $this->purchase = $this->ci->model('purchase',true);
        $this->supplier_module_model = module_model_load("supplier",'purchases');
        $this->supplier_model = $this->ci->model('supplier',true);

        $from =       input_val('PARAM_0');
        $to =         input_val('PARAM_1');

        $fno = explode("-", $from);
        $tno = explode("-", $to);
        $from = min($fno[0], $tno[0]);
        $to = max($fno[0], $tno[0]);
        if (!$from || !$to) return;

        $trans_where = array();
        $currency =   input_val('PARAM_2');
        $email =      input_val('PARAM_3');

        $comments =   input_val('PARAM_4');
        $orientation =input_val('PARAM_5') ? 'L' : 'P' ;

        $start_date =   input_val('PARAM_6');
        if( !is_date($start_date) ){
            $start_date = null;
        } else {
            $start_date = date('Y-m-d',strtotime($start_date));
        }

        $end_date =     input_val('PARAM_7');
        if( !is_date($end_date) ){
            $end_date = null;
        } else {
            $end_date = date('Y-m-d',strtotime($end_date));
        }

        $reference =    input_val('PARAM_8');

        $remitdata = array();
        $remitdetail = array();
        // $redetail = array();
        $redetail2 = array();
        $remitprint = array();

        $ind =0;
        $pge=1;

        for ($i = $from; $i <= $to; $i++){

            if ($fno[0] == $tno[0])
                $types = array($fno[1]);
            else
                $types = array(ST_BANKPAYMENT, ST_SUPPAYMENT, ST_SUPPCREDIT);

            foreach ($types as $j) {
                $redetail = array();


                $trans = $this->purchase->get_purchase_tran($j,$i,$start_date,$end_date,$reference);
                if( !$trans || !$trans->supplier_id ){
                    continue;
                }
                array_push($remitdata, $trans);
                $in = $ind;
                
                $supplier = $this->supplier_module_model->get_detail($remitdata[$ind]->supplier_id);
                $items = $this->supplier_model->get_alloc_supp_sql_ci($remitdata[$ind]->supplier_id, $remitdata[$ind]->type, $remitdata[$ind]->trans_no);
                array_push($remitdetail, $items);

                $Total = $subTotal = $discountTotal = $left_alloc = $leftAllocate = 0;
                
                if (! empty($remitdetail[$ind]))
                foreach ($remitdetail[$in] AS $detail) {
                    $subTotal += $detail->price;
                    $leftAllocate += $detail->left_alloc;
                    $left_alloc += $detail->left_alloc;
                    $Total += $detail->price;


                    $det = array(
                        'trans_type'=> tran_name($detail->trans_type),
                        'reference'=>  $detail->reference,
                        'tran_date'=> sql2date($detail->tran_date),
                        'due_date'=> sql2date($detail->due_date),
                        'price'=> number_total($detail->price),
                        'left_alloc'=> number_total($detail->left_alloc),
                        'Net' => number_total($detail->price-$detail->left_alloc),
                        );
                    array_push($redetail, $det);
                }else{
                    $Total += abs($remitdata[$ind]->Total);
                    $subTotal = abs($remitdata[$ind]->Total);
                    $leftAllocate = abs($remitdata[$ind]->Total) - abs($remitdata[$ind]->alloc);
                }

                // ------------
                $templatePath = $this->ci->db->select("template_path")
                ->from('files_template_remittances')
                ->where('is_used', 1)
                ->get()->row();

                if ($remitdata[$ind]->curr_code == null){
                    $cusref = "";
                }else{
                    $cusref = $remitdata[$ind]->curr_code;
                }

                $page = array(
                    "template" => $templatePath->template_path,
                    "tran_date" => sql2date($remitdata[$ind]->tran_date),
                    "reference" => $remitdata[$ind]->reference,
                    "page"  => $pge,
                    "payment_terms" => $remitdata[$ind]->terms,
                    "order_to" => $supplier->supp_name,
                    "order_addres" => $supplier->address,
                    "customer_ref" => $remitdata[$ind]->reference,
                    "type" => transaction_type_tostring($remitdata[$ind]->type),
                    "gst_no" => $remitdata[$ind]->gst_no,
                    "supref" => $remitdata[$ind]->cheque,
                    "due_date" => $remitdata[$ind]->tran_date ? sql2date($remitdata[$ind]->tran_date) : null,
                    "detail" => $redetail,
                    "currency" => $cusref .": ".price_in_words( $Total ,ST_CUSTPAYMENT),
                    "subtotal" => number_total($subTotal),
                    "totallo" => number_total($leftAllocate),
                    "totremit" => number_total($Total),
                );
                $dataprint['data'] = $page;
                array_push($remitprint, $dataprint);
            $pge++;
            $ind++;
            }
        }
        echo json_encode($remitprint);
    }

    // ---------------payment voucher
    public function print_paymentvoucher($trans_type=ST_BANKDEPOSIT){
        $this->bank_trans_model = module_model_load('trans','bank');
        $this->customer_model = module_model_load('customer','sales');
        $this->sale_tran_model = module_model_load('trans','sales');
        $this->supplier_model = module_model_load('supplier','purchases');
        $this->gl_trans_model = module_model_load('trans','gl');

        $trans_no = input_val('trans_no');
        if( strlen($trans_no) < 1 ){
            $trans_no = input_val('PARAM_0');
        }

        $where = array();

        $start_date = input_val("start_date");
        if($start_date){
            $where['bt.trans_date >='] = date2sql($start_date);
        }
        $end_date = input_val("end_date");
        if($end_date){
            $where['bt.trans_date <='] = date2sql($end_date);
        }
        $account = input_val('account');
        if($account){
            $where['bt.bank_act'] = $account;
        }

        $paydata = array();

        if( !empty($where) ){
            $trans = $this->bank_trans_model->get_trans($where);
            if( count($trans) > 0 ) foreach ($trans AS $tran){
            $detailaja = array();
                // -------------
                if( !is_object($tran) )
                    return;

                $payment_to = "";
                $payment_from = $tran->bank_account_name;

                switch ($tran->person_type_id) {
                    case 2:
                        $sale_tran = $this->sale_tran_model->get_tran($tran->type,$tran->trans_no);
                        $customer = $this->customer_model->get_detail($sale_tran->debtor_no);
                        if( isset($customer->debtor_ref) ){
                            $payment_to = trim($customer->debtor_ref);
                        }
                        if( isset($sale_tran->branch_detail->branch_ref) ){
                            if( strlen($payment_to) > 0 ){
                                $payment_to .= "/";
                            }
                            $payment_to .= $sale_tran->branch_detail->branch_ref;
                        }
                        break;
                    case 3:
                        $supplier = $this->supplier_model->get_detail($tran->person_id);
                        if( is_object($supplier) && isset($supplier->supp_name) ){
                            $payment_to = $supplier->supp_name;
                        }
                        break;
                    default:
                        $payment_to= $tran->person_id;
                        break;
                }
                // ---------------

                if( $tran->type==ST_BANKDEPOSIT ){
                    $pay_change = $payment_to;
                    $payment_to = $payment_from;
                    $payment_from = $pay_change;
                }
                // ---------------

                $currency_originer = $tran->bank_curr_code;
                $exc_rate = get_exchange_rate_from_home_currency($currency_originer, $tran->trans_date);

                $gl_trans = $this->gl_trans_model->get_gl_trans($tran->type, $tran->trans_no);
                $total_amount = 0;
                foreach ($gl_trans AS $row){
                    $debit = $credit = 0;
                    if( $row->amount >0 ){
                        $total_amount += $row->amount;
                    }

                    $number = ( $row->amount > 0 ) ? $row->amount : 0;
                    $number2 = ( $row->amount <0 ) ? abs($row->amount) : 0;

                    $detail = array(
                        'account'      => (isset($row->account)) ? $row->account : "",
                        'account_name' => (isset($row->account_name)) ? $row->account_name : "",
                        'debit'     => $number > 0 ? number_total($number/$exc_rate) : "",
                        'credit'    => $number2 > 0 ? number_total($number2/$exc_rate) : "",
                        'memo'      => (isset($row->memo_)) ? $row->memo_ : "",
                    );
                    array_push($detailaja, $detail);
                }

                // ------------
                $templatePath = $this->ci->db->select("template_path")
                ->from('files_template_bank_payment')
                ->where('is_used', 1)
                ->get()->row();

                $data = array("",
                    "template" => $templatePath->template_path,
                    "tran_date" => sql2date($tran->trans_date),
                    "date" => sql2date($tran->trans_date),
                    'trans_no'=>"#".$tran->trans_no,
                    'reference'=>$tran->ref,
                    'payment_from'=>html_entity_decode($payment_from),
                    'payment_to'=>html_entity_decode($payment_to),
                    'cheque'=>html_entity_decode($tran->cheque),
                    'detail' => $detailaja,
                    'amount' => 'AMOUNT IN WORD: '.price_in_words( $total_amount ,ST_CUSTPAYMENT),

                );
                $dataprint['data'] = $data;
                array_push($paydata, $dataprint);
            }
        }
        echo json_encode($paydata);
    }

    // ---------------Deposit Voucher
    public function print_depositvoucher($trans_type=ST_BANKPAYMENT){
        $this->bank_trans_model = module_model_load('trans','bank');
        $this->customer_model = module_model_load('customer','sales');
        $this->sale_tran_model = module_model_load('trans','sales');
        $this->supplier_model = module_model_load('supplier','purchases');
        $this->gl_trans_model = module_model_load('trans','gl');

        $trans_no = input_val('trans_no');
        if( strlen($trans_no) < 1 ){
            $trans_no = input_val('PARAM_0');
        }

        $where = array();

        $start_date = input_val("start_date");
        if($start_date){
            $where['bt.trans_date >='] = date2sql($start_date);
        }
        $end_date = input_val("end_date");
        if($end_date){
            $where['bt.trans_date <='] = date2sql($end_date);
        }
        $account = input_val('account');
        if($account){
            $where['bt.bank_act'] = $account;
        }

        $depdata = array();

        if( !empty($where) ){
            $trans_depo = $this->bank_trans_model->get_trans($where);
            if( count($trans_depo) > 0 ) foreach ($trans_depo AS $tran){
            $detailaja = array();
                // -------------
                if( !is_object($tran) )
                    return;

                $payment_to = "";
                $payment_from = $tran->bank_account_name;

                switch ($tran->person_type_id) {
                    case 2:
                        $sale_tran = $this->sale_tran_model->get_tran($tran->type,$tran->trans_no);
                        $customer = $this->customer_model->get_detail($sale_tran->debtor_no);
                        if( isset($customer->debtor_ref) ){
                            $payment_to = trim($customer->debtor_ref);
                        }
                        if( isset($sale_tran->branch_detail->branch_ref) ){
                            if( strlen($payment_to) > 0 ){
                                $payment_to .= "/";
                            }
                            $payment_to .= $sale_tran->branch_detail->branch_ref;
                        }
                        break;
                    case 3:
                        $supplier = $this->supplier_model->get_detail($tran->person_id);
                        if( is_object($supplier) && isset($supplier->supp_name) ){
                            $payment_to = $supplier->supp_name;
                        }
                        break;
                    default:
                        $payment_to= $tran->person_id;
                        break;
                }
                // ---------------

                if( $tran->type==ST_BANKPAYMENT ){
                    $pay_change = $payment_to;
                    $payment_to = $payment_from;
                    $payment_from = $pay_change;
                }
                // ---------------

                $currency_originer = $tran->bank_curr_code;
                $exc_rate = get_exchange_rate_from_home_currency($currency_originer, $tran->trans_date);

                $gl_trans = $this->gl_trans_model->get_gl_trans($tran->type, $tran->trans_no);
                $total_amount = 0;
                foreach ($gl_trans AS $row){
                    $debit = $credit = 0;
                    if( $row->amount >0 ){
                        $total_amount += $row->amount;
                    }

                    $number = ( $row->amount > 0 ) ? $row->amount : 0;
                    $number2 = ( $row->amount <0 ) ? abs($row->amount) : 0;

                    $detail = array(
                        'account'      => (isset($row->account)) ? $row->account : "",
                        'account_name' => (isset($row->account_name)) ? $row->account_name : "",
                        'debit'     => $number > 0 ? number_total($number/$exc_rate) : "",
                        'credit'    => $number2 > 0 ? number_total($number2/$exc_rate) : "",
                        'memo'      => (isset($row->memo_)) ? $row->memo_ : "",
                    );
                    array_push($detailaja, $detail);
                }

                // ------------
                $templatePath = $this->ci->db->select("template_path")
                ->from('files_template_bank_deposit')
                ->where('is_used', 1)
                ->get()->row();

                $data = array("",
                    "template" => $templatePath->template_path,
                    "tran_date" => sql2date($tran->trans_date),
                    "date" => sql2date($tran->trans_date),
                    'trans_no'=>"#".$tran->trans_no,
                    'reference'=>$tran->ref,
                    'payment_from'=>html_entity_decode($payment_from),
                    'payment_to'=>html_entity_decode($payment_to),
                    'cheque'=>html_entity_decode($tran->cheque),
                    'detail' => $detailaja,
                    'amount' => 'AMOUNT IN WORD: '.price_in_words( $total_amount ,ST_CUSTPAYMENT),

                );
                $dataprint['data'] = $data;
                array_push($depdata, $dataprint);
            }
        }
        echo json_encode($depdata);
    }

    // ---------------Deposit Voucher
    public function print_transfervoucher($trans_type=ST_BANKTRANSFER){
        $this->bank_trans_model = module_model_load('trans','bank');
        $this->customer_model = module_model_load('customer','sales');
        $this->sale_tran_model = module_model_load('trans','sales');
        $this->supplier_model = module_model_load('supplier','purchases');
        $this->gl_trans_model = module_model_load('trans','gl');

        $trans_no = input_val('trans_no');
        if( strlen($trans_no) < 1 ){
            $trans_no = input_val('PARAM_0');
        }

        $where = array();

        $start_date = input_val("start_date");
        if($start_date){
            $where['bt.trans_date >='] = date2sql($start_date);
        }
        $end_date = input_val("end_date");
        if($end_date){
            $where['bt.trans_date <='] = date2sql($end_date);
        }
        $account = input_val('account');
        if($account){
            $where['bt.bank_act'] = $account;
        }

        $transferdata = array();

        if( !empty($where) ){
            $transfer = $this->bank_trans_model->get_trans($where);
            if( count($transfer) > 0 ) foreach ($transfer AS $tran){
            $detailaja = array();
                // -------------
                if( !is_object($tran) )
                    return;

                $payment_to = "";
                $payment_from = $tran->bank_account_name;

                switch ($tran->person_type_id) {
                    case 2:
                        $sale_tran = $this->sale_tran_model->get_tran($tran->type,$tran->trans_no);
                        $customer = $this->customer_model->get_detail($sale_tran->debtor_no);
                        if( isset($customer->debtor_ref) ){
                            $payment_to = trim($customer->debtor_ref);
                        }
                        if( isset($sale_tran->branch_detail->branch_ref) ){
                            if( strlen($payment_to) > 0 ){
                                $payment_to .= "/";
                            }
                            $payment_to .= $sale_tran->branch_detail->branch_ref;
                        }
                        break;
                    case 3:
                        $supplier = $this->supplier_model->get_detail($tran->person_id);
                        if( is_object($supplier) && isset($supplier->supp_name) ){
                            $payment_to = $supplier->supp_name;
                        }
                        break;
                    default:
                        $payment_to= $tran->person_id;
                        break;
                }
                // ---------------

                if( $tran->type==ST_BANKTRANSFER ){
                    $pay_change = $payment_to;
                    $payment_to = $payment_from;
                    $payment_from = $pay_change;
                }
                // ---------------

                $currency_originer = $tran->bank_curr_code;
                $exc_rate = get_exchange_rate_from_home_currency($currency_originer, $tran->trans_date);

                $gl_trans = $this->gl_trans_model->get_gl_trans($tran->type, $tran->trans_no);
                $total_amount = 0;
                foreach ($gl_trans AS $row){
                    $debit = $credit = 0;
                    if( $row->amount >0 ){
                        $total_amount += $row->amount;
                    }

                    $number = ( $row->amount > 0 ) ? $row->amount : 0;
                    $number2 = ( $row->amount <0 ) ? abs($row->amount) : 0;

                    $detail = array(
                        'account'      => (isset($row->account)) ? $row->account : "",
                        'account_name' => (isset($row->account_name)) ? $row->account_name : "",
                        'debit'     => $number > 0 ? number_total($number/$exc_rate) : "",
                        'credit'    => $number2 > 0 ? number_total($number2/$exc_rate) : "",
                        'memo'      => (isset($row->memo_)) ? $row->memo_ : "",
                    );
                    array_push($detailaja, $detail);
                }

                // ------------
                $templatePath = $this->ci->db->select("template_path")
                ->from('files_template_bank_accout')
                ->where('is_used', 1)
                ->get()->row();

                $data = array("",
                    "template" => $templatePath->template_path,
                    "tran_date" => sql2date($tran->trans_date),
                    "date" => sql2date($tran->trans_date),
                    'trans_no'=>"#".$tran->trans_no,
                    'reference'=>$tran->ref,
                    'payment_from'=>html_entity_decode($payment_from),
                    'payment_to'=>html_entity_decode($payment_to),
                    'cheque'=>html_entity_decode($tran->cheque),
                    'detail' => $detailaja,
                    'amount' => 'AMOUNT IN WORD: '.price_in_words( $total_amount ,ST_CUSTPAYMENT),

                );
                $dataprint['data'] = $data;
                array_push($transferdata, $dataprint);
            }
        }
        echo json_encode($transferdata);
    }

    // ---------------Bank Reconcile
    public function print_reconcile(){}


    // -------------------------------------------------------------------------------------------------------------------
    //manage crud template document printing
    function manage_template(){
        $type = get_instance()->uri->segment(4);
        global $Ajax;

        if($type == '107'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Invoice"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/invoice.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '113'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Credit Notes"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/credit_note.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '110'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Deliveries"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/deliveries.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == 'statements'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Statements"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/statements.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '109'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Sales Orders"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/sales_order.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '111'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Sales Quotations"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/sales_quotation.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '112'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Receipts"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/receipts.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '209'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Purchase Orders"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/purchase_orders.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '210'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Remittances"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/remittances.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '1'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Bank Payment"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/bank_payment.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '2'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Bank Deposit"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/bank_deposit.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == '4'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Bank Account Transfer Voucher"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/bank_accout.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }else if($type == 'reconcile'){
            $Ajax->activate('_page_body');
            page(_("Manage Template Bank Reconcile"));
            start_form();
            box_start();

            require_once(BASEPATH.'../ci_module/report/views/bank_reconcile.php');

            box_footer();
            box_end();
            end_form();
            end_page();
        }
    }

    //function for template invoice
    public function do_upload_invoice() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_invoice', $data);
                echo "Upload Finished";
		//echo json_encode($path);
            }
        }
    }

    public function get_templates_invoice() {

        $q = "SELECT * FROM files_template_invoice ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_invoice() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_invoice SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_invoice SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_invoice() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_invoice WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_invoice WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }
    // ---------------------------------------------------
    //function for template credite_note
    public function do_upload_credit_note() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_credit_note', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_credit_note() {

        $q = "SELECT * FROM files_template_credit_note ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_credit_note() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_credit_note SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_credit_note SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_credit_note() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_credit_note WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_credit_note WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }

    // ---------------------------------------------------
    //function for template deliveries
    public function do_upload_deliveries() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_deliveries', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_deliveries() {

        $q = "SELECT * FROM files_template_deliveries ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_deliveries() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_deliveries SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_deliveries SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_deliveries() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_deliveries WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_deliveries WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }
    // ------------------------------------
    //function for template sales_order
    public function do_upload_sales_order() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_sales_order', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_sales_order() {

        $q = "SELECT * FROM files_template_sales_order ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_sales_order() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_sales_order SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_sales_order SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_sales_order() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_sales_order WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_sales_order WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }

    // ------------------------------------
    //function for template sales_quotation
    public function do_upload_sales_quotation() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_sales_quotation', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_sales_quotation() {

        $q = "SELECT * FROM files_template_sales_quotation ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_sales_quotation() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_sales_quotation SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_sales_quotation SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_sales_quotation() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_sales_quotation WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_sales_quotation WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }

    // ------------------------------------
    //function for template receipts
    public function do_upload_receipts() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_receipts', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_receipts() {

        $q = "SELECT * FROM files_template_receipts ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_receipts() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_receipts SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_receipts SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_receipts() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_receipts WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_receipts WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }

    // ------------------------------------
    //function for template receipts
    public function do_upload_statements() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_statements', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_statements() {

        $q = "SELECT * FROM files_template_statements ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_statements() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_statements SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_statements SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_statements() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_statements WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_statements WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }

    //function for template purchase_orders
    public function do_upload_purchase_orders() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_purchase_orders', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_purchase_orders() {

        $q = "SELECT * FROM files_template_purchase_orders ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_purchase_orders() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_purchase_orders SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_purchase_orders SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_purchase_orders() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_purchase_orders WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_purchase_orders WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }

    //function for template remittances
    public function do_upload_remittances() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_remittances', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_remittances() {

        $q = "SELECT * FROM files_template_remittances ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_remittances() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_remittances SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_remittances SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_remittances() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_remittances WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_remittances WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }

    //function for template bank_payment
    public function do_upload_bank_payment() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_bank_payment', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_bank_payment() {

        $q = "SELECT * FROM files_template_bank_payment ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_bank_payment() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_bank_payment SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_bank_payment SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_bank_payment() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_bank_payment WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_bank_payment WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }

     //function for template bank_deposit
    public function do_upload_bank_deposit() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_bank_deposit', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_bank_deposit() {

        $q = "SELECT * FROM files_template_bank_deposit ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_bank_deposit() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_bank_deposit SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_bank_deposit SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_bank_deposit() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_bank_deposit WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_bank_deposit WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }


     //function for template bank_accout
    public function do_upload_bank_accout() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_bank_accout', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_bank_accout() {

        $q = "SELECT * FROM files_template_bank_accout ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_bank_accout() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_bank_accout SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_bank_accout SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_bank_accout() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_bank_accout WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_bank_accout WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }

     //function for template bank_reconcile
    public function do_upload_bank_reconcile() {
        $template_name = $_POST["template_name"];
        
        $path = BASEPATH."../company/words/";

        $temp = explode(".", $_FILES["file"]["name"]);
        $temp2 = explode(".", $_FILES["file2"]["name"]);
        $ext1 = end($temp);
        $ext2 = end($temp2);
        $filename = $filename = md5(date("Y-m-d H:i:s") . $template_name);
        $filename1 = $filename . '.' . $ext1;
        $filename2 = $filename . '.' . $ext2;

        // echo print_r($data22);

        if ($_FILES["file"]["error"] > 0 || $_FILES["file2"]["error"] > 0) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }

            if ($_FILES["file2"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file2"]["error"] . "<br>";
            }
        } else {
            if (file_exists($path . $filename1) || file_exists($path . $filename2)) {
                echo "Template name already exist";
            } else {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $filename1);
                move_uploaded_file($_FILES["file2"]["tmp_name"], $path . $filename2);

                $data = array(
                    'template_path' => $filename1,
                    'template_screenshot' => $filename2,
                    'template_name' => $template_name,
                    'is_used' => '0'
                );

                $this->ci->db->insert('files_template_bank_reconcile', $data);
                echo "Upload Finished";
            }
        }
    }

    public function get_templates_bank_reconcile() {

        $q = "SELECT * FROM files_template_bank_reconcile ORDER BY is_used DESC ";
        $dat = $this->ci->db->query($q);
        $data = array("data" => $dat->result());
        echo json_encode($data);
    }

    public function use_template_bank_reconcile() {

        $id = $this->ci->input->post('template_id');

        $q1 = "UPDATE files_template_bank_reconcile SET is_used = '0'";
        $this->ci->db->query($q1);
        $q2 = "UPDATE files_template_bank_reconcile SET is_used = '1' WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
    }

    function delete_template_bank_reconcile() {
        $id = $this->ci->input->post('template_id');

        $q = "SELECT * FROM files_template_bank_reconcile WHERE id = '" . $id . "'";

        $files = $this->ci->db->query($q)->row();

        unlink(BASEPATH."../company/words/" . $files->template_path);
        unlink(BASEPATH."../company/words/" . $files->template_screenshot);

        $q2 = "delete from files_template_bank_reconcile WHERE id = '" . $id . "'";
        $this->ci->db->query($q2);
        return $q2;
    }
}
ENDIF;