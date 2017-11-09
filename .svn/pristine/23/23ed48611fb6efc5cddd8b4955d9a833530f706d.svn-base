<?php

/*
 * amount/number
 */
function amount_cells_ex($label, $name, $size, $max = null, $init = null, $params = null, $post_label = null, $dec = null)
{
    global $Ajax;
    if ($name == 'price')
        $size = 10;

    if (! isset($dec))
        $dec = user_price_dec();
    if (! isset($_POST[$name]) || $_POST[$name] == "") {
        if ($init !== null)
            $_POST[$name] = $init;
        else
            $_POST[$name] = '';
    }
    if ($label != null) {
        if ($params == null)
            $params = "class='label'";
        label_cell($label, $params);
    }
    if (! isset($max))
        $max = $size;

    $attributes = _attributes_str2array($params);
    $attributes['align'] = 'center';
    echo "<td " . _parse_attributes($attributes) . " >";

    echo "<input class='amount form-control' type=\"text\" name=\"$name\" size=\"$size\" maxlength=\"$max\" dec=\"$dec\" value=\"" . $_POST[$name] . "\">";
    // echo input_money();

    if ($post_label) {
        echo "<span id='_{$name}_label'> $post_label</span>";
        $Ajax->addUpdate($name, '_' . $name . '_label', $post_label);
    }
    echo "</td>\n";
    $Ajax->addUpdate($name, $name, $_POST[$name]);
    $Ajax->addAssign($name, $name, 'dec', $dec);
}

function amount_cells($label, $name, $init = null, $params = null, $post_label = null, $dec = null)
{
    amount_cells_ex($label, $name, 15, 15, $init, $params, $post_label, $dec);
}

function amount_row($label, $name, $init = null, $params = null, $post_label = null, $dec = null)
{
    echo "<tr>";
    amount_cells($label, $name, $init, $params, $post_label, $dec);
    echo "</tr>\n";
}

function input_money_cells($name, $value = NULL)
{
    get_instance()->bootstrap->input_only = true;
    echo '<td>';
    input_money(NULL, $name, floatval($value));
    echo "</td>";
    get_instance()->bootstrap->input_only = false;
}

/*
 * Sales Areas
 */
function sales_areas_list_cells($label, $name, $selected_id = null)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo sales_areas_list($name, $selected_id);
    echo "</td>\n";
}

function sales_areas_list_row($label, $name, $selected_id = null)
{
    echo "<tr><td class='label'>$label</td>";
    sales_areas_list_cells(null, $name, $selected_id);
    echo "</tr>\n";
}

/*
 * Sales Persons List
 */
function sales_persons_list_cells($label, $name, $selected_id = null, $spec_opt = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>\n";
    echo sales_persons_list($name, $selected_id, $spec_opt);
    echo "</td>\n";
}

function sales_persons_list_row($label, $name, $selected_id = null, $spec_opt = false)
{
    if (! $selected_id) {
        $selected_id = 1;
    }
    echo "<tr><td class='label'>$label</td>";
    sales_persons_list_cells(null, $name, $selected_id, $spec_opt);
    echo "</tr>\n";
}

/*
 *
 */
function yesno_list_cells($label, $name, $selected_id = null, $name_yes = "", $name_no = "", $submit_on_change = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo yesno_list($name, $selected_id, $name_yes, $name_no, $submit_on_change);
    echo "</td>\n";
}

function yesno_list_row($label, $name, $selected_id = null, $name_yes = "", $name_no = "", $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    yesno_list_cells(null, $name, $selected_id, $name_yes, $name_no, $submit_on_change);
    echo "</tr>\n";
}

/*
 *
 */
function dimensions_list_cells($label, $name, $selected_id = null, $no_option = false, $showname = null, $showclosed = false, $showtype = 0, $submit_on_change = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo dimensions_list($name, $selected_id, $no_option, $showname, $submit_on_change, $showclosed, $showtype);
    echo "</td>\n";
}

function dimensions_list_row($label, $name, $selected_id = null, $no_option = false, $showname = null, $showclosed = false, $showtype = 0, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    dimensions_list_cells(null, $name, $selected_id, $no_option, $showname, $showclosed, $showtype, $submit_on_change);
    echo "</tr>\n";
}

/*
 *
 */
function payment_terms_list_cells($label, $name, $selected_id = null)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo payment_terms_list($name, $selected_id);
    echo "</td>\n";
}

function payment_terms_list_row($label, $name, $selected_id = null)
{
    if (! $selected_id) {
        $selected_id = 5;
    }
    echo "<tr><td class='label'>$label</td>";
    payment_terms_list_cells(null, $name, $selected_id);
    echo "</tr>\n";
}

/*
 *
 */
function credit_status_list_cells($label, $name, $selected_id = null)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo credit_status_list($name, $selected_id);
    echo "</td>\n";
}

function credit_status_list_row($label, $name, $selected_id = null)
{
    echo "<tr><td class='label'>$label</td>";
    credit_status_list_cells(null, $name, $selected_id);
    echo "</tr>\n";
}

/*
 *
 */
function tax_groups_list_cells($label, $name, $selected_id = null, $none_option = false, $submit_on_change = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo tax_groups_list($name, $selected_id, $none_option, $submit_on_change);
    echo "</td>\n";
}

function tax_groups_list_row($label, $name, $selected_id = null, $none_option = false, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    tax_groups_list_cells(null, $name, $selected_id, $none_option, $submit_on_change);
    echo "</tr>\n";
}

/*
 *
 */
function crm_category_types_list_row($label, $name, $selected_id = null, $filter = array(), $submit_on_change = true)
{
    echo "<tr><td class='label'>$label</td><td>";
    echo crm_category_types_list($name, $selected_id, $filter, $submit_on_change);
    echo "</td></tr>\n";
}

/*
 *
 */
function select_button_cell($name, $value, $title = false)
{
    button_cell($name, $value, $title, ICON_ADD, 'selector');
}

/*
 *
 */
function inactive_control_cell($id, $value, $table, $key)
{
    global $Ajax;

    $name = "Inactive" . $id;
    $value = $value ? 1 : 0;

    if (check_value('show_inactive')) {
        if (isset($_POST['LInact'][$id]) && (get_post('_Inactive' . $id . '_update') || get_post('Update')) && (check_value('Inactive' . $id) != $value)) {
            update_record_status($id, ! $value, $table, $key);
        }
        echo '<td align="center">' . checkbox(null, $name, $value, true, '') . hidden("LInact[$id]", $value, false) . '</td>';
    }
}
//
// Displays controls for optional display of inactive records
//
function inactive_control_row($th)
{
    echo "<tr><td colspan=" . (count($th)) . ">" . "<div style='float:left;'>" . checkbox(null, 'show_inactive', null, true) . _("Show also Inactive") . "</div><div style='float:right;'>" . submit('Update', _('Update'), false, '', null, 'save') . "</div></td></tr>";
}

function record_status_list_row($label, $name)
{
    return yesno_list_row($label, $name, null, _('Inactive'), _('Active'));
}

function label_cells($label, $value, $params = "", $params2 = "", $id = '')
{
    if ($label != null)
        echo "<td $params>$label</td>\n";
    label_cell($value, $params2, $id);
}

function label_row($label, $value, $params = "", $params2 = "", $leftfill = 0, $id = '')
{
    echo "<tr trheer='' >";
    if ($params == "") {
        echo "<td class='label'>$label</td>";
        $label = null;
    }

    label_cells($label, $value, $params, $params2, $id);
    if ($leftfill != 0)
        echo "<td colspan=$leftfill></td>";

    echo "</tr>\n";
}

function quick_entry_types_list_row($label, $name, $selected_id = null, $submit_on_change = false)
{
    global $quick_entry_types;

    echo "<tr><td class='label'>$label</td><td>";
    echo array_selector($name, $selected_id, $quick_entry_types, array(
        'select_submit' => $submit_on_change
    ));
    echo "</td></tr>\n";
}

function quick_actions_list_row($label, $name, $selected_id = null, $submit_on_change = false)
{
    global $quick_actions;

    echo "<tr><td class='label'>$label</td><td>";
    echo array_selector($name, $selected_id, $quick_actions, array(
        'select_submit' => $submit_on_change
    ));
    echo "</td></tr>\n";
}

function shippers_list_cells($label, $name, $selected_id = null)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo shippers_list($name, $selected_id);
    echo "</td>\n";
}

function shippers_list_row($label, $name, $selected_id = null)
{
    echo "<tr><td class='label'>$label</td>";
    shippers_list_cells(null, $name, $selected_id);
    echo "</tr>\n";
}

/*
 *
 */
function qty_cells($label, $name, $init = null, $params = null, $post_label = null, $dec = null)
{
    if (! isset($dec))
        $dec = user_qty_dec();

    amount_cells_ex($label, $name, 15, 15, $init, $params, $post_label, $dec);
}

function qty_row($label, $name, $init = null, $params = null, $post_label = null, $dec = null)
{
    if (! isset($dec))
        $dec = user_qty_dec();

    echo "<tr>";
    amount_cells($label, $name, $init, $params, $post_label, $dec);
    echo "</tr>\n";
}

function text_cells($label, $name, $value = null, $size = "", $max = "", $title = false, $labparams = "", $post_label = "", $inparams = "")
{
    global $Ajax;

    default_focus($name);
    if ($label != null)
        label_cell($label, $labparams);
    echo "<td>";

    if ($value === null)
        $value = get_post($name);

    $attribute = array(
        'type'=>'text',
        'name'=>$name,
        'value'=>$value,
        'class'=>get_instance()->bootstrap->input_class
    );
    if( is_string($title) AND strlen($title) > 0 ){
        $attribute['title'] = $title;
    }
    echo '<input '._parse_attributes($attribute).'>';

    if ($post_label != "")
        echo " " . $post_label;

    echo "</td>\n";
    $Ajax->addUpdate($name, $name, $value);
}

function text_row($label, $name, $value = null, $size = "", $max = "", $title = null, $params = "", $post_label = "")
{
    echo "<tr><td class='label'>$label</td>";
    text_cells(null, $name, $value, $size, $max, $title, $params, $post_label);

    echo "</tr>\n";
}


