<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class UserSession {
    function __construct() {
        $ci = get_instance();
        $this->ci = $ci;
        $this->db = $ci->db;



        $this->page_security = 'SA_TAXREP';
//         $this->model = $this->ci->module_model( NULL ,'report',true);



    }
    function index(){
        show_404();
    }

    function login(){

        if( input_val('password') && input_val('username') ){
            bug($_SESSION["wa_current_user"]);
            die('sbumti');
        }

        global $assets_path;
        page_add_css($this->ci->page->theme."/css/login.css");

        $data = array('com_name'=>null,'logo'=>null);
        $company_info = $this->db->where_in('name',array('coy_name','coy_logo'))->get('sys_prefs')->result();

        if( !empty($company_info) ){
            foreach ($company_info AS $info){
                if( $info->name=='coy_logo' ){
                    $data['logo'] = $info->value;
                } else if ($info->name=='coy_name') {
                    $data['com_name'] = $info->value;
                }
            }
        }
        if (isset($_COOKIE['loginFalse'])) {
            unset($_COOKIE['loginFalse']);
            setcookie('loginFalse', '', time() - 3600);
            /*
             * <p style="color:red;">The user and password combination is not valid for the system.</p>
             */
        }
        module_view('login',$data,true,'login','user');
    }
}