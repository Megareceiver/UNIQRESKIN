<?php

class GlInquiryProfitLoss
{

    function __construct()
    {
        $this->account_model = module_model_load("gl_account",'gl');
        $this->gl_account_model = module_model_load("accounts",'gl');
        
        $this->check_input_get();
        $this->check_submit();
    }

    function index()
    {}

    function view()
    {
        start_form();
        box_start();
        $this->filter();

        div_start('pl_tbl', null, false, array(
            'style' => 'padding-top:15px'
        ));
        $this->display_profit_and_loss();
        div_end();

        box_footer();
        box_end();
        end_form();
    }

    private function filter()
    {
        global $sel;
        $dim = get_company_pref('use_dimension');
        $date = today();
        if (! isset($_POST['TransFromDate']))
            $_POST['TransFromDate'] = begin_month($date);
        if (! isset($_POST['TransToDate']))
            $_POST['TransToDate'] = end_month($date);

        row_start();
        col_start(12,'col-md-3');
        input_date_bootstrap(_("From"), 'TransFromDate');

        col_start(12,'col-md-3');
        input_date_bootstrap(_("To"), 'TransToDate');

        if( isNotMobile() ){
            bootstrap_set_label_column(5);
        }
        col_start(12,'col-md-3');
        $sel = array(
            _("Accumulated"),
            _("Period Y-1"),
            _("Budget")
        );
        input_array_selector('Compare to', 'Compare', null, $sel);
        //
        if ($dim >= 1) {
            row_start();
            col_start(12,'col-md-3');
            dimensions_bootstrap(_("Dimension") . " 1:", 'Dimension', null, true, " ", false, 1);
        }

        if ($dim > 1) {
            col_start(12,'col-md-3');
            dimensions_bootstrap(_("Dimension") . " 2:", 'Dimension2', null, true, " ", false, 2);
        }

        col_start(12,'offset-md-0 col-md-1 offset-3');
        submit('Show', _("Show"), true, '', 'default', 'search');
        col_end();

        hidden('AccGrp');
        row_end();
    }

    private function display_profit_and_loss()
    {
        global $path_to_root;

        $from = input_val("TransFromDate");
        $to = input_val("TransToDate");
        

        $drilldown = 0; // Root level
        if (isset($_POST["AccGrp"]) && (strlen($_POST['AccGrp']) > 0)){
            $drilldown = 1; // Deeper Level
        }

        $dec = 0;
        $pdec = user_percent_dec();

        if ($this->compare == 0 || $this->compare == 2) {
            $end = $to;
            $begin = ($this->compare == 2) ? $from : begin_fiscalyear();
        } elseif ($this->compare == 1) {
            $begin = add_months($from, - 12);
            $end = add_months($to, - 12);
        }

        start_table(TABLESTYLE);
        
        if (! $drilldown) { // Root Level
            $parent = - 1;
            $classper = 0.0;
            $classacc = 0.0;
            $salesper = 0.0;
            $salesacc = 0.0;

            // Get classes for PL

            $classes = $this->account_model->get_classes(false,0);
//             bug($classes);die;
            foreach ($classes AS $class){
                $class = (array)$class;
                $class_per_total = 0;
                $class_acc_total = 0;
                $convert = get_class_type_convert($class["ctype"]);

                // Print Class Name
                table_section_title($class["class_name"], 4);
                $this->profit_loss_table_header();

                // Get Account groups/types under this group/type

                $types = $this->account_model->get_types(false, $class['cid'], - 1);
                foreach ($types AS $accounttype){
                    $accounttype = (array)$accounttype;
                    
                    $TypeTotal = $this->display_type($accounttype["id"], $accounttype["name"], $from, $to, $begin, $end, $this->compare, $convert, $dec, $pdec, $rep, $this->dimension, $this->dimension2, $drilldown, $path_to_root);
                    $class_per_total += $TypeTotal[0];
                    $class_acc_total += $TypeTotal[1];

                    if ($TypeTotal[0] != 0 || $TypeTotal[1] != 0) {
                        $uri_acc = sprintf("/gl/inquiry/profit_loss.php?TransFromDate=%s&TransToDate=%s&Dimension=%s&Dimension2=%s&Compare=%s&AccGrp=%s",
                                $from,$to,$this->dimension,$this->dimension2,$this->compare,$accounttype['id']);
                        
                        alt_table_row_color($k);
                        label_cell(anchor($uri_acc,$accounttype['id'] . " " . $accounttype['name']));
                        amount_cell($TypeTotal[0] * $convert);
                        amount_cell($TypeTotal[1] * $convert);
                        amount_cell($this->achieve($TypeTotal[0], $TypeTotal[1]));
                        end_row();
                    }
                }

                // Print Class Summary

                start_row("class='inquirybg'");
                label_cell(_('Total') . " " . $class["class_name"]);
                amount_cell($class_per_total * $convert);
                amount_cell($class_acc_total * $convert);
                amount_cell($this->achieve($class_per_total, $class_acc_total));
                end_row();

                $salesper += $class_per_total;
                $salesacc += $class_acc_total;
            }

            start_row("class='inquirybg' style='font-weight:bold'");
            label_cell(_('Calculated Return'),'class="text-right"');
            amount_cell($salesper * - 1);
            amount_cell($salesacc * - 1);
            amount_cell($this->achieve($salesper, $salesacc));
            end_row();
        } else {
            // Level Pointer : Global variable defined in order to control display of root
            global $levelptr;
            $levelptr = 0;

            $accounttype = get_account_type($_POST["AccGrp"]);
            $classid = $accounttype["class_id"];
            $class = get_account_class($classid);
            $convert = get_class_type_convert($class["ctype"]);

            // Print Class Name
            table_section_title($_POST["AccGrp"] . " " . get_account_type_name($_POST["AccGrp"]), 4);
            $this->profit_loss_table_header();

            $classtotal = $this->display_type($accounttype["id"], $accounttype["name"], $from, $to, $begin, $end, $this->compare, $convert, $dec, $pdec, $rep, $this->dimension, $this->dimension2, $drilldown, $path_to_root);
        }

        end_table(1);
    }
    
    private function profit_loss_table_header(){
        global $sel;
        start_row('class="table-warning"');
        labelheader_cell( _("Group/Account Name"));
        labelheader_cell(_("Period"),'class="text-right"');
        labelheader_cell($sel[$this->compare],'class="text-right"');
        labelheader_cell(_("Achieved %"),'class="text-right"');
        end_row();
    }
    

    private function display_type($type, $typename, $from, $to, $begin, $end, $compare, $convert, &$dec, &$pdec, &$rep, $dimension = 0, $dimension2 = 0, $drilldown)
    {
        global $levelptr, $k;

        $code_per_balance = 0;
        $code_acc_balance = 0;
        $per_balance_total = 0;
        $acc_balance_total = 0;
        unset($totals_arr);
        $totals_arr = array();

        // Get Accounts directly under this group/type
//         $result = get_gl_accounts(null, null, $type);
//         while ($account = db_fetch($result)) {
        $accounts = $this->gl_account_model->get_gl_accounts(null, null, $type);
        foreach ($accounts AS $account ){
            $account = (object)$account;
            $per_balance = get_gl_trans_from_to($from, $to, $account->account_code, $this->dimension, $this->dimension2);
//             $per_balance = 0;
            
            if ($compare == 2)
                $acc_balance = get_budget_trans_from_to($begin, $end, $account->account_code, $this->dimension, $this->dimension2);
            else
                $acc_balance = get_gl_trans_from_to($begin, $end, $account->account_code, $this->dimension, $this->dimension2);
            if (! $per_balance && ! $acc_balance)
                continue;

            if ($drilldown && $levelptr == 0) {
                $uri_account_inquiry = sprintf("/gl/inquiry/gl_account_inquiry.php?TransFromDate=%s&TransToDate=%s&Dimension=%s&Dimension2=%s&account=%s",
                        $from,$to,$this->dimension,$this->dimension2,$account->account_code);
                
                start_row("class='stockmankobg'");
                label_cell(anchor($uri_account_inquiry,$account->account_code . " " . $account->account_name));
                amount_cell($per_balance * $convert);
                amount_cell($acc_balance * $convert);
                amount_cell($this->achieve($per_balance, $acc_balance));
                end_row();
            }

            $code_per_balance += $per_balance;
            $code_acc_balance += $acc_balance;
        }

        $levelptr = 1;

        // Get Account groups/types under this group/type
//         $result = get_account_types(false, false, $type);
//         while ($accounttype = db_fetch($result)) {
        $types = $this->account_model->get_types(false, false, $type);
        foreach ($types AS $accounttype){
            $accounttype = (array)$accounttype;
            $totals_arr = $this->display_type($accounttype["id"], $accounttype["name"], $from, $to, $begin, $end, $compare, $convert, $dec, $pdec, $rep, $this->dimension, $this->dimension2, $drilldown);
            $per_balance_total += $totals_arr[0];
            $acc_balance_total += $totals_arr[1];
        }

        // Display Type Summary if total is != 0
        if (($code_per_balance + $per_balance_total + $code_acc_balance + $acc_balance_total) != 0) {
            if ($drilldown && $type == $_POST["AccGrp"]) {
                start_row("class='inquirybg' style='font-weight:bold'");
                label_cell(_('Total') . " " . $typename);
                amount_cell(($code_per_balance + $per_balance_total) * $convert);
                amount_cell(($code_acc_balance + $acc_balance_total) * $convert);
                amount_cell($this->achieve(($code_per_balance + $per_balance_total), ($code_acc_balance + $acc_balance_total)));
                end_row();
            }
            // START Patch#1 : Display only direct child types
            $acctype1 = get_account_type($type);
            $parent1 = $acctype1["parent"];
            if ($drilldown && $parent1 == $_POST["AccGrp"])
            // END Patch#2
            // elseif ($drilldown && $type != $_POST["AccGrp"])
            {
                $url = "<a href='" . site_url() . "/gl/inquiry/profit_loss.php?TransFromDate=" . $from . "&TransToDate=" . $to . "&Compare=" . $compare . "&Dimension=" . $this->dimension . "&Dimension2=" . $this->dimension2 . "&AccGrp=" . $type . "'>" . $type . " " . $typename . "</a>";

                alt_table_row_color($k);
                label_cell($url);
                amount_cell(($code_per_balance + $per_balance_total) * $convert);
                amount_cell(($code_acc_balance + $acc_balance_total) * $convert);
                amount_cell($this->achieve(($code_per_balance + $per_balance_total), ($code_acc_balance + $acc_balance_total)));
                end_row();
            }
        }

        $totals_arr[0] = $code_per_balance + $per_balance_total;
        $totals_arr[1] = $code_acc_balance + $acc_balance_total;
        return $totals_arr;
    }

    private function achieve($d1, $d2)
    {
        if ($d1 == 0 && $d2 == 0)
            return 0;
        elseif ($d2 == 0)
            return 999;
        $ret = ($d1 / $d2 * 100.0);
        if ($ret > 999)
            $ret = 999;
        return $ret;
    }

    private function check_submit()
    {
        global $Ajax;
        if (get_post('Show')) {
            $Ajax->activate('pl_tbl');
        }
    }

    private function check_input_get()
    {
        if (isset($_GET["TransFromDate"]))
            $_POST["TransFromDate"] = $_GET["TransFromDate"];
        if (isset($_GET["TransToDate"]))
            $_POST["TransToDate"] = $_GET["TransToDate"];
        
        $this->compare = input_val("Compare");
        if( is_null($this->compare) ){
            $this->compare = 0;
        }
        
        $this->dimension = input_val("Dimension");
        $this->dimension2 = input_val("Dimension2");

        if (isset($_GET["AccGrp"]))
            $_POST["AccGrp"] = $_GET["AccGrp"];
    }
}