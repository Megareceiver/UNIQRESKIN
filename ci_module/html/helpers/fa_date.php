<?php
// -----------------------------------------------------------------------------------
//
// Since FA 2.2 $init parameter is superseded by $check.
// When $check!=null current date is displayed in red when set to other
// than current date.
//
function date_cells($label, $name, $title = null, $check = null, $inc_days = 0, $inc_months = 0, $inc_years = 0, $params = null, $submit_on_change = false)
{
    global $use_date_picker, $path_to_root, $Ajax, $ci;

    $attributes = _attributes_str2array($params);

    if ($label != null)
        label_cell($label, $params);

        // echo "<td>";

    // $class = $submit_on_change ? 'date active' : 'date';

    $aspect = $check ? 'aspect="cdate"' : '';
    if ($check && (get_post($name) != Today()))
        $aspect .= ' style="color:#FF0000"';

    default_focus($name);
    $size = (user_date_format() > 3) ? 11 : 10;
    // echo "<span class=\"inputdate\" > <input type=\"text\" name=\"$name\" class=\"$class\" $aspect size=\"$size\" maxlength=\"12\" value=\""
    // . $_POST[$name]. "\""
    // .($title ? " title='$title'": '')." > $post_label</span>";
    // echo "</td>\n";


    echo "<td "._parse_attributes($attributes).">";
    echo input_date_bootstrap(NULL,$name, NULL, $disabled = false, $submit_on_change = null, $inc_days , $inc_months , $inc_years );
    echo "</td>";
//     echo $ci->finput->qdate($label, $name, null, 'column', false, null, null, $inc_days, $inc_months, $inc_years);

    if (isset($_POST[$name])) {
        $Ajax->addUpdate($name, $name, $_POST[$name]);
    }
}

function date_row($label, $name, $title = null, $check = null, $inc_days = 0, $inc_months = 0, $inc_years = 0, $params = null, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    date_cells(null, $name, $title, $check, $inc_days, $inc_months, $inc_years, $params, $submit_on_change);
    echo "</tr>\n";
}
