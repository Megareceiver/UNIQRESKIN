<?php
$page_security = 'SA_TAXRATES';
$path_to_root = "..";
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/purchasing/includes/supp_trans_class.inc");

global $ci;
$trans_type = $ci->input->get('type');
$js = get_js_date_picker();
$js.= add_js_ufile('/js/jquery.min.js').add_js_ufile('/js/import.js');

if( $trans_type == ST_SUPPINVOICE ){
    page('Re-Post Purchase invoice',false, false, "", $js);
} elseif( $trans_type==ST_SUPPCREDIT ) {
    page('Re-Post Supplier Credit Note',false, false, "", $js);
} else {
    page('Re-Post Purchase',false, false, "", $js);
}

$from = $ci->input->post('start_date');
$to = $ci->input->post('end_date');

$form = array(
    'start_date' => array('type'=>'DATE','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
    'end_date' => array('type'=>'DATE','title'=>_('End Date'),'value'=>end_fiscalyear()),
    'trans_type' => array('type'=>'HIDDEN','title'=>_('End Date'),'value'=>$trans_type),
    'all'=> array('type'=>'HIDDEN','title'=>_('End Date'),'value'=>$ci->input->get('all') ),
);

if( $from && $to ){
    $ci->controller_load('purchase_repost');
    $trans_type = $ci->input->post('trans_type');
    $supplier_model = $ci->model('supplier',true);

    if( $trans_type==ST_SUPPINVOICE || $trans_type==ST_SUPPCREDIT  ){
        $ci->db->from('supp_trans AS trans');

        if( $ci->input->post('all') != true ){

            $ci->db->join('gl_trans AS gl','gl.type=trans.type AND gl.type_no=trans.trans_no');
            $ci->db->where_in('gl.account',array(4450,4451))->where('gl.amount !=',0);
            $ci->db->where( array('gl.tran_date >='=>date2sql($from),'gl.tran_date <='=>date2sql($to),'trans.type'=>$trans_type) );
        } else {
            $ci->db->where( array('trans.tran_date >='=>date2sql($from),'trans.tran_date <='=>date2sql($to),'trans.type'=>$trans_type,'trans.ov_amount >'=>0) );
        }

        $ci->db->group_by('trans.trans_no');

        $transactions = $ci->db->get()->result();


        foreach ( $transactions AS $inv){
//             $ci->purchase_repost->create_cart($inv->trans_no,$trans_type);

            $_SESSION['supp_trans'] = new supp_trans($trans_type);

            read_supp_invoice($inv->trans_no, ST_SUPPINVOICE, $_SESSION['supp_trans']);

            $supplier_model->supplier_invoice($_SESSION['supp_trans']);
        }
    } else if ( $trans_type==ST_SUPPRECEIVE ) {

        $ci->controller_load('repost');
        if( !function_exists('create_new_po') ){
            include_once(ROOT."/purchasing/includes/po_class.inc");
            include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");
            include_once($path_to_root . "/purchasing/includes/db/suppliers_db.inc");
        }
        $grns = $ci->db->where( array('delivery_date >='=>date2sql($from), 'delivery_date <='=>date2sql($to)) )->get('grn_batch')->result();
        if( $grns && count($grns) >0 ){
            foreach ($grns AS $grm){
                $ci->repost->supp_receive( $grm->id );
            }
        }

//
    }
} else {
    start_form();
    $ci->view('common/form',array('items'=>$form));
    submit('UPDATE_ITEM', _("Submit"), true, _('Submit'));
    end_form();
}
end_page();



?>
