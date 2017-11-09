<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');
class PurchasesReportInvoice {

    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->input = $ci->input;
        $this->customer_trans_model = $ci->model('customer_trans',true);
		$this->purchase_model = $ci->model('purch_order',true);
		$this->purchase = $ci->model('purchase',true);
		$this->sys_model = $ci->model('config',true);
		$this->bank_model = $ci->model('bank_account',true);
		$this->contact_model = $ci->model('crm',true);
		$this->sale_order_model = $ci->model('sale_order',true);
		$this->common_model = $ci->model('common',true);
        $this->supplier_model = $ci->model('supplier',true);
        $this->supplier_trans_model = $ci->model('supplier_trans',true);

        $this->report = module_control_load('report','report');
        
        $this->supplier_module_model = module_model_load("supplier",'purchases');

    }

    function invoice_print(){


        $tran_id = 		input_val('trans_no');

        $invoice = $this->supplier_model->get_invoice($tran_id,ST_SUPPINVOICE);

        $this->rep = $this->report->front_report(_('Supplier Invoice'),$this->invoice_report_table);

        $this->rep->SetHeaderType('TemplateInvoice');

        if( is_object($invoice) ){
             $this->pdf_print($invoice);

        }
        $this->rep->End();

    }

    var $invoice_report_table = array (
        'space1'=>array(' ',2),
        'stock_id'=>array('Item Code',60),
        'description'=>array('Item Description' ,225),
        'quantity'=>array('Quantity' ,300,'center'),
        'unit_price'=>array('Units',325,'center'),
        'price'=>array('Unit Price',385,'right'),
        'total'=>array('Total',515,'right')

    );

    private function pdf_print($tran=NULL){

        $this->rep->params = array(
            'comments' => input_val('PARAM_5'),
            'bankaccount'=>0,
            'tran_date'=>sql2date($tran->tran_date),
            'reference'=>$tran->reference,
            'payment_terms'=>$tran->payment_terms_name,
            'delivery_info'=>array(
                'Order To'=>NULL,
                'Deliver To'=>$this->rep->print_company()
            ),
            'contact'=>'',
//             'contact'=>$this->contact_model->get_branch_contacts($tran->branch_code,'invoice',$tran->supplier_id),
            'aux_info' => array (
                "Supplier Reference"          =>  $tran->supp_reference,
                _ ( "Sales Person" )            =>  NULL,
                _('Your GST no.')               =>  $tran->tax_id,
                _ ( "Invoice Date" )         =>  sql2date($tran->tran_date),
                _ ( "Self Bill Approval Ref" )                =>  $tran->self_bill_approval_ref,
            )
        );

        $supplier = $this->supplier_module_model->get_detail($tran->supplier_id);
        if( is_object($supplier) ){
            $this->rep->params['delivery_info']["Order To"] = $supplier->supp_name;
            $this->rep->params['delivery_info']["Order To"].= "\n".$supplier->address;
        }


        $customer_ref = null;
        if( isset($tran->order_) ){
            $this->rep->params['aux_info']["Customer's Reference"] = $this->sale_order_model->get_field($tran->order_,'customer_ref');
        }


//         $items = $this->customer_trans_model->trans_detail('*',array('debtor_trans_type'=>ST_SALESINVOICE,'debtor_trans_no'=>$tran->trans_no));
//         bug($tran); die;
        $items = $tran->items;
        if( empty($items) ){
            return;
        } else {
            $this->rep->NewPage();
            $sign = 1;
            $SubTotal = $discountTotal = $shippingTotal = $taxTotal = 0;
            $taxes = array();
            foreach ($items AS $detail){
                if( !isset($detail->discount_percent) ){
                    $detail->discount_percent = 0;
                }
                $line_price = $detail->unit_price * $detail->quantity;

                $Net = round2($sign * ((1 - $detail->discount_percent) * $line_price), user_price_dec());
                $discountTotal += $line_price -$Net;
                $SubTotal += $Net;

                if( $detail->tax_type_id ){
                    $tax = tax_calculator($detail->tax_type_id,$line_price,$tran->tax_included);

                    if( is_object($tax) ){
                        if( !isset($taxes[$detail->tax_type_id]) ){
                            $taxes[$detail->tax_type_id] = array('name'=>$tax->name ." (".$tax->code." ".$tax->rate."%)" ,'amount'=>0);
                        }
                        $taxes[$detail->tax_type_id]['amount'] += $tax->value;
                        $taxTotal += $tax->value;
                    }
                }


                $this->rep->TextCol(1, 2,   $detail->stock_id, -2);
                $this->rep->TextCol(2, 3,   wordwrap($detail->description,40,"\n",true), 0);
                $this->rep->TextCol(3, 4,	number_format2($sign*$detail->quantity,get_qty_dec($detail->stock_id)), -21);
                $this->rep->TextCol(4, 5,	$detail->units, -2);
                $this->rep->TextCol(5, 6,	number_total($detail->unit_price), -2);
//                 $this->rep->TextCol(6, 7,	number_total($detail->discount_percent*100) . "%" , -2);
                $this->rep->TextCol(6, 7,	number_total($Net));
                $this->rep->NewLine();
            }

            $this->rep->row = $this->rep->bottomMargin + 8.5 * $this->rep->lineHeight;


            $this->rep->aligns[3] = 'right';

            $this->rep->TextCol(1, 5,	$this->rep->company['curr_default'].":".price_in_words( $tran->tax_included ? $SubTotal :$SubTotal+$taxTotal ,ST_CUSTPAYMENT));

            $this->rep->Font('bold');
            $this->rep->TextCol(3,6,	_('TOTAL INVOICE INCL. GST'));
            $this->rep->TextCol(6, 7,	number_total( $tran->tax_included ? $SubTotal :$SubTotal+$taxTotal ));


            $this->rep->NewLine(-1);
            $this->rep->TextCol(3, 6,	_('TOTAL INVOICE EX GST'));
            $this->rep->TextCol(6, 7,	number_total($tran->tax_included ? $SubTotal-$taxTotal: $SubTotal));


            $this->rep->Font();
            if( abs($discountTotal) != 0 ){
                $this->rep->NewLine(-1);
                $this->rep->TextCol(3, 6,	_('Discount Given'));
                $this->rep->TextCol(6, 7,	number_total($discountTotal));
            }



            if( count($taxes) > 0 ) foreach ($taxes AS $tax){
                if( abs($tax['amount']) != 0 ){
                    $this->rep->NewLine(-1);
                    $this->rep->TextCol(3, 6,	$tax['name'].' Amount');
                    $this->rep->TextCol(6, 7,	number_total($tax['amount']) );
                }
            }

            if( abs($shippingTotal) != 0 ){
                $this->rep->NewLine(-1);
                $this->rep->TextCol(3, 6,	_('Shipping'));
                $this->rep->TextCol(6, 7,	number_total($shippingTotal));
            }


            $this->rep->NewLine(-1);
            $this->rep->TextCol(3, 6,	_(' Sub-total'));
            $this->rep->TextCol(6, 7,	number_total($SubTotal));


        }

    }
}
