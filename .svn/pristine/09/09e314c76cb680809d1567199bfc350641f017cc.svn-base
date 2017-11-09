<?php
if( !function_exists('log_add') ) {


    function log_add($table,$action=1,$id=0,$data_old=NULL,$data_new=NULL){
        $model = load_module_model('log',true,'log');

        $data = array(
            'table'=>$table,
            'action'=>$action,
            'table_id'=>$id,
        );
        if( $data_old ){
            $data['data_old'] = json_encode($data_old);
        }
        if( $data_new ){
            $data['data_new'] = json_encode($data_new);
        }
        $model->add($data);
    }
}