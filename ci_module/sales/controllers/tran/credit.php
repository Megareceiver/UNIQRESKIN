<?php

class SalesTranCredit
{

    function __construct ()
    {
        $this->check_finish();
        $this->check_submit();
        
        $this->create_cart();
    }

    function index ()
    {}

    function form ()
    {
        start_form();
        hidden('cart_id');
        $this->credit_header();
        
        box_start('Items to Credit');
        $this->credit_items();
        
        
        box_start();
        $this->credit_options();
        
        box_footer_start();
        submit_icon('ProcessCredit', _("Process Credit Note"),"");
        submit_icon('Update', _("Update"),"save",_('Update credit value for quantities entered'));
        
//         submit('Update', _("Update"), true, _('Update credit value for quantities entered'), true);
//         submit('ProcessCredit', _("Process Credit Note"), true, '', 'default');
        box_footer_end();
        
        box_end();
        end_form();
    }
    
    private function credit_header(){
        $cart = $_SESSION['Items'];
        box_start(); row_start();
        col_start(3);
        bootstrap_set_label_column(4);
        input_label(_("Customer"), null, $cart->customer_name);
        input_label(_("Branch"), null, get_branch_name($cart->Branch));
        input_label(_("Currency"), null, $cart->customer_currency);

        
        col_start(5);
        bootstrap_set_label_column(5);
        //	if (!isset($_POST['ref']))
        //		$_POST['ref'] = $Refs->get_next(11);
        
        input_label(_("Crediting Invoice"), null, get_customer_trans_view_str(ST_SALESINVOICE, array_keys($cart->src_docs)));
        if ($_SESSION['Items']->trans_no==0) {
            //     		ref_cells(_("Reference"), 'ref', '', null, "class='tableheader2'");
            input_ref(_("Reference"), 'ref');
        } else {
            input_label(_("Reference"), null, $cart->reference);
        }
        
        
        if (!isset($_POST['ShipperID'])) {
            $_POST['ShipperID'] = $_SESSION['Items']->ship_via;
        }
        shippers_bootstrap("Shipping Company", "ShipperID");
        
        //	if (!isset($_POST['sales_type_id']))
        //	  $_POST['sales_type_id'] = $_SESSION['Items']->sales_type;
        //	label_cell(_("Sales Type"), "class='tableheader2'");
        //	sales_types_list_cells(null, 'sales_type_id', $_POST['sales_type_id']);
        
        col_start(4);
        input_label(_("Invoice Date"), null, $_SESSION['Items']->src_date);
        //         date_row(_("Credit Note Date"), 'CreditDate', '', $_SESSION['Items']->trans_no==0, 0, 0, 0, "class='tableheader2'");
        input_date_bootstrap(_("Credit Note Date"), 'CreditDate');
        
        bootstrap_set_label_column(NULL);
        row_end();
        box_end();
    }
    private function credit_items()
    {
        div_start('credit_items');
        start_table(TABLESTYLE, "width=80%");
        $th = array(_("Item Code"), _("Item Description"), _("Invoiced Quantity"), _("Units"),
        	_("Credit Quantity"), _("Price"), _("Discount %"), _("Total"));
        table_header($th);
    
        $k = 0; //row colour counter
    
        foreach ($_SESSION['Items']->line_items as $line_no=>$ln_itm) {
    		if ($ln_itm->quantity == $ln_itm->qty_done) {
    			continue; // this line was fully credited/removed
    		}
    		alt_table_row_color($k);
    
    
    		//	view_stock_status_cell($ln_itm->stock_id); alternative view
        	label_cell($ln_itm->stock_id);
    
    		text_cells(null, 'Line'.$line_no.'Desc', $ln_itm->item_description, 30, 50);
    		$dec = get_qty_dec($ln_itm->stock_id);
        	qty_cell($ln_itm->quantity, false, $dec);
        	label_cell($ln_itm->units);
    		amount_cells(null, 'Line'.$line_no, number_format2($ln_itm->qty_dispatched, $dec),
    			null, null, $dec);
        	$line_total =($ln_itm->qty_dispatched * $ln_itm->price * (1 - $ln_itm->discount_percent));
    
        	amount_cell($ln_itm->price);
        	percent_cell($ln_itm->discount_percent*100);
        	amount_cell($line_total);
        	end_row();
        }
    
        if (!check_num('ChargeFreightCost')) {
        	$_POST['ChargeFreightCost'] = price_format($_SESSION['Items']->freight_cost);
        }
    	$colspan = 7;
    	start_row();
    	label_cell(_("Credit Shipping Cost"), "colspan=$colspan align=right");
    	small_amount_cells(null, "ChargeFreightCost", price_format(get_post('ChargeFreightCost',0)));
    	end_row();
    
        $inv_items_total = $_SESSION['Items']->get_items_total_dispatch();
    
        $display_sub_total = price_format($inv_items_total + input_num($_POST['ChargeFreightCost']));
        label_row(_("Sub-total"), $display_sub_total, "colspan=$colspan align=right", "align=right");
    //TUANVT6
        $taxes = $_SESSION['Items']->get_taxes_new(null,input_num($_POST['ChargeFreightCost']));
        $tax_total = display_edit_tax_items_new($taxes, $colspan, $_SESSION['Items']->tax_included);
    
        $price = $inv_items_total + input_num('ChargeFreightCost');

        if( $_SESSION['Items']->tax_included != 1){
            $price+= $_SESSION['Items']->tax_total_dispatch;
        }
    //     $display_total = price_format(($inv_items_total + input_num('ChargeFreightCost') + $tax_total));
    
        label_row(_("Credit Note Total"), price_format($price) , "colspan=$colspan align=right", "align=right");
    
        end_table();
    	div_end();
    }
    private function credit_options() {
        global $Ajax;
    
        if (isset($_POST['_CreditType_update'])){
            $Ajax->activate('options');
        }
        div_start('options');
        row_start("justify-content-center");
        col_start(8);
//             start_table(TABLESTYLE2);
    
        credit_types(_("Credit Note Type"), 'CreditType',null, true);
//             credit_type_list_row(_("Credit Note Type"), 'CreditType', null, true);
    
        if ($_POST['CreditType'] == "Return")
        {
            /*if the credit note is a return of goods then need to know which location to receive them into */
            if (!isset($_POST['Location'])){
                $_POST['Location'] = $_SESSION['Items']->Location;
            }
                
//             locations_list_row(_("Items Returned to Location"), 'Location', $_POST['Location']);
            locations_bootstrap(_("Items Returned to Location"), 'Location');
        }
        else
        {
            /* the goods are to be written off to somewhere */
            gl_all_accounts_list_row(_("Write off the cost of the items to"), 'WriteOffGLCode', null);
        }
    
        input_textarea(_("Memo"), "CreditText");
        row_end();
        div_end();
    }

    private function check_submit ()
    {
        if (isset($_POST['ProcessCredit']) && can_process()) {
            
            $new_credit = ($_SESSION['Items']->trans_no == 0);
            
            if (! isset($_POST['WriteOffGLCode']))
                $_POST['WriteOffGLCode'] = 0;
            
            $this->copy_to_cart();
            if ($new_credit)
                new_doc_date($_SESSION['Items']->document_date);
                
                // die('begin write trans line 204');
            $credit_no = $_SESSION['Items']->write($_POST['WriteOffGLCode']);

            if ($credit_no == - 1) {
                display_error(_("The entered reference is already in use."));
                set_focus('ref');
            } elseif ($credit_no) {
                processing_end();
                if ($new_credit) {
                    meta_forward($_SERVER['PHP_SELF'], "AddedID=$credit_no");
                } else {
                    meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$credit_no");
                }
            }
        }
        
        // -----------------------------------------------------------------------------
        
        if (isset($_POST['Location'])) {
            $_SESSION['Items']->Location = $_POST['Location'];
        }
        
        if (get_post('Update')){
            global $Ajax;
            $Ajax->activate('credit_items');
        }
    }

    private function check_finish ()
    {
        if (isset($_GET['AddedID'])) {
            $credit_no = $_GET['AddedID'];
            $this->update_credit_finish($credit_no);
            display_footer_exit();
        } elseif (isset($_GET['UpdatedID'])) {
            $credit_no = $_GET['UpdatedID'];
            $this->update_credit_finish($credit_no,false);
            display_footer_exit();
        } else
            check_edit_conflicts();
    }
    private function update_credit_finish($tran_no=0,$add_new = true){
        $trans_type = ST_CUSTCREDIT;
        if( $add_new ){
            display_notification(_("Credit Note has been processed"));
        } else {
            display_notification(_("Credit Note has been updated"));
        }
        
        
        box_start();
        row_start();
        col_start(12);
        mt_list_start('Actions', '', 'blue');
        
        mt_list_tran_view(_("&View this Credit Note"),$trans_type, $tran_no);
        
        $credit_pdf = print_document_link($tran_no . "-" . $trans_type, _("&Print This Credit Note"), true, $trans_type);
        mt_list($credit_pdf);
        $credit_pdf_email = print_document_link($tran_no . "-" . $trans_type, _("&Email This Credit Note"), true, $trans_type, false, "printlink", "", 1);
        mt_list($credit_pdf_email);
        
        mt_list_gl_view( _("View the GL &Journal Entries for this Credit Note"),$trans_type, $tran_no);
        
        if( $add_new ){
            mt_list_hyperlink("admin/attachments.php", _("Add an Attachment"),"filterType=$trans_type&trans_no=$tran_no");
        }
        
        row_end();
        box_footer();
        box_end();
    }

    private function create_cart ()
    {
        if (isset($_GET['InvoiceNumber']) && $_GET['InvoiceNumber'] > 0) {
            
            $_SESSION['Items'] = new Cart(ST_SALESINVOICE, 
                    $_GET['InvoiceNumber'], true);
            $this->copy_from_cart();
        } elseif (isset($_GET['ModifyCredit']) && $_GET['ModifyCredit'] > 0) {
            
            $_SESSION['Items'] = new Cart(ST_CUSTCREDIT, $_GET['ModifyCredit']);
            $this->copy_from_cart();
        } elseif (! processing_active()) {
            /*
             * This page can only be called with an invoice number for crediting
             */
            die(
                    _(
                            "This page can only be opened if an invoice has been selected for crediting."));
        } elseif (! check_quantities()) {
            display_error(
                    _(
                            "Selected quantity cannot be less than zero nor more than quantity not credited yet."));
        }
    }

    function copy_from_cart ()
    {
        $cart = &$_SESSION['Items'];
        $_POST['ShipperID'] = $cart->ship_via;
        $_POST['ChargeFreightCost'] = price_format($cart->freight_cost);
        $_POST['CreditDate'] = $cart->document_date;
        $_POST['Location'] = $cart->Location;
        $_POST['CreditText'] = $cart->Comments;
        $_POST['cart_id'] = $cart->cart_id;
        $_POST['ref'] = $cart->reference;
    }

    function copy_to_cart ()
    {
        $cart = &$_SESSION['Items'];
        $cart->ship_via = $_POST['ShipperID'];
        $cart->freight_cost = input_num('ChargeFreightCost');
        $cart->document_date = $_POST['CreditDate'];
        $cart->Location = (isset($_POST['Location']) ? $_POST['Location'] : "");
        $cart->Comments = $_POST['CreditText'];
        if ($_SESSION['Items']->trans_no == 0)
            $cart->reference = $_POST['ref'];
    }
}