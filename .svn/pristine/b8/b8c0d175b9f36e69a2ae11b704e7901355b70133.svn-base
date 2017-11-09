<?php
class GstForm3  {
    function __construct() {
        $ci = get_instance();
        $this->tax_model = $ci->model('tax',true);
        $this->model = module_model_load('form_3');

        $this->report = module_control_load('report','report');
        $this->api = $ci->api;
        $this->db = $ci->db;

        $this->get_val();
    }

    private function get_val(){
        $this->date_from = input_val('start_date');
        if( $this->date_from ){
            $this->date_from = date('Y-m-d',strtotime($this->date_from) );
        }
        $this->date_to =  input_val('end_date');
        if( $this->date_to ){
            $this->date_to = date('Y-m-d',strtotime($this->date_to) );
        }
//         $this->date_from = '01-4-2016';
//         $this->date_from = '01-11-2016';
	    $this->date_from = date2sql($this->date_from);
	    $this->date_to = date2sql($this->date_to);
    }

    function index(){


	    $output = (input_val('ouput_xml')) ? 'xml': 'pdf';
	    if( input_val('ouput_text') ){
	        $output = 'tap';
	    }
	    if( $this->date_from || $this->date_to ){
	        /*
	         * remove Kastam 150921
	         */
	        /*
	        $sale_model = get_instance()->model('sale',true);
            $delivery_days_rule = $sale_model->delivery_daysRule($this->date_to,$this->date_from);
            if( $delivery_days_rule['total'] > 0){
                display_error('There are open Delivery Note yet to invoice and over 21 days, please issue invoice to those Delivery Note before proceed GST Form 3');
                if( in_ajax() ){
                    return false;
                } else {
                    return $this->form();
                }

            }
            */
	    	$this->map_gst();
	    	$this->get_debtor_trans();
	    	$this->get_purchase();
	    	$this->get_bank_payments();

	    	if( !isset($this->values['5b']) ){
	    		$this->values['5b'] = 0;
	    	}
	    	$this->values['5b'] = abs($this->values['5b']);
	    	if( !isset($this->values['6b']) ){
	    		$this->values['6b'] = 0;
	    	}
	    	$this->values['6b'] = abs($this->values['6b']);

	    	if( $this->values['5b'] > $this->values['6b'] ){
	    		$this->values['7'] = $this->values['5b'] - $this->values['6b'];
	    	} elseif( $this->values['5b'] < $this->values['6b'] ){
	    		$this->values['8'] = $this->values['6b'] - $this->values['5b'];
	    	}

	    	if( isset($this->values['14']) ){
	    		$this->values['15'] = $this->values['14']*0.06;
	    	}

			foreach ($this->map_value AS $key=>$map){
				if( !array_key_exists($key, $this->values) ){
					$this->values[$key] = NULL;
				}
			}
			foreach ($this->map_gsp AS $key=>$map){
				if( !array_key_exists($key, $this->values) ){
					$this->values[$key] = NULL;
				}
			}

            if( $output=='xml' ){
                return $this->gaf_output();
            } else if ($output=='tap') {
                return $this->tap_output();
            }
	    	return $this->pdf_output();
	    } else {
	        $this->form();
	    }
    }

    private function form(){
        $date = new DateTime('now');
        $date->modify('last day of this month');
        $date_first = new DateTime('now');
        $date_first->modify('first day of this month');

        $this->report->fields = array(
            'start_date' => array('type'=>'qdate','title'=>_('Start Date'),'value'=>$date_first->format('d-m-Y') ),
            'end_date' => array('type'=>'qdate','title'=>_('End Date'),'value'=>$date->format('d-m-Y') ),
        );
        $submit = array(
            'UPDATE_ITEM'=>array('Print GST Form 3',"default"),
            'ouput_xml'=>array('Export GAF',false),
            'ouput_text'=>array('Export TAP',false)
        );
        $this->report->form('GST Form 3',$submit);


    }

    function pdf_output(){
        $temp = ROOT.'/company/gst3-150718.pdf';
        if( !class_exists('TCPDF') ){
            require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/config/tcpdf_config.php');
            require_once(BASEPATH.'thirdparty/tcpdf_6_2_8/tcpdf.php');

        }
        require_once(BASEPATH.'thirdparty/FPDI-1.5.4/fpdi.php');
        $arr = get_company_prefs();

        $this->pdf = new FPDI('P','mm'); //FPDI extends TCPDF
        $this->pdf->SetAuthor('QuanNH');
        $this->pdf->SetTitle('GST form 3');
        $this->pdf->SetSubject('GST form 3');
        // 		$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $this->pdf->setPrintFooter(false);
        $this->pdf->setPrintHeader(false);
        $this->pdf->SetFont('helvetica', '', 9);

        $pages = $this->pdf->setSourceFile( $temp );

        // page 1
        $this->pdf->AddPage();
        $this->pdf->useTemplate( $this->pdf->ImportPage(1), 0, 0 );
        $this->pdf->Text(62, 156, $arr['gst_no']);
        $this->pdf->Text(62, 165, html_entity_decode($arr['coy_name']) );
        $this->pdf->Text(115, 201.5, date('d - m - Y',strtotime($this->date_from) ));
        $this->pdf->Text(115, 214, date('d - m - Y',strtotime($this->date_to) ));

        $endDate_time = strtotime($this->date_to);
        // 		$endDate_nextmount = date('m-Y',strtotime($this->date_to." +2 month") );
        // 		$endDate_nextmount = '01-'.$endDate_nextmount;
        // 		$this->pdf->Text(115, 227, date('d - m - Y',strtotime($endDate_nextmount.' -1 day')) );
        // 		$endDate_nextmount = date('m-Y',strtotime($this->date_to." +2 Month") );
        // 		$endDate_nextmount = '01-'.$endDate_nextmount;
        $this->pdf->Text(115, 227, add_months($this->date_to,1) );

        if( $this->values['5a'] != 0 ){
            $this->pdf->Text(124, 252.3, number_total($this->values['5a'],true) );
        }
        if( $this->values['5b'] != 0 ){
            $this->pdf->Text(124, 263, number_total($this->values['5b'],true) );
        }

        // page 2
        $this->pdf->AddPage();
        $this->pdf->useTemplate( $this->pdf->ImportPage(2), 0, 0 );

        if( $this->values['6a'] != 0 ){
            $this->pdf->Text(124, 51.8, number_total($this->values['6a'],true) );
        }
        if( $this->values['6b'] != 0 ){
            $this->pdf->Text(124, 62.3, number_total($this->values['6b'],true) );
        }
        $this->pdf->Text(124, 83.4, number_total($this->values['7'],true) );
        $this->pdf->Text(124, 102.6, number_total($this->values['8'],true) );

        for ($i=10; $i<=18 ; $i++){
            if( isset($this->values[$i]) ){
                $this->pdf->Text(122.8, 156.65+(13.2*($i-10) ), money_total_format($this->values[$i]) );
            }

        }

        // page 3
        $this->pdf->AddPage();
        $this->pdf->useTemplate( $this->pdf->ImportPage(3), 0, 0 );

        $msic_items = api_get("msic2/msic_items");
        $msics = array();
        foreach ($msic_items AS $msic_i){
            $msics[$msic_i->id] = $msic_i->id;
        }
        $msic_line = 1;
        $output_tax_other = 0;

        if( count($this->msic) > 0 ){
            arsort($this->msic);
            foreach ($this->msic AS $co=>$output_tax){
                if( $co != ''  && in_array($co,$msics) && $msic_line <= 5){
                    $this->msic_line($co,$output_tax,$output_tax/$this->msic_total*100,$msic_line);
                    $msic_line++;
                } else {
                    $output_tax_other+= $output_tax;
                }
            }
        }

        if( $msic_line <= 5 ){
            for ($ii=5; $ii >= $msic_line; $ii--){
                $this->msic_line(null,0,0,$ii);
            }
        }
        if( $this->msic_total > 0 ){
            $this->msic_line(null,$output_tax_other,$output_tax_other/$this->msic_total*100,6);
        }

        $this->msic_line(null,$this->msic_total,null,7);

        // 		$this->pdf->Output( 'newTest.pdf', 'I' );
        $fname= '/company/0/pdf_files/gst-form3.pdf';
//         $this->pdf->Output();
        $this->pdf->Output(ROOT.$fname,'F');
        global $Ajax;

        if (user_rep_popup())
            $Ajax->popup(site_url($fname));
    }

    var $msic = array();
    var $msic_total = 0;
    private function map_gst(){
        $this->map_value = array(
            '5a'=>array('SR','DS'),
            '6a'=>array('TX','IM','TX-E43','TX-RE'),
            '10'=>array('ZRL'),
            '11'=>array('ZRE'),
            '12'=>array('ES','ES43'),
            '13'=>array('RS'),
            '14'=>array('IS'),

        );
        $this->map_gsp = array(
            '5b'=>array('SR','DS','AJS'),
            '6b'=>array('TX','IM','TX-E43','TX-RE','AJP'),
            '15'=>array('IS'),
        );
        $this->values = array('7'=>0,'8'=>0,'16'=>0);

        $tax_code = api_get("tax_code");

        $this->tax_code = array();
        if( !empty($tax_code) && !empty($tax_code->options) ){ foreach ($tax_code->options AS $taxcodeid=>$taxcodeval){
            $this->tax_code[$taxcodeid] = $taxcodeval->title;
        }}


        $tax_items = api_get('taxdetail');
        $this->tax = array();
        foreach ($tax_items AS $tax){
            $this->tax[$tax->id] = $tax;
        }

    }

    function get_debtor_trans(){

//         $customer_trans_new = $this->cus_trans_model->gst_grouping( $this->date_from, $this->date_to);
        $customer_trans_new = $this->model->sale_trans( $this->date_from, $this->date_to);

        foreach ($customer_trans_new AS $ite){ if( $ite->tax_id ){
            if( !array_key_exists($ite->tax_id,$this->tax) ){
                continue;
            }
            $tax = $this->tax[$ite->tax_id];
            $tax_type_code = $this->tax_code[$tax->gst_03_type];

            if( $ite->curr_rate ){
                $price = $ite->unit_price*$ite->curr_rate;
            } else {
                $price = $ite->unit_price;
            }
            if( $ite->type=='CCN' ){
                $price = -$price;
            }

            foreach ($this->map_value AS $position=>$map ){
                if( in_array($tax_type_code,$map) ){
                    $this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,$ite->discount_percent,$position);
                }
            }
            foreach ($this->map_gsp AS $position=>$map ){
                if( in_array($tax_type_code,$map) ){
                    $this->calculator_item_gst_amount($price,$ite->quantity,$ite->tax_included,$tax->rate,$ite->discount_percent,$position);
                }
            }

            if( !isset($this->msic[$ite->msic] ) ){
                $this->msic[$ite->msic] = 0;
            }

            if( $ite->tax_included ){
                $tax = $tax->rate/(100+$tax->rate)*$price*$ite->quantity*( 1- $ite->discount_percent);
            } else {
                $tax = $price*($tax->rate/100)*$ite->quantity*( 1- $ite->discount_percent);
            }

            $this->msic[$ite->msic] += $tax;
            $this->msic_total += $tax;
        }}

//         $customer_trans_old = $this->cus_trans_model->gst_grouping_from_trans_tax( $this->date_from, $this->date_to);
        $customer_trans_old = $this->model->sale_trans_from_trans_tax( $this->date_from, $this->date_to);
        foreach ($customer_trans_old AS $ite){ if( $ite->unit_price > 0 && $ite->quantity > 0 && $ite->tax_id ){

            $tax = $this->tax[$ite->tax_id];
            $tax_type_code = $this->tax_code[$tax->gst_03_type];
            if( $ite->curr_rate ){
                $price = $ite->unit_price*$ite->curr_rate;
            } else {
                $price = $ite->unit_price;
            }

            if( $ite->type=='CCN' ){
                $price = -$price;
            }

            foreach ($this->map_value AS $position=>$map ){
                if( in_array($tax_type_code,$map) ){
                    $this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,$ite->discount_percent,$position);
                }
            }

            foreach ($this->map_gsp AS $position=>$map ){
                if( in_array($tax_type_code,$map) ){
                    $this->calculator_item_gst_amount($price,$ite->qty,$ite->tax_included,$tax->rate,$ite->discount_percent,$position);

                }
            }
            if( !isset( $this->msic[$ite->msic] ) ){
                $this->msic[$ite->msic] = 0;
            }

            if( $ite->tax_included ){
                $tax = $tax->rate/(100+$tax->rate)*$price*$ite->quantity*( 1- $ite->discount_percent);
            } else {
                $tax = $price*($tax->rate/100)*$ite->quantity*( 1- $ite->discount_percent);
            }

            $this->msic[$ite->msic] += $tax;
            $this->msic_total += $tax;
        }}



        /*
         * fix tax ID for bad debt
         */
//         $customer_trans_baddebt = $this->cus_trans_model->gst_grouping_baddebt( $this->date_from, $this->date_to);
        $customer_trans_baddebt = $this->model->sale_trans_baddebt( $this->date_from, $this->date_to);

        foreach ($customer_trans_baddebt AS $ite){
            $tax_id = $_SESSION['SysPrefs']->prefs['baddeb_sale_tax'];

            $tax = $this->tax[$tax_id];
            $tax_type_code = $this->tax_code[$tax->gst_03_type];
            if( $ite->curr_rate ){
                $price = $ite->unit_price*$ite->curr_rate;
            } else {
                $price = $ite->unit_price;
            }

            foreach ($this->map_value AS $position=>$map ){
                if( in_array($tax_type_code,$map) ){
                    $this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,$ite->discount_percent,$position);
                }
            }
            foreach ($this->map_gsp AS $position=>$map ){
                if( in_array($tax_type_code,$map) ){
                    $this->calculator_item_gst_amount($price,$ite->quantity,$ite->tax_included,$tax->rate,$ite->discount_percent,$position);
                }
            }
            if( !isset( $this->msic[$ite->msic] ) ){
                $this->msic[$ite->msic] = 0;
            }

            if( $ite->tax_included ){
                $tax = $tax->rate/(100+$tax->rate)*$price*$ite->quantity*( 1- $ite->discount_percent);
            } else {
                $tax = $price*($tax->rate/100)*$ite->quantity*( 1- $ite->discount_percent);
            }

            $this->msic[$ite->msic] += $tax;
            $this->msic_total += $tax;

        }

    }

    function get_purchase(){
//         $supplier_trans = $this->supp_trans_model->gst_grouping(  $this->date_from, $this->date_to);

        $supplier_trans_new = $this->model->purchase_trans(  $this->date_from, $this->date_to);

        foreach ($supplier_trans_new AS $ite){
            if( !array_key_exists($ite->tax_id,$this->tax) ){
                continue;
            }
            if( $ite->unit_price != 0 && $ite->quantity != 0 && $ite->tax_id ){
                $tax = $this->tax[$ite->tax_id];
                
                $tax_type_code = isset($this->tax_code[$tax->gst_03_type]) ? $this->tax_code[$tax->gst_03_type] : NULL;
                
                if( $ite->curr_rate ){
                    $price = $ite->unit_price*$ite->curr_rate;
                } else {
                    $price = $ite->unit_price;
                }


                foreach ($this->map_value AS $position=>$map ){
                    if( in_array($tax_type_code,$map) ){
                        $this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                    }

                }
                foreach ($this->map_gsp AS $position=>$map ){

                    if( in_array($tax_type_code,$map) ){
                        $this->calculator_item_gst_amount($price,$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                    }

                }



                if(  $ite->fixed_access ){
                    $price = $price * $ite->quantity;
                    if( $ite->tax_included ){
                        $tax = $tax->rate/(100+$tax->rate)*$price;
                        $this->values['16'] += $price-$tax;
                    } else {
                        $this->values['16'] += $price;
                    }

                }

                if( !isset($this->msic[$ite->msic] ) ){
                    $this->msic[$ite->msic] = 0;
                }
                if( is_numeric($tax) ){
                    $this->msic[$ite->msic] += $tax;
                }

            }

        }


//         $supplier_trans_old = $this->supp_trans_model->gst_grouping_from_trans_tax(  $this->date_from, $this->date_to);
        $supplier_trans_old = $this->model->purchase_trans_from_trans_tax(  $this->date_from, $this->date_to);

        foreach ($supplier_trans_old AS $ite){
            // bug($ite); die;
            if( $ite->unit_price > 0 && $ite->quantity != 0 && $ite->tax_id ){
                if( !array_key_exists($ite->tax_id, $this->tax) ) continue;
                $tax = $this->tax[$ite->tax_id];
                $tax_type_code = $this->tax_code[$tax->gst_03_type];
                if( $ite->curr_rate ){
                    $price = $ite->unit_price*$ite->curr_rate;
                } else {
                    $price = $ite->unit_price;
                }


                foreach ($this->map_value AS $position=>$map ){
                    if( in_array($tax_type_code,$map) ){
                        $this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                    }

                }
                foreach ($this->map_gsp AS $position=>$map ){

                    if( in_array($tax_type_code,$map) ){
                        $this->calculator_item_gst_amount($price,$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                    }

                }

                if(  $ite->fixed_access ){
                    $price = $price * $ite->quantity;
                    if( $ite->tax_included ){
                        $tax = $tax->rate/(100+$tax->rate)*$price;
                        $this->values['16'] += $price-$tax;
                    } else {
                        $this->values['16'] += $price;
                    }

                }

                if( !isset($this->msic[$ite->msic] ) ){
                    $this->msic[$ite->msic] = 0;
                }
                $this->msic[$ite->msic] += $tax;
            }


        }

        /*
         * add bad deb
         */
//         $supplier_trans_baddebt = $this->supp_trans_model->gst_grouping_baddebt(  $this->date_from, $this->date_to);
        $supplier_trans_baddebt = $this->model->purchase_trans_baddebt(  $this->date_from, $this->date_to);

        foreach ($supplier_trans_baddebt AS $ite){
            $ite->tax_id = $_SESSION['SysPrefs']->prefs['baddeb_purchase_tax'];
            if( $ite->unit_price != 0 && $ite->quantity != 0 ){
                $tax = $this->tax[$ite->tax_id];
                $tax_type_code = $this->tax_code[$tax->gst_03_type];
                //bug($tax_type_code);
                if( $ite->curr_rate ){
                    $price = $ite->unit_price*$ite->curr_rate;
                } else {
                    $price = $ite->unit_price;
                }


                foreach ($this->map_value AS $position=>$map ){
                    if( in_array($tax_type_code,$map) ){
                        $this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                    }

                }
                foreach ($this->map_gsp AS $position=>$map ){

                    if( in_array($tax_type_code,$map) ){
                        $this->calculator_item_gst_amount($price,$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                    }

                }
                if(  $ite->fixed_access ){
                    $price = $price * $ite->quantity;
                    if( $ite->tax_included ){
                        $tax = $tax->rate/(100+$tax->rate)*$price;
                        $this->values['16'] += $price-$tax;
                    } else {
                        $this->values['16'] += $price;
                    }

                }
            }


        }
    }

    function get_bank_payments() {

        $bank_trans = $this->model->bank_trans( $this->date_from,$this->date_to);
        foreach ($bank_trans AS $ite){
            if( !array_key_exists($ite->tax_id, $this->tax) ){
                continue;
            }

            $tax = $this->tax[$ite->tax_id];
            $tax_type_code = $this->tax_code[$tax->gst_03_type];
            if( $ite->curr_rate ){
                $price = $ite->unit_price*$ite->curr_rate;
            } else {
                $price = $ite->unit_price;
            }

            if( $price != 0 ){
                foreach ($this->map_value AS $position=>$map ){
                    if( in_array($tax_type_code,$map) ){
                        // 						$this->calculator_item_value( abs($price),$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                        $this->calculator_item_value( ($price),$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                    }

                }
                foreach ($this->map_gsp AS $position=>$map ){
                    if( in_array($tax_type_code,$map) ){
                        // 						$this->calculator_item_gst_amount( abs($price),$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                        $this->calculator_item_gst_amount( ($price),$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
                    }

                }
            }
        }
    }

    function calculator_item_value($unit_price=0,$qty=0,$tax_included = false,$tax_rate=0,$discount=0,$postion='-none-'){
        if( !$postion ){
            return;
        }
        if( !isset($this->values[$postion]) ){
            $this->values[$postion] = 0;
        }

        $price = $unit_price * $qty * (1-$discount);

        if( $tax_included ){
            $tax = $tax_rate/(100+$tax_rate)*$price;
            $this->values[$postion] += $price-$tax;
        } else {
            $this->values[$postion] += $price;
        }



    }

    function calculator_item_gst_amount($unit_price=0,$qty=0,$tax_included = false,$tax_rate=0,$discount=0,$postion='-none-'){
        if( !$postion ){
            return;
        }

        if( !isset($this->values[$postion]) ){
            $this->values[$postion]= 0;
        }

        $price = $unit_price * $qty * (1-$discount);

        if( $tax_included ){
            $tax = $tax_rate/(100+$tax_rate)*$price;
        } else {
            $tax = $price*($tax_rate/100);
        }
        // 		if( $postion=='5b'){
        // 		    bug('add 5b+='.$price.' $unit_price='.$unit_price.' $qty='.$qty);
        // 		}
        $this->values[$postion] += $tax;

    }
    private function msic_line($code,$amount,$percent,$line=1){
        $h = 9.43;
        switch ($line){
            case 2:
                $line_percent_h = 54.4;
                $line_code_h = 55.15;
                $line_amount_h = 54.9;
                break;
            case 3:
                $line_percent_h = 54;
                $line_code_h = 55.4;
                $line_amount_h = 54.7;
                break;
            case 4:
                $line_percent_h = 54;
                $line_code_h = 55.8;
                $line_amount_h = 54.62;
                break;
            case 5:
                $line_percent_h = 54;
                $line_code_h = 55.2;
                $line_amount_h = 54.8;
                break;
            case 6:

                $line_amount_h = 54.5;
                $line_percent_h = 54;
                break;
            case 7:
                $line_amount_h = 54.2;
                break;
            default:
                $line_percent_h = 54.65;
                $line_code_h = 55.2;
                $line_amount_h = 54.8;
                break;
        }
        $line --;
        if( $line <=4 ){
            $this->pdf->Text(32, $line_code_h+$line*$h,$code );
        }

        $this->pdf->Text(73.5, $line_amount_h+$line*$h, money_total_format($amount,true) );
        if( $line < 6 ){
            $this->pdf->Text(154, $line_percent_h+ $line*$h , number_format2($percent,2) );
        }
    }

    private function gaf_output(){
        $data = array();
        $data['supplies_xml'] = $this->supplies_xml();
        $data['purchase_xml'] = $this->purchase_xml();
        $data['gl_trans_xml'] = $this->gl_trans_xml();

        $data['company'] = get_company_prefs();
        $data['date_from'] = $this->date_from;
        $data['date_to'] = $this->date_to;
        $data['created'] = date('Y-m-d');
        // bug($data['customer_trans_new']);die;
        $data['purchase_total'] = $this->purchase_total;
        $data['sale_total'] = $this->sale_total;
        $data['gl_trans_total'] = $this->gl_trans_total;
        $xml = get_instance()->view('reporting/gst-form3-xml',$data,true);
        // 	    bug($data['company']); die;

        header('Content-type:text/xml');
        header('Content-disposition:attachment;filename="GSTForm3.xml"');
        // 	    global $Ajax;

        // 	    if (user_rep_popup())
            // 	        $Ajax->popup(site_url($fname));
        echo $xml;
    }

    var $sale_total = array('count'=>0,'amount'=>0,'gst'=>0);
    private function supplies_xml(){
        $xml = NULL;
        $customer_trans_new = $this->model->sale_trans(  $this->date_from, $this->date_to);
        $line = 0;
        foreach ($customer_trans_new AS $ite){if( $ite->unit_price > 0 && $ite->quantity > 0 && $ite->tax_id ){
            if( !array_key_exists($ite->tax_id, $this->tax) ) continue;
            $tax = $this->tax[$ite->tax_id];
            if( $ite->curr_rate ){
                $price = $ite->unit_price*$ite->curr_rate;
            } else {
                $price = $ite->unit_price;
            }
            $item_tax_calcula = tax_calculator($ite->tax_id,$price,$ite->tax_included,$tax);
            $line++;
            $data = array(
                'customer'=>htmlentities($ite->customername),
                'tran_date'=>$ite->tran_date,
                'trans_no'=>$ite->trans_no,
                'line_no'=>$line,
                'price'=>$item_tax_calcula->price,
                'gst_value'=>$item_tax_calcula->value,
                'tax_code'=>$this->tax_code[$tax->gst_03_type],
                'item_name'=>htmlentities($ite->item_name),
                'currence'=>$ite->currence,

            );
            $this->sale_total['amount'] += $data['price'];
            $this->sale_total['gst'] += $data['gst_value'];
            $xml .= get_instance()->view('reporting/gst_form3/supplie-xml',$data,true);

        }}

        $customer_trans_old = $this->model->sale_trans_from_trans_tax(  $this->date_from, $this->date_to);
        foreach ($customer_trans_old AS $ite){if( $ite->unit_price > 0 && $ite->quantity > 0 && $ite->tax_id ){
            if( !array_key_exists($ite->tax_id, $this->tax) ) continue;
            $tax = $this->tax[$ite->tax_id];
            if( $ite->curr_rate ){
                $price = $ite->unit_price*$ite->curr_rate;
            } else {
                $price = $ite->unit_price;
            }
            $item_tax_calcula = tax_calculator($ite->tax_id,$price,$ite->tax_included,$tax);
            $line++;
            $data = array(
                'customer'=>htmlentities($ite->customername),
                'tran_date'=>$ite->tran_date,
                'trans_no'=>$ite->trans_no,
                'line_no'=>$line,
                'price'=>$item_tax_calcula->price,
                'gst_value'=>$item_tax_calcula->value,
                'tax_code'=>$this->tax_code[$tax->gst_03_type],
                'item_name'=>htmlentities($ite->item_name),
                'currence'=>$ite->currence,

            );
            $this->sale_total['amount'] += $data['price'];
            $this->sale_total['gst'] += $data['gst_value'];
            $xml .= get_instance()->view('reporting/gst_form3/supplie-xml',$data,true);

        }}
        $this->sale_total['count'] += $line;
        return $xml;
    }

    var $purchase_total = array('count'=>0,'amount'=>0,'gst'=>0);

    private function purchase_xml(){
        $xml = NULL;
        $supplier_trans_new = $this->model->purchase_trans(  $this->date_from, $this->date_to);
        $line = 0;
        foreach ($supplier_trans_new AS $ite){if( $ite->unit_price > 0 && $ite->quantity > 0 && $ite->tax_id ){
            if( !array_key_exists($ite->tax_id, $this->tax) ) continue;
            $tax = $this->tax[$ite->tax_id];
            if( $ite->curr_rate ){
                $price = $ite->unit_price*$ite->curr_rate;
            } else {
                $price = $ite->unit_price;
            }
            $item_tax_calcula = tax_calculator($ite->tax_id,$price,$ite->tax_included,$tax);
            $line++;
            $data = array(
                'supp_name'=>htmlentities($ite->supp_name),
                'tran_date'=>$ite->tran_date,
                'trans_no'=>$ite->trans_no,
                'line_no'=>$line,
                'price'=>$item_tax_calcula->price,
                'gst_value'=>$item_tax_calcula->value,
                'tax_code'=>$this->tax_code[$tax->gst_03_type],
                'item_name'=>$ite->item_name,
                'currence'=>$ite->currence,

            );
            $this->purchase_total['amount'] += $data['price'];
            $this->purchase_total['gst'] += $data['gst_value'];
            $xml .= get_instance()->view('reporting/gst_form3/purchase-xml',$data,true);

        }}

        $supplier_trans_old = $this->model->purchase_trans_from_trans_tax(  $this->date_from, $this->date_to);
        foreach ($supplier_trans_old AS $ite){if( $ite->unit_price > 0 && $ite->quantity > 0 && $ite->tax_id ){
            if( !array_key_exists($ite->tax_id, $this->tax) ) continue;
            $tax = $this->tax[$ite->tax_id];
            if( $ite->curr_rate ){
                $price = $ite->unit_price*$ite->curr_rate;
            } else {
                $price = $ite->unit_price;
            }
            $item_tax_calcula = tax_calculator($ite->tax_id,$price,$ite->tax_included,$tax);
            $line++;
            $data = array(
                'customer'=>$ite->customername,
                'tran_date'=>$ite->tran_date,
                'trans_no'=>$ite->trans_no,
                'line_no'=>$line,
                'price'=>$item_tax_calcula->price,
                'gst_value'=>$item_tax_calcula->value,
                'tax_code'=>$this->tax_code[$tax->gst_03_type],
                'item_name'=>$ite->item_name

            );
            $this->purchase_total['amount'] += $data['price'];
            $this->purchase_total['gst'] += $data['gst_value'];
            $xml .= get_instance()->view('reporting/gst_form3/supplie-xml',$data,true);

        }}
        $this->purchase_total['count'] = $line;
        return $xml;
    }

    var $gl_trans_total = array('count'=>0,'credit'=>0,'debit'=>0,'balance'=>0);
    private function gl_trans_xml(){
        $xml = NULL;

        $select = 'gl.*,acc.account_name';
        // $select.=' CASE 1 WHEN 1 THEN 'one' WHEN 2 THEN 'two' ELSE 'more' END;';
        $this->db->select($select)->join('chart_master AS acc','acc.account_code = gl.account','left');
        if( $this->date_from ){
            $this->db->where('gl.tran_date >=',$this->date_from);
        }
        if( $this->date_to ){
            $this->db->where('gl.tran_date <=',$this->date_to);
        }
        $this->db->where(array('amount !='=>0));
        // 	    $this->db->limit(10);
        $trans = $this->db->get('gl_trans AS gl')->result_array();
        $line=0;
        $balance = 0;
        foreach ($trans AS $ite){
            $ite['credit'] = $ite['debit']= 0;
            if( $ite['amount'] > 0 ){
                $ite['credit'] = abs( $ite['amount'] );
                $this->gl_trans_total['credit'] += $ite['credit'];
            } else if ($ite['amount'] < 0) {
                $ite['debit'] = abs( $ite['amount'] );
                $this->gl_trans_total['debit'] += $ite['debit'];
            }
            $balance+=$ite['amount'];

            $ite['total'] = $balance;
            switch ($ite['type']){
                case 10: $ite['type_name'] = 'Sales Invoice'; break;
                case 11: $ite['type_name'] = 'Customer Credit'; break;
                case 12: $ite['type_name'] = 'Customer Payment'; break;
                case 13: $ite['type_name'] = 'Customer Delivery'; break;
                case 20: $ite['type_name'] = 'Supplier Invoice'; break;
                case 21: $ite['type_name'] = 'Supplier Credit'; break;
                case 22: $ite['type_name'] = 'Supplier Payment'; break;
                case 25: $ite['type_name'] = 'Supplier Receive'; break;
                case 35: $ite['type_name'] = 'Cost Update'; break;
                default: $ite['type_name'] = 'Journal'; break;

            }

            $xml .= get_instance()->view('reporting/gst_form3/gl-trans-xml',$ite,true);

            $line++;
        }
        $this->gl_trans_total['count'] = $line;
        $this->gl_trans_total['balance'] = $balance;
        return $xml;

    }

    public function tap_output(){
        $format = 'c1|c2|c3|c4|b5|c6|c7|c8|c9|c10|c11|c12|c13|i14|c15|i16|c17|i18|c19|i20|c21|i22|c23|c24';

        $format = array(
            1=>'5a',
            2=>'5b',
            3=>'6a',
            4=>'6b',
            5=>'9',
            6=>'10',
            7=>'11',
            8=>'12',
            9=>'13',
            10=>'14',
            11=>'16',
            12=>'17',
            13=>'18',

            14=>null,
            15=>'19a',
            16=>null,
            17=>'19b',
            18=>null,
            19=>'19c',
            20=>null,
            21=>'19d',
            22=>null,
            23=>'19e',
            24=>'19f'
        );
        // 	    $b = array(5=>9);
        // 	    $i = array(14=>null,16=>null,18=>null,20=>null,22=>null);
        $tap = null;

        if( count($this->msic) > 0 ){
            arsort($this->msic);
            $msic_other = 0;
            foreach ($this->msic AS $k=>$val){
                if( $k < 0 ){
                    $msic_other+=$val;
                    unset($this->msic[$k]);
                }
            }
            $format[24] = $msic_other;

        }
        $msic_keys = array_keys($this->msic);

        foreach ($format AS $k=>$position){
            if( $k==5 ){
                $value = ( isset($this->values[$position]) ) ? 1 : 0;
            } elseif( $k <= 13 ){
                $value = ( isset($this->values[$position]) ) ? $this->values[$position] : 0;
                $value= atmoney_format($value);
            } else {
                $line = ($k-12)/2;
                if( isset($msic_keys[$line-1]) ){
                    $msic_key = $msic_keys[$line-1];
                    if(($k-13)%2){
                        $format[$k] = intval($msic_key);
                    } else {
                        $format[$k] = atmoney_format($this->msic[$msic_key]);
                    }
                } elseif( ($k-13)%2 != 1 ){
                    $format[$k] = atmoney_format(0);
                } else {
                    $format[$k] = 0;
                }
                $value = $format[$k];

            }
            $tap.="$value|";

        }
        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=tap-file.txt");
        print_r(substr($tap,0,-1)); die;

    }
}