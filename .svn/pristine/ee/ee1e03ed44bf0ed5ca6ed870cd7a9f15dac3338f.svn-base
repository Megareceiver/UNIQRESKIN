<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaintenanceOpening {

    function __construct() {
        $fiscal_year = get_current_fiscalyear();
        if( empty($fiscal_year) ){
            redirect('admin/fiscal-years');
        }
        global $ci, $Ajax;
        $this->ci = $ci;
        $this->ajax = $Ajax;
        $this->page_security = 'SA_SETUPCOMPANY';
        $path_to_root = '.';
        include_once(ROOT. "/includes/ui.inc");

        $this->db = $ci->db;
        $this->model_sys = $ci->model('config',true);
        $this->model = module_model_load('opening');

        $date = new DateTime(begin_fiscalyear());
        $date->modify('-1 day');
        $this->end_last_fiscalyear = $date->format($this->ci->dateformatPHP);

        $this->page = input_val('page');
        $this->page_select = false;
        if( !$this->page ){
            $this->page = input_val('first');
            $this->page_select = true;
        }
        if( !$this->page ){
            $this->page = input_val('next');
            $this->page_select = true;
        }
        if( !$this->page ){
            $this->page = input_val('end');
            $this->page_select = true;
        }

        if( !$this->page || intval($this->page) < 1){
            $this->page = 1;
        }

        $config_model = $this->ci->model('config',true);
        $bank_model = $this->ci->model('bank',true);


        add_js_file('/js/opening.js');

        $this->ci->smarty->registerPlugin('function', 'gl_ob_edit', "MaintenanceOpening::edit_button" );

    }

    function index(){
        global $Ajax;

        $type = $this->ci->uri->segment(3);

        page( ucfirst($type).' Opening Balance');
        $Ajax->activate('_page_body');
        start_form();

        if( $this->ci->input->post('submit')  ){
            return $this->customer_submit();
        } elseif ( $this->ci->input->get('remove') ) {
            $this->item_remove($type,input_val('remove'));
            redirect( "maintenance/opening/$type" );
        } else  if( $this->ci->uri->segment(4)=='add' || $this->ci->uri->segment(4)=='view'  ){
            $id = 0;
            if ( $this->ci->input->post() ){
                foreach ($this->fields AS $key=>$val){
                    $this->fields[$key]['value'] = $this->ci->input->post($key);
                }
                if( $this->fields['currency']['value'] ){
                    $this->fields['curr_rate']['value'] = $this->model_sys->exchange_rate_get($this->fields['currency']['value'],$this->fields['tran_date']['value']);
                }
            } else {
                $id = $this->ci->input->get('edit');
            }
            $this->formView($type,$id);
        } else {
            $html = $this->listView($type);
            echo $html;
        }

        end_form();
        end_page();
    }

    function bank($pay_type = 'bank'){
        if( has_post() ){
            $banks = input_post('bank');

            foreach ($banks AS $id=>$amount){
                $amount = str2numeric($amount);

                if( is_numeric($amount) ){
                    $this->add_bank_opening_balance($id,$amount);
                }
            }
            display_notification(_("Bank Opening Balance has been updated."));

        }

        page( "Bank Account Opening Balance");

        $exist_date = $this->ci->db->select('trans_date')->where('amount !=',0)->get('opening_gl',1)->row();

        if( $exist_date && isset($exist_date->tran_date) ){
            $tran_date = sql2date($exist_date->tran_date);
        } else {
            $current_year = get_current_fiscalyear();
            $date = new DateTime(date2sql($current_year['begin']));
            $date->modify('-1 day');
            $tran_date = $date->format('d-m-Y');
        }
        $_POST['trans_date'] = $tran_date;

        box_start();
        start_form();
        start_table(TABLESTYLE2);


        echo '<thead><tr><td colspan=2 >Opening Balance Date</td>';

        echo date_cells(null,'trans_date',null, $check = null, $inc_days = 0, $inc_months = 0, $inc_years = 0, 'style="width:200px"');

        echo '</tr></thead>';

        $i = 1;


        $bank_account_posting = $this->ci->db->get('bank_accounts AS b')->result();
        echo '<tr><td colspan=2 class="col-xl-9 col-sm-5 col-5"></td><td class="textright" >Amount</td></tr>';


        foreach ($bank_account_posting AS $group=>$bank){
            $ob = $this->ci->db->where(array('ob.type'=>$pay_type,'ob.account'=>$bank->id))->get('opening_gl AS ob')->row();

            echo '<tr><td style="width:7%;">'.$bank->account_code.'</td><td>'.$bank->bank_account_name.'</td>';



            echo '<td>';
            input_money(NULL,"bank[".$bank->id."]",isset($ob->amount) ? number_total($ob->amount) : NULL , curr_default() );
            echo '</td>';
//             echo '<td><input class="textright" type="text" name="bank['.$bank->id.']" value="'.(isset($ob->amount) ? number_total($ob->amount) : NULL).'" ></td>';
            echo '</tr>';
        }
//         echo '<tr><td class="textright textbold" colspan=2 >Total</td>';
//         echo '<td class="textright" >'.money_total_format($debit_total).'</td>';
//         echo '<td class="textright">'.money_total_format($credit_total).'</td>';
//         echo '</tr>';
        echo '<input type="hidden" name="type" value="bank">';
        end_table(1);

        box_footer_start();
        submit('submit', _("Submit"), true, '', 'default','save');
//         submit('submit', _("Submit"), true, '', false,'save');
        box_footer_end();
        end_form(1);
        box_end();

    }

    function add_bank_opening_balance($account_id=0,$amount=0){
        if( !$account_id )
            return;

        $pay_type = 'bank';
        $data = array();

        $data['trans_date'] = date2sql(input_val('trans_date'));
        $data['account'] = $account_id;
        $data['amount'] = $amount;

        $openning_gl = $this->db->where(array('account'=>$data['account'],'pay_type'=>$pay_type))->get('opening_gl')->row();

       if( $openning_gl && isset($openning_gl->id)  ){


            $this->db->insert('opening_cache',array('data'=>json_encode($openning_gl) ));
            $this->db->where(array('id'=>$openning_gl->id,'pay_type'=>$pay_type))->update('opening_gl',array('amount'=>$amount));
            $gl_trans_id = $openning_gl->gl_tran_id;
            $openning_id = $openning_gl->id;
        } else {
            $data['pay_type'] = $pay_type;
            $data['type'] = $pay_type;
            $this->db->insert('opening_gl',$data);
            $openning_id = $this->db->insert_id();
            $gl_trans_id = null;
        }

        $bank_trans = array(
            'type'=>ST_OPENING_BANK,
            'bank_act'=>$data['account'],
            'trans_date'=>$data['trans_date'],
            'amount'=>$data['amount']
        );
        if( $gl_trans_id ){

            $this->db->where('id',$openning_gl->gl_tran_id)->update('bank_trans',$bank_trans);
        } else {
            $bank_trans['trans_no'] = get_next_trans_no(ST_OPENING_BANK);
            $this->db->insert('bank_trans',$bank_trans);
            $gl_trans_id = $this->db->insert_id();
            $this->db->where(array('id'=>$openning_id,'pay_type'=>$pay_type))->update('opening_gl',array('gl_tran_id'=>$gl_trans_id));
        }

    }

    static function edit_button($item=NULL){
        $ci = get_instance();

        $html = '<a class="ajaxsubmit button" href="'.site_url($ci->uri->uri_string()).'/view?edit='.$item->id.'" >'.((isset($item->allocation) && abs($item->allocation) > 0) ? '<i class="fa fa-eye text-primary"></i>' : '<i class="fa fa-edit"></i>' ).'</a>';

        if( !isset($item->allocation) || $item->allocation==null || abs($item->allocation)==0 ){
            $html .= ' <a class="ajaxsubmit button" href="'.site_url($ci->uri->uri_string()).'?remove='.$item->id.'" ><i class="fa fa-remove text-danger"></i></a>';
        }

        return $html;
    }

    var $table_view = array(
        'trans_no'=>array('Trans#','left',5),
        'tran_date'=>array('Date','center',6,'date'),
        'type'=>array('Type','center',9,'trans_type'),
        'name'=>'Customer',
        'debit'=>array('Debit','textright',10,'number'),
        'credit'=>array('Credit','textright',10,'number'),
        'debit_base'=>array('Debit (Base)','textright',10,'number'),
        'credit_base'=>array('Credit (Base)','textright',10,'number'),
        'items_action'=>array(NULL,'text-center','gl_ob_edit','1')
    );

    private function listView($ob_type='sale'){
        $ci = get_instance();
        $data['page'] = $this->page;
        $data['items'] = array();
        $data['total'] = 0;
        $type = ST_OPENING_CUSTOMER;

        if( $ob_type=='customer' ){
            $openings = $this->model->openingCustomer($this->page);
        } else if ( $ob_type=='supplier') {
            $openings = $this->model->openingSupplier($this->page);
        }
        if( is_array($openings) ){
            $data['items'] = $openings['items'];
            $data['total'] = $openings['total'];
        }

        $data['type'] = $ob_type;
        box_start();
        table_view($this->table_view,$data,false,true);

        box_footer_start();
        echo '<a title="Add new item" class="ajaxsubmit btn green btn_right" href="'.site_url($ci->uri->uri_string()).'/add"><i class="fa fa-plus"></i> Add </a>';
        box_footer_end();

        box_end();
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

    function formView($type='customer',$id=0){
        global $Ajax,$ci;
        $data = $this->fields;

        $payment_from = array();
        if( $id ){
            $ob_type = ( $type=='customer' ) ? ST_OPENING_CUSTOMER : ST_OPENING_SUPPLIER;
            $opening_sale = $this->model->openingCustomerItem($id,$ob_type);
            foreach ($data AS $field=>$val){
                if( isset($opening_sale->$field) && isset($data[$field]) && is_array($data[$field]) && array_key_exists('value', $data[$field]) ){
                    $data[$field]['value'] = $opening_sale->$field;
                }
            }
            if( is_array($opening_sale->payment_from) && !empty($opening_sale->payment_from) ){
                $payment_from = $opening_sale->payment_from;
            }

        }

        $data['type'] = $type;

        if( !isset($data['tran_date']['value']) || !$data['tran_date']['value']  ){

            $data['tran_date']['value'] = $this->end_last_fiscalyear;
        } else {
            $data['tran_date']['value'] = qdate_format($data['tran_date']['value']);
        }
        $Ajax->activate('_page_body');

        $edit = true;
        if( isset($opening_sale) && isset($opening_sale->allocation) && abs($opening_sale->allocation) > 0 ){
            $error_msg = " <b>This Opening Balance invoice</b> has been allocated";
            if( is_array($payment_from) && !empty($payment_from) ){
                foreach ($payment_from AS $payment){
                    $error_msg .=" [".get_trans_view_str($payment->type, $payment->tran_no,"Payment ".$payment->tran_no)."]";
                }
            }
            display_error("$error_msg. Please remove the allocation to edit");
            $this->ci->smarty->assign('formViewOnly',1);
            $edit = false;
        }

        $this->ci->temp_view('customer_form',$data);
        hidden('type',$type);
        hidden('form_view',true);
         if( $edit ) {
            submit_center('submit', _("Save"), true, '', true);
        }




    }

    private function customer_submit(){
        $data = array();
        foreach ($this->fields AS $key=>$val){
            $data[$key] = $this->ci->input->post($key);
        }
        $type = $this->ci->input->post('type');
        if( $type=='customer' ){
            if( !$this->ci->input->post('branch') ) {
                display_error(_("Please input Branch"));
            }
            $data['type'] = ST_OPENING_CUSTOMER;
        } else {
            $data['type'] = ST_OPENING_SUPPLIER;
        }

        $data['trans_no'] = get_next_trans_no($data['type']);

        if( $data['id'] ) {
            $opening_sale = $this->ci->db->where('id',$data['id'])->get('opening_sale')->row();
            $data['trans_no'] = $opening_sale->trans_no;
        }

        if( !$data['tran_date'] ){
            $data['tran_date'] = $this->end_last_fiscalyear;
        } else {
            $data['tran_date'] = date2sql($data['tran_date']);
        }

        $opening_exist_ref = $this->ci->db->where(array('trans_no !='=>$data['trans_no'],'ref'=>$data['ref']))->get('opening_sale')->row();

        $success = false;
        if( !$data['ref'] ){
            display_error(_("Please input Reference#"));
//         } elseif ( $opening_exist_ref && isset($opening_exist_ref->id)){
//             display_error(_("Duplicate Reference#"));

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

            $this->write_opening_cus_sup($item_id,$type);
//             $action = true;
            $success = true;
            global $Ajax;
            $Ajax->redirect( site_url("maintenance/opening/$type") );
        }

        /*
         * add data into form if can't save data
         */
        if( !$success ){
            foreach ($this->fields AS $key=>$val){
                $this->fields[$key]['value'] = input_val($key);
            }
            $this->formView($type,$data['id']);
        }

    }

    private function write_opening_cus_sup($sale_id=0,$type='customer'){

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
            $gl_trans->set_value('tran_date',date2sql($this->end_last_fiscalyear));
        }

        $gl_trans->set_value('type_no',$opening_sale->trans_no);


        $debit = $opening_sale->debit*$opening_sale->curr_rate;
        $credit = $opening_sale->credit*$opening_sale->curr_rate;
//         bug($opening_sale); die('data opening   ');


        switch ($type){
            case 'customer':
                $customer_model = $this->ci->model('cutomer',true);

                $gl_trans->customer($opening_sale->customer,$opening_sale->branch);

                $this->packet_trans($opening_sale,$type);

                $gl_trans->set_value('person_type_id',ST_OPENING_CUSTOMER);



                $gl_trans->set_value('person_id',$customer_model->customer_detail($opening_sale->customer,'name'));
                $gl_trans->set_value('type',ST_OPENING_CUSTOMER);

                if( $debit > 0 ){
                    //$gl_trans->write_customer_trans(ST_SALESINVOICE,$debit,$opening_sale);


                    $gl_trans->add_trans($gl_trans->sales_account,			-$debit);
                    $gl_trans->add_trans($gl_trans->receivables_account,	$debit);
                    $gl_trans->add_trans($gl_trans->sales_account,			$debit);
                    $gl_trans->add_trans($gl_trans->receivables_account,	-$debit);
                }

                /* credit */
                if ( $credit > 0 ){


                    $currency = $config_model->get_sys_pref_val('curr_default');



                    $bank_account_default = $bank_model->bank_accounts_default($currency);
                    // 	                die("do write_opening bank_account_default=$bank_account_default");
                    $gl_trans->add_trans($gl_trans->receivables_account,	-$credit);
                    $gl_trans->add_trans($bank_account_default,				$credit);
                    $gl_trans->add_trans($gl_trans->receivables_account,	$credit);
                    $gl_trans->add_trans($bank_account_default,				-$credit);
                }

                break;
            case 'supplier':
                $supp_model = $this->ci->model('supplier',true);
                $this->packet_trans($opening_sale,$type);

                $gl_trans->set_value('person_type_id',PT_SUPPLIER);
                $gl_trans->set_value('person_id',$supp_model->supplier_detail($opening_sale->customer,'supp_name'));


                $Payable_Account = $config_model->get_sys_pref_val('creditors_act');
                $GRN_Clearing_Account = $config_model->get_sys_pref_val('grn_clearing_act');
                $Receivable_Account = $config_model->get_sys_pref_val('debtors_act');
                $gl_trans->set_value('type',ST_OPENING_SUPPLIER);
                if( $debit > 0 ){

                    $gl_trans->add_trans($Payable_Account,		-$debit);
                    $gl_trans->add_trans($GRN_Clearing_Account,	$debit);
                    $gl_trans->add_trans($Payable_Account,		$debit);
                    $gl_trans->add_trans($GRN_Clearing_Account,	-$debit);

                }

                if( $credit > 0 ){
                    $currency = $config_model->get_sys_pref_val('curr_default');
                    $bank_account_default = $bank_model->bank_accounts_default($currency);

                    $gl_trans->add_trans($bank_account_default,	-$credit);
                    $gl_trans->add_trans($Payable_Account,		$credit);
                    $gl_trans->add_trans($bank_account_default,	$credit);
                    $gl_trans->add_trans($Payable_Account,		-$credit);
                }
                break;
        }


        $gl_trans->insert_trans($type.'-'.$sale_id);

    }

    private function packet_trans($opening_sale,$type='customer'){

        if ( floatval($opening_sale->credit) > 0 ){
            $amount = -$opening_sale->credit;
        } else if ( floatval($opening_sale->debit) > 0 ){
            $amount = $opening_sale->debit;
        }


        $amount = $amount;

        $customer_trans = array(
            'trans_no'=>$opening_sale->trans_no,
            'tran_date'=>$opening_sale->tran_date,
            'due_date'=>$opening_sale->tran_date,
            'reference'=>$opening_sale->ref,
            'ov_amount'=>$amount,
            'tpe'=>'1',
            'rate'=>$opening_sale->curr_rate,

        );


        if( $type == 'customer' ){
            $customer_trans['debtor_no'] =$opening_sale->customer;
            $customer_trans['branch_code'] =$opening_sale->branch;
            $customer_trans['type'] =ST_OPENING_CUSTOMER;
            $db_table = 'debtor_trans';
        } else if ($type == 'supplier'){
            $customer_trans['ov_amount'] *= -1;
            $customer_trans['supplier_id'] =$opening_sale->customer;
            $customer_trans['type'] =ST_OPENING_SUPPLIER;
            $db_table = 'supp_trans';
        }

        $where_existed = array('trans_no'=>$customer_trans['trans_no'],'type'=>$customer_trans['type']);

        $existed = $this->ci->db->where($where_existed)->get($db_table)->row();


        if( $existed && !empty($existed) ){
            $this->ci->db->where($where_existed)->update($db_table,$customer_trans);
            display_notification(_('Update '.ucfirst($type)." Opening Balance success."), 1);
        } else {
            $this->ci->db->insert($db_table,$customer_trans);
            display_notification(_('Add new '.ucfirst($type)." Opening Balance success."), 1);
        }
//         bug(  $this->ci->db->last_query() );
//         bug($customer_trans);die;
        //         bug($this->ci->db->last_query()); die;
        //die('end add supplier trans');

    }

    private function item_remove($type='customer',$opening_id=0){

        $opening = $this->db->where('id',$opening_id)->get('opening_sale')->row();
        if( !empty($opening) ){
            if( $type =='customer' ){
                $this->ci->db->where(array('type'=>$opening->type,'trans_no'=>$opening->trans_no))->delete('debtor_trans');
            } elseif($type=='supplier') {
                $this->ci->db->where(array('type'=>$opening->type,'trans_no'=>$opening->trans_no))->delete('supp_trans');
            }

            $this->ci->db->where('opening',$type.'-'.$opening_id)->delete('gl_trans');
            $this->ci->db->where('id',$opening_id)->delete('opening_sale');
        }
        global $Ajax;
        return $Ajax->redirect(site_url($this->ci->uri->uri_string));


    }
}