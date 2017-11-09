<?php

class SetupWizard
{

    var $wizard_title = NULL;
    var $wizard_run = false;
    var $wizard_steps = array();

    function __construct()
    {
        $this->db = get_instance()->db;
    }

    function index()
    {

        if( get_company_pref('setup-finish')  ){
            return true;
        }
        if( count($_POST) > 0 ){
            $_SESSION["run_wizard"] = input_post('start')==1;

        }
        if ( !$_SESSION["run_wizard"] ) {
            global $Ajax;
            $Ajax->redirect(get_instance()->url_back);
        } else {
            $this->run(false);
        }
    }

    var $steps = array(
        'fiscal_year_items'=>array('Fiscal Year',0,'admin/fiscal-years'),
        'company_setup'=>array('Company Setting',0,'admin/company_preferences.php'),
        'gl_setup'=>array('System & General GL Setup',0 ,'admin/gl_setup.php'),
        'product'=>array('Product Master',0,'inventory/manage/items.php'),
        'bank'=>array('Bank Account',0,'gl/manage/bank_accounts.php'),
        'supplier'=>array('Supplier Master',0,'purchasing/manage/suppliers.php'),
        'customer'=>array('Customer Master',0,'sales/manage/customers.php')
    );

    function status_show(){
        if( get_company_pref('setup-finish')  ){
            return true;
        }
        $dialog = $_SERVER['SCRIPT_NAME'] == '/index.php';
        if( isset($_SESSION["run_wizard"]) AND $_SESSION["run_wizard"] != 1 ){
            $dialog = false;
        }
        $data = array(
            'steps'=>$this->steps,
        );

        if( !$this->wizard_run ){
            return;
        }
        if( $dialog ){
            module_view('setup-wizard-dialog',$data,$display = true, $use_theme=false,$module="setup");
        } else {
            module_view('setup-wizard',$data,$display = true, $use_theme=false,$module="setup");
        }

    }

    function run($check_post=true)
    {
        if( get_company_pref('setup-finish')  ){
            return true;
        }
        if( !isset($_SESSION['App']) ){
            return;
        }
        if ( $check_post AND count($_POST) > 0 AND !in_ajax() ) {
            return true;
        }
        /*
         * Step 1. Setup Fiscal year
         */
        $this->fiscal_year_items();

        /*
         * Step 2. Company Setup
         */
        $this->Company_Setup();
        /*
         * Step 3: System & General GL Setup
         */
        $this->General_GL_Setup();

        if( $this->wizard_run ){
            $_SESSION['App']->applications['stock']->enabled = false;
        }

        /*
         * Step 4. Create Products or Services
         */
        $this->Product_Item();
        if( $this->wizard_run ){
            $_SESSION['App']->applications['AP']->enabled = false;
        }

        /*
         * Step 7. Bank Account Setup
         */
        $this->BankAccount_Item();

        /*
         * Step 5. Create Supplier
         */
        $this->Supplier_Item();
        if( $this->wizard_run ){
            $_SESSION['App']->applications['orders']->enabled = false;
        }
        /*
         * Step 6. Create Customer
         */
        $this->Customer_Item();
        if( $this->wizard_run ){
            $_SESSION['App']->applications['GL']->enabled = false;
        }


        // get_instance()->smarty->
        if ($this->wizard_run and count($this->wizard_steps) > 0) {
//             global $assets_path;
//             add_js_source("$assets_path/plugins/bootstrap-tour/js/bootstrap-tour-standalone.js");
//             add_css_source("$assets_path/plugins/bootstrap-tour/css/bootstrap-tour.css");

//             $steps = NULL;
//             $steps = json_encode($this->wizard_steps);
//             $js = " var tour = new Tour({ steps: " . json_encode($this->wizard_steps) . " }); tour.restart();";
//             add_document_ready_js($js);
        } else {
            $_SESSION['App']->applications['stock']->enabled = true;
            $_SESSION['App']->applications['AP']->enabled = true;
            $_SESSION['App']->applications['orders']->enabled = true;
            $_SESSION['App']->applications['GL']->enabled = true;
        }
    }

    private function step($element = NULL, $content = NULL, $backdrop = false, $placement = 'top')
    {
        if (strlen($element) > 0 and strlen($content) > 0) {
            $step = array(
                'element' => $element,
                'title' => $this->wizard_title,
                'content' => $content,
                'placement' => $placement
            );
            if ($backdrop) {
                $step['backdrop'] = 'true';
                $step['backdropPadding'] = 5;
            }
            $this->wizard_steps[] = $step;
        }
    }

    private function fiscal_year_items()
    {
        if ($this->wizard_run) {
            return;
        }
        $this->wizard_title = 'Add new Fiscal Year';
        $query = $this->db->where('closed', 0)->get('fiscal_year');
        if ($query->num_rows() < 0) {
            uri_is($this->steps['fiscal_year_items'][2], true);
            $this->wizard_run = true;
            $this->step('#fiscalyear-form', 'Add a fiscal year into system.', true);
            $this->step('#ADD_ITEM', 'Save new data', false);
        } else {
            $this->steps['fiscal_year_items'][1] = 1;
        }
    }

    private function Company_Setup()
    {
        if ($this->wizard_run) {
            return;
        }
        $this->wizard_title = 'Company Setup';
//         $uri = 'admin/company_preferences.php';

        $prefs = get_company_prefs();

        if (! isset($prefs['coy_name']) or strlen($prefs['coy_name']) < 1) {
            $this->step('input[name=coy_name]', 'Company Name to appear on every report', true);
            $this->wizard_run = true;
        }

        if (! isset($prefs['postal_address']) or strlen($prefs['postal_address']) < 1) {
            $this->step('textarea[name=postal_address]', 'Company Address', true);
            $this->wizard_run = true;
        }

        if (! isset($prefs['email']) or strlen($prefs['email']) < 1) {
            $this->step('input[name=email]', 'Email address: to receive system notification or reports', true);
            $this->wizard_run = true;
        }

        if (! isset($prefs['curr_default']) or strlen($prefs['curr_default']) < 1) {
            $this->step('select[name=curr_default]', 'Home currency: this is very import and could not be changed after transactions are made', true);
            $this->wizard_run = true;
        }

        if (! isset($prefs['f_year']) or strlen($prefs['f_year']) < 1) {
            $this->step('select[name=f_year]', 'Home currency: this is very import and could not be changed after transactions are made', true);
            $this->wizard_run = true;
        }

        if ($this->wizard_run == true) {
            $this->step('#update', 'Update Company Setup', false);
            uri_is($this->steps['company_setup'][2], true);
        }  else {
            $this->steps['company_setup'][1] = 1;
        }

        if( in_ajax() AND uri_is($this->steps['company_setup'][2]) ){

            if( $this->wizard_run ){
                $error_notify = "User must enter these following information for the company setup:";
                $error_notify.= "<ul>";
                $error_notify.= "<li>Company Name to appear on every report</li>";
                $error_notify.= "<li>Company Address</li>";
                $error_notify.= "<li>Email address: to receive system notification or reports</li>";
                $error_notify.= "<li>Home currency: this is very import and could not be changed after transactions are made</li>";
                $error_notify.= "</ul>";

                display_error( $error_notify );
            }
        }
    }

    private function General_GL_Setup()
    {

        if ($this->wizard_run) {
            return;
        }

        $prefs = get_company_prefs();
        $accounts_check = array(
            'retained_earnings_act'=>'Retained Earnings',
            'profit_loss_year_act'=>'Profit/Loss Year',
            'exchange_diff_act'=>'Exchange Variances Account',
            'rounding_difference_act'=>'Rounding Difference Account',

            'freight_act'=>'Shipping Charged Account',
            /*Purchase Acc*/
            'debtors_act'=>'Receivable Account',
            'default_sales_act'=>'Sales Account',
            'default_sales_discount_act'=>'Sales Discount Account',
            'default_prompt_payment_act'=>'Prompt Payment Discount Account',

            /*Sale Acc*/
            'creditors_act'=>'Payable Account',
            'pyt_discount_act'=>'Purchase Discount Account',
            'grn_clearing_act'=>'GRN Clearing Account',

            'default_inv_sales_act'=>'Sales Account',
            'default_inventory_act'=>'Inventory Account',
            'default_cogs_act'=>'C.O.G.S. Account',
            'default_adj_act'=>'Inventory Adjustments Account',
            'default_assembly_act'=>'Item Assembly Costs Account'
        );

        foreach ($accounts_check AS $name=>$title){
            if( !isset($prefs[$name]) OR strlen($prefs[$name]) < 1 ){
                $this->step("div[glname=$name]", "Select : $title", true);
                if( !$this->wizard_run ){
                    $this->wizard_run = true;
                }
            }
        }

        if ($this->wizard_run == true) {
            uri_is($this->steps['gl_setup'][2], true);
            $this->wizard_title = 'System and General GL Setup ';
            $this->step('#submit', 'Update Setup', false);
            if( in_ajax() AND uri_is($this->steps['gl_setup'][2]) ){
                display_error( "Must to check and ensure all GL setting are set to ensure the posting correctly");
            }
        } else {
            $this->steps['gl_setup'][1] = 1;
        }
    }

    private function Product_Item(){
        if ($this->wizard_run) {
            return;
        }

        $query = $this->db->where('inactive',0)->get('stock_master');
        if ($query->num_rows() < 1) {

            $this->wizard_title = 'Create Products or Services';
            $this->step('input[name=NewStockID]', 'Product Code', true);
            $this->step('input[name=description]', 'Product Name', true);

            $this->step("div[gstname=sales_gst_type_id]", "Select : Sales GST Type", true);
            $this->step("div[gstname=purchase_gst_type_id]", "Select : Purchase GST Type", true);

            $this->step("div[glname=sales_account]", "Select : Sales Account", true);
            $this->step("div[glname=inventory_account]", "Select : Inventory Account", true);
            $this->step("div[glname=cogs_account]", "Select : C.O.G.S. Account", true);
            $this->step("div[glname=adjustment_account]", "Select : Inventory Adjustments Account", true);

            $this->step("div[selectname=inactive]", "Item status must Active", true);

            $this->wizard_run = true;
            $this->step('#addupdate', 'Add Product', false);
        }

        if( $this->wizard_run != true ) {
            $this->steps['product'][1] = 1;
        } else {
            if( uri_is("inventory/manage/item_categories.php", false) OR uri_is("inventory/manage/item_categories.php", false) ){
                $this->wizard_steps = NULL;
            } else {
                uri_is($this->steps['product'][2], true);
                if( in_ajax() AND uri_is($this->steps['product'][2]) ){
                    display_error( "Must create at least one item or service to proceed transaction");
                }
            }

        }

    }

    private function Supplier_Item(){
        if ($this->wizard_run) {
            return;
        }
        $query = $this->db->where('inactive',0)->get('suppliers');

        if ($query->num_rows() < 1) {

            $this->wizard_title = 'Create Supplier';

            $this->step('input[name=supp_name]', 'Supplier Name', true);
            $this->step('input[name=supp_ref]', 'Supplier Short Name', true);

            $this->step('div[glname=payable_account]', 'Accounts Payable', true);
            $this->step('div[glname=purchase_account]', 'Purchase Account', true);
            $this->step('div[glname=payment_discount_account]', 'Accounts Discount', true);

            $this->wizard_run = true;
            $this->step('#submit', 'Add Supplier', false);
        }

        if( $this->wizard_run != true ) {
            $this->steps['supplier'][1] = 1;
        } else {
            uri_is($this->steps['supplier'][2], true);
            if( in_ajax() AND uri_is($this->steps['supplier'][2]) ){
                display_error( "Must create at least one supplier in the system to proceed PURCHASE transaction and make sure all GL account settings as well as tax settings of the supplier are set");
            }
        }
    }

    private function Customer_Item(){
        if ($this->wizard_run) {
            return;
        }
        $query = $this->db->where('inactive',0)->get('debtors_master');
//         bug($query);die;
        if ($query->num_rows() < 1) {

            $this->wizard_title = 'Create Customer';

            $this->step('input[name=CustName]', 'Customer Name', true);
            $this->step('input[name=cust_ref]', 'Customer Short Name', true);

            $this->step('div[currname=curr_code]', "Customer's Currency", true);
            $this->step('div[saletypename=sales_type]', "Tax include", true);


             $this->wizard_run = true;
            $this->step('#submit', 'Add Supplier', false);
        }

        if( $this->wizard_run != true ) {
            $this->steps['customer'][1] = 1;
        } else {
            uri_is($this->steps['customer'][2], true);
            if( in_ajax() AND uri_is($this->steps['customer'][2]) ){
                display_error( "Must create at least on customer in the system to proceed the SALES transaction. User also need to make sure all tax settings or GL Accounts settings for Customer and Customer Branch are set");
            }
        }
    }

    private function BankAccount_Item(){
        if ($this->wizard_run) {
            return;
        }
        $query = $this->db->where('inactive',0)->get('bank_accounts');
        if ($query->num_rows() < 1) {

            $this->wizard_title = 'Create Bank Account';

            $this->step('input[name=bank_account_name]', 'Bank Account Name', true);
            $this->step('div[banktypename=account_type]', "Account Type", true);
            $this->step('div[currname=BankAccountCurrency]', "Bank Currency", true);


            $this->step('div[glname=account_code]', "Bank Account GL Code", true);
            $this->step('input[name=bank_name]', 'Bank Name', true);

            $this->wizard_run = true;
            $this->step('#ADD_ITEM', 'Add Bank', false);
        }

        if( $this->wizard_run != true ) {
            $this->steps['bank'][1] = 1;
        } else {
            uri_is($this->steps['bank'][2], true);
            if( in_ajax() AND uri_is($this->steps['bank'][2]) ){
                display_error( "Must create at least on customer in the system to proceed the SALES transaction. User also need to make sure all tax settings or GL Accounts settings for Customer and Customer Branch are set");
            }
        }
    }

}