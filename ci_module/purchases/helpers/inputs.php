<?php

function supp_transactions_bootstrap($label, $name, $selected = null)
{
    global $all_items;
    $supplier_transaction_types = array(
            $all_items=>_("All Types"),
            '6'=>_("GRNs"),
            '1'=> _("Invoices"),
            '2'=> _("Overdue Invoices"),
            '3' => _("Payments"),
            '4' => _("Credit Notes"),
            '5' => _("Overdue Credit Notes")
    );
    $input = array_selector($name, $selected, $supplier_transaction_types, array(
        'class' => 'form-control'
    ));

    form_group_bootstrap($label, $input);
}

function supplier_list_bootstrap($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false, $all = false, $editkey = false)
{
    $input = supplier_list($name, $selected_id, $all_option, $submit_on_change, $all, $editkey, 'form-control input-sm');
    form_group_bootstrap($label, $input);
}

function input_supplier_credit($supplier, $credit, $parms = '')
{
    $value_attributes = array(
        'target'=>'_blank',
        'href'=>"/purchasing/inquiry/supplier_inquiry.php?supplier_id=$supplier",
        'onclick'=>'javascript:openWindow(this.href,this.target); return false;'
    );
    $value = anchor($value_attributes['href'],price_format($credit),$value_attributes);

    input_label(_("Current Credit"), NULL , $value);
//     label_row(, "<a target='_blank' " . ($credit < 0 ? 'class="redfg"' : '') . "href='" . site_url() . "/purchasing/inquiry/supplier_inquiry.php?supplier_id=" . $supplier . "'" . " onclick=\"javascript:openWindow(this.href,this.target); return false;\" >" . price_format($credit) . "</a>", $parms);
}