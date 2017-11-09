<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class OpeningOb
{
    function __construct()
    {
        $this->db = get_instance()->db;
        $this->model_sys = get_instance()->model('config',true);

        $this->model = module_model_load('trans');
        $this->allocation_model = module_model_load('allocation', 'gl');
    }

    /*
     * Edit
     */
    function edit(){
        $id = 0;
        if ( count($_POST)> 0 )  {
            foreach ($this->fields as $key => $val) {
                $this->fields[$key]['value'] = input_val($key);
            }
            if ($this->fields['currency']['value']) {
                $this->fields['curr_rate']['value'] = $this->model_sys->exchange_rate_get($this->fields['currency']['value'], $this->fields['tran_date']['value']);
            }
        } else {
            $id = input_get('edit');
        }
        $this->form($id);
    }

    var $fields = array(
        'id' => array(
            'value' => 0
        ),
        'customer' => array(
            'value' => null
        ),
        'branch' => array(
            'value' => null
        ),
        'currency' => array(
            'value' => null
        ),
        'curr_rate' => array(
            'value' => 1
        ),
        'debit' => array(
            'value' => null
        ),
        'credit' => array(
            'value' => null
        ),

        // 'trans_no'=>array('value'=>null),
        'tran_date' => array(
            'value' => ''
        ),
        'ref' => array(
            'value' => null
        )
    );

    var $type = 'customer';
    function form($id = 0)
    {
        global $Ajax, $ci;


        $data = $this->fields;

        $payment_from = array();
        if ($id) {
            $ob_type = ($this->type == 'customer') ? ST_OPENING_CUSTOMER : ST_OPENING_SUPPLIER;
            $opening_sale = $this->model->openingCustomerItem($id, $ob_type);
            foreach ($data as $field => $val) {
                if (isset($opening_sale->$field) && isset($data[$field]) && is_array($data[$field]) && array_key_exists('value', $data[$field])) {
                    $data[$field]['value'] = $opening_sale->$field;
                }
            }
            if (is_array($opening_sale->payment_from) && ! empty($opening_sale->payment_from)) {
                $payment_from = $opening_sale->payment_from;
            }
        }

        $data['type'] = $this->type;

        if (! isset($data['tran_date']['value']) || ! $data['tran_date']['value']) {
            $date = new DateTime(begin_fiscalyear());
            $date->modify('-1 day');
            $data['tran_date']['value'] = $date->format(get_instance()->dateformatPHP);
        } else {
            $data['tran_date']['value'] = qdate_format($data['tran_date']['value']);
        }
        $Ajax->activate('_page_body');

        $edit = true;
        if (isset($opening_sale) && isset($opening_sale->allocation) && abs($opening_sale->allocation) > 0) {
            $error_msg = " <b>This Opening Balance invoice</b> has been allocated";
            if (is_array($payment_from) && ! empty($payment_from)) {
                foreach ($payment_from as $payment) {
                    $error_msg .= " [" . get_trans_view_str($payment->type, $payment->tran_no, "Payment " . $payment->tran_no) . "]";
                }
            }
            display_error("$error_msg. Please remove the allocation to edit");
//             get_instance()->smarty->assign('formViewOnly', 1);
            $edit = false;
        }

        get_instance()->temp_view('customer_form', $data);
        box_footer_start();
        hidden('type', $this->type);
        hidden('form_view', true);
        if ($edit) {
            submit('submit', _("Save"), true, '', true, 'save');
//             submit('submit', _("Save"), true, '', false, 'save');
        }
        box_footer_end();
    }

    /*
     * remove item
     */

    function remove_cus_sup($opening_id = 0)
    {
        $opening = $this->db->where('id', $opening_id)
        ->get('opening_sale')
        ->row();
        if (! empty($opening)) {
            if ($this->type == 'customer') {
                $this->db->where(array(
                    'type' => $opening->type,
                    'trans_no' => $opening->trans_no
                ))->delete('debtor_trans');
            } elseif ($this->type == 'supplier') {
                $this->db->where(array(
                    'type' => $opening->type,
                    'trans_no' => $opening->trans_no
                ))->delete('supp_trans');
            }

            $this->db->where('opening', $this->type . '-' . $opening_id)->delete('gl_trans');
            $this->db->where('id', $opening_id)->delete('opening_sale');
        }
        global $Ajax;
        return $Ajax->redirect(site_url(get_instance()->uri->uri_string));
    }

    /*
     * Submit item
     */
    function customer_submit(){
        $data = array();
        foreach ($this->fields AS $key=>$val){
            $data[$key] = input_post($key);
        }
        $type = input_post('type');

        if( $type=='customer' ){
            if( !input_post('branch') ) {
                display_error(_("Please input Branch"));
            }
            $data['type'] = ST_OPENING_CUSTOMER;
        } else {
            $data['type'] = ST_OPENING_SUPPLIER;
        }

        $data['trans_no'] = get_next_trans_no($data['type']);

        if( $data['id'] ) {
            $opening_sale = $this->db->where('id',$data['id'])->get('opening_sale')->row();
            $data['trans_no'] = $opening_sale->trans_no;
        }

        if( !$data['tran_date'] ){
            $data['tran_date'] = $this->end_last_fiscalyear;
        } else {
            $data['tran_date'] = date2sql($data['tran_date']);
        }

        $opening_exist_ref = $this->db->where(array('trans_no !='=>$data['trans_no'],'ref'=>$data['ref']))->get('opening_sale')->row();

        $success = false;
        if( !$data['ref'] ){
            display_error(_("Please input Reference#"));
            //         } elseif ( $opening_exist_ref && isset($opening_exist_ref->id)){
            //             display_error(_("Duplicate Reference#"));

        } elseif (!$data['debit'] && !$data['credit'] ){
            display_notification_centered(sprintf( _("Please input Debit or Credit"),'0'));
        } else {

            if( is_null($data['credit']) ){
                $data['credit'] = 0;
            }
            if( is_null($data['debit']) ){
                $data['debit'] = 0;
            }
            if( isset($data['id']) && $data['id'] > 0 ){
                $this->db->where('id',$data['id'])->update('opening_sale',$data);
                $item_id = $data['id'];
            } else {
                $this->db->insert('opening_sale',$data);
                $item_id = $this->db->insert_id();
            }

            $this->write_opening_cus_sup($item_id,$type);
            //             $action = true;
            $success = true;
            global $Ajax;
            $Ajax->redirect( site_url("opening/$type") );
        }

        /*
         * add data into form if can't save data
         */
        if( !$success ){
            foreach ($this->fields AS $key=>$val){
                $this->fields[$key]['value'] = input_val($key);
            }
            $this->form($data['id']);
        } else {
            redirect("opening/$type");
        }

    }

    private function write_opening_cus_sup($sale_id=0,$type='customer'){

        $config_model = get_instance()->model('config',true);
        $bank_model = get_instance()->model('bank',true);
        $gl_trans_model = get_instance()->model('gl_trans',true);

        $gl_trans = get_instance()->gl_trans;
        if( !is_object($gl_trans) )
            return;

        if( !$sale_id ){
            return;
        }

        $opening_sale = $this->db->where('id',$sale_id)->get('opening_sale')->row();

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
                $customer_model = get_instance()->model('cutomer',true);

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
                $supp_model = get_instance()->model('supplier',true);
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

        $existed = $this->db->where($where_existed)->get($db_table)->row();


        if( $existed && !empty($existed) ){
            $this->db->where($where_existed)->update($db_table,$customer_trans);
            display_notification(_('Update '.ucfirst($type)." Opening Balance success."), 1);
        } else {
            $this->db->insert($db_table,$customer_trans);
            display_notification(_('Add new '.ucfirst($type)." Opening Balance success."), 1);
        }
        //         bug(  $this->db->last_query() );
        //         bug($customer_trans);die;
        //         bug($this->db->last_query()); die;
        //die('end add supplier trans');

    }


    /*
     * Table View
     */
    var $table_view = array(
        'trans_no' => array(
            'label' => 'Trans#'
        ),
        'tran_date' => array(
            'label' => 'Date',
            'type' => 'date',
            'class' => 'text-center',
            'align' => 'center'
        ),
        'type' => array(
            'label' => 'Type',
            'fun' => 'systype_name',
            'name' => 'type'
        ),
        'name' => 'Customer',
        'debit' => array(
            'label' => 'Debit',
            'type' => 'amount'
        ),
        'credit' => array(
            'label' => 'Credit',
            'type' => 'amount'
        ),
        'debit_base' => array(
            'label' => 'Debit (Base)',
            'type' => 'amount'
        ),
        'credit_base' => array(
            'label' => 'Credit (Base)',
            'type' => 'amount'
        ),
        'items_action' => array(
            'label' => '',
            'fun' => 'row_actions',
            'class' => 'text-center',
            'align' => 'center'
        )
    );

    function items()
    {
        if( $this->type=='customer' ){
            $sql = $this->model->opening_customer();
        } elseif ( $this->type=='supplier' ){
            $sql = $this->model->opening_supplier();
        }


        $table = & new_db_pager('customer_ob_tbl', $sql, $this->table_view, $table = null, $key = null, $page_len = 10);
        $table->ci_control = $this;

        db_table_responsive($table);
        // display_db_pager($table);

        box_footer_start();
        echo '<a title="Add new item" class="ajaxsubmit btn green btn_right" href="' . site_url('opening/'.$this->type.'/add') . '"><i class="fa fa-plus"></i> Add </a>';
        box_footer_end();
    }

    function row_actions($row, $cell = NULL)
    {
        $ci = get_instance();
        $allocation = 0;
        $allo_sql = $this->allocation_model->str_for_invoice($row['trans_no'], $row['type']);
        $allo = $this->db->query($allo_sql)->row();
        if (is_object($allo) and isset($allo->allo_sum)) {
            $allocation = floatval($allo->allo_sum);
        }

        $html = '<a class="ajaxsubmit button" href="' . site_url($ci->uri->uri_string()) . '/view?edit=' . $row['id'] . '" >' . ((abs($allocation) > 0) ? '<i class="fa fa-eye text-primary"></i>' : '<i class="fa fa-edit"></i>') . '</a>';

        if (abs($allocation) == 0) {
            $html .= ' <a class="ajaxsubmit button" href="' . site_url($ci->uri->uri_string()) . '?remove=' . $row['id'] . '" ><i class="fa fa-remove text-danger"></i></a>';
        }

        return $html;
    }
}