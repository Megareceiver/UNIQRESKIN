<?php

class SalesTranPayment
{

    function __construct()
    {
        $this->page_start();
        $this->page_submit();
        $this->page_finish();

        $this->document_add();
    }

    function form(){
        $this->form_start();

        start_form();
        hidden('trans_no');
        hidden('old_ref', $this->old_ref);

        box_start();
        row_start();
        col_start(4);
        bootstrap_set_label_column(5);

        bank_accounts(_("Into Bank Account"), 'bank_account', null, true);
        if ($this->new)
            customer_list_bootstrap(_("From Customer"), 'customer_id', null, false, true);
        else {
            input_label(_("From Customer"), null, $_SESSION['alloc']->person_name);
            hidden('customer_id', $_POST['customer_id']);
        }

        if (db_customer_has_branches($_POST['customer_id'])) {
            customer_branches_bootstrap(_("Branch"), $_POST['customer_id'], 'BranchID', null, false, true, true);
        } else {
            hidden('BranchID', ANY_NUMERIC);
        }

        read_customer_data();
        set_global_customer($_POST['customer_id']);

        if (isset($_POST['HoldAccount']) && $_POST['HoldAccount'] != 0)
            display_warning(_("This customer account is on hold."));

        $display_discount_percent = percent_format($_POST['pymt_discount']*100) . "%";

        col_start(4);
        input_date_bootstrap(_("Date of Deposit"), 'DateBanked');

        input_ref(_("Reference"), 'ref');
        input_text('Cheque No.','cheque');
//         ref_row(_("Reference:"), 'ref','' , null, '', ST_CUSTPAYMENT);

        if( defined('COUNTRY') && COUNTRY==65 ){
            input_ref('Customer Ref','source_ref');
        }

        col_start(4);

        $comp_currency = get_company_currency();
        $cust_currency = $_SESSION['alloc']->set_person($_POST['customer_id'], PT_CUSTOMER);
        if (!$cust_currency)
            $cust_currency = $comp_currency;
        $_SESSION['alloc']->currency = $bank_currency = get_bank_account_currency($_POST['bank_account']);

        if ($cust_currency != $bank_currency) {

            input_money(_("Payment Amount"), 'bank_amount', null, $bank_currency);

        }
        input_money(_("Bank Charge"), 'charge', null, $bank_currency);
//         amount_row(_("Bank Charge:"), 'charge', null, '', $bank_currency);

//         end_outer_table(1);
        row_end();

        if (count($_SESSION['alloc']->allocs) > 0){
            if ($_SESSION['alloc']->currency != $_SESSION['alloc']->person_curr){
                box_start(sprintf(_("Allocated amounts in %s "), $_SESSION['alloc']->person_curr));
            } else {
                box_start();
            }
            div_start('alloc_tbl');
            show_allocatable(false);
            div_end();
        }


        box_start();
        row_start('justify-content-center');
            col_start(8);
            input_label(_("Customer prompt payment discount"), null, $display_discount_percent);
            input_money(_("Amount of Discount"), 'discount',null,$cust_currency);
            input_money(_("Amount"), 'amount',null,$cust_currency);
            input_textarea('Memo', 'memo_');
        row_end();

        box_footer_start();
        if( intval( $document_id = input_get('document')) > 0 ){
            hidden('document_id',$document_id);
        }

        if ($this->new)
            submit('AddPaymentItem', _("Add Payment"), true, '', 'default');
        else
            submit('AddPaymentItem', _("Update Payment"), true, '', 'default');

        box_footer_end();
        box_end();
        end_form();
    }
    var $new = 1;
    var $old_ref = NULL;
    private function form_start(){
        //----------------------------------------------------------------------------------------------
        $this->new = 1;
        $old_ref = 0;

        //Chaitanya : 13-OCT-2011 - To support Edit feature
        if (isset($_GET['trans_no']) && $_GET['trans_no'] > 0 ){
            $_POST['trans_no'] = $_GET['trans_no'];

            $this->new = 0;
            $myrow = get_customer_trans($_POST['trans_no'], ST_CUSTPAYMENT);
            $_POST['customer_id'] = $myrow["debtor_no"];
            $_POST['customer_name'] = $myrow["DebtorName"];
            $_POST['BranchID'] = $myrow["branch_code"];
            $_POST['bank_account'] = $myrow["bank_act"];
            $_POST['ref'] =  $myrow["reference"];
            $old_ref = $myrow["reference"];
            $charge = get_cust_bank_charge(ST_CUSTPAYMENT, $_POST['trans_no']);
            $_POST['charge'] =  price_format($charge);
            $_POST['DateBanked'] =  sql2date($myrow['tran_date']);
            $_POST["amount"] = price_format($myrow['Total'] - $myrow['ov_discount']);
            $_POST["bank_amount"] = price_format($myrow['bank_amount']+$charge);
            $_POST["discount"] = price_format($myrow['ov_discount']);
            $_POST["memo_"] = get_comments_string(ST_CUSTPAYMENT,$_POST['trans_no']);
            $_POST['cheque'] =  $myrow['cheque'];
            //Prepare allocation cart
            if (isset($_POST['trans_no']) && $_POST['trans_no'] > 0 )
                $_SESSION['alloc'] = new allocation(ST_CUSTPAYMENT,$_POST['trans_no']);
            else
            {
                $_SESSION['alloc'] = new allocation(ST_CUSTPAYMENT,0);
                $Ajax->activate('alloc_tbl');
            }
        }
        // bug($_SESSION['alloc']);die;
        //----------------------------------------------------------------------------------------------
        $this->new = !$_SESSION['alloc']->trans_no;
        $this->old_ref = $old_ref;
    }

    private function page_start()
    {
        // ----------------------------------------------------------------------------------------
        if (isset($_GET['customer_id'])) {
            $_POST['customer_id'] = $_GET['customer_id'];
        } elseif (input_get('reinvoice')) {

        }

        if (! isset($_POST['bank_account'])) { // first page call
            $_SESSION['alloc'] = new allocation(ST_CUSTPAYMENT, 0, get_post('customer_id'));

            if (isset($_GET['SInvoice'])) {
                // get date and supplier
                $inv = get_customer_trans($_GET['SInvoice'], ST_SALESINVOICE);
                $dflt_act = get_default_bank_account($inv['curr_code']);
                $_POST['bank_account'] = $dflt_act['id'];
                if ($inv) {
                    $_SESSION['alloc']->person_id = $_POST['customer_id'] = $inv['debtor_no'];
                    $_SESSION['alloc']->read();
                    $_POST['DateBanked'] = sql2date($inv['tran_date']);
                    foreach ($_SESSION['alloc']->allocs as $line => $trans) {
                        if ($trans->type == ST_SALESINVOICE && $trans->type_no == $_GET['SInvoice']) {
                            $un_allocated = $trans->amount - $trans->amount_allocated;
                            if ($un_allocated) {
                                $_SESSION['alloc']->allocs[$line]->current_allocated = $un_allocated;
                                $_POST['amount'] = $_POST['amount' . $line] = price_format($un_allocated);
                            }
                            break;
                        }
                    }
                    unset($inv);
                } else
                    display_error(_("Invalid sales invoice number."));
            }
        }

        if (! isset($_POST['customer_id'])) {
            $_SESSION['alloc']->person_id = $_POST['customer_id'] = get_global_customer(false);
            $_SESSION['alloc']->read();
        }
        if (! isset($_POST['DateBanked'])) {
            $_POST['DateBanked'] = new_doc_date();
            if (! is_date_in_fiscalyear($_POST['DateBanked'])) {
                $_POST['DateBanked'] = end_fiscalyear();
            }
        }
    }

    private function page_submit()
    {
        global $Ajax;
        if (list_updated('BranchID')) {
            // when branch is selected via external editor also customer can change
            $br = get_branch(get_post('BranchID'));
            $_POST['customer_id'] = $br['debtor_no'];
            $_SESSION['alloc']->person_id = $br['debtor_no'];
            $Ajax->activate('customer_id');
        }
        if (isset($_POST['_customer_id_button'])) {
            // unset($_POST['branch_id']);
            $Ajax->activate('BranchID');
        }

        if (get_post('AddPaymentItem') && can_process()) {

            new_doc_date($_POST['DateBanked']);

            $new_pmt = ! $_SESSION['alloc']->trans_no;


            // Chaitanya : 13-OCT-2011 - To support Edit feature
            $payment_no = write_customer_payment($_SESSION['alloc']->trans_no, $_POST['customer_id'], $_POST['BranchID'], $_POST['bank_account'], $_POST['DateBanked'], $_POST['ref'], input_num('amount'), input_num('discount'), $_POST['memo_'], 0, input_num('charge'), input_num('bank_amount', input_num('amount')), $_POST['cheque']);

            $_SESSION['alloc']->trans_no = $payment_no;
            $_SESSION['alloc']->write();

            $tran_type = $_SESSION['alloc']->type;
            $tran_no = $_SESSION['alloc']->trans_no;

            if (isset($_POST['source_ref'])) {
                update_source_ref($tran_type, $tran_no, $_POST['source_ref']);
            }

            if( intval( $document_id = input_post('document_id')) > 0 ){
                $mobile_model = module_model_load('mobile','documents');
                $mobile_model->update_posting_link($tran_type,$tran_no,$document_id);
            }
            unset($_SESSION['alloc']);
            meta_forward($_SERVER['PHP_SELF'], $new_pmt ? "AddedID=$tran_no" : "UpdatedID=$tran_no");
        }

        if (list_updated('customer_id') || ($this->new && list_updated('bank_account'))) {
            $_SESSION['alloc']->set_person($_POST['customer_id'], PT_CUSTOMER);
            $_SESSION['alloc']->read();
            $_POST['memo_'] = $_POST['amount'] = $_POST['discount'] = '';
            $Ajax->activate('alloc_tbl');
        }
    }

    private function page_finish()
    {
        if (isset($_GET['AddedID'])) {
            $this->add_payment_success();
            display_footer_exit();
        } elseif (isset($_GET['UpdatedID'])) {
            $this->payment_update_success();
            display_footer_exit();
        }
    }
    private function add_payment_success(){
        $payment_no = $_GET['AddedID'];

        display_notification_centered(_("The customer payment has been successfully entered."));

        box_start();
        row_start();

        col_start(6);
        mt_list_start( 'Actions', '', 'blue');
            mt_list_link(_("Enter Another &Customer Payment"), "/sales/customer_payments.php");
            mt_list_link(_("Enter Other &Deposit"), "/gl/gl_bank.php?NewDeposit=Yes");
            mt_list_link(_("Enter Payment to &Supplier"), "/purchasing/supplier_payment.php");
            mt_list_link(_("Enter Other &Payment"), "/gl/gl_bank.php?NewPayment=Yes");
            mt_list_link(_("Bank Account &Transfer"), "/gl/bank_transfer.php");

        col_start(6);
        mt_list_start('Actions', '', 'blue');
            mt_list_print(_("&Print This Receipt"), ST_CUSTPAYMENT, $payment_no . "-" . ST_CUSTPAYMENT, 'prtopt');
            mt_list_tran_view(_("&View this Customer Payment"), ST_CUSTPAYMENT, $payment_no);
            mt_list_gl_view(_("&View the GL Journal Entries for this Customer Payment"),ST_CUSTPAYMENT, $payment_no);

        row_end();
        box_footer();
        box_end();
    }
    private function payment_update_success(){
        $payment_no = $_GET['UpdatedID'];

        display_notification_centered(_("The customer payment has been successfully updated."));

        box_start();
        row_start();

        col_start(6);
        mt_list_start( 'Actions', '', 'blue');
            mt_list_print(_("&Print This Receipt"), ST_CUSTPAYMENT, $payment_no . "-" . ST_CUSTPAYMENT, 'prtopt');
            mt_list_gl_view(_("&View the GL Journal Entries for this Customer Payment"), ST_CUSTPAYMENT, $payment_no);
        // hyperlink_params($path_to_root . "/sales/allocations/customer_allocate.php", _("&Allocate this Customer Payment"), "trans_no=$payment_no&trans_type=12");

        col_start(6);
        mt_list_start( 'Actions', '', 'blue');
            mt_list_link( _("Select Another Customer Payment for &Edition"), "/sales/inquiry/customer_inquiry.php?");
            mt_list_link( _("Enter Another &Customer Payment"),  "/sales/customer_payments.php");
        row_end();
        box_footer();
        box_end();
    }


    private function document_add(){
        $document_id = input_get('document');
        if( is_numeric($document_id) AND !in_ajax() ){
            $mobile_model = module_model_load('mobile','documents');
            $row = $mobile_model->item($document_id);
            $data = unserialize($row->data);
            if( !empty($data) ){
                $_POST['ref']           = $data['ref'];
                $_POST['bank_account']   = $data['account_id'];
                $_POST['memo_']        = $data['customer_bill_no'];
                $_POST['amount']         = $data['customer_amount'];
                $_POST['bank_amount']         = $data['customer_amount'];

                $_POST['customer_id']  = $data['customer_id'];
            }
        }
    }
}