<?php

function small_amount_cells($label, $name, $init = null, $params = null, $post_label = null, $dec = null)
{
    amount_cells_ex($label, $name, 7, 12, $init, $params, $post_label, $dec);
}

function amount_cell($label, $bold = false, $params = "", $id = null)
{
    if ($bold)
        label_cell("<b>" . price_format($label) . "</b>", "nowrap align=right " . $params, $id);
    else
        label_cell(price_format($label), "nowrap align=right " . $params, $id);
}

function label_cell($label, $params = "", $id = null, $type = 'td')
{
    global $Ajax;

    if (isset($id)) {
        $params .= " id='$id'";
        $Ajax->addUpdate($id, $id, $label);
    }
    if (is_array($params)) {
        $params = _parse_attributes($params);
    }
    echo "<$type $params>$label</$type>\n";

    return $label;
}

function tax_cell($tax_id, $params = "")
{
    $type = 'td';
    $tax = get_gst($tax_id);
    $label = NULL;
    if( is_object($tax) AND isset($tax->no) ){
        $label = $tax->no. " (".$tax->rate."%)";
    }

    echo "<$type $params>$label</$type>\n";
}
// -----------------------------------------------------------------------------------
function small_qty_cells($label, $name, $init = null, $params = null, $post_label = null, $dec = null)
{
    if (! isset($dec))
        $dec = user_qty_dec();
    amount_cells_ex($label, $name, 7, 12, $init, $params, $post_label, $dec);
}
/*
 *
 */
function delete_button_cell($name, $value, $title = false)
{
    button_cell($name, $value, $title, ICON_DELETE);
}

function edit_button_cell($name, $value, $title = false)
{
    button_cell($name, $value, $title, ICON_EDIT);
}

function button_cell($name, $value, $title = false, $icon = false, $aspect = '')
{
    echo "<td align='center'>";
    echo button($name, $value, $title, $icon, $aspect);
    echo "</td>";
}