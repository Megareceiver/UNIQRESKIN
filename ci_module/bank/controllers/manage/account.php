<?php

class BankManageAccount
{

    var $selected_id = 0;

    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");
        $this->listview();
        box_footer_show_active();

        box_start("Bank Account Detail",'fa-bank');
        $this->detail();


        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');

        box_footer_end();

        box_end();
        end_form();
    }

    private function listview()
    {
        $result = get_bank_accounts(check_value('show_inactive'));
        global $bank_account_types;

        start_table(TABLESTYLE);

        $th = array(
            _("Account Name"),
            _("Type"),
            _("Currency"),
            _("GL Account"),
            _("Bank"),
            _("Number"),
            _("Bank Address"),
            _("Dflt"),
            "edit" => array(
                'label' => "Edit",
                'width' => '5%'
            ),
            "delete" => array(
                'label' => 'Del',
                'width' => '5%'
            )
        );
        inactive_control_column($th);
        table_header($th);

        $k = 0;
        while ($myrow = db_fetch($result)) {

            alt_table_row_color($k);

            label_cell($myrow["bank_account_name"], "nowrap");

            $acc_type = "";
            if (isset($bank_account_types[$myrow["account_type"]])) {
                $acc_type = $bank_account_types[$myrow["account_type"]];
            }
            label_cell($acc_type, "nowrap");
            label_cell($myrow["bank_curr_code"], "nowrap");
            label_cell($myrow["account_code"] . " " . $myrow["account_name"], "nowrap");
            label_cell($myrow["bank_name"], "nowrap");
            label_cell($myrow["bank_account_number"], "nowrap");
            label_cell($myrow["bank_address"]);
            if ($myrow["dflt_curr_act"])
                label_cell(_("Yes"));
            else
                label_cell(_("No"));

            inactive_control_cell($myrow["id"], $myrow["inactive"], 'bank_accounts', 'id');
            edit_button_cell("Edit" . $myrow["id"], _("Edit"));
            delete_button_cell("Delete" . $myrow["id"], _("Delete"));
            end_row();
        }

//         inactive_control_row($th);
        end_table(1);
    }

    private function detail()
    {
        global $bank_account_types;

        row_start('justify-content-md-center');
        col_start(8,"col-md-8 col-md-offset-2");

        $is_used = $this->selected_id != - 1 && key_in_foreign_table($this->selected_id, 'bank_trans', 'bank_act');

        if ($this->selected_id != - 1) {
            if ( $this->mode == 'Edit') {
                $myrow = get_bank_account($this->selected_id);
                $_POST['account_code'] = $myrow["account_code"];
                $_POST['account_type'] = $myrow["account_type"];
                $_POST['bank_name'] = $myrow["bank_name"];
                $_POST['bank_account_name'] = $myrow["bank_account_name"];
                $_POST['bank_account_number'] = $myrow["bank_account_number"];
                $_POST['bank_address'] = $myrow["bank_address"];
                $_POST['BankAccountCurrency'] = $myrow["bank_curr_code"];
                $_POST['dflt_curr_act'] = $myrow["dflt_curr_act"];
            }
            hidden('selected_id', $this->selected_id);
            hidden('account_code');
            hidden('account_type');
            hidden('BankAccountCurrency', $_POST['BankAccountCurrency']);
            set_focus('bank_account_name');
        }

        input_text_bootstrap(_("Bank Account Name"), 'bank_account_name');

        if ($is_used) {
            $account_type = isset($bank_account_types[$_POST['account_type']]) ? $bank_account_types[$_POST['account_type']] : NULL;
            input_label_bootstrap(_("Account Type"), null, $account_type);
        } else {
            bank_account_types(_("Account Type"), 'account_type');
        }
        if ($is_used) {
            input_label_bootstrap(_("Bank Account Currency"), 'BankAccountCurrency');
        } else {
            currency_bootstrap(_("Bank Account Currency"), 'BankAccountCurrency');
        }

        yesno_bootstrap(_("Default currency account"), 'dflt_curr_act');

        if ($is_used)
            input_label_bootstrap(_("Bank Account GL Code"), 'account_code');
        else
            gl_accounts_bootstrap(_("Bank Account GL Code"), 'account_code');

        input_text_bootstrap(_("Bank Name"), 'bank_name');
        input_text_bootstrap(_("Bank Account Number"), 'bank_account_number');
        input_textarea_bootstrap(_("Bank Address"), 'bank_address');

        col_end();
        row_end();
    }
}