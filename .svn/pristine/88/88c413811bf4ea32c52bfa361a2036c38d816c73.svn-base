<?php

function credit_link($row)
{
    if (@$_GET['popup'])
        return '';

    $tran_type = $row['tran_type'];
    $tran_no = $row['tran_no'];
    $uri = NULL;
    switch ($tran_type) {
        case ST_SALESINVOICE:
            if ($row["Outstanding"]) {
                $uri = "sales/customer_credit_invoice.php?InvoiceNumber=$tran_no";
            }
            break;
        case ST_SUPPINVOICE:
            if ($row["TotalAmount"] - $row["Allocated"]) {
                $uri = "purchasing/supplier_credit.php?New=1&invoice_no=$tran_no";
            }
            break;
        default:
            $uri = NULL;
            break;
    }

    if (strlen($uri) > 0) {
        return anchor($uri, '<i class="fa fa-reply-all text-danger"></i>', array(
            'title' => 'Credit This'
        ));
    }
}



/*
 *
 *
 */

//     $icon = 'fa-reply';
//     $uri = "/sales/allocations/customer_allocate.php?trans_no=" . $row["trans_no"]
//             ."&trans_type=" . $row["type"]."&debtor_no=" . $row["debtor_no"];
//     $attributes = array('title'=>'Allocation');
// //    $link =
// //        pager_link(_("Allocation"),
// //            "/sales/allocations/customer_allocate.php?trans_no=" . $row["trans_no"]
// //            ."&trans_type=" . $row["type"]."&debtor_no=" . $row["debtor_no"], ICON_ALLOC);

// //    if ($row["type"] == ST_CUSTCREDIT && $row['TotalAmount'] > 0)
// //    {
// //        /*its a credit note which could have an allocation */
// //        return $link;
// //    }
// //    elseif (($row["type"] == ST_CUSTPAYMENT || $row["type"] == ST_BANKDEPOSIT) &&
// //        (floatcmp($row['TotalAmount'], $row['Allocated']) >= 0))
// //    {
// //        /*its a receipt  which could have an allocation*/
// //        return $link;
// //    }
// //    else
//     if ($row["type"] == ST_CUSTPAYMENT && $row['TotalAmount'] <= 0)
//     {
//         /*its a negative receipt */
//         return '';
//     } elseif ($row["type"] == ST_SALESINVOICE && round2($row['TotalAmount'] - $row['Allocated'], 2) > 0.01) {
//         $icon = 'fa-money';
//         $uri =  "/sales/customer_payments.php?customer_id=".$row["debtor_no"]."&SInvoice=" . $row["trans_no"];
//         $attributes = array('title'=>'Payment');
//     }
// //        return pager_link(_("Payment"),
// //            "/sales/customer_payments.php?customer_id=".$row["debtor_no"]."&SInvoice=" . $row["trans_no"], ICON_MONEY);

//     return anchor($uri, '<i class="fa '.$icon.'"></i>', $attributes);
// }

