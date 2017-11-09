<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_GLSETUP';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");
include ROOT.'/includes/view.php';
include ROOT.'/admin/controller/opening_balance.php';
include ROOT.'/admin/model/opening_balance_model.php';

global $ci,$Ajax;

$model = new opening_balance_model();
$js = null;

if( isset($_GET['type']) ){
	$type = $_GET['type'];
} else if (isset($_POST['type'])) {
	$type = $_POST['type'];
}
$Ajax->activate('_page_body');


switch ($type){
	case 'bank':
		page(_($help_context = "Bank Account Opening Balance"),false, false, "", $js);
		$date = new DateTime(begin_fiscalyear());
		$date->modify('-1 day');

		$fields = array(
			'trans_date'=>array('input'=>'date','title'=>_('Date'),'value'=>$date->format('d-m-Y')),
			'bank_act'=>array('input'=>'bank_accounts','title'=>_('Bank Account'),'onchange_ajax'=>true,'value'=>null),
			'amount'=>array('input'=>'number','title'=>_('Amount'))
		);



		if( $ci->input->post('bank_act') ){
		    $fields['bank_act']['value'] = $ci->input->post('bank_act');
		}


		$openning =$ci->db->where( array('account'=>$fields['bank_act']['value'],'pay_type'=>'bank') )->get('opening_gl')->row();
		if( isset($openning->id) ){
// 		    bug( $openning );
		    $fields['amount']['value'] = $openning->amount;
		    $fields['trans_date']['value'] = sql2date($openning->tran_date);

		} else if ( !array_key_exists('_bank_act_update', $_POST) ){
		    if( $ci->input->post('trans_date') ){
		        $fields['trans_date']['value'] = $ci->input->post('trans_date');
		    }
		    if( $ci->input->post('amount') ){
		        $fields['amount']['value'] = $ci->input->post('amount');
		    }
		}

		$action = new openingbalance_controller($fields);

		break;
	case 'gl':
// 		$js .= get_js_date_picker();
		page(_($help_context = "System GL Accounts Opening Balance"),false, false, "", $js);
		$gl_accounts = $model->chart_master();
		$action = new openingbalance_controller($gl_accounts);
		break;
	case 'inv':

	default:
		$action = new controller();
		break;
}

$fields['type'] = array('input'=>'hidden','value'=>$type);
$action->fields['type'] = array('input'=>'hidden','value'=>$type);
$action->actions();


include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
// bug();die;
$view = new view($fields);
// include_once($path_to_root . "/admin/db/company_db.inc");

//-------------------------------------------------------------------------------------------------


//-------------------------------------------------------------------------------------------------

// bug($fields);die;
switch ($type){
	case 'bank':
// 		$view->form();
        $ci->finput->print_form($action->fields);
		break;
	case 'gl':
		$action->gl_account();
		break;
	default:break;
}




//-------------------------------------------------------------------------------------------------

end_page();

?>
