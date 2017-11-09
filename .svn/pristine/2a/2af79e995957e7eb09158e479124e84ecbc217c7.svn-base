<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fiscal_Year {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->db = $ci->db;
        $this->fiscalyear_model = $ci->model('fiscalyear',true);
    }
    var $mode = null;
    function index(){
        $js = "";

        $js .= get_js_date_picker();
        page(_($help_context = "Fiscal Years"), false, false, "", $js);
        simple_page_mode(true);

        global $Ajax, $Mode, $selected_id;
        $this->mode = $Mode;

        if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') {
            if( $Mode=='UPDATE_ITEM' ){
                display_error(_("This function is temporary disabled for upgrading") );
                return false;
            }
        	$this->handle_submit($selected_id);
        } else if ( $Mode == 'Delete' ){
            display_error(_("This function is temporary disabled for upgrading") );
            return false;
//             $this->handle_delete($selected_id);
        } else if ( $Mode == 'RESET' ){
            $selected_id = -1;
        }

        $this->items();
        $this->form($selected_id);

        $popup_delete = '<p style="padding-bottom: 15px;" >You can delete fiscal year with the fllowing conditions:</p><ul>'
            .'<li>The latest fiscal year must be deleted first before older fiscal year can be deleted. For example if there are 2014,2015 & 2016 in the system, them 2016 first be deleted before you can delete 2015</li>'
            .'<li>The fiscal year to be deleted must be closed if transactions exist</li>'
            .'<li>If the fiscal year does not have any transaction, the deletion will be permitted regardless of being closed or not.</li>'
        .'</ul>'
                            ;
        error_popup('delete-fiscalyear','errormsg','Deleting a Fiscal Year',$popup_delete);
        end_page();
    }

    private function items(){
        $company_year = get_company_pref('f_year');


        start_form();
        display_note(_("Warning: Deleting a fiscal year all transactions are removed and converted into relevant balances. This process is irreversible!"),0, 1, "class='currentfg'");
		start_table(TABLESTYLE);

		$th = array(_("Fiscal Year Begin"), _("Fiscal Year End"), _("Closed"), "", "");
		table_header($th);

		$items = $this->db->order_by('begin')->get('fiscal_year')->result();

		$k=0;
		if( $items ) foreach ($items AS $ite){
		    if ($ite->id == $company_year) {
		        start_row("class='stockmankobg'");
		    } else
		        alt_table_row_color($k);

		    $from = sql2date($ite->begin);
		    $to = sql2date($ite->end);
		    if ($ite->closed == 0){
		        $closed_text = _("No");
		    } else {
		        $closed_text = _("Yes");
		    }
		    label_cell($from);
		    label_cell($to);
		    label_cell($closed_text);
		    edit_button_cell("Edit".$ite->id, _("Edit"));
// 		    	 	label_cell(null);
		    if ($ite->id != $company_year) {
		        delete_button_cell("Delete".$ite->id, _("Delete"));
		        submit_js_confirm("Delete".$ite->id,sprintf(_("Are you sure you want to delete fiscal year %s - %s? All transactions are deleted and converted into relevant balances. Do you want to continue ?"), $from, $to));
		    } else
		        label_cell('');
		    end_row();
		}

		end_table();
		end_form();
		display_note(_("The marked fiscal year is the current fiscal year which cannot be deleted."), 0, 0, "class='currentfg'");

    }
    private function form($id=-1){
        start_form();
        start_table(TABLESTYLE2);

        if ($id != -1) {
            if($this->mode =='Edit') {
                $myrow = $this->db->where('id',$id)->get('fiscal_year')->row();

                $_POST['from_date'] = sql2date($myrow->begin);
                $_POST['to_date']  = sql2date($myrow->end);
                $_POST['closed']  = $myrow->closed;
            }
            hidden('from_date');
            hidden('to_date');
            label_row(_("Fiscal Year Begin:"), $_POST['from_date']);
            label_row(_("Fiscal Year End:"), $_POST['to_date']);
        } else {
            $max_fiscalyear = $this->db->select('MAX(end) AS end')->get('fiscal_year')->row();
            if( $max_fiscalyear && isset($max_fiscalyear->end) ){
                $begin = add_days(sql2date($max_fiscalyear->end), 1);
            } else {
                $begin = NULL;
            }

            if ( $begin ) { // AND $Mode != 'ADD_ITEM'
                $_POST['from_date'] = $begin;
                $_POST['to_date'] = end_month(add_months($begin, 11));
            }
//             bug($begin);
//             date_row(_("Fiscal Year Begin:"), 'from_date', '', null, 0, 0, 1001);
            echo $this->ci->finput->qdate('Fiscal Year Begin','from_date',null,'row');
            echo $this->ci->finput->qdate('Fiscal Year End','to_date',null,'row');
//             date_row(_("Fiscal Year End:"), 'to_date', '', null, 0, 0, 1001);
        }

        hidden('selected_id', $id);
        yesno_list_row(_("Is Closed:"), 'closed', null, "", "", false);
        end_table(1);
        submit_add_or_update_center($id == -1, '', 'both');
        end_form();
    }

    private function handle_submit($selected_id = -1){
        $ok = true;
        if ($selected_id != -1){
            if ($_POST['closed'] == 1){
                if (check_years_before($_POST['from_date'], false)){
                    display_error( _("Cannot CLOSE this year because there are open fiscal years before"));
                    set_focus('closed');
                    return false;
                }
                $ok = close_year($selected_id);
            } else
                open_year($selected_id);

            if ($ok){
                update_fiscalyear($selected_id, $_POST['closed']);
                display_notification(_('Selected fiscal year has been updated'));
            }
        } else {
            if (!$this->check_data())
                return false;
            add_fiscalyear($_POST['from_date'], $_POST['to_date'], $_POST['closed']);
            display_notification(_('New fiscal year has been added'));
        }
        $this->mode = 'RESET';
    }

    private function check_data(){

    	if (!is_date($_POST['from_date']) || is_date_in_fiscalyears($_POST['from_date']))
    	{
    		display_error( _("Invalid BEGIN date in fiscal year. is_date=".is_date($_POST['from_date']) ));
    		set_focus('from_date');
    		return false;
    	}
    	if (!is_date($_POST['to_date']) || is_date_in_fiscalyears($_POST['to_date']))
    	{
    		display_error( _("Invalid END date in fiscal year."));
    		set_focus('to_date');
    		return false;
    	}
    	if (!check_begin_end_date($_POST['from_date'], $_POST['to_date']))
    	{
    		display_error( _("Invalid BEGIN or END date in fiscal year."));
    		set_focus('from_date');
    		return false;
    	}
    	if (date1_greater_date2($_POST['from_date'], $_POST['to_date'])) {
    		display_error( _("BEGIN date bigger than END date."));
    		set_focus('from_date');
    		return false;
    	}
    	return true;
    }

    private function handle_delete($selected_id=-1){

        display_error(_("This function is temporary disabled for upgrading") );
        return false;

//         $fiscalyear_model = $ci->model('fiscalyear',true);
        $myrow = $this->db->where('id',$selected_id)->get('fiscal_year')->row();
//         $myrow = get_fiscalyear($selected_id);
        // PREVENT DELETES IF DEPENDENT RECORDS IN gl_trans
        // 	if (check_years_before(sql2date($myrow['begin']), true)){
        // 		display_error(_("Cannot delete this fiscal year because there are fiscal years before."));
        // 		return false;
        // 	}
        if( !$this->fiscalyear_model->check_is_last($selected_id) ){
            display_error(_('Cannot delete this fiscal year. Click <a href="#" data-toggle="modal" data-target="#delete-fiscalyear" >HERE</a> to see the conditions for deletion.'));
            return false;
        }
        if(  $this->fiscalyear_model->trans_in_year($selected_id) > 0 ){
            display_error(_("Cannot delete this fiscal year because there are transactions inside.") );
            return false;
        }

        if ($this->check_can_delete($selected_id)) {
            global $session;
            $this->ci->api_membership->get_data('log/fiscalyear/'.$myrow->begin.'/'.$myrow->end.'/'.$session->checkSubDirectory());
            //only delete if used in neither customer or supplier, comp prefs, bank trans accounts
            delete_this_fiscalyear($selected_id);
            display_notification(_('Selected fiscal year has been deleted'));
        }
        $Mode = 'RESET';
    }

    private function check_can_delete($selected_id){
        global $ci;
        $fiscalyear_model = $ci->model('fiscalyear',true);

    	$myrow = get_fiscalyear($selected_id);
    	// PREVENT DELETES IF DEPENDENT RECORDS IN gl_trans
    // 	if (check_years_before(sql2date($myrow['begin']), true)){
    // 		display_error(_("Cannot delete this fiscal year because there are fiscal years before."));
    // 		return false;
    // 	}
        if( !$fiscalyear_model->check_is_last($selected_id) ){
            display_error(_('Cannot delete this fiscal year. Click <a href="#" data-toggle="modal" data-target="#delete-fiscalyear" >HERE</a> to see the conditions for deletion.'));

    		return false;
        }
        if(  $fiscalyear_model->trans_in_year($selected_id) > 0 ){
            display_error(_("Cannot delete this fiscal year because there are transactions inside.") );
        	return false;
        }
    // 	if ($myrow['closed'] == 0){
    // 		display_error(_("Cannot delete this fiscal year because the fiscal year is not closed."));
    // 		return false;
    // 	}
    	return true;
    }
}