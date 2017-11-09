<?php
if ( !function_exists('form_edit') ){

    function form_edit($fields=array(),$return=false,$formWidth=8){
        $ci = get_instance();
        foreach ($fields AS $key=>$field){
            if( isset($field[0]) && !array_key_exists('title', $field) ){
                $fields[$key]['title'] = $field[0];
                unset($fields[$key][0]);
            }
            if( !array_key_exists('type', $field) ){
                $fields[$key]['type'] = 'text';
            }
            if( isset($field[1]) ){
                $fields[$key]['type'] = $field[1];
                unset($fields[$key][1]);
            }

            if( !array_key_exists('value', $field) ){
                $fields[$key]['value'] = NULL;
            }
            if( isset($field[2]) ){
                $fields[$key]['value'] = $field[2];
                unset($fields[$key][2]);
            }
        }
        $data = array('fields'=>$fields,'formclass'=>'formtable','form_width'=>$formWidth);
        row_start('justify-content-center');
        module_view('form',$data,true, true, $module='html' );
        row_end();

//         $ci->view('common/form',array('items'=>$fields,'formclass'=>'formtable'),$return);
    }
}