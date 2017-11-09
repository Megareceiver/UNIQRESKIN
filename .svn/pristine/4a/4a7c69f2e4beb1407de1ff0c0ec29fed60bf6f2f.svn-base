<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class AdminExpenseType
{

    static $f_year = NULL;

    function __construct()
    {
        global $ci;
        $this->ci = $ci;
        $this->db = $ci->db;
        $this->model = module_model_load('expense');
        $page_security = 'SA_FISCALYEARS';
        //$ci->smarty->registerPlugin('function', 'AdminExpenseType_Items', "AdminExpenseType::listViewActions");
        // self::$f_year = get_company_pref('f_year');
    }

    function listViewActions($row = NULL)
    {
        $html = button("edit" . $row['id'], $row['id'], _("Edit"), ICON_EDIT, $aspect = false);

        $html .= button("delete" . $row['id'], $row['id'], _("Delete"), ICON_DELETE, $aspect = false);
        return $html;
    }

    function index()
    {
        global $Ajax;
        if (in_ajax()) {
            $Ajax->activate('_page_body');
        }

        page(_("Expense Type"));
        start_form($multi = false, $dummy = false, $action = site_url('admin/expense-type'));

        $selected_id = 0;
        if (post_edit('edit')) {
            $selected_id = post_edit('edit');
        } elseif ($id = post_edit('delete')) {
            $this->model->delete($id);
        } elseif ($_POST) {
            $this->submit();
        }

        box_start();

        $table = & new_db_pager('expense_type', $this->model->items_table(), $this->datatable_view);
        $table->ci_control = $this;
        display_db_pager($table);

        box_start();
        $this->edit($selected_id);

        box_footer_start();
        submit_add_or_update_center(($selected_id > 0) ? false : true, '', 'default');
        box_footer_end();
        box_end();
        end_form();
        end_page();
    }

    var $datatable_view = array(
        'Class' => array(
            "name" => 'class'
        ),
        'Type' => array(
            "name" => 'title'
        ),
        'COA Mapping' => array(
            "name" => 'gl_account'
        ),
        'COA Description' => array(
            "name" => 'gl_description'
        ),
        'Actions' => array(
            'fun' => 'listViewActions',
            'align' => 'center'
        )
    );

    var $field = array(
        'id' => array(
            NULL,
            'HIDDEN'
        ),
        'title' => array(
            "Expense Type"
        ),
        'class' => array(
            "Class"
        ),
        'gl_account' => array(
            "COA Mapping",
            'gl_acc'
        )
    );

    private function edit($id = 0)
    {
        $row = $this->model->get_row($id);
        if (! empty($row))
            foreach ($this->field as $k => $a) {
                if (isset($row->$k)) {
                    $this->field[$k][2] = $row->$k;
                }
            }
        form_edit($this->field, false);
    }

    private function submit()
    {
        $selected_id = $this->ci->input->post('id');

        if (input_post('UPDATE_ITEM') && intval($selected_id) > 0) {

            $this->data = array();

            if (! $this->check_data_add())
                return false;

            $this->model->update($selected_id, $this->data);

            display_notification(_('Selected Expense Type has been updated'));
        } elseif ($_POST['ADD_ITEM']) {
            $this->data = array();

            if (! $this->check_data_add())
                return false;

            $this->model->add($this->data);
            display_notification(_('New Expense Type has been added'));
            
        }
        global $Ajax;
        $Ajax->activate('_page_body');
        
    }

    private function check_data_add()
    {
        $id = input_post('id');
        $title = input_post('title');

        if ( $this->model->check_exist($title, $id)) {
            display_error(_("Expense Type is duplicated."));
            set_focus('title');
            return false;
        }

        $this->data = array(
            'id' => $id,
            'title' => $title,
            'class'=>input_post('class'),
            'gl_account'=>input_post('gl_account')
        );
        return true;
    }
}