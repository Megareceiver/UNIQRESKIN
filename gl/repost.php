<?php
$page_security = 'SA_JOURNALENTRY';
$path_to_root = "..";
// include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");
global $ci;
$js = get_js_date_picker();
page('Re-Post Bank payment/deposit');
$start_date = $ci->input->post('start_date');
$end_date = $ci->input->post('end_date');
$start_date = $end_date= '10-04-2015';
if( $start_date || $end_date ) {
    $gl_bank_trans_model = $ci->model('gl_bank_trans',true);
    $items = $gl_bank_trans_model->get_bank_trans(ST_BANKPAYMENT,null,null,null,array('bt.trans_date >='=>date2sql($start_date),'bt.trans_date <='=>date2sql($end_date)));
    if( $items && count($items) > 0 ){
        foreach ($items AS $bank_tran){
//             bug($bank_tran);
                $cart = new items_cart($bank_tran->type);
                $cart->order_id = $bank_tran->trans_no;
                $PersonDetailID = NULL;
                if ($bank_tran->person_type_id == PT_CUSTOMER) {
                    $trans = get_customer_trans($bank_tran->trans_no, $bank_tran->type);
                    $person_id = $bank_tran->debtor_no;
                    $PersonDetailID = $trans["branch_code"];
                } elseif ($bank_tran->person_type_id == PT_SUPPLIER) {
                    $trans = get_supp_trans($bank_tran->trans_no, $bank_tran->type);
                    $person_id = $bank_trans->supplier_id;
//                 } elseif ($bank_tran->person_type_id == PT_MISC){
//                     $_POST['person_id'] = $bank_tran->person_id;
//                 } elseif ($bank_tran->person_type_id == PT_QUICKENTRY){
//                     $_POST['person_id'] = $bank_trans["person_id"];
                } else
                    $person_id = $bank_tran->person_id;

                $cart->memo_ = get_comments_string($bank_tran->type, $bank_tran->trans_no);
                $cart->tran_date = sql2date($bank_tran->trans_date);
                $cart->reference = $Refs->get($bank_tran->type, $bank_tran->trans_no);
                $cart->person_id = $person_id;
                $cart->branch_id = $PersonDetailID;
                $cart->original_amount = $bank_tran->amount;
                $result = get_gl_trans($bank_tran->type, $bank_tran->trans_no);

                if ($result) { while ($row = db_fetch($result)) {
                    if (is_bank_account($row['account'])) {
                        $ex_rate = $bank_tran->amount/$row['amount'];
                    } elseif($row['gst']>0) {
                        $date = $row['tran_date'];
                        $cart->add_gl_item( $row['account'], $row['dimension_id'], $row['dimension2_id'], $row['amount'], $row['gst'] ,$bank_tran->ref, $row['memo_']);
                    }
                }}
                foreach($cart->gl_items as $line_no => $line)
                    $cart->gl_items[$line_no]->amount *= $ex_rate;


//             }
            //$_POST['memo_'] = $cart->memo_;
//             $_POST['ref'] = $cart->reference;
//             $_POST['date_'] = $cart->tran_date;

//             $_SESSION['pay_items'] = &$cart;


//             $_SESSION['pay_items'] = &$_SESSION['pay_items'];
//             $new = $_SESSION['pay_items']->order_id == 0;

            //         add_new_exchange_rate(get_bank_account_currency(get_post('bank_account')), get_post('date_'), input_num('_ex_rate'));

            $trans = write_bank_transaction($bank_tran->type, $bank_tran->trans_no, $bank_tran->bank_act,
                $cart, $bank_tran->trans_date,
                $bank_tran->person_type_id, $person_id, $PersonDetailID,
                 $bank_tran->ref,$gst=0, $cart->memo_, true, $settled_amount=null,
                $bank_tran->tax_inclusive,$bank_tran->cheque
            );

//             $trans_type = $trans[0];
//             $trans_no = $trans[1];
            new_doc_date($bank_tran->trans_date);
            // 	die('end commit trans 313');
//             $_SESSION['pay_items']->clear_items();
//             unset($_SESSION['pay_items']);

            commit_transaction();
//             die('repost bank');
        }

    }


} else {

    $form = array(
        'start_date' => array('type'=>'DATE','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
        'end_date' => array('type'=>'DATE','title'=>_('End Date'),'value'=>end_fiscalyear()),
    );

    start_form();
    $ci->view('common/form',array('items'=>$form));
    submit('UPDATE_ITEM', _("Submit"), true, _('Submit'));
    end_form();
}
end_page();

