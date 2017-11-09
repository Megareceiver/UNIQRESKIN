<?php
function gl_view($row){
    return get_gl_view_str($row["type"], $row["trans_no"]);
}

function get_gl_view_listview($object=NULL){
    if( is_object($object) ){
        return get_gl_view_str($object->type,$object->trans_no);
    }
}

function get_gl_view_str($type=0, $trans_no=0, $label="", $force=false, $class='', $id='',$icon=true)
{
    if (!$force && !user_show_gl_info())
        return "";

//     $icon = false;
    if ($label == ""){
        $label = _("GL");
//         $icon = ICON_GL;
    } else {
        $pars = access_string($label);
        $label = $pars[0];
    }
    $link = "gl/view/gl_trans_view.php?type_id=$type&trans_no=$trans_no";

    $trans_view_new = array(ST_CUSTPAYMENT,ST_SALESINVOICE,ST_CUSTCREDIT,ST_OPENING_CUSTOMER,ST_OPENING_SUPPLIER);

    if( in_array($type, $trans_view_new) ){
        $link = "gl/tran_view?type_id=$type&trans_no=$trans_no";
    }
    $attributes = array(
        'id'=>$id,
        'class'=>$class.' button',
        'target'=>'_blank',
        'onclick'=>"javascript:openWindow(this.href,this.target); return false;"
    );
    $pars = access_string($label);
//     if (user_graphic_links() && $icon)
//         $pars[0] = set_icon($icon, $pars[0]);

    $attributes = array_merge( $attributes , _attributes_str2array($pars[1]) );


    return $icon ? anchor($link,'<i class="fa fa-book text-info"></i> ' ,$attributes) : anchor($link,$label ,$attributes) ;
//     return viewer_link($label,$link, $class, $id, $icon);
}