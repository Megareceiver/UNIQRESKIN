<?php

class at_smarty {
    static $smarty, $ci;
    function __construct(){
        $ci = get_instance();
        if( isset($ci->smarty) ){
            self::$smarty = $ci->smarty;
        }
        self::$ci = $ci;
    }

    static function background_red($template){
        $data = array();

        for( $i=1 ; $i < 10; $i++){
            if(  ( isset($template["num$i"]) ) ){
                $data[] = $template["num$i"];
            }
        }
        $hidden = ( $template['hidden'] )?" display:none; ": NULL;


        $dec = user_amount_dec();

        $red = false;
        for( $i=1 ; $i < count($data); $i++){
            if ( $red == true ) break;
            $red = (number_format2($data[$i],$dec) != number_format2($data[$i-1],$dec)) ? true :false;
        }
        return $red ? 'background-color: red' : $hidden;
    }

    static function tran_detail_view($template,$params){
        global $systypes_array;
        $tran_type = ( isset($template['type']) )?$template['type']: NULL;
        $tran_no = ( isset($template['tran_no']) )?$template['tran_no']: NULL;

        $title = null;
        if( $tran_type ){
            $title = isset($systypes_array[$tran_type]) ? $systypes_array[$tran_type] : $tran_type ;
        }
        if( isset($template['title']) ){
            $title = $template['title'];
        }
        switch ($tran_type){
            case ST_SALESINVOICE:
                $uri = "sales/view/view_invoice.php?trans_type=$tran_type&trans_no=$tran_no";
                break;
            case ST_CUSTPAYMENT:
                $uri = "sales/view/view_receipt.php?trans_type=$tran_type&trans_no=$tran_no";
                break;
            case ST_CUSTCREDIT:
                $uri = "sales/view/view_credit.php?trans_type=$tran_type&trans_no=$tran_no";
                break;
            case ST_SUPPAYMENT :
                $uri = "purchasing/view/view_supp_payment.php?trans_no=$tran_no";
                break;

            case ST_BANKPAYMENT:
                $uri = "gl/gl_bank.php?trans_type=$tran_type&trans_no=$tran_no&ModifyPayment=Yes";
                break;
            case ST_BANKDEPOSIT:
                $uri = "gl/gl_bank.php?trans_type=$tran_type&trans_no=$tran_no&ModifyDeposit=Yes";
                break;
            default:
                $uri = "gl/view/gl_trans_view.php?type_id=$tran_type&trans_no=$tran_no";break;
        }

        return anchor($uri,$title,'target="_blank"');
    }

    static function gl_tran_view($template,$params){
        global $systypes_array;
        $tran_type = ( isset($template['type']) )?$template['type']: NULL;
        $tran_no = ( isset($template['tran_no']) )?$template['tran_no']: NULL;

        $title = null;
        if( $tran_type ){
            $title = isset($systypes_array[$tran_type]) ? $systypes_array[$tran_type] : $tran_type ;
        }
        if( isset($template['title']) ){
            $title = $template['title'];
        }

        $uri = "gl/tran_view?type_id=$tran_type&trans_no=$tran_no";
        return anchor($uri,$title,'target="_blank"');
    }


    static function trans_type($template=null){
        global $systypes_array;
        $type = ( isset($template['type']) )?$template['type']:null;

        if( !$type && is_string($template) ) {
            $type = $template;
        }
        return ( isset($systypes_array[$type]) ) ? $systypes_array[$type] : $type;

    }

    static function submit_form($template){
        $ajax = ( isset($template['ajax']) )?$template['ajax']:false;
        $html = '<button title="Refresh Inquiry" value="Search" name="RefreshInquiry"  type="submit" '.($ajax ? ' class="ajaxsubmit" aspect="default" ' : NULL).'>Search</button>';
        return $html;
    }

    static function payment_person_name($template){
        $type = ( isset($template['type']) )?$template['type']:NULL;
        $person_id = ( isset($template['person_id']) )?$template['person_id']:NULL;
        $full = ( isset($template['full']) )?$template['full']:true;
        return payment_person_name($type,$person_id,$full);
    }
}
