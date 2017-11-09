<?php
class GlManageAccount
{
    var $selected_id = 0;
    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        global $Ajax;

        start_form();
        box_start("");

        if (db_has_gl_accounts())
        {
            row_start('justify-content-md-center');

            col_start(6);
            gl_accounts_bootstrap(null, 'AccountList', null, false, false,
            _('New account'), true, check_value('show_inactive'));
            col_start(2);
            bootstrap_set_label_column(9);
            check_bootstrap( _("Show inactive:"), 'show_inactive', null, true);
            bootstrap_set_label_column(NULL);
            row_end();

            if (get_post('_show_inactive_update')) {
                $Ajax->activate('AccountList');
                set_focus('AccountList');
            }
        }

//         $this->listview();

//         box_start("");
        row_start('justify-content-md-center','style="padding-top:15px"');
        $this->detail();
        row_end();

        box_footer_start();
        if ($this->selected_id == "")
        {
            submit('add', _("Add Account"), true, '', 'default','save');
        }
        else
        {
            submit_center_first('update', _("Update Account"), '', 'default');
            submit_center_last('delete', _("Delete account"), '',true);
        }

//         submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        box_footer_end();

        box_end();
        end_form();
    }


    private function detail()
    {

        col_start(8,'class="col-md-offset-2"');

        if ($this->selected_id != "")
        {
            //editing an existing account
            $myrow = get_gl_account($this->selected_id);

            $_POST['account_code'] = $myrow["account_code"];
            $_POST['account_code2'] = $myrow["account_code2"];
            $_POST['account_name']	= $myrow["account_name"];
            $_POST['account_type'] = $myrow["account_type"];
            $_POST['inactive'] = $myrow["inactive"];

            $tags_result = get_tags_associated_with_record(TAG_ACCOUNT, $this->selected_id);
            $tagids = array();
            while ($tag = db_fetch($tags_result))
                $tagids[] = $tag['id'];
            $_POST['account_tags'] = $tagids;

            hidden('account_code', $_POST['account_code']);
            hidden('selected_account', $this->selected_id);

            input_label_bootstrap(_("Account Code:"), 'account_code');
        }
        else
        {
            if (!isset($_POST['account_code'])) {
                $_POST['account_tags'] = array();
                $_POST['account_code'] = $_POST['account_code2'] = '';
                $_POST['account_name']	= $_POST['account_type'] = '';
                $_POST['inactive'] = 0;
            }
            input_text_bootstrap(_("Account Code:"), 'account_code');
        }

        input_text_bootstrap(_("Account Code 2:"), 'account_code2');

        input_text_bootstrap(_("Account Name:"), 'account_name');

        gl_account_types(_("Account Group:"), 'account_type', null);

        tags_list(_("Account Tags:"), 'account_tags', 5, TAG_ACCOUNT, true);

        yesno_bootstrap(_("Account status:"), 'inactive');

        col_end();

    }
}