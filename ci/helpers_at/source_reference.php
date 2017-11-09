<?php
function update_source_ref($trans_type,$trans_no,$ref){
    global $ci;
    if( strlen($ref) < 1 ){
        return false;
    }
    $data = array('trans_type'=>$trans_type,'trans_no'=>$trans_no);
    $ref_exit = $ci->db->where($data)->get('source_reference')->row();
    if( $ref_exit && isset($ref_exit->id)){
        $ci->db->where($data)->update('source_reference',array('reference'=>$ref));
    } else {
        $data['reference'] = $ref;
        $ci->db->insert('source_reference',$data);
    }

//     bug($ci->db->last_query());
}


function is_new_reference($ref, $type  ,$tran_no=0)
{
    if ( strtolower($ref)=='auto' ){
        return true;
    }
    $db_info = get_systype_db_info($type);

    $db_name = $db_info[0];
    $db_type = $db_info[1];
    $db_trans = $db_info[2];
    $db_ref = $db_info[3];

    $ref = (trim($ref));

    if ($db_ref == null) { // journal or bank trans store references in refs table
        $db_name = "refs";
        $db_type = 'type';
        $db_trans = 'id';
        $db_ref = 'reference';
    }

    $db_name = str_replace(TB_PREF, null, $db_name);

    $ci = get_instance();
    $ci->db->select("tran.$db_ref, tran.$db_trans")->from($db_name." AS tran");
//     $ci->db->left_join('voided AS v',"tran.$db_type=v.type AND tran.$db_trans=v.id");

    $ci->db->where(array("tran.".$db_ref=>$ref));
    $ci->db->where("tran.$db_trans NOT IN ( SELECT voided.id FROM voided AS voided WHERE voided.type=$type )");
//     $ci->db->where("v.id");

    if ($db_type != null) {

//         $sql = "SELECT $db_ref FROM $db_name
//         LEFT JOIN ".TB_PREF."voided v ON
//         $db_name.$db_type=v.type AND $db_name.$db_trans=v.id
//         WHERE $db_name.$db_ref=$ref AND ISNULL(v.id)
//         AND $db_name.$db_type=$type";

        $ci->db->where("tran.$db_type",$type);
//         $ci->db->where("ISNULL(v.id)");
    } else {
//         $sql = "SELECT $db_ref FROM $db_name
//         LEFT JOIN ".TB_PREF."voided v ON
//         v.type=$type AND $db_name.$db_trans=v.id
//         WHERE $db_ref=$ref AND ISNULL(v.id)";
//         $ci->db->where(array("tran.".$db_ref=>$ref,"tran.$db_type"=>$type));
//         $ci->db->where("ISNULL(v.id)");
	}
	$data= $ci->db->get();
	$exist = true;
	if( !is_object($data) ){
	    check_db_error("could not test for unique reference", $ci->db->last_query());
	} elseif( $data->num_rows > 0 ) { foreach ($data->result() AS $row){
        if( isset($row->order_no) AND $row->order_no != $tran_no ){
            $exist = false;
        } elseif ( isset($row->trans_no) AND $row->trans_no != $tran_no ){
            $exist = false;
        }

	}}
	return $exist;
// bug($ci->db->last_query() ); die;
// 	$result = db_query($sql, "could not test for unique reference");

// 	return (db_num_rows($result) == 0);

}

?>