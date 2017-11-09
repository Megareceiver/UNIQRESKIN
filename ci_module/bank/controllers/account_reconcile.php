<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BankAccountReconcile {

    function __construct() {
        $this->db = get_instance()->db;
        $this->input = get_instance()->input;
        $this->datatable = module_control_load('datatable','html');

        $this->reconcile_db = module_model_load('reconciled','bank');

    }

    static function smarty_actions($item=NULL){

        $html = gl_tran_view($item->trans_no,$item->type,'icon');

        $selected = strlen($item->reconciled > 6) ? true : false;
        $html.= '<span class="icon19"><input name="rec_'.$item->id.'" value="'.$selected.'" onclick="JsHttpRequest.request(\'_rec_'.$item->id.'_update\', this.form);" title="Reconcile this transaction" type="checkbox" '.($selected ? 'checked' : NULL).'></span>';
        $html.= hidden('[last'.$item->id.']',$selected);

        $html.= anchor_report_icon($item->trans_no,$item->type);
        return $html;
    }

    private function build_page(){
        get_instance()->formdata = array(
            'bank_account' => array('type'=>'BANK_ACCOUNTS','title'=>'Account','value'=>0,'onchange_ajax'=>true ),
            'bank_date' => array('type'=>'options','title'=>'Bank Statement','value'=>''),
            'reconcile_date'=>array('type'=>'date','value'=>'' ),
            'beg_balance'=>array('type'=>'number','value'=>0 ),
            'end_balance'=>array('type'=>'number','value'=>0 ),
            'total'=>array('type'=>'lable','value'=>0 ),
            'reconciled'=>array('type'=>'lable','value'=>0,'id'=>'reconciled' ),
            'difference'=>array('type'=>'lable','value'=>0,'id'=>'difference' ),

        );

        get_instance()->smarty->registerPlugin('function', 'reconcile_actions', "BankAccountReconcile::smarty_actions" );
    }

    private function form_actions(){
        global $Ajax;

        $formdata_old = array();
        if( $this->input->post() ) {
            foreach (get_instance()->formdata AS $k=>$f){
                if( isset($f['value']) ){
                    $formdata_old[$k] = formdata_getval ($k);
                    formdata_setpost($k);
                }
            }


            if( $formdata_old['bank_account'] != formdata_getval('bank_account') ) {
                $Ajax->activate('bank_reconcile_header');
                $Ajax->activate('reconsile_transactions');
            } elseif ($formdata_old['bank_date'] != formdata_getval('bank_date')) {
                $Ajax->activate('reconsile_transactions');
            }


            set_cache_page_val(array('bank_account'=>formdata_getval('bank_account')));
        } else {

            formdata_set('bank_account',get_cache_page_val('bank_account'));
        }


        if( input_post('bank_date') ){
            $bank_date =formdata_getval('bank_date');

            if( !is_date($bank_date) OR strlen($bank_date) < 4 ){
                $bank_date = Today();

            } else {
                $bank_date = sql2date(input_post('bank_date'));
//                 formdata_setval('bank_date',$bank_date );
            }
//             formdata_setval('bank_date',$bank_date );
            set_cache_page_val(array('bank_date'=>$bank_date));

            formdata_setval('reconcile_date',$bank_date );

//             $Ajax->activate('bank_reconcile_header');
//             $Ajax->activate('reconsile_transactions');
        } else {
            formdata_set('reconcile_date',Today());
//             formdata_set('bank_date',get_cache_page_val('bank_date'));
        }






        $id = find_submit('_rec_');
        if ($id != -1) {
            $this->change_tpl_flag($id);
        }
//         else if( $this->input->post() ){
// //             formdata_setpost('reconcile_date');
//             $Ajax->activate('bank_concile');
//         }


    }

    private function change_tpl_flag($reconcile_id)
    {
        $reconcile_value = check_value("rec_".$reconcile_id) ? formdata_getval('bank_date') : NULL;

        $this->db->where('id',$reconcile_id)->update('bank_trans',array('reconciled'=>$reconcile_value));
//     	global	$Ajax;

//     	if (!check_date() && check_value("rec_".$reconcile_id)) // temporary fix
//     		return false;

//     	if (get_post('bank_date')=='')	// new reconciliation
//     		$Ajax->activate('bank_date');

//     	$_POST['bank_date'] = date2sql(get_post('reconcile_date'));
//     	$reconcile_value = check_value("rec_".$reconcile_id)
//     						? ("'".$_POST['bank_date'] ."'") : 'NULL';

//     	update_reconciled_values($reconcile_id, $reconcile_value, $_POST['reconcile_date'], input_num('end_balance'), $_POST['bank_account']);

//     	$Ajax->activate('reconciled');
//     	$Ajax->activate('difference');
    	return true;
    }

    function index(){
        $this->build_page();

        add_js_file('js/reconcile.js');
        page("Reconcile Bank Account");
        div_start('bank_concile');

        start_form();

            div_start('bank_reconcile_header');
            $this->form_actions();
            $this->reconcile_data();
//             bug(get_instance()->formdata);
            module_view('account-reconcile');
            div_end();

        $bank_id = formdata_getval('bank_account');
        if( $bank_id ){
            $bank = $this->db->where('id',$bank_id)->get('bank_accounts')->row();
            html_element('h3',$bank->bank_name." - ".$bank->bank_curr_code,'style="text-align: center;"');
        }


        $this->datatable->view(
            $this->table_trans,
            $this->reconcile_db->get_bank_account_reconcile(formdata_getval('bank_account'), formdata_getval('reconcile_date')),
            'reconsile_transactions'
        );
        end_form();

        div_end();
        end_page();
    }

    var $table_trans = array(
        'type'=>array('Type','',13,'trans_type'),
        'trans_no'=>array('#',null,7),
        'ref'=>array('Reference',null,12),
        'trans_date'=>array('Date','center',8),
        'debit'=>array('Debit','right',12,'number'),
        'credit'=>array('Credit','right',12,'number'),
        'person_name'=>array('Person/Item'),
        'cheque'=>array('Cheque No.'),
        'actions'=>array('X','center',8,'func_add','reconcile_actions'),
    );

    private function reconcile_data(){
        $formdata = get_instance()->formdata;
        $reconcile_date = formdata_getval('reconcile_date');
        $bank_date = formdata_getval('bank_date');


        $bank_acc = formdata_getval('bank_account');
        $data = $this->reconcile_db->get_max_reconciled( $reconcile_date ,$bank_acc );
bug($data);
        formdata_set_atribute('bank_date',$this->reconcile_db->get_reconciliation_list($bank_acc),'options');

//         if ( !formdata_getval('beg_balance') ){
            formdata_set('beg_balance', $data->beg_balance);
            formdata_set('end_balance', $data->end_balance);
            formdata_set('total',       price_format($data->total) );
            formdata_set('reconciled',  price_format($data->end_balance- $data->beg_balance) );

            if (get_post('bank_date')) {
                // if it is the last updated bank statement retrieve ending balance
                $ending_reconciled = $this->reconcile_db->get_ending_reconciled($bank_acc, $bank_date );

                if($ending_reconciled) {
                    formdata_set('end_balance', $ending_reconciled->ending_reconcile_balance);
                }
            }

//         }

//         formdata_set('transactions',$this->reconcile_db->get_bank_account_reconcile(formdata_getval('bank_account'), formdata_getval('reconcile_date')));

//         bug(formdata_getval('transactions'));die('bbb');
    }


}