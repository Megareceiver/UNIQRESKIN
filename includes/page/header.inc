<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
/*
 * If no context is set current page/menu screen is selected.
 */
if (! function_exists('help_url')) {

    function help_url($context = null)
    {
        global $help_base_url, $help_context, $old_style_help;

        $country = $_SESSION['language']->code;
        $clean = 0;

        if ($context != null) {
            $help_page_url = $context;
        } elseif (isset($help_context)) {
            $help_page_url = $help_context;
        } else // main menu
{
            $app = $_SESSION['sel_app'];
            $help_page_url = $_SESSION['App']->applications[$app]->help_context;
            $clean = 1;
        }

        if (@$old_style_help)
            $help_page_url = _($help_page_url);

        if ($clean)
            $help_page_url = access_string($help_page_url, true);

        return $help_base_url . urlencode(strtr(ucwords($help_page_url), array(
            ' ' => '',
            '/' => '',
            '&' => 'And'
        ))) . '&ctxhelp=1&lang=' . $country;
    }
}

if (! function_exists('send_scripts')) {

    function send_scripts()
    {
        global $js_static, $js_path, $js_userlib, $path_to_root, $go_debug;

        $js = '';
        foreach ($js_static as $jsfile) {
            if (filter_var($jsfile, FILTER_VALIDATE_URL) || substr($jsfile, 0, 2) == '//') {
                $js .= '<script language="javascript" type="text/javascript" src="' . $jsfile . '"></script>' . "\n";
            } else {
                $js .= '<script language="javascript" type="text/javascript" src="' . $js_path . $jsfile . '"></script>' . "\n";
            }
            // $fpath = company_path().'/js_cache/'.$jsfile;

            // if (!file_exists($fpath) || $go_debug)
            // cache_js_file($fpath, file_get_contents($js_path.$jsfile));

            // $js .= '<script language="javascript" type="text/javascript" src="'.str_replace(COMPANY_DIR, COMPANY_ASSETS, $fpath) . '"></script>';
            // $js .= '<script language="javascript" type="text/javascript" src="'.$js_path.DS.$jsfile. '"></script>';
        }

        foreach ($js_userlib as $jsfile) {
            $js .= '<script language="javascript" type="text/javascript" src="' . str_replace(COMPANY_DIR, COMPANY_ASSETS, $jsfile) . '"></script>';
        }

        echo $js;
    }
}

if (! function_exists('send_styles')) {

    function send_styles()
    {
        global $css_lib, $css_static, $assets_path;
        $css_files = NULL;

        foreach ($css_static as $css) {
            if (filter_var($css, FILTER_VALIDATE_URL) || substr($css, 0, 2) == '//') {
                $css_files .= '<link href="' . $css . '" rel="stylesheet" type="text/css">' . "\n";
            } else {
                $css_files .= '<link href="' . $assets_path . $css . '" rel="stylesheet" type="text/css">' . "\n";
            }
        }
        foreach ($css_lib as $css_file) {
            if (filter_var($css_file, FILTER_VALIDATE_URL) || substr($css_file, 0, 2) == '//') {
                $css_files .= '<link href="' . $css_file . '" rel="stylesheet" type="text/css">' . "\n";
            } else {
                $css_files .= '<link href="' . $assets_path . '/css/' . $css_file . '" rel="stylesheet" type="text/css">' . "\n";
            }
        }

        global $ci;

        if (! empty($ci->page->css))
            foreach ($ci->page->css as $css) {
                // if( file_exists($resource_dir."/css/$css") ){
                // $file = AT_ASSEETS."/css/$css";
                // } else if (file_exists($resource_dir.'/'.$css)) {
                // $file = AT_ASSEETS.'/'.$css;
                // } else {
                global $theme;
                $file = AT_ASSEETS . "$theme/css/$css";
                // }
                // /$file = $assets_path.substr($file, 1);
                $css_files .= '<link href="' . $file . '" rel="stylesheet" type="text/css">' . "\n";
            }

        echo $css_files . "\n";
    }
}
if (! function_exists('send_document_ready_js')) {
    function send_document_ready_js()
    {
        global $js_document_ready;
        $js_script = NULL;
        if (! empty($js_document_ready))
            foreach ($js_document_ready as $js) {
                $js_script .= "$js\n";
            }
        echo "<script type=\"text/javascript\"><!-- \n $( document ).ready(function() { \n$js_script  }); \n--></script>";
    }
}

if (! function_exists('page_header')) {

    function page_header($title, $no_menu = false, $is_index = false, $onload = "", $js = "", $css = '', $button_reload = false)
    {
//         add_user_js_data();
        // var user ={"pdec":' . user_price_dec() . ', "ts": ","};
        // titles and screen header
        global $path_to_root, $def_app, $use_popup_windows, $help_base_url, $db_connections, $dateseps;

        if (in_ajax())
            return; // just for speed up

        // $jsext = substr_replace($_SERVER['PHP_SELF'], '.js', -4);

        // if (file_exists($_SERVER['DOCUMENT_ROOT'].$jsext))
                        // add_js_ufile($jsext);

        $theme = user_theme();

        if ($help_base_url != null && $use_popup_windows && $js == '') {
            if (! function_exists('get_js_open_window')) {
                include_once ($path_to_root . '/includes/ui/ui_view.inc');
            }
            add_js_source(get_js_open_window(900, 500));
        }

        if ($js != '')
            add_js_source($js);

        if (! isset($no_menu)) {
            $no_menu = NULL;
        }
        if (isset($_SESSION["App"]) && is_object($_SESSION["App"]) && isset($_SESSION["App"]->selected_application) && $_SESSION["App"]->selected_application != "")
            $sel_app = $_SESSION["App"]->selected_application;
        elseif (isset($_SESSION["sel_app"]) && $_SESSION["sel_app"] != "")
            $sel_app = $_SESSION["sel_app"];
        else
            $sel_app = user_startup_tab();
        $_SESSION["sel_app"] = $sel_app;

        // When startup tab for current user was set to already
        // removed/inactivated extension module select Sales tab as default.
        if (isset($_SESSION["App"]) && is_object($_SESSION["App"]))
            $_SESSION["App"]->selected_application = isset($_SESSION["App"]->applications[$sel_app]) ? $sel_app : 'orders';

        $page_header_args = func_get_args();
        if (isset($db_connections)) // skip before installation
            hook_invoke_all('pre_header', $page_header_args);

        $encoding = $_SESSION['language']->encoding;

        if (! headers_sent()) {
            header("Content-type: text/html; charset='$encoding'");
        }

        include_once ($path_to_root . "/themes/$theme/renderer.php");
        $rend = new renderer();

        echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
        echo "<html dir='" . $_SESSION['language']->dir . "' >\n";
        echo "<head profile=\"http://www.w3.org/2005/10/profile\"><title>$title</title>";
        echo "<meta http-equiv='Content-type' content='text/html; charset=$encoding'>";
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        echo '<meta content="width=device-width, initial-scale=1" name="viewport" />';

        // echo "<link href='$path_to_root/themes/default/images/favicon.ico' rel='icon' type='image/x-icon'> \n";

//         if ($css) {
//             echo '<link href="' . $css . '" rel="stylesheet" type="text/css">';
//         }

        send_styles();

        global $thoseps, $decseps, $date_system, $dateseps;

        $ts = $thoseps[user_tho_sep()];
        $ds = $decseps[user_dec_sep()];

        $js_user_value = "\n"
            . "var user = {\n"
            // 	  . "theme: '". $path_to_root . '/themes/'. user_theme().'/'."',\n"
        . "loadtxt: '"._('Requesting data...')."',\n"
            . "date: '".Today()."',\n"	// server date
            . "datesys: ".$date_system.",\n"
                . "datefmt: ".user_date_format().",\n"
                    . "datesep: '".$dateseps[user_date_sep()]."',\n"
                        . "ts: '$ts',\n"
                        . "ds: '$ds',\n"
        	  . "pdec : " . user_price_dec() . "}\n";

        echo '<script type="text/javascript">var date_format =\''.get_instance()->dateformat.'\';var date_separator = "' . $dateseps[user_date_sep()] . '"; '.$js_user_value.'</script>';
        send_scripts();
        send_document_ready_js();

        echo "</head> \n";
        if ($onload == ""){
//             echo "<body class='page-body  loaded home'>";
            echo '<body class="page-container-bg-solid">';

        } else
            echo "<body onload=\"$onload\">";

        $rend->menu_header($title, $no_menu, $is_index, $button_reload);
        error_box();
    }
}

?>