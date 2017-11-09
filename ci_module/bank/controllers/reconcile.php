<?php

class BankReconcile
{

    function __construct ()
    {
        
        // $this->dim = get_company_pref('use_dimension');
        $this->check_submit();
        $this->document_add();
    }

    function form ()
    {
        $this->check_value_default();
        
        // ------------------------------------------------------------------------------------------------
        start_form();
        
        box_start();
        row_start('justify-content-md-center');
        col_start(12, 'col-md-6');
        bank_accounts(_("Account"), 'bank_account', null, true);
        col_start(12, 'col-md-4');
        if (! isMobile()) {
            bootstrap_set_label_column(4);
        }
        
        bank_reconciliations(_("Bank Statement"), get_post('bank_account'), 
                'bank_date', null, true, _("New"));
        row_end();
        
        $this->bank = get_bank_account($_POST["bank_account"]);
        
        row_start();
        col_start(12);
        $this->form_header();
        col_end();
        row_end();
        
        box_start();
        
        $this->form_detail(
                $this->bank['bank_account_name'] . " - " .
                         $this->bank['bank_curr_code']);
        
        box_footer_start();
        
        if (intval($document_id = input_get('document')) > 0) {
            hidden('document_id', $document_id);
        }
        
        submit('Reconcile', _("Reconcile"), true, '', 'default');
        box_footer_end();
        box_end();
        
        end_form();
    }

    function form_header ()
    {
        $result = get_max_reconciled(get_post('reconcile_date'), 
                $_POST['bank_account']);
        
        if ($row = db_fetch($result)) {
            
            $_POST["reconciled"] = price_format(
                    $row["end_balance"] - $row["beg_balance"]);
            $total = $row["total"];
            if (! isset($_POST["beg_balance"])) { // new selected
                                                  // account/statement
                $_POST["last_date"] = sql2date($row["last_date"]);
                $_POST["beg_balance"] = price_format($row["beg_balance"]);
                $_POST["end_balance"] = price_format($row["end_balance"]);
                if (get_post('bank_date')) {
                    // if it is the last updated bank statement retrieve ending
                    // balance
                    
                    $row = get_ending_reconciled($_POST['bank_account'], 
                            $_POST['bank_date']);
                    
                    if ($row) {
                        $_POST["end_balance"] = price_format(
                                $row["ending_reconcile_balance"]);
                    }
                }
            }
        }
        echo "<hr>";
        
        div_start('summary');
        
        start_table(TABLESTYLE);
        $th = array(
                'date' => array(
                        'label' => _("Reconcile Date"),
                        'width' => '13%'
                ),
                'begin' => array(
                        'label' => _("Beginning Balance"),
                        'width' => '20%'
                ),
                'end' => array(
                        'label' => _("Ending Balance"),
                        'width' => '20%'
                ),
                _("Account Total"),
                _("Reconciled Amount"),
                _("Difference")
        );
        table_header($th);
        start_row();
        
        date_cells("", "reconcile_date", 
                _('Date of bank statement to reconcile'), 
                get_post('bank_date') == '', 0, 0, 0, null, true);
        
        input_money_cells('beg_balance');
        input_money_cells("end_balance");
        
        $reconciled = input_num('reconciled');
        $difference = input_num("end_balance") - input_num("beg_balance") -
                 $reconciled;
        
        amount_cell($total);
        amount_cell($reconciled, false, '', "reconciled");
        amount_cell($difference, false, '', "difference");
        
        end_row();
        end_table();
        div_end();
    }

    private function form_detail ()
    {
        $sql = get_sql_for_bank_account_reconcile($_POST['bank_account'], 
                get_post('reconcile_date'));
        
        $cols = array(
                _("Type") => array(
                        'fun' => 'systype_name',
                        'ord' => ''
                ),
                _("#") => array(
                        'fun' => 'trans_view',
                        'ord' => ''
                ),
                _("Reference"),
                'date' => array(
                        'label' => _("Date"),
                        'type' => 'date',
                        'width' => '5%',
                        'align' => 'center',
                        'class' => 'text-center'
                ),
                
                _("Debit") => array(
                        'align' => 'right',
                        'fun' => 'fmt_debit'
                ),
                _("Credit") => array(
                        'align' => 'right',
                        'insert' => true,
                        'fun' => 'fmt_credit'
                ),
                _("Person/Item") => array(
                        'fun' => 'fmt_person'
                ),
                _("Cheque No."),
                'gl' => array(
                        'label' => "GL",
                        'insert' => true,
                        'fun' => 'gl_view',
                        'align' => 'center'
                ),
                "X" => array(
                        'label'=>'Check',
                'insert' => true,
                'fun' => 'rec_checkbox',
                'class'=>'text-center',
                'align' => 'center',
            ),
            'print'=>array(
                'label'=>'PRT',
                'insert' => true,
                'fun' => 'prt_link',
                'align' => 'center',
                'class'=>'text-center'
            )
        );
        $table = & new_db_pager('trans_tbl', $sql, $cols);

        $table->ci_control = $this;
        display_db_pager($table);
    }

    function fmt_debit($row)
    {
    	$value = $row["amount"];
    	return $value>=0 ? price_format($value) : '';
    }

    function fmt_credit($row)
    {
    	$value = -$row["amount"];
    	return $value>0 ? price_format($value) : '';
    }

    function prt_link($row)
    {
        if (in_array($row['type'], array(
            ST_CUSTPAYMENT,
            ST_SUPPAYMENT,
            ST_BANKPAYMENT,
            ST_BANKDEPOSIT
        )))
            return print_document_link($row['trans_no'] . "-" . $row['type'], _("Print Receipt"), true, $row['type'], ICON_PRINT);
    }

    function check_value_default()
    {
        if (! isset($_POST['reconcile_date'])) { // init page
            $_POST['reconcile_date'] = new_doc_date();
            // $_POST['bank_date'] = date2sql(Today());
        }

        if (! isset($_POST['bank_account']))
            $_POST['bank_account'] = "";
    }

    function check_submit()
    {
        global $Ajax;

        if (list_updated('bank_account')) {
            $Ajax->activate('bank_date');
            update_data();
        }

        if (list_updated('bank_date')) {
            $_POST['reconcile_date'] = get_post('bank_date') == '' ? Today() : sql2date($_POST['bank_date']);
            update_data();
        }
        if (get_post('_reconcile_date_changed')) {
            $_POST['bank_date'] = check_date() ? date2sql(get_post('reconcile_date')) : '';
            $Ajax->activate('bank_date');
            update_data();
        }

        $id = find_submit('_rec_');
        if ($id != - 1)
            $this->change_tpl_flag($id);

        if (isset($_POST['Reconcile'])) {
            set_focus('bank_date');

            foreach($_POST['last'] as $id => $value){
                if ($value != check_value('rec_'.$id))
                    if(!$this->change_tpl_flag($id)) break;
            }
            
            display_notification(_("Reconcile processed"));
            $Ajax->activate('_page_body');
        }
    }

    private function document_add(){
        $document_id = input_get('document');
        if( is_numeric($document_id) AND !in_ajax() ){
            $mobile_model = module_model_load('mobile','documents');
            $row = $mobile_model->item($document_id);
            $data = unserialize($row->data);
            if( !empty($data) ){
                $_POST['beg_balance'] = $data['bank_open_balance'];
                $_POST['end_balance'] = $data['bank_closing_balance'];

                if( isset($data['bank_account']) ){
                    $bank = get_instance()->db->where('account_code',$data['bank_account'])->get('bank_accounts')->row();
                    if( is_object($bank) AND isset($bank->id) ){
                        $_POST['bank_account'] = $bank->id;
                    }
                }
                $data['bank_date'] = trim($data['bank_date']);
                $tran_date = date("Y-m-d",strtotime($data['bank_date']));;
                if( is_date($tran_date) ){
                    $_POST['reconcile_date'] = sql2date($tran_date);
                }
            }
        }
    }
    
    //---------------------------------------------------------------------------------------------
    // Update db record if respective checkbox value has changed.
    //
    private function change_tpl_flag($reconcile_id)
    {
        global	$Ajax;
    
        if (!check_date()
                && check_value("rec_".$reconcile_id)) // temporary fix
            return false;
    
            if (get_post('bank_date')=='')	// new reconciliation
                $Ajax->activate('bank_date');
    
                $_POST['bank_date'] = date2sql(get_post('reconcile_date'));
                $reconcile_value = check_value("rec_".$reconcile_id)
                ? ("'".$_POST['bank_date'] ."'") : 'NULL';
    
                update_reconciled_values($reconcile_id, $reconcile_value, $_POST['reconcile_date'], input_num('end_balance'), $_POST['bank_account']);
    
                $Ajax->activate('reconciled');
                $Ajax->activate('difference');
                return true;
    }
}