<?php
class sale_type extends CI_finput{
    function input($name,$value,$category=0,$input_return_type='html',$readonly=false,$all=true){

        $options = array();
        $ci = get_instance();
        $ci->db->select('id, sales_type AS title, inactive')->order_by('sales_type', 'ASC');


        $options = $ci->db->get('sales_types')->result();

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