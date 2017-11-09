<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//class Sale extends CI_Controller {
class Taxes {
    var $aaa = '';
	function __construct() {
		global $ci;
		$this->ci = $ci;
		$this->taxes_model = $ci->model('tax',true);
		$this->ci->load_smarty_plugin('pdf_smarty');
	}

	function tax_inquiry(){
	    $table = array(
	        'type_name'=>array('3','Type'),
	        'desc'=>array('3','Description'),
	        'collectible'=>array('2','Amount'),
	        'net_input'=>array('2','Outputs/Inputs'),
	    );

	    $date_from = $this->ci->input->post('date_from');
	    $date_to = $this->ci->input->post('date_to');
	    if( !$date_from && !$date_to ){
	        $row = get_company_prefs();
	        $date = Today();
	        $date_to = add_months($date, -$row['tax_last']);
	        $date_to = end_month($date_to);
	        $date_from = begin_month($date_to);
	        $date_from = add_months($date_from, -$row['tax_prd'] + 1);

	    }
// 	    bug($date_from);die;

	    $items_tax_trans_detail = $this->taxes_model->get_summary($date_from, $date_to);

        $this->ci->view('gl/inquiry/tax',array('table'=>$table,'items'=>$items_tax_trans_detail,'from'=>$date_from,'to'=>$date_to));


	}
}