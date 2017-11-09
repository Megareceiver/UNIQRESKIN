<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Audit_trail {
    function __construct() {
        global $ci;
        $this->ci = $ci;
        $this->db = $ci->db;
    }
    function index(){
    	$js = get_js_date_picker();
    	$js .= get_js_open_window(900, 500);

    	$page_security = 'SA_GLSETUP';
    	page(_($help_context = "Audit Trail"),false, false, "", $js);

    	global $Ajax;
    	$Ajax->activate('_page_body');

    	$this->items();
    	end_page();
    }

    private function items(){
        $page = 0;
        if( $this->ci->input->post('first') ){
            $page = input_val('first');
        } elseif( $this->ci->input->post('pre') ){
            $page = input_val('pre');
        } elseif( $this->ci->input->post('next') ){
            $page = input_val('next');
        } elseif( $this->ci->input->post('end') ){
            $page = input_val('end');
        }

    	if( !$page ){
    		$page = 1;
    	}

    	$data['fillter_title'] = $this->ci->input->post('type');



    	$this->db->select('a.*')->from('audit_trail AS a');
    	$this->db->select('u.real_name AS username')->join('users AS u','u.id=a.user','left');

    		$this->db->where('type',$data['fillter_title']);


    	$tempdb = clone $this->db;

    	$data['items'] = $this->db->limit(page_padding_limit, page_padding_limit*($page-1) )->order_by('a.stamp  DESC')->get()->result();
    	$data['total'] = $tempdb->count_all_results();
    	$data['page'] = $page;
    	$data['lastpage'] = round2($data['total']/page_padding_limit,0);
//     	bug($data['lastpage'] );die;

    	$data['table'] = array(
    		'type'=>array('Type',null,12),
    		'trans_no'=>array('Trans Number','center',10),
    		'username'=>array('Created by',null,15),
    		'description'=>'Description',
    		'gl_date'=>array('Trans Date','center',10),
    		'stamp'=>array('Created Date','center',12),
    		'actions'=>array('','center',5)
    	);

    	start_form();
    	$this->ci->view('page_fillter/audit_trail',array('fillter_title'=>input_val('type')));
    	$this->ci->view('common/table',$data);
    	end_form();
    }
}