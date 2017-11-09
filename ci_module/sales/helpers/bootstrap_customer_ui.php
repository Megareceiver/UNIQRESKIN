<?php
function customer_credit_bootstrap($customer, $credit, $parms=NULL)
{
    $attributes = array(
        'class'=>NULL,
        'target'=> '_blank',
        'onclick'=>'javascript:openWindow(this.href,this.target); return false;'
    );
    if( $credit < 0 ){
        $attributes['class'] = 'redfg';
    }
    $html = anchor("sales/inquiry/customer_inquiry.php?customer_id=$customer",price_format($credit),$attributes);
    return input_label_bootstrap('Current Credit',NULL,$html);
//     label_row( _("Current Credit:"),
//     "<a target='_blank' " . ($credit<0 ? 'class="redfg"' : '')
//     ."href='".site_url()."/sales/inquiry/customer_inquiry.php?customer_id=".$customer."'"
//         ." onclick=\"javascript:openWindow(this.href,this.target); return false;\" >"
//             . price_format($credit)
//             ."</a>", $parms);
}