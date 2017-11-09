<?php

include ROOT.'/includes/controller.php';


class openingbalance_controller extends controller {
	var $fields = array();
	function __construct($fields=array()){
		global $ci, $Ajax;
		$this->ci = $ci;
		$this->db = $ci->db;
		$this->Ajax = $Ajax;

		$this->fields = $fields;
		$this->model = new model();
		$this->view = new view();
	}

	function actions(){
		if (isset($_POST['submit']) ){
		    $begin_fiscalyear = begin_fiscalyear();

            $input_date = null;
            if( $this->ci->input->post('trans_date') ) {
                $input_date = $this->ci->input->post('trans_date');
            }

		    if( strtotime($input_date) >= strtotime(begin_fiscalyear()) ){
		        trigger_error('Date input can\'t in current fiscal year!', E_USER_ERROR);
		    } else {
		        if( $_POST['type'] == 'bank' ){
		            return $this->add_bank_opening_balance();
		        } else if( $_POST['type'] == 'inv' ){
		            return $this->add_item_opening_balance();
		        } else {
		            return $this->add_gl_opening_balance();
		        }
		    }


		}
	}



	function add_bank_opening_balance(){
	    $pay_type = 'bank';
		$data = array();
		foreach ($this->fields AS $kname=>$val){
			$data[$kname] = $_POST[$kname];
			$this->fields[$kname]['value'] = $_POST[$kname];
		}
		if( isset($data['trans_date']) ){
			$data['trans_date'] = date('Y-m-d',strtotime($data['trans_date']));
		}


		$openning_gl = $this->db->where(array('account'=>$data['bank_act'],'pay_type'=>$pay_type))->get('opening_gl')->row();
		$openning_add = $data;
		unset($openning_add['bank_act']); $openning_add['account'] = $data['bank_act'];
		unset($openning_add['trans_date']); $openning_add['tran_date'] = $data['trans_date'];
		$openning_add['pay_type'] = $pay_type;


		if( $openning_gl && isset($openning_gl->id)  ){


		    $this->db->insert('opening_cache',array('data'=>json_encode($openning_gl) ));
		    $this->db->where(array('id'=>$openning_gl->id,'pay_type'=>$pay_type))->update('opening_gl',$openning_add);
		    $gl_trans_id = $openning_gl->gl_tran_id;
		    $openning_id = $openning_gl->id;
		} else {

		    $this->db->insert('opening_gl',$openning_add);
		    $openning_id = $this->db->insert_id();
		    $gl_trans_id = null;
		}

		if( $gl_trans_id ){

		    $this->db->where('id',$openning_gl->gl_tran_id)->update('bank_trans',$data);
		} else {
		    $this->db->insert('bank_trans',$data);
		    $gl_trans_id = $this->db->insert_id();
		    $this->db->where(array('id'=>$openning_id,'pay_type'=>$pay_type))->update('opening_gl',array('gl_tran_id'=>$gl_trans_id));
		}


		display_notification(_("Bank Opening Balance has been updated."));
		$this->reset();
	}

	function gl_account($account=array()){

		global $ci;
		start_form();
		box_start();
		start_table(TABLESTYLE2);


		echo '<tr><td colspan=2 >Opening Balance Date</td>';
		$exist_date = $ci->db->select('tran_date')->where('amount !=',0)->get('opening_gl',1)->row();

		if( $exist_date && isset($exist_date->tran_date) ){
			$tran_date = date('d-m-Y',strtotime($exist_date->tran_date) );
		} else {
		    $current_year = get_current_fiscalyear();
			$date = new DateTime(date2sql($current_year['begin']));
			$date->modify('-1 day');
			$tran_date = $date->format('d-m-Y');
		}
		$_POST['trans_date'] = $tran_date;
		echo date_cells(null,'trans_date');

		echo '</tr>';

		$i = 1;

		$model = $ci->model('openning',true);
		$gl_account_posting = $model->gl_account_get();
		$debit_total = $credit_total = 0;
		foreach ($gl_account_posting AS $group=>$items){

                echo '<tr><td colspan=3 class="tableheader" >'.$group.'</td></tr>';
                if( $i <= 1 ){
                    echo '<tr><td style="width:60%;"> </td><td class="textright" >Debit</td><td class="textright" >Credit</td></tr>';
                }

			if( $items ){

				foreach ($items as $code=>$item) {
					echo '<tr><td>'.$item['name'].'</td>';
					echo '<td>';
					input_money(NULL,"debit[$code]",money_total_format($item['debit']) , curr_default() );
                    echo '</td>';
					echo '<td>';
					input_money(NULL,"credit[$code]",money_total_format($item['credit']) , curr_default() );
					echo '</td>';
					echo '</tr>';
					$debit_total+=$item['debit'];
					$credit_total+=$item['credit'];
				}

			}
			$i++;
		}
		echo '<tr><td class="textright textbold" >Total</td>';
		echo '<td class="textright" >'.money_total_format($debit_total).'</td>';
		echo '<td class="textright">'.money_total_format($credit_total).'</td>';
		echo '</tr>';
		echo '<input type="hidden" name="type" value="gl">';
		end_table(1);

		box_footer_start();
		submit('submit', _("Submit"), true, '', 'default','save');
		box_footer_end();
		box_end();
		end_form(1);
	}

	function add_gl_opening_balance(){
		global $ci;
		$model = $ci->model('openning',true);

		$tran_date = date('Y-m-d',strtotime($_POST['trans_date']));
		$debits = $ci->input->post('debit');
		$credits = $ci->input->post('credit');



		if( is_array($debits) ){
			foreach ($debits AS $acc_code=>$debit){
				if( $debit != '' ){
					$newData = array(
						'amount' =>strtonumber($debit),
						'type'=>ST_OPENING_GL,
						'account'=>$acc_code,
						'tran_date'=>$tran_date
					);

					$model->update_gl_account($newData,'debit');

				}

			}
		}

		if( is_array($credits) ){
			foreach ($credits AS $acc_code=>$credit){ if( $credit != '' ){
					$newData = array(
						'amount' =>-strtonumber($credit),
						'type'=>1,
						'account'=>$acc_code,
						'tran_date'=>$tran_date
					);
					$model->update_gl_account($newData,'credit');
			}}
		}

		display_notification(_("GL Account Opening Balance has been updated."));
		$this->reset();
	}


	private function add_item_opening_balance(){
		$items = $_POST['code'];
		$cost = $_POST['cost'];
// 		bug($_POST);
// 		die('call me');
	}

	function listview(){

		global $Ajax;

		$th = array();
		$input = '';

// 		foreach ($this->fields AS $name=>$field){
// 			if( isset($field['title']) && $field['title'] ){
// 				$th[] = $field['title'];
// 				$input.= '<td>'.$this->view->input($name.'[1]',$field,false).'</td>';
// 				$post[] = $name;
// 			}
// 		}
// 		$input.='<td><button id="AddItem" class="ajaxsubmit" title="Add new item to document" value="Add Item" name="AddItem" type="submit" ><span>Add Item</span></button></td>';

// 		$th[]= '';





// 		start_table(TABLESTYLE2,' id="item_listview" ');
// 		table_header($th);


		if( isset($_POST['AddItem']) || isset($_POST['submit']) ){
			$item_ids = $this->ci->input->post('id');
			if( count($item_ids) > 0 ){
				for ($i=0;$i< count($item_ids);$i++){
					$newData = array();
					foreach ($this->fields AS $name=>$field){ if( isset($field['title']) && $field['title'] ){
						$post_val = $this->ci->input->post($name);
						if( isset($post_val[$i]) ){
							$newData[$name] = $post_val[$i];
						}
					}}

					if( isset($newData['qty']) && $newData['qty'] > 0 ){
						if( isset($item_ids[$i]) ){
							//bug('update id='.$item_ids[$i]);
							//$this->model->update($newData,'opening_product',$item_ids[$i]);
							$this->ci->db->where('id',$item_ids[$i])->update('opening_product',$newData);
						} else {
							$this->ci->db->insert('opening_product',$newData);
						}
					}
				}
			}
			if( isset($_POST['submit']) ){
				redirect('admin/'.basename($_SERVER["SCRIPT_FILENAME"]).'?type='.$_POST['type']);
			} else {
				$Ajax->redirect('opening_balance_items.php?type='.$_POST['type']);
				page_modified();
			}

		}

		if( isset($_POST['Remove']) ){
			$this->model->delete($_POST['Remove'],'opening_product');
			$Ajax->redirect('opening_balance_items.php?type='.$_POST['type']);
		}

		$data = array();
		$data['items'] = $this->ci->db->get('opening_product')->result();

		$data['fields'] = $this->fields;
		echo $this->ci->view('opening/inventory',$data,true);
	}



	function add_inventory(){
		global $ci;

		$model = $ci->model('openning',true);

		if( isset($_POST['code']) ){
			$item_total = count($_POST['code']);

			if( $item_total > 0 ){
				for ($i=0;$i<$item_total;$i++){

					$newData = array();
					foreach ($this->fields AS $name=>$field){
						if( isset($field['title']) && $field['title'] ){
							if( isset($_POST[$name][$i]) ){
								$newData[$name] = $_POST[$name][$i];
							}
						}
					}

					if( isset($newData['qty']) && $newData['qty'] > 0 ){
// 						bug($newData);
						$openning = $model->product_get($newData['code']);
						unset($newData['total']);
						if( $openning->id ){

							$ci->db->where('id',$openning->id)->update('opening_product',$newData);
						} else {
							$ci->db->insert('opening_product',$newData);
						}

// 						bug($ci->db->last_query());
// 						bug($newData);
//

					}

				}
			}

// 			die('go to update');

		}

		$date = new DateTime(date('Y').'-1-1');
		$date->modify('-1 day');

		$items = $this->model->get_all('opening_product');
		$trans_no = $this->model->get_trans_no('stock_moves');
		$adj_id = get_next_trans_no(ST_INVADJUST);

		while ($row = db_fetch($items)){


			$itemAccount = $this->model->get_row('stock_id='.db_escape($row['code']),'stock_master');
			//$inventory_account = $itemAccount['inventory_account'];

			$stock_moves = array(
				'trans_no'=>$trans_no,
				'stock_id'=>$row['code'],
				'loc_code'=>'DEF',
				'type'=>ST_INVADJUST,
				//'type_no'=>$adj_id,
				'tran_date'=>$date->format('Y-m-d'),
				'qty'=>$row['qty'],
			);

			$this->model->insert($stock_moves,'stock_moves');

			$gl_trans = array(
					'type'=>ST_INVADJUST,
					'type_no'=>$adj_id,
					'tran_date'=>$date->format('Y-m-d'),
					'account'=>$itemAccount['inventory_account'],
					'amount'=>$row['cost']*$row['qty']
			);


			if( isset($row['gl_tran_id']) && $row['gl_tran_id'] !='' ){
				$ci->db->where('counter',$row['gl_tran_id'])->update('gl_trans',$gl_trans);
			} else {
				$ci->db->insert('gl_trans',$gl_trans);
				$gl_trans_id = $ci->db->insert_id();
				$ci->db->where('id',$row['id'])->update('opening_product',array('gl_tran_id'=>$gl_trans_id));
			}




		}
	}


	function listview_customer(){

		global $Ajax;

		$th = array();
		$input = '';
// 	bug($this->fields );die('quannh');
		foreach ($this->fields AS $name=>$field){
			if( isset($field['title']) && $field['title'] ){
				$th[] = $field['title'];
				$input.= '<td>'.$this->view->input($name.'[1]',$field,false).'</td>';
				$post[] = $name;
			}
		}
		$input.='<td><button id="AddItem" class="ajaxsubmit" title="Add new item to document" value="Add Item" name="AddItem" type="submit" ><span>Add Item</span></button></td>';

		$th[]= '';





		start_table(TABLESTYLE2,' id="item_listview" ');
		table_header($th);


		if( isset($_POST['AddItem']) ){

			if( isset($_POST[$post[0]]) ){
				// 							bug($_POST);die;
				$item_total = count($_POST[$post[0]]);

				if( $item_total > 0 ){
					for ($i=1;$i<=$item_total;$i++){
						$newData = array();
						foreach ($this->fields AS $name=>$field){
							if( isset($field['title']) && $field['title'] ){
								if( isset($_POST[$name][$i]) ){
									$newData[$name] = $_POST[$name][$i];
								}
							}
						}
						if( isset($newData['balance']) && $newData['balance'] > 0 ){
							$exist_id = $this->model->get_row_id(" customer_id='".$newData['customer_id']."'",'opening_customer');
							if( $exist_id ){
								$this->model->update($newData,'opening_customer',$exist_id);
							} else {
								$this->model->insert($newData,'opening_customer');
							}

						}
					}
				}

				//display_notification(_("GL Account Opening Balance has been updated."));
			}
			$Ajax->redirect('opening_balance_items.php?type='.$_POST['type']);
			page_modified();
		}

		if( isset($_POST['Delete']) ){
			$this->model->delete($_POST['Delete'],'opening_customer');
			$Ajax->redirect('opening_balance_items.php?type='.$_POST['type']);
		}

		$items = $this->model->get_all('opening_customer');
		$count = 2;

		while ($itemrow = db_fetch($items)){

			$item_row_html='';

			foreach ($this->fields AS $name=>$field){
				if( isset($field['title']) && $field['title'] ){
					$field['value'] = $itemrow[$name];
					$item_row_html.= '<td>'.$this->view->input($name.'['.$count.']',$field,false).'</td>';

				}
			}
			$remove = '<button value="'.$itemrow['id'].'" name="Delete" class="ajaxsubmit" type="submit"> Remove </button>';
			echo '<tr>'.$item_row_html.'<td>'.$remove.'</td></tr>';
			$count++;
		}


		echo '<tr>'.$input.'</tr>';
		end_table(1);
	}

	function add_customer(){
		if( isset($_POST['balance']) ){
			$item_total = count($_POST['balance']);

			if( $item_total > 0 ){
				for ($i=0;$i<$item_total;$i++){
					$newData = array();
					foreach ($this->fields AS $name=>$field){
						if( isset($field['title']) && $field['title'] ){
							if( isset($_POST[$name][$i]) ){
								$newData[$name] = $_POST[$name][$i];
							}
						}
					}

					if( isset($newData['balance']) && $newData['balance'] > 0 ){
						$exist_id = $this->model->get_row_id(" customer_id='".$newData['customer_id']."'",'opening_customer');
						if( $exist_id ){
							$this->model->update($newData,'opening_customer',$exist_id);
						} else {
							$this->model->insert($newData,'opening_customer');
						}

					}

				}
			}
		}
		// 		die('update DB');
		$date = new DateTime(date('Y').'-1-1');
		$date->modify('-1 day');
		$items = $this->model->get_all('opening_customer');
		$balance =0;
		while ($row = db_fetch($items)){
			$balance +=$row['balance'];
		}
		$account = $this->model->get_row(" name='debtors_act' ",'sys_prefs');
		bug($account);
		bug($balance);
		bug('account receivable=');
	}

}
