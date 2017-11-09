<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Dashboard
{

    function __construct()
    {
        $this->ci = get_instance();
        $this->page_security = 'SA_GLTRANSVIEW';
        $this->ci->page_title = 'Dashboard';
        include_once (ROOT . "/includes/ui.inc");

        include_once (ROOT . "/reporting/includes/class.graphic.inc");

        $this->model = $this->ci->module_model('dashboard', 'widgets', true);
        if (! defined('FLOAT_COMP_DELTA'))
            define('FLOAT_COMP_DELTA', 0.004);
        add_js_file('https://www.google.com/jsapi');
        check_dir(company_path() . "/pdf_files/");
    }

    function home()
    {
        $home_shoft = array(
            'quote'=>array(
                '/sales/inquiry/sales_orders_view.php?type=32',
                'Create a Quote',
                'Send a quotation to your customer',
                'file-pdf-o'
            ),
            'sale-invoice'=>array(
                '/sales/sales_order_entry.php?NewInvoice=0',
                'Create an Invoice',
                'Sell items or service to your customer',
                'envelope'
            ),
            'customer-payment'=>array(
                '/sales/customer_payments.php',
                'Customer Payment',
                'Receive payment from your customers',
                'money'
            ),
            'customer-credit'=>array(
                '/sales/credit_note_entry.php?NewCredit=Yes',
                'Create a Credit Note',
                'Credit your customer for goods returned',
                'mail-reply-all'
            ),
            'supplier-invoice'=>array(
                '/purchasing/po_entry_items.php?NewInvoice=Yes',
                'Create a Supplier Invoice',
                'Purchase items from supplier',
                'truck'
            ),
            'supplier-payment'=>array(
                '/purchasing/supplier_payment.php',
                'Supplier Payment',
                'Pay your suppliers',
                'bank'
            ),
            'reconcile'=>array(
                '/gl/bank_account_reconcile.php',
                'Bank Reconciliation',
                'View and reconcile your bank statement',
                'file-excel-o'
            ),
            'bank-payment'=>array(
                '/gl/gl_bank.php?NewPayment=Yes',
                'Pay Expenses',
                'Manually capture expenses into your bank',
                'cart-arrow-down'
            ),
            'bank-trans'=>array(
                '/gl/inquiry/bank_inquiry.php',
                'View Bank Transaction',
                'View payments, receipts and bank tranfer',
                'bank'
            ),
            'customer-statement'=>array(
                '/reporting/reports_main.php?Class=0&REP_ID=108',
                'Send Customer Statements',
                'Email ( print) statements to all your customer',
                'send-o'
            ),
            'profit-loss'=>array(
                '/gl/inquiry/profit_loss.php',
                'Profit and Loss',
                'View your Profit and Loss report',
                'search'
            ),
            'balance-sheet'=>array(
                '/gl/inquiry/balance_sheet.php',
                'Balance Sheet',
                'Statement of Assets and Liabilities report',
                'balance-scale'
            ),
            'gst'=>array(
                'gst/form-5',
                'Prepare GST Return',
                'Tax returns and Tax reporting',
                'list-ol'
            ),
            'setup'=>array(
                '/admin/company_preferences.php',
                'Company Maintenance',
                'Manage branding, financial years, Tax and so on',
                'gear'
            )
        );
        if( ! defined('COUNTRY') OR COUNTRY == 60 ){
            $home_shoft['gst'][0] = "gst/form-3";
        }

        module_view('home',array('app'=>$home_shoft),true, false,'dashboard' );
    }

    function display($apps = null, $title = 'Sales Dashboard')
    {
        page($title);
        $html = NULL;

        if (is_string($apps)) {
            $blocks = $this->model->items($apps, $uid = 1);
        } elseif (is_array($apps)) {
            $blocks = $apps;
        }

        $column_left = NULL;
        $column_right = NULL;
        $count = 0;
        foreach ($blocks as $wg) {

            if (method_exists($this, $wg->widget)) {
                $method = $wg->widget;

                $param = json_decode(html_entity_decode(isset($wg->param) ? $wg->param : NULL, ENT_QUOTES));
                if (is_object($param)) {
                    $graph_type = (isset($param->graph_type)) ? $param->graph_type : NULL;
                } elseif (isset($wg->graph_type) && is_string($wg->graph_type)) {
                    $graph_type = $wg->graph_type;
                }

                $graph_html = $this::$method($graph_type, $wg->description, isset($wg->id) ? $wg->id : null);
                if ($method == 'sales_invoice_overdue' || $method == 'purchase_invoice_overdue') {
                    $html .= '<div class="col-md-12">' . $graph_html . '</div>';
                    // die('go here');
                } else
                    if ($graph_html) {
                        $count ++;
                        if ($count % 2 == 1) {
                            $column_left .= $graph_html;
                        } else {
                            $column_right .= $graph_html;
                        }
                    }
            }
        }

        if ($column_right != NULL) {
            $html .= '<div class="col-md-6"><div class="row">' . $column_left . '</div></div>';
            $html .= '<div class="col-md-6"><div class="row">' . $column_right . '</div></div>';
        } else {
            $html .= '<div class="col-md-12">' . $column_left . '</div>';
        }
        echo '<div class="row">' . $html . '</div>';
        end_page();
    }

    private function customers($graph_type = 'ColumnChart', $title = NULL, $id = 0)
    {
        $debtors = $this->model->debtors(begin_fiscalyear(), Today(), 10);

        if ($graph_type == 'Table') {

            $table = array(
                'name' => 'Customer',
                'total' => array(
                    'Amount',
                    'text-right',
                    20
                )
            )
            ;
            $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';
            $html .= $this->ci->view('common/table-block', array(
                'style' => 'striped',
                'table' => $table,
                'items' => $debtors,
                'class' => 'block dashboard'
            ), true);
            $html .= '</div>';
        } else
            if ($graph_type == 'ColumnChart') {
                include_once (ROOT . "/reporting/includes/class.graphic.inc");
                $pg = new graph();
                $i = 0;

                foreach ($debtors as $row) {
                    $pg->x[$i] = $row->debtor_no . " " . $row->name;
                    $pg->y[$i] = $row->total;
                    $i ++;
                }
                $pg->title = $title;
                $pg->axis_x = _("Customer");
                $pg->axis_y = _("Amount");
                $pg->graphic_1 = $today;
                $pg->type = 2;
                $pg->skin = 1;
                $pg->built_in = false;
                $img_file = "/pdf_files/" . uniqid("") . ".png";
                $filename = company_path() . $img_file;
                $pg->display($filename, true);

                $html = '<div class="dashboard_item clearfix" >';
                $html .= '<img src="' . COMPANY_ASSETS . '/' . $img_file . '" border="0" alt="$title">';
                $html .= '</div>';
            }
        return $html;
    }

    private function sales_invoice_overdue($graph_type = null, $title = NULL, $id = 0)
    {
        $limit = 10;
        $invoices = $this->model->sales_overdue(Today(), $limit);

        if ($invoices && count($invoices) > 0) {
            $table = array(
                'trans_no' => '#',
                'tran_date' => 'Date',
                'due_date' => 'Due Date',
                'debtor_no_name' => 'Customer',
                'curr_code' => 'Currency',
                'total' => array(
                    'Total',
                    'text-right'
                ),
                'remainder' => array(
                    'Remainder',
                    'text-right'
                ),
                'days' => 'Days'
            );
            $html = '<div class="dashboard_item clearfix" ><h3>' . $limit . '/' . count($invoices) . ' Overdue Sales Invoices</h3>';
            $html .= $this->ci->view('common/table-block', array(
                'style' => 'striped',
                'table' => $table,
                'items' => $invoices,
                'class' => 'block dashboard',
                'limit' => 10
            ), true);
            $html .= '</div>';
            return $html;
        }
    }

    private function purchase_invoice_overdue($graph_type = null, $title = NULL, $id = 0)
    {
        $limit = 10;
        $invoices = $this->model->purchase_overdue(Today(), $limit);

        if ($invoices && count($invoices) > 0) {
            $table = array(
                'trans_no' => '#',
                'tran_date' => 'Date',
                'due_date' => 'Due Date',
                'supp_name' => 'Supplier',
                'curr_code' => 'Currency',
                'total' => array(
                    'Total',
                    'text-right'
                ),
                'remainder' => array(
                    'Remainder',
                    'text-right'
                ),
                'days' => 'Days'
            );
            $html = '<div class="dashboard_item clearfix" ><h3>' . $limit . '/' . count($invoices) . ' Overdue Purchase Invoices</h3>';
            $html .= $this->ci->view('common/table-block', array(
                'style' => 'striped',
                'table' => $table,
                'items' => $invoices,
                'class' => 'block dashboard',
                'limit' => 10
            ), true);
            $html .= '</div>';
            return $html;
        }
    }

    private function suppliers($graph_type = 'ColumnChart', $title = NULL, $id = 0)
    {
        $suppliers = $this->model->suppliers(NULL, Today(), 10);

        if ($graph_type == 'html' or $graph_type == 'Table') {
            $table = array(
                'supp_name' => 'Supplier',
                'total' => array(
                    'Amount',
                    'text-right',
                    20
                )
            )
            ;
            $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';
            $html .= $this->ci->view('common/table-block', array(
                'style' => 'striped',
                'table' => $table,
                'items' => $suppliers,
                'class' => 'block dashboard'
            ), true);
            $html .= '</div>';
        } else
            if ($graph_type == 'ColumnChart') {
                include_once (ROOT . "/reporting/includes/class.graphic.inc");
                $pg = new graph();
                $i = 0;

                foreach ($suppliers as $row) {
                    $pg->x[$i] = $row->supplier_id . " " . $row->supp_name;
                    $pg->y[$i] = $row->total;
                    $i ++;
                }

                $pg->title = $title;
                $pg->axis_x = _("Supplier");
                $pg->axis_y = _("Amount");
                $pg->graphic_1 = $today;
                $pg->type = 2;
                $pg->skin = 1;
                $pg->built_in = false;
                $img_file = "/pdf_files/" . uniqid("") . ".png";
                $filename = company_path() . $img_file;
                $pg->display($filename, true);

                $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';
                $html .= '<img src="' . COMPANY_ASSETS . '/' . $img_file . '" border="0" alt="$title">';
                $html .= '</div>';
            }
        return $html;
    }

    private function weeklysales($graph_type = 'LineChart', $title = NULL, $id = 0)
    {
        $sales = $this->model->weekly_sales(10);

        $rows = array();
        // flag is not needed
        $flag = true;
        $table = array();
        $table['cols'] = array(
            array(
                'label' => 'Week End',
                'type' => 'string'
            ),
            array(
                'label' => 'Gross Sales',
                'type' => 'number'
            )
        );

        $rows = array();
        // while($r = db_fetch_assoc($result)) {
        foreach ($sales as $row) {
            $temp = array();
            // the following line will used to slice the Pie chart
            $temp[] = array(
                'v' => $row->week_end,
                'f' => sql2date($row->week_end)
            );
            $temp[] = array(
                'v' => (float) $row->gross_sales,
                'f' => number_format2($row->gross_sales, user_price_dec())
            );
            $rows[] = array(
                'c' => $temp
            );
        }

        $table['rows'] = $rows;

        $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';

        if ($graph_type == 'LineChart') {
            $jsonTable = json_encode($table);

            $js = "google.load('visualization', '1', {'packages':['corechart','table']});
                    google.setOnLoadCallback(drawChart" . $id . ");
                function drawChart" . $id . "() {
                  var data = new google.visualization.DataTable(" . $jsonTable . ");
                  var options = {";
            if ($this->graph_type != 'Table')
                $js .= "height: 300, ";
            $js .= "title: '" . $title . "'
                    };
                  var chart" . $id . " = new google.visualization.$graph_type(document.getElementById('widget_div_$id'));
                              chart" . $id . ".draw(data, options);
                }";
            add_js_source($js);
            $html .= '<div id="widget_div_' . $id . '" class="dragbox-content" ></div>';
        } else {
            $table = array(
                'week_end' => array(
                    'Week End',
                    null,
                    20
                ),
                'gross_sales' => array(
                    'Gross Sales',
                    'text-right',
                    80,
                    'number'
                )
            )
            ;
            $html .= $this->ci->view('common/table-block', array(
                'style' => 'striped',
                'table' => $table,
                'items' => $sales,
                'class' => 'block dashboard'
            ), true);
        }

        $html .= '</div>';

        return $html;
    }

    private function weeklypurchase($graph_type = 'LineChart', $title = NULL, $id = 0)
    {
        $sales = $this->model->weekly_purchase(10);

        $rows = array();
        // flag is not needed
        $flag = true;
        $table = array();
        $table['cols'] = array(
            array(
                'label' => 'Week End',
                'type' => 'string'
            ),
            array(
                'label' => 'Gross Sales',
                'type' => 'number'
            )
        );

        $rows = array();
        // while($r = db_fetch_assoc($result)) {
        foreach ($sales as $row) {
            $temp = array();
            // the following line will used to slice the Pie chart
            $temp[] = array(
                'v' => $row->week_end,
                'f' => sql2date($row->week_end)
            );
            $temp[] = array(
                'v' => (float) $row->gross_sales,
                'f' => number_format2($row->gross_sales, user_price_dec())
            );
            $rows[] = array(
                'c' => $temp
            );
        }

        $table['rows'] = $rows;

        $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';

        if ($graph_type == 'LineChart') {
            $jsonTable = json_encode($table);

            $js = "google.load('visualization', '1', {'packages':['corechart','table']});
                    google.setOnLoadCallback(drawChart" . $id . ");
                function drawChart" . $id . "() {
                  var data = new google.visualization.DataTable(" . $jsonTable . ");
                  var options = {";
            if ($this->graph_type != 'Table')
                $js .= "height: 300, ";
            $js .= "title: '" . $title . "'
                    };
                  var chart" . $id . " = new google.visualization.$graph_type(document.getElementById('widget_div_$id'));
                      chart" . $id . ".draw(data, options);
                }";
            add_js_source($js);
            $html .= '<div id="widget_div_' . $id . '" class="dragbox-content" ></div>';
        } else {
            $table = array(
                'week_end' => array(
                    'Week End',
                    null,
                    20
                ),
                'gross_sales' => array(
                    'Gross Sales',
                    'text-right',
                    80,
                    'number'
                )
            )
            ;
            $html .= $this->ci->view('common/table-block', array(
                'style' => 'striped',
                'table' => $table,
                'items' => $sales,
                'class' => 'block dashboard'
            ), true);
        }

        $html .= '</div>';

        return $html;
    }

    function dailysales($graph_type = 'LineChart')
    {
        // $sales = $this->model->daily_sales(10);
    }

    private function items($graph_type = 'ColumnChart', $title = NULL, $id = 0)
    {
        $stock_items = $this->model->stock_items(begin_fiscalyear(), Today(), 10);

        if ($graph_type == 'html' or $graph_type == 'Table') {
            $table = array(
                'description' => array(
                    'Item',
                    NULL,
                    70
                ),
                'total' => array(
                    'Amount',
                    'text-right',
                    20,
                    'number'
                ),
                'qty' => array(
                    'Quantity',
                    'text-right',
                    10
                )
            )
            ;
            $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';
            $html .= $this->ci->view('common/table-block', array(
                'style' => 'striped',
                'table' => $table,
                'items' => $stock_items,
                'class' => 'block dashboard'
            ), true);
            $html .= '</div>';
        } else
            if ($graph_type == 'ColumnChart') {
                include_once (ROOT . "/reporting/includes/class.graphic.inc");
                $pg = new graph();
                $i = 0;

                foreach ($stock_items as $row) {
                    $pg->x[$i] = $row->supplier_id . " " . $row->supp_name;
                    $pg->y[$i] = $row->total;
                    $i ++;
                }
                $pg->title = $title;
                $pg->axis_x = _("Supplier");
                $pg->axis_y = _("Amount");
                $pg->graphic_1 = $today;
                $pg->type = 2;
                $pg->skin = 1;
                $pg->built_in = false;
                $img_file = "/pdf_files/" . uniqid("") . ".png";
                $filename = company_path() . $img_file;
                $pg->display($filename, true);

                $html = '<div class="dashboard_item clearfix" >';
                $html .= '<img src="' . COMPANY_ASSETS . '/' . $img_file . '" border="0" alt="$title">';
                $html .= '</div>';
            } elseif ($graph_type == 'PieChart') {
                include_once (ROOT . "/reporting/includes/class.graphic.inc");
                $pg = new graph();
                $i = 0;
                foreach ($stock_items as $row) {
                    $pg->x[$i] = $row->descriptio;
                    $pg->y[$i] = $row->total;
                    $i ++;
                }
                $pg->title = $title;
                $pg->axis_x = _("Item");
                $pg->axis_y = _("Amount");
                $pg->graphic_1 = $today;
                $pg->type = 2;
                $pg->skin = 1;
                $pg->built_in = false;
                $img_file = "/pdf_files/" . uniqid("") . ".png";
                $filename = company_path() . $img_file;
                $pg->display($filename, true);
                // echo "<img src='$filename' border='0' alt='$title' style='max-width:100%'>";
                $html = '<div class="dashboard_item clearfix" >';
                $html .= '<img src="' . COMPANY_ASSETS . '/' . $img_file . '" border="0" alt="$title">';
                $html .= '</div>';
            }
        return $html;
    }

    private function glreturn($graph_type = 'ColumnChart', $title = NULL, $id = 0)
    {
        $gl_item = $this->model->gl_return(begin_fiscalyear(), Today(), 10);
        $html = NULL;
        if ($graph_type == 'Table') {

            $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';
            $html .= $this->ci->temp_view('glreturn_table', array(
                'items' => $gl_item
            ), false, 'dashboard', false);
            $html .= '</div>';
        } elseif ($graph_type == 'PieChart') {
            $pg = new graph();

            $i = 0;
            $total = 0;
            foreach ($gl_item as $row) {
                if ($row->ctype > 3) {
                    $total += $row->total;
                    $myrow['total'] = - $row->total;
                    $pg->x[$i] = $row->class_name;
                    $pg->y[$i] = abs($row->total);
                    $i ++;
                }
            }
            $pg->x[$i] = $title;
            $pg->y[$i] = - $total;
            $pg->title = $title;
            $pg->axis_x = _("Class");
            $pg->axis_y = _("Amount");
            $pg->graphic_1 = Today();
            $pg->type = 5;
            $pg->skin = 1;
            $pg->built_in = false;
            $img_file = "/pdf_files/" . uniqid("") . ".png";
            $filename = company_path() . $img_file;
            $pg->display($filename, true);

            $html = '<div class="dashboard_item clearfix" >';
            $html .= '<img src="' . COMPANY_ASSETS . '/' . $img_file . '" border="0" alt="' . $title . '">';
            $html .= '</div>';
        }
        return $html;
    }

    private function bankbalances($graph_type = 'ColumnChart', $title = NULL)
    {
        $items = $this->model->bankBalances(Today());
        if ($items && count($items) > 0) {
            $table = array(
                'bank_account_name' => 'Account',
                'balance' => array(
                    'Balance',
                    'text-right',
                    70,
                    'number'
                )
            )
            ;
            $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';
            $html .= $this->ci->view('common/table-block', array(
                'style' => 'striped',
                'table' => $table,
                'items' => $items,
                'class' => 'block dashboard border_ccc'
            ), true);
            $html .= '</div>';
            return $html;
        }
    }

    private function dailybankbalances($graph_type = 'ColumnChart', $title = NULL, $id = 0)
    {
        $days_future = 15;
        $days_past = 15;

        $balances = $this->model->dailyBankBalances(null, $days_past, $days_future);
        if ($balances && count($balances) > 0) {

            $rows = array();
            // flag is not needed
            $flag = true;
            $table = array();
            $table['cols'] = array(
                array(
                    'label' => 'Date',
                    'type' => 'string'
                ),
                array(
                    'label' => 'Balance',
                    'type' => 'number'
                )
            );

            $rows = array();
            $total = 0;
            $last_day = 0;
            $date = add_days(Today(), - $days_past);
            $balance_date = $date;

            $bank_name = NULL;
            foreach ($balances as $r) {
                $bank_name = $r->bank_account_name;
                if ($r->trans_date == null) {
                    $total = $r->amount;
                } else {
                    $balance_date = sql2date($r->trans_date);
                    while (date1_greater_date2($balance_date, $date)) {
                        $temp = array();
                        $temp[] = array(
                            'v' => (string) $date,
                            'f' => $date
                        );
                        $temp[] = array(
                            'v' => (float) $total,
                            'f' => number_format2($total, user_price_dec())
                        );
                        $rows[] = array(
                            'c' => $temp
                        );
                        $date = add_days($date, 1);
                    }
                    $total += $r->amount;
                    $temp = array();
                    $temp[] = array(
                        'v' => (string) $balance_date,
                        'f' => $balance_date
                    );
                    $temp[] = array(
                        'v' => (float) $total,
                        'f' => number_format2($total, user_price_dec())
                    );
                    $rows[] = array(
                        'c' => $temp
                    );
                    $date = $balance_date;
                }
            }
            $end_date = add_days(Today(), $days_future);
            while (date1_greater_date2($end_date, $date)) {
                $temp = array();
                $temp[] = array(
                    'v' => (string) $date,
                    'f' => $date
                );
                $temp[] = array(
                    'v' => (float) $total,
                    'f' => number_format2($total, user_price_dec())
                );
                $rows[] = array(
                    'c' => $temp
                );
                $last_day ++;
                $date = add_days($date, 1);
            }

            $table['rows'] = $rows;
            $jsonTable = json_encode($table);

            $js = "google.load('visualization', '1', {'packages':['corechart','table']});
            google.setOnLoadCallback(drawChart" . $id . ");
            function drawChart" . $id . "() {
              var data = new google.visualization.DataTable(" . $jsonTable . ");
              var options = {";
            if (! isset($this->graph_type) || $this->graph_type != 'Table')
                $js .= "height: 300, ";
            $js .= "title: '" . $title . "'
                };
              var chart" . $id . " = new google.visualization." . $graph_type . "(document.getElementById('widget_div_" . $id . "'));
              chart" . $id . ".draw(data, options);
            }";
            add_js_source($js);

            $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';
            $html .= '<div id="widget_div_' . $id . '" class="dragbox-content" ></div>';
            $html .= '</div>';
            return $html;
        }
    }

    private function banktransactions()
    {
        $bank_trans_model = $this->ci->module_model('bank', 'trans', true);

        $days_past = $days_future = 15;
        $start_date = add_days(Today(), - $days_past);
        $end_date = add_days(Today(), $days_future);

        // $bank_act = $bank_trans_model->get_bankID_balanceBigest($end_date);
        $bank_act = $bank_trans_model->get_bankID_balanceBigest();
        if ($bank_act) {

            $trans = $bank_trans_model->get_bank_trans_for_bank_account($bank_act, $start_date, $end_date);

            if ($trans && count($trans) > 0) {

                $data = array(
                    'items' => $trans,
                    'class' => 'block dashboard',
                    'date_begin' => $start_date,
                    'date_end' => $end_date
                );
                $data['table'] = array(
                    'trans_no' => array(
                        '#',
                        NULL,
                        5
                    ),
                    'trans_date' => array(
                        'Date',
                        'text-center',
                        10
                    ),
                    'amount' => array(
                        'Receipt',
                        null,
                        10
                    ),
                    'person_id' => array(
                        'Payment',
                        null,
                        10
                    ),
                    'qty' => array(
                        'Balance',
                        null,
                        10
                    ),
                    'qty' => array(
                        'Person/Item',
                        null,
                        10
                    ),
                    'qty' => array(
                        'Memo',
                        null,
                        10
                    ),
                    'qty' => array(
                        '',
                        null,
                        10
                    )
                )
                ;

                $data['open_balance'] = $bank_trans_model->get_balance($bank_act, $start_date);

                $html = '<div class="dashboard_item clearfix" ><h3>' . $title . '</h3>';
                $html .= $this->ci->temp_view('transactions', $data, false, 'bank', false);
                $html .= '</div>';
            }
            return $html;
        }
    }
}