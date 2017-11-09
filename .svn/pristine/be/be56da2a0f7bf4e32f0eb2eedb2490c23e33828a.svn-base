<?php
class AccountingPaymentPersonSmarty {
    function __construct(){

    }

    static function payment_person_name($template=null, $params=null){
        $type = ( isset($template['type']) )?$template['type']:null;
        $person_id = ( isset($template['person_id']) )?$template['person_id']:0;

        global $payment_person_types;
        switch ($type)
        {
            case PT_MISC :
                return $person_id;
            case PT_QUICKENTRY :
                $qe = get_quick_entry($person_id);
                return ($full ? $payment_person_types[$type] . " ":"") . $qe["description"];
            case PT_WORKORDER :
                global $wo_cost_types;
                return $wo_cost_types[$person_id];
            case PT_CUSTOMER :
                return ($full ? $payment_person_types[$type] . " ":"") . get_customer_name($person_id);
            case PT_SUPPLIER :
                return ($full ? $payment_person_types[$type] . " ":"") . get_supplier_name($person_id);
            default :
                //DisplayDBerror("Invalid type sent to person_name");
                //return;
                return '';
        }

    }
}
?>