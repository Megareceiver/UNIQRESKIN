<?php
// ----------------------------------------------------------------------------------------------
// Universal array combo generator
// $items is array of options 'value' => 'description'
// Options is reduced set of combo_selector options and is merged with defaults.
function array_selector($name, $selected_id, $items, $options = null)
{
    global $Ajax;

    $opts = array( // default options
        'spec_option' => false, // option text or false
        'spec_id' => 0, // option id
        'select_submit' => false, // submit on select: true/false
        'async' => true, // select update via ajax (true) vs _page_body reload
        'default' => '', // default value when $_POST is not set
        'multi' => false, // multiple select
                          // search box parameters
        'height' => false, // number of lines in select box
        'sel_hint' => null,
        'disabled' => false,
        'class' => get_instance()->bootstrap->input_class,
        'data-size'=>NULL,
        'data-live-search'=>false
    );
    // ------ merge options with defaults ----------
    if ($options != null)
        $opts = array_merge($opts, $options);

    $select_submit = $opts['select_submit'];
    $spec_id = $opts['spec_id'];
    $spec_option = $opts['spec_option'];
    $disabled = $opts['disabled'] ? "disabled" : '';
    $multi = $opts['multi'];

    if ($selected_id == null) {
        $selected_id = get_post($name, $opts['default']);
    }
    if (! is_array($selected_id))
        $selected_id = array(
            (string) $selected_id
        ); // code is generalized for multiple selection support

    if (isset($_POST['_' . $name . '_update'])) {
        if (! $opts['async'])
            $Ajax->activate('_page_body');
        else
            $Ajax->activate($name);
    }

    // ------ make selector ----------
    $selector = $first_opt = '';
    $first_id = false;
    $found = false;
    // if($name=='SelectStockFromList') display_error($sql);
    foreach ($items as $value => $descr) {
        $sel = '';
        if (in_array((string) $value, $selected_id, true)) {
            $sel = 'selected';
            $found = $value;
        }
        if( is_array($descr) ){
            $descr = $descr['title'];
        } elseif ( is_object($descr) ){
            $descr = $descr->title;
        }
        if ($first_id === false) {
            $first_id = $value;
            $first_opt = $descr;
        }
        $selector .= "<option $sel value='$value'>$descr</option>\n";
    }

    if ($first_id !== false) {
        $sel = ($found === $first_id) || ($found === false && ($spec_option === false)) ? "selected='selected'" : '';
//         $selector = sprintf($first_opt, $sel) . $selector;
        $selector = $selector;
    }
    // Prepend special option.
    if ($spec_option !== false) { // if special option used - add it
        $first_id = $spec_id;
        $first_opt = $spec_option;
        $sel = $found === false ? 'selected' : '';
        $selector = "<option $sel value='$spec_id' >$spec_option</option>" . $selector;
    }

    if ($found === false) {
        $selected_id = array(
            $first_id
        );
    }
    
    //$_POST[$name] = $multi ? $selected_id : $selected_id[0];
    if( $multi ){
        $_POST[$name] = $selected_id;
    } elseif ( is_array($selected_id) AND isset($selected_id[0]) ){
        $_POST[$name] = $selected_id[0];
    }




    $attributes = array(
        'autocomplete'=>'off',
        'title'=>$opts['sel_hint'],
        'name'=>$name,
        'class'=>'combo selectpicker '.$opts['class']
    );

    if( $opts['data-live-search']){
        $attributes['data-live-search'] = true;
    }

    if( intval($opts['data-size']) > 0 ){
        $attributes['data-size'] = $opts['data-size'];
    }

    if( $multi ){
        $attributes['multiple'] = true;
        $attributes['name'] .= "[]";
    }
    if( $opts['height'] !== false ){
        $attributes['size'] = $opts['height'];
    }
//     $disabled
    $selector = "<select " . _parse_attributes($attributes) . ">" . $selector . "</select>\n";

    $Ajax->addUpdate($name, "_{$name}_sel", $selector);

    $selector = "<span id='_{$name}_sel' class='select w-100'>" . $selector . "</span>\n";

    if ($select_submit != false) { // if submit on change is used - add select button
        global $_select_button;
        $selector .= sprintf($_select_button, $disabled, user_theme(), (fallback_mode() ? '' : 'display:none;'), '_' . $name . '_update') . "\n";
    }
    default_focus($name);

    return $selector;
}


