<?php
class FaPermissionSmarty{
    function __construct(){

    }

    static function application_link($params,$template){
        $app = (isset($params['app'])) ? $params['app'] : NULL;

        if( empty($app) OR !is_object($params['app']) ){
            return NULL;
        }

        $outer = (isset($params['outer'])) ? $params['outer'] : NULL;

        switch (get_class($app)){
            case 'app_function':
                $lnk = access_string($app->label);
                $label = $lnk[0];
                if( isset($app->fa_icon) AND strlen($app->fa_icon) > 0 ){
                    $label = '<i class="'.$app->fa_icon.'"></i> '. $label;
                }

                if ($_SESSION["wa_current_user"]->can_access_page($app->access) AND $app->label != "") {
                    $html = anchor($app->link,$label,'class="nav-link"');
//                     $html = anchor(NULL,$lnk[0],'class="nav-link "');
                } elseif (! $_SESSION["wa_current_user"]->hide_inaccessible_menu_items()){
                    $html = $label;
                } else {
                    $html = NULL;
                }
                break;
            default:
                $lnk = access_string($app->name);
                $label = $lnk[0];
                if( isset($app->icon) AND strlen($app->icon) > 0 ){
                    $label = '<i class="'.$app->icon.'"></i> '. $label;
                }
//                 $html = anchor('index.php?application='.$app->id,$label);
                $uri = 'index.php?application='.$app->id;
                if(  strtolower($lnk[0]) != "home" ){
                    $uri = NULL;
                }
                $menu_attribute = array('class'=>'nav-link');
                if( !$app->enabled ){
                    $menu_attribute['class'] .= ' disabled';
                }
                $html = anchor($uri,$label,$menu_attribute);
                break;


        }

        if( strlen($html) > 0 ){
            return (strlen($outer) > 1 ) ? "<$outer>$html</$outer>" : $html;
        }

    }

    static function application_menu($params){
        $module = (isset($params['module'])) ? $params['module'] : NULL;

        if( empty($module) OR !is_object($module) OR get_class($module) != 'module' ){
            return NULL;
        }

        $label = $module->name;

        if( count($module->lappfunctions) > 0 OR count($module->rappfunctions) > 0 ){
            $label .= '<span class="arrow">';
        }
        if( isset($module->icon) AND strlen($module->icon) > 0 ){
            $label = '<i class="'.$module->icon.'"></i> '. $label;
        }

        $link = $module->link;
        if( strtolower($label) != 'documents upload' ){
//             $link = NULL;
        }

        return anchor($link,$label,'class=" nav-link nav-toggle" ');
    }
}