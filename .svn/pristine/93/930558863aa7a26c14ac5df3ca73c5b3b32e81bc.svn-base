<?php

function hidden($name, $value = null, $echo = true)
{
    global $Ajax;

    if ($value === null)
        $value = get_post($name);

    $ret = "<input type=\"hidden\" name=\"$name\" value=\"$value\">";
    $Ajax->addUpdate($name, $name, $value);
    if ($echo)
        echo $ret . "\n";
    else
        return $ret;
}

// ---------------------------------------------------------------------------------
function checkbox($label, $name, $value = null, $submit_on_change = false, $title = false, $class = NULL)
{
    global $Ajax;
    $str = '';

    if ($label)
        $str .= $label . "  ";
        // if ($submit_on_change !== false) {
        // if ($submit_on_change === true){
        // //$submit_on_change =
        // $submit_on_change = 'JsHttpRequest.request(\'_update\', this.form);';
        // }

    // }
    if ($value === null)
        $value = get_post($name, 0);

    $attributes = array(
        'type' => 'checkbox',
        'name' => $name,
        'value' => 1,
        'class' => $class,
        'id' => "checkboxs_$name"
    );

    if ($submit_on_change) {
        $js_func_name = trim("_" . $name . "_update");
        $attributes['onclick'] = 'JsHttpRequest.request(\'' . $js_func_name . '\', this.form);';
    }

    if ($title) {
        $attributes['title'] = $title;
    }

    if ($value == 1) {
        $attributes['checked'] = 'checked';
    }
    $str .= '<input ' . _parse_attributes($attributes) . '>';

    $Ajax->addUpdate($name, $name, $value);
    return $str;
}

// -----------------------------------------------------------------------------------
function radio($label, $name, $value, $selected = null, $submit_on_change = false, $class = NULL)
{
    if (! isset($selected))
        $selected = get_post($name) == $value;

    $attributes = array(
        'type' => 'radio',
        'name' => $name,
        'value' => $value,
        'class' => $class,
        'id' => "radio_$name" . "_$value"
    );
    if ($submit_on_change === true) {
        // $submit_on_change = " JsHttpRequest.request(\"_{$name}_update\", this.form);";
        $submit_on_change = " JsHttpRequest.request('_" . $name . "_update', this.form);";
        $attributes['onclick'] = $submit_on_change;
    }
    if ($selected) {
        $attributes['checked'] = '1';
    }

    return '<input ' . _parse_attributes($attributes) . '>';
}

function check($label, $name, $value = null, $submit_on_change = false, $title = false)
{
    echo checkbox($label, $name, $value, $submit_on_change, $title);
}

function check_cells($label, $name, $value = null, $submit_on_change = false, $title = false, $params = '')
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td $params>";
    echo check(null, $name, $value, $submit_on_change, $title);
    echo "</td>";
}

function check_row($label, $name, $value = null, $submit_on_change = false, $title = false)
{
    echo "<tr><td class='label'>$label</td>";
    echo check_cells(NULL, $name, $value, $submit_on_change, $title);
    echo "</tr>\n";
}

function text_cells_ex($label, $name, $size, $max = null, $init = null, $title = null, $labparams = null, $post_label = null, $submit_on_change = false)
{
    global $Ajax;

    default_focus($name);
    if (! isset($_POST[$name]) || $_POST[$name] == "") {
        if ($init)
            $_POST[$name] = $init;
        else
            $_POST[$name] = "";
    }
    if ($label != null)
        label_cell($label, $labparams);

    if (! isset($max))
        $max = $size;

    echo "<td>";

    $attributes = array(
        'type' => 'text',
        'name' => $name,
        'size'=>$size,
        'maxlength'=>$max,
        'value' => $_POST[$name],
        'class'=>'form-control',
    );
    if( strlen($title) > 0 ){
        $attributes['title'] = $title;
    }
    if( $submit_on_change ){
        $attributes['class'] .= ' searchbox';
    }

//     $class = $submit_on_change ? 'class="searchbox"' : '';
    echo '<input ' . _parse_attributes($attributes) . ' >';

    if ($post_label)
        echo " " . $post_label;

    echo "</td>\n";
    $Ajax->addUpdate($name, $name, $_POST[$name]);
}

function locations_list_cells($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false)
{
    if ($label != null)
        echo "<label class='col-md-3 control-label'>$label</label>\n";
    echo '<div class="col-md-9">';
    echo locations_list($name, $selected_id, $all_option, $submit_on_change, 'form-control');
    echo '</div>';
}

function locations_list_row($label, $name, $selected_id = null, $all_option = false, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    locations_list_cells(null, $name, $selected_id, $all_option, $submit_on_change);
    echo "</tr>\n";
}

function locations_list($name, $selected_id = null, $all_option = false, $submit_on_change = false, $class_of_input = NULL)
{
    global $all_items;

    $sql = "SELECT loc_code, location_name, inactive FROM " . TB_PREF . "locations";

    return combo_input($name, $selected_id, $sql, 'loc_code', 'location_name', array(
        'spec_option' => $all_option === true ? _("All Locations") : $all_option,
        'spec_id' => $all_items,
        'select_submit' => $submit_on_change,
        'class' => $class_of_input
    ));
}

function textarea_cells($label, $name, $value, $cols, $rows, $title = null, $params = "", $params2 = "")
{
    global $Ajax;

    default_focus($name);
    if ($label != null)
        echo "<td $params>$label</td>\n";
    if ($value == null)
        $value = (! isset($_POST[$name]) ? "" : $_POST[$name]);
    echo "<td $params2 ><textarea name='$name' cols='$cols' rows='$rows'" . ($title ? " title='$title'" : '') . ">$value</textarea></td>\n";
    $Ajax->addUpdate($name, $name, $value);
}

function textarea_row($label, $name, $value, $cols, $rows, $title = null, $params = "")
{
    echo "<tr><td class='label'>$label</td>";
    textarea_cells(null, $name, $value, $cols, $rows, $title, $params);
    echo "</tr>\n";
}

/*
 * currencies
 */
function currencies_list_cells($label, $name, $selected_id = null, $submit_on_change = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo currencies_list($name, $selected_id, $submit_on_change);
    echo "</td>\n";
}

function currencies_list_row($label, $name, $selected_id = null, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    currencies_list_cells(null, $name, $selected_id, $submit_on_change);
    echo "</tr>\n";
}

/*
 *
 */
function fiscalyears_list_cells($label, $name, $selected_id = null)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo fiscalyears_list($name, $selected_id);
    echo "</td>\n";
}

function fiscalyears_list_row($label, $name, $selected_id = null)
{
    echo "<tr><td class='label'>$label</td>";
    fiscalyears_list_cells(null, $name, $selected_id);
    echo "</tr>\n";
}

/*
 *
 */
function file_cells($label, $name, $id = "")
{
    if ($id != "")
        $id = "id='$id'";
    label_cells($label, "<input type='file' name='$name' $id />");
}

function file_row($label, $name, $id = "")
{
    echo "<tr><td class='label'>$label</td>";
    file_cells(null, $name, $id);
    echo "</tr>\n";
}

/*
 *
 */
function security_roles_list_cells($label, $name, $selected_id = null, $new_item = false, $submit_on_change = false, $show_inactive = false)
{
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>";
    echo security_roles_list($name, $selected_id, $new_item, $submit_on_change, $show_inactive);
    echo "</td>\n";
}

function security_roles_list_row($label, $name, $selected_id = null, $new_item = false, $submit_on_change = false, $show_inactive = false)
{
    echo "<tr><td class='label'>$label</td>";
    security_roles_list_cells(null, $name, $selected_id, $new_item, $submit_on_change, $show_inactive);
    echo "</tr>\n";
}

/*
 *
 */
function number_list($name, $selected, $from, $to, $no_option = false)
{
    $items = array();
    for ($i = $from; $i <= $to; $i ++)
        $items[$i] = "$i";

    return array_selector($name, $selected, $items, array(
        'spec_option' => $no_option,
        'spec_id' => ALL_NUMERIC
    ));
}

