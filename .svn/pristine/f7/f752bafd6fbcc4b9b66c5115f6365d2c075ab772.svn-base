<?php
function tran_view_detail($tran_type, $tran_no, $label = "")
{
    $anchor_attr = array(
        'target'=>'_blank',
        'onclick'=>"javascript:openWindow(this.href,this.target); return false;"
    );
    switch ($tran_type){
        case ST_CUSTDELIVERY :
            $href = anchor("sales/detail/delivery-note/$tran_no",$tran_no,$anchor_attr);
            break;
        case ST_CUSTCREDIT:
        case ST_CUSTPAYMENT:
        case ST_SALESINVOICE:
        case ST_SALESORDER:
        case ST_SALESQUOTE:
            $href = get_trans_view_str($tran_type, $tran_no, $label);
            break;
        default:
            $href = get_trans_view_str($tran_type, $tran_no, $label);
            break;
    }
    return $href;
}