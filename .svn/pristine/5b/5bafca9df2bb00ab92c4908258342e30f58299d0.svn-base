<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AdminFiscalYears {
    static $f_year = NULL;
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->db = $ci->db;
        $this->model = $this->ci->module_model( $ci->module,'fiscalyear',true);
        $page_security = 'SA_FISCALYEARS';
        $ci->smarty->registerPlugin('function', 'AdminFiscalYears_Items', "AdminFiscalYears::listViewActions" );
        self::$f_year = get_company_pref('f_year');

    }

    function index(){

        if( $this->ci->uri->uri_string !='admin/fiscal-years' ){
            redirect('admin/fiscal-years');
        }

        global $Ajax;
        if(in_ajax()) {
            $Ajax->activate('_page_body');
        }

        page(_("Fiscal Years"));

        

        start_form($multi=false, $dummy=false, $action=site_url('admin/fiscal-years'));
        
        $selected_id = 0;
        if( post_edit('edit') ){
            $selected_id = post_edit('edit');
            $Ajax->addFocus(true, 'begin');
        } elseif (post_edit('delete') OR post_edit("delete_confim") OR post_edit("delete_purgin")) {
            $this->delete();
        } elseif( $_POST ){
            $this->submit();
        }
        
        if( $selected_id == 0 AND !in_ajax() ){
            display_warning(_("Warning: Deleting a fiscal year all transactions are removed and converted into relevant balances. This process is irreversible!"),1);
            display_warning(_("The marked fiscal year is the current fiscal year which cannot be deleted."), 0, 1, "class='currentfg'");
        
        }
        
        $items = $this->model->items();

        box_start();
        //table_view($this->table_view,$items,false,false);
        ci_table_view($this->table_view,$this->model->items());

        box_start($title = NULL, $icon = NULL, $new_row=true, $box_id='fiscalyear-form');
        $this->edit($selected_id);

        box_footer_start();
        submit_add_or_update_center(($selected_id >0) ? false : true, '', 'default');
        box_footer_end();
        box_end();
        end_form();
        end_page();
    }

    var $table_view = array(
        'begin'=>array("Fiscal Year Begin",null,20,'date'),
        'end'=>array("Fiscal Year End",null,20,'date'),
        'closed_text'=>array("Closed",null,50,),
        'items_action'=>array(NULL,'','AdminFiscalYears_Items')
    );
    var $field = array(
        'id'=>array(NULL,'HIDDEN'),
        'begin'=>array("Fiscal Year Begin",'qdate'),
        'end'=>array("Fiscal Year End",'qdate'),
        'closed'=>array("Is Closed",'checkbox'),
    );

    private function submit(){

        $selected_id = $this->ci->input->post('id');

        if ( input_post('UPDATE_ITEM') &&  intval($selected_id) > 0){
            $close = $this->ci->input->post('closed');
            $close = ( $close || $close=='on' ) ? 1 : 0;
            if ( $close == 1 ){
                if ( $this->model->check_years_before($this->ci->input->post('begin'), false)){
                    display_error( _("Cannot CLOSE this year because there are open fiscal years before"));
                    set_focus('closed');
                    return false;
                }
                $ok = $this->model->close_year($selected_id);
                $this->model->update($selected_id, array('closed'=>$close));
            } else {
                $this->model->open_year($selected_id);
                $data = array(

                    'begin'=>date2sql($this->ci->input->post('begin')),
                    'end'=>date2sql($this->ci->input->post('end')),
                    'closed'=>$close

                );
                $this->model->update($selected_id, $data);
                $ok = true;
            }

            if ($ok){
                display_notification(_('Selected fiscal year has been updated'));
            }

        } elseif ( input_post('ADD_ITEM') ){
            $this->data = array();

            if (!$this->check_data_add() )
                return false;

            $this->model->add($this->data);
            display_notification(_('New fiscal year has been added'));
        }
//         redirect('admin/fiscal-years');
    }

    private function check_data_add(){
        $data = array(
            'id'=>$this->ci->input->post('id'),
            'begin'=>$this->ci->input->post('begin'),
            'end'=>$this->ci->input->post('end'),
            'closed'=> ( $this->ci->input->post('closed') || $this->ci->input->post('closed')=='on' ) ? true : false
        );

    	if (!is_date($data['begin']) || $this->model->in_fiscalyears($data['begin'])) {
    		display_error( _("Invalid BEGIN date in fiscal year."));
    		set_focus('from_date');
    		return false;
    	}

    	if ( !is_date($data['end']) || $this->model->in_fiscalyears($data['end'])) {
    		display_error( _("Invalid END date in fiscal year."));
    		set_focus('to_date');
    		return false;
    	}

    	if (! $this->model->check_begin_end_date($data['begin'], $data['end'])) {
    		display_error( _("Invalid BEGIN or END date in fiscal year."));
    		set_focus('from_date');
    		return false;
    	}

    	if ( strtotime($data['begin']) >= strtotime($data['end']) ) {
    		display_error( _("BEGIN date bigger than END date."));
    		set_focus('from_date');
    		return false;
    	}
    	$this->data = $data;
    	return true;
    }

    private function edit($id=0){


        if( $id < 1 ){
            $this->field['begin'][2] = NULL;

            $max_fiscalyear = $this->db->select('MAX(end) AS end')->get('fiscal_year')->row();
            if( $max_fiscalyear && isset($max_fiscalyear->end) ){
                $this->field['begin'][2] = add_days(sql2date($max_fiscalyear->end), 1);
                $this->field['end'][2] = end_month(add_months($this->field['begin'][2], 11));
            }

        } else {
            $data = $this->db->where('id',$id)->get('fiscal_year')->row();
            $this->field['begin'][2] = sql2date($data->begin);
            $this->field['end'][2] = sql2date($data->end);
            $this->field['closed'][2] = $data->closed;
            $this->field['id'][2] = $data->id;
        }

        bootstrap_set_label_column(6);
        form_edit($this->field,false,4);


    }

    private function delete($id=0){
        $check_id = post_edit('delete');
        $delete_confim = post_edit('delete_confim');
        $delete_purgin = post_edit('delete_purgin');

        if( !$delete_confim && ! $delete_purgin ) {
            $year_select = $this->model->get_fiscalyear($check_id);
            if( !$this->model->check_is_last($year_select->id) ){
                display_error(_('Cannot delete this fiscal year. Click <a href="#" data-toggle="modal" data-target="#delete-fiscalyear" >HERE</a> to see the conditions for deletion.'));
                modal_view('delete_msg_popup',array('id'=>'delete-fiscalyear','class'=>'danger','title'=>'Deleting a Fiscal Year'));
                return false;
            }


            $trans_total = $this->model->trans_in_year($year_select->id);

            if ($year_select->closed  == 0 && intval($trans_total) > 0){
                display_error(_("Cannot delete this fiscal year because the fiscal year is not closed."));
                return false;
            }

            $data = array(
                'id'=>'delete-fiscalyear-confim',
                'class'=>'errormsg',
                'title'=>'WARNING - Delete Fiscal Year',
                'button_name'=>'delete_confim'.post_edit('delete'),
                 "button_value"=>post_edit('delete'),
                'year'=>$this->model->get_fiscalyear(post_edit('delete'))
            );
            $this->ci->temp_view('delete_confim_msg_popup', $data,'confim_popup','admin',true);
        } else if ( $delete_confim ) {
            $year = $this->model->get_fiscalyear($delete_confim);
            $data = array(
                'id'=>'delete-fiscalyear-confim',
                'class'=>'errormsg',
                'title'=>'PURGING Fiscal Year ('.sql2date($year->begin).' to '.sql2date($year->end).')',
                'button_name'=>'delete_purgin'.$delete_confim,
            );
            $this->ci->temp_view('purgin_confim_msg_popup', $data,'confim_popup','admin',true);
        } else if ( $delete_purgin ) {
            $year_select = $this->model->get_fiscalyear($delete_purgin);
            if( isset($year_select->id) ){
                log_add('fiscal_year',-1,$year_select->id);
                $this->model->delete($year_select->id);
                display_notification(_('Selected fiscal year has been deleted'));
            }
        }

    }

    static function listViewActions($item=NULL){
        $html = tbl_edit("edit".$item->id, $item->id,false);

        if( self::$f_year != $item->id){
            $html.= tbl_remove("delete".$item->id, $item->id,false,false);
        }
        return $html;
    }
}