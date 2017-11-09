<?php
define('BASEPATH', dirname(__FILE__).'/');

define('APPPATH', dirname(__FILE__).'/');

date_default_timezone_set('Asia/Singapore');
// ini_set('memory_limit', '-1');
// ini_set('max_execution_time', 123456);

function &get_instance() {
    global $ci;
    return $ci;
}

if( !defined('MODULEPATH') ){
    define('MODULEPATH', dirname(__FILE__).'/../ci_module/');
}
set_time_limit(360);
error_reporting(E_ALL);
ini_set('display_errors', 1);
class ci {
	var $helper = array('url','cookie','file','text','string','input','tax','page','listview','formview');
	var $library = array('update','input','gl_trans','form','page');
	var $db_config;
	var $url_back = "javascript:goBack('-1');";
	function __construct($db_config=null){
		require(BASEPATH.'core/Common.php');
		global $theme;

		if ( isset($_SERVER['HTTP_REFERER']) AND $_SERVER['HTTP_REFERER'] != $this->url_back ){
		    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		    if( $_SERVER['HTTP_REFERER'] != $url ){
		        $this->url_back = $_SERVER['HTTP_REFERER'];
		    }
		}

		$this->theme = $theme;
		$this->smarty();
		$this->load_word();
		$this->load = & load_class('Loader', 'core');

		$this->load_config();
		$this->autoload();

		if( $db_config && !empty($db_config) ){
			if( !isset($db_config['host']) ){
				$db_config = $db_config[0];
			}
			$this->db_config = $db_config;
			$this->load_db($db_config);

		}

// 		$dbname_check = $this->db->query("SHOW DATABASES LIKE '".$db_config['dbname']."';")->row();
// 		if( !$this->db->conn_id || !$dbname_check ){
// 		    $this->view('error',array('power_by'=>$power_by));die();
// 		}

		if( $this->helper && count($this->helper) > 0 ) {
			foreach ($this->helper AS $helper){
				$this->load_helper($helper);
			}
		}

		$this->helper_at();

		if( $this->library && count($this->library) > 0 ){
			foreach ($this->library AS $lib){
				$this->load_library($lib);
			}
		}

		$this->input = &load_class('Input', 'core');
		$this->uri = &load_class('URI', 'core');
		$this->ref = & load_class('references', '/accounting/libraries','at_');
		$this->ref->db = $this->db;

		if ( ! class_exists('CI_Model', FALSE)){
		    load_class('Model', 'core');
		}

		$this->load_smarty_functions();
		$this->load_smarty_plugin('pdf_smarty');
		$this->load_smarty_plugin('form_smarty');
		$this->load_smarty_plugin('at_smarty');

// 		if( !class_exists('api_membership') ){
// 			include_once(BASEPATH.'libraries/api_membership.php');
// 		}

// 		$this->api = new api_membership();

		$this->update->check();

		$this->finput = & load_class('finput', 'libraries');

		$this->template = (object) array('layout'=>'common','module'=>NULL);
		$this->auto_load_module('fa');
		$this->auto_load_module('html');
		$this->auto_load_module('membership');
		$this->auto_load_module('gl');
		$this->auto_load_module();


		$this->librarie_in_module('bootstrap','html');
		$this->librarie_in_module('api','membership');

	}

	function load_config(){
	    if( !method_exists($this, 'config') ){
	        $this->config = &load_class('config', 'core');
	    }

	}
	function autoload(){
        include_once BASEPATH.'config/autoload.php';
        if( isset($autoload) ){
            if( !empty($autoload['helper_module']) ) foreach ($autoload['helper_module'] AS $module_name){
                foreach (glob(MODULEPATH."/$module_name/helpers/*.php") AS $contr){
                    include_once($contr);
                }
            }
        }
	}

	function load_helper($file=''){
		$ext_helper = BASEPATH.'helpers/'.$file.'_helper.php';
		if (file_exists($ext_helper)){
			include_once($ext_helper);
		}
	}

	function helper_at(){
	    foreach (glob(BASEPATH.'/helpers_at/*.php') AS $contr){
	        include_once($contr);
	    }
	}

	var $front_report = false;
	function load_library($file='',$return=false,$default_value = null){
		$ext_lib = BASEPATH.'libraries/'.$file.'.php';

		if( $this->front_report AND in_array($file, array('qpdf'))){
            return FALSE;
		}

         if( class_exists('TCPDF') ){
        //      bug('call TCPDF here '.$file);
         }

		if (file_exists($ext_lib)){
			include_once($ext_lib);
			$prefix = 'CI_';
			if( class_exists($file) ){
			    $object = new $file($default_value);
				if( $return ){
					return $object;
				} else {
					$this->$file = $object;
					unset($object);
				}
			} elseif(class_exists($prefix.$file)) {
			    $className = $prefix.$file;
			    $object = new $className();
			    if( $return ){
			        return $object;
			    } else {
			        $this->$file = $object;
			        unset($object);
			    }
			}

		}

// 		foreach (scandir(BASEPATH.'input') AS $input){
// 			if( !in_array($input, array('.','..') ) ){

// 				$input_name = basename($input, ".php");
// 				$input_class = $input_name.'_input';

// 				if( !class_exists($input_class) && $input !='.svn' ){

// 					require(BASEPATH.'input/'.$input);
// 					$this->$input_class = new $input_class();
// 				}
// 			}
// 		}
	}


	function load_db($db_config=null,$active_record=true,$return=false){

		$params = array(
			'dbdriver'	=> 'mysql',
			'hostname'	=> (isset($db_config['host'])) ? rawurldecode($db_config['host']) : '',
			'username'	=> (isset($db_config['dbuser'])) ? rawurldecode($db_config['dbuser']) : '',
			'password'	=> (isset($db_config['dbpassword'])) ? rawurldecode($db_config['dbpassword']) : '',
			'database'	=> (isset($db_config['dbname'])) ? rawurldecode($db_config['dbname']) : ''
		);
		require_once(BASEPATH.'database/DB_driver.php');
		if ( ! isset($active_record) OR $active_record == TRUE) {
			require_once(BASEPATH.'database/DB_active_rec.php');

			if ( ! class_exists('CI_DB')) {
				eval('class CI_DB extends CI_DB_active_record { }');
			}
		} else {
			if ( ! class_exists('CI_DB')) {
				eval('class CI_DB extends CI_DB_driver { }');
			}
		}

		require_once(BASEPATH.'database/drivers/'.$params['dbdriver'].'/'.$params['dbdriver'].'_driver.php');

		$driver = 'CI_DB_'.$params['dbdriver'].'_driver';
		$DB = new $driver($params);

		if ($DB->autoinit == TRUE) {
			$DB->initialize();
		}


		$this->db = $DB;

	}

	function controller_load($file=null,$return=false){


        if( $file && !isset($this->$file) ){
            $file_exists = false;
            if( file_exists(BASEPATH."/controllers/$file.php") ){
                $controller_name = pathinfo($file,PATHINFO_FILENAME);
                $controller_class_name = ucfirst($file);
                require_once BASEPATH."/controllers/$file.php";
                $file_exists = true;
            }


            if( $file_exists ){
                $this->$controller_name = new $controller_class_name();
            }

        }
	}

	function module_control_load($module=null,$control=null,$return=false,$loadDefault = true){

	    if( !$module ) return false;

	    $module = uri2_file_name($module);

	    $dir = realpath(MODULEPATH.DS.$module);

	    if( is_dir($dir)){
	        $this->auto_load_module($module);
	        $control_file = uri2_file_name($control);
	        $controller_class_name = str2_function_name($control);

	        $file = $dir."/controllers/$control_file.php";
	        $control_default = realpath($dir."/controllers/$control_file.php");
	        if( input_get('bugci') ){
	            bug($control_default);
	        }
	        
            if( $control && file_exists($control_default) ){

                $controller_name = pathinfo($control_default,PATHINFO_FILENAME);
                require_once $file;

                $controller_class_name = str2_function_name($module).str2_function_name($control);
                if( input_get('bugci') ){
                    bug($controller_class_name);
                }

                if( class_exists($controller_class_name) ){
                    if( $return ){
                        return new $controller_class_name();
                    }

                    $this->$controller_name = new $controller_class_name();
                    return true;
                } elseif ( class_exists($controller_class_name = str3_function_name($module).str3_function_name($control)) ){
                    if( $return ){
                        return new $controller_class_name();
                    }

                    $this->$controller_name = new $controller_class_name();
                    return true;
                }
            } elseif( file_exists($dir."/$module.php") ) {
	            $controller_class_name = str2_function_name($module);
	            require_once $dir."/$module.php";

	            $controller_name = pathinfo($dir."$module.php",PATHINFO_FILENAME);
	            //                 $controller_class_name = ucfirst($module);

	            if( class_exists($controller_class_name) ){
	                $controller = new $controller_class_name();

	                if( $return ){
	                    return $controller;
	                }
	                $this->$controller_name = $controller;
	                return true;
	            }

	        }



	    }
	    return false;

	}

	function controller(){

        return NULL;
		foreach (glob(BASEPATH.'/controllers/*.php') AS $contr){
			$controller_name = pathinfo($contr,PATHINFO_FILENAME);
			$controller_class_name = ucfirst($controller_name);

			require_once $contr;
			$this->$controller_name = new $controller_class_name();

		}
	}

	static function model($model, $return = false, $db_conn = FALSE){
		$name = ucfirst($model).'_Model';
		$model = strtolower($model);

		$model_file = APPPATH.'models/'.$model.'_model.php';

		if ( file_exists($model_file)) {

			if( !class_exists($name) ){
				require($model_file);
			}

			if( $return ){
				$return = new $name();
				return $return;
			} else {
				self::$name = new $name();
			}

		}
	}

    function module_model($module=null,$model=null, $return = false){
	    if( !$module ) {
	        $module = $this->uri->segment(1);
	    }

	    if( !$model ){
	        $model = $module;
	    }
	    $module = uri2_file_name($module);
	    $name = ucfirst($module).'_'.ucfirst($model).'_Model';
	    $model = strtolower($model);

	    $model_file = MODULEPATH.DS."$module/models/".$model."_model.php";
	    $model_file = realpath($model_file);
	    if( !$model_file ){
	        $model_file = MODULEPATH.DS."$module/models/".$model.".php";
	        $model_file = realpath($model_file);
	    }
// bug($model_file);
	    if ( file_exists($model_file)) {
	        $name2 = str3_function_name($module).'_'.str3_function_name($model)."_Model";
            $name3 = str3_function_name($module).str3_function_name($model)."_Model";
	        if( !class_exists($name) AND !class_exists($name2) AND !class_exists($name3) ){
	            require($model_file);
	        }

	        if( $return ){
	            if ( class_exists($name)){
	                $return = new $name();
	            } elseif ( class_exists($name2) ){
	                $return = new $name2();
	            } elseif ( class_exists($name3) ){
	                $return = new $name3();
	            }
                return $return;
	        } else {
	            self::$name = new $name();
	        }

	    }
	}


	function smarty(){

		$smarty = & load_class('Smarty', 'thirdparty/Smarty-3.1.21',null);
		if ( ! function_exists('check_dir')){
		    $this->load_helper('file');
		}

// 		$smarty->setTemplateDir(APPPATH.'/template');
		check_dir(ROOT.'/tmp/smarty-compile/');
		$smarty->setCompileDir(ROOT.'/tmp/smarty-compile/');
		$smarty->setTemplateDir(BASEPATH.'/views/');
		$smarty->setConfigDir(BASEPATH . '/thirdparty/Smarty-3.1.21/configs');
		$smarty->addPluginsDir(BASEPATH . '/thirdparty/Smarty-3.1.21/ci');

		$this->smarty = $smarty;
	}

	function load_smarty_functions(){


		if( !class_exists('form') ){
			$this->load->library('form');
		}

		$methods = get_class_methods('form' );
		foreach ($methods AS $plugin){

			if( $plugin !='__construct' ){
				$this->smarty->registerPlugin('function', $plugin, 'form::'.$plugin);
			}

		}

		if( !class_exists('formlist') ){
		    $this->load_library('formlist');
		    $methods = get_class_methods('formlist' );
		    if( $methods && !empty($methods)){
		        foreach ($methods AS $plugin){
		            if( $plugin !='__construct' ){
		                $this->smarty->registerPlugin('function', $plugin, 'formlist::'.$plugin);
		            }
		        }
		    }
		}

		include_once BASEPATH.'finput/bootstrap/smarty.php';
		$smartyMethods = get_class_methods('bootrap_smarty' );
		foreach ($smartyMethods AS $plugin){

		    if( $plugin !='__construct' ){
		        $this->smarty->registerPlugin('function', $plugin, 'bootrap_smarty::'.$plugin);
		    }

		}
	}

	function librarie_in_module($filename,$module){
        $lib_file = realpath(MODULEPATH.DS."$module/libraries/$filename.php");
        if( $lib_file ){
            $method_name = strtolower($filename);

            $object_name = str3_function_name($module).str3_function_name($method_name)."Lib";
            if( !class_exists($object_name) ){
                include_once $lib_file;
            }

            if( !method_exists($this, $method_name) AND class_exists($object_name) ){
                $this->$method_name = new $object_name();
            }

//             $this->$obj_name =
        }

	}

	var $module_name = NULL;
	function auto_load_module($module_name=NULL){

        if( is_null($module_name) ){
            $uris = $this->uri->segments;

            if( count($uris) > 0 AND realpath(MODULEPATH.DS.$uris[1]) ){
                if( in_array($uris[1], array('html','fa')) )
                    return;
                $this->module_name = $uris[1];
                $module_name = $uris[1];
            }
        } else {
            $module_name;
        }

        if( strlen($module_name) > 0 ){
            $this->helper_in_module($module_name);
            $this->smarty_in_module($module_name);
            $this->hooks_in_module($module_name);
        }

	}

	public function smarty_in_module($module_name=NULL){

	    if( !realpath(MODULEPATH.DS.$module_name) )
	        return;
	    $module_dir = realpath(MODULEPATH.DS.$module_name).DS;
        $module = pathinfo($module_dir);
        if( strlen($smarty_dir = realpath($module_dir."/smarty")) > 0 ) foreach (glob($smarty_dir."/*.php") AS $lib){
            $file = pathinfo($lib);
            $objec_name = str3_function_name($module['basename']).str3_function_name($file['filename'])."Smarty";
            include_once $lib;
            if( class_exists($objec_name) )foreach (get_class_methods($objec_name) AS $plugin){
    		    if( $plugin !='__construct' AND !array_key_exists($plugin, $this->smarty->registered_plugins['function']) ){
    		        $this->smarty->registerPlugin('function', $plugin, "$objec_name::$plugin");
    		    }
            }
        }
	}

	function helper_in_module($module_name=NULL){

	    if( !realpath(MODULEPATH.DS.$module_name) )
	        return;
// 	    bug($module_name);
	    $module_dir = realpath(MODULEPATH.DS.$module_name).DS;
	    $module = pathinfo($module_dir);
	    if( strlen($smarty_dir = realpath($module_dir."/helpers")) > 0 ) foreach (glob($smarty_dir."/*.php") AS $helper_file){
            include_once $helper_file;
        }
	}

	function hooks_in_module($module_name=NULL){
	    if( !realpath(MODULEPATH.DS.$module_name) )
	        return;
	    $module_dir = realpath(MODULEPATH.DS.$module_name).DS;
	    if( file_exists($module_dir."hooks/".strtolower($module_name)."_hooks.php") ){
	        include_once $module_dir."hooks/".strtolower($module_name)."_hooks.php";
	        if( isset($load_modules) AND is_array($load_modules)) foreach ($load_modules AS $module){
	            $this->auto_load_module($module);
	        }
	    }
	}

	public function load_word(){

	    $load = false;
// 	    if( file_exists($file = realpath(BASEPATH.'thirdparty/PHPWord-develop/src/PHPWord2.php')) ){
// 	        require_once $file;
// 	        $load = true;
// 	    }


//         $PHPWord = new PHPWord();
// 	    $section = $PHPWord->createSection();

// 	    $section->addText('Hello world!');

// 	    $objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
// // 	    $objWriter->save('helloWorld.docx');

// // 	    $objWriter = PHPWord_IOFactory::createWriter( $phpword_object, "Word2007" );
// 	    $objWriter->save( "php://output" );



        if( !defined('DOCX_REPORT_TEMP') ) {
            define('DOCX_REPORT_TEMP', realpath(BASEPATH.'/../report/docx').DS);
        }
	}

	public function view($view, $vars = array(), $return = FALSE,$clear=false) {

		if( is_array($vars) ){
			foreach ($vars AS $var_name=>$var_value){
				$this->smarty->assign($var_name, $var_value);
			}
		}

		if( $return ){
		    $html = $this->smarty->fetch(APPPATH."views/$view.html");
// 		    $html = preg_replace('~>\s+<~', '><', $html);
//             $html = preg_replace('/\s\s+/','', $html);
			return compress($html);
		} else {
			$this->smarty->display(APPPATH."views/$view.html");
		}
	}

    var $page_title = null;
    public function temp_view($view, $vars = array(),$use_theme=true,$module=NULL,$display = true) {
        $data = $this->smarty->getTemplateVars();
	    $data += array('view'=>NULL,'page_title'=>$this->page_title);

	    include_once(BASEPATH.'config/template.php');

	    if( isset($template[$this->template->layout]) ){
	        $css_dir = ROOT.'/assets/css/';
	        $scss_dir = ROOT.'/assets/scss/';

	        foreach ($template[$this->template->layout]['css'] AS $css){
	            $info = pathinfo($css);
                if( !file_exists($css_dir.$css) ){
//                     $scss = $scss_dir.$info['filename'].'.scss';
//                     if( file_exists($scss) ){
//                         bug($scss);
//                         $css = $scss_build->compile($scss);
//                         bug($css); die;
//                     }

                } else {
                    $data['css'][] = AT_ASSEETS."css/$css";
                }
	       }
	       foreach ($template[$this->template->layout]['js'] AS $js){
	           if( file_exists(ROOT.'/assets/js/'.$js) ){
	               $data['js'][] = AT_ASSEETS."js/$js";
	           }
	       }
	    }


	    if( is_array($vars) ){
	        foreach ($vars AS $var_name=>$var_value){
	            $data[$var_name] = $var_value;
	        }
	    }
	    $data['dateformat'] = $this->dateformat;
	    $data['datemonthformat'] = $this->datemonthformat;

        if( !$module && $this->template->module  ){
            $module = $this->template->module;
        }
	    if( trim($module) !='' ){

	        $view_path = MODULEPATH.DS."$module/views/$view.html";
	        $html = $this->smarty->fetch($view_path,$data);

	        if( $use_theme ){
// 	            $data['content'] = $html;
// 	            $html = $this->smarty->fetch(BASEPATH.DS."views/layout/$use_theme.html",$data);
	            if( is_string($use_theme) ){
	                $data['content'] = $html;
	                $html = $this->smarty->fetch(BASEPATH.DS."views/layout/$use_theme.html",$data);

	            }
	        }

	        if($display){
                echo $html;
//                 return true;
	        } else {
	            return $html;
	        }

	    }


	    if( $use_theme ){
	        $this->smarty->display(APPPATH."views/layout/".$this->template->layout.".html",$data);
	    } else {
	        echo $data['view'];
	    }

	}

	function finput_load(){
		require(BASEPATH.'finput/finput.php');
		$finput = new finput();
		foreach (glob(BASEPATH.'finput/*.php') AS $input){
			$input_name = basename($input, ".php");

			if( $input_name!='finput'){
				require($input);
				//$finput::$input_name = $input_name::input();

				//$finput->$input_name = call_user_func($input_name);
			}
		}

		$this->finput = $finput;

	}

	function load_smarty_plugin($lib_name=null){
	    if( !$lib_name ) return;

	    if( !class_exists($lib_name) ){
	        $this->load_library($lib_name);
	    }


	    foreach (get_class_methods($lib_name) AS $plugin){
	        if( $plugin !='__construct' && !isset($this->smarty->registered_plugins['function'][$plugin]) ){
	            $this->smarty->registerPlugin('function', $plugin, "$lib_name::".$plugin);
	        }

	    }
	}
}