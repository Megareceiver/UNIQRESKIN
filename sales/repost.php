<?php
$page_security = 'SA_SALESINVOICE';
$path_to_root = "..";
// include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
global $ci;

$ci->controller_load('repost');
$ci->repost->index(); die;

$type = $ci->input->get('type');
if ( !$type ) {
    $type = ST_SALESINVOICE;
}
if( $ci->input->get('do') ){
	include_once($path_to_root . "/sales/includes/cart_class.inc");
	$data = array('done'=>1,'next'=>$ci->input->get('next'));
	$sale_model = $ci->model('sale',true);
	$cart = new Cart($type, array($ci->input->get('do')) );
	$invoice_no = $sale_model->write_sales_invoice( $cart,true );
	echo json_encode($data); die;
}

$js = get_js_date_picker();
$js.= add_js_ufile('/js/jquery.min.js').add_js_ufile('/js/import.js');


page('Re-Post Sale invoice',false, false, "", $js);
$start_date = $ci->input->post('start_date');
$end_date = $ci->input->post('end_date');
if( $start_date || $end_date ) {

    $ci->db->select('trans.trans_no')->where(array('trans.type'=>$type,'trans.ov_amount !='=>0));

    if( $ci->input->post('all') !=true ){

        $ci->db->join('gl_trans AS gl','gl.type=trans.type AND gl.type_no=trans.trans_no');
        $ci->db->where_in('gl.account',array(4450,4451))->where('gl.amount !=',0);
        $ci->db->get('gl.*');
    }

    $ci->db->group_by('trans.trans_no');
    if( $start_date ){
        $ci->db->where('trans.tran_date >=',date2sql($start_date));
    }
    if( $end_date ){
        $ci->db->where('trans.tran_date <=',date2sql($end_date));
    }
    $invoices = $ci->db->get('debtor_trans AS trans')->result();

    $i = 0;
    if( count($invoices) > 0 ) {
    	$items = ''; foreach ($invoices AS $inv) {$items .= ','.$inv->trans_no;}

		echo '<input type="hidden" id="ajaxtotal" value="'.count($invoices).'" >'
				.'<input type="hidden" id="ajaxcomplate" value="0" >'
				.'<textarea id="invoices" style="display:none;" >'.substr($items,1).'</textarea>';
		echo "<script type='text/javascript'>setTimeout( do_repost_sale,0);</script>";
    }
} else {

    $form = array(
        'start_date' => array('type'=>'DATE','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
        'end_date' => array('type'=>'DATE','title'=>_('End Date'),'value'=>end_fiscalyear()),
    );
    if( $ci->input->get('all') ){
        $form['all'] = array('type'=>'HIDDEN','title'=>null,'value'=>$ci->input->get('all') );
    }
    start_form();
    $ci->view('common/form',array('items'=>$form));
    submit('UPDATE_ITEM', _("Submit"), true, _('Submit'));
    end_form();
}
end_page();

