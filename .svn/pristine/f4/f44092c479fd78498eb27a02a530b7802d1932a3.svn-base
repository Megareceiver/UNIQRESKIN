<?php
function customer_credit_row($customer, $credit, $parms='')
{
    global $path_to_root;

    label_row( _("Current Credit:"),
    "<a target='_blank' " . ($credit<0 ? 'class="redfg"' : '')
    ."href='".site_url()."/sales/inquiry/customer_inquiry.php?customer_id=".$customer."'"
        ." onclick=\"javascript:openWindow(this.href,this.target); return false;\" >"
            . price_format($credit)
            ."</a>", $parms);
}