<?php
class customer extends CI_finput{
    function input($name,$value,$input_return_type='html',$readonly=false,$all=true,$attributes=""){

        $empty = _('-- Select Customer --');
        $empty = NULL;

        $ci = get_instance();
        $options = $ci->db->select('debtor_no AS id,name AS title,curr_code')->order_by('name', 'ASC')->get('debtors_master')->result();
        $autoSubmit = ( isset($attributes['submitchange']) ) ? $attributes['submitchange'] : false;
        switch ($input_return_type=='value'){
            case 'value':
                return $options[$value];
                break;
            default:
                return self::options($name,$options,$value,$empty,false,false,$autoSubmit);
                break;
        }
    }

}