<?php

class CustomerManagerBranches
{

    function __construct()
    {
        // $this->db = get_instance()->db;
        // $this->input = get_instance()->input;
        $this->datatable = module_control_load('datatable', 'html');
    }

    function form()
    {
        start_form();
        box_start("");
        col_start(11);
        customer_list_bootstrap('Select a customer', 'customer_id', null, true, false, false);

        col_start(12, 'style="padding-top: 15px;"');
        $this->branches_list();
        col_end();
        box_end();

        box_start("Branches Detail");

        tabs_bootstrap('tabs', array(
            'settings' => array(
                _('&General settings'),
                $this->id != - 1
            ),
            'contacts' => array(
                _('&Contacts'),
                $this->id != - 1
            )
        ));

        switch (get_post('_tabs_sel')) {
            default:
            case 'settings':

                $this->branches_item($this->id);
                break;
            case 'contacts':
                $contacts = new contacts('contacts', $this->id, 'cust_branch');
                $contacts->show();
                break;
            case 'orders':
                break;
        }
        ;
        hidden('branch_code');
        hidden('selected_id', $this->id);

        tabbed_content_end();

        box_footer_start();
        div_start('controls');
        switch (get_post('_tabs_sel')) {
            case 'settings':
                submit_add_or_update_center($this->id == - 1, '', 'both');
                break;
            case 'contacts':
                $contacts->_bottom_controls();
                break;
        }
        div_end();
        box_footer_end();

        box_end();
        end_form();
    }

    /*
     * Table Actions
     */
    function edit_link($row, $cell = NULL)
    {
        return button("Edit" . $row["branch_code"], _("Edit"), '', ICON_EDIT);
    }

    private function branches_list()
    {
        $num_branches = db_customer_has_branches($_POST['customer_id']);

        $sql = get_sql_for_customer_branches();

        // ------------------------------------------------------------------------------------------------
        if ($num_branches) {
            $cols = array(
                'branch_code' => 'skip',
                _("Short Name"),
                _("Name"),
                _("Contact"),
                _("Sales Person"),
                _("Area"),
                _("Phone No"),
                _("Fax No"),
                _("E-mail") => 'email',
                _("Tax Group"),
                _("Inactive") => 'inactive',

                // array('fun'=>'inactive'),
                ' ' => array(
                    'insert' => true,
                    'fun' => 'select_link'
                ),
                array(
                    'insert' => true,
                    'fun' => 'edit_link'
                ),
                array(
                    'insert' => true,
                    'fun' => 'del_link'
                )
            );

            if (! @$_REQUEST['popup']) {
                $cols[' '] = 'skip';
            }
            $table = & new_db_pager('branch_tbl', $sql, $cols, 'cust_branch');
            $table->set_inactive_ctrl('cust_branch', 'branch_code');
            $table->ci_control = $this;
            // display_db_pager($table);
            db_table_responsive($table);
        }

        else
            display_note(_("The selected customer does not have any branches. Please create at least one branch."));
    }

    var $customer_id, $id = - 1;

    var $mode = NULL;

    function branches_item($branches_id = 0)
    {
        $_POST['email'] = "";
        if ($branches_id != - 1) {
            if ($this->mode == 'Edit' || ! isset($_POST['br_name'])) {
                // editing an existing branch
                $myrow = get_cust_branch($_POST['customer_id'], $_POST['branch_code']);

                set_focus('br_name');
                $_POST['branch_code'] = $myrow["branch_code"];
                $_POST['br_name'] = $myrow["br_name"];
                $_POST['br_ref'] = $myrow["branch_ref"];
                $_POST['br_address'] = $myrow["br_address"];
                $_POST['br_post_address'] = $myrow["br_post_address"];
                // $_POST['contact_name'] = $myrow["contact_name"];
                $_POST['salesman'] = $myrow["salesman"];
                $_POST['area'] = $myrow["area"];
                // $_POST['rep_lang'] =$myrow["rep_lang"];
                // $_POST['phone'] =$myrow["phone"];
                // $_POST['phone2'] =$myrow["phone2"];
                // $_POST['fax'] =$myrow["fax"];
                // $_POST['email'] =$myrow["email"];
                $_POST['tax_group_id'] = $myrow["tax_group_id"];
                $_POST['disable_trans'] = $myrow['disable_trans'];
                $_POST['default_location'] = $myrow["default_location"];
                $_POST['default_ship_via'] = $myrow['default_ship_via'];
                $_POST['sales_account'] = $myrow["sales_account"];
                $_POST['sales_discount_account'] = $myrow['sales_discount_account'];
                $_POST['receivables_account'] = $myrow['receivables_account'];
                $_POST['payment_discount_account'] = $myrow['payment_discount_account'];
                $_POST['group_no'] = $myrow["group_no"];
                $_POST['notes'] = $myrow["notes"];
            }
        } elseif ($this->mode != 'ADD_ITEM') { // end of if $SelectedBranch only do the else when a new record is being entered
            $myrow = get_default_info_for_branch($_POST['customer_id']);
            // $_POST['rep_lang'] = $myrow['rep_lang'];
            $num_branches = db_customer_has_branches($_POST['customer_id']);
            if (! $num_branches) {
                $_POST['br_name'] = $myrow["name"];
                $_POST['br_ref'] = $myrow["debtor_ref"];
                $_POST['contact_name'] = _('Main Branch');
                $_POST['br_address'] = $_POST['br_post_address'] = $myrow["address"];
            }
            $_POST['branch_code'] = "";
            if (! isset($_POST['sales_account']) || ! isset($_POST['sales_discount_account'])) {
                $company_record = get_company_prefs();

                // We use the Item Sales Account as default!
                // $_POST['sales_account'] = $company_record["default_sales_act"];
                $_POST['sales_account'] = $_POST['notes'] = '';
                $_POST['sales_discount_account'] = $company_record['default_sales_discount_act'];
                $_POST['receivables_account'] = $company_record['debtors_act'];
                $_POST['payment_discount_account'] = $company_record['default_prompt_payment_act'];
            }
        }

        bootstrap_set_label_column(5);
        col_start(6);
        hidden('popup', @$_REQUEST['popup']);
        fieldset_start('Name and Contact');
        input_text_bootstrap("Name", 'br_name');
        input_text_bootstrap("Short Name", 'br_ref');

        fieldset_start('Sales');
        sales_persons_bootstrap('Sales Person', 'salesman');
        sales_areas_bootstrap('Sales Area', 'area');
        sales_groups_bootstrap('Sales Group', 'group_no');
        locations_bootstrap('Inventory Location', 'default_location');
        shippers_bootstrap('Shipping Company', 'default_ship_via');
        tax_groups_bootstrap('Tax Group', 'tax_group_id');

        fieldset_start('GL Accounts');
        gl_accounts_bootstrap('Sales Account', 'sales_account', null, false, false, true);
        gl_accounts_bootstrap('Sales Discount', 'sales_discount_account');
        gl_accounts_bootstrap('Accounts Receivable', 'receivables_account', null, true);
        gl_accounts_bootstrap('Prompt Payment Discount', 'payment_discount_account', null, false, false, true);

        col_start(6);
        if ($this->id == - 1) {
            fieldset_start('General contact data');
            input_text_bootstrap("Contact Person", 'contact_name');
            input_text_bootstrap("Phone Number", 'phone');
            input_text_bootstrap("Secondary Phone Number", 'phone2');
            input_text_bootstrap("Fax Number", 'fax');
            input_text_bootstrap("E-mail", 'email');
            languages_bootstrap('Document Language', 'rep_lang', null, _("Customer default"));
        }

        fieldset_start('Addresses');
        input_textarea_bootstrap('Mailing Address', 'br_post_address');
        input_textarea_bootstrap('Billing Address', 'br_address');
        input_textarea_bootstrap('General Notes', 'notes');
        if ($this->id == - 1) {
            yesno_bootstrap('Disable this Branch', 'disable_trans');
        }

         fieldset_end();
        col_end();
    }
}