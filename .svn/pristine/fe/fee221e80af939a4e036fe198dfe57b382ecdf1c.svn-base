<?php
define('MODULEPATH', dirname(__FILE__).'/ci_module/');
$path_to_root=".";

include_once("includes/session.inc");

$page_security = 'SA_OPEN';
ini_set('xdebug.auto_trace',1);

global $ci;

$uris = $ci->uri->segments;
$call_ci = false;

if( isset($uris[1]) && $uris[1]  ){
    $ci->module = $uris[1];

    if( isset($uris[3]) ){
        $control = module_control_load($uris[2].'/'.$uris[3],$uris[1],false);
    }
    if( count($uris) > 1 AND (!isset($control) || !is_object($control)) ){
        $control = module_control_load($uris[2],$uris[1]);
    }

    $ci->template->module = $ci->module;

    if( function_exists('get_post') ){
        include_once(ROOT . "/includes/ui.inc");
    }


    if( isset($control) && is_object($control) ){
        $call_ci = true;
        $action = null;
        if( isset($uris[3]) ){
            $action = func_name($uris[3]);
        }

        if ( $action && is_callable(array($control, $action)) ) {
            $control->$action();
        } elseif( is_callable(array($control, 'index')) ) {
            $control->index();
        } else {
            $call_ci = false;
        }

    }

}


if ( !$call_ci ){
    if( count($uris) > 1 ){
        redirect(site_url());
        //header('Location: index.php?application=H');

    } else {

        add_access_extensions();

        $app = &$_SESSION["App"];

        if (isset($_GET['application'])){
            $app->selected_application = $_GET['application'];
        } else {
            $app->selected_application = 'H';
//             header('Location: index.php?application=H');
            redirect(site_url().'?application=H');
            //$app->selected_application = 'H';
        }
        $app->display();

    }


}


?>