<?php

class PurchasesTranPayment
{

    function __construct()
    {
        $this->check_input_get();
        $this->check_submit();

        $this->alloc_cart = module_control_load('cart','allocation');

        $this->document_add();
    }

    function form()
    {

        start_form();

        box_start();
        $this->payment_header();
        $this->payment_transactions();
        if( intval( $document_id = input_get('document')) > 0 ){
            hidden('document_id',$document_id);
        }
        box_footer_start();
        submit('ProcessSuppPayment', _("Enter Payment"), true, '', 'default');
        box_footer_end();
        box_end();
        end_form();
    }

    private function payment_header(){
        global $Refs, $Ajax;

        row_start();

        col_start(4);
        bootstrap_set_label_column(5);
        supplier_list_bootstrap(_("Payment To"), 'supplier_id', null, false, true);

        if (list_updated('supplier_id') || list_updated('bank_account')) {
            $_SESSION['alloc']->read();
            $_POST['memo_'] = $_POST['amount'] = '';
            $Ajax->activate('alloc_tbl');
        }

        set_global_supplier($_POST['supplier_id']);

        if (!list_updated('bank_account') && ! get_post('__ex_rate_changed')){
            if( empty($_POST['bank_account']) ){
                $_POST['bank_account'] = get_default_supplier_bank_account($_POST['supplier_id']);
            }

        }else
            $_POST['amount'] = price_format(0);

        bank_accounts(_("From Bank Account"), 'bank_account', null, true);

        bank_balance_label($_POST['bank_account']);


        col_start(4);
        bootstrap_set_label_column(4);

        input_date_bootstrap(_("Date Paid"), 'DatePaid', '', false, true);

        input_ref('Reference', 'ref',$Refs->get_next(ST_SUPPAYMENT));
        input_text('Cheque No.', 'cheque');

        col_start(4);

        $comp_currency = get_company_currency();
        $supplier_currency = $_SESSION['alloc']->set_person($_POST['supplier_id'], PT_SUPPLIER);
//         $_SESSION['alloc']->read(ST_SUPPAYMENT, 0, $_POST['supplier_id'], PT_SUPPLIER);

        if (! $supplier_currency)
            $supplier_currency = $comp_currency;
        $_SESSION['alloc']->currency = $bank_currency = get_bank_account_currency($_POST['bank_account']);

        if ($bank_currency != $supplier_currency) {
            input_money('Bank Amount', 'bank_amount',null,$bank_currency);
//             amount_row(_("Bank Amount:"), 'bank_amount', null, '', $bank_currency, 2);
        }

        input_money('Bank Charge', 'charge',null,$bank_currency);
        row_end();
    }

    private function payment_transactions(){
        div_start('alloc_tbl');
        $this->alloc_cart->alloca_table(false);

        div_end();

        row_start('justify-content-md-center');
        bootstrap_set_label_column(3);
        col_start(8);
            input_money('Amount of Discount', 'discount');
            input_money('Amount of Payment', 'amount');
            input_textarea_bootstrap('Memo', 'memo_');
        row_end();
    }

    private function payment_success()
    {
        $payment_id = $_GET['AddedID'];
        row_start();

        col_start(4);
        mt_list_start('Actions','','blue');

        mt_list_link(_("Enter another supplier &payment"), "/purchasing/supplier_payment.php?supplier_id=" . $_POST['supplier_id']);
        mt_list_link(_("Enter Other &Payment"), "/gl/gl_bank.php?NewPayment=Yes");
        mt_list_link(_("Enter &Customer Payment"), "/sales/customer_payments.php");
        mt_list_link(_("Enter Other &Deposit"), "/gl/gl_bank.php?NewDeposit=Yes");
        mt_list_link(_("Bank Account &Transfer"), "/gl/bank_transfer.php");

        col_start(4);
        mt_list_start('Printing',null,'red');

        mt_list(submenu_print(_("&Print This Remittance"), ST_SUPPAYMENT, $payment_id . "-" . ST_SUPPAYMENT, 'prtopt'));
        mt_list(submenu_print(_("&Email This Remittance"), ST_SUPPAYMENT, $payment_id . "-" . ST_SUPPAYMENT, null, 1));

        mt_list_end();

        col_start(4);
        mt_list_start('GL',null,'green-sharp');
//         submenu_view(_("View this Payment"), ST_SUPPAYMENT, $payment_id);
        $gl_link = get_gl_view_str(ST_SUPPAYMENT, $payment_id, _("View the GL &Journal Entries for this Payment"),$force=false, $class='', $id='',$icon=false);
        mt_list( $gl_link );
        row_end();

    }

    private function check_input_get()
    {
        if (isset($_GET['supplier_id'])) {
            $_POST['supplier_id'] = $_GET['supplier_id'];
        }

        if (isset($_GET['AddedID'])) {
            box_start();
                display_notification(_("Payment has been sucessfully entered"));
                $this->payment_success();
            box_footer();
            box_end();
            display_footer_exit();
        }
    }

    private function check_submit()
    {
        global $Ajax;
        if (! isset($_POST['supplier_id']))
            $_POST['supplier_id'] = get_global_supplier(false);

        if (! isset($_POST['DatePaid'])) {
            $_POST['DatePaid'] = new_doc_date();
            if (! is_date_in_fiscalyear($_POST['DatePaid']))
                $_POST['DatePaid'] = end_fiscalyear();
        }

        if (isset($_POST['_DatePaid_changed'])) {
            $Ajax->activate('_ex_rate');
        }

        if (list_updated('supplier_id')) {
            $_POST['amount'] = price_format(0);
            $_SESSION['alloc']->person_id = get_post('supplier_id');
            $Ajax->activate('amount');
        } elseif (list_updated('bank_account'))
            $Ajax->activate('alloc_tbl');

            // ----------------------------------------------------------------------------------------

        if (! isset($_POST['bank_account'])) { // first page call
            $_SESSION['alloc'] = new allocation(ST_SUPPAYMENT, 0, get_post('supplier_id'));

            if (isset($_GET['PInvoice'])) {
                // get date and supplier
                $inv = get_supp_trans($_GET['PInvoice'], ST_SUPPINVOICE);
                $dflt_act = get_default_bank_account($inv['curr_code']);
                $_POST['bank_account'] = $dflt_act['id'];
                if ($inv) {
                    $_SESSION['alloc']->person_id = $_POST['supplier_id'] = $inv['supplier_id'];
                    $_SESSION['alloc']->read();
                    $_POST['DatePaid'] = sql2date($inv['tran_date']);
                    $_POST['memo_'] = $inv['supp_reference'];

                    foreach ($_SESSION['alloc']->allocs as $line => $trans) {
                        if ($trans->type == ST_SUPPINVOICE && $trans->type_no == $_GET['PInvoice']) {
                            $un_allocated = abs($trans->amount) - $trans->amount_allocated;
                            $_SESSION['alloc']->amount = $_SESSION['alloc']->allocs[$line]->current_allocated = $un_allocated;
                            $_POST['amount'] = $_POST['amount' . $line] = price_format($un_allocated);
                            break;
                        }
                    }
                    unset($inv);
                } else
                    display_error(_("Invalid purchase invoice number."));
            }
        }

        if (isset($_POST['ProcessSuppPayment'])) {
            /* First off check for valid inputs */
            if (check_inputs() == true) {
                handle_add_payment();
                end_page();
                exit();
            }
        }
    }

    private function document_add(){
        $document_id = input_get('document');
        if( is_numeric($document_id) AND !in_ajax() ){
            $mobile_model = module_model_load('mobile','documents');
            $row = $mobile_model->item($document_id);
            $data = unserialize($row->data);
            if( !empty($data) ){
                $_POST['ref']           = $data['ref'];
                $_POST['supplier_id']   = $data['supplier_id'];
                $_POST['amount']        = $data['supplier_amount'];
                $_POST['memo_']         = $data['supplier_bill_no'];

                if( isset($data['bank_account']) ){
                    $bank = get_instance()->db->where('account_code',$data['bank_account'])->get('bank_accounts')->row();
                    if( is_object($bank) AND isset($bank->id) ){
                        $_POST['bank_account'] = $bank->id;
                    }
                }
            }
//             bug($data);
        }
    }
}