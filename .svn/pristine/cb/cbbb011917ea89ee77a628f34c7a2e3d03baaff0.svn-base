<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class SetupSystemDisplay
{

    function __construct()
    {}

    function index()
    {
        start_form();
        box_start("");

        $this->form();

        box_footer_start();
        submit('setprefs', _("Update"), true, '', 'default','save');
        box_form_end();
        box_end();
        end_form(2);
    }

    private function form()
    {
        bootstrap_set_label_column(5);

        // start_outer_table(TABLESTYLE2);

        // table_section(1);
        row_start();
        col_start(6);
        fieldset_start(_("Decimal Places"));

        numbers_list(_("Prices"), 'prices_dec', user_price_dec(), 0, 10);
        numbers_list(_("Amounts"), 'amount_dec', user_amount_dec(), 0, 10);
        numbers_list(_("Quantities"), 'qty_dec', user_qty_dec(), 0, 10);
        numbers_list(_("Exchange Rates"), 'rates_dec', user_exrate_dec(), 0, 10);
        numbers_list(_("Percentages"), 'percent_dec', user_percent_dec(), 0, 10);

        fieldset_start(_("Dateformat and Separators"));

        dateformats_list(_("Dateformat"), "date_format", user_date_format());

        dateseps_list(_("Date Separator"), "date_sep", user_date_sep());

        /*
         * The array $dateseps is set up in config.php for modifications
         * possible separators can be added by modifying the array definition by editing that file
         */

        thoseps_list(_("Thousand Separator"), "tho_sep", user_tho_sep());

        /*
         * The array $thoseps is set up in config.php for modifications
         * possible separators can be added by modifying the array definition by editing that file
         */

        decseps_list(_("Decimal Separator"), "dec_sep", user_dec_sep());

        /*
         * The array $decseps is set up in config.php for modifications
         * possible separators can be added by modifying the array definition by editing that file
         */
        if (! isset($_POST['language']))
            $_POST['language'] = $_SESSION['language']->code;

        fieldset_start(_("Language"));

        languages_bootstrap(_("Language"), 'language', $_POST['language']);

        col_start(6);
        fieldset_start(_("Miscellaneous"));

        check_bootstrap("Show hints for new users", 'show_hints', user_hints());

        check_bootstrap(_("Show GL Information"), 'show_gl', user_show_gl_info());

        check_bootstrap(_("Show Item Codes"), 'show_codes', user_show_codes());

        // themes_list_row(_("Theme:"), "theme", user_theme());

        /*
         * The array $themes is set up in config.php for modifications
         * possible separators can be added by modifying the array definition by editing that file
         */

        pagesizes_list(_("Page Size"), "page_size", user_pagesize());

        tab_list(_("Start-up Tab"), 'startup_tab', user_startup_tab());

        /*
         * The array $pagesizes is set up in config.php for modifications
         * possible separators can be added by modifying the array definition by editing that file
         */

        if (! isset($_POST['print_profile']))
            $_POST['print_profile'] = user_print_profile();

        print_profiles(_("Printing profile"), 'print_profile', null, _('Browser printing support'));

        check_bootstrap(_("Use popup window to display reports"), 'rep_popup', user_rep_popup(), false, _('Set this option to on if your browser directly supports pdf files'));

        check_bootstrap(_("Use icons instead of text links"), 'graphic_links', user_graphic_links(), false, _('Set this option to on for using icons instead of text links'));

        input_text(_("Query page size"), 'query_size', 5, 5, '', user_query_size());

        check_bootstrap(_("Remember last document date"), 'sticky_doc_date', sticky_doc_date(), false, _('If set document date is remembered on subsequent documents, otherwise default is current date'));

        col_end();
        row_end();
    }
}
