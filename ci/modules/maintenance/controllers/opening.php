<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MaintenanceOpening {
    function __construct() {
        global $ci, $Ajax;
        $this->ci = $ci;
        $this->ajax = $Ajax;
        $this->page_security = 'SA_SETUPCOMPANY';
        $path_to_root = '.';
        include_once(ROOT. "/includes/ui.inc");

        $this->db = $ci->db;
        $this->model_sys = $ci->model('config',true);
        $this->page = $this->ci->input->get('p');

        $date = new DateTime(begin_fiscalyear());
        $date->modify('-1 day');
        $this->end_last_fiscalyear = $date->format($this->ci->dateformatPHP);

        if( !$this->page ){
            $this->page = 1;
        }


    }

    function index(){
        die('go to index opening balance');
    }

    function bank(){
        page( "Bank Account Opening Balance");


        $date = new DateTime(begin_fiscalyear());
        $date->modify('-1 day');
        $fields = array(
            'trans_date'=>array('input'=>'date','title'=>_('Date'),'value'=>$date->format('d-m-Y')),
            'bank_act'=>array('input'=>'bank_accounts','title'=>_('Bank Account'),'onchange_ajax'=>true,'value'=>null),
            'amount'=>array('input'=>'number','title'=>_('Amount'))
        );

    }

    function customer(){
        add_js_file('/js/opening.js');
        page( 'Customers Opening Balance');
        start_form();

        if( $this->ci->input->post('submit') ){
            $this->customer_submit();
        } else if ( $this->ci->input->post('_remove') ) {
            $this->item_remove('customer',$this->ci->input->post('_remove'));
        } else  if( $this->ci->input->get('edit') OR $this->ci->input->get('new') OR $this->ci->input->post() ){
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

            $this->formView('customer',$id);
        } else {
            $this->listView('customer');
        }
        end_form();

        end_page();

    }

    function supplier(){
        add_js_file('/js/opening.js');
        page( 'Supplier Opening Balance');
        start_form();
        if( $this->ci->input->post('submit') ){
            return $this->customer_submit();
        } elseif ( $this->ci->input->post('_remove') ) {
            $this->item_remove('customer',$this->ci->input->post('_remove'));
        } elseif( $this->ci->input->get('edit') OR $this->ci->input->get('new') OR $this->ci->input->post() ){
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
            $this->formView('supplier',$id);
        } else {
            $this->listView('supplier');
        }

        end_form();
        end_page();

    }

    private function listView($ob_type='sale'){
        $data['page'] = $this->page;

        if( $ob_type=='customer' ){
            $type = ST_OPENING_CUSTOMER;
            $this->db->select('sale.*, deb.name')->where(array('sale.type'=>$type))->join('debtors_master AS deb', 'deb.debtor_no=sale.customer', 'left');
            $this->db->join('cust_allocations AS allo','allo.trans_no_to=sale.trans_no AND allo.trans_type_to='.$type,'left')->select('allo.amt AS allocation');

            $data['items'] = $this->db->limit(page_padding_limit,($this->page-1)*page_padding_limit)->get('opening_sale AS sale')->result();
        } else if ( $ob_type=='supplier') {
            $type = ST_OPENING_SUPPLIER;

            $this->db->select('sale.*, sup.supp_name AS name')->where('type',$type)->join('suppliers AS sup', 'sup.supplier_id=sale.customer', 'left');
            $this->db->where('sup.supp_name !=','');

            $this->db->join('supp_allocations AS allo','allo.trans_no_to=sale.trans_no AND allo.trans_type_to='.$type,'left')->select('allo.amt AS allocation');

            $data['items'] = $this->db->limit(page_padding_limit,($this->page-1)*page_padding_limit)->get('opening_sale AS sale')->result();
        }


        $where_type = array($type);

        $data['total'] = $this->db->select('COUNT(s.id) AS total, SUM(s.debit*curr_rate) AS debit, SUM(s.credit*curr_rate) AS credit')->where_in('type',$where_type)->get('opening_sale AS s')->row();


        if( $data['lastpage']* $limit < $data['total']->total ){
            $data['lastpage'] ++;
        }
        $data['type'] = $ob_type;


// bug($data);die;
        $this->ci->temp_view('table_items',$data);
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
        if( $id ){
            $this->db->select('ob.*');
            if( $type=='customer' ){
                $this->db->join('cust_allocations AS allo','allo.trans_no_to=ob.trans_no AND allo.trans_type_to='.ST_OPENING_CUSTOMER,'left')->select('allo.amt AS allocation');
            } elseif ($type=='supplier') {
                $this->db->join('supp_allocations AS allo','allo.trans_no_to= ob.trans_no AND allo.trans_type_to='.ST_OPENING_SUPPLIER,'left')->select('allo.amt AS allocation');
            }

            $opening_sale = $this->db->where('ob.id',$id)->get('opening_sale AS ob')->row();

            foreach ($data AS $field=>$val){
                if( isset($opening_sale->$field) && isset($data[$field]) && is_array($data[$field]) && array_key_exists('value', $data[$field]) ){
                    $data[$field]['value'] = $opening_sale->$field;
                }
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
        if( isset($opening_sale) && isset($opening_sale->allocation) &&abs($opening_sale->allocation) > 0 ){
            display_error("<b>This Opening Balance invoice</b> has been allocated. Please remove the allocation to edit");
            $this->ci->smarty->assign('formViewOnly',1);
            $edit = false;
        }

        $this->ci->temp_view('customer_form',$data);
        hidden('type',$type);

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
//             $trans_type = ST_SALESINVOICE;
        } else {
            $data['type'] = ST_OPENING_SUPPLIER;
//             $trans_type = ST_SUPPINVOICE;
        }
        //$type = $this->ci->input->post('type');

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

        if( !$data['ref'] ){
            display_error(_("Please input Reference#"));
        } elseif ( $opening_exist_ref && isset($opening_exist_ref->id)){
            display_error(_("Duplicate Reference#"));
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
// die('end write');
            $action = true;

            $this->ajax->redirect( site_url($this->ci->uri->uri_string) );
        }
//         die('end function submit openning');

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

        if ( $opening_sale->credit ){
            $amount = -$opening_sale->credit;
        } else if ($opening_sale->debit){
            $amount = $opening_sale->debit;
        }

        $amount = $amount*$opening_sale->curr_rate;

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
            $customer_trans['supplier_id'] =$opening_sale->customer;
            $customer_trans['type'] =ST_OPENING_SUPPLIER;
//             $customer_trans['ov_amount'] = $customer_trans['ov_amount']*-1;

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

    private function item_remove($type='customer',$opening_id=0){

        $opening = $this->db->where('id',$opening_id)->get('opening_sale')->row();
//         display_error(_("opening_id = $opening_id"));
//         bug($this->db->last_query());
//         bug($opening);
//         die('hree');
//         display_error(_("remove id= $opening_id"));
        if( !empty($opening) ){
            if( $type =='customer' ){
                $this->ci->db->where(array('type'=>$opening->type,'trans_no'=>$opening->trans_no))->delete('debtor_trans');
            } elseif($type=='supplier') {
                $this->ci->db->where(array('type'=>$opening->type,'trans_no'=>$opening->trans_no))->delete('supp_trans');


            }

            $this->ci->db->where('opening',$type.'-'.$opening_id)->delete('gl_trans');
            $this->ci->db->where('id',$opening_id)->delete('opening_sale');
        }
        $this->ajax->redirect(site_url($this->ci->uri->uri_string));

    }
}