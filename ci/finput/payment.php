<?php
class payment extends CI_finput{
    function input($name,$value,$category=0,$input_return_type='html',$readonly=false,$all=true){

        $options = array();
        $ci = get_instance();
        $ci->db->select('terms_indicator AS id, terms AS title, inactive')->order_by('terms', 'ASC');

        if ($category == PM_CASH) {// only cash
            $ci->db->where(array('days_before_due'=>0,'day_in_following_month'=>0));
        }
        if ($category == PM_CREDIT){ // only delayed payments

            $ci->db->where(array('days_before_due <>'=>0,'day_in_following_month <>'=>0));
        }
        $options = $ci->db->get('payment_terms')->result();

        $empty = _('-- Select Payment Method --');
        $empty = NULL;
        switch ($input_return_type=='value'){
            case 'value':
                return $options[$value];
                break;
            default:
                return self::options($name,$options,$value,$empty); break;
        }
    }

}