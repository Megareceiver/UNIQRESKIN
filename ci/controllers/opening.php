<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Opening extends ci {
    var $date_max = null;
	function __construct() {
		global $ci, $Ajax;;
		$this->ci = $ci;
		$this->db = $ci->db;
		$this->ajax = $Ajax;

		$this->customer_trans_model = $this->ci->model('customer_trans',true);
		$this->supplier_trans_model = $this->ci->model('supplier_trans',true);
		$this->common_model = $this->ci->model('common',true);
		$date = new DateTime(begin_fiscalyear());
		$date->modify('-1 day');
		$this->date_max = $date->format('Y-m-d');
	}

	var $stop = false;
	function index(){
        $post_date = null;
        if( $this->ci->input->post('tran_date') ){
            $post_date = $this->ci->input->post('tran_date');
        }

        $view_type = 'list';
	    if( $post_date && strtotime($post_date) > strtotime($this->date_max) ){
	       // trigger_error('Date input can\'t in current fiscal year!', E_USER_ERROR);
	        display_error(_("Date input can't in current fiscal year!"));
	        $view_type = 'form';
	        $this->stop = true;
	    }

        $type = $this->ci->input->get('type');
        if( $this->ci->input->post('type') ){
            $type = $this->ci->input->post('type');
        }
        switch ($type){
            case 'sale': return $this->items_view($type,$view_type); break;
            case 'purchase': return $this->items_view($type,$view_type); break;
            case 'inventory' : $this->inventory($type); break;
            default :break;
        }
	}

	private function items_view($type='sale',$view_type='list'){
	    $limit = 10;
	    start_form();
	    if( $this->ci->input->post('Remove') ){
	        $this->item_remove($type,$this->ci->input->post('Remove'));
	    } elseif( $this->ci->input->post() && $this->stop != true ){
            $this->item_submit();
	    }


        if( $this->ci->input->get('edit') OR $this->ci->input->get('new') OR $this->ci->input->post('new') OR $view_type=='form' ){
            if( $view_type=='form' OR  $this->ci->input->post('new') ){
                $id = 0;
                foreach ($this->fields AS $key=>$val){
                    $this->fields[$key]['value'] = $this->ci->input->post($key);
                }

            } else {
                $id = $this->ci->input->get('edit');
            }
            $this->item_form($type,$id);

        } else {
            $page = $this->ci->input->get('p');
            if( !$page ){
                $page = 1;
            }
            $data['page'] = $page;
            if( $type=='sale' ){
                $where_type = array(ST_SALESINVOICE,ST_CUSTPAYMENT);
                $this->db->select('sale.*, deb.name')->where_in('type',$where_type)->join('debtors_master AS deb', 'deb.debtor_no=sale.customer', 'left');
                $data['items'] = $this->db->limit($limit,($page-1)*$limit)->get('opening_sale AS sale')->result();
            } else if ( $type=='purchase') {
                $where_type = array(ST_SUPPINVOICE,ST_SUPPAYMENT);
                $data['items'] = $this->db->select('sale.*, sup.supp_name AS name')->where_in('type',$where_type)->join('suppliers AS sup', 'sup.supplier_id=sale.customer', 'left')->limit($limit*($page),($page-1)*$limit)->get('opening_sale AS sale')->result();
            }

            $data['total'] = $this->db->select('COUNT(s.id) AS total, SUM(s.debit*curr_rate) AS debit, SUM(s.credit*curr_rate) AS credit')->where_in('type',$where_type)->get('opening_sale AS s')->row();
            $data['lastpage'] = round($data['total']->total/$limit);
            if( $data['lastpage']* $limit < $data['total']->total ){
            	$data['lastpage'] ++;
            }
            $data['type'] = $type;
            $this->ci->view('opening/table_items',$data);
        }

        hidden('type',$type);
        end_form();
	}

	var $fields = array(
	        'id'=>array('value'=>0),
			'customer'=>array('value'=>null),
			'branch'=>array('value'=>null),
			'currency'=>array('value'=>null),
			'curr_rate'=>array('value'=>1),
			'debit'=>array('value'=>null),
			'credit'=>array('value'=>null),

			//'trans_no'=>array('value'=>null),
			'tran_date'=>array('value'=>''),
			'ref'=>array('value'=>null),
    );
	function item_form($type='sale',$id=0){
	    $data = $this->fields;

	    if( $id ){
	        $opening_sale = $this->db->where('id',$id)->get('opening_sale')->row();
	        foreach ($data AS $field=>$val){
	            if( isset($opening_sale->$field) && isset($data[$field]) && is_array($data[$field]) && array_key_exists('value', $data[$field]) ){
	                $data[$field]['value'] = $opening_sale->$field;
	            }
	        }
	    }
	    $data['type'] = $type;

	    if( !isset($data['tran_date']['value']) || !$data['tran_date']['value']  ){
	        $date = new DateTime(begin_fiscalyear());
	        $date->modify('-1 day');
	        $data['tran_date']['value'] = $date->format('Y-m-d');
	    }
// 	    bug($data);
	    $this->ci->view('opening/sale_item',$data);
// 	    submit_center('submit', _("Save"), true, '', 'default');
	    submit_center('submit', _("Save"), true, '', false);
	}

	private function item_submit(){


	    $data = array();
	    foreach ($this->fields AS $key=>$val){
	        $data[$key] = $this->ci->input->post($key);
	    }
	    $type = $this->ci->input->post('type');

	    if( $type=='sale' ){
	        $data['type'] = ST_SALESINVOICE;
// 	        $data['trans_no'] = $this->customer_trans_model->trans_no_new($data['type']);
	         $data['trans_no'] = get_next_trans_no(ST_SALESINVOICE);
	         if( !$this->ci->input->post('branch') ) {
	             display_error(_("Please input Branch"));
	         }

	    } else if ($type=='purchase'){
            $data['customer'] = $this->ci->input->post('supplier');
            unset($data['supplier']);
            $data['type'] = ST_SUPPINVOICE;
//             $data['trans_no'] = $this->supplier_trans_model->trans_no_new($data['type']);
            $data['trans_no'] = get_next_trans_no(ST_SUPPINVOICE);
        }


        if( $data['id'] ) {
            $opening_sale = $this->ci->db->where('id',$data['id'])->get('opening_sale')->row();
            $data['trans_no'] = $opening_sale->trans_no;
        }

        if( !$data['tran_date'] ){
        	$date = new DateTime(begin_fiscalyear());
        	$date->modify('-1 day');
            $data['tran_date'] = $date->format('Y-m-d');
        } else {
            $data['tran_date'] = date('Y-m-d',strtotime($data['tran_date']) );
        }

        $action = false;
        if( !$data['ref'] ){
            display_error(_("Please input Reference#"));
//             display_notification_centered(sprintf( _("Please input Reference#"),'0'));
//             die('show error');
        } elseif (!$data['debit'] && !$data['credit'] ){
            display_notification_centered(sprintf( _("Please input Debit or Credit"),'0'));
        } else {

            if( isset($data['id']) && $data['id'] > 0 ){
                $this->ci->db->where('id',$data['id'])->update('opening_sale',$data);
                $item_id = $data['id'];
            } else {
                $this->ci->db->insert('opening_sale',$data);
                $item_id = $this->ci->db->insert_id();
            }
            if( $type=='sale' ){
                $this->write_opening($item_id,$type);
            } else if ($type=='purchase'){
                $this->write_opening($item_id,$type);
            }
            $action = true;

            $this->ajax->redirect(basename($_SERVER["SCRIPT_FILENAME"])."?type=$type");
        }

        if($action == false){
            $_POST['new'] = 1;
        }

	}

	private function item_remove($type='sale',$opening_id=0){
	    $opening = $this->ci->db->where('id',$opening_id)->get('opening_sale')->row();

	    if( !empty($opening) ){
	        if( $type =='sale' ){
	            $this->ci->db->where(array('type'=>$opening->type,'trans_no'=>$opening->trans_no))->delete('debtor_trans');
	        } elseif($type=='purchase') {
	            $this->ci->db->where(array('type'=>$opening->type,'trans_no'=>$opening->trans_no))->delete('supp_trans');


	        }
	        $this->ci->db->where('opening',$type.'-'.$opening_id)->delete('gl_trans');
            $this->ci->db->where('id',$opening_id)->delete('opening_sale');
	    }
	    $this->ajax->redirect(basename($_SERVER["SCRIPT_FILENAME"])."?type=$type");

	}

	private function write_opening($sale_id=0,$type='sale'){

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
	            $customer_model = $this->ci->model('cutomer',true);

	            $gl_trans->customer($opening_sale->customer,$opening_sale->branch);
	            $this->packet_trans($opening_sale,ST_SALESINVOICE);

	            $gl_trans->set_value('person_type_id',PT_CUSTOMER);



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
// 	                die("do write_opening bank_account_default=$bank_account_default");
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


	/*
	 * BEGIN
	 * Opening balance inventory
	 */
	var $inv_fields =array(
		'code'=>array('type'=>'products','title'=>'Code','size'=>30),
		'name'=>array('type'=>'text','title'=>'Product Name','size'=>30,'attr'=>'class="prodname form-control "'),
		'cost'=>array('type'=>'number','title'=>'Opening Cost<br>(Base Curr)','size'=>12,'class'=>'form-control'),
		'qty'=>array('type'=>'number','title'=>'Opening QTY','size'=>12),
		'total'=>array('type'=>'text','title'=>'Valuation </br>(Base curr)','size'=>12,'attr'=>'class="number form-control" disabled'),
	);

	private function inventory($type='inventory'){


	    if( $this->ci->input->post('Remove') ){
	       $this->inventory_item_remove($this->ci->input->post('Remove'));
       } elseif( $this->ci->input->post('AddItem') ){
           $this->inventory_add();
       } elseif( $this->ci->input->post('update') && $this->ci->input->post('update')=='items' ){
          $this->inventory_add();
	    } elseif( $this->ci->input->post() ){
	       bug($this->ci->input->post());die;
	    }


	    $data = array();
	    $data['items'] = $this->ci->db->get('opening_product')->result();

	    $data['fields'] = $this->inv_fields;

	    start_form();
	    box_start();
	    $this->ci->view('opening/inventory',$data);
	    hidden('type',$type);

	    box_footer_start();

	    submit('update', _("Update"), true, '', 'default','save');
	    box_footer_end();
	    box_end();
	    end_form();

	}


	private function inventory_item_remove($id){
        $openning =  $this->db->where('id',$id)->get('opening_product')->row();

        if( $openning ){
             $this->db->where( array('trans_no'=>$openning->trans_no,'stock_id'=>$openning->code,'type'=>ST_INVADJUST) )->delete('stock_moves');
             $this->db->where('counter',$openning->gl_tran_id)->delete('gl_trans');
             $this->db->where('id',$id)->delete('opening_product');
        }

	}

	private function inventory_add(){
	    include_once(ROOT . "/purchasing/includes/purchasing_db.inc");
	    $model = $this->ci->model('openning',true);
	    if( $this->ci->input->post('code') ){
	        $item_total = count($this->ci->input->post('code'));
	        if( $item_total > 0 ){
	            for ($i=0;$i<$item_total;$i++){
                    $newData = array();
                    foreach ($this->inv_fields AS $name=>$field){
                        if( isset($field['title']) && $field['title'] ){
	                       if( isset($_POST[$name][$i]) ){
	                           $newData[$name] = $_POST[$name][$i];
                            }
                        }
                    }

                    if( isset($newData['qty']) && $newData['qty'] > 0 ){
                        $openning =  $this->db->where('code',$newData['code'])->get('opening_product')->row();;
	                    unset($newData['total']);
	                    if( $openning->id ){
                           $this->db->update('opening_product',$newData,array('id'=>$openning->id));
                        } else {
	                       $this->db->insert('opening_product',$newData);
                        }
                    }
                }
            }
        }

        foreach ( $this->db->get('opening_product')->result() AS $opening_item){
            $itemAccount = $this->common_model->get_row(array('stock_id'=>$opening_item->code),'stock_master');
            if( !$itemAccount ) continue;
            //$inventory_account = $itemAccount['inventory_account'];

            if( $opening_item->trans_no ){
                $trans_no = $opening_item->trans_no;
            } else {
                $trans_no = get_next_trans_no(ST_INVADJUST);
            }

            $stock_moves = array(
                'trans_no'=>$trans_no,
                'stock_id'=>$opening_item->code,
                'loc_code'=>'DEF',
                'type'=>ST_INVADJUST,
                'tran_date'=>$this->date_max,
                'qty'=>$opening_item->qty,
                'price'=>$opening_item->cost*$opening_item->qty
            );
            $this->common_model->stock_move_update($stock_moves);

            $this->common_model->update_material_cost($opening_item->code);


            $gl_trans = array(
                'type'=>ST_INVADJUST,
                'type_no'=>$trans_no,
                'tran_date'=>$this->date_max,
                'account'=>$itemAccount->inventory_account,
                'amount'=>$opening_item->cost*$opening_item->qty
            );

            $this->db->reset();
            if( isset($opening_item->gl_tran_id) && $opening_item->gl_tran_id != 0 ){
                $this->db->where('counter',$opening_item->gl_tran_id)->update('gl_trans',$gl_trans);
            } else {
                $this->db->insert('gl_trans',$gl_trans);
                $gl_trans_id = $this->db->insert_id();
                $this->db->reset();
                $this->db->where('id',$opening_item->id)->update('opening_product',array('gl_tran_id'=>$gl_trans_id,'trans_no'=>$trans_no));
            }

        }
	}
	/*
	 * END
	 * Opening balance inventory
	 */
}