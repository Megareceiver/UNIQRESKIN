<?php

function taxes_items(){
    $taxes = get_instance()->api->get_data('taxdetail');
    $_SESSION["ci_session_tax"] = (array)$taxes;
    return $_SESSION["ci_session_tax"];
}

function get_gst($tax_id=0){
    $tax_id = intval($tax_id);
    if( $tax_id < 1 ){
        return  array();
    }
    if( !isset($_SESSION["ci_session_tax"]) ){
        $_SESSION["ci_session_tax"] = array();
    }

    if( !array_key_exists($tax_id, $_SESSION["ci_session_tax"]) OR empty($_SESSION["ci_session_tax"][$tax_id]) ){
        $_SESSION["ci_session_tax"][$tax_id] = get_instance()->api->get_data('taxdetail/'.$tax_id);
    }

    return $_SESSION["ci_session_tax"][$tax_id];

}


function tax($tax_id){
    return get_gst($tax_id);
}

if( !function_exists("get_gst_string") ){
    function get_gst_string($id,$html=false){
        $dim = "";
        if ($id <= 0 && $html){
            $dim = "&nbsp;";
        } else {
            $tax = get_gst($id);
            if( is_object($tax) ){
                $dim = $tax->no." (".$tax->rate."%)";
            }
        }
        return $dim;
    }
}

function tax_calculator($tax_id,$price=0,$tax_included=false,$tax=null){
    $data = array('rate'=>0,'name'=>null,'price'=>0,'value'=>0,'gst_03_type'=>0,'sales_gl_code'=>null,'purchasing_gl_code'=>null, 'code'=>NULL);

    if( !$tax_id OR !is_numeric($tax_id) ){
        $data['price'] = $price;
        return (object)$data;
    }

    global $ci;
    if( !$tax ) {

        $tax =$ci->api->get_data('taxdetail/'.$tax_id);
    }
    $ci->db->reset();
    $tax_gl_acc = $ci->db->where('id',$tax_id)->get('tax_types')->row();


    if($tax && is_object($tax) AND !empty($tax) AND isset($tax->id)){

        $data['id'] = $tax->id;
        $data['rate'] = $tax->rate;
        $data['code'] = $tax->no;
        $data['name'] = $tax->name;
        $data['gst_type'] = NULL;
        if( isset($tax->gst_03_type) ){
            $data['gst_type'] = $tax->gst_03_type;
        }
        if( $tax_gl_acc->sales_gl_code ){
            $data['sales_gl_code'] = $tax_gl_acc->sales_gl_code;
        }

        if( $tax_gl_acc->purchasing_gl_code ){
            $data['purchasing_gl_code'] = $tax_gl_acc->purchasing_gl_code;
        }


        if( $tax_included ){
            $tax = $tax->rate/(100+$tax->rate)*$price;
            $data['value'] = $tax;
            $data['price'] = $price-$tax;
        } else {

            $tax = $tax->rate*$price/100;
            $data['value'] = $tax;
            $data['price'] = $price;
        }

    } else {
        $data['value'] = 0;
        $data['price'] = $price;
    }
    return (object)$data;
}
