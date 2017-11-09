<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class VoidMain {

    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->tran_model = module_model_load('tran','void');

    }

    function exist_transaction($type, $type_no){

        $void_entry = $this->tran_model->get_entry($type, $type_no);

        if ($void_entry != null)
            return false;

        switch ($type){
            case ST_JOURNAL : // it's a journal entry
                if (!exists_gl_trans($type, $type_no))
                    return false;
                    break;

            case ST_BANKPAYMENT : // it's a payment
            case ST_BANKDEPOSIT : // it's a deposit
            case ST_BANKTRANSFER : // it's a transfer
                if (!exists_bank_trans($type, $type_no))
                    return false;
                    break;

            case ST_SALESINVOICE : // it's a customer invoice
            case ST_CUSTCREDIT : // it's a customer credit note
            case ST_CUSTPAYMENT : // it's a customer payment
            case ST_CUSTDELIVERY : // it's a customer dispatch
                if (!exists_customer_trans($type, $type_no))
                    return false;
                    break;

            case ST_LOCTRANSFER : // it's a stock transfer
                if (get_stock_transfer_items($type_no) == null)
                    return false;
                    break;

            case ST_INVADJUST : // it's a stock adjustment
                if (get_stock_adjustment_items($type_no) == null)
                    return false;
                    break;

            case ST_PURCHORDER : // it's a PO
                return false;

            case ST_SUPPRECEIVE : // it's a GRN
                if (exists_grn_on_invoices($type_no))
                    return false;
                    break;

            case ST_SUPPINVOICE : // it's a suppler invoice
            case ST_SUPPCREDIT : // it's a supplier credit note
            case ST_SUPPAYMENT : // it's a supplier payment
                if (!exists_supp_trans($type, $type_no))
                    return false;
                    break;

            case ST_WORKORDER : // it's a work order
                if (!get_work_order($type_no, true))
                    return false;
                    break;

            case ST_MANUISSUE : // it's a work order issue
                if (!exists_work_order_issue($type_no))
                    return false;
                    break;

            case ST_MANURECEIVE : // it's a work order production
                if (!exists_work_order_produce($type_no))
                    return false;
                    break;

            case ST_SALESORDER: // it's a sales order
            case ST_SALESQUOTE: // it's a sales quotation
                return false;
            case ST_COSTUPDATE : // it's a stock cost update
                return false;
                break;
        }

        return true;
    }

    function void_transaction($type, $type_no, $date_, $memo_,$check_voided=true)
    {

        $void_entry = $this->tran_model->get_entry($type, $type_no);

        if( $check_voided ) {
            if ($void_entry != null)
                return false;
        }


        switch ($type) {
            case ST_JOURNAL : // it's a journal entry
                if (!exists_gl_trans($type, $type_no))
                    return false;
                    void_journal_trans($type, $type_no);
                    break;

            case ST_BANKDEPOSIT : // it's a deposit
            case ST_BANKTRANSFER : // it's a transfer
                if (!check_void_bank_trans($type, $type_no))
                    return false;
            case ST_BANKPAYMENT : // it's a payment
                if (!exists_bank_trans($type, $type_no))
                    return false;
                    void_bank_trans($type, $type_no);
                    break;

            case ST_CUSTPAYMENT :
                $model = module_model_load('payment','sales');

                // it's a customer payment
                if (  $model->check_void_bank_trans($type, $type_no) != true )
                    return false;
                bug($model);die;
                void_customer_payment($type, $type_no); die('go here');
                break;
            case ST_SALESINVOICE : // it's a customer invoice
            case ST_CUSTCREDIT : // it's a customer credit note
            case ST_CUSTDELIVERY : // it's a customer dispatch
                    if (!exists_customer_trans($type, $type_no))
                        return false;
                    if ($type == ST_CUSTDELIVERY)	// added 04 Oct 2008 by Joe Hunt. If delivery note has a not voided invoice, then NO.
                    {
                        $childs = get_sales_child_lines($type, $type_no, false); // 2011-03-17 This had been changed. Joe
                        if ($childs && db_num_rows($childs))
                            return false;
                    }
                    post_void_customer_trans($type, $type_no);
                    break;

            case ST_LOCTRANSFER : // it's a stock transfer
                if (get_stock_transfer_items($type_no) == null)
                    return false;
                    void_stock_transfer($type_no);
                    break;

            case ST_INVADJUST : // it's a stock adjustment
                if (get_stock_adjustment_items($type_no) == null)
                    return false;
                    void_stock_adjustment($type_no);
                    break;

            case ST_PURCHORDER : // it's a PO
                return false;

            case ST_SUPPRECEIVE : // it's a GRN
                if (exists_grn_on_invoices($type_no))
                    return false;
            case ST_SUPPINVOICE : // it's a suppler invoice
            case ST_SUPPCREDIT : // it's a supplier credit note
            case ST_SUPPAYMENT : // it's a supplier payment
                if (!exists_supp_trans($type, $type_no))
                    return false;
                    if (!post_void_supp_trans($type, $type_no))
                        return false;
                    break;

            case ST_WORKORDER : // it's a work order
                if (!get_work_order($type_no, true))
                    return false;
                    void_work_order($type_no);
                    break;

            case ST_MANUISSUE : // it's a work order issue
                if (!exists_work_order_issue($type_no))
                    return false;
                    void_work_order_issue($type_no);
                    break;

            case ST_MANURECEIVE : // it's a work order production
                if (!exists_work_order_produce($type_no))
                    return false;
                    void_work_order_produce($type_no);
                    break;

            case ST_SALESORDER: // it's a sales order
            case ST_SALESQUOTE: // it's a sales quotation
                return false;

            case ST_COSTUPDATE : // it's a stock cost update
                return false;
                break;
        }

        // only add an entry if it's actually been voided
        add_audit_trail($type, $type_no, $date_, _("Voided.")."\n".$memo_);

        global $Refs;
        $Refs->restore_last($type, $type_no);
        add_voided_entry($type, $type_no, $date_, $memo_);
        return true;
    }


}