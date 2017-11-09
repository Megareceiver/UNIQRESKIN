<?php
function ref_get($tran_type,$tran_no){
    $db = get_instance()->db;
    $data = $db->where(array('id'=>$tran_no,'type'=>$tran_type))->get('refs');

    if( $data->num_rows() > 0 ){
        return $data->row()->reference;
    }
    return NULL;
}

function get_next_reference($type,$prev_ref=NULL)
{
    $result = get_instance()->db->from('sys_types')->where('type_id',$type)->select('next_reference')->get();
    if( !is_object($result) ){
        check_db_error("The last transaction ref for $type could not be retreived", get_instance()->db->last_query(),false);
        return FALSE;
    } else {
        $ref_using = $result->row()->next_reference;

    }
    $ref_using = check_ref_used($type,$ref_using);
//     if( check_ref_used($type,$ref_using) ){
//         $ref_using = ref_increment($ref_using);
//     }


    return $ref_using;

}

function ref_increment($reference, $back=false){
    // New method done by Pete. So f.i. WA036 will increment to WA037 and so on.
    // If $reference contains at least one group of digits,
    // extract first didgits group and add 1, then put all together.
    // NB. preg_match returns 1 if the regex matches completely
    // also $result[0] holds entire string, 1 the first captured, 2 the 2nd etc.
    //
    if (preg_match('/^(\D*?)(\d+)(.*)/', $reference, $result) == 1)
    {
        list($all, $prefix, $number, $postfix) = $result;
        $dig_count = strlen($number); // How many digits? eg. 0003 = 4
        $fmt = '%0' . $dig_count . 'd'; // Make a format string - leading zeroes
        $val = intval($number + ($back ? ($number<1 ? 0 : -1) : 1));
        $nextval =  sprintf($fmt, $val); // Add one on, and put prefix back on

        return $prefix.$nextval.$postfix;
    }
    else
        return $reference;
}


function check_ref_used($tran_type,$reference=NULL){
    $db = get_instance()->db;
    $ref_return = $reference;

    switch ($tran_type){
        case ST_BANKPAYMENT:
        case ST_BANKDEPOSIT:
            $result = $db->where(array('type'=>$tran_type,'ref'=>$reference))->get('bank_trans');
            $ref_next = ref_increment($reference);
            if( $ref_next==$reference ){
                $ref_next = $ref_next.'1';
            }

            if( $result->num_rows() > 0 ){

                $ref_return = check_ref_used($tran_type,$ref_next);
            } else {
                $ref_return = $reference;
            }
            break;
//         case ST_SALESINVOICE :
//         case ST_CUSTCREDIT :
//         case ST_CUSTDELIVERY :
//             $result = $db->where(array('type'=>$tran_type,'ref'=>$reference))->get('bank_trans');
//             $ref_next = ref_increment($reference);
//             if( $ref_next==$reference ){
//                 $ref_next = $ref_next.'1';
//             }
            
//             if( $result->num_rows() > 0 ){
            
//                 $ref_return = check_ref_used($tran_type,$ref_next);
//             } else {
//                 $ref_return = $reference;
//             }
        default:
            
//             $ref_return = $reference;
            $result = $db->where(array('type'=>$tran_type,'reference'=>$reference))->get('refs');
            $ref_next = ref_increment($reference);
            if( $ref_next==$reference ){
                $ref_next = $ref_next.'1';
            }
            
            if( $result->num_rows() > 0 ){
                $ref_return = check_ref_used($tran_type,$ref_next);
            } else {
                $ref_return = $reference;
            }
            break;
    }
    return $ref_return;

}

?>