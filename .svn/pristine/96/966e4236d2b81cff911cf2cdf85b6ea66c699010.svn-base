<?php

/**********************************************************************
// Creator: QuanNH
// date_:   151126
***********************************************************************/
class renderer
{

    var $css = array(
        'default.css',
        'popup.css',
        'bootstrap.css',
        'style.css',
        'at.css',
        'ball.css',
        'jquery-ui.css',
        'stylemonthpicker.css'
    )
    // 'bootstrap-select.css'

    ;

    var $js = array(
        'at.js',
        'listview.js',
        'qunit-1.11.0.js',
        'monthpicker.js',
        'Navigation_0003.js'
    )
    ;

    function __construct()
    {
        global $assets_path;
        $this->theme_uri = '//' . $_SERVER['HTTP_HOST'] . '/themes/accountanttoday';
        $this->theme_img_uri = '//' . $_SERVER['HTTP_HOST'] . '/themes/accountanttoday/images';

        if (! class_exists('model')) {
            include ROOT . '/includes/model.php';
        }
        $this->model = new model();

        // add_js_file($assets_path.'/js/jquery-min.1.9.1.js');
        // add_js_file($assets_path.'/js/jquery-ui.js');
        // add_js_file($assets_path.'/js/mask/min.js');

        add_js_file($assets_path . '/bootstrap/bootstrap.min.js');
        add_js_file($assets_path . '/js/opening.js');
        add_js_file($assets_path . '/chosen/chosen.jquery.js');
        add_js_file($assets_path . 'bootstrap/select/bootstrap-select.js');


        foreach ($this->js as $js) {
            add_js_file($this->theme_uri . '/js/' . $js);
        }
        foreach ($this->css as $css) {
            add_css_source($this->theme_uri . '/css/' . $css);
        }
        add_css_source($assets_path . '/chosen/chosen.css');
        add_css_source($assets_path . 'bootstrap/select/bootstrap-select.css');
        add_css_source($assets_path . 'css/layout.css');
        add_css_source($assets_path . 'css/responsive.css');

        add_css_source($assets_path . 'css/demo_fix_100220.css');

        $comp_subdirs = array(
            'images',
            'pdf_files',
            'backup',
            'js_cache',
            'reporting',
            'attachments'
        );
        foreach ($comp_subdirs as $dir_check) {
            check_dir(COMPANY_DIR . "/$dir_check");
        }
    }

    function get_icon($category)
    {
        global $path_to_root, $show_menu_category_icons;
        if ($show_menu_category_icons)
            $img = $category == '' ? 'right.gif' : $category . '.png';
        else
            $img = 'right.gif';
        return "<img src='" . $this->theme_img_uri . "/$img' style='vertical-align:middle;' border='0'>&nbsp;&nbsp;";
    }

    function wa_header()
    {
        page(_($help_context = "Main Menu"), false, true);
    }

    function wa_footer()
    {
        end_page(false, true);
    }

    function menu_header($title, $no_menu, $is_index, $button_reload = false)
    {
        global $path_to_root, $help_base_url, $db_connections, $power_by, $session;
        $system_config = $this->model->get_row("name='coy_logo'", 'sys_prefs');
        $coy_logo = company_logo();
        $logo = null;
        if ($system_config && isset($system_config['value']) && $coy_logo) {
            $logo = '<img src="' . $coy_logo . '" style="height:64px;" >';
        }
        $home = site_url();
        if (! $no_menu) {
            $cur = get_company_Pref('coy_name');
            // echo ' <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">';
            // echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery.js'></script>\n";
            echo "<div class='wrapper'>";
            echo "<div class='menu-header'>
            <!-- logo -->
            <div class='menu-header-auto'>
            <ul>
            <li> <a  class='active' href='$home/admin/change_current_user_password.php?selected_id=" . $_SESSION["wa_current_user"]->username . "'> Account  </a> </li>

            <li> <a href='$home/admin/display_prefs.php?'> Settings </a></li>
            <li> <a href='#'>Help </a> </li>
                </ul>
                </div>
                </div>";
            add_access_extensions();

            echo "<div class='header'>
                <a href='$home'>$logo</a>
                <div class='search-info'>
                <p class='company-name'>$cur</p>
                <div class='search'>
                <input type='text' name='search' value='' placeholder='Search transaction' style='display: none;'/>
                <div class='btn search-btn' >
                <a href='$home/access/logout.php?'>LOGOUT</a>
            </div>
            </div>
            <div class='clear'></div>
            <div class='help'>";

            echo "</div>
            </div>
            </div>";
            echo '<div class="clear"></div>';
            echo '<div class="container">';
        }

        if (! $no_menu) {

            $applications = $_SESSION['App']->applications;
            $local_path_to_root = $path_to_root;
            // $img = "<img src='$local_path_to_root/themes/dashboard/images/login.gif' width='14' height='14' border='0' alt='"._('Logout')."'>&nbsp;&nbsp;";
            $himg = "<img src='$local_path_to_root/themes/" . user_theme() . "/images/help.gif' width='14' height='14' border='0' alt='" . _('Help') . "'>&nbsp;&nbsp;";
            $sel_app = $_SESSION['sel_app'];

            echo '<div class="menu">
        <div id="nav">';
            echo "<ul>";

            $arr = array();
            $arr[1] = "<img src='" . $this->theme_img_uri . "/menuicon1.png' >";
            $arr[2] = "<img src='" . $this->theme_img_uri . "/menuicon_06.png' >";
            $arr[3] = "<img src='" . $this->theme_img_uri . "/menuicon_08.png' >";
            $arr[4] = "<img src='" . $this->theme_img_uri . "/menuicon_10.png' >";
            $arr[5] = "<img src='" . $this->theme_img_uri . "/menuicon_12.png' >";
            $arr[6] = "<img src='" . $this->theme_img_uri . "/menuicon_03.png' >";
            $arr[7] = "<img src='" . $this->theme_img_uri . "/menuicon_15.png' >";
            $mt = "<img src='" . $this->theme_img_uri . "/mt.png' style='position: absolute; right: 10px; top: -6px;'>";
            $i = 0;
            foreach ($applications as $app) {
                if ($app->id != 'Dashboard') {

                    if ($_SESSION["wa_current_user"]->check_application_access($app)) {
                        if ($app->id == $app->id)
                            $sel_application = $app;
                        $acc = access_string($app->name);

                        if (isset($arrmenuroot) && $arrmenuroot[$acc[0]] == null) {
                            echo "<li class='root-level has-sub " . ($sel_app == $app->id ? 'active' : '') . "'>";

                            echo "<a class='dropdownWithHref a" . ($sel_app == $app->id ? 'selected' : 'menu_tab') . "' href='$local_path_to_root/index.php?application=" . $app->id . "'$acc[1]>" . $arr[$i] . "<span>" . $acc[0] . "</span></a>";
                        } else {

                            echo "<li class='root-level has-sub " . ($sel_app == $app->id ? '' : '') . "'>";

                            switch ($app->id) {
                                case 'orders':
                                    $dashboard_uri = 'sales/dashboard';
                                    break;
                                case 'GL':
                                    $dashboard_uri = 'gl/dashboard';
                                    break;
                                case 'stock':
                                    $dashboard_uri = 'products/dashboard';
                                    break;
                                case 'AP':
                                    $dashboard_uri = 'purchases/dashboard';
                                    break;
                                default:
                                    $dashboard_uri = NULL;
                                    break;
                            }

                            echo "<a class='dropdownWithHref a" . ($sel_app == $app->id ? 'selected' : 'menu_tab') . "' href='" . site_url($dashboard_uri) . "'>" . ((isset($arr[$i])) ? $arr[$i] : null) . "<span>" . $acc[0] . "</span></a>";
                        }
                        $i ++;
                    }
                    // //////////////////////
                    echo "<ul class='sub-level' style='width: 170px;'>";
                    foreach ($sel_application->modules as $module) {
                        $apps = array();
                        foreach ($module->lappfunctions as $appfunction)
                            $apps[] = $appfunction;
                        foreach ($module->rappfunctions as $appfunction)
                            $apps[] = $appfunction;
                        $application = array();

                        $module_link = ( $module->link ) ? site_url($module->link) : "#";

                        echo "<li class='seperatorBottom'><a  href='$module_link'><span>" . $module->name . "</span>" .(($apps == null) ? NULL : $mt) . "</a>";

                        if( count($apps) > 0 ){
                            echo '<ul class="sub-level1">';

                            foreach ($apps as $application) {
                                $lnk = access_string($application->label);
                                if ($_SESSION["wa_current_user"]->can_access_page($application->access)) {
                                    if ($application->label != "") {
                                        echo "<li class='seperatorBottom'><a style='cursor: pointer;' href='" . $home . $application->link . "'>" . $lnk[0] . "</a>";
                                        echo "</li>";
                                    }
                                } elseif (! $_SESSION["wa_current_user"]->hide_inaccessible_menu_items())
                                echo "<li class='seperatorBottom'><a href='#' style='cursor: pointer;' class='disabled'>" . $lnk[0] . "</a></li>";
                            }
                            echo '</ul>';
                        }

                        echo '</li>';
                    }
                    echo '</ul>';

                    echo "</li>";

                    if( $dashboard_uri=="products/dashboard"
                        AND
                        (
                            substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.')+1)=="xersolution.com"
                            OR
                            substr($_SERVER['SERVER_NAME'], strpos($_SERVER['SERVER_NAME'], '.')+1)=="xersolution.com"
                        )
                    ){
                        echo '<li class="dropdownWithHref a" ><a target="_blank" href="http://a21.sg:89/Account/Login.aspx?hrm='.md5(date('dmY')).'" ><img src="' . $this->theme_img_uri . '/menuicon_03.png" ><span>HRM</span></a></li>';
                    }
                }
            }

            echo '</ul>';
            echo '</div></div>';
        }

        // echo "</ul>";
        echo '</div>';
        // phan chinh
        echo "<div class='main-content'>";
        echo "<div class='container'>";
        // //////////////////////HOME///////////////////////////////////

        if (isset($_GET['application']) && $_GET['application'] == 'H') {

            $home_shoft = array(
//                 array(
//                     '1',
//                     '#',
//                     'Dash Board',
//                     'An overview of how your business is perfoming'
//                 ),
                array(
                    '2',
                    '/sales/inquiry/sales_orders_view.php?type=32',
                    'Create a Quote',
                    'Send a quotation to your customer'
                ),
                array(
                    '3',
                    '/sales/sales_order_entry.php?NewInvoice=0',
                    'Create an Invoice',
                    'Sell items or service to your customer'
                ),
                array(
                    '4',
                    '/sales/customer_payments.php',
                    'Customer Payment',
                    'Receive payment from your customers'
                ),
                array(
                    '5',
                    '/sales/credit_note_entry.php?NewCredit=Yes',
                    'Create a Credit Note',
                    'Credit your customer for goods returned'
                ),
                array(
                    '6',
                    '/purchasing/po_entry_items.php?NewInvoice=Yes',
                    'Create a Supplier Invoice',
                    'Purchase items from supplier'
                ),
                array(
                    '7',
                    '/purchasing/supplier_payment.php',
                    'Supplier Payment',
                    'Pay your suppliers'
                ),
                array(
                    '8',
                    '/gl/bank_account_reconcile.php',
                    //'Bank Reconcilliation',
                    'Bank Reconciliation',
                    'View and reconcile your bank statement'
                ),
                array(
                    '9',
                    '/gl/gl_bank.php?NewPayment=Yes',
                    'Pay Expenses',
                    'Manually capture expenses into your bank'
                ),
                array(
                    '10',
                    '/gl/inquiry/bank_inquiry.php',
                    'View Bank Transaction',
                    'View payments, receipts and bank tranfer'
                ),
                array(
                    '11',
                    '/reporting/reports_main.php?Class=0&REP_ID=108',
                    'Send Customer Statements',
                    'Email ( print) statements to all your customer'
                ),
                array(
                    '12',
                    '/gl/inquiry/profit_loss.php',
                    'Profit and Loss',
                    'View your Profit and Loss report'
                ),
                array(
                    '13',
                    '/gl/inquiry/balance_sheet.php',
                    'Balance Sheet',
                    'Statement of Assets and Liabilities report'
                ),
                array(
                    '14',
                    '/taxes/gst5.php',
                    'Prepare GST Return',
                    'Tax returns and Tax reporting'
                ),
                array(
                    '15',
                    '/admin/company_preferences.php',
                    'Company Maintenance',
                    'Manage branding, financial years, Tax and so on'
                )
            )
            ;
            // <div class="block-1 block-end">
            // <a class="block-img" href="'.$path_to_root.'/gl/inquiry/profit_loss.php">
            // <img src="'.$path_to_root.'/themes/'.user_theme().'/images/12_hover.png"/>
            // <img src="'.$path_to_root.'/themes/'.user_theme().'/images/12.png"/>
            // </a>
            // <p><b>Profit and Loss</b></p>
            // <div class="block-content"><p>View your Profit and Loss report</p></div>
            // </div>
            $menu = '<div class="add-new"> <div class="h2"> <p>Application Shortcut</p> </div></div>
            <div class="clear"></div>
                <div class="table1">';

            foreach ($home_shoft as $k => $menite) {

                $menu .= '<a href="' . $path_to_root . $menite[1] . '" class="block-1 ' . (($k > 1 && ($k + 1) % 6 == 0) ? 'block-end' : null) . '">

			              	<span class="block-img" >
        			              	<img src="' . $path_to_root . '/themes/' . user_theme() . '/images/' . $menite[0] . '_hover.png"/>
        			              	<img src="' . $path_to_root . '/themes/' . user_theme() . '/images/' . $menite[0] . '.png"/>
        			              	</span>
			              	<p><b>' . $menite[2] . '</b></p>
        			              	<div class="block-content"><p>' . $menite[3] . '</p></div>
        			              	</a>
        			              	';
            }

            $menu .= '<div class="clear"></div>
                  <div class="margin100"></div>
                          </div>';
            echo $menu;
        }
        // ///////////////////end home///////////////////////////////////
        if ($no_menu) {} elseif ($title && ! $is_index) {

            if ($button_reload) {
                echo '<h2 class="add-new" >' . $title . ' - <button title="Refresh" name="page_reload" type="button" class="buttontitle" >Reload data</button></h2>';
            } else {
                echo "<h2 class='add-new'>$title</h2>";
            }

            if (user_hints())
                echo "<span id='hints'></span>";
            echo '<br><div class="clear"></div>';
        }
    }

    function menu_footer($no_menu, $is_index)
    {
        global $version, $allow_demo_mode, $app_title, $power_url, $power_by, $path_to_root, $Pagehelp, $Ajax, $copyright;
        include_once ($path_to_root . "/includes/date_functions.inc");
        echo "</div>"; // column

        if ($no_menu == false) {
            echo "<div class='footer'>";
            if ($no_menu == false) {
                if (isset($_SESSION['wa_current_user'])) {
                    $phelp = implode('; ', $Pagehelp);
                    // echo " " . Today() . " | " . Now() . " - ";
                    $Ajax->addUpdate(true, 'hotkeyshelp', $phelp);
                    if ($phelp != '')
                        echo " " . $phelp;
                }
            }
            echo "<p><a target='_blank' href='$power_url'><font >" . $copyright . "</font></a></p>\n";

            echo "</div>";
            // echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/'></script>\n";
        }
    }

    function display_applications(&$waapp)
    {
        global $path_to_root, $use_popup_windows;
        include_once ("$path_to_root/includes/ui.inc");
        include_once ($path_to_root . "/reporting/includes/class.graphic.inc");

        $selected_app = $waapp->get_selected_application();
        // $head_menu = ltrim( $selected_app->name, '&');
        if (! $_SESSION["wa_current_user"]->check_application_access($selected_app))
            return;

        if (method_exists($selected_app, 'render_index')) {
            $selected_app->render_index();
            return;
        }
        // first have a look through the directory,
        // and remove old temporary pdfs and pngs
        $dir = company_path() . '/pdf_files';

        if ($d = @opendir($dir)) {
            while (($file = readdir($d)) !== false) {
                if (! is_file($dir . '/' . $file) || $file == 'index.php')
                    continue;
                    // then check to see if this one is too old
                $ftime = filemtime($dir . '/' . $file);
                // seems 3 min is enough for any report download, isn't it?
                if (time() - $ftime > 180) {
                    unlink($dir . '/' . $file);
                }
            }
            closedir($d);
        }

        $dashboard_app = $waapp->get_application("Dashboard");
        echo '<div id="console" ></div>';

        $userid = $_SESSION["wa_current_user"]->user;
        $sql = "SELECT DISTINCT column_id FROM " . TB_PREF . "dashboard_widgets" . " WHERE user_id =" . db_escape($userid) . " AND app=" . db_escape($selected_app->id) . " ORDER BY column_id";
        $columns = db_query($sql);

        while ($column = db_fetch($columns)) {
            echo '<div class="column" id="column' . $column['column_id'] . '" >';
            $sql = "SELECT * FROM " . TB_PREF . "dashboard_widgets" . " WHERE column_id=" . db_escape($column['column_id']) . " AND user_id = " . db_escape($userid) . " AND app=" . db_escape($selected_app->id) . " ORDER BY sort_no";
            $items = db_query($sql);
            while ($item = db_fetch($items)) {
                $widgetData = $dashboard_app->get_widget($item['widget']);
                echo '
                      <div class="dragbox" id="item' . $item['id'] . '">
                          <h2>' . $item['description'] . '</h2>
                              <div id="widget_div_' . $item['id'] . '" class="dragbox-content" ';
                if ($item['collapsed'] == 1)
                    echo 'style="display:none;" ';
                echo '>';
                if ($widgetData != null) {
                    if ($_SESSION["wa_current_user"]->can_access_page($widgetData->access)) {
                        include_once ($path_to_root . $widgetData->path);
                        $className = $widgetData->name;
                        $widgetObject = new $className($item['param']);
                        $widgetObject->render($item['id'], $item['description']);
                    } else {
                        echo "<center><br><br><br><b>";
                        echo _("The security settings on your account do not permit you to access this function");
                        echo "</b>";
                        echo "<br><br><br><br></center>";
                    }
                }
                echo '</div></div>';
            }

            echo '</div>';
            // echo '</div></div>';
        }
        echo '<div class="clear"></div>';
    }
}

?>