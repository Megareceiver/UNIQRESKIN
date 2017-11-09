<?php

include ROOT.'/includes/controller.php';

class opening extends controller {
	var $fields = array();
	function __construct(){
		global $ci, $Ajax;
		$this->ci = $ci;
		$this->Ajax = $Ajax;
		$this->model = new model();
		$this->view = new view();
	}

	function date_endoflastyear(){
		$date = new DateTime(date('Y').'-1-1');
		$date->modify('-1 day');
		return $date->format('d-m-Y');
	}


	function system_gl(){
		$gl_model = $this->ci->model('gl',true);
		$gl_trans_model = $this->ci->model('gl_trans',true);
		$openning_model = $this->ci->model('openning',true);

		$accounts = $gl_model->get_gl_accounts();
		$date_default = $this->date_endoflastyear();

		if( $this->ci->input->post() ){

			$gl_posting = array(
				'type'=>ST_BANKPAYMENT,
				'tran_date'=>$this->ci->input->post('trans_date'),
				'account'=>0,
				'amount'=>0
			);
			$credits = $this->ci->input->post('credit');
			if( $credits ){foreach ($credits AS $acc=>$val){
				$amount = intval($val);
				if( $amount > 0 ){
					$gl_posting['account'] =$acc;
					$gl_posting['amount'] =-$val;
					//$gl_trans_model->gl_trans_customer($gl_posting);
				}

			}}


			$debits = $this->ci->input->post('debit');
			$openning_model->check_gl_trans_openningfield();
			if( $debits ){foreach ($debits AS $acc=>$val){
				$amount = intval($val);
				if( $amount > 0 ){

					$gl_posting['account'] =$acc;
					$gl_posting['amount'] =$val;
					$gl_posting['openning'] = $openning_model->add_gl_system($gl_posting);

					$gl_posting['counter'] = 9999;
					$gl_trans_model->gl_trans_customer($gl_posting);
				}

			}}

			bug($debits); die('submit data');
		}


		$talbe = '<table class="datagird-border"  cellspacing="0" cellpadding="0" ><thead>'
				.'<tr><td style="width:10%;" >GL code</td><td style="width:60%;" >GL Description</td><td style="width:10%;" >Debit</td><td style="width:10%;" >Credit</td></tr>'
			.'</thead>';
		$talbe.='<tr><td colspan=3 >Opening Balance Date</td>';
		$talbe.='<td>'.$this->view->datefix('trans_date',array('value'=>$date_default,'readonly'=>true),false).'</td>';
		$talbe.='</tr>';


		$i = 1;
		$groups = array();

		foreach ($accounts AS $item){
			if( $i > 7 ) break;
			if( !in_array($item->id, $groups) ){

				$groups[] = $item->id;
				$i++;

				$talbe.= '<tr><td colspan=4 class="tableheader" >'.$item->type_name.'</td></tr>';
				$talbe.= '<tr class="tableheader2" ><td>GL code</td><td>GL Description</td><td>Debit</td><td>Credit</td></tr>';
			}

			$talbe.='<tr><td>'.$item->account_code.'</td>';
			$talbe.= '<td>'.$item->account_name.'</td>';
			$talbe.= '<td class="center" >'.$this->ci->form->input_text('debit['.$item->account_code.']',null,'class="number" ').'</td>';
			$talbe.= '<td class="center" >'.$this->ci->form->input_text('credit['.$item->account_code.']',null,'class="number" ').'</td>';
			$talbe.= '</tr>';

		}
		$talbe.= '</table>';
		echo $talbe;
	}
}