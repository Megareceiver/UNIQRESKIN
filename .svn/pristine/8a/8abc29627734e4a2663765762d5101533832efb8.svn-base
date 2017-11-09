<?php
function input_switch($label, $name, $value = null, $data=array(),$submit_on_change = false,$columns=6, $help=NULL,$data_size='mini'){

    if( empty($data) OR count($data) < 1 ){
        $data = array('Yes','No');
    }

    $attributes = array(
        'type' => 'checkbox',
        'name' => $name,
        'data-size'=>$data_size,
        'class' => 'switch ',
        'data-on-text'=>$data[0],
        'data-off-text'=>$data[1],
        'checked'=>false,
//         'id' => "checkboxs_$name",
        'value'=>1

    );
//     $attributes['class'] = null;

    if ($value === null)
        $value = get_post($name, 0);

    if( $value OR ($value == 'on' AND $value != '0') ){
        $attributes['checked'] = true;
    }

//     $attributes['value'] = $value;

    if ($submit_on_change) {
        $attributes['class'] .= ' ajaxsubmit';
        $js_func_name = trim("_" . $name . "_update");
        global $Ajax;
        $Ajax->addUpdate($name, $name, $value);

//         $attributes['onclick'] = 'JsHttpRequest.request(\'' . $js_func_name . '\', this.form);';
    }

//     $input = '<input type="checkbox" class="" data-size="mini" checked  checked data-off-text="'.$data[1].'">';
    $input = '<input ' . _parse_attributes($attributes) . '>';

//     $attributes['type'] = 'hidden';
//     $input.= '<input ' . _parse_attributes($attributes) . '>';
    form_group_bootstrap($label, $input, $columns, $help);



}