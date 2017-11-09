<?php


function edit_link($row)
{
    $tran_type = $row['tran_type'];
    $tran_no = $row['tran_no'];

    if (@$_GET['popup'] || get_voided_entry($tran_type, $tran_no) || is_closed_trans($tran_type, $tran_no))
        return '';

    $uri = '';

    switch ($tran_type) {
        case ST_SALESQUOTE:
            $uri = "sales/sales_order_entry.php?ModifyQuotationNumber=$tran_no";
            break;
        case ST_SALESORDER:
            $uri = "/sales/sales_order_entry.php?ModifyOrderNumber=$tran_no";
            break;

        case ST_SALESINVOICE:
            if (ABS($row['Allocated']) <= 0) {
                $uri = "sales/customer_invoice.php?ModifyInvoice=$tran_no";
            }
            break;

        case ST_PURCHORDER:
            $uri = "/purchasing/po_entry_items.php?" . SID . "ModifyOrderNumber=" . $tran_no;
            break;
        case ST_CUSTCREDIT:
            if ($row['order_'] == 0) // free-hand credit note
                $uri = "sales/credit_note_entry.php?ModifyCredit=$tran_no";
                else // credit invoice
                    $uri = "sales/customer_credit_invoice.php?ModifyCredit=$tran_no";
                break;
        case ST_CUSTDELIVERY:
            $uri = "sales/customer_delivery.php?ModifyDelivery=$tran_no";
            break;
        case ST_CUSTPAYMENT:
            $uri = "sales/customer_payments.php?trans_no=$tran_no";
            break;
        default:
    }

    if (strlen($uri) > 0) {
        return anchor($uri, '<i class="fa fa-edit"></i>', array(
            'title' => 'Edit',
            'class'=>'button text-info'
        ));
    } else {
//         return NULL;
        //return '<button class="button disable" disabled><i class="fa fa-edit"></i></button>';
        return anchor($uri, '<i class="fa fa-edit"></i>', array(
                'title' => 'Edit',
                'class'=>'button text-info disable'
        ));

    }
}

function prt_link($row)
{
    if (isset($row['type']) AND $row['type'] > 0 ) {
        $tran_type = $row['type'];
    } elseif (isset($row['tran_type']) AND $row['tran_type'] > 0) {
        $tran_type = $row['tran_type'];
    }

    if (isset($row['trans_no'])) {
        $tran_no = $row['trans_no'];
    }  elseif (isset($row['tran_no'])) {
        $tran_no = $row['tran_no'];
    }

    switch ($tran_type) {
        case ST_SALESQUOTE:
            $button = print_document_link($tran_no, _("Print"), true, $tran_type, true);
            break;
        case ST_SALESINVOICE:
            $button = print_document_link($tran_no . "-" . $tran_type, _("Print"), true, $row['type'],true, null);

            // if (defined('COUNTRY') && COUNTRY == 60) {
            // $button .= print_document_link($tran_no . "-" . $tran_type, _("Print"), true, $tran_type, 'file-text', 'icons_printtaxinvoice', null, 0, 0, 1);
            // }

            break;
        case ST_CUSTPAYMENT:
        case ST_BANKDEPOSIT:
            $button = print_document_link($tran_no . "-" . $tran_type, _("Print Receipt"), true, ST_CUSTPAYMENT, true);
            break;
        case ST_BANKPAYMENT:
            $button = '';
        case ST_CUSTDELIVERY:
            $button = print_document_link($tran_no, _("Print"), true, $tran_type, true);
            break;
        default:
            $button = print_document_link($tran_no . "-" . $tran_type, _("Print"), true, $tran_type, true);
            break;
    }
    return $button;
}

function order_link($row)
{
    $tran_type = $row['tran_type'];
    if ($tran_type == ST_SALESQUOTE) {
        $attributes = array(
            'title' => 'Sales Order',
            'class'=>'button'
        );
        $html = anchor("sales/sales_order_entry.php?NewQuoteToSalesOrder=" . $row['order_no'], '<i class="fa fa-cart-plus"></i>', $attributes);
    }
    return $html;
}

function dispatch_link($row)
{
    $tran_type = $row['tran_type'];
    $uri = NULL;
    $icon = 'fa-plus';
    if ($tran_type == ST_SALESORDER) {
        $title = 'Dispatch';
        $uri = 'sales/customer_delivery.php?OrderNumber=' . $row['order_no'];
        $icon = 'fa-truck';
    } else {
        $title = 'Sales Order';
        $uri = 'sales/customer_delivery.php?OrderNumber=' . $row['order_no'];
        $icon = 'fa-truck';
    }

    $attributes = array(
        'title' => $title,
        'class'=>'button text-primary'
    );
    return anchor($uri, '<i class="fa ' . $icon . '"></i>', $attributes);
}

function invoice_link($row)
{
    $tran_type = NULL;
    if( isset($row['tran_type']) ){
        $tran_type = $row['tran_type'];
    }
    if( strlen($tran_type) < 1 ){
        $tran_type = $row['type'];
    }
    $tran_no = 0;
    if( array_key_exists('trans_no', $row) ){
        $tran_no = $row["trans_no"];
    }
    if ($tran_type == ST_SALESORDER) {

        $uri = "/sales/sales_order_entry.php?NewInvoice=" . $row["order_no"];
        $attributes = array(
            'title' => 'Invoice',
            'class'=>'button'
        );
        $icon = 'fa-money';
        return anchor($uri, '<i class="fa ' . $icon . '"></i>', $attributes);
    } elseif ($tran_type==ST_CUSTDELIVERY){
        $uri = "/sales/customer_invoice.php?DeliveryNumber=$tran_no";
        $attributes = array(
            'title' => 'Invoice',
            'class'=>'button'
        );
        $icon = 'fa-money';

        if( $row["Outstanding"]== 0 ){
            $attributes['disabled']=true;
            return '<button '._parse_attributes($attributes).'><i class="fa ' . $icon . '"></i></button>';;
        } else {
            return anchor($uri, '<i class="fa ' . $icon . '"></i>', $attributes);
        }
    }
}

function delivery_link($row)
{
    $tran_type = $row['tran_type'];
    if ($tran_type == ST_SALESQUOTE or $tran_type == ST_SALESORDER) {

        $uri = "sales/sales_order_entry.php?NewDelivery=" . $row['order_no'];
        $attributes = array(
            'title' => 'Delivery',
            'class'=>'button text-primary'
        );
        $icon = 'fa-truck';
        return anchor($uri, '<i class="fa ' . $icon . '"></i>', $attributes);
    }
}

function allocation_link($row)
{

    //     $link = pager_link(_("Allocation"), "/sales/allocations/customer_allocate.php?trans_no=" . $row["trans_no"] . "&trans_type=" . $row["type"] . "&debtor_no=" . $row["debtor_no"], ICON_ALLOC);

    if ($row["type"] == ST_CUSTCREDIT && $row['TotalAmount'] > 0) {
        /* its a credit note which could have an allocation */
        $link = "sales/allocations/customer_allocate.php?trans_no=" . $row["trans_no"] . "&trans_type=" . $row["type"] . "&debtor_no=" . $row["debtor_no"];
        $title = _("Allocation");
        $icon = 'fa-money';
        return $link;
    } elseif (($row["type"] == ST_CUSTPAYMENT || $row["type"] == ST_BANKDEPOSIT) && (floatcmp($row['TotalAmount'], $row['Allocated']) >= 0)) {
        /* its a receipt which could have an allocation */
        //         return $link;
        $link = "sales/allocations/customer_allocate.php?trans_no=" . $row["trans_no"] . "&trans_type=" . $row["type"] . "&debtor_no=" . $row["debtor_no"];
        $title = _("Allocation");
        $icon = 'fa-money';
    } elseif ($row["type"] == ST_CUSTPAYMENT && $row['TotalAmount'] <= 0) {
        /* its a negative receipt */
        return '';
    } elseif ($row["type"] == ST_SALESINVOICE && round2($row['TotalAmount'] - $row['Allocated'], 2) > 0.01) {
        $link = "/sales/customer_payments.php?customer_id=" . $row["debtor_no"] . "&SInvoice=" . $row["trans_no"];
        $title = _("Payment");
        $icon = 'fa-money';
        //         return pager_link(_("Payment"), "/sales/customer_payments.php?customer_id=" . $row["debtor_no"] . "&SInvoice=" . $row["trans_no"], ICON_MONEY);
    }

    return anchor($link,"<li class=\"fa $icon\"></li>",array('title'=>$title,'class'=>'button text-danger'));

}


/*
 * table buttons
 */

function icon_submit($name, $value, $button_type = 'secondary', $icon='save', $async=false, $title=false,$button_text=NULL){

    if( is_null($button_type) ){
        $button_type = 'secondary';
    }

    $attributes = array(
        'type'=>'submit',
        'name'=>$name,
        'id'=>$name,
        'value'=>$value,
        'title'=>$title,
        'class'=>"btn btn-$button_type",
    );

    if( $async ){
        $attributes['class'] .= " ajaxsubmit";
    }


    if ( !is_string($icon) ){
        $icon_show = '<i class="fa fa-save"></i> ';
    } elseif( is_string($icon) ) {

        if( strpos($icon, 'fa-') !== false ){
            $icon = "fa $icon";
        }
        $icon_show = '<i class="'.$icon.'"></i> ';
    }

    $button =  "<button "._parse_attributes($attributes)." >".$icon_show." $button_text</button>\n";
    return $button;
}
function icon_submit_cells($name, $value, $button_type = 'secondary', $icon='save', $async=false, $title=false ,$button_text =NULL)
{
    $button = icon_submit($name, $value, $button_type , $icon, $async, $title, $button_text);
    echo "<td align=\"center\" >$button</td>\n";
}
function tbl_add($name,$value= 'Add Item'){
    return icon_submit_cells($name, $value , 'success', 'fa-plus', true, _('Add new item to document'));
}
function tbl_edit($name,$value = 'Edit',$td_inclue=true,$async=true){
    if( !$td_inclue ){
        return icon_submit($name, $value , 'warning', 'icon-pencil', $async, _('Edit document line'));
    }
    return icon_submit_cells($name, $value , 'warning', 'icon-pencil', $async, _('Edit document line'));
}
function tbl_remove($name,$value= 'Delete',$td_inclue=true,$async=true){
    if( !$td_inclue ){
        return icon_submit($name, $value , 'danger', 'icon-trash', $async, _('Remove line from document'));
    }
    return icon_submit_cells($name, $value , 'danger', 'icon-trash', $async, _('Remove line from document'));
}
function tbl_update($name,$value= 'Update'){
    return icon_submit_cells($name, $value , 'success', 'fa-save', true, _('Confirm changes'));
}
function tbl_cancel($name,$value= 'Cancel'){
    return icon_submit_cells($name, $value , 'info', 'fa-refresh', true, _('Cancel changes'));
}