<?php

class GlInquiryBalanceSheet
{

    function __construct ()
    {
        $this->account_model = module_model_load("gl_account",'gl');
        $this->check_input_get();
        $this->check_submit();
        
    }

    function index ()
    {}

    function view ()
    {
        start_form();
        
        box_start();
        $this->filter();
        
        div_start('balance_tbl', null, null, array(
                'style' => 'padding-top:15px'
        ));
        $this->display_balance_sheet();
        div_end();
        
        box_footer();
        box_end();
        end_form();
    }

    private function filter ()
    {
        row_start('justify-content-md-center');
        $dim = get_company_pref('use_dimension');
        
        col_start(12, 'col-md-3');
        input_date_bootstrap(_("As at"), 'TransToDate');
        
        if (! isMobile()) {
            bootstrap_set_label_column(5);
        }
        if ($dim >= 1) {
            col_start(3);
            dimensions_bootstrap(_("Dimension") . " 1", 'Dimension', null, true, 
                    " ", false, 1);
        }
        
        if ($dim > 1) {
            col_start(12, 'col-md-3');
            dimensions_bootstrap(_("Dimension") . " 2", 'Dimension2', null, 
                    true, " ", false, 2);
        }
        
        col_start(12, 'col-md-1');
        submit('Show', _("Show"), true, '', 'default', 'search');
        
        hidden('TransFromDate');
        hidden('AccGrp');
        row_end();
    }

    private function display_balance_sheet ()
    {
        $from = $_POST["TransFromDate"];
        if (isset($_POST['TransToDate'])) {
            $to = $_POST['TransToDate'];
        } else {
            $to = end_fiscalyear();
        }
        
        if (! isset($_POST['Dimension']))
            $_POST['Dimension'] = 0;
        if (! isset($_POST['Dimension2']))
            $_POST['Dimension2'] = 0;
        $dimension = $_POST['Dimension'];
        $dimension2 = $_POST['Dimension2'];
        $lconvert = $econvert = 1;
        
        $drilldown = 0; // Root level
        if (isset($_POST["AccGrp"]) && (strlen($_POST['AccGrp']) > 0)){
            // Deeper Level
            $drilldown = 1; 
        }
        
        start_table(TABLESTYLE);
        
        if (! $drilldown) { // Root Level

            $equityclose = $calculateclose =  $lclose = 0.0;
            $parent = - 1;
            
            // Get classes for BS
            $classes = $this->account_model->get_classes(false, 1);
            foreach ($classes AS $class){
                $class = (array)$class;
                $classclose = 0.0;
                $convert = get_class_type_convert($class["ctype"]);
                $ctype = $class["ctype"];
                $classname = $class["class_name"];
                
                // Print Class Name
                table_section_title($classname);
                
                // Get Account groups/types under this group/type
                $typeresult = get_account_types(false, $class['cid'], - 1);
                
                while ($accounttype = db_fetch($typeresult)) {
                    $TypeTotal = $this->display_type($accounttype["id"], 
                            $accounttype["name"], $from, $to, $convert, 
                            $dimension, $dimension2, $drilldown);
                    // Print Summary
                    if ($TypeTotal != 0) {

                        $uri_sub_link = sprintf("/gl/inquiry/balance_sheet.php?TransFromDate=%s&TransToDate=%s&Dimension=%s&Dimension2=%s&AccGrp=%u",
                                            $from,$to,$dimension,$dimension2,$accounttype['id']);
                        $url = anchor( $uri_sub_link, $accounttype['id'] . " " . $accounttype['name']);
                        alt_table_row_color($k);
                        label_cell($url,'style="width:20%;"');
                        amount_cell($TypeTotal * $convert);
                        end_row();
                    }
                    $classclose += $TypeTotal;
                }
                
                // Print Class Summary
                start_row("class='inquirybg' style='font-weight:bold'");
                label_cell("Total $classname",'class="text-right"');
                amount_cell($classclose * $convert);
                end_row();
                
                if ($ctype == CL_EQUITY) {
                    $equityclose += $classclose;
                    $econvert = $convert;
                }
                if ($ctype == CL_LIABILITIES) {
                    $lclose += $classclose;
                    $lconvert = $convert;
                }
                
                $calculateclose += $classclose;
            }
            
            if ($lconvert == 1){
                $calculateclose *= - 1;
            }
                
            //Final Report Summary
            $uri_profit_loss_link = sprintf("/gl/inquiry/profit_loss.php?TransFromDate=%s&TransToDate=%s&Dimension=%s&Dimension2=%s&Compare=0",
                    $from,$to,$dimension,$dimension2);
            $url = anchor($uri_profit_loss_link, _('Calculated Return'));
            
            start_row("class='inquirybg' style='font-weight:bold'");
            label_cell($url);
            amount_cell($calculateclose);
            end_row();
            
            start_row("class='inquirybg' style='font-weight:bold'");
            label_cell("Total Liabilities and Equities",'class="text-right"');
            amount_cell(
                    $lclose * $lconvert + $equityclose * $econvert +
                             $calculateclose);
            end_row();
        } else { // Drill Down

            // Level Pointer : Global variable defined in order to control
            // display of root
            global $levelptr;
            $levelptr = 0;
            
            $accounttype = get_account_type($_POST["AccGrp"]);
            $classid = $accounttype["class_id"];
            $class = get_account_class($classid);
            $convert = get_class_type_convert($class["ctype"]);
            
            // Print Class Name
            table_section_title(
                    $_POST["AccGrp"] . " " .
                             get_account_type_name($_POST["AccGrp"]));
            
            $classclose = $this->display_type($accounttype["id"], 
                    $accounttype["name"], $from, $to, $convert, $dimension, 
                    $dimension2, $drilldown);
        }
        
        end_table(1); // outer table
    }

    private function display_type ($type, $typename, $from, $to, $convert, 
            $dimension, $dimension2, $drilldown)
    {
        global $levelptr, $k;
        
        $acctstotal = 0;
        $typestotal = 0;
        
        // Get Accounts directly under this group/type
        $result = get_gl_accounts(null, null, $type);
        $net_balance = 0;
        while ($account = db_fetch($result)) {
            $net_balance = get_gl_trans_from_to("", $to,  $account["account_code"], $dimension, $dimension2);
            if ( ! $net_balance)
                continue;
            
            if ($drilldown && $levelptr == 0) {
                // $url = "<a
                // href='$path_to_root/gl/inquiry/gl_account_inquiry.php?TransFromDate="
                // . $from . "&TransToDate=" . $to . "&Dimension=" . $dimension
                // . "&Dimension2=" . $dimension2
                // . "&account=" . $account['account_code'] . "'>" .
                // $account['account_code']
                // ." ". $account['account_name'] ."</a>";
                $url = anchor(
                        "/gl/inquiry/gl_account_inquiry.php?TransFromDate=" .
                                 $from . "&TransToDate=" . $to . "&Dimension=" .
                                 $dimension . "&Dimension2=" . $dimension2 .
                                 "&account=" . $account['account_code'], 
                                $account['account_name']);
                
                start_row("class='stockmankobg'");
                label_cell($url);
                amount_cell(($net_balance) * $convert);
                end_row();
            }
            
            $acctstotal += $net_balance;
        }
        
        $levelptr = 1;
        
        // Get Account groups/types under this group/type
        $result = get_account_types(false, false, $type);
        while ($accounttype = db_fetch($result)) {
            $typestotal += display_type($accounttype["id"], 
                    $accounttype["name"], $from, $to, $convert, $dimension, 
                    $dimension2, $drilldown, $path_to_root);
        }
        
        // Display Type Summary if total is != 0
        if (($acctstotal + $typestotal) != 0) {
            if ($drilldown && $type == $_POST["AccGrp"]) {
                start_row("class='inquirybg' style='font-weight:bold'");
                label_cell(_('Total') . " " . $typename);
                amount_cell(($acctstotal + $typestotal) * $convert);
                end_row();
            }
            // START Patch#1 : Display only direct child types
            $acctype1 = get_account_type($type);
            $parent1 = $acctype1["parent"];
            if ($drilldown && $parent1 == $_POST["AccGrp"]) 
            // END Patch#2
            // elseif ($drilldown && $type != $_POST["AccGrp"])
            {
                // $url = "<a
                // href='$path_to_root/gl/inquiry/balance_sheet.php?TransFromDate="
                // . $from . "&TransToDate=" . $to . "&Dimension=" . $dimension
                // . "&Dimension2=" . $dimension2
                // . "&AccGrp=" . $type ."'>" . $type . " " . $typename ."</a>";
                
                $url = anchor(
                        "/gl/inquiry/balance_sheet.php?TransFromDate=" . $from .
                                 "&TransToDate=" . $to . "&Dimension=" .
                                 $dimension . "&Dimension2=" . $dimension2 .
                                 "&AccGrp=" . $type, $typename);
                
                alt_table_row_color($k);
                label_cell($url);
                amount_cell(($acctstotal + $typestotal) * $convert);
                end_row();
            }
        }
        return ($acctstotal + $typestotal);
    }

    private function check_submit ()
    {
        global $Ajax;
        if (get_post('Show')) {
            $Ajax->activate('balance_tbl');
        }
    }

    private function check_input_get ()
    {
        if (isset($_GET["TransFromDate"]))
            $_POST["TransFromDate"] = $_GET["TransFromDate"];
        else {
            $_POST["TransFromDate"] = begin_fiscalyear();
        }
        
        if (isset($_GET["TransToDate"]))
            $_POST["TransToDate"] = $_GET["TransToDate"];
        else {
            $_POST["TransToDate"] = end_fiscalyear();
        }
        
        if (isset($_GET["Dimension"]))
            $_POST["Dimension"] = $_GET["Dimension"];
        if (isset($_GET["Dimension2"]))
            $_POST["Dimension2"] = $_GET["Dimension2"];
        if (isset($_GET["AccGrp"]))
            $_POST["AccGrp"] = $_GET["AccGrp"];
    }
}