<?php
class Accounting_Point_Model extends CI_Model {
    function __construct(){
        parent::__construct();
    }

    function get_sale_point($id){
        $this->db->select('pos.*')->from('sales_pos as pos');
        $this->db->left_join('locations as loc','pos.pos_location=loc.loc_code')->select('loc.location_name');
        $this->db->left_join('bank_accounts as acc','pos.pos_account=acc.id')->select('acc.bank_account_name');
        $this->db->where('pos.id',$id);
        $result = $this->db->get();

        if( is_object($result) )
            return $result->row();
        else {

            display_error( _("Could not get POS definition.") );
            return FALSE;
        }


//         $sql = "SELECT pos.*, loc.location_name, acc.bank_account_name FROM "
//             .TB_PREF."sales_pos as pos
// 		LEFT JOIN ".TB_PREF."locations as loc on pos.pos_location=loc.loc_code
// 		LEFT JOIN ".TB_PREF."bank_accounts as acc on pos.pos_account=acc.id
// 		WHERE pos.id=".db_escape($id);

//         $result = db_query($sql, "could not get POS definition");

//         return db_fetch($result);
    }
}