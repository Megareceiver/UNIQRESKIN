<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class SalesDetail
{

    function __construct ()
    {
        $this->detail_model = module_model_load('detail', 'sales');
        $this->branch_model = module_model_load('branch', 'crm');
    }

    function index ()
    {
        die('view index');
    }

    function delivery_note ()
    {
        page(_("View Sales Dispatch"), true, false);
        $tran_no = get_instance()->uri->segment(4);
        $this->tran = $this->detail_model->get_trans($tran_no, ST_CUSTDELIVERY);
        $this->branch = $this->branch_model->get_item($this->tran->branch_code);
        $this->order = $this->detail_model->sales_order_header($this->tran->order_, ST_SALESORDER);
        
        box_start(sprintf(_("DISPATCH NOTE #%d"), $tran_no));
        row_start();
        col_start(4);
        portlet_start('Charge To');
        elem_p($this->tran->DebtorName);
        elem_p(nl2br($this->tran->address));
        col_start(4);
        portlet_start('Charge Branch', 'blue');
        elem_p($this->branch->br_name);
        elem_p(nl2br($this->branch->br_address));
        col_start(4);
        portlet_start('Delivered To', 'yellow-crusta');
        elem_p($this->order->deliver_to);
        elem_p(nl2br($this->order->delivery_address));
        
        col_start(12);
        portlet_start('Charge To');
            $this->delivery_detail($tran_no);
        
        col_start(12);
        $this->show_tran_details(ST_CUSTDELIVERY, $tran_no);
        row_end();
        
        box_end();
    }
    
    private function delivery_detail($tran_no){
        $bootstrap = new HtmlBootstrapLib();
        $bootstrap->row_start();
        $bootstrap->col_start(4);
            input_label("Reference", null, $this->tran->reference);
            input_label("Currency", null, $this->order->curr_code);
            input_label("Our Order No", null, tran_view_detail(ST_SALESORDER, $this->order->order_no));
        $bootstrap->col_start(4);
        input_label("Customer Order Ref.", null, $this->order->customer_ref);
        input_label("Shipping Company", null, $this->tran->shipper_name);
        input_label("Sales Type", null, $this->tran->sales_type);
        $bootstrap->col_start(4);
        input_label("Dispatch Date", null, sql2date($this->tran->tran_date));
        input_label("Due Date", null, sql2date($this->tran->due_date));
        $bootstrap->col_start(12);
        elem_p(comments_display(ST_CUSTDELIVERY, $tran_no));
        $bootstrap->row_end();
    }

    private function show_tran_details ($tran_type, $tran_no)
    {
        $cols = array(
                _("Item Code") => array(
                        'name' => 'stock_id'
                ),
                _("Item Description")=>array('name'=>'StockDescription'),
                _("Quantity"),
                _("Unit")=>array('name'=>'units','type'=>'qty'),
                _("Price")=>array('name'=>'unit_price','type'=>'amount'),
                _("Discount %")=>array('name'=>'units'),
                _("Total")=>array('name'=>'units','type'=>'amount'),
        );
        
        $sql = $this->detail_model->get_tran_details($tran_type, $tran_no, true);
        $this->table = & new_db_pager('trans_tbl', $sql, $cols, null, null, - 1);
        start_table(TABLESTYLE);
        $this->show_table();
        end_table();
    }
    
    var $table = NULL;
    private function show_table(){
        $table = $this->table;
        $table->select_records();
        foreach ($table->columns as $num_col => $col) {
            if (isset($col['head']) &&
                    ($col['type'] != 'inactive' || get_post('show_inactive'))) {
                        $attributes = array();
                        if (is_array($col)) {
                            if (isset($col['class'])) {
                                $attributes['class'] = $col['class'];
                            }
                            if (isset($col['align'])) {
                                switch ($col['align']) {
                                    case 'center':
                                        $attributes['class'] = " text-center";
                                        break;
                                }
                            }
        
                            if (isset($col['width'])) {
                                $attributes['width'] = $col['width'];
                            }
                        }
        
                        $header_label = strtoupper($col['head']);
                        if (isset($col['label'])) {
                            $header_label = $col['label'];
                        }
        
                        $headers[$header_label] = $attributes;
                    }
        }
        /* show a table of records returned by the sql */
        
        table_header($headers);
        
        if ($table->header_fun) { // if set header handler
            start_row("class='{$pager->header_class}'");
            $fun = $pager->header_fun;
            if (method_exists($pager, $fun)) {
                $h = $pager->$fun($pager);
            } elseif (function_exists($fun)) {
                $h = $fun($pager);
            }
        
            foreach ($h as $c) { // draw header columns
                $pars = isset($c[1]) ? $c[1] : '';
                label_cell($c[0], $pars);
            }
            end_row();
        }
        
        $cc = 0;
        foreach ($table->data as $line_no => $row) {
        
            $marker = $table->marker;
        
            if ($marker && $marker($row))
                start_row("class='$pager->marker_class'");
                else
                    alt_table_row_color($cc);
                    foreach ($table->columns as $k => $col) {
                        $coltype = $col['type'];
                        $cell = isset($col['name']) ? $row[$col['name']] : '';
                        if (isset($col['fun'])) { // use data input function if defined
                            $fun = $col['fun'];
                            $fun_class = isset($col['class']) ? $col['class'] : NULL;
                            if (method_exists($pager, $fun)) {
                                $cell = $pager->$fun($row, $cell);
                            } elseif (! is_null($pager->ci_control) and
                                    method_exists($pager->ci_control, $fun)) {
                                        $cell = $pager->ci_control->$fun($row);
                            } elseif (function_exists($fun)) {
                                $cell = $fun($row, $cell);
                            } else
                                $cell = '';
                        }
                        switch ($coltype) { // format column
                            case 'time':
                                label_cell($cell, "width=40");
                                break;
                            case 'date':
                                label_cell(sql2date($cell), "align='center' nowrap");
                                break;
                            case 'dstamp': // time stamp displayed as date
                                label_cell(sql2date(substr($cell, 0, 10)),
                                "align='center' nowrap");
                                break;
                            case 'tstamp': // time stamp - FIX user format
                                label_cell(
                                sql2date(substr($cell, 0, 10)) . ' ' .
                                        substr($cell, 10), "align='center'");
                                break;
                            case 'percent':
                                percent_cell($cell);
                                break;
                            case 'amount':
                                if ($cell == '')
                                    label_cell('');
                                    else {
                                        label_cell(number_total($cell),
                                                "nowrap align=right ");
                                        // amount_cell($cell, false);
                                    }
        
                                    break;
        
                            case 'qty':
                                if ($cell == '')
                                    label_cell('');
                                    else
                                        qty_cell($cell, false,
                                                isset($col['dec']) ? $col['dec'] : null);
                                        break;
                            case 'email':
                                email_cell($cell,
                                isset($col['align']) ? "align='" . $col['align'] .
                                "'" : null);
                                break;
                            case 'rate':
                                label_cell(number_format2($cell, user_exrate_dec()),
                                "align=center");
                                break;
                            case 'inactive':
                                if (get_post('show_inactive'))
                                    $pager->inactive_control_cell($row);
                                    break;
                            default:
        
                                // case 'text':
                                if (isset($col['align']))
                                    label_cell($cell, "align='" . $col['align'] . "'");
                                    else
                                        label_cell($cell);
                            case 'skip': // column not displayed
                        }
                    }
                    end_row();
        }
        
        if ($table->footer_fun) { // if set footer handler
            start_row("class='{$pager->footer_class}'");
            $fun = $pager->footer_fun;
            if (method_exists($pager, $fun)) {
                $h = $pager->$fun($pager);
            } elseif (function_exists($fun)) {
                $h = $fun($pager);
            }
        
            foreach ($h as $c) { // draw footer columns
                $pars = isset($c[1]) ? $c[1] : '';
                label_cell($c[0], $pars);
            }
            end_row();
        }
    }
}