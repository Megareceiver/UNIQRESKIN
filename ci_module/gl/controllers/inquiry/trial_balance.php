<?php

class GlInquiryTrialBalance
{
    var $balance = -1;
    var $class_total = false;
   
    var $dimension, $dimension2 = 0;
    function __construct ()
    {
        $check = input_get("check");
        if( $check=="profit-loss" ){
            $this->balance = 0;
            $this->class_total = true;
        }else if($check=="balance" ){
            $this->balance = 1;
            $this->class_total = true;
        }
        
        $this->gl_tran_model = module_model_load('trans', 'gl');
        $this->account_model = module_model_load("gl_account", 'gl');
        $this->check_submit();
    }

    function index ()
    {}

    function view ()
    {
        box_start();
        $this->filter();
        $this->set_filter_value();
        
        if (! isset($_POST['Dimension']))
            $_POST['Dimension'] = 0;
        if (! isset($_POST['Dimension2']))
            $_POST['Dimension2'] = 0;
        
        $this->items();
        
        box_footer_start();
        box_footer_end();
        box_end();
    }
    
    static $forward, $current, $total;
    private function sum_amount($debit=0,$credit=0,$balance=0, $type="",$class_id=0){
        if( in_array($type, array("forward","current","total")) ){
            $group = self::$$type;
            if( !is_array($group) ){
                $group = array('debit'=>0,'credit'=>0,"balance"=>0);
            }
        } else {
            return;
        }

       $group["debit"] += (float)$debit;
       $group["credit"] += (float)$credit;
       $group["balance"] += (float)$balance;
       
       $class_id = (int)$class_id;
       if( $class_id > 0 ){
          
           if( !isset($group[$class_id]) ){

              
               $group[$class_id] = array('debit'=>0,'credit'=>0,"balance"=>0);
           }
           $group[$class_id]["debit"] += (float)$debit;
           $group[$class_id]["credit"] += (float)$credit;
           $group[$class_id]["balance"] += (float)$balance;
       }
       self::$$type = $group;

    }
    private function filter ()
    {
        $dim = get_company_pref('use_dimension');
        start_form();
        row_start();
        if (isMobile()) {
            bootstrap_set_label_column(5);
        }
        
        $date = today();
        if (! isset($_POST['TransFromDate']))
            $_POST['TransFromDate'] = begin_month($date);
        if (! isset($_POST['TransToDate']))
            $_POST['TransToDate'] = end_month($date);
        
        col_start(12, 'col-md-3');
        
        input_date_bootstrap(_("From"), 'TransFromDate');
        
        col_start(12, 'col-md-3');
        input_date_bootstrap(_("To"), 'TransToDate');
        if ($dim >= 1)
            dimensions_list_cells(_("Dimension") . " 1:", 'Dimension', null, 
                    true, " ", false, 1);
        if ($dim > 1)
            dimensions_list_cells(_("Dimension") . " 2:", 'Dimension2', null, 
                    true, " ", false, 2);
        
        if (! isMobile()) {
            bootstrap_set_label_column(8);
        }
        col_start(12, 'col-md-2');
        
        $_POST["NoZero"] = true;
        check_bootstrap(_("No zero values"), 'NoZero', null);
        col_start(12, 'col-md-2');
        check_bootstrap(_("Only balances"), 'Balance', null);
        
        col_start(12, 'col-md-1 offset-md-0 offset-3');
        submit('Show', _("Show"), true, '', 'default', 'search');
        
        row_end();
        end_form();
    }
    private function set_filter_value(){
        $this->dimension = input_post("Dimension");
        $this->dimension2 = input_post("Dimension2");
    }

    private function items ()
    {
        $k = 0;
        $pdeb = $pcre = $cdeb = $ccre = $tdeb = $tcre = $pbal = $cbal = $tbal = 0;
        
        div_start('balance_tbl', $trigger = null, $non_ajax = false, 'style="padding-top:15px;"');
        
        start_table(TABLESTYLE);
        
        start_row();
        labelheader_cell( _("Account"),'rowspan="2"');
        labelheader_cell( _("Account Name"),'rowspan="2"');
        labelheader_cell( _("Brought Forward"),'colspan=2 class="text-center"');
        labelheader_cell( _("This Period"),'colspan=2 class="text-center"');
        labelheader_cell( _("Balance"),'colspan=2 class="text-center"');
        end_row();
        
        start_row();
        $number_col_attribute = array("style"=>"width:10%;","class"=>"text-right");
        labelheader_cell( _("Debit"),$number_col_attribute);
        labelheader_cell( _("Credit"),$number_col_attribute);
        labelheader_cell( _("Debit"),$number_col_attribute);
        labelheader_cell( _("Credit"),$number_col_attribute);
        labelheader_cell( _("Debit"),$number_col_attribute);
        labelheader_cell( _("Credit"),$number_col_attribute);
        end_row();
        
        
        $classes = $this->account_model->get_classes(false,$this->balance);
        foreach ($classes as $class) {
            start_row('class="text-bold bg-primary text-white"');
            label_cell( sprintf("Class - %s - %s",$class->cid,$class->class_name),"colspan=8");
            end_row();
            
            // Get Account groups/types under this group/type with no parents
//             $typeresult = get_account_types(false, $class->cid, - 1);
//             while ($accounttype = db_fetch($typeresult)) {
            $types = $this->account_model->get_types(false, $class->cid, - 1);
            foreach ($types AS $accounttype ){
                $this->trial_balance($accounttype->id, $accounttype->name, $class->cid);
            }
        }
        
        if (!check_value('Balance')) {
            start_row("class='inquirybg' style='font-weight:bold'");
            label_cell(_("Total") . " - " . $_POST['TransToDate'], "colspan=2");
            amount_cell(self::$forward["debit"]);
            amount_cell(self::$forward["credit"]);
            amount_cell(self::$current["debit"]);
            amount_cell(self::$current["credit"]);
            amount_cell(self::$total["debit"]);
            amount_cell(self::$total["credit"]);
            end_row();
        }
        
        start_row("class='inquirybg' style='font-weight:bold'");
        label_cell(_("Ending Balance") . " - " . $_POST['TransToDate'], "colspan=2");
        amount_cell(self::$forward["balance"],false,"colspan=2");
        amount_cell(self::$current["balance"],false,"colspan=2");
        amount_cell(self::$total["balance"],false,"colspan=2");
        end_row();
        
        end_table(1);
        if (($pbal = round2($pbal, user_price_dec())) != 0 && $_POST['Dimension'] == 0 && $_POST['Dimension2'] == 0){
            display_warning( _("The Opening Balance is not in balance, probably due to a non closed Previous Fiscalyear."));
        }
            
        div_end();
    }

    private function trial_balance($type, $typename, $class_id= 0)
    {
        global $clear_trial_balance_opening;
//         global $pdeb, $pcre, $cdeb, $ccre, $tdeb, $tcre, $pbal, $cbal, $tbal;
        
        $printtitle = 0; 
        // Flag for printing type name
        
        $k = 0;
        $begin = get_fiscalyear_begin_for_date($_POST['TransFromDate']);
        // $begin = begin_fiscalyear();
        if (date1_greater_date2($begin, $_POST['TransFromDate']))
            $begin = $_POST['TransFromDate'];
        $begin = add_days($begin, - 1);
        
        // Get Accounts directly under this group/type
        $accounts = get_gl_accounts(null, null, $type);
        while ($account = db_fetch($accounts)) {
            
            
            // FA doesn't really clear the closed year, therefore the brought
            // forward balance includes all the transactions from the past, even
            // though the balance is null.
            // If we want to remove the balanced part for the past years, this
            // option removes the common part from from the prev and tot
            // figures.
            if (@$clear_trial_balance_opening) {
                $open = get_balance($account["account_code"],  $this->dimension, $this->dimension2, $begin, $begin, false, true);
                $offset = min($open['debit'], $open['credit']);
            } else {
                $offset = 0;
            }
            
//             $offset = 0;

            $prev = $this->gl_tran_model->get_balance($account["account_code"], 
                    $this->dimension, $this->dimension2, $begin, 
                    $_POST['TransFromDate'], false, false);
            
            $curr = $this->gl_tran_model->get_balance($account["account_code"], 
                    $this->dimension, $this->dimension2, 
                    $_POST['TransFromDate'], $_POST['TransToDate'], true, true);
            $tot = $this->gl_tran_model->get_balance($account["account_code"], 
                    $this->dimension, $this->dimension2, $begin, 
                    $_POST['TransToDate'], false, true);
            
            if (check_value("NoZero") && ! $prev['balance'] && ! $curr['balance'] && ! $tot['balance']){
                continue;
            }
            
            // Print Type Title if it has atleast one non-zero account
            if (! $printtitle) {
                start_row('class="bg-info text-white"');
                label_cell(_("Group") . " - " . $type . " - " . $typename, "colspan=8");
                end_row();
                $printtitle = 1;
            }
            
            start_row();
            
            $uri_account = sprintf("/gl/inquiry/gl_account_inquiry.php?TransFromDate=%s&TransToDate=%s&account=%s&Dimension=%s&Dimension2=%s",
                    $_POST["TransFromDate"], $_POST["TransToDate"], $account["account_code"], $this->dimension, $this->dimension2);
            
            label_cell(anchor($uri_account,$account["account_code"]));
            label_cell($account["account_name"]);
            
            if (check_value('Balance')) {
                display_debit_or_credit_cells($prev['balance']);
                display_debit_or_credit_cells($curr['balance']);
                display_debit_or_credit_cells($tot['balance']);
            } else {
                amount_total_cell($prev['debit'] - $offset);
                amount_total_cell($prev['credit'] - $offset);
                amount_total_cell($curr['debit']);
                amount_total_cell($curr['credit']);
                amount_total_cell($tot['debit'] - $offset);
                amount_total_cell($tot['credit'] - $offset);
                
                $this->sum_amount($prev['debit'],$prev['credit'],0,"forward",$class_id);
                $this->sum_amount($curr['debit'],$curr['credit'],0,"current",$class_id);
                $this->sum_amount($tot['debit'],$tot['credit'],0,"total",$class_id);
            }
            $this->sum_amount(0,0,$prev['balance'],"forward",$class_id);
            $this->sum_amount(0,0,$curr['balance'],"current",$class_id);
            $this->sum_amount(0,0,$tot['balance'],"total",$class_id);

            end_row();
        }
        
        // Get Account groups/types under this group/type
//         $result = get_account_types(false, false, $type);
//         while ($accounttype = db_fetch($result)) {
        $types = $this->account_model->get_types(false, false, $type);
        foreach ($types AS $accounttype ){
            // Print Type Title if has sub types and not previously printed
            if (! $printtitle) {
                start_row("class='inquirybg' style='font-weight:bold'");
                label_cell(_("Group") . " - " . $type . " - " . $typename,  "colspan=8");
                end_row();
                $printtitle = 1;
            }
//             display_trial_balance($accounttype["id"],  $accounttype["name"] . ' (' . $typename . ')');
            $this->trial_balance($accounttype->id, $accounttype->name. ' (' . $typename . ')');
        }
    }

    private function check_submit ()
    {
        global $Ajax;
        if (get_post('Show')) {
            $Ajax->activate('balance_tbl');
        }
        
        if (isset($_POST['TransFromDate'])) {
            $row = get_current_fiscalyear();
            if (date1_greater_date2($_POST['TransFromDate'], 
                    sql2date($row['end']))) {
                display_error(
                        _(
                                "The from date cannot be bigger than the fiscal year end."));
                set_focus('TransFromDate');
                return;
            }
        }
    }
}