<?php
function item_tax_types($label, $name, $selected_id = null)
{
    $input = item_tax_types_list($name, $selected_id);
    form_group_bootstrap($label, $input);
}

function input_multitaxes($label, $name, $selected_ids=array()){
    $items = taxes_items();

    $html= '<div class="form-control multicheckbox" style="clear: both;" >';

    if( !empty($items) ){
        $val = $selected_ids;
        foreach ($items AS $tax){
            $idname = "tax".$tax->id;
            $html.= '<label for="'.$idname.'"><input type="checkbox" '.( ((is_array($val) && in_array($tax->id, $val)) OR ($val==$tax->id)) ? 'checked' : null ).' id="'.$idname.'" name="'.$name.'['.$tax->id.']" value="'.$tax->id.'" />'.$tax->title.'</label>';
        }
    }
    $html.= '</div>';
    $html.= '<p class="form-control-newline" ><button class="inputsubmit checkall" type="button" >Select All</button> <button class="inputsubmit uncheckall" type="button" >Uncheck All</button></p>';
    return $html;

    if( strlen($label) < 1 ){
        return $html;
    }
    form_group_bootstrap($label, $html);
}
function gst_list_bootstrap($label, $name, $selected_id=null, $submit_on_change = false,$group_tax=''){

    $groups = NULL;


    if( !isset($_SESSION['taxcode'][$group_tax]) ){
        $item_api = get_instance()->api->get_data('taxcode/'.$group_tax);
        $_SESSION['taxcode'][$group_tax] = (array)$item_api->options;
    }

    $items = $_SESSION['taxcode'][$group_tax];

    if( !$selected_id ){
        $selected_id = input_post($name);
    }
    $input = array_selector($name, $selected_id, $items, array(
        'select_submit' => $submit_on_change,
        'class'=>'show-tick form-control',
        'data-size'=>6,
        'data-live-search'=>true
    ));

    form_group_bootstrap($label, $input,null,null,array('gstname'=>$name));
}

function tax_types($label, $name, $selected_id=null, $none_option=false,$submit_on_change=false){
    $input =  tax_types_list($name, $selected_id, $none_option, $submit_on_change);
    form_group_bootstrap($label, $input);
}