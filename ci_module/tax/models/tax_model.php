<?php
class Tax_Tax_Model extends CI_Model {
    function __construct(){
        parent::__construct();
//         global $ci;
//         $this->ci = $ci;

    }

    function get_setting($id=0){
        $data = array(

        );
        if( !$id ) return $data;

        $this->db->select('tax.*')->where('tax.id',$id)->from('tax_types AS tax');
        $this->db->join('chart_master AS chart1','chart1.account_code = tax.sales_gl_code','left')->select('chart1.account_name AS SalesAccountName');
        $this->db->join('chart_master AS chart2','chart2.account_code = tax.purchasing_gl_code','left')->select('chart2.account_name AS PurchasingAccountName');
        $data = $this->db->get()->row();
        return $data;
    }
}