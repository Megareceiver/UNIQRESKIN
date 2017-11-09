<?php
function comments_display($tran_type, $tran_no)
{
    $db = get_instance()->db;
    $query = $db->where(array("type"=>$tran_type,"id"=>$tran_no))->get("comments");
        
    $html = "";
    if (! is_object($query) or empty($query)) {
        check_db_error("could not query comments transaction table", $this->db->last_query());
    } elseif( $query->num_rows() > 0 ) {
        foreach ($query->result() AS $r){
            $html.= nl2br($r->memo_) . "<br>";
        }
    }
    return $html;
}