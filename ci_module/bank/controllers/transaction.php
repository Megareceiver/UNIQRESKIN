<?php

class BankTransaction
{
    var $dim = 0;
    var $tran_cart=NULL;

    function __construct()
    {
        $this->dim = get_company_pref('use_dimension');
        $this->check_submit();
        $this->document_add();
    }

    function index(){
        $this->tran_cart = $_SESSION['pay_items'];

        start_form();
        box_start();
        $this->bank_tran_header();

        if( $this->tran_cart->trans_type == ST_BANKPAYMENT ){
            box_start('Payment Items');
        }elseif( $this->tran_cart->trans_type == ST_BANKDEPOSIT ){
                box_start('Deposit Items');
        } else {
            box_start();
        }


        if ($this->tran_cart->trans_type == ST_BANKPAYMENT && config_ci('kastam') ) {
            $title = "";
            echo '<center><a title="Add new item to document" class="inputsubmit leftbutton" data-toggle="modal" data-target="#bankpayment_simplified_invoice" >SIMPLIFIED INVOICE</a> <span class="headingtext">' . $title . '</span></center>';
        } else {
            //             display_heading($title);
        }

        //$this->gl_items($_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ? _("Payment Items"):_("Deposit Items"), $_SESSION['pay_items']);
        $this->gl_items();

        box_footer_start();

        if( intval( $document_id = input_get('document')) > 0 ){
            hidden('document_id',$document_id);
        }

        $voided = false;
        if( $_SESSION['pay_items']->order_id ){
            $voided = is_voided_display($_SESSION['pay_items']->trans_type, $_SESSION['pay_items']->order_id, _("This payment note has been voided."));
        }
        if( !$voided ){

            submit('Update', _("Update"),true, '', true,'save');
            submit('Process', $_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ?_("Process Payment"):_("Process Deposit"), true,'',true);
//             submit('Process', $_SESSION['pay_items']->trans_type==ST_BANKPAYMENT ?_("Process Payment"):_("Process Deposit"), true,'',false);
        }
        box_footer_end();


        box_end();
        end_form();
    }

    function bank_tran_header()
    {
        global $Ajax, $Refs, $ci;


        $this->set_default_value();

        $payment = $this->tran_cart->trans_type == ST_BANKPAYMENT;

        $this->tran_cart->tax_inclusive = input_post('tax_inclusive');
        $this->tran_cart->cheque = input_post('cheque');
        $customer_error = false;

        div_start('pmt_header');
        row_start();
        bootstrap_set_label_column(4);

        col_start(4);
        input_date_bootstrap(_("Date"), 'date_', null, false, true, 0, 0, 0);
        input_ref("Reference", 'ref');

        input_switch('Tax Inclusive', 'tax_inclusive', $this->tran_cart->tax_inclusive, null, true, $columns = 4, NULL,$data_size='small');
        input_text(_('Cheque Number'), 'cheque');

        col_start(4);

        payment_person_types($payment ? _("Pay To:") : _("From:"), 'PayType', $_POST['PayType'], true);

        switch ($_POST['PayType']) {
            case PT_MISC:
                input_text($payment ? _("To the Order of:") : _("Name:"), 'person_id');
                break;
            // case PT_WORKORDER :
            // workorders_list_row(_("Work Order:"), 'person_id', null);
            // break;
            case PT_SUPPLIER:

                // supplier_list_row(_("Supplier:"), 'person_id', null, false, true, false, true);
                supplier_list_bootstrap('Supplier', 'person_id', null, false, true, false, true);
                break;
            case PT_CUSTOMER:
                input_customers(_("Customer:"), 'person_id', null, false, true, false, true);

                if (db_customer_has_branches($_POST['person_id'])) {
                    input_customer_branches(_("Branch:"), $_POST['person_id'], 'PersonDetailID', null, false, true, true, true);
                } else {
                    $_POST['PersonDetailID'] = ANY_NUMERIC;
                    hidden('PersonDetailID');
                }
                $trans = get_customer_habit($_POST['person_id']); // take care of customers on hold
                if ($trans['dissallow_invoices'] != 0) {
                    if ($payment) {
                        $customer_error = true;
                        display_error(_("This customer account is on hold."));
                    } else
                        display_warning(_("This customer account is on hold."));
                }
                break;

            case PT_QUICKENTRY:

                input_quick_entries(_("Type"), 'person_id', null, ($payment ? QE_PAYMENT : QE_DEPOSIT), true);
                $qid = get_quick_entry(get_post('person_id'));
                if (list_updated('person_id')) {
                    unset($_POST['totamount']); // enable default
                    $Ajax->activate('footer');
                    $Ajax->activate('totamount');
                }
                if ( !empty($qid) ){
                    input_money($qid['base_desc'], 'totamount', price_format($qid['base_amount']), null, "&nbsp;&nbsp;" . submit('go', _("Go"), false, false, true));
                }
                
                break;
            // case payment_person_types::Project() :
            // dimensions_list_row(_("Dimension:"), 'person_id', $_POST['person_id'], false, null, true);
            // break;
        }
        if (! isset($this->tran_cart->goods_invoice)) {
            $this->tran_cart->goods_invoice = false;
        }
        if( config_ci('kastam')){
            echo $ci->finput->supplier_invoice_goods(_('Load Import'), 'goods_invoice', $this->tran_cart->goods_invoice, 'row');
        } else {
            hidden('goods_invoice');
        }

        if( config_ci('kastam')){
            input_text('Source Ref', 'source_ref');
        }

        col_start(4);

        bank_accounts($payment ? _("From:") : _("Into:"), 'bank_account', null, true);

        if ($payment)
            bank_balance_label($_POST['bank_account']);

        $bank_currency = get_bank_account_currency($_POST['bank_account']);

        exchange_rate_display(get_company_currency(), $bank_currency, input_post('date_'));

        // end_outer_table(1); // outer table

        row_end();
        div_end();

        if ($customer_error) {
            end_form();
            end_page();
            exit();
        }
    }

    private function gl_items_table_header()
    {
        $th = array(
            'code'=>array('label'=>_("Account Code"),'width'=>'15%'),
            'acc_desc'=>array('label'=>_("Account Description"),'width'=>'20%'),

        );
        switch ($this->dim){
            case 2:
                $th += array(
                    _("Dimension") . " 1",
                    _("Dimension") . " 2",
                    _("Amount"),
                );
                break;
            case 1:
                $th += array(
                    _("Dimension"),
                    _("Amount"),
                );
                break;
            default:
                $th += array(
                    _("Amount"),
                    'gst'=>array('label'=>_('GST'),'width'=>'10%'),
                );
                break;
        }
        $th[] = _("Memo");

        if (isset($this->tran_cart->invoice_import) && ! empty($this->tran_cart->invoice_import)) {
            $th[] = 'Import Declaration Number';

        }
        $th["edit"] = array(
            'label' => "Edit",
            'width' => '5%'
        );
        $th['delete'] = array(
            'label' => "Del",
            'width' => '5%'
        );

        return $th;
    }

    function gl_items()
    {
        $th = $this->gl_items_table_header();

        div_start('items_table',$trigger = null, $non_ajax = false, 'class="pb-3"');
        start_table(TABLESTYLE);

        table_header($th);
        $k = 0; // row colour counter

        $id = find_submit('Edit');
        $tax_total = 0;

        foreach ($this->tran_cart->gl_items as $line => $item) {

            if ($id != $line) {
                start_row();

                label_cell($item->code_id);
                $gl_acc = get_gl_account($item->code_id);
                label_cell(isset($gl_acc['account_name']) ? $gl_acc['account_name'] : $item->description);

                if ($this->dim >= 1)
                    label_cell(get_dimension_string($item->dimension_id, true));
                if ($this->dim > 1)
                    label_cell(get_dimension_string($item->dimension2_id, true));

                if ($this->tran_cart->trans_type == ST_BANKDEPOSIT)
                    amount_cell(- $item->amount);
                else
                    amount_cell($item->amount);

                $tax_title = '';
                if ($item->gst) {
                    $tax_detail = get_gst($item->gst);
                    if( is_object($tax_detail) ){
                        $tax_title = $tax_detail->no . " (" . $tax_detail->rate . "%)";

                        if ($this->tran_cart->tax_inclusive) {
                            $tax_total += $tax_detail->rate / (100 + $tax_detail->rate) * $item->amount;
                        } else {
                            $tax_total += $item->amount * $tax_detail->rate / 100;
                        }
                    }

                }
                label_cell($tax_title);
                label_cell($item->description);
                if (isset($this->tran_cart->invoice_import) && ! empty($this->tran_cart->invoice_import)) {
                    text_cells(null, 'import_declaration_number');
                }
                tbl_edit("Edit$line");
                tbl_remove("Delete$line");
//                 edit_button_cell("Edit$line", _("Edit"), _('Edit document line'));
//                 delete_button_cell("Delete$line", _("Delete"), _('Remove line from document'));
                end_row();
            } else {
                // gl_edit_item_controls($this->tran_cart, $dim, $line);
                $this->gl_item_edit($line);
            }
        }

        if ($id == - 1) {
            $this->gl_item_edit();
        }

        if ($this->tran_cart->count_gl_items()) {
            $colspan = ($this->dim == 2 ? 4 : ($this->dim == 1 ? 3 : 2));
            echo "<tr>";

            $total_price = number_format2(abs($this->tran_cart->gl_items_total()), user_price_dec());
            label_cells(_("Sum"), $total_price, "colspan=" . $colspan . " align=right", "align=right", 4);

            echo "<td align=right >" . number_format2(abs($tax_total), user_price_dec()) . "</td>";
            echo "<td colspan=3></td>";
            echo "</tr>";

            $total = ($this->tran_cart->gl_items_total());

            if ($this->tran_cart->tax_inclusive != 1) {
                $total += $tax_total;
            }

            echo '<tr><td align="right" colspan="' . $colspan . '">Total </td><td align="right" colspan="2">' . number_format2(abs($total), user_price_dec()) . '</td><td align="right" colspan="3"></td></tr>';
       }

        end_table();
        div_end();

        $this->gl_options();
    }

    private function gl_item_edit($Index = null)
    {
        global $Ajax, $ci;

        start_row();
        $id = find_submit('Edit');
        if ($Index != - 1 && $Index == $id) {
            $item = $this->tran_cart->gl_items[$Index];
            // bug($item);die;
            $_POST['code_id'] = $item->code_id;
            $_POST['dimension_id'] = $item->dimension_id;
            $_POST['dimension2_id'] = $item->dimension2_id;
            $_POST['amount'] = price_format(abs($item->amount));
            $_POST['description'] = $item->description;
            $_POST['LineMemo'] = $item->description;
            $_POST['gst'] = $item->gst;

            hidden('Index', $id);
//             echo gl_all_accounts_list('code_id', null, true, true);
            gl_all_accounts_list_cells('code_id');

            if ($this->dim >= 1)
                dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
            if ($this->dim > 1)
                dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);

            $Ajax->activate('items_table');
        } elseif (isset($this->tran_cart->invoice_import) && ! empty($this->tran_cart->invoice_import)) {
            return;
        } else {
            $_POST['amount'] = price_format(0);
            $_POST['dimension_id'] = 0;
            $_POST['dimension2_id'] = 0;
            // $_POST['LineMemo'] = ""; // let memo go to next line Joe Hunt 2010-05-30
            if (isset($_POST['_code_id_update'])) {
                $Ajax->activate('code_id');
            }

            if ($_POST['PayType'] == PT_CUSTOMER) {
                $acc = get_branch_accounts($_POST['PersonDetailID']);
                $_POST['code_id'] = $acc['receivables_account'];
            } elseif ($_POST['PayType'] == PT_SUPPLIER) {
                $acc = get_supplier_accounts($_POST['person_id']);
                $_POST['code_id'] = $acc['payable_account'];
                $gst_usefor = 3;
            }             // elseif ($_POST['PayType'] == PT_WORKORDER)
            // $_POST['code_id'] = get_company_pref('default_assembly_act');
            else {
                $_POST['code_id'] = get_company_pref($this->tran_cart->trans_type ? 'default_cogs_act' : 'default_inv_sales_act');
            }
            gl_all_accounts_list_cells('code_id');
            if ($this->dim >= 1)
                dimensions_list_cells(null, 'dimension_id', null, true, " ", false, 1);
            if ($this->dim > 1)
                dimensions_list_cells(null, 'dimension2_id', null, true, " ", false, 2);
        }
        if ($this->dim < 1)
            hidden('dimension_id', 0);
        if ($this->dim < 2)
            hidden('dimension2_id', 0);

        input_money_cells('amount');
        $gst_usefor = 1;
        if (isset($_SESSION['pay_items']->trans_type)) {
            if ($_SESSION['pay_items']->trans_type == ST_BANKPAYMENT) {
                $gst_usefor = 3;
            } else
                if ($_SESSION['pay_items']->trans_type == ST_BANKDEPOSIT) {
                    $gst_usefor = 2;
                }
        }

        item_tax_types_list_cells(null,'gst',null,$gst_usefor);
        text_cells_ex(null, 'LineMemo', 35, 255);

        if ($id != - 1) {
            tbl_update('UpdateItem');
            tbl_cancel('CancelItemChanges');

//             button_cell('UpdateItem', _("Update"), _('Confirm changes'), ICON_UPDATE);
//             button_cell('CancelItemChanges', _("Cancel"), _('Cancel changes'), ICON_CANCEL);
            set_focus('amount');
        } else {
            tbl_add("AddItem");
            label_cell(NULL);
//             submit_cells('AddItem', _("Add Item"), 'colspan=2 align="center" ', _('Add new item to document'), true,'plus');
        }

        end_row();
    }

    function gl_options()
    {
        box_start();
        div_start('footer');

        row_start('justify-content-md-center');
        bootstrap_set_label_column(2);
        col_start(8);
//         echo "<table align='center' style='margin-top: 10px;' >";

        if( config_ci('kastam')){
            text_cells(_('Custom Duty'), 'custom_duty', null, null, null, null, ' width="20%" ');
            text_cells(_('Custom Assessed Value'), 'custom_assessed_value', null, null, null, null, ' width="20%" ');
        } else {
            hidden('custom_duty');
            hidden('custom_assessed_value');
        }

        $type = get_post('PayType');
        $bank_curr = get_bank_account_currency(get_post('bank_account'));

        $person_curr = $type == PT_CUSTOMER ? get_customer_currency(get_post('person_id')) : ($type == PT_SUPPLIER ? get_supplier_currency(get_post('person_id')) : $bank_curr);

        if ($person_curr != $bank_curr) {
            $_POST['settled_amount'] = price_format(abs($this->tran_cart->gl_items_total() / get_exchange_rate_from_to($bank_curr, $person_curr, get_post('date_'))));

            amount_row($type == PT_CUSTOMER ? _("Settled AR Amount:") : _("Settled AP Amount:"), 'settled_amount', null, null, $person_curr, user_price_dec());
            input_money($type == PT_CUSTOMER ? _("Settled AR Amount:") : _("Settled AP Amount:"), 'settled_amount' ,null, $person_curr);
        }
        // textarea_row(_("GST"), 'gst_', null, 20, 3);
        // textarea_row(_("Memo"), 'memo_', null, 50, 3,null,'colspan="3"');
        input_textarea_bootstrap( _("Memo"), 'memo_');
        col_end();
        row_end();

        div_end();
    }

    function page_finish(){
        if (isset($_GET['AddedID'])) {
            $this->add_payment_success();
            display_footer_exit();
        }


        if (isset($_GET['UpdatedID']))
        {
            $this->payment_update_success();
            display_footer_exit();
        }

        if (isset($_GET['AddedDep']))  {
            $this->deposit_add_success();
            display_footer_exit();
        }

        if (isset($_GET['UpdatedDep'])) {
            $this->deposit_update_success();
            display_footer_exit();
        }
    }
    private function add_payment_success(){
        $tran_no = $_GET['AddedID'];
        $tran_type = ST_BANKPAYMENT;

        display_notification_centered(sprintf(_("Payment %d has been entered"), $tran_no));

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');


        mt_list_link( _("Enter Another &Payment"), $_SERVER['PHP_SELF']."?NewPayment=yes");
        mt_list_link( _("Enter A &Deposit"), $_SERVER['PHP_SELF']."?NewDeposit=yes");
        mt_list_link( _("Add an Attachment"), "admin/attachments.php?filterType=$tran_type&trans_no=$tran_no");

        col_start(6);
        mt_list_start('Printing', null, 'red');
        mt_list_print(_("&Print This Voucher"), ST_GLPAYMENT, $tran_no, 'prtopt');
        mt_list_gl_view(_("&View the GL Postings for this Payment") ,$tran_type, $tran_no );

        //hyperlink_params($_SERVER['PHP_SELF'],_("&Print This Order"));
        // 	display_note(print_document_link($this->tran_no, _("&Print This Voucher 2"), true, ST_GLPAYMENT, false, 'prtopt'));

        row_end();
        box_footer();
        box_end();

    }
    private function payment_update_success(){
        $this->tran_no = $_GET['UpdatedID'];
        $this->tran_type = ST_BANKPAYMENT;

        display_notification_centered(sprintf(_("Payment %d has been modified"), $this->tran_no));

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_link( _("Enter Another &Payment"), $_SERVER['PHP_SELF']."?NewPayment=yes");
        mt_list_link( _("Enter A &Deposit"), $_SERVER['PHP_SELF']."?NewDeposit=yes");

        col_start(6);
        mt_list_start('Printing', null, 'red');
        mt_list_gl_view(_("&View the GL Postings Payment #".$this->tran_no), $this->tran_type, $this->tran_no);

        row_end();
        box_footer();
        box_end();

    }
    private function deposit_add_success(){
        $this->tran_no = $_GET['AddedDep'];
        $this->tran_type = ST_BANKDEPOSIT;

        display_notification_centered(sprintf(_("Deposit %d has been entered"), $this->tran_no));

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_link(_("Enter Another Deposit"), $_SERVER['PHP_SELF']."?NewDeposit=yes");
        mt_list_link( _("Enter A Payment"), $_SERVER['PHP_SELF']."?NewPayment=yes");

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        // 	display_note(print_document_link($this->tran_no, _("&Print This Voucher"), true, ST_GLDEPOSIT, false, 'download'));
        mt_list_print(_("&Print This Voucher"), ST_GLDEPOSIT, $this->tran_no, 'prtopt');
        mt_list_gl_view( _("View the GL Postings for this Deposit"),$this->tran_type, $this->tran_no);

        row_end();
        box_footer();
        box_end();
    }
    private function deposit_update_success(){
        $this->tran_no = $_GET['UpdatedDep'];
        $this->tran_type = ST_BANKDEPOSIT;

        display_notification_centered(sprintf(_("Deposit %d has been modified"), $this->tran_no));

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_link( _("Enter Another &Deposit"), $_SERVER['PHP_SELF']."?NewDeposit=yes");
        mt_list_link( _("Enter A &Payment"), $_SERVER['PHP_SELF']."?NewPayment=yes");

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_gl_view( _("&View the GL Postings for this Deposit"), $this->tran_type, $this->tran_no );
        row_end();
        box_footer();
        box_end();

    }


    function check_submit()
    {
        global $Ajax;

        //----------------------------------------------------------------------------------------
        if (list_updated('PersonDetailID')) {
            $br = get_branch(get_post('PersonDetailID'));
            $_POST['person_id'] = $br['debtor_no'];
            $Ajax->activate('person_id');
        }

        if (input_post('_tax_inclusive_update')) {
            $Ajax->activate('pmt_header');
            $Ajax->activate('items_table');
        }

        if (isset($_POST['_PayType_update'])) {
            $_POST['person_id'] = '';

            $Ajax->activate('pmt_header');
            $Ajax->activate('code_id');
            $Ajax->activate('pagehelp');
            $Ajax->activate('editors');
            $Ajax->activate('footer');
        }
    }

    private function set_default_value()
    {
        if (! isset($_POST['PayType'])) {
            if (isset($_GET['PayType']))
                $_POST['PayType'] = $_GET['PayType'];
            else
                $_POST['PayType'] = "";
        }

        if (! isset($_POST['person_id'])) {
            if (isset($_GET['PayPerson']))
                $_POST['person_id'] = $_GET['PayPerson'];
            else
                $_POST['person_id'] = "";
        }

        if (! $this->tran_cart->order_id && ! get_post('bank_account')) {
            if ($_POST['PayType'] == PT_CUSTOMER)
                $_POST['bank_account'] = get_default_customer_bank_account($_POST['person_id']);
            elseif ($_POST['PayType'] == PT_SUPPLIER)
                $_POST['bank_account'] = get_default_supplier_bank_account($_POST['person_id']);
            else
                unset($_POST['bank_account']);
        }
    }

    private function document_add(){
        $document_id = input_get('document');
        if( is_numeric($document_id) AND !in_ajax() ){
            $mobile_model = module_model_load('mobile','documents');
            $row = $mobile_model->item($document_id);
            $data = unserialize($row->data);
            if( !empty($data) ){
                $_POST['ref'] = $data['ref'];
                $_POST['memo_'] = $data['bank_remark'];

                if( isset($data['bank_account']) ){
                    $bank = get_instance()->db->where('account_code',$data['bank_account'])->get('bank_accounts')->row();
                    if( is_object($bank) AND isset($bank->id) ){
                        $_POST['bank_account'] = $bank->id;
                        $_POST['PayType'] = 0;
                    }
                }


                if($row->tran_type=="bank_payment"){
                    $_POST['person_id'] = $data['bank_payee'];
                    $_SESSION['pay_items']->add_gl_item($data['gl_account'], 0,0, floatval($data['bank_amount']), 0, null ,$data['bank_remark']);
                } elseif($row->tran_type=="bank_deposit") {
                    $_POST['person_id'] = $data['bank_payor'];
                    $_POST['cheque'] = $data['cheque_no'];

                    $_SESSION['pay_items']->add_gl_item($data['gl_account'], 0,0, floatval($data['bank_amount']), 0, null ,$data['bank_remark']);
                }
            }
//             bug($data);
        }
    }
}