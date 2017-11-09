<?php

class CustomerManagerCustomer
{

    var $customer_id = 0;

    function __construct()
    {
        $this->db = get_instance()->db;
        $this->input = get_instance()->input;
        $this->datatable = module_control_load('datatable', 'html');
        $this->bootstrap = get_instance()->bootstrap;
    }

    function delete()
    {
        $cancel_delete = 0;
        // PREVENT DELETES IF DEPENDENT RECORDS IN 'debtor_trans'
        if (key_in_foreign_table($this->customer_id, 'debtor_trans', 'debtor_no')) {
            $cancel_delete = 1;
            display_error(_("This customer cannot be deleted because there are transactions that refer to it."));
        } else {
            if (key_in_foreign_table($this->customer_id, 'sales_orders', 'debtor_no')) {
                $cancel_delete = 1;
                display_error(_("Cannot delete the customer record because orders have been created against it."));
            } else {
                if (key_in_foreign_table($this->customer_id, 'cust_branch', 'debtor_no')) {
                    $cancel_delete = 1;
                    display_error(_("Cannot delete this customer because there are branch records set up against it."));
                }
            }
        }

        if ($cancel_delete == 0) { // ie not cancelled the delete as a result of above tests

            delete_customer($this->customer_id);

            display_notification(_("Selected customer has been deleted."));
            unset($_POST['customer_id']);
            $selected_id = '';
            $Ajax->activate('_page_body');
        } // end if Delete Customer
    }

    /*
     * Form View
     */
    function form()
    {
        global $Ajax;
        // $this->customer_id = $_POST['customer_id'] = 140;
        // $_POST['_tabs_sel'] = 'transactions';

        start_form();
        box_start();

        if (db_has_customers()) {
            row_start();
            col_start(8);

            customer_list_bootstrap('Select a customer', 'customer_id', null, $submit_on_change = true, $editkey = false, _('New customer'), check_value('show_inactive'));

            col_start(3);
            $this->bootstrap->label_column = 8;
            check_bootstrap(_("Show inactive"), 'show_inactive', null, true);

            if (get_post('_show_inactive_update')) {
                $Ajax->activate('customer_id');
                set_focus('customer_id');
            }
            // col_end(true);
            row_end();
        } else {
            hidden('customer_id');
        }

        if (! $this->customer_id || list_updated('customer_id')) {
            unset($_POST['_tabs_sel']); // force settings tab for new customer
            set_focus('show_inactive');
        }

        // row_start(null, 'style="padding: 15px;"');

        tabs_bootstrap('tabs', array(
            'settings' => array(
                _('&General settings'),
                $this->customer_id
            ),
            'contacts' => array(
                _('&Contacts'),
                $this->customer_id
            ),
            'transactions' => array(
                _('&Transactions'),
                $this->customer_id
            ),
            'orders' => array(
                _('Sales &Orders'),
                $this->customer_id
            )
        ));
        bootstrap_set_label_column(3);
        switch (get_post('_tabs_sel')) {
            default:
            case 'settings':
                $this->customer_settings($this->customer_id);
                break;
            case 'contacts':
                $contacts = new contacts('contacts', $this->customer_id, 'customer');
                $contacts->show();
                break;
            case 'transactions':
                $_GET['customer_id'] = $this->customer_id;
                $_GET['popup'] = 1;
                $inquiry_ci = module_control_load('inquiry/transactions', 'sales');
                div_start('transactions_div', $trigger = null, $non_ajax = false, 'style="width:100%;"');

                $inquiry_ci->popup();
                div_end();
                // include_once (ROOT . "/sales/inquiry/customer_inquiry.php");
                break;
            case 'orders':
                $_GET['customer_id'] = $this->customer_id;
                $_GET['popup'] = 1;
                div_start('orders_div', $trigger = null, $non_ajax = false, 'style="width:100%;"');
                include_once (ROOT . "/sales/inquiry/sales_orders_view.php");
                div_end();
                break;
        }
        tabbed_content_end();

        // div_end(); // end tab;
        // row_end();

        if( in_ajax() ){
            $Ajax->activate("_page_body");
        }
        box_footer_start();
        div_start('controls');
        hidden('popup', @$_REQUEST['popup']);
        switch (get_post('_tabs_sel')) {
            default:
            case 'settings':
                if (! $this->customer_id) {
                    submit('submit', _("Add New Customer"), true, '', 'default');
                } else {
                    submit_return('select', $this->customer_id, _("Select this customer and return to document entry."));

                    // submit_center_first('submit', _("Update Customer"), _('Update customer data'), @$_REQUEST['popup'] ? true : 'default');
//                     submit('submit', _("Update Customer"), _('Update customer data'), @$_REQUEST['popup'] ? true : 'default');
                    submit('submit', _("Update Customer"), _('Update customer data'),false);
                    submit('delete', _("Delete Customer"), _('Delete customer data if have been never used'), true);

                    // submit_center_last('delete', _("Delete Customer"), _('Delete customer data if have been never used'), true);
                }
                break;
            case 'contacts':
                $contacts->_bottom_controls();
                break;
            case 'transactions':

                // $_GET['customer_id'] = $this->customer_id;
                // $_GET['popup'] = 1;
                // include_once (ROOT . "/sales/inquiry/customer_inquiry.php");
                break;
            case 'orders':

                // $_GET['customer_id'] = $this->customer_id;
                // $_GET['popup'] = 1;
                // include_once (ROOT . "/sales/inquiry/sales_orders_view.php");
                break;
        }

        div_end(); // controls
        box_footer_end();

        box_end();
        end_form();
    }

    private function customer_settings()
    {
        global $SysPrefs, $auto_create_branch, $ci;

        $selected_id = $this->customer_id;

        if (! $selected_id) {
            if (list_updated('customer_id') || ! isset($_POST['CustName'])) {
                $_POST['CustName'] = $_POST['cust_ref'] = $_POST['address'] = $_POST['tax_id'] = '';
                $_POST['dimension_id'] = 0;
                $_POST['dimension2_id'] = 0;
                $_POST['sales_type'] = - 1;
                $_POST['curr_code'] = get_company_currency();
                $_POST['credit_status'] = - 1;
                $_POST['payment_terms'] = $_POST['notes'] = '';

                $_POST['discount'] = $_POST['pymt_discount'] = percent_format(0);
                $_POST['credit_limit'] = price_format($SysPrefs->default_credit_limit());
                $_POST['gst'] = 1;
            }
        } else {
            $myrow = get_customer($selected_id);
            // bug($myrow);die;
            $_POST['CustName'] = $myrow["name"];
            $_POST['cust_ref'] = $myrow["debtor_ref"];
            $_POST['address'] = $myrow["address"];
            $_POST['tax_id'] = $myrow["tax_id"];
            $_POST['dimension_id'] = $myrow["dimension_id"];
            $_POST['dimension2_id'] = $myrow["dimension2_id"];
            $_POST['sales_type'] = $myrow["sales_type"];
            $_POST['curr_code'] = $myrow["curr_code"];
            $_POST['credit_status'] = $myrow["credit_status"];
            $_POST['payment_terms'] = $myrow["payment_terms"];
            $_POST['discount'] = percent_format($myrow["discount"] * 100);
            $_POST['pymt_discount'] = percent_format($myrow["pymt_discount"] * 100);
            $_POST['credit_limit'] = price_format($myrow["credit_limit"]);
            $_POST['notes'] = $myrow["notes"];
            $_POST['inactive'] = $myrow["inactive"];

            $_POST['industry_code'] = $myrow["industry_code"];
            $_POST['customer_tax_id'] = $myrow["customer_tax_id"];
            $_POST['gst_03_box_msic'] = $myrow["msic"];
        }
        row_start('justify-content-center', 'style="width:100%; margin-left:0;"');

        // $this->bootstrap->label_column = NULL;
        col_start(6);
        bootstrap_set_label_column(4);
        $this->bootstrap->fieldset_start("Name and Address");
        input_text_bootstrap("Customer Name", 'CustName', $_POST['CustName']);
        input_text_bootstrap("Customer Short Name:", 'cust_ref');
        input_textarea_bootstrap(_("Address"), 'address', $_POST['address']);
        input_text_bootstrap(_("GSTNo:"), 'tax_id');

        if (! $selected_id || is_new_customer($selected_id) || (! key_in_foreign_table($selected_id, 'debtor_trans', 'debtor_no') && ! key_in_foreign_table($selected_id, 'sales_orders', 'debtor_no'))) {
            currency_bootstrap(_("Customer's Currency"), 'curr_code');
        } else {
            input_label_bootstrap(_("Customer's Currency"), 'curr_code', $_POST['curr_code']);
            hidden('curr_code', $_POST['curr_code']);
        }
        sales_types_bootstrap(_("Sales Type/Price List:"), 'sales_type');

        $disable = null;

        $company = get_company_pref();
        if (! isset($company['gst_no']) || trim($company['gst_no']) == '') {
            $disable = ' disabled="disabled " ';
        }

        if (! isset($_POST['customer_tax_id'])) {
            $_POST['customer_tax_id'] = 0;
        }

        radios_bootstrap('GST by', 'gst', input_post('gst'), $submit_on_change = true, array(
            'Product Setting',
            'Customer'
        ));
        if (input_post('gst') == 1) {
            gst_list_bootstrap('Customer GST', 'customer_tax_id');
        }

        if ($selected_id) {
            yesno_bootstrap('Customer status', 'inactive');
        }

        $this->bootstrap->fieldset_end();

        col_start(6);
        $this->bootstrap->fieldset_start("Sales");
        input_percent(_("Discount Percent:"), 'discount');
        input_percent(_("Prompt Payment Discount Percent:"), 'pymt_discount');
        input_money(_("Credit Limit:"), 'credit_limit');
        payment_terms_bootstrap(_("Payment Terms:"), 'payment_terms');
        credit_status_bootstrap(_("Credit Status:"), 'credit_status');

        $dim = get_company_pref('use_dimension');
        if ($dim >= 1) {
            dimensions_bootstrap("Dimension 1", 'dimension_id');
        } else {
            hidden('dimension_id', 0);
        }

        if ($dim > 1) {
            dimensions_bootstrap("Dimension 2", 'dimension2_id');
        } else {
            hidden('dimension2_id', 0);
        }

        input_textarea_bootstrap("General Notes", 'notes');

        /*
         * using for Kastam
         * industry_code_list_row(_("Industry Code:"), 'gst_03_box', $_POST['msic']);
         */
        hidden('gst_03_box');
        if ($selected_id) {
            $edit_branches = anchor(site_url("/sales/manage/customer_branches.php") . "?debtor_no=" . $selected_id . (@$_REQUEST['popup'] ? '&popup=1' : ''), (@$_REQUEST['popup'] ? _("Select or Add") : _("Add or Edit")), 'class="bold"');
            input_label_bootstrap('Customer branches', null, $edit_branches);
        }
        $this->bootstrap->fieldset_end();
        row_end();

        if (! $selected_id && isset($auto_create_branch) && $auto_create_branch == 1) {
            col_start(12,NULL, false);
            fieldset_start("Branch");
            row_start();
            $this->bootstrap->col_start(6);
            input_text_bootstrap("Phone", 'phone');
            input_text_bootstrap("Secondary Phone Number", 'phone2');
            input_text_bootstrap("Fax Number", 'fax');
            input_text_addon_bootstrap(_("E-mail:"), 'email', null, 'email');
            sales_persons_bootstrap("Sales Person", 'salesman');
            $this->bootstrap->col_end();

            $this->bootstrap->col_start(6);
            locations_bootstrap("Inventory Location", 'location');
            shippers_bootstrap('Shipping Company', 'ship_via');
            sales_areas_bootstrap('Sales Area', 'area');
            tax_groups_bootstrap('Tax Group', 'tax_group_id');
            $this->bootstrap->col_end();
            row_end();
            fieldset_end();
            // $this->bootstrap->col_end();
        }

    }
}