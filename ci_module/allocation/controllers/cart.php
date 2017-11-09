<?php
class AllocationCart
{

    function __construct()
    {
//         $this->check_input_get();
//         $this->check_submit();
    }


    function alloca_table($show_totals) {
        global $systypes_array;

        $k = $counter = $total_allocated = 0;

        $cart = $_SESSION['alloc'];
        $supp_ref = in_array($cart->type, array(ST_SUPPCREDIT, ST_SUPPAYMENT, ST_BANKPAYMENT));

        if (count($cart->allocs))
        {
            if ($cart->currency != $cart->person_curr)
                display_heading(sprintf(_("Allocated amounts in %s:"), $cart->person_curr));
            start_table(TABLESTYLE, "width=60%");
            $th = array(_("Transaction Type"), _("#"), $supp_ref ? _("Supplier Ref"): _("Ref"), _("Date"), _("Due Date"), _("Amount"),
                _("Other Allocations"), _("Left to Allocate"), _("This Allocation"),'','');

            table_header($th);

            foreach ($cart->allocs as $id => $alloc_item) {
                if ( floatcmp(abs($alloc_item->amount), $alloc_item->amount_allocated)) {

                    alt_table_row_color($k);
                    label_cell($systypes_array[$alloc_item->type]);
                    label_cell(get_trans_view_str($alloc_item->type, $alloc_item->type_no));
                    label_cell($alloc_item->ref);
                    label_cell($alloc_item->date_, "align=right");
                    label_cell($alloc_item->due_date, "align=right");
                    amount_cell(abs($alloc_item->amount));
                    amount_cell($alloc_item->amount_allocated);

                    $_POST['amount' . $id] = price_format($alloc_item->current_allocated);

                    $un_allocated = round((abs($alloc_item->amount) - $alloc_item->amount_allocated), 6);
                    amount_cell($un_allocated, false,'', 'maxval'.$id);
//                     amount_cells(null, "amount" . $id);//, input_num('amount' . $id));

                    input_money_cells("amount" . $id,$alloc_item->current_allocated);

                    icon_anchor_cell("Alloc$id", 'success', 'fa-plus', "allocate_all($id)");

                    $unallocated = hidden("un_allocated" . $id, price_format($un_allocated), false);
                    icon_anchor_cell("DeAll$id", 'warning', 'fa-reply', "allocate_none($id)",null,$unallocated);

                    end_row();

                    $total_allocated += input_num('amount' . $id);
                }
            }

            if ($show_totals) {
                label_row(_("Total Allocated"), price_format($total_allocated),
                "colspan=8 align=right", "align=right id='total_allocated'", 3);
                /*
                 $amount = $_SESSION['alloc']->amount;

                 if ($_SESSION['alloc']->type == ST_SUPPCREDIT
                 || $_SESSION['alloc']->type == ST_SUPPAYMENT
                 ||  $_SESSION['alloc']->type == ST_BANKPAYMENT)
                     $amount = -$amount;
                */
                $amount = abs($cart->amount);

                if (floatcmp($amount, $total_allocated) < 0)
                {
                    $font1 = "<font color=red>";
                    $font2 = "</font>";
                }
                else
                    $font1 = $font2 = "";
                $left_to_allocate = price_format($amount - $total_allocated);
                label_row(_("Left to Allocate"), $font1 . $left_to_allocate . $font2,
                "colspan=8 align=right", "nowrap align=right id='left_to_allocate'",
                3);
            }
            end_table(1);
        }
        hidden('TotalNumberOfAllocs', count($cart->allocs));
    }
}