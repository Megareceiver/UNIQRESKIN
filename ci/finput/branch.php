<?php
class branch extends CI_finput{
    function input($name,$value,$input_return_type='html',$debtor_no=0,$attributes=NULL,$readonly=false,$all=true){


        $options = array();

        $empty = _('-- Select Branch --');
        $empty = NULL;
        $ci = get_instance();

        $ci->db->select('branch_code AS id,branch_ref AS title')->order_by('br_name', 'ASC');
        if( isset($debtor_no) ){
            $ci->db->where('debtor_no',$debtor_no);
        }
        $options = $ci->db->get('cust_branch')->result();


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