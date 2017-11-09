<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class SupplierReportAgedAnalysis extends ci
{

    function __construct()
    {
        $ci = get_instance();
        $this->input = $ci->input;
        $this->db = $ci->db;
        $this->analysis_model = module_model_load('analysis', 'supplier');

    }

    var $report_table = array(
        'type' => array(
            'Supplier',
            100,
            'left'
        ),
        'reference' => array(
            '',
            130,
            'left'
        ),
        'supp_reference' => array(
            '',
            190,
            'center'
        ),
        'tran_date' => array(
            'Current',
            250,
            'right'
        ),
        'nowdue' => array(
            '',
            320,
            'right'
        ),
        'pastdue1' => array(
            '',
            385,
            'right'
        ),
        'pastdue2' => array(
            '',
            450,
            'right'
        ),
        'debit_base' => array(
            'Total Balance',
            515,
            'right'
        )
    );

    private function report_input(){
        $to = input_val('PARAM_0');
        $supplier_id = input_val('PARAM_1');
        $currency = input_val('PARAM_2');
        $show_all = input_val('PARAM_3');
        $summaryOnly = input_val('PARAM_4');
        $no_zeros = input_val('PARAM_5');
        $graphics = input_val('PARAM_6');
        $comments = input_val('PARAM_7');
        $orientation = input_val('PARAM_8') ? 'L' : 'P';
        $destination = input_val('PARAM_9');
        $dec = user_price_dec();

        if ($destination)
            include_once (ROOT . "/reporting/includes/excel_report.inc");
        else
            include_once (ROOT . "/reporting/includes/pdf_report.inc");

        if ($graphics) {
            include_once (ROOT . "/reporting/includes/class.graphic.inc");
            $pg = new graph();
        }

        if ($supplier_id == ALL_TEXT)
            $from = _('All');
        else
            $from = get_supplier_name($supplier_id);

        if ($summaryOnly == 1)
            $summary = _('Summary Only');
        else
            $summary = _('Detailed Report');

        if ($currency == ALL_TEXT) {
            $convert = true;
            $currency = _('Balances in Home Currency');
        } else {
            $convert = false;
            $currency_taget = input_val('PARAM_2');
        }

        if ($no_zeros)
            $nozeros = _('Yes');
        else
            $nozeros = _('No');
        if ($show_all)
            $show = _('Yes');
        else
            $show = _('No');

        $PastDueDays1 = get_company_pref('past_due_days');
        $PastDueDays2 = 2 * $PastDueDays1;
        $nowdue = "1-" . $PastDueDays1 . " " . _('Days');
        $pastdue1 = $PastDueDays1 + 1 . "-" . $PastDueDays2 . " " . _('Days');
        $pastdue2 = _('Over') . " " . $PastDueDays2 . " " . _('Days');
        $this->report_table['nowdue'][0] = $nowdue;
        $this->report_table['pastdue1'][0] = $pastdue1;
        $this->report_table['pastdue2'][0] = $pastdue2;

        $params = array(
            0 => $comments,
            1 => array(
                'text' => _('End Date'),
                'from' => $to,
                'to' => ''
            ),
            2 => array(
                'text' => _('Supplier'),
                'from' => $from,
                'to' => ''
            ),
            3 => array(
                'text' => _('Currency'),
                'from' => $currency,
                'to' => ''
            ),
            4 => array(
                'text' => _('Type'),
                'from' => $summary,
                'to' => ''
            ),
            5 => array(
                'text' => _('Show Also Allocated'),
                'from' => $show,
                'to' => ''
            ),
            6 => array(
                'text' => _('Suppress Zeros'),
                'from' => $nozeros,
                'to' => ''
            )
        );

//         if ($convert)
//             $headers[2] = _('currency');

        $this->rep = new FrontReport(_('Aged Supplier Analysis'), "AgedSupplierAnalysis", user_pagesize(), 9, $orientation);

        list ($headers, $cols, $aligns) = get_instance()->reporting->report_front_params($this->report_table);

        if ($orientation == 'L')
            recalculate_cols($cols);

        $this->rep->Font();
        $this->rep->Info($params, $cols, $headers, $aligns);
    }

    var $aged_origin_total = array(0,0,0,0,0);
    var $aged_home_total = array(0,0,0,0,0);
    function aged_analysis_print()
    {
        $this->report_input();
        $to = input_val('PARAM_0');
        $supplier_id = input_val('PARAM_1');
        $currency = input_val('PARAM_2');
        $show_all = input_val('PARAM_3');
        $summaryOnly = input_val('PARAM_4');
        $no_zeros = input_val('PARAM_5');

        $dec = user_price_dec();
        $this->rep->NewPage();

//         $total = array();
//         $total[0] = $total[1] = $total[2] = $total[3] = $total[4] = 0.0;

//         $PastDueDays1 = get_company_pref('past_due_days');
//         $PastDueDays2 = 2 * $PastDueDays1;

//         $nowdue = "1-" . $PastDueDays1 . " " . _('Days');
//         $pastdue1 = $PastDueDays1 + 1 . "-" . $PastDueDays2 . " " . _('Days');
//         $pastdue2 = _('Over') . " " . $PastDueDays2 . " " . _('Days');

        $this->db->from('suppliers')->select('supplier_id, supp_name AS name , curr_code');
        if ($supplier_id != ALL_TEXT) {
            $this->db->where('supplier_id', $supplier_id);
        }

        if ($currency != ALL_TEXT and ! empty($currency_taget)) {
            $this->db->where('curr_code', $currency_taget);
        }

        $result = $this->db->order_by('curr_code, supp_name')
            ->get()
            ->result_array();

        $base_currency = curr_default();
        $currency_group = $base_currency;

        $rep = $this->rep;
        foreach ($result as $myrow) {
            // if (! $convert && $currency_taget != $myrow['curr_code'])
            // continue;

            if( $myrow['curr_code'] != $currency_group ){

                if( array_sum($this->aged_origin_total) > 0 ){
                    $this->line_total( "Grand Total",$currency_group,$this->aged_origin_total,2);
                }


                $currency_group = $myrow['curr_code'];
                foreach ($this->aged_origin_total AS $k=>$val){
                    $this->aged_origin_total[$k] = 0;
                }
//                 foreach ($this->aged_home_total AS $k=>$val){
//                     $this->aged_home_total[$k] = 0;
//                 }
            }

            $supprec = $this->analysis_model->get_supplier_details($myrow['supplier_id'], $to, $show_all);
            if (! $supprec)
                continue;


            $supplier_total = array(
                $supprec["Balance"] - $supprec["Due"],
                $supprec["Due"] - $supprec["Overdue1"],
                $supprec["Overdue1"] - $supprec["Overdue2"],
                $supprec["Overdue2"],
                $supprec["Balance"]
            );

            foreach ($this->aged_origin_total AS $i=>$v){
                $this->aged_origin_total[$i] += floatval($supplier_total[$i]);
            }

            if ($no_zeros && floatcmp(array_sum($supplier_total), 0) == 0)
                continue;

            $this->line_total( $myrow['name'],$myrow['curr_code'],$supplier_total);

            if( $myrow['curr_code'] != $base_currency ){
                $supprec_home_currency = $this->analysis_model->get_supplier_details($myrow['supplier_id'], $to, $show_all,true);
//                 bug($supprec);
//                 bug($supprec_home_currency);die;

                $supplier_total_base = array(
                    $supprec_home_currency["Balance"] - $supprec_home_currency["Due"],
                    $supprec_home_currency["Due"] - $supprec_home_currency["Overdue1"],
                    $supprec_home_currency["Overdue1"] - $supprec_home_currency["Overdue2"],
                    $supprec_home_currency["Overdue2"],
                    $supprec_home_currency["Balance"]
                );

                foreach ($this->aged_home_total AS $i=>$v){
                    $this->aged_home_total[$i] += floatval($supplier_total_base[$i]);
                }

                $this->line_total( "",$base_currency,$supplier_total_base);
            } else {
                foreach ($this->aged_home_total AS $i=>$v){
                    $this->aged_home_total[$i] += floatval($supplier_total[$i]);
                }
            }

            if (! $summaryOnly) {
                $res = $this->analysis_model->analysis_invoices($myrow['supplier_id'], $to, $show_all);
                if (count($res) < 1)
                    continue;
                $rep->Line($rep->row + 4);
                foreach ($res as $trans) {
                    $rep->NewLine(1, 2);
                    $rep->TextCol(0, 1, tran_name($trans['type']), - 2);
                    $rep->TextCol(1, 2, $trans['reference'], - 2);
                    $rep->TextCol(2, 3, sql2date($trans['tran_date']), - 2);

                    // foreach ($trans as $i => $value)
                    // $trans[$i] *= $rate;

                    $str = array(
                        $trans["Balance"] - $trans["Due"],
                        $trans["Due"] - $trans["Overdue1"],
                        $trans["Overdue1"] - $trans["Overdue2"],
                        $trans["Overdue2"],
                        $trans["Balance"]
                    );
                    for ($i = 0; $i < count($str); $i ++)
                        $rep->AmountCol($i + 3, $i + 4, $str[$i], $dec);
                }
                $rep->Line($rep->row - 8);
                $rep->NewLine(2);
            }
        }
//         die('aaa');

//         if ($summaryOnly) {
            $rep->Line($rep->row + 4);
            $rep->NewLine();
//         }
        $this->line_total( "Grand Total",$currency_group,$this->aged_origin_total,-1);

        $rep->End();
    }

    private function line_total($title,$currency,$total_array = null,$new_line=0){
        $dec = user_price_dec();

        if(  count($total_array) < 3 ){
//             $total_array = $this->aged_origin_total;
        }

        $this->rep->fontSize += 2;
        $this->rep->TextCol(0, 2, $title);
        $this->rep->TextCol(2, 3, $currency);
        $this->rep->fontSize -= 2;


        foreach ($total_array AS $i=>$val){
            $this->rep->AmountCol($i + 3, $i + 4, $val, $dec);
        }


        $base_currency = curr_default();

        if( $title == "Grand Total" AND $currency != $base_currency AND array_sum($this->aged_home_total) > 0 ){
            $this->rep->NewLine();
            $this->rep->Line($this->rep->row + 4);
            $this->rep->NewLine();
            $this->rep->fontSize += 2;
            $this->rep->TextCol(0, 2, "$title in Base Currency");
            $this->rep->TextCol(2, 3, $base_currency);
            $this->rep->fontSize -= 2;

            foreach ($this->aged_home_total AS $i=>$val){
                $this->rep->AmountCol($i + 3, $i + 4, $val, $dec);
            }
        }

        if( $new_line > 0 ){
            $this->rep->NewLine($new_line);
        } elseif( $new_line != -1 ) {
            $this->rep->NewLine(1, 2);
        }


    }
}