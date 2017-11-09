<?php
class AccountingCommentSmarty {


    static function comment_get($template=null){
        $tran_type = ( isset($template['tran_type']) )?$template['tran_type']:null;
        $tran_no = ( isset($template['tran_no']) )?$template['tran_no']:null;

        $str_return = "";

        $db_query = get_instance()->db->where(array('type'=>$tran_type,'id'=>$tran_no))->get('comments');

        if( !is_object($db_query) ){
            check_db_error("could not query comments transaction table", get_instance()->db->last_query(), false);
        } else {
            foreach ($db_query->result() AS $comment){
                if (strlen($str_return))
                    $str_return = $str_return . " \n";
                $str_return .= $comment->memo_;
            }


        }

        return $str_return;
    }
}