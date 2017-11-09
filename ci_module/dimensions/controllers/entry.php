<?php

class DimensionsEntry
{
    var $selected_id = NULL;

    function __construct ()
    {
        
        if (isset($_GET['trans_no'])) {
            $this->selected_id = $_GET['trans_no'];
        } elseif (isset($_POST['selected_id'])) {
            $this->selected_id = $_POST['selected_id'];
        } else
            $this->selected_id = - 1;
        
        $this->page_get();
        $this->page_submit();
    }

    function index ()
    {
        if (isset($_POST['closed']) && $_POST['closed'] == 1)
            display_note(_("This Department is closed."));
        
        start_form();
        
        box_start();
        
        row_start('justify-content-md-center');
        col_start(8);
        
        $this->form();
        row_end();
        
        box_footer_start();
        if ($this->selected_id != - 1) {
            submit_icon('UPDATE_ITEM', _("Update"),'save',_('Save changes to department'));
            if ($_POST['closed'] == 1)
                submit_icon('reopen', _("Re-open This Department"), 'rotate-right', _('Mark this department as re-opened'));
            else
                submit_icon('close', _("Close This Department"), 'unlink', _('Mark this department as closed'));
            submit_icon('delete', _("Delete This Department"),'trash-o', _('Delete unused department'));
        } else {
            submit_icon('ADD_ITEM', _("Add"), 'save');
        }
        box_footer_end();
        box_end();
        
        end_form();
    }

    private function form ()
    {
        global $SysPrefs;
        $dim = get_company_pref('use_dimension');
        if ($this->selected_id != - 1) {
            $myrow = get_dimension($this->selected_id);
            
            if (strlen($myrow[0]) == 0) {
                display_error(_("The class sent is not valid."));
                display_footer_exit();
            }
            
            // if it's a closed dimension can't edit it
            // if ($myrow["closed"] == 1)
            // {
            // display_error(_("This dimension is closed and cannot be
            // edited."));
            // display_footer_exit();
            // }
            
            $_POST['ref'] = $myrow["reference"];
            $_POST['closed'] = $myrow["closed"];
            $_POST['name'] = $myrow["name"];
            $_POST['type_'] = $myrow["type_"];
            $_POST['date_'] = sql2date($myrow["date_"]);
            $_POST['due_date'] = sql2date($myrow["due_date"]);
            $_POST['memo_'] = get_comments_string(ST_DIMENSION, $this->selected_id);
            
            $tags_result = get_tags_associated_with_record(TAG_DIMENSION, $this->selected_id);
            $tagids = array();
            while ($tag = db_fetch($tags_result))
                $tagids[] = $tag['id'];
            $_POST['dimension_tags'] = $tagids;
            
            hidden('ref', $_POST['ref']);
            input_label(_("Dimension Reference"), "ref");
            hidden('selected_id', $this->selected_id);
        } else {
            $_POST['dimension_tags'] = array();
            global $Refs;
            input_text(_("Class Reference"), 'ref',$Refs->get_next(ST_DIMENSION));
        }
        
        input_text(_("Name"), 'name');
        numbers_list(_("Type"), 'type_', null, 1, $dim);
        
        input_date_bootstrap(_("Start Date"), 'date_');
        input_date_bootstrap(_("Date Required By"), 'due_date',false,false,$SysPrefs->default_dimension_required_by());
//         date_row(_("Date Required By") . ":", 'due_date', '', null, $SysPrefs->default_dimension_required_by());
        
//         tag_list_row(_("Tags:"), 'dimension_tags', 5, TAG_DIMENSION, true);
        tags_list(_("Tags"), 'dimension_tags',5, TAG_DIMENSION, true);
        input_textarea(_("Memo"), 'memo_');
    }

    private function page_get ()
    {
        $finish = false;
        if (isset($_GET['AddedID'])) {
            display_notification(_("The department has been entered."));
            $finish = true;
        }
        
        if (isset($_GET['UpdatedID'])) {
            display_notification(_("The department has been updated."));
            $finish = true;
        }
        
        // ---------------------------------------------------------------------------------------
        
        if (isset($_GET['DeletedID'])) {
            display_notification(_("The dimension has been deleted."));
            $finish = true;
        }
        
        // ---------------------------------------------------------------------------------------
        
        if (isset($_GET['ClosedID'])) {
            $id = $_GET['ClosedID'];
            $finish = true;
            display_notification( _("The department has been closed. There can be no more changes to it.") . " #$id");
        }
        
        // ---------------------------------------------------------------------------------------
        
        if (isset($_GET['ReopenedID'])) {
            $id = $_GET['ReopenedID'];
            $finish = true;
            display_notification_centered( _("The department has been re-opened. ") . " #$id");
        }
        
        if( $finish ){
            box_start();
            row_start("justify-content-md-center");
            col_start(6);
            mt_list_start('Actions', '', 'blue');
                mt_list_link(_("Enter a &new department"));
                mt_list_link(_("&Select an existing department"),"/dimensions/inquiry/search_dimensions.php");
//                 hyperlink_no_params("", _("Enter a &new department"));
//                 hyperlink_no_params($path_to_root . "/dimensions/inquiry/search_dimensions.php", _("&Select an existing department"));
            row_end();
            box_footer();
            box_end();
            display_footer_exit();
        }
    }

    private function page_submit ()
    {
        $selected_id = $this->selected_id;
        if (isset($_POST['ADD_ITEM']) || isset($_POST['UPDATE_ITEM'])) {
            if (! isset($_POST['dimension_tags']))
                $_POST['dimension_tags'] = array();
            
            if (can_process()) {
                
                if ($selected_id == - 1) {
                    $id = add_dimension($_POST['ref'], $_POST['name'], 
                            $_POST['type_'], $_POST['date_'], $_POST['due_date'], 
                            $_POST['memo_']);
                    add_tag_associations($id, $_POST['dimension_tags']);
                    meta_forward($_SERVER['PHP_SELF'], "AddedID=$id");
                } else {
                    
                    update_dimension($selected_id, $_POST['name'], 
                            $_POST['type_'], $_POST['date_'], $_POST['due_date'], 
                            $_POST['memo_']);
                    update_tag_associations(TAG_DIMENSION, $selected_id, 
                            $_POST['dimension_tags']);
                    
                    meta_forward($_SERVER['PHP_SELF'], "UpdatedID=$selected_id");
                }
            }
        }
        
        // --------------------------------------------------------------------------------------
        
        if (isset($_POST['delete'])) {
            
            $cancel_delete = false;
            
            // can't delete it there are productions or issues
            if (dimension_has_payments($this->selected_id) ||
                     dimension_has_deposits($this->selected_id)) {
                display_error(
                        _(
                                "This department cannot be deleted because it has already been processed."));
                set_focus('ref');
                $cancel_delete = true;
            }
            
            if ($cancel_delete == false) { // ie not cancelled the delete as a result of above tests
              
                // delete
                delete_dimension($this->selected_id);
                delete_tag_associations(TAG_DIMENSION, $this->selected_id, true);
                meta_forward($_SERVER['PHP_SELF'], "DeletedID=$this->selected_id");
            }
        }
        
        // -------------------------------------------------------------------------------------
        
        if (isset($_POST['close'])) {
//             bug($this->selected_id);die;
            // update the closed flag
            close_dimension($this->selected_id);
            meta_forward($_SERVER['PHP_SELF'], "ClosedID=$this->selected_id");
        }
        
        if (isset($_POST['reopen'])) {
            
            // update the closed flag
            reopen_dimension($this->selected_id);
            meta_forward($_SERVER['PHP_SELF'], "ReopenedID=$this->selected_id");
        }
    }
}