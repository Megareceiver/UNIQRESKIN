<?php

class SupplierManageSupplier
{
    function __construct()
    {

    }

    var $id = -1;
    function index(){
        global $Ajax;
        start_form();
        box_start("");
        if (db_has_suppliers()){
            row_start();
            col_start(9);
            supplier_list_bootstrap(_("Select a supplier"), 'supplier_id', null,_('New supplier'), true, check_value('show_inactive'));
            col_start(3);
            bootstrap_set_label_column(6);
            check_bootstrap(_("Show inactive"), 'show_inactive', null, true);
            col_end();
            row_end();
            if (get_post('_show_inactive_update')) {
                $Ajax->activate('supplier_id');
                set_focus('supplier_id');
            }
            if (get_post('supplier_id')) {
                set_focus('supp_name');
            }
        }
        else
        {
            hidden('supplier_id', get_post('supplier_id'));
        }

        if (!$this->id)
            unset($_POST['_tabs_sel']); // force settings tab for new customer

        tabs_bootstrap('tabs', array(
            'settings' => array(_('&General settings'), $this->id),
            'contacts' => array(_('&Contacts'), $this->id),
            'transactions' => array(_('&Transactions'), $this->id),
            'orders' => array(_('Purchase &Orders'), $this->id),
        ));

        switch (get_post('_tabs_sel')) {
            default:
            case 'settings':
                $this->settings($this->id);
                break;
            case 'contacts':
                $contacts = new contacts('contacts', $this->id, 'supplier');
                $contacts->show();
                break;
            case 'transactions':
                $_GET['supplier_id'] = $this->id;
                $_GET['popup'] = 1;
                include_once(ROOT."/purchasing/inquiry/supplier_inquiry.php");
                break;
            case 'orders':
                $_GET['supplier_id'] = $this->id;
                $_GET['popup'] = 1;
                include_once(ROOT."/purchasing/inquiry/po_search_completed.php");
                break;
        };

        tabbed_content_end();
        hidden('popup', @$_REQUEST['popup']);
//         row_end();
        box_footer_start();
        div_start('controls');
        switch (get_post('_tabs_sel')) {
            default:
            case 'settings':
                if ( $this->id ){
                    submit('submit', 'Update Supplier', true, 'Update supplier data', @$_REQUEST['popup'] ? true : 'default', $icon=NULL);
//             		submit_center_first('submit', _("Update Supplier"), _('Update supplier data'), );
//                     submit_center_first('submit', _("Update Supplier"), _('Update supplier data'), false);
                    submit_return('select', get_post('supplier_id'), _("Select this supplier and return to document entry."));
                    submit_center_last('delete', _("Delete Supplier"), _('Delete supplier data if have been never used'), true);
                }
                else
                {
                    submit('submit', 'Add New Supplier Details', true, null, 'default', $icon=NULL);
//                     submit_center('submit', _("Add New Supplier Details"), true, '', 'default');
                }
                break;
            case 'contacts':
                $contacts->_bottom_controls();
                break;

        };
        div_end();
        box_footer_end();


        box_end();
        end_form();
    }

    function settings(&$supplier_id){
        global $Ajax;
        $Ajax->activate('_page_body');

        $_POST['gst'] = 0;
        if ($supplier_id) {
            //SupplierID exists - either passed when calling the form or from the form itself
            $myrow = get_supplier($_POST['supplier_id']);

            $_POST['supp_name'] = $myrow["supp_name"];
            $_POST['supp_ref'] = $myrow["supp_ref"];
            $_POST['address']  = $myrow["address"];
            $_POST['supp_address']  = $myrow["supp_address"];

            $_POST['gst_no']  = $myrow["gst_no"];
            $_POST['website']  = $myrow["website"];
            $_POST['supp_account_no']  = $myrow["supp_account_no"];
            $_POST['bank_account']  = $myrow["bank_account"];
            $_POST['dimension_id']  = $myrow["dimension_id"];
            $_POST['dimension2_id']  = $myrow["dimension2_id"];
            $_POST['curr_code']  = $myrow["curr_code"];
            $_POST['payment_terms']  = $myrow["payment_terms"];
            $_POST['credit_limit']  = price_format($myrow["credit_limit"]);
            $_POST['tax_group_id'] = $myrow["tax_group_id"];
            $_POST['tax_included'] = $myrow["tax_included"];
            $_POST['payable_account']  = $myrow["payable_account"];
            $_POST['purchase_account']  = $myrow["purchase_account"];
            $_POST['payment_discount_account'] = $myrow["payment_discount_account"];
            $_POST['notes']  = $myrow["notes"];
            $_POST['inactive'] = $myrow["inactive"];


            $_POST['supplier_tax_id'] = $myrow["supplier_tax_id"];

            if( $myrow["supplier_tax_id"] ){
                $_POST['gst'] = 1;
            }
            $_POST['industry_code'] = $myrow["industry_code"];
            $_POST['self_bill'] = $myrow["self_bill"];
            $_POST['self_bill_approval_ref'] = $myrow["self_bill_approval_ref"];
            // 	 	if( !isset($_POST['valid_gst']) ){
            // 	 		$_POST['valid_gst'] = $myrow["valid_gst"];
            // 	 	}


        } else {
            $_POST['supp_name'] = $_POST['supp_ref'] = $_POST['address'] = $_POST['supp_address'] = $_POST['tax_group_id'] = $_POST['website'] = $_POST['supp_account_no'] = $_POST['notes'] = '';
            $_POST['dimension_id'] = 0;
            $_POST['dimension2_id'] = 0;
            $_POST['tax_included'] = 0;
            $_POST['sales_type'] = -1;
            $_POST['gst_no'] = $_POST['bank_account'] = '';
            $_POST['payment_terms']  = '';
            $_POST['credit_limit'] = price_format(0);

            $company_record = get_company_prefs();
            $_POST['curr_code']  = $company_record["curr_default"];
            $_POST['payable_account'] = $company_record["creditors_act"];
            $_POST['purchase_account'] = ''; // default/item's cogs account
            $_POST['payment_discount_account'] = $company_record['pyt_discount_act'];
            $_POST['self_bill'] = false;
            $_POST['self_bill_approval_ref'] = null;
            // 		$_POST['valid_gst'] = 0;
        }
        if( isset($_POST['_valid_gst_update'] ) ){
            $_POST['valid_gst'] = input_post('valid_gst');
        } else if ( $supplier_id ){
            $_POST['valid_gst'] = $myrow["valid_gst"];
        }

        $tax_disable = false;
        if( isset($_POST['valid_gst']) && $_POST['valid_gst'] ){
            $_POST['gst'] = 1;
            $_POST['supplier_tax_id'] = 16;
            $tax_disable = true;
        }


        row_start(null,'style="width:100%; margin-left:0;"');
        col_start(6);
        bootstrap_set_label_column(NULL);
        fieldset_start("Basic Data");
            input_text_bootstrap("Supplier Name", 'supp_name');
            input_text_bootstrap("Short Name", 'supp_ref');
            input_text_bootstrap("GSTNo", 'gst_no');
            input_text_bootstrap("Website", 'Website');


        if ($supplier_id && !is_new_supplier($supplier_id) && (key_in_foreign_table($_POST['supplier_id'], 'supp_trans', 'supplier_id') ||
            key_in_foreign_table($_POST['supplier_id'], 'purch_orders', 'supplier_id')))
        {
            input_label_bootstrap('Supplier\'s Currency','curr_code');
            hidden('curr_code', $_POST['curr_code']);
        } else {
            currency_bootstrap('Supplier\'s Currency','curr_code');
        }

        $disable = null;
        $company = get_company_pref();
        if( !isset($company['gst_no']) ||  trim($company['gst_no']) =='' ){
            $disable = ' disabled="disabled " ';
        }

        //tax_groups_list_row(_("Tax Group:"), 'tax_group_id', null);
        ///radio///
        if( !isset($_POST['supplier_tax_id']) ){
            $_POST['supplier_tax_id'] = null;
        }

        radios_bootstrap('GST by', 'gst', input_post('gst'), $submit_on_change = true, array(
            'Product Setting',
            'Supplier'
        ));
        if (input_post('gst') == 1) {
            gst_list_bootstrap('Supplier GST', 'supplier_tax_id');
        }

        if( config_ci('kastam') ){
            input_text_bootstrap("Our Customer No", 'supp_account_no');
            input_date_bootstrap('Last Verified Date','last_verifile');
            check_bootstrap('Is Valid GST Reg','valid_gst',true);
        } else {
            hidden('supp_account_no');
            hidden('last_verifile');
            hidden('valid_gst');
        }

        fieldset_start("Basic Data");
            input_text_bootstrap("Bank Name / Account", 'bank_account');
            input_money('Credit Limit','credit_limit');
            payment_terms_bootstrap('Payment Terms','payment_terms');

            check_bootstrap('Prices contain tax included','tax_included');
        //industry_code_list_row(_("Industry Code:"), 'industry_code', $_POST['industry_code']);
//         table_section_title(_("Purchasing"));
//         text_row(_("Bank Name/Account:"), 'bank_account', null, 42, 40);
//         amount_row(_("Credit Limit:"), 'credit_limit', null);
//         payment_terms_list_row(_("Payment Terms:"), 'payment_terms', null);
        //
        // tax_included option from supplier record is used directly in update_average_cost() function,
        // therefore we can't edit the option after any transaction waas done for the supplier.
        //
        //if (is_new_supplier($supplier_id))
//         check_row(_("Prices contain tax included:"), 'tax_included');
        //else {
        //	hidden('tax_included');
        //	label_row(_("Prices contain tax included:"), $_POST['tax_included'] ? _('Yes') : _('No'));
        //}

        col_start(6);
        $dim = get_company_pref('use_dimension');
        if ($dim >= 1)
        {
            fieldset_start("Department");
            dimensions_bootstrap(_("Department")." 1", 'dimension_id', null, true, " ", false, 1);
            if ($dim > 1){
                dimensions_bootstrap(_("Department")." 2", 'dimension2_id', null, true, " ", false, 2);
            }

        }
        if ($dim < 1)
            hidden('dimension_id', 0);
        if ($dim < 2)
            hidden('dimension2_id', 0);


        fieldset_start("Addresses");
            input_textarea_bootstrap('Mailing Address','address');
            input_textarea_bootstrap('Physical Address','supp_address');

        fieldset_start("General");
            input_textarea_bootstrap('General Notes','notes');

        if ($supplier_id) {
            yesno_bootstrap('Supplier status', 'inactive', null, _('Inactive'), _('Active'));
        }

        if( config_ci('kastam') ){
            fieldset_start("Self Bill");
            input_text_bootstrap(_("Approval Ref:"), 'self_bill_approval_ref');
            check_bootstrap('Self Bill','self_bill');
        } else {
            hidden('self_bill_approval_ref');
            hidden('self_bill');
        }
        fieldset_start("Accounts");
        gl_accounts_bootstrap('Accounts Payable' ,'payable_account');
        gl_accounts_bootstrap('Purchase Account' ,'purchase_account',        null,false, false, _("Use Item Inventory/COGS Account"));
        gl_accounts_bootstrap('Purchase Discount','payment_discount_account');



        if (!$supplier_id) {
            col_start(12);

            fieldset_start("Contact Data");
            col_start(6);
            input_text_bootstrap(_("Phone Number"), 'phone');
            input_text_bootstrap(_("Secondary Phone Number:"), 'phone2');
            input_text_bootstrap(_("Contact Person"), 'contact');

            col_start(6);
            input_text_bootstrap(_("Fax Number"), 'fax');
            input_text_bootstrap(_("E-mail"), 'email');
            languages_bootstrap(_("Document Language"), 'rep_lang', null, _('System default'));
            col_end(true);
        } else {
            col_start(12);
            col_end(true);
        }
        col_end();
        row_end();

    }
}