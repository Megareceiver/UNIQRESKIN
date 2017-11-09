<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');


class AdminAuditTrail
{

    function __construct()
    {
        $this->ci = get_instance();
        $this->db = $this->ci->db;
        $this->void_model = load_module_model('tran', true, 'void');
    }

    function index()
    {
        if ($this->ci->uri->uri_string != 'admin/audit-trail') {
            redirect('admin/audit-trail');
        }

        global $Ajax;
        $Ajax->activate('_page_body');

        page(_("Audit Trail"));
        start_form();
        box_start();
        module_view('audit_trail_filter', array(
            'fillter_title' => input_val('type')
        ));
        // table_view($this->table_view,$this->items(),false,true);
//         include_once ROOT."/includes/db_pager.inc";
        // start_table();

        unset($_SESSION['trans_tbl']);
        $table = & new_db_pager('trans_tbl', $this->items(true), $this->table_view);

        $table->ci_control = $this;
        display_db_pager($table);

        // end_table();
        box_footer();
        box_end();
        end_form();
        end_page();
    }

    // var $table_view = array(
    // 'type'=>array('Type',null,12,'trans_type','type'),
    // 'trans_no'=>array('Trans Number','center',10),
    // 'username'=>array('Created by',null,15),
    // 'description'=>'Description',
    // 'gl_date'=>array('Trans Date','center',10,'date_str','gl_date'),
    // 'stamp'=>array('Created Date','center',20,'datetime_str','stamp'),
    // 'items_action'=>array(null,'center','gl_view_str'),

    // );
    var $table_view = array(
        'type' => array(
            'label' => 'Type',
            'fun' => 'systype_name',
            'name' => 'type',
            'width' => '13%',
        ),
        'trans_no' => array(
            'label' => 'Trans Number',
            'width' => '11%',
            'class'=>'text-center',
            'align' => 'center',
        ),
        'username' => array(
            'label' => 'Created by',
            'width' => '10%'
        ),
        'description' => array(
            'label' => 'Description'
        ),
        'gl_date' => array(
            'label' => 'Trans Date',
            'type' => 'date',
            'width' => '9%',
//              'align' => 'center',
//             'class'=>'text-center'
        ),
        'stamp' => array(
            'label' => 'Created Date',
            'type' => 'tstamp',
            'width' => '14%',
//             'align' => 'center',
//             'class'=>'text-center'
        ),

        // 'items_action'=>array(null,'fun' => 'systype_name'),
        'gl' => array(
            'label' => "GL",
            'insert' => true,
            'fun' => 'gl_view',
            'align' => 'center',
            'width' => '5%',
            'class' => 'text-center'
        )
    )
    ;

    private function items($return_query = false)
    {
        $fillter = input_val('type');
        if (is_null($fillter)) {
            $fillter = 0;
        }
        $page = input_val('page');
        if (! $page) {
            $page = input_val('first');
        }
        if (! $page) {
            $page = input_val('next');
        }
        if (! $page) {
            $page = input_val('end');
        }
        if (intval($page) < 1) {
            $page = 1;
        }

        if ($return_query) {
            $this->db->select('a.type, a.trans_no');
        } else {
            $this->db->select('a.*');
        }
        //
        $this->db->from('audit_trail AS a');
        $this->db->select('u.real_name AS username')->join('users AS u', 'u.id=a.user', 'left');

        $this->db->select('a.description, a.gl_date, a.stamp');

        $this->db->where('type', $fillter);
        $this->db->order_by('a.stamp  DESC');
        // $this->void_model->not_voided('a.type','a.trans_no');

        if ($return_query) {
            $query = $this->db->get();
            return $this->db->last_query();
        }
        $tempdb = clone $this->db;

        // $items = $this->db->limit(page_padding_limit, page_padding_limit*($page-1) )->get()->result();
        $data['items'] = $this->db->limit(page_padding_limit, page_padding_limit * ($page - 1))
            ->get()
            ->result();
        $data['total'] = $tempdb->count_all_results();
        $data['page'] = $page;
        return $data;
    }
}