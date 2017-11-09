<?php
class DocumentsBookkeepers  {
    function __construct() {
        $this->db = get_instance()->db;
        $this->input = get_instance()->input;
        $this->datatable = module_control_load('datatable','html');
//         $this->modal = module_control_load('modal','html');

        $this->mobile_model = module_model_load('mobile');
    }

    var $document_tran_types = array(
            null=>' -- All document types --',
            'bank_deposit'=>'Bank Deposit',
            'bank_payment'=>'Bank Payment',
            'bank_statement'=>'Bank Statement',
            'customer_bill'=>'Customer Bill',
            'customer_receipt'=>'Customer Receipt',
            'expense_bill'=>'Expense Bill',
            'expense_payment'=>'Expense Payment',
            'supplier_bill'=>'Supplier Bill',
            'supplier_payment'=>'Supplier Payment'
        );

    function table_document_type($row=NULL){
        $type = isset($row['tran_type']) ? $row['tran_type'] : NULL;

        return (strlen($type) > 0 ) ? $this->document_tran_types[$type] : NULL;
    }
    function table_document_status($row=NULL){
        if( !isset($row['status']) )
            return NULL;
        if( $row['status'] < 2 ){
            return '<span style="color:red; font-weight: bold;">UNPOST</span>';
        } else {
            return '<span style="color:#DDD; font-weight: bold;">POSTED</span>';
        }
    }
    function table_document_posting($row=NULL){
        if( $row['status']==1 ){
            return icon_submit('Posting'.$row['id'], '1' , 'info', 'fa-send-o', true, _('View File'),'Post');
        }
        return icon_submit('Detail'.$row['id'], '1' , 'success', 'fa-newspaper-o', true, _('View File'),'View');

    }
    function table_document_file($row=NULL){
        return icon_submit('ViewFile'.$row['id'], $row['id'], 'success', 'fa-file-image-o', true, _('View File'),'File');
    }
    function table_document_delete($row=NULL){
        return icon_submit("SelectDelete".$row['id'], 1 , 'danger', 'icon-trash', true, _('Remove line from document'));
    }

    var $fields = array(
        'tran_type' => array('type'=>'options','title'=> 'Type','value'=>null ),
        'date_from' => array('type'=>'date','title'=> 'From','value'=>"" ),
        'date_to' => array('type'=>'date','title'=> 'To','value'=>NULL ),
        'status' => array('type'=>'options','title'=> NULL,'value'=>0),
    );

    function index(){
        global $Ajax;

        $view_file = $this->view_file();
        $view_detail = $this->view_detail();
        $delete_item = $this->item_delete();
        $posting_item = $this->posting();
        $taget_id       = max($view_file,$view_detail,$delete_item,$posting_item);
        if( $taget_id > 0 ){
            if( post_edit('ConfirmDelete') ){
                global $Ajax;
                div_start("_dialog_span");div_end();
                $Ajax->activate('_dialog_span');
                $Ajax->activate('mobile_upload');
            } else {
                return;
            }

        }

        page("Documents Upload");
        $apk = "mobileAccountant_v1.0.apk";
        if( file_exists(config_item('assets_path')."../".$apk) ){
            msg_info(anchor(config_item('assets_domain')."../".$apk,"Download"),"Mobile Accountant (Android)");
        }
        
        box_start();
        start_form();

        $this->filter();

        $sql = $this->mobile_model->document_items( input_post('tran_type'), input_post('date_from'), input_post('date_to'),input_post('status'));
        $table = & new_db_pager('mobile_upload', $sql, $this->datatable_view);
        $table->ci_control = $this;
        display_db_pager($table);

        div_start("_dialog_span");div_end();
        end_form();

        box_footer();
        box_end();


        end_page();
    }

    private function filter()
    {
        row_start('inquiry-filter justify-content-center');
        col_start(5);
        input_array_selector("Document Type",'tran_type',NULL,$this->document_tran_types);
        col_start(2);
        input_date_bootstrap("From", 'date_from', begin_month());

        col_start(2);
        input_date_bootstrap("To", 'date_to', end_month());

        col_start(1);
        input_array_selector(NULL,'status',NULL,array(0=>'All',2=>'Posted',1=>'Unpost'));

        col_start(1);
        submit_bootstrap('RefreshInquiry', _("Show"), _('Refresh Inquiry'), 'default','search');
        row_end();
    }


    var $datatable_view = array(
        'Document Type'=>array(
            'fun' => 'table_document_type',
            //,'left',10,'bookeeper_type'
        ),
        'Upload ID'=>array( 'name'=>'id'),
        'Trn Ref'=>array('name'=>'ref','left',8),
        'Transaction Party'=>array('name'=>'party'),
        'Status'=>array('fun' => 'table_document_status','align'=>'center'),
        'Upload Date'=>array('type'=>'date','name'=>'datetime','align'=>'center'),
        'Data View'=>array('fun' => 'table_document_posting','align'=>'center'),
        'Document View'=>array('fun' => 'table_document_file','align'=>'center'),
        'DEL'=>array('fun' => 'table_document_delete','align'=>'center')
    );

    /*
     * Item value for listview
     */
    private function tran_type_str($tran_type=NULL){
        switch ($tran_type){
            case 'bank_deposit':
                $str = "BD";
                break;
            case 'bank_payment':
                $str = "BP";
                break;
            case 'bank_statement':
                $str = "BT"; break;

            case 'customer_bill':
                $str = "CB"; break;
            case 'customer_receipt':
                $str = "CR"; break;

            case 'expense_bill':
                $str = "EB"; break;
            case 'expense_payment':
                $str = "EB"; break;

            case 'supplier_bill':
                $str = "SB"; break;
            case 'supplier_payment':
                $str = "SP"; break;
            default:
                $str = NULL;
        }
        return $str;
    }

    private function upload_title($str=NULL){
        $title = null;
        if( strlen($str) > 0 ){
            $data = explode('_', $str);
            if( count($data) > 0 ) foreach ($data AS $t){
                $title .= " ".ucfirst($t);
            }
        }
        return $title;
    }

    private function view_detail(){
        global $Ajax;
        $tran_id = post_edit('Detail');
        if( !in_ajax() )
            return false;
        if ( !is_numeric($tran_id) OR $tran_id < 1 ){
            return false;
        }
        $data = $this->db->where('id',$tran_id)->get('documents_bookkeepers')->row();
        if( is_object($data) AND isset($data->id) ){
            $uploaded = unserialize($data->data);
            $fields = array(
            );
            foreach ($uploaded AS $k=>$val){
                if( !in_array($k, array('file','name="user_upload"')) ){
                    if( strpos($k, '_amount') OR strpos($k, '_open_balance') ){
                        $val = number_total(floatval($val));
                    }
                    $fields[$k] = array('type'=>'text','title'=>$this->upload_title($k),'value'=>$val );
                }

            }
            switch ($data->tran_type){
//                 case 'bank_deposit':
//                     $str = "BD";
//                     break;
//                 case 'bank_payment':
//                     $str = "BP";
//                     break;
//                 case 'bank_statement':
//                     $str = "BT"; break;

//                 case 'customer_bill':
//                     $str = "CB"; break;
//                 case 'customer_receipt':
//                     $str = "CR"; break;

//                 case 'expense_bill':
//                     $str = "EB"; break;
//                 case 'expense_payment':
//                     $str = "EB"; break;

                case 'supplier_bill':
                case 'supplier_payment':
                    $fields['supplier_id'] = array('type'=>'text','title'=>"Supplier",'value'=>NULL );

                    if( isset($uploaded['supplier_id']) ){
                        $supplier = $this->db->where('supplier_id',$uploaded['supplier_id'])->get('suppliers')->row();
                        if( is_object($supplier) ){
                             $fields['supplier_id']['value'] = $supplier->supp_name;
                        }
                    }
                    $fields['type']['title'] = "Expense Type";
                    if( isset($uploaded['type']) ){
                        $expense_type = $this->db->where('id',$uploaded['type'])->get('sys_expense_type')->row();
                        if( is_object($supplier) ){
                            $fields['type']['value'] = $expense_type->title;
                        }
                    }

                    break;
                default:
                    $str = NULL;
            }
            show_dialog('_dialog_span',array('fields'=>$fields,'title'=>$this->upload_title($data->tran_type)." Details"));

            return $tran_id;
        }
        return false;
    }

    private function view_file(){
        $view_file_id = post_edit('ViewFile');
        if ( is_numeric($view_file_id) AND $view_file_id > 0 AND in_ajax() ){
            $data = $this->db->where('id',$view_file_id)->get('documents_bookkeepers')->row();

            $dialog = array();
            if( file_exists(config_item('assets_path').$data->file) ){
                $dialog['img_src'] = config_item('assets_domain').$data->file;
            } else {
                $dialog['svg'] = '<svg width="500" height="300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 300" preserveAspectRatio="none"><defs><style type="text/css">#holder_15adab72675 text { fill:rgba(255,255,255,.75);font-weight:normal;font-family:Helvetica, monospace;font-size:10pt } </style></defs><g id="holder_15adab72675"><rect width="500" height="300" fill="#777"></rect><g><text x="225.5" y="155.5">500x300</text></g></g></svg>';
            }
            show_dialog('_dialog_span',$dialog);
            return $view_file_id;
        }
        return false;

    }

    private function item_delete($id=0){
        $select_delete_id = post_edit('SelectDelete');
        if ( is_numeric($select_delete_id) AND $select_delete_id > 0 AND in_ajax() ){
            $data = $this->db->where('id',$select_delete_id)->get('documents_bookkeepers')->row();
            $dialog = array(
                'title'=>"Confim Delete",
                'content'=>'Are you sure to delete Document '.$this->upload_title($data->tran_type).' <strong>'.$data->id.'</strong> ?',
                'button_ok'=>array('name'=>"ConfirmDelete$select_delete_id",'title'=>'Confirm', 'value'=>$select_delete_id),
            );
            show_dialog('_dialog_span',$dialog);
            return $select_delete_id;
        }

        $confirm_delete_id = post_edit('ConfirmDelete');
        if ( is_numeric($confirm_delete_id) AND $confirm_delete_id > 0 ){
            $data = $this->db->where('id',$confirm_delete_id)->get('documents_bookkeepers')->row();

            if( is_object($data) AND isset($data->id) ){
                $uploaded = unserialize($data->data);
                $this->db->where('id',$data->id)->delete('documents_bookkeepers');
                if( isset($uploaded['file']) && strlen($file=$uploaded['file']) > 0 && ($file_path=realpath(config_item('assets_path').$file))){
                    unlink($file_path);
                }
            }
            return $confirm_delete_id;
        }
        return FALSE;
    }

    private function posting(){
        $id = post_edit('Posting');
        if ( !is_numeric($id) OR !in_ajax() OR $id < 1 ){
            return false;
        }
        $data = $this->mobile_model->item($id);
        if( is_object($data) AND isset($data->id) ){
            switch ($data->tran_type){
                case 'bank_payment':    $uri = site_url('gl/gl_bank.php').'?NewPayment=Yes&document='.$data->id; break;
                case 'bank_deposit':    $uri = site_url('gl/gl_bank.php').'?NewDeposit=Yes&document='.$data->id; break;
                case 'bank_statement':  $uri = site_url("gl/bank_account_reconcile.php")."?document=".$data->id; break;
                case 'supplier_bill':   $uri = site_url('purchasing/supplier_invoice.php').'?New=1&document='.$data->id; break;
                case 'supplier_payment':$uri = site_url('purchasing/supplier_payment.php').'?document='.$data->id; break;
                case 'customer_receipt':$uri = site_url('sales/customer_payments.php').'?document='.$data->id; break;
                case 'customer_bill':   
                    $uri = site_url("sales/invoice")."?NewInvoice=1&document=".$data->id;
                    display_notification(_('This function is under construction'));
                    return FALSE;
                    break;

                case 'expense_bill':    $uri = ""; break;
                case 'expense_payment': $uri = ""; break;
                default:
                    $uri = NULL;
            }
        }
        if( strlen($uri) > 0 ){
            global $Ajax;
            div_start("_dialog_span");div_end();
            $Ajax->activate('_dialog_span');
            $Ajax->redirect($uri);
            return $id;
        }
        return FALSE;
    }

}