<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class GlTranView
{

    var $tran_type, $tran_no = NULL;

    var $dim = 0;

    function __construct ()
    {
        $this->tran_no = input_get("trans_no");
        $this->tran_type = input_get("type_id");
        
        $this->dim = get_company_pref('use_dimension');
        
        $this->model = module_model_load('trans','gl');
    }

    function display ()
    {
        page(_("General Ledger Transaction Details"), true);
        
        if (is_null($this->tran_no) || is_null($this->tran_type)) {
            /* Script was not passed the correct parameters */
            display_note(
                    _(
                            "The script must be called with a valid transaction type and transaction number to review the general ledger postings for."));
            end_page();
            exit();
        }
        
//         $result = get_gl_trans($this->tran_type, $this->tran_no);

        $trans = $this->model->get_gl_trans($this->tran_type,$this->tran_no);

        //if (db_num_rows($result) == 0) {
        if( count($trans) < 1 ){
            $tran_name = systype_name(null,$this->tran_type);
            $tran_no = $this->tran_no;
            display_error(_("No general ledger transactions have been created for $tran_name number $tran_no"));
            end_page(true);
            exit();
        }
        
        box_start();
        $this->gl_heading($trans[0]);
        
        box_start();
        $this->gl_trans($trans);
        box_end();
    }

    private function gl_heading ($myrow)
    {
        
        start_table(TABLESTYLE);
        $th = array(
                _("General Ledger Transaction Details"),
                _("Reference"),
                _("Date"),
                _("Person/Item")
        );
        table_header($th);
        start_row();
        label_cell(systype_name(null,$this->tran_type)." #" . $this->tran_no);
        label_cell($myrow->reference);
        
        if ($myrow->tran_date) {
            label_cell(sql2date($myrow->tran_date));
        } else {
            label_cell(NULL);
        }
        
        label_cell(
                payment_person_name($myrow->person_type_id, $myrow->person_id));
        
        end_row();
        
        comments_display_row($this->tran_type, $this->tran_no);
        
        end_table(1);
        
        
        switch ($this->tran_type){
            case ST_SALESINVOICE:
                $report_link = 'sales/sales_order_entry.php?reinvoice=' .$this->tran_no;
                break;
            case ST_SUPPINVOICE:
                $report_link = 'purchasing/supplier_invoice.php?reinvoice=' .$this->tran_no;
                break;
            case ST_SUPPRECEIVE:
                $report_link = 'purchasing/po_entry_items.php?re-receive=' .$this->tran_no;
                break;
            case ST_CUSTPAYMENT:
                $report_link = 'sales/customer_payments.php?reinvoice=' .$this->tran_no;
                break;
            default:$report_link = null;
                break;
                
        }

        
        if (is_date_in_fiscalyear($myrow->tran_date) && $report_link) {
            $anchor = anchor($report_link, 
                    '<img width="12" src="../../themes/template/images/ok.gif">RE-POST', 
                    ' class="ajaxsubmit" ');
            echo '<div style="padding-bottom:15px; text-align: right; position: relative; top :0;" class="btnadd" >' .
                     $anchor . '</div>';
        }
        
        
    }
    
    private function gl_trans_th(){
        /* show a table of the transactions returned by the sql */
        
        if ($this->dim == 2) {
            $th = array(
                    _("Account Code"),
                    _("Account Name"),
                    _("Department") . " 1",
                    _("Dimension") . " 2",
                    _("Debit"),
                    _("Credit"),
                    _("Memo")
            );
        } elseif ($this->dim == 1) {
            $th = array(
                    _("Account Code"),
                    _("Account Name"),
                    _("Department"),
                    _("Debit"),
                    _("Credit"),
                    _("Memo")
            );
        }
        
        else {
            $th = array(
                    _("Account Code"),
                    _("Account Name"),
                    _("Debit"),
                    _("Credit"),
                    _("Memo")
            );
        }
        return $th;
    }
    private function gl_trans($trans=NULL){
        start_table(TABLESTYLE);
        
        $th = $this->gl_trans_th();
        table_header($th);
        
        $credit = $debit = 0;
        $lines = array();
        
        if ($this->tran_type != 0) {
            $tran = NULL;
            //while ($myrow = db_fetch($result)) {
            foreach ($trans AS $myrow){
                $myrow = (array)$myrow;
        
                $account = $myrow['account'];
                if (! array_key_exists($account, $lines)) {
                    $lines[$account] = array(
                            'account_name' => '',
                            'dimension_id' => '',
                            'dimension2_id' => '',
                            'amount' => 0,
                            'memo_' => ''
                    );
                }
                if ($myrow['account_name']) {
                    $lines[$account]['account_name'] = $myrow['account_name'];
                } else {
                    global $ci;
                    $model = $ci->model('bank', true);
                    $bank_acc = $model->item(
                            array(
                                    'account_code' => $myrow['account']
                            ));
                    
                    $lines[$account]['account_name'] = '<span class="theme" >Bank -</span>' . ( isset($bank_acc->bank_account_name) ? $bank_acc->bank_account_name : NULL)
                            ;
                }
        
                $lines[$account]['dimension_id'] = $myrow['dimension_id'];
                $lines[$account]['dimension2_id'] = $myrow['dimension2_id'];
                $lines[$account]['memo_'] = $myrow['memo_'];
                $lines[$account]['amount'] += $myrow['amount'];
                $tran = $myrow;
            }
        
            foreach ($lines as $acc_code => $line) {
        
                //                 if (! $heading_shown) {
        
                //                     $this->gl_heading($tran);
                //                     start_table(TABLESTYLE);
                //                     table_header($th);
                //                     $heading_shown = true;
                //                 }
        
                alt_table_row_color($k);
        
                label_cell($acc_code);
                label_cell($line['account_name']);
                if ($this->dim >= 1)
                    label_cell(
                            get_dimension_string($line['dimension_id'], true));
                    if ($this->dim > 1)
                        label_cell(
                                get_dimension_string($line['dimension2_id'], true));
        
                        display_debit_or_credit_cells($line['amount']);
                        label_cell($line['memo_']);
                        end_row();
                        if ($line['amount'] > 0)
                            $debit += $line['amount'];
                            else
                                $credit += $line['amount'];
            }
        } else {
        
            while ($line = db_fetch($result)) {
                //                 if (! $heading_shown) {
                //                     $this->gl_heading($line);
                //                     start_table(TABLESTYLE);
                //                     table_header($th);
                //                     $heading_shown = true;
                //                 }
        
                alt_table_row_color($k);
        
                label_cell($line['account']);
                label_cell($line['account_name']);
                if ($dim >= 1)
                    label_cell(
                            get_dimension_string($line['dimension_id'], true));
                    if ($dim > 1)
                        label_cell(
                                get_dimension_string($line['dimension2_id'], true));
        
                        display_debit_or_credit_cells($line['amount']);
                        label_cell($line['memo_']);
                        end_row();
                        if ($line['amount'] > 0)
                            $debit += $line['amount'];
                            else
                                $credit += $line['amount'];
            }
        }
        
//         if ($heading_shown) {
            start_row("class='inquirybg' style='font-weight:bold'");
            label_cell(_("Total"), "colspan=2");
            if ($this->dim >= 1)
                label_cell('');
                if ($this->dim > 1)
                    label_cell('');
                    // number_format2($debit,user_amount_dec());
                    label_cell(number_format2($debit, user_amount_dec()),
                            "nowrap align=right ");
                    // amount_cell($debit);
                    label_cell(number_format2(- $credit, user_amount_dec()),
                            "nowrap align=right ");
                    // amount_cell(-$credit);
                    label_cell('');
                    end_row();
                    end_table(1);
//         }
    }
}