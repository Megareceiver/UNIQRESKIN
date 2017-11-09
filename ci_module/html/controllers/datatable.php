<?php
class HtmlDatatable  {
    function __construct() {
        $this->db = get_instance()->db;
        $this->db->reset();
    }

    var $limit = 100;

    public function view($tableFields = array(), $query,$elementID_out=NULL,$view='table_items',$view_module='html'){
        $offset = 0;

        $data = array('table'=>$tableFields,'items'=>NULL);

        if( is_object($query) && get_class($query)=="CI_DB_mysql_result" ){

//             $db = $this->db->query($this->db->last_query()." LIMIT $offset,".$this->limit);
            $db = $this->db->query($this->db->last_query());

            $data['items'] = $db->result();
        } elseif ( is_array($query)){
            $data['items'] = $query;
        }else {
            display_db_error("Database have errors!", $this->db->last_query(),false);
            return FALSE;
        }

        if( strlen($elementID_out) >0 ){
            div_start($elementID_out);
        }

        module_view($view,$data,true, true,$view_module);

        if( strlen($elementID_out) >0 ){
            div_end();
        }

    }
}