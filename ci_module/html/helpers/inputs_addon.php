<?php

function input_text_addon_bootstrap ($label, $name, $value = null, $addon = NULL, 
        $help = NULL)
{
    default_focus($name);
    if (! isset($_POST[$name]) || $_POST[$name] == "") {
        if ($value)
            $_POST[$name] = $value;
        else
            $_POST[$name] = "";
    }
    
    $attributes = array(
            'type' => 'text',
            'name' => $name,
            'class' => 'form-control',
            'value' => $_POST[$name]
    );
    
    $html = '<input ' . _parse_attributes($attributes) . ' >';
    
    $add = NULL;
    
    if (strlen($addon) > 0) {
        $spin = 'fa-spin';
        $spin = NUlL;
        $color = 'font-blue';
        // $icon.=" $spin $color";
        // $icon_set = '<i class="fa '.$icon.'"></i>';
        $add = '<span class="input-group-addon">' . $addon . '</span>';
    }
    
    $html = '<div class="input-group right">' . $html . $add . '</div>';
    
    form_group_bootstrap($label, $html, NULL, $help);
}

function input_text_addon_both ($label, $name, $value = null, $addonRight = NULL, 
        $addonLeft = NULL, $help = NULL)
{
    default_focus($name);
    if (! isset($_POST[$name]) || $_POST[$name] == "") {
        if ($value)
            $_POST[$name] = $value;
        else
            $_POST[$name] = "";
    }
    
    $attributes = array(
            'type' => 'text',
            'name' => $name,
            'class' => 'form-control',
            'value' => $_POST[$name]
    );
    
    $html = '<input ' . _parse_attributes($attributes) . ' >';
    
    $add = NULL;
    
    if (strlen($addonRight) > 0) {
        if (strpos($addonRight, 'button') !== false) {
            $html .= $addonRight;
        } else {
            $html .= '<span class="input-group-addon">' . $addonRight . '</span>';
        }
    }
    
    if (strlen($addonLeft) > 0) {
        $html = '<span class="input-group-addon">' . $addonLeft . '</span>' .
                 $html;
    }
    
    $html = '<div class="input-group right">' . $html . '</div>';
    
    form_group_bootstrap($label, $html, NULL, $help);
}

function input_text_iconright_bootstrap ($label, $name, $value = null, $icon = NULL, 
        $help = NULL, $class = "", $title = NULL)
{
    default_focus($name);
    
    if (! isset($_POST[$name]) || $_POST[$name] == "") {
        if ($value)
            $_POST[$name] = $value;
        else
            $_POST[$name] = NULL;
    }
    
    $attributes = array(
            'type' => 'text',
            'name' => $name,
            'class' => "form-control $class",
            'value' => $_POST[$name]
    );
    
    $html = '<input ' . _parse_attributes($attributes) . ' >';
    
    $icon_set = NULL;
    if (strlen($icon) > 0) {
        $spin = 'fa-spin';
        $spin = NUlL;
        $color = 'font-blue';
        // $color = NULL;
        
        $icon .= " $spin $color";
        $icon_set = '<i class="fa ' . $icon . '"></i>';
    }
    
    $html = '<div class="input-icon right">' . $icon_set . $html . '</div>';
    
    form_group_bootstrap($label, $html, NULL, $help);
}

function input_ref ($label, $name, $value = null, $title = null, 
        $submit_on_change = false, $size = NULL, $max = NULL, $help = NULL)
{
    $class_add = $submit_on_change ? "ajaxsubmit" : NULL;
    return input_text_iconright_bootstrap($label, $name, $value, 'fa-key', 
            $help, $class_add);
}

function input_exc_rate ($label, $name, $value = null, $currency = NULL, $help = NULL)
{
    global $Ajax;
    default_focus($name);
    
    if (! $value) {
        $value = input_post($name);
        // bug($_POST);
    }
    
    if (! isset($_POST[$name]) || $_POST[$name] == "") {
        if ($value)
            $_POST[$name] = $value;
        else
            $_POST[$name] = "";
    }
    
    $attributes = array(
            'type' => 'text',
            'name' => $name,
            'class' => 'form-control',
            'value' => $value
    );
    // bug($attributes);
    $html = '<input ' . _parse_attributes($attributes) . ' >';
    
    $add = NULL;
    
    if (strlen($currency) < 1) {
        $currency = curr_default();
    }
    // $button = submit('get_rate',_("Get"), false, _('Get current rate from') .
    // ' ' . $xchg_rate_provider , true);
    $button = '<button class="btn blue ajaxsubmit" type="button" name="get_rate" id="get_rate" title="Get current rate from ' .
             $currency . '" value="Get" >Get</button>';
    
    $html .= '<span class="input-group-btn">' . $button . '</span>';
    
    // if (strlen($addonLeft) > 0) {
    $html = '<span class="input-group-addon"> to ' . $currency . ' </span>' .
             $html;
    // }
    
    $html = '<div class="input-group right">' . $html . '</div>';
    
    $Ajax->addUpdate($name, $name, $_POST[$name]);
    $Ajax->addAssign($name, $name, 'dec', user_price_dec());
    
    form_group_bootstrap($label, $html, NULL, $help);
}

function input_money ($label, $name, $value = null, $currency = NULL, $post_label = null)
{
    global $Ajax;
    if (! $value) {
        $value = input_post($name);
    }
    if (is_null($value)) {
        $value = 0;
    }
    $_POST[$name] = $value;
    
    if (strlen($currency) < 1) {
        $currency = get_company_currency();
    }
    
    $html = '<div class="input-group left">
                <span class="input-group-btn">
                    <button class="btn btn-info" >' .
             $currency .
             '</button>
                </span>
                <input class="form-control input-money amount" dec="2" type="text" name="' .
             $name . '" value="' . number_total($value) . '"  ></div>';
    form_group_bootstrap($label, $html);
    
    if ($post_label) {
        // echo "<span id='_{$name}_label'> $post_label</span>";
        $Ajax->addUpdate($name, '_' . $name . '_label', $post_label);
    }
    $Ajax->addUpdate($name, $name, $value);
    // $Ajax->addAssign($name, $name, 'dec', $dec);
}

function input_percent ($label, $name, $value = null, $help = NULL)
{
    if (! $value) {
        $value = input_val($name);
    }
    
    $input = '<input class="form-control" type="number" name="' . $name .
             '"  value="' . ($value) . '">';
    $addon = '<span class="input-group-btn"><button class="btn" type="button">%</button></span>';
    $html = '<div class="input-group input-group-sm">' . $input . $addon .
             '</div>';
    
    form_group_bootstrap($label, $html, NULL, $help);
}

function show_voided_inquiry ($name, $value = null, $submit_on_change = false)
{
    if ($value === null)
        $value = get_post($name, 0);
    
    $inputs = checkbox_material($name, $value, $submit_on_change, false);
    $html = "<div class=\"md-checkbox-list clearfix\">$inputs</div>";
    $columns = isMobile() ? 3 : 8;
    form_group_bootstrap('Voided', $html, $columns);
}

function qty_input ($label, $name, $init = null, $post_label = null, 
        $dec = null)
{
    global $Ajax;
    $size = 10;

    
    if (! isset($dec))
        $dec = user_price_dec();
    if (! isset($_POST[$name]) || $_POST[$name] == "") {
        if ($init !== null)
            $_POST[$name] = $init;
        else
            $_POST[$name] = '';
    }

    if (! isset($max))
        $max = $size;
    

    
    $html = "<input class='amount form-control' type=\"number\" name=\"$name\" size=\"$size\" maxlength=\"$max\" dec=\"$dec\" value=\"" .
             $_POST[$name] . "\">";
    
     form_group_bootstrap($label, $html, NULL);
    
//     if ($post_label) {
//         echo "<span id='_{$name}_label'> $post_label</span>";
//         $Ajax->addUpdate($name, '_' . $name . '_label', $post_label);
//     }

    $Ajax->addUpdate($name, $name, $_POST[$name]);
    $Ajax->addAssign($name, $name, 'dec', $dec);
}
