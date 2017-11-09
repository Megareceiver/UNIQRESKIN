<?php
class Sales_Update_Model extends CI_Model
{
    function add_gl_debtor_trans(){
        if ( !$this->db->field_exists('gl_code', 'debtor_trans_details')){
            $this->db->query(" ALTER TABLE `debtor_trans_details` ADD `gl_code` varchar(15) NULL DEFAULT NULL AFTER stock_id;");
        }

        $this->db->query("ALTER TABLE `gl_trans` CHANGE `memo_` `memo_` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
    }
}
