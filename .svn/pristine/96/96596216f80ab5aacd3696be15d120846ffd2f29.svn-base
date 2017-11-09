<?php
include ROOT.'/includes/controller.php';


class opening_sale {
	var $fields = array();
	function __construct(){
		global $ci, $Ajax;
		$this->ci = $ci;
		$this->Ajax = $Ajax;
		$this->model = new model();
		$this->view = new view();

		$this->fields = array(
			'customer'=>array('title'=>_('Customer'),'value'=>'','readonly'=>true),
			'amount'=>array('input'=>'number','title'=>_('Amount'))
		);
		$this->check_db();

	}

	private function check_db(){

	}

	function write_opening($sale_id=0,$type='sale'){

		$config_model = $this->ci->model('config',true);
		$bank_model = $this->ci->model('bank',true);
		$gl_trans_model = $this->ci->model('gl_trans',true);

		$gl_trans = $this->ci->gl_trans;
		if( !is_object($gl_trans) )
			return;

		if( !$sale_id ){
			return;
		}

		$opening_sale = $this->ci->db->where('id',$sale_id)->get('opening_sale')->row();

		$gl_trans->set_value('type_no',$opening_sale->ref);
		$gl_trans->set_value('tran_date',$opening_sale->tran_date);
		$gl_trans->set_value('branch',$opening_sale->branch);


		if( !$gl_trans->value['tran_date'] ){
			$date = new DateTime(date('Y').'-1-1');
			$date->modify('-1 day');
			$gl_trans->set_value('tran_date',$date->format('d-m-Y'));
		}

		$gl_trans->set_value('type_no',$opening_sale->trans_no);


		$debit = $opening_sale->debit*$opening_sale->curr_rate;
		$credit = $opening_sale->credit*$opening_sale->curr_rate;



		switch ($type){
			case 'sale':

				$gl_trans->customer($opening_sale->customer,$opening_sale->branch);
				$this->packet_trans($opening_sale,ST_SALESINVOICE);

				$gl_trans->set_value('person_type_id',PT_CUSTOMER);
				$customer_model = $this->ci->model('cutomer',true);
				$gl_trans->set_value('person_id',$customer_model->customer_detail($opening_sale->customer,'name'));

				if( $debit > 0 ){
					//$gl_trans->write_customer_trans(ST_SALESINVOICE,$debit,$opening_sale);

					$gl_trans->set_value('type',ST_SALESINVOICE);
					$gl_trans->add_trans($gl_trans->sales_account,			-$debit);
					$gl_trans->add_trans($gl_trans->receivables_account,	$debit);
					$gl_trans->add_trans($gl_trans->sales_account,			$debit);
					$gl_trans->add_trans($gl_trans->receivables_account,	-$debit);
				}

				/* credit */
				if ( $credit > 0 ){
					$gl_trans->set_value('type',ST_SUPPAYMENT);

					$currency = $config_model->get_sys_pref_val('curr_default');
					$bank_account_default = $bank_model->bank_accounts_default($currency);

					$gl_trans->add_trans($gl_trans->receivables_account,	-$credit);
					$gl_trans->add_trans($bank_account_default,				$credit);
					$gl_trans->add_trans($gl_trans->receivables_account,	$credit);
					$gl_trans->add_trans($bank_account_default,				-$credit);
				}

				break;
			case 'purchase':
				$supp_model = $this->ci->model('supplier',true);
				$this->packet_trans($opening_sale,ST_SUPPINVOICE);

				$gl_trans->set_value('person_type_id',PT_SUPPLIER);
				$gl_trans->set_value('person_id',$supp_model->supplier_detail($opening_sale->customer,'supp_name'));


				$Payable_Account = $config_model->get_sys_pref_val('creditors_act');
				$GRN_Clearing_Account = $config_model->get_sys_pref_val('grn_clearing_act');
				$Receivable_Account = $config_model->get_sys_pref_val('debtors_act');

				if( $debit > 0 ){
					$gl_trans->set_value('type',ST_SUPPINVOICE);
					$gl_trans->add_trans($Payable_Account,		-$debit);
					$gl_trans->add_trans($GRN_Clearing_Account,	$debit);
					$gl_trans->add_trans($Payable_Account,		$debit);
					$gl_trans->add_trans($GRN_Clearing_Account,	-$debit);

				}

				if( $credit > 0 ){
					$currency = $config_model->get_sys_pref_val('curr_default');
					$bank_account_default = $bank_model->bank_accounts_default($currency);

					$gl_trans->set_value('type',ST_SUPPAYMENT);

					$gl_trans->add_trans($bank_account_default,	-$credit);
					$gl_trans->add_trans($Payable_Account,		$credit);
					$gl_trans->add_trans($bank_account_default,	$credit);
					$gl_trans->add_trans($Payable_Account,		-$credit);
				}
				break;
		}


		$gl_trans->insert_trans($type.'-'.$sale_id);

	}

	private function packet_trans($opening_sale,$type=0){
		if ( $opening_sale->credit ){
			$amount = $opening_sale->credit;
		} else if ($opening_sale->debit){
			$amount = $opening_sale->debit;
		}

		$customer_trans = array(
			'trans_no'=>$opening_sale->trans_no,

// 			'debtor_no'=>$opening_sale->customer,
// 			'branch_code'=>$opening_sale->branch,
			'tran_date'=>$opening_sale->tran_date,
			'due_date'=>$opening_sale->tran_date,
			'reference'=>$opening_sale->ref,
			'ov_amount'=>$amount,
			'tpe'=>'1',
			'rate'=>$opening_sale->curr_rate,

		);

		if( $type == ST_SALESINVOICE ){
			$customer_trans['debtor_no'] =$opening_sale->customer;
			$customer_trans['branch_code'] =$opening_sale->branch;
			$customer_trans['type'] =ST_SALESINVOICE;

			$db_table = 'debtor_trans';

		} else if ($type == ST_SUPPINVOICE){
			$customer_trans['supplier_id'] =$opening_sale->customer;
			$customer_trans['type'] =ST_SUPPINVOICE;
			$db_table = 'supp_trans';
		}

		$where_existed = array('trans_no'=>$customer_trans['trans_no'],'type'=>$customer_trans['type']);

        $existed = $this->ci->db->where($where_existed)->get($db_table)->row();



        if( $existed && !empty($existed) ){
            $this->ci->db->where($where_existed)->update($db_table,$customer_trans);
        } else {
            $this->ci->db->insert($db_table,$customer_trans);
        }

//         bug($this->ci->db->last_query()); die;
		//die('end add supplier trans');

	}


	function listview($type='sale'){
		global $Ajax;

		if( $this->ci->input->post() ){
			return self::write_opening();
		}

		$th = array();
		$input = '';
		start_table(TABLESTYLE,' id="item_listview" ');
		echo '<thead><tr>';
		echo '<td style="width:10%" >Transaction#</td>';
		echo '<td style="width:10%" >Date</td>';
		echo '<td style="width:10%" >Type</td>';
		if( $type=='purchase' ){
			echo '<td style="width:30%" >Supplier</td>';
		} else {
			echo '<td style="width:30%" >Customer</td>';
		}

		echo '<td style="width:100px" >Debit</td>';

		echo '<td style="width:100px" >Credit</td>';
		echo '<td style="width:100px" >Debit (Base)</td>';
		echo '<td style="width:100px" >Credit (Base)</td>';

		echo '<td style="width:190px" ></td>';
		echo '</tr></thead>';


		$limit = 10;
		$page = $this->ci->input->get('p');
		if( !$page ){
            $page = 1;
		}
// 		bug($page);

		if( $type=='sale' ){
			$where_type = array(ST_SALESINVOICE,ST_CUSTPAYMENT);
			$sales = $this->ci->db->select('sale.*, deb.name')->where_in('type',$where_type)->join('debtors_master AS deb', 'deb.debtor_no=sale.customer', 'left')->limit($limit*($page),($page-1)*$limit)->get('opening_sale AS sale')->result();
		} else if ( $type=='purchase') {
			$where_type = array(ST_SUPPINVOICE,ST_SUPPAYMENT);
			$sales = $this->ci->db->select('sale.*, sup.supp_name AS name')->where_in('type',$where_type)->join('suppliers AS sup', 'sup.supplier_id=sale.customer', 'left')->limit($limit*($page),($page-1)*$limit)->get('opening_sale AS sale')->result();
		}

		$total = $this->ci->db->select('COUNT(s.id) AS total, SUM(s.debit*curr_rate) AS debit, SUM(s.credit*curr_rate) AS credit')->where_in('type',$where_type)->get('opening_sale AS s')->row();
// 		bug( $this->ci->db->last_query() );die;



		if( !empty($sales) && count($sales) > 0 ){
		    foreach ($sales AS $sale){
    			$sale_items = $this->ci->db->where('sale_id',$sale->id)->get('opening_sale_item')->result();

    			$amount = 0;
    			$credit = 0;
    			$debit =0;
    			$item_row_html ='<td>'.$sale->trans_no.'</td>';
    			$item_row_html.='<td>'.$this->ci->form->date_format(array('time'=>$sale->tran_date)).'</td>';
    			switch ($sale->type){
    				case ST_SALESINVOICE:
    					$type_name = 'Sale Invoice'; break;
    				case ST_CUSTPAYMENT:
    					$type_name = 'Customer Deposit'; break;
    				case ST_SUPPINVOICE:
    					$type_name = 'Supplier Invoice'; break;
    				case ST_SUPPAYMENT:
    					$type_name = 'Supplier Payment'; break;
    				default:$type_name = 'unknow'; break;
    			}
    			$item_row_html.='<td>'.$type_name.'</td>';
    			$item_row_html.='<td>'.$sale->name.'</td>';
    			$item_row_html.='<td>'.$sale->debit.'</td>';

    			$item_row_html.='<td>'.$sale->credit.'<input type="hidden" class="" value="'.$sale->credit.'" ></td>';

    			$debit_base =round($sale->debit*$sale->curr_rate,2);
    			$credit_base =round($sale->credit*$sale->curr_rate,2);
    			$item_row_html.='<td>'.$debit_base.'<input type="hidden" class="openning_debit" value="'.$debit_base.'" ></td>';
    			$item_row_html.='<td>'.$credit_base.'<input type="hidden" class="openning_credit" value="'.$credit_base.'" ></td>';


    			$remove = null;
    			if( $sale->status == 0 ){
    				$remove.= '<a class="ajaxsubmit" href="/admin/opening_balance/'.basename($_SERVER["SCRIPT_FILENAME"]).'?add=view-item&id='.$sale->id.'" > View </a>';
    				$remove.= '<a class="ajaxsubmit" href="/admin/opening_balance/'.basename($_SERVER["SCRIPT_FILENAME"]).'?add=remove&id='.$sale->id.'" style="margin-left:5px;" > Remove </a>';
    			}

    			echo '<tr>'.$item_row_html.'<td >'.$remove.'</td></tr>';
		  }

		  if( $total->total > $limit ){
		      echo '<tr><td colspan="7" ></td>';
		      if( $page > 1 ){
		          echo '<td><a class="ajaxsubmit" href="/admin/opening_balance/'.basename($_SERVER["SCRIPT_FILENAME"]).'?p='.($page-1).'" > Prev '.$limit.'</a></td>';
		      } else {
		          echo '<td></td>';
		      }
		      if( $page*$limit < $total->total ){
		          echo '<td><a class="ajaxsubmit" href="/admin/opening_balance/'.basename($_SERVER["SCRIPT_FILENAME"]).'?p='.($page+1).'" > Next '.$limit.'</a></td>';
		      } else {
		          echo '<td></td>';
		      }
		      echo '</tr>';
		  }


		}



		end_table(1);
		$add_more_button = anchor(current_url(null,'add=add-item'),'<img height="12" src="../../themes/template/images/ok.gif"> Add Invoice',array('class'=>"ajaxsubmit",'title'=>"Add new item to document"));

		if( $type=='sale' ){
			//$add_more_button .= anchor(current_url(null,'add=add-deposit'),'<img height="12" src="../../themes/template/images/ok.gif"> Add Deposit',array('class'=>"ajaxsubmit",'title'=>"Add new item to document",'style'=>'margin-left: 10px;'));
		} else if ( $type=='purchase') {
			//$add_more_button .= anchor(current_url(null,'add=add-payment'),'<img height="12" src="../../themes/template/images/ok.gif"> Add Payment',array('class'=>"ajaxsubmit",'title'=>"Add new item to document",'style'=>'margin-left: 10px;'));
		}
		echo '<p>'.$add_more_button.'</p>';
		echo '<script type="text/javascript">$("input.total_credit").val("'.number_format2($total->credit,2).'");$("input.total_debit").val("'.number_format2($total->debit,2).'");$("input.total_trans").val("'.$total->total.'");</script>';
	}

	function additem($type='sale'){
	    global $Ajax;
		$date = new DateTime(date('Y').'-1-1');
		$date->modify('-1 day');

		$data = array(
			'id'=>array('value'=>0),
			'customer'=>array('value'=>null),
			'branch'=>array('value'=>null),

			'currency'=>array('value'=>null),
			'curr_rate'=>array('value'=>1),
			'debit'=>array('value'=>null),
			'credit'=>array('value'=>null),

			//'trans_no'=>array('value'=>null),
			'tran_date'=>array('value'=>$date->format('d-m-Y')),
			'ref'=>array('value'=>null),
			//'items'=>null,

			'form_type'=>$this->ci->input->get('add')
		);

		if( !empty($this->ci->input->post()) ){
			
			$customer_trans_model = $this->ci->model('customer_trans',true);
			$supplier_trans_model = $this->ci->model('supplier_trans',true);

			$new_data = array();
			foreach ($data AS $field=>$val){
				if( !in_array($field, array('form_type')) ) $new_data[$field] = $this->ci->input->post($field);
			}
    			if( strtotime($new_data['tran_date']) >= strtotime(begin_fiscalyear()) ){
    		        trigger_error('Date input can\'t in current fiscal year!', E_USER_ERROR);
    		    } else {
    		        if( $type=='sale' ){
    		            unset($new_data['supplier']);
    		            $new_data['type'] = ST_SALESINVOICE;
    		            $new_data['trans_no'] = $customer_trans_model->trans_no_new($new_data['type']);
    		        } else if ($type=='purchase'){
    		            $new_data['customer'] = $this->ci->input->post('supplier');
    		            unset($new_data['supplier']);
    		            $new_data['type'] = ST_SUPPINVOICE;
    		            $new_data['trans_no'] = $supplier_trans_model->trans_no_new($new_data['type']);
    		        }

    		        if( $new_data['id'] ) {

    		            $opening_sale = $this->ci->db->where('id',$new_data['id'])->get('opening_sale')->row();
    		            $new_data['trans_no'] = $opening_sale->trans_no;
    		            //                 bug($new_data);die;
    		        }

    		        if( !$new_data['tran_date'] ){
    		            $new_data['tran_date'] = $date->format('Y-m-d');
    		        }

    		        $new_data['tran_date'] = date('Y-m-d',strtotime($new_data['tran_date']) );

    		        if( !$new_data['ref'] ){
    		            display_notification_centered(sprintf( _("Please input Reference#"),'0'));
    		        } elseif (!$new_data['debit'] && !$new_data['credit'] ){
    		            display_notification_centered(sprintf( _("Please input Debit or Credit"),'0'));
    		        } else {


    		            if( isset($new_data['id']) && $new_data['id'] > 0 ){
    		                $this->ci->db->where('id',$new_data['id'])->update('opening_sale',$new_data);
    		                $item_id = $new_data['id'];
    		            } else {

    		                $this->ci->db->insert('opening_sale',$new_data);
    		                $item_id = $this->ci->db->insert_id();
    		            }
    		            // 				bug( $this->ci->db->last_query() );
    		            // 				bug($new_data); die;

    		            if( $type=='sale' ){
    		                $this->write_opening($item_id,$type);
    		            } else if ($type=='purchase'){
    		                $this->write_opening($item_id,$type);
    		            }
    		            $Ajax->redirect(basename($_SERVER["SCRIPT_FILENAME"])); die;
// die('iam go here');
//     		            redirect('admin/opening_balance/'.basename($_SERVER["SCRIPT_FILENAME"]));
    		           // return;
    		        }
    		    }


		}

		if( $this->ci->input->get('id') ){

			$opening_sale = $this->ci->db->where('id',$this->ci->input->get('id'))->get('opening_sale')->row();
			foreach ($data AS $field=>$val){
				if( isset($opening_sale->$field) && isset($data[$field]) ){
					$data[$field]['value'] = $opening_sale->$field;
				}
			}
		}


		$data['type'] = $type;
		$view = $this->ci->view('opening/sale_item',$data,true);
		echo $view;
	}

	function add_purchase(){
		$date = new DateTime(date('Y').'-1-1');
		$date->modify('-1 day');

		if( !empty($this->ci->input->post()) ){
			return $this->post_item('purchase');
		}

		$data = array(
				'supplier'=>array('value'=>null),
// 				'branch'=>array('value'=>null),
				'trans_no'=>array('value'=>null),

				'date'=>array('value'=>$date->format('d-m-Y')),
				'ref'=>array('value'=>null),
				'items'=>null,
				'id'=>0,
				'form_type'=>$this->ci->input->get('add')
		);

		if( $this->ci->input->get('id') ){
			$sale = $this->ci->db->where('id',$this->ci->input->get('id'))->where('type',ST_SUPPRECEIVE)->get('opening_sale')->row();
			if( !empty($sale) ){
				$data['supplier']['value'] = $sale->customer;
// 				$data['branch']['value'] = $sale->branch;
				$data['payment']['value'] = $sale->payment;
				$data['ref']['value'] = $sale->ref;


				$data['trans_no']['value'] = $sale->trans_no;

				$data['items'] = $this->ci->db->where('sale_id',$sale->id)->get('opening_sale_item')->result();
				$data['id'] = $sale->id;
			}



		}
		// 		bug($data);
		$view = $this->ci->view('opening/sale_item',$data,true);
		echo $view;
	}

	private function post_item($type='sale'){


		$sale = array(
			'customer'=>$this->ci->input->post('customer'),
			'supplier'=>$this->ci->input->post('supplier'),
			'ref'=>$this->ci->input->post('ref'),
			'branch'=>$this->ci->input->post('branch'),
			'trans_no'=>intval($this->ci->input->post('trans_no')),
// 			'amount'=>0
		);


		$items =  $this->ci->input->post('item_id');

// 		die('post item');
		$currencys =  $this->ci->input->post('curr');
		$debits =  $this->ci->input->post('debit');
		$credits =  $this->ci->input->post('credit');

		$item_id = $this->ci->input->post('item_id');

/*
		if ( !empty($items) ){


			if( $this->ci->input->post('id') ){
				$sale_next_id = $this->ci->input->post('id');
			} else {
				$sale_next = $this->ci->db->select_max('id')->get('opening_sale')->row();
				if( $sale_next ){
					$sale_next_id = $sale_next->id + 1;
				}
			}
			$item_ids = array();




			foreach ($items AS $index=>$item){
// 				if($item){
					$sale_item = array(
						'sale_id'=>$sale_next_id,
						'credit'=>$credits[$index],
						'debit'=>$debits[$index],
						'currency'=>$currencys[$index]
					);

					$sale['id'] = $sale_next_id;

					if( $item_id[$index] ){
						$this->ci->db->where('id',$item_id[$index])->update('opening_sale_item',$sale_item);
						$item_ids[] = $item_id[$index];
					} else {
						$this->ci->db->insert('opening_sale_item',$sale_item);
						$item_ids[] = $this->ci->db->insert_id();
					}
					bug($this->ci->db->last_query() );
// 				}
			}
		}

*/
		if( $type=='sale' ){
			unset($sale['supplier']);
			$sale['type'] = ST_SALESINVOICE;
		} else if ($type=='purchase'){
			$sale['customer'] = $sale['supplier'];
			unset($sale['supplier']);
			$sale['type'] = ST_SUPPRECEIVE;
		}


		if( $this->ci->input->post('id') ){
			$this->ci->db->where('id',$this->ci->input->post('id'))->update('opening_sale',$sale);
			$this->ci->db->where('sale_id',$this->ci->input->post('id'))->where_not_in('id', $item_ids)->delete('opening_sale_item');
			$item_id = $this->ci->input->post('id');
		} else {

			$this->ci->db->insert('opening_sale',$sale);

			$item_id = $sale['id'];

		}


		if( $type=='sale' ){
			$this->write_opening($item_id,$type);
		} else if ($type=='purchase'){
			$this->write_opening($item_id,$type);
		}

		redirect('admin/opening_balance/'.basename($_SERVER["SCRIPT_FILENAME"]));
		return;
	}



	function add_deposit($type='sale'){
		$date = new DateTime(date('Y').'-1-1');
		$date->modify('-1 day');

		$data = array(

			'trans_no'=>array('value'=>null),
			'tran_date'=>array('value'=>$date->format('d-m-Y') ),
			'ref'=>array('value'=>null),
			'payment'=>array('value'=>0),
			'id'=>0
		);

		if( $type=='sale' ){
			$data['customer'] = array('value'=>null);
			$data['branch'] = array('value'=>null);
		} elseif( $type=='purchase' ){
			$data['supplier'] = array('value'=>null);
		}

		if( !empty($this->ci->input->post()) ){
			$trans = array();
			foreach ($data AS $key=>$v){
				$trans[$key] = $v['value'];
				if( $this->ci->input->post($key) ){
					$trans[$key] = $this->ci->input->post($key);
				}

			}
			return $this->post_deposit($trans,$type);
		}
		$view = $this->ci->view('opening/sale_payment',$data,true);
		echo $view;
	}

	private function post_deposit($data=null,$type='sale'){
		$gl_trans = $this->ci->gl_trans;
		if( !is_object($gl_trans) ) return;
		$gl_trans_model = $this->ci->model('gl_trans',true);

// 		$gl_trans->customer($data['customer'],$data['branch']);

		$gl_trans = array(
				'type'=>null,
				'type_no'=>$data['trans_no'],
				'account'=>'',
				'trans_no'=>$data['trans_no'],
// 				'customer'=>$data['customer'],
// 				'branch'=>$data['branch'],
				'tran_date'=>$data['tran_date'],
				'ref'=>$data['ref'],
				'account'=>0,
				'person_type_id'=>null,

				'amount'=> $data['payment']
		);

		$config_model = $this->ci->model('config',true);
		if( $type=='purchase' ){
			$gl_trans['type'] = ST_SUPPAYMENT;
			$gl_trans['customer'] = $data['supplier'];
			$gl_trans['person_id']= $data['supplier'];
			$gl_trans['person_type_id']= PT_SUPPLIER;

			$gl_trans['account']= $config_model->get_sys_pref_val('creditors_act');

		} else {
			$gl_trans['type'] = ST_CUSTPAYMENT;
			$gl_trans['customer'] = $data['customer'];
			$gl_trans['branch'] = $data['branch'];
			$gl_trans['person_id']= $data['customer'];
			$gl_trans['person_type_id']= PT_CUSTOMER;
// 			$gl_trans['account']= $gl_trans->sales_account;
			$gl_trans['account']= $config_model->get_sys_pref_val('debtors_act');
		}

		$gl_trans_model->gl_trans_customer($gl_trans);
// 		bug($gl_trans);die('supplier payment');
		$this->add_openning_item($gl_trans);
		redirect('admin/opening_balance/'.basename($_SERVER["SCRIPT_FILENAME"]));
		return;

	}

	private function add_openning_item($data=null){
		$openning = array(
			'type'=>null,
			'customer'=>null,
			'tran_date'=>date('Y-m-d'),
			'amount'=>0,
			'branch'=>0,
			'ref'=>null,
			'trans_no'=>0
		);
		foreach ($openning AS $key=>$v){
			if( isset($data[$key]) ){
				$openning[$key] = $data[$key];
			}
		}
		if( isset($data['tran_date']) ){
			$openning['tran_date'] = date('Y-m-d',strtotime($openning['tran_date']) );
		}
		$this->ci->db->insert('opening_sale',$openning);
// 		bug($openning);
// 		die('add openning');
	}
}