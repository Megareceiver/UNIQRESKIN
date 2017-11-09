<?php
class ManageCurrency
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

        box_start("Currency Detail",'fa-dollar');
        $this->detail();

        box_footer_start();
        submit_add_or_update_center($this->selected_id == - 1, '', 'both');

        box_footer_end();

        box_end();
        end_form();
    }

    private function listview()
    {
        $company_currency = get_company_currency();

        $result = get_currencies(check_value('show_inactive'));
        start_table(TABLESTYLE);
        $th = array(_("Abbreviation"), _("Symbol"), _("Currency Name"),
            _("Hundredths name"), _("Country"), _("Auto update"), "", "");
        inactive_control_column($th);
        table_header($th);

        $k = 0; //row colour counter

        while ($myrow = db_fetch($result))
        {

            if ($myrow[1] == $company_currency)
            {
                start_row("class='currencybg'");
            }
            else
                alt_table_row_color($k);

            label_cell($myrow["curr_abrev"]);
            label_cell($myrow["curr_symbol"]);
            label_cell($myrow["currency"]);
            label_cell($myrow["hundreds_name"]);
            label_cell($myrow["country"]);
            label_cell(	$myrow[1] == $company_currency ? '-' :
            ($myrow["auto_update"] ? _('Yes') :_('No')), "align='center'");
            inactive_control_cell($myrow["curr_abrev"], $myrow["inactive"], 'currencies', 'curr_abrev');
            edit_button_cell("Edit".$myrow["curr_abrev"], _("Edit"));
            if ($myrow["curr_abrev"] != $company_currency)
                delete_button_cell("Delete".$myrow["curr_abrev"], _("Delete"));
            else
                label_cell('');
            end_row();

        } //END WHILE LIST LOOP

        end_table();
        if( !in_ajax() ){
            display_notification('The marked currency is the home currency which cannot be deleted.');
        }

    }

    private function detail()
    {
        row_start('justify-content-md-center');
        bootstrap_set_label_column(4);
        col_start(8);

        if ($this->selected_id != '')
        {
            if ( $this->mode == 'Edit') {
                //editing an existing currency
                $myrow = get_currency($this->selected_id);

                $_POST['Abbreviation'] = $myrow["curr_abrev"];
                $_POST['Symbol'] = $myrow["curr_symbol"];
                $_POST['CurrencyName']  = $myrow["currency"];
                $_POST['country']  = $myrow["country"];
                $_POST['hundreds_name']  = $myrow["hundreds_name"];
                $_POST['auto_update']  = $myrow["auto_update"];
            }
            hidden('Abbreviation');
            hidden('selected_id', $this->selected_id);
            input_label_bootstrap(_("Currency Abbreviation"), 'Abbreviation' );
        }
        else
        {
            $_POST['auto_update']  = 1;
            input_text_bootstrap(_("Currency Abbreviation"), 'Abbreviation');
        }

        input_text_bootstrap(_("Currency Symbol"), 'Symbol');
        input_text_bootstrap(_("Currency Name"), 'CurrencyName');
        input_text_bootstrap(_("Hundredths Name"), 'hundreds_name');
        input_text_bootstrap(_("Country"), 'country');
        check_bootstrap(_("Automatic exchange rate update"), 'auto_update', get_post('auto_update'));
//         end_table(1);

//         submit_add_or_update_center($selected_id == '', '', 'both');
        col_end();
        row_end();
    }
}