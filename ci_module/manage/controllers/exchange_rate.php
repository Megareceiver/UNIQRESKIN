<?php
class ManageExchangeRate
{

    var $selected_id = 0;
    var $mode = NULL;

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");

        if (!isset($_POST['curr_abrev']))
            $_POST['curr_abrev'] = get_company_pref('curr_default');

        row_start('justify-content-md-center');
        col_start(8);
        currency_bootstrap("Select a currency", 'curr_abrev',null,true);
        col_end();
        row_end();


        // if currency sel has changed, clear the form
        if ($_POST['curr_abrev'] != get_global_curr_code())
        {
            clear_data();
            $selected_id = "";
        }

        set_global_curr_code($_POST['curr_abrev']);

        if (is_company_currency($_POST['curr_abrev']))
        {

            display_notification(_("The selected currency is the company currency."), 2);
            display_notification(_("The company currency is the base currency so exchange rates cannot be set for it."), 1);
            box_footer_start();
            box_footer_end();
        }
        else
        {
            row_start(NULL,'style="padding-top:15px;"');
            $this->listview();
            row_end();

            box_start("");
            $this->detail();

            box_footer_start();
            submit_add_or_update_center($this->selected_id =="" , '', 'both');
            box_footer_end();


        }

        box_end();
        end_form();
    }


    function edit_link($row)
    {
      return button('Edit'.$row["id"], _("Edit"), true, ICON_EDIT);
    }

    function del_link($row)
    {
        return button('Delete'.$row["id"], _("Delete"), true, ICON_DELETE);
    }
    private function listview()
    {

        $sql = get_sql_for_exchange_rates();

        $cols = array(
            _("Date to Use From") => 'date',
            _("Exchange Rate") => 'rate',
            'Edit'=>array('insert'=>true, 'fun'=>'edit_link','width'=>'5%' ,'label'=>'Edit'),
            'Del'=>array('insert'=>true, 'fun'=>'del_link','width'=>'5%','label'=>'Del'),
        );
        $table =& new_db_pager('orders_tbl', $sql, $cols);
        $table->ci_control = $this;
//         $table->edit_link = ManageExchangeRate;

        if ($table->rec_count == 0)
            $table->ready = false;

        display_db_pager($table);
    }

    private function detail()
    {
        global $selected_id, $Ajax, $xr_providers, $dflt_xr_provider;


        fieldset_start('Exchange rates are entered against the company currency.');
        row_start('justify-content-md-center');
        col_start(6);



        $xchg_rate_provider = ((isset($xr_providers) && isset($dflt_xr_provider)) ? $xr_providers[$dflt_xr_provider] : 'ECB');


        if ($selected_id != "")
        {
            //editing an existing exchange rate

            $myrow = get_exchange_rate($selected_id);

            $_POST['date_'] = sql2date($myrow["date_"]);
            $_POST['BuyRate'] = maxprec_format($myrow["rate_buy"]);

            hidden('selected_id', $selected_id);
            hidden('date_', $_POST['date_']);
//             bug($myrow);
//             bug($_POST);
            input_label_bootstrap(_("Date to Use From:"), 'date_');
        }
        else
        {
            $_POST['date_'] = Today();
            $_POST['BuyRate'] = '';
            input_date_bootstrap( _("Date to Use From"), 'date_');
        }

        if ( isset($_POST['get_rate']) AND !post_edit('Edit') )
        {
            $_POST['BuyRate'] = maxprec_format(retrieve_exrate($_POST['curr_abrev'], $_POST['date_']));
            $Ajax->activate('BuyRate');
        }

        input_exc_rate('Exchange Rate','BuyRate',NULL);
//         amount_row(_("Exchange Rate:"), 'BuyRate', null, '',
//         submit('get_rate',_("Get"), false, _('Get current rate from') . ' ' . $xchg_rate_provider , true), 'max');


        col_end();
        row_end();
        fieldset_end();

    }
}