<?php
if( !function_exists('get_customer') ){
    function get_customer($customer_id,$return_ci=false){
        $query = get_instance()->db->where('debtor_no',$customer_id)->get('debtors_master');

        if( $return_ci ){
            return $query->row();
        }

        $sql = get_instance()->db->last_query();
        $result = db_query($sql, "could not get customer");
        return db_fetch($result);
    }
}
