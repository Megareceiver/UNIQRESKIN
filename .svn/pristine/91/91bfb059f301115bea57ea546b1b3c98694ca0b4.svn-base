<?php
function tran_name($type=1){
    global $systypes_array;
    return ( array_key_exists($type, $systypes_array) ) ? $systypes_array[$type] : 'no transaction name';
}

function get_tran_comment($type, $trans_no){
    $ci = get_instance();
    $tran = $ci->db->where(array('id'=>$trans_no,'type'=>$type))->get('comments');

    if( is_object($tran) ){
        $data = $tran->row();
        return isset($data->memo_) ? $data->memo_ : NULL ;
    }
    return NULL;
}

function trans_view_anchor($type, $trans_no,$label=""){
    switch ($type){
        case ST_SALESINVOICE:
        case ST_CUSTCREDIT:
        case ST_CUSTCREDIT:
        case ST_CUSTCREDIT:
            $anchor = get_trans_view_str($type, $trans_no, $label); break;
        case ST_PURCHORDER:
        case ST_SUPPINVOICE:
        case ST_SUPPCREDIT:
        case ST_SUPPAYMENT:
        case ST_SUPPRECEIVE:

            $anchor = get_supplier_trans_view_str($type, $trans_no,$label);break;
        case ST_BANKPAYMENT:
        case ST_BANKDEPOSIT:
        case ST_BANKTRANSFER:
            $anchor = get_banking_trans_view_str($type, $trans_no,$label); break;

        default:
            $anchor = get_trans_view_str($type, $trans_no, $label); break;
    }
    return $anchor;
}