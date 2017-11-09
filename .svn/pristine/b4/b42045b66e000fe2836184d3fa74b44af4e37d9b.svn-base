<?php

class GlManageQuickEntry
{

    var $selected_id, $selected_id2 = 0;

    var $mode, $mode2 = NULL;

    function __construct()
    {
        $this->actions();
    }

    function index()
    {
        start_form();
        box_start("");
        $this->listview();

        $app = &$_SESSION["App"];
        box_start("Quick Entry Detail");
        $this->detail();

        // box_footer_start();
        // submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        // box_footer_end();

        if ($this->selected_id != - 1) {
            $this->entries();
        }

        box_end();
        end_form();
    }

    private function actions()
    {
        global $Ajax;
        if (list_updated('bal_type') || list_updated('type')) {
            $Ajax->activate('qe');
        }

        if (list_updated('actn'))
            $Ajax->activate('edit_line');
    }

    private function listview()
    {
        global $quick_entry_types;

        $result = get_quick_entries();

        start_table(TABLESTYLE);
        $th = array(
            _("Description"),
            _("Type"),
            "edit" => array(
                'label' => NULL,
                'width' => '5%'
            ),
            "delete" => array(
                'label' => NULL,
                'width' => '5%'
            )
        );
        table_header($th);

        $k = 0;
        while ($myrow = db_fetch($result)) {
            alt_table_row_color($k);
            $type_text = $quick_entry_types[$myrow["type"]];
            label_cell($myrow['description']);
            label_cell($type_text);
            edit_button_cell("Edit" . $myrow["id"], _("Edit"));
            delete_button_cell("Delete" . $myrow["id"], _("Delete"));
            end_row();
        }

        end_table(1);
    }

    private function detail()
    {
        div_start('qe');
        row_start('justify-content-md-center');
        col_start(8);

        if ($this->selected_id != - 1) {
            if ($this->mode == 'Edit') {

                $myrow = get_quick_entry($this->selected_id);

                $_POST['id'] = $myrow["id"];
                $_POST['description'] = $myrow["description"];
                $_POST['type'] = $myrow["type"];
                $_POST['base_desc'] = $myrow["base_desc"];
                $_POST['bal_type'] = $myrow["bal_type"];
                $_POST['base_amount'] = $myrow["bal_type"] ? $myrow["base_amount"] : price_format($myrow["base_amount"]);
            }
            hidden('selected_id', $this->selected_id);
        }

        input_text_bootstrap(_("Description") . ':', 'description');

        quick_entry_types(_("Entry Type") . ':', 'type', null, true);

        if (get_post('type') == QE_JOURNAL) {
            yesno_bootstrap(_("Balance Based"), 'bal_type', null, _("Yes"), _("No"), true);
        }

        if (get_post('type') == QE_JOURNAL && get_post('bal_type') == 1) {
            yesno_bootstrap(_("Period"), 'base_amount', null, _("Monthly"), _("Yearly"));
            gl_accounts_bootstrap(_("Account"), 'base_desc', null, true);
        } else {
            input_text_bootstrap(_("Base Amount Description"), 'base_desc', 50, 60, '');
            input_money(_("Default Base Amount"), 'base_amount');
        }
        col_end();
        row_end();

//         box_form_end();


        div_end();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');
        box_footer_end();

    }

    private function entries()
    {
        global $quick_actions;
        box_start(_("Quick Entry Lines") . " - " . $_POST['description']);



        start_table(TABLESTYLE2);
        $dim = get_company_pref('use_dimension');
        if ($dim == 2)
            $th = array(
                _("Post"),
                _("Account/Tax Type"),
                _("Amount"),
                _("Department"),
                _("Department") . " 2",
                "edit" => array(
                    'label' => NULL,
                    'width' => '5%'
                ),
                "delete" => array(
                    'label' => NULL,
                    'width' => '5%'
                )
            );
        elseif ($dim == 1)
            $th = array(
                _("Post"),
                _("Account/Tax Type"),
                _("Amount"),
                _("Department"),
                "edit" => array(
                    'label' => NULL,
                    'width' => '5%'
                ),
                "delete" => array(
                    'label' => NULL,
                    'width' => '5%'
                )
            );
        else
            $th = array(
                _("Post"),
                _("Account/Tax Type"),
                _("Amount"),
                "edit" => array(
                    'label' => NULL,
                    'width' => '5%'
                ),
                "delete" => array(
                    'label' => NULL,
                    'width' => '5%'
                )
            );

        table_header($th);
        $k = 0;
        $result = get_quick_entry_lines($this->selected_id);
        while ($myrow = db_fetch($result)) {
            alt_table_row_color($k);

            label_cell($quick_actions[$myrow['action']]);

            $act_type = strtolower(substr($myrow['action'], 0, 1));

            if ($act_type == 't') {
                label_cells($myrow['tax_name'], '');
            } else {
                label_cell($myrow['dest_id'] . ' ' . $myrow['account_name']);
                if ($act_type == '=')
                    label_cell('');
                elseif ($act_type == '%')
                    label_cell(number_format2($myrow['amount'], user_exrate_dec()), "nowrap align=right ");
                else
                    amount_cell($myrow['amount']);
            }
            if ($dim >= 1)
                label_cell(get_dimension_string($myrow['dimension_id'], true));
            if ($dim > 1)
                label_cell(get_dimension_string($myrow['dimension2_id'], true));
            edit_button_cell("BEd" . $myrow["id"], _("Edit"));
            delete_button_cell("BDel" . $myrow["id"], _("Delete"));
            end_row();
        }
        end_table(1);

        div_start('edit_line');


        if ($this->selected_id2 != - 1) {
            if ($this->mode2 == 'BEd') {
                // editing an existing status code
                $myrow = get_quick_entry_line($this->selected_id2);

                $_POST['id'] = $myrow["id"];
                $_POST['dest_id'] = $myrow["dest_id"];
                $_POST['actn'] = $myrow["action"];
                $_POST['amount'] = $myrow["amount"];
                $_POST['dimension_id'] = $myrow["dimension_id"];
                $_POST['dimension2_id'] = $myrow["dimension2_id"];
            }
        }

        row_start();
        col_start(8,'class="col-md-offset-2"');
        quick_actions( "Posted", 'actn', null, true);


        $actn = strtolower(substr($_POST['actn'], 0, 1));

        if ($actn == 't') {
            // item_tax_types_list_row(_("Item Tax Type").":",'dest_id', null);
            tax_types(_("Tax Type") . ":", 'dest_id', null);
        } else {
            gl_accounts_bootstrap(_("Account") . ":", 'dest_id', null, $_POST['type'] == QE_DEPOSIT || $_POST['type'] == QE_PAYMENT);
            if ($actn != '=') {
                if ($actn == '%')
                    small_amount_row( "Part", 'amount', price_format(0), null, "%", user_exrate_dec());
                else
                    amount_row( "Amount", 'amount', price_format(0));
            }
        }
        if ($dim >= 1)
            dimensions_bootstrap( "Department", 'dimension_id', null, true, " ", false, 1);
        if ($dim > 1)
            dimensions_bootstrap( "Department 2", 'dimension2_id', null, true, " ", false, 2);


        if ($dim < 2)
            hidden('dimension2_id', 0);
        if ($dim < 1)
            hidden('dimension_id', 0);
        col_end();
        row_end();
        div_end();

        hidden('selected_id', $this->selected_id);
        hidden('selected_id2', $this->selected_id2);

//         submit_add_or_update_center2($this->selected_id2 == - 1, '', true);
        box_footer_start();
        submit_add_or_update_center($this->selected_id2 == - 1, '', 'both');
        box_footer_end();
    }
}