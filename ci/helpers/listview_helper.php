<?php
if ( !function_exists('table_view') ){
    function table_view($table=array(),$result=NULL,$return=false,$page_padding=true){
        $ci = get_instance();
//         $result = "";
        if( isset($result['items']) && is_array($result['items']) ) {
            $items = $result['items'];
        } else {

            $items = $result;
        }
        $page = 1;
        if( isset($result['page'])){
            $page = $result['page'];
        }

        $total = 0;
        if( isset($result['total'])){
            $total = $result['total'];
        }
        $ci->view('common/table_view',array('table'=>$table,'items'=>$items,'page_padding'=>$page_padding,'page'=>$page,'total'=>$total),$return);
    }
}