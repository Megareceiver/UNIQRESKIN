<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SupplierReport {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->db = $ci->db;

        if( !isset($ci->pdf) ){
            $ci->load_library('reporting');
        }
        $this->tcpdf = $ci->pdf->tcpdf;
        $this->pdf = $ci->pdf;

        $this->model = module_model_load( 'report','supplier' );
        $this->trans_model = module_model_load( 'transaction','supplier' );
    }

    var $supplier_balances_table = array(
        'type'=>array('title'=>'Trans Type' ,'w'=>17),
        'reference'=>array('title'=>'#','w'=>13,'class_header'=>'textcenter'),
        'tran_date'=>array('title'=>'Date','w'=>10,'class'=>'textcenter'),
        'due_date'=>array('title'=>'Due Date','w'=>10,'class'=>'textcenter'),

        'debit'=>array('title'=>'Charges','w'=>12.5,'class'=>'textright'),
        'credit'=>array('title'=>'Credits','w'=>12.5,'class'=>'textright'),
        'allocated'=>array('title'=>'Allocated','w'=>12.5,'class'=>'textright'),
        'outstanding'=>array('title'=>'Outstanding','w'=>12.5,'class'=>'textright'),
    );

    function check(){
        $from = "1-1-2015-1-1";
        $to = "31-12-2015";
        $balance = $this->trans_model->get_open_balance(null, $from);
        bug($balance);
        die('call me');
    }
    /*
     * report 201
     */
    function supplier_balances(){
        $this->pdf->title = 'Supplier Balances';
        $from =    input_val('PARAM_0');
        $to =      input_val('PARAM_1');
        $fromsupp = input_val('PARAM_2');

        if ($fromsupp == ALL_TEXT)
            $supp_view = _('All');
        else
            $supp_view = get_supplier_name($fromsupp);

        $dec = user_price_dec();

        $show_balance = input_val('PARAM_3');
        $currency = input_val('PARAM_4');
        $exchange_rate = 1;
        if ($currency == ALL_TEXT){
            $convert = true;
            $currency = _('Balances in Home currency');

        } else
            $convert = false;
        $no_zeros = input_val('PARAM_5') ? _('Yes') : _('No');
        $no_zeros = _('No');
        $comments = input_val('PARAM_6');

        $orientation = input_val('PARAM_7') ? 'L' : 'P';
        $destination = input_val('PARAM_8');

        if ($show_balance){
            unset($this->supplier_balances_table['outstanding']);
            $this->supplier_balances_table['balance'] = array('title'=>'Balance','w'=>12,'class'=>'textright');
        }

        $this->ci->smarty->assign('items_view',$this->supplier_balances_table);

        $this->db->select('supplier_id, supp_name AS name, curr_code');
        if ($fromsupp != ALL_TEXT){
            $this->db->where('supplier_id',intval($fromsupp));
        }
        $suppliers = $this->db->order_by('supp_name')->get('suppliers')->result();

        if( $suppliers && count($suppliers) > 0 ){
            $header_data = array(
                'page_title'=>$this->pdf->title,
                'now'=>date(user_date_display().' H:i O').' GMT',
                'fiscal_year'=>$this->pdf->fiscal_year,
                'date_range'=>$from.' '.$to,
                'fromsupp'=>$supp_view,
                'currency'=>$currency,
                'no_zeros'=>$no_zeros,
                'host'=>$_SERVER['SERVER_NAME'],
                'user'=>$_SESSION["wa_current_user"]->name,

                'content_w'=>$this->pdf->width
            );
            $this->tcpdf->talbe_header = $this->ci->view('reporting/header/balances',$header_data,true);
            $this->tcpdf->line_befor_content = false;
            $this->tcpdf->line_begin_page = true;

            $this->tcpdf->startPageGroup();
            $this->tcpdf->SetFillColor(230,230,230);
            $this->tcpdf->AddPage();

            $grand_total = array('debit'=>0, 'credit'=>0,'allocated'=>0, 'outstanding'=>0, 'balance'=>0,'gl_2100'=>0);
//             $suppliers = array();
            foreach ($suppliers AS $supp){
                $total = (object)array('credit'=>0,'debit'=>0,'allocated'=>0,'outstanding'=>0,'balance'=>0,'gl_2100'=>0);
                $balance_total = 0;
                if( $exchange_rate ){
                    $rate = get_exchange_rate_from_home_currency($supp->curr_code, Today());
                    $this->ci->smarty->assign('exchange_rate',$rate);
                }


                $balance = $this->trans_model->get_open_balance($supp->supplier_id, $from);
                $balance->balance = $balance->credit - $balance->debit ;

                $balance->outstanding = $balance->credit - $balance->debit - $balance->allocated;
// bug($balance);
//                 $balance = (object)array('credit'=>0,'debit'=>0,'allocated'=>0,'outstanding'=>0,'balance'=>0);

                $trans = $this->trans_model->get_transactions($supp->supplier_id, $from, $to);
//                 bug($trans);die;
                $y_befor_header = $this->tcpdf->GetY();

                if( count($trans) < 1 && abs($balance->balance) <=0 ){
                    continue;
                }

                $this->pdf->check_add_page();
                $this->pdf->line();
                $this->tcpdf->SetFont(null,'B',8);

                $this->pdf->Cell(34,$supp->name,'L',true,true);
                $this->pdf->Cell(6,$supp->curr_code,'L',true,true);
                $this->tcpdf->SetFont(null,'',8);
                $this->pdf->Cell($this->supplier_balances_table['due_date']['w'],"Open Balance",'C',true,true);
                $this->pdf->Cell($this->supplier_balances_table['debit']['w'],number_total($balance->debit),'R',true,true);
                $this->pdf->Cell($this->supplier_balances_table['credit']['w'],number_total($balance->credit),'R',true,true);
                $this->pdf->Cell($this->supplier_balances_table['allocated']['w'],number_total($balance->allocated),'R',true,true);

//                 $balance->balance -= abs($balance->allocated);

                if( $show_balance ){
                    $this->pdf->Cell($this->supplier_balances_table['balance']['w'],number_total($balance->balance),'R',true,true);
                } else {
                    $this->pdf->Cell($this->supplier_balances_table['outstanding']['w'],number_total($balance->outstanding),'R',true,true);
                }

                $this->pdf->line();

                foreach ($total AS $k=>$va){
                    $total->$k = isset($balance->$k) ? $balance->$k : 0;
                }

                foreach ($trans AS $tran){
                    $this->pdf->check_add_page();
                    $left_allocated = abs($tran->total_amount) - abs($tran->allocated);

                    if ( $no_zeros==_('Yes') && abs($left_allocated) < 0.02)
                        continue;


                    $total->balance += ($tran->credit - $tran->debit);

//                     if( $tran->credit > 0 ){
//                         $total->balance -= abs($tran->allocated);
//                     } elseif ($tran->debit > 0) {
//                         $total->balance += abs($tran->allocated);
//                     }

                    foreach ($total AS $k=>$va){
//                         if( $k=='outstanding' ){
//                             $total->$k += abs($tran->$k);
// //                             $total->outstanding += ($tran->credit-$tran->allocated) - ($tran->debit + $tran->allocated);
//                             if( in_array($tran->type, array(ST_SUPPINVOICE,ST_OPENING_SUPPLIER) ) ){
//                                 $total->outstanding = $total->outstanding + ($tran->credit - $tran->allocated);
//                             }else {
//                                 $total->outstanding -= $tran->debit - $tran->allocated;
//                             }
//                         } else {
                            $total->$k += (isset($tran->$k)) ? $tran->$k : 0;
//                         }

                    }
                    $tran->balance = $total->balance;
                    $this->supplier_balances_total($tran,null,$show_balance,0);
                }

                $this->pdf->check_add_page();
                $this->supplier_balances_total($total,"Total",$show_balance,1);
                foreach ($grand_total AS $key=>$vv){
//                     if( $key=='outstanding' ){

//                     } else {
                        $grand_total[$key] += $total->$key;
//                     }

                }
//                 $this->supplier_balances_total($grand_total,"Grand Total",$show_balance,1);
            }

            $this->supplier_balances_total($grand_total,"Grand Total",$show_balance,2);
        }
    }

    private function supplier_balances_total($total=null,$title="Total",$show_balance=false,$line_type=1){
        if( is_array($total) ){
            $total = (object)$total;
        }
        $total_cell_h  = $this->pdf->line_h;
        if( $line_type==1 ){
            $this->pdf->line(1,false);
            $total_cell_h = $this->pdf->line_h*1.5;
        } elseif( $line_type==2 ) {
            $this->pdf->line(2);
        }

        if( $line_type==0 ){
            $this->pdf->Cell($this->supplier_balances_table['type']['w'],tran_name($total->type));
            $this->pdf->Cell($this->supplier_balances_table['reference']['w'], $total->reference);
            $this->pdf->Cell($this->supplier_balances_table['tran_date']['w'],sql2date($total->tran_date),'C');
            $this->pdf->Cell($this->supplier_balances_table['due_date']['w'],sql2date($total->due_date),'C');
        } else {
            $title_w = $this->supplier_balances_table['type']['w'] + $this->supplier_balances_table['reference']['w'] + $this->supplier_balances_table['tran_date']['w'] + $this->supplier_balances_table['due_date']['w'];
            $this->tcpdf->SetFont(null,'B',8);
            $this->pdf->Cell($title_w,$title,'L',true, false,$total_cell_h);
            $this->tcpdf->SetFont(null,'',8);
        }



        $this->pdf->Cell($this->supplier_balances_table['debit']['w'],number_total($total->debit),'R',true, false,$total_cell_h);
        $this->pdf->Cell($this->supplier_balances_table['credit']['w'],number_total($total->credit),'R',true, false,$total_cell_h);
        $this->pdf->Cell($this->supplier_balances_table['allocated']['w'],number_total($total->allocated),'R',true, false,$total_cell_h);
        if( $show_balance ){
            $this->pdf->Cell($this->supplier_balances_table['balance']['w'],number_total($total->balance),'R',true, false,$total_cell_h,($line_type==2? 0:1));
        } else {
            $this->pdf->Cell($this->supplier_balances_table['outstanding']['w'],number_total($total->outstanding,false,true),'R',true, false,$total_cell_h,($line_type==2? 0:1));
        }

        if( $line_type==1 ){
//             $this->pdf->line(1,false);
        } elseif( $line_type==2 ) {
            $this->pdf->line(2);
        }

    }
}