<?php

class gst3 extends ci {
	var $date_from, $date_to;
	function __construct(){
		global $db_connections, $ci;

		$this->load_db($db_connections[0]);
        $this->ci = $ci;

		$this->msic = array();
		$this->msic_total = 0;

		$this->tax_model = $this->model('tax',true);
		$this->cus_trans_model = $this->model('customer_trans',true);
		$this->supp_trans_model = $this->model('supplier_trans',true);
		$this->bank_trans_model = $this->model('bank_trans',true);

		$api = $this->load_library('api_membership',true);
		$tax_code = $api->get_data("tax_code",true);

		$this->tax_code = $tax_code['options'];

		$tax_items = $this->tax_model->tax_type_items();

		$this->tax = array();
		foreach ($tax_items AS $tax){
		    $this->tax[$tax->id] = $tax;
		}



	}

	function date_check(){
		if( isset($this->date_from) ){
			$this->date_from = date('Y-m-d',strtotime($this->date_from) );
		}
		if( isset($this->date_to) ){
			$this->date_to = date('Y-m-d',strtotime($this->date_to) );
		}
	}

	function get_debtor_trans(){
// 		$this->date_check();
// 		$query = 'tran.id, tran.unit_price AS price, tran.quantity AS qty, tran.discount_percent AS discount, saletype.tax_included, tax.gst_03_type AS tax_code, tax.rate AS tax_rate, cu.msic, cu.debtor_ref AS customerid';
// 		//$query.=' , tax.name as tax_name';
// 		$this->db->select($query,false);

// // 		$this->db->from('debtor_trans AS deb');
// 		$this->db->from('debtor_trans_details AS tran');
// 		$this->db->join('debtor_trans as deb', 'deb.trans_no = tran.debtor_trans_no AND tran.debtor_trans_type = deb.type', 'left');

// 		$this->db->join('debtors_master as cu', 'cu.debtor_no = deb.debtor_no', 'left');
// 		$this->db->join('sales_orders as o', 'cu.debtor_no = deb.debtor_no', 'left');
// 		$this->db->join('sales_types as saletype', 'saletype.id = deb.tpe', 'left');
// 		$this->db->join('tax_types as tax', 'tax.id = tran.tax_type_id', 'left');




// 		$this->db->where("deb.tran_date between '$this->date_from' and '$this->date_to'");
// 		$this->db->where("tran.debtor_trans_type",ST_SALESINVOICE);

// // 		$this->db->where("deb.type",ST_SALESINVOICE);
// 		$this->db->group_by("tran.id");
// 		$this->db->where( array('tran.unit_price >'=>0),true );

// 		$data = $this->db->get()->result();
// // 		bug( $this->db->last_query() ); die('end get sale item');

// 		$this->sale_trans = $data;

		$customer_trans_new = $this->cus_trans_model->gst_grouping(  $this->date_from, $this->date_to);

		foreach ($customer_trans_new AS $ite){ if( $ite->unit_price > 0 && $ite->quantity > 0 && $ite->tax_id ){

			$tax = $this->tax[$ite->tax_id];
			$tax_code = $this->tax_code[$tax->gst_03_type];
			if( $ite->curr_rate ){
				$price = $ite->unit_price*$ite->curr_rate;
			} else {
				$price = $ite->unit_price;
			}

			foreach ($this->map_value AS $position=>$map ){
				if( in_array($tax_code,$map) ){
					$this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,$ite->discount_percent,$position);
				}
			}
			foreach ($this->map_gsp AS $position=>$map ){
				if( in_array($tax_code,$map) ){
					$this->calculator_item_gst_amount($price,$ite->quantity,$ite->tax_included,$tax->rate,$ite->discount_percent,$position);
				}
			}
			if( !array_key_exists($ite->msic, $this->msic) ){
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
// 		bug($this->msic); die;

		$customer_trans_old = $this->cus_trans_model->gst_grouping_from_trans_tax(  $this->date_from, $this->date_to);
		foreach ($customer_trans_old AS $ite){ if( $ite->unit_price > 0 && $ite->quantity > 0 && $ite->tax_id ){
		   // bug($ite);
			$tax = $this->tax[$ite->tax_id];
			$tax_code = $this->tax_code[$tax->gst_03_type];
			if( $ite->curr_rate ){
				$price = $ite->unit_price*$ite->curr_rate;
			} else {
				$price = $ite->unit_price;
			}

			foreach ($this->map_value AS $position=>$map ){
				if( in_array($tax_code,$map) ){
					$this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,$ite->discount_percent,$position);
				}
			}
			foreach ($this->map_gsp AS $position=>$map ){
				if( in_array($tax_code,$map) ){
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
		}}

	}

	function get_purchase_orders(){
// 		$this->date_check();
// 		$this->db->select('tran.id, tran.quantity as qty, tran.unit_price*o.rate AS price , 0 AS discount , o.tax_included , tax.gst_03_type AS tax_code, tax.rate AS tax_rate, o.fixed_access',false);
// 		$this->db->from('supp_invoice_items AS tran');
// 		$this->db->join('supp_trans as o', 'o.trans_no = tran.supp_trans_no', 'left');
// 		$this->db->join('tax_types as tax', 'tax.id = tran.tax_type_id', 'left');

// 		$this->db->where("o.tran_date between '$this->date_from' and '$this->date_to'");
// 		$this->db->where("o.type",ST_SUPPINVOICE);
// 		$this->db->where("tran.unit_price >",0);

// 		$data = $this->db->get()->result();
// // 		bug( $this->db->last_query() ); die('end get sale item');

// 		$this->purchase_trans = $data;

		$supplier_trans_new = $this->supp_trans_model->gst_grouping(  $this->date_from, $this->date_to);
		foreach ($supplier_trans_new AS $ite){
			if( $ite->unit_price > 0 && $ite->quantity > 0 && $ite->tax_id ){
				$tax = $this->tax[$ite->tax_id];
				$tax_code = $this->tax_code[$tax->gst_03_type];
				if( $ite->curr_rate ){
					$price = $ite->unit_price*$ite->curr_rate;
				} else {
					$price = $ite->unit_price;
				}


				foreach ($this->map_value AS $position=>$map ){
					if( in_array($tax_code,$map) ){
						$this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
					}

				}
				foreach ($this->map_gsp AS $position=>$map ){

					if( in_array($tax_code,$map) ){
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

		$supplier_trans_old = $this->supp_trans_model->gst_grouping_from_trans_tax(  $this->date_from, $this->date_to);
		foreach ($supplier_trans_old AS $ite){
			// 			bug($ite);
			if( $ite->unit_price > 0 && $ite->quantity > 0 && $ite->tax_id ){
				if( !array_key_exists($ite->tax_id, $this->tax) ) continue;
				$tax = $this->tax[$ite->tax_id];
				$tax_code = $this->tax_code[$tax->gst_03_type];
				if( $ite->curr_rate ){
					$price = $ite->unit_price*$ite->curr_rate;
				} else {
					$price = $ite->unit_price;
				}


				foreach ($this->map_value AS $position=>$map ){
					if( in_array($tax_code,$map) ){
						$this->calculator_item_value($price,$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
					}

				}
				foreach ($this->map_gsp AS $position=>$map ){

					if( in_array($tax_code,$map) ){
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

// 		bug('item purchase='. (count($supplier_trans_old)+ count($supplier_trans_new)) );
	}

	var $bank_trans = array();
	function get_bank_payments() {
// 		$this->date_check();
// 		$bank_trans_model = $this->model('bank_trans',true);
// 		$bank_trans = $bank_trans_model->trans_date(null,$this->date_from,$this->date_to);
// 		if( !empty($bank_trans) ){foreach ($bank_trans AS $tran){

// 			$details = $bank_trans_model->trans_detail(null,array('trans_no'=>$tran->trans_no));
// 			if( !empty($details) ){ foreach ($details AS $detai){
// // 				$this->bank_trans = array_merge_recursive($this->bank_trans, $detail);
// 				$detai->tax_included = $tran->tax_inclusive;
// 				$this->bank_trans[] = $detai;
// 			}}
// 		}}
		$bank_trans = $this->bank_trans_model->gst_grouping( $this->date_from,$this->date_to);
		foreach ($bank_trans AS $ite){
			if( !array_key_exists($ite->tax_id, $this->tax) ) {
// 				bug($ite);die;
				continue;
			}

			$tax = $this->tax[$ite->tax_id];
			$tax_code = $this->tax_code[$tax->gst_03_type];
			if( $ite->curr_rate ){
				$price = $ite->unit_price*$ite->curr_rate;
			} else {
				$price = $ite->unit_price;
			}

			if( $price != 0 && $tax->gst_03_type ){
				foreach ($this->map_value AS $position=>$map ){
					if( in_array($tax_code,$map) ){
						$this->calculator_item_value( abs($price),$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
					}

				}
				foreach ($this->map_gsp AS $position=>$map ){
					if( in_array($tax_code,$map) ){
						$this->calculator_item_gst_amount( abs($price),$ite->quantity,$ite->tax_included,$tax->rate,0,$position);
					}

				}
			}
		}
// 		bug('item bank='. count($bank_trans));
	}

	function map_gst(){
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


		$this->msic = array();
		$this->msic_total = 0;

	}

	var $values = array();
	function calculator(){
// 		$map_value = array(
// 			'5a'=>array('SR','DS'),
// 			'6a'=>array('TX','IM','TX-E43','TX-RE'),
// 			'10'=>array('ZRL'),
// 			'11'=>array('ZRE'),
// 			'12'=>array('ES','ES43'),
// 			'13'=>array('RS'),
// 			'14'=>array('IS'),

// 		);
// 		$map_gsp = array(
// 			'5b'=>array('SR','DS','AJS'),
// 			'6b'=>array('TX','IM','TX-E43','TX-RE','AJP'),
// 			'15'=>array('IS'),
// 		);
// 		$this->values['7'] = 0;
// 		$this->values['8'] = 0;
// 		$this->values['16'] = 0;

// 		foreach ($this->sale_trans AS $ite){
// 			if( $ite->price > 0 && $ite->qty > 0 && $ite->tax_code ){
// 				foreach ($map_value AS $position=>$map ){
// 					$item_tax_code = $this->tax_code[$ite->tax_code];
// 					if( in_array($item_tax_code,$map) ){
// 						$this->calculator_item_value($ite->price,$ite->qty,$ite->tax_included,$ite->tax_rate,$ite->discount,$position);
// 					}
// 				}
// 				foreach ($map_gsp AS $position=>$map ){
// 					$item_tax_code = $this->tax_code[$ite->tax_code];
// 					if( in_array($item_tax_code,$map) ){
// 						$this->calculator_item_gst_amount($ite->price,$ite->qty,$ite->tax_included,$ite->tax_rate,$ite->discount,$position);
// 					}
// 				}
// 			}
// 			if( !isset( $this->msic[$ite->msic] ) ){
// 				$this->msic[$ite->msic] = 0;
// 			}

// 			if( $ite->tax_included ){
// 				$tax = $ite->tax_rate/(100+$ite->tax_rate)*$ite->price*$ite->qty*( 1- $ite->discount);
// 			} else {
// 				$tax = $ite->price*($ite->tax_rate/100)*$ite->qty*( 1- $ite->discount);
// 			}

// 			$this->msic[$ite->msic] += $tax;
// 			$this->msic_total += $tax;
// 		}


// 		foreach ($this->purchase_trans AS $ite){
// 			if( $ite->price > 0 && $ite->qty > 0 && $ite->tax_code ){
// 				foreach ($map_value AS $position=>$map ){
// 					$item_tax_code = $this->tax_code[$ite->tax_code];
// 					if( in_array($item_tax_code,$map) ){
// 						$this->calculator_item_value($ite->price,$ite->qty,$ite->tax_included,$ite->tax_rate,0,$position);
// 					}

// 				}
// 				foreach ($map_gsp AS $position=>$map ){
// 					$item_tax_code = $this->tax_code[$ite->tax_code];
// 					if( in_array($item_tax_code,$map) ){
// 						$this->calculator_item_gst_amount($ite->price,$ite->qty,$ite->tax_included,$ite->tax_rate,0,$position);
// 					}

// 				}
// 			}

// 			if(  $ite->fixed_access ){
// 				$price = $ite->price * $ite->qty;
// 				if( $ite->tax_included ){
// 					$tax = $ite->tax_rate/(100+$ite->tax_rate)*$ite->price;
// 					$this->values['16'] += $price-$tax;
// 				} else {
// 					$this->values['16'] += $price;
// 				}

// 			}
// 		}

// // 		bug($this->bank_trans);die;
// 		foreach ($this->bank_trans AS $ite){

// 			if( $ite->amount != 0 && $ite->gst_03_type ){
// 				foreach ($map_value AS $position=>$map ){
// 					$item_tax_code = $this->tax_code[$ite->gst_03_type];
// 					if( in_array($item_tax_code,$map) ){
// 						$this->calculator_item_value( abs($ite->amount)*$ite->currence_rate,1,$ite->tax_included,$ite->rate,0,$position);
// 					}

// 				}
// 				foreach ($map_gsp AS $position=>$map ){
// 					$item_tax_code = $this->tax_code[$ite->gst_03_type];
// 					if( in_array($item_tax_code,$map) ){
// 						$this->calculator_item_gst_amount( abs($ite->amount)*$ite->currence_rate,1,$ite->tax_included,$ite->rate,0,$position);
// 					}

// 				}
// 			}
// 		}




		if( !isset($this->values['5b']) ){
			$this->values['5b'] = 0;
		}
		if( !isset($this->values['6b']) ){
			$this->values['6b'] = 0;
		}

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

// 		bug(' purchase total = '.count($this->purchase_trans).' sale total='. count($this->sale_trans) ); die();

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
// 			$this->values[$postion] +=
		} else {
			$tax = $price*($tax_rate/100);
		}
		$this->values[$postion] += $tax;

	}

	var $sale_total = array('count'=>0,'amount'=>0,'gst'=>0);
	function supplies_xml(){
	    $xml = NULL;
	    $customer_trans_new = $this->cus_trans_model->gst_grouping(  $this->date_from, $this->date_to);
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
	        $xml .= $this->ci->view('reporting/gst_form3/supplie-xml',$data,true);

	    }}

	    $customer_trans_old = $this->cus_trans_model->gst_grouping_from_trans_tax(  $this->date_from, $this->date_to);
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
	        $xml .= $this->ci->view('reporting/gst_form3/supplie-xml',$data,true);

	    }}
	    $this->sale_total['count'] += $line;
	    return $xml;
	}

	var $purchase_total = array('count'=>0,'amount'=>0,'gst'=>0);

	function purchase_xml(){
	    $xml = NULL;
	    $supplier_trans_new = $this->supp_trans_model->gst_grouping(  $this->date_from, $this->date_to);
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
	        $xml .= $this->ci->view('reporting/gst_form3/purchase-xml',$data,true);

	    }}

	    $supplier_trans_old = $this->supp_trans_model->gst_grouping_from_trans_tax(  $this->date_from, $this->date_to);
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
	        $xml .= $this->ci->view('reporting/gst_form3/supplie-xml',$data,true);

	    }}
	    $this->purchase_total['count'] = $line;
	    return $xml;
	}

	var $gl_trans_total = array('count'=>0,'credit'=>0,'debit'=>0,'balance'=>0);
	function gl_trans_xml(){
	    $xml = NULL;

	    $select = 'gl.*,acc.account_name';
       // $select.=' CASE 1 WHEN 1 THEN 'one' WHEN 2 THEN 'two' ELSE 'more' END;';
	    $this->ci->db->select($select)->join('chart_master AS acc','acc.account_code = gl.account','left');
	    if( $this->date_from ){
	        $this->ci->db->where('gl.tran_date >=',$this->date_from);
	    }
	    if( $this->date_to ){
	        $this->ci->db->where('gl.tran_date <=',$this->date_to);
	    }
	    $this->ci->db->where(array('amount !='=>0));
// 	    $this->ci->db->limit(10);
	    $trans = $this->ci->db->get('gl_trans AS gl')->result_array();
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

	        $xml .= $this->ci->view('reporting/gst_form3/gl-trans-xml',$ite,true);

	        $line++;
	    }
	    $this->gl_trans_total['count'] = $line;
	    $this->gl_trans_total['balance'] = $balance;
	    return $xml;

	}
}