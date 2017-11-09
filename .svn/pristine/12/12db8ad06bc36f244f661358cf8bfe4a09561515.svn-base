<?php
/*
 * Show debug messages returned from an error on the page.
 * Debugging info level also determined by settings in PHP.ini
 * if $debug=1 show debugging info, dont show if $debug=0
 */
if (! isset($path_to_root) || isset($_GET['path_to_root']) ||
         isset($_POST['path_to_root']))
    die("Restricted access");

if (! ini_get('date.timezone'))
    ini_set('date.timezone', 'Europe/Berlin');
$error_logfile = dirname(__FILE__) . '/tmp/errors.log';
$debug = 0; // show sql on database errors
$show_sql = 0; // show all sql queries in page footer for debugging purposes
$go_debug = 1; // set to 1 for basic debugging, or 2 to see also backtrace
                 // after failure.


if ($go_debug > 0) {
    error_reporting(1);
    ini_set("display_errors", "On");
} else {
    error_reporting(E_USER_WARNING | E_USER_ERROR | E_USER_NOTICE);
    // ini_alter("error_reporting","E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE");
    ini_set("display_errors", "On");
}

if ($error_logfile != '') {
    ini_set("error_log", $error_logfile);
    ini_set("ignore_repeated_errors", "On");
    ini_set("log_errors", "On");
}

// Main Title
$app_title = "AccountantToday";

// Build for development purposes
$build_version = date("d.m.Y", filemtime("$path_to_root/CHANGELOG.txt"));

// Powered by
// $power_by = "A2000 Solutions Pte Ltd<br>(Approved Malaysia GST Software by
// ACCOUNTANT TODAY)";
// $power_url = "http://accountanttoday.net";
// $copyright = 'Copyright &copy; 2014 by '.$power_by;
// $theme = 'uniq365';
$theme = 'metronic';

/*
 * No check on edit conflicts. Maybe needed to be set to 1 in certains Windows
 * Servers
 */
$no_check_edit_conflicts = 0;

/*
 * Do not print zero lines amount of 0.00 in Sales Documents if service item. 1
 * = do not
 */
$no_zero_lines_amount = 1;

/* Use icon for editkey (=true) right of combobox. 1 = use, 0 = do not use */
$use_icon_for_editkey = 0;

/*
 * Creates automatic a default branch with contact. Value 0 do not create auto
 * branch
 */
$auto_create_branch = 1;

/* Save Report selections (a value > 0 means days to save. 0 = no save) */
$save_report_selections = 0;

/* use popup windows for views */
$use_popup_windows = 1;

/* use date picker for all date fields */
$use_date_picker = 1;

/* use Audit Trails in GL */
/*
 * This variable is deprecated. Setting this to 1, will stamp the user name in
 * the memo fields in GL
 */
/* This has been superseded with built in Audit Trail */
$use_audit_trail = 0;

/* $show_voiced_gl_trans = 0, setting this to 1 will show the voided gl trans */
$show_voided_gl_trans = 0;

/* use old style convert (income and expense in BS, PL) */
$use_oldstyle_convert = 0;

/* show users online discretely in the footer */
$show_users_online = 0;

/* show item codes on purchase order */
$show_po_item_codes = 0;

/* default print destination. 0 = PDF/Printer, 1 = Excel */
$def_print_destination = 0;

/* default print orientation. 0 = Portrait, 1 = Landscape */
$def_print_orientation = 0;

// Wiki context help configuration
// If your help wiki use translated page titles uncomment next line
// $old_style_help = 1; // this setting is depreciated and subject to removal in
// next FA versions
$old_style_help = 0;
// locally installed wiki module
// $help_base_url = $path_to_root.'/modules/wiki/index.php?n='._('Help').'.';
// context help feed from frontaccounting.com
// $help_base_url = 'http://frontaccounting.com/fawiki/index.php?n=Help.';
// not used
$help_base_url = null;

/* per user data/cache directory */
$comp_path = $path_to_root . '/company';

/*
 * allow alpha characters in accounts. 0 = numeric, 1 = alpha numeric, 2 =
 * uppercase alpha numeric
 */
$accounts_alpha = 0;

/*
 * Date systems. 0 = traditional, 1 = Jalali used by Iran, nabour countries,
 * Afghanistan and some other Central Asian nations,
 * 2 = Islamic used by other arabic nations. 3 = traditional, but where
 * non-workday is Friday and start of week is Saturday
 */
$date_system = 0;

/* email stock location if order below reorder-level */
/* Remember to set an email on the Location(s). */
$loc_notification = 0;

/* print_invoice_no. 0 = print reference number, 1 = print invoice number */
$print_invoice_no = 0;

/* 1 = print Subtotal tax excluded, tax and Total tax included */
$alternative_tax_include_on_docs = 0;

/* suppress tax rates on documents. 0 = no, 1 = yes. */
$suppress_tax_rates = 0;

/* default dateformats and dateseps indexes used before user login */
$dflt_date_fmt = 0;
$dflt_date_sep = 0;

/* default PDF pagesize taken from /reporting/includes/tcpdf.php */
$pagesizes = array(
        "Letter",
        "A4"
);

/* Accounts Payable */
/*
 * System check to see if quantity charged on purchase invoices exceeds the
 * quantity received.
 * If this parameter is checked the proportion by which the purchase invoice is
 * an overcharge
 * referred to before reporting an error
 */

$check_qty_charged_vs_del_qty = true;

/*
 * System check to see if price charged on purchase invoices exceeds the
 * purchase order price.
 * If this parameter is checked the proportion by which the purchase invoice is
 * an overcharge
 * referred to before reporting an error
 */

$check_price_charged_vs_order_price = True;

$config_allocation_settled_allowance = 0.005;

/*
 * Show average costed values instead of fixed standard cost in report,
 * Inventory Valuation Report
 */
$use_costed_values = 0;

/*
 * Allow negative prices for dummy/service items. To be moved to GL db settings
 */
$allow_negative_prices = 1;

/* Show menu category icons in core themes */
$show_menu_category_icons = 0;

// Internal configurable variables
// -----------------------------------------------------------------------------------

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

$js_path = '//' . $_SERVER['SERVER_NAME'] . '/assets/';
$assets_path = '//' . $_SERVER['SERVER_NAME'] . '/assets/';

if (isset($_SESSION["wa_current_user"]) && function_exists("user_company")) {
    define("BACKUP_PATH", $comp_path . '/' . user_company() . "/backup/");
}
if (! defined('AT_ASSEETS')) {
    define('AT_ASSEETS', '//' . $_SERVER['SERVER_NAME'] . '/assets/');
    // define('COUNTRY', 65); //Singapore
    define('COUNTRY', 60); // Malaysia
    /*
     * define('COUNTRY', 60); //
     * https://countrycode.org/
     */
}

$js_path = AT_ASSEETS;
$assets_path = AT_ASSEETS;

if (! defined('COMPANY_ASSETS')) {
    // define('COMPANY_ASSETS',
    // '//companyasset.accountanttoday.net/'.$session->checkSubDirectory() );
    define('COMPANY_ASSETS', 
            '//' . $_SERVER['SERVER_NAME'] . '/tmp/' .
                     $session->checkSubDirectory());
}
if (! defined('COMPANY_DIR')) {
    define('COMPANY_DIR', ROOT . '/tmp/' . $session->checkSubDirectory());
}

?>
