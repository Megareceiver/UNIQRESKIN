<?php

function alloc_link($row)
{
    $tran_type = $row["type"];
    if( strlen($tran_type) < 1 ){
        $tran_type = $row['tran_type'];
    }

    $tran_no = $row['trans_no'];
    if( intval($tran_no) < 1 ){
        $tran_no = $row['tran_no'];
    }

    $html = NULL;
    switch ($tran_type){
        case ST_BANKPAYMENT:
        case ST_SUPPCREDIT:
        case ST_SUPPAYMENT:
            if( (- $row["TotalAmount"] - $row["Allocated"]) >= 0 ){
                $uri = "purchasing/allocations/supplier_allocate.php?trans_no=$tran_no&trans_type=$tran_type&supplier_id=". $row["supplier_id"];
                $html = anchor($uri, '<i class="fa fa-tasks"></i>', array('title' => 'Allocations') );
            }
            break;
        case ST_SUPPINVOICE:
            if( ( $row["TotalAmount"] - $row["Allocated"]) > 0 ){
                $uri = "purchasing/supplier_payment.php?PInvoice=$tran_no&supplier_id=".$row["supplier_id"];
                $html = anchor($uri, '<i class="fa fa-money"></i>', array('title' => 'Pay') );
            }

            break;

    }
    return $html;
}


function fmt_credit($row)
{
    $tran_type = $row["type"];
    if( strlen($tran_type) < 1 ){
        $tran_type = $row['tran_type'];
    }

    $number = 0;
    switch ($tran_type){
        case ST_CUSTCREDIT:
        case ST_CUSTPAYMENT:
        case ST_BANKDEPOSIT:
            $number = -$row["TotalAmount"];
            break;
        default:
            $number = $row["TotalAmount"];
            break;

    }

    return number_total($number,true,false);
}


function fmt_balance($row)
{
    $value = ($row["type"] == ST_BANKPAYMENT || $row["type"] == ST_SUPPCREDIT || $row["type"] == ST_SUPPAYMENT)
    ? -$row["TotalAmount"] - $row["Allocated"]
    : $row["TotalAmount"] - $row["Allocated"];
    return $value;
}


function fmt_debit($row)
{
    $tran_type = $row["type"];
    if( strlen($tran_type) < 1 ){
        $tran_type = $row['tran_type'];
    }

    $number = 0;
    switch ($tran_type){
        case ST_CUSTCREDIT:
        case ST_CUSTPAYMENT:
        case ST_BANKDEPOSIT:
            $number = -$row["TotalAmount"];
            break;
        default:
            $value = $row["TotalAmount"];
            break;

    }


    $value = -$row["TotalAmount"];
    return $value>=0 ? number_total($value) : '';

}

function due_date($row)
{

    $tran_type = $row["type"];
    if( strlen($tran_type) < 1 ){
        $tran_type = $row['tran_type'];
    }

    $value = "";
    switch ($tran_type){
        case ST_SALESINVOICE:
        case ST_OPENING:
        case ST_SUPPINVOICE:
        case ST_SUPPCREDIT:
            $value = $row["due_date"];
            break;
        default:
            $value = "";
            break;

    }
    return $value;


}


function systype_name($dummy, $type)
{
    global $systypes_array;
    return $systypes_array[$type];
}

