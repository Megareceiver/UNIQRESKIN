<?php
class location extends CI_finput{
    function input($name,$value,$input_return_type='html',$customer_id=0,$attributes=NULL,$readonly=false,$all=true){


        $options = array();

        $empty = _('-- Select Location --');
        $empty = NULL;
        $ci = get_instance();

        $ci->db->select('loc_code AS id, location_name AS title, inactive')->order_by('location_name', 'ASC');

        $options = $ci->db->get('locations')->result();


        switch ($input_return_type=='value'){
            case 'value':
                return $options[$value];
                break;
            default:
                return self::options($name,$options,$value,$empty);
                break;
        }
    }

}