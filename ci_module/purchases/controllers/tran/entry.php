<?php
class PurchasesTranEntry
{

    function __construct()
    {
        $this->check_input_get();
        $this->check_submit();

        $this->order = module_control_load('order','purchases');
        $this->order->cart = $_SESSION['PO'];
    }

    function form(){

        start_form();
        box_start_col_md_8();
        $this->order->form_header();
        // echo "<br>";


        box_start('Order Items');
        $this->order->form_items();

        row_start('justify-content-center');
            bootstrap_set_label_column(1);
            col_start(8);
            input_textarea('Memo', 'Comments');
        row_end();

        box_footer_start();
        div_start('controls', 'items_table');

        $process_txt = _("Place Order");
        $update_txt = _("Update Order");
        $cancel_txt = _("Cancel Order");
        if ($_SESSION['PO']->trans_type == ST_SUPPRECEIVE) {
            $process_txt = _("Process GRN");
            $update_txt = _("Update GRN");
            $cancel_txt = _("Cancel GRN");
        } elseif ($_SESSION['PO']->trans_type == ST_SUPPINVOICE) {
            $process_txt = _("Process Invoice");
            $update_txt = _("Update Invoice");
            $cancel_txt = _("Cancel Invoice");
        }

        if ($_SESSION['PO']->order_has_items()) {
            if ($_SESSION['PO']->order_no) {
                submit('Commit', $update_txt,true, '', 'default');
            } else {
                submit('Commit', $process_txt,true, '', 'default');
//                 submit('Commit', $process_txt,true, '', false);
            }

            submit('CancelOrder', $cancel_txt);
        } else
            submit('CancelOrder', $cancel_txt, true, false, 'cancel');
        div_end();
        box_footer_end();
        //---------------------------------------------------------------------------------------------------
        box_end();
        end_form();
    }


    private function order_finish(){
        $order_no = $_GET['AddedID'];
        $trans_type = ST_PURCHORDER;

        if (!isset($_GET['Updated']))
            display_notification(_("Purchase Order has been entered"));
        else
            display_notification(_("Purchase Order has been updated") . " #$order_no");

        box_start();
        row_start();

        col_start(6);
        mt_list_start('Printing', null, 'red');
        mt_list_print( _("&Print This Order"), $trans_type, $order_no);
        mt_list_print( _("&Email This Order"), $trans_type, $order_no, null, false);

        col_start(6);
        mt_list_start('Actions', '', 'blue');
        mt_list_link(_("&Receive Items on this Purchase Order"),"purchasing/po_receive_items.php?PONumber=$order_no");
        mt_list_hyperlink($_SERVER['PHP_SELF'], _("Enter &Another Purchase Order"), "NewOrder=yes");
        mt_list_link(_("Select An &Outstanding Purchase Order"),"purchasing/inquiry/po_search.php");

        row_end();
        box_footer();
        box_end();
    }
    private function grn_finish(){
        $trans_no = intval($_GET['AddedGRN']);

        box_start();
        row_start();
        if( is_numeric($trans_no) ){

            $trans_type = ST_SUPPRECEIVE;
            display_notification_centered(_("Direct Purchase Invoice has been entered"));

//             display_note(get_trans_view_str($trans_type, $trans_no, _("&View this GRN")), 0);
            col_start(6);
            mt_list_start('Printing', null, 'red');

            mt_list_tran_view(_("View this GRN"),$trans_type, $trans_no);
            $clearing_act = get_company_pref('grn_clearing_act');
            if ($clearing_act){
//                 display_note(get_gl_view_str($trans_type, $trans_no, _("View the GL Journal Entries for this Delivery")), 1);
                mt_list_gl_view(_("View the GL Journal Entries for this Delivery"),$trans_type, $trans_no);
            }

            col_start(6);
            mt_list_start('Actions', '', 'blue');
            // not yet
            //	display_note(print_document_link($trans_no, _("&Print This GRN"), true, $trans_type), 0, 1);

            mt_list_link( _("Entry purchase &invoice for this receival"), "purchasing/supplier_invoice.php?New=1");

//             hyperlink_params(site_url()."/admin/attachments.php", _("Add an Attachment"), "filterType=$trans_type&trans_no=$trans_no");
            mt_list_link( _("Add an Attachment"), "admin/attachments.php?filterType=$trans_type&trans_no=$trans_no");

//             hyperlink_params($_SERVER['PHP_SELF'], _("Enter &Another GRN"), "NewGRN=Yes");
            mt_list_link( _("Enter &Another GRN"), $_SERVER['PHP_SELF']."?NewGRN=Yes");

            row_end();
//             mt_list_tran_view(_("View this Invoice"),$trans_type, $trans_no);
//             mt_list_print(_("&Print This Invoice"),$trans_type,$trans_no);



//             mt_list_link( _("Enter &Another Direct Invoice"), $_SERVER['PHP_SELF']."?NewInvoice=Yes");
        }

        box_footer();
        box_end();
    }
    private function pi_finish(){
        $trans_no = $_GET['AddedPI'];

        box_start();
        row_start();


        if( is_numeric($trans_no) ){
            $trans_type = ST_SUPPINVOICE;
            display_notification(_("Direct Purchase Invoice has been entered"));

            col_start(6);
            mt_list_start('Printing', null, 'red');
            mt_list_tran_view(_("View this Invoice"),$trans_type, $trans_no);
//             display_note(get_trans_view_str($trans_type, $trans_no, _("&View this Invoice")), 0);

            // not yet
            // 	display_note(print_document_link($trans_no, _("&Print This Invoice"), true, $trans_type), 0, 1);
//             display_note(print_document_link($trans_no, _("&Print This Invoice"), true, $trans_type),1);
            mt_list_print(_("&Print This Invoice"),$trans_type,$trans_no);

//             display_note(get_gl_view_str($trans_type, $trans_no, _("View the GL Journal Entries for this Invoice")), 1);
            mt_list_gl_view(_("View the GL Journal Entries for this Invoice"),$trans_type, $trans_no);

            col_start(6);
            mt_list_start('Actions', '', 'blue');
//             hyperlink_params("purchasing/supplier_payment.php", _("Entry supplier &payment for this invoice"), "PInvoice=".$trans_no);
            mt_list_link( _("Entry supplier &payment for this invoice"), "purchasing/supplier_payment.php?PInvoice=$trans_no");

//             hyperlink_params("admin/attachments.php", _("Add an Attachment"), "filterType=$trans_type&trans_no=$trans_no");
            mt_list_link( _("Add an Attachment"), "admin/attachments.php?filterType=$trans_type&trans_no=$trans_no");

//             hyperlink_params($_SERVER['PHP_SELF'], _("Enter &Another Direct Invoice"), "NewInvoice=Yes");
            mt_list_link( _("Enter &Another Direct Invoice"), $_SERVER['PHP_SELF']."?NewInvoice=Yes");
        }
        row_end();
        box_footer();
        box_end();

    }
    private function check_input_get(){

        if (isset($_GET['AddedID'])) {
            $this->order_finish();
            display_footer_exit();
        } elseif (isset($_GET['AddedGRN'])) {
            $this->grn_finish();
            display_footer_exit();
        } elseif (isset($_GET['AddedPI'])) {
            $this->pi_finish();
            display_footer_exit();
        }
    }
    private function check_submit(){

        $id = find_submit('Delete');

        if ($id != -1)
            handle_delete_item($id);

        if (isset($_POST['Commit'])){
            handle_commit_order();
        }
        if (isset($_POST['UpdateLine']))
            handle_update_item();

        if (isset($_POST['EnterLine']))
            handle_add_new_item();

        if (isset($_POST['CancelOrder']))
            handle_cancel_po();

        if (isset($_POST['CancelUpdate']))
            unset_form_variables();

        if (isset($_POST['CancelUpdate']) || isset($_POST['UpdateLine'])) {
            line_start_focus();
        }
    }
}