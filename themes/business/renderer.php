<?php
/**********************************************************************
// Creator: Alastair Robertson
// date_:   2013-01-30
// Title:   Dashboard theme renderer
// Free software under GNU GPL
***********************************************************************/
    class renderer
    {
        function get_icon($category)
        {
            global  $path_to_root, $show_menu_category_icons;

            if ($show_menu_category_icons)
                $img = $category == '' ? 'right.gif' : $category.'.png';
            else
                $img = 'right.gif';
            return "<img src='$path_to_root/themes/".user_theme()."/images/$img' style='vertical-align:middle;' border='0'>&nbsp;&nbsp;";
        }

        function wa_header()
        {

          page(_($help_context = "Main Menu"), false, true);
        }

        function wa_footer()
        {
            end_page(false, true);
        }

        function menu_header($title, $no_menu, $is_index)
        {
                    global $path_to_root, $help_base_url, $db_connections;
                    $pageURL = strtolower($_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);

                    $arr_link = array(
                        'www.a2000projects.com/accountanttoday/',
                        'a2000projects.com/accountanttoday/',
                        'a2000projects.com/accountanttoday/index.php',
                        'www.a2000projects.com/accountanttoday/index.php',

                        'www.a2000projects.com/AccountantToday/',
                        'a2000projects.com/AccountantToday/',
                        'a2000projects.com/AccountantToday/index.php',
                        'www.a2000projects.com/AccountantToday/index.php'

                        );
                    //echo ' <script src="//code.jquery.com/jquery-1.10.2.js"></script>';
                    echo '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
                            <script>
                            $(document).ready(function(){

                                $(".ajaxsubmit").addClass("btn btn-success");
                               $(".navibutton").addClass("btn btn-success");
                            });
                            </script>';
                    if (!$no_menu) {
                //css
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/jquery-ui-1.10.3.custom.min.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/entypo.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/bootstrap.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/neon-core.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/neon-theme.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/neon-forms.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/custom.css'>";
                echo "<link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic'>";
                //jquery
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery-1.11.0.min.js'></script>\n";
           // if (isset($_GET['application']) || in_array($pageURL, $arr_link)) echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/script.js'></script>\n";
                
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/jquery-jvectormap-1.2.2.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/rickshaw.min.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/responsive-tables.css'>";

                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/font-awesome.min.css'>";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/main-gsap.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery-ui-1.10.3.minimal.min.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/bootstrap.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/joinable.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/resizeable.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/neon-api.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery-jvectormap-1.2.2.min.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery-jvectormap-europe-merc-en.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery.sparkline.min.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/d3.v3.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/rickshaw.min.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/raphael-min.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/morris.min.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/toastr.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/neon-chat.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/neon-custom.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/neon-demo.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery.tocify.min.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/responsive-tables.js'></script>\n";

                 echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery.peity.min.js'></script>\n";            
            }
                    echo "<div class='page-container horizontal-menu'>";
                    echo "<header class='navbar navbar-fixed-top'>
                            <div class='navbar-inner'>
                                    <!-- logo -->
                                <div class='navbar-brand'>
                                        <a href='http://a21.sg:9999/at/'>

                                                <img src='$path_to_root/themes/".user_theme()."/images/logo@2x.png' width='120' >
                                        </a>
                                </div>";
                   add_access_extensions();


            if (!$no_menu)
            {
                $applications = $_SESSION['App']->applications;
                $local_path_to_root = $path_to_root;
                //$img = "<img src='$local_path_to_root/themes/dashboard/images/login.gif' width='14' height='14' border='0' alt='"._('Logout')."'>&nbsp;&nbsp;";
                $himg = "<img src='$local_path_to_root/themes/".user_theme()."/images/help.gif' width='14' height='14' border='0' alt='"._('Help')."'>&nbsp;&nbsp;";
                $sel_app = $_SESSION['sel_app'];

                        

                            echo    "<ul class='navbar-nav' >";

                               $arr = array();
                               $arr[1] = '<i class="entypo-gauge"></i>';
                               $arr[2] = '<i class="entypo-layout"></i>';
                               $arr[3] = '<i class="entypo-newspaper"></i>';
                               $arr[4] = '<i class="entypo-doc-text"></i>';
                               $arr[5] = '<i class="entypo-bag"></i>';
                               $arr[6] = '<i class="entypo-newspaper"></i>';
                               $arr[7] = '<i class="entypo-flow-tree"></i>'; 
                               $i = 1;
                  ///////////////menu/////////////////////
                    $arrmenu = array();
                    $arrmenu['Sales Quotation Inquiry'] = 'Sales Quotation Entry';
                    $arrmenu['Sales Quotation Inquirylink'] = 'sales/sales_order_entry.php?NewQuotation=Yes';
                    $arrmenu['Sales Order Inquiry'] = 'Sales Order Entry';
                    $arrmenu['Sales Order Inquirylink'] = 'sales/sales_order_entry.php?NewOrder=Yes';
                    ///purchase////
                    $arrmenu['Purchase Orders Inquiry'] = 'Purchase Order Entry';
                    $arrmenu['Purchase Orders Inquirylink'] = 'purchasing/po_entry_items.php?NewOrder=Yes';
                    $arrmenu['GRN on Purchase Orders'] = 'Outstanding Purchase Orders Maintenance';
                    $arrmenu['GRN on Purchase Orderslink'] = 'purchasing/inquiry/po_search.php?';
					
					$arrmenuroot = array();
					$arrmenuroot['Analysis'] = 'Analysis';
					$arrmenuroot['Setup'] = 'Setup';
					/////////////////////////////////////////             
                foreach($applications as $app)
                {
                                 if($app->id != 'Dashboard')   
								 {
                                    if ($_SESSION["wa_current_user"]->check_application_access($app))
                                    {
                                            if ($app->id == $app->id) $sel_application = $app;
                                            
                                            $acc = access_string($app->name);
											if($arrmenuroot[$acc[0]] == null)
                                            {
                                                echo "<li class='root-level has-sub ".($sel_app == $app->id ? '' : '')."'>";
                                                        echo "<a class='".($sel_app == $app->id ? 'selected' : 'menu_tab')
                                                                ."' href='$local_path_to_root/index.php?application=".$app->id
                                                                ."'$acc[1]>".$arr[$i]."&nbsp;&nbsp;<span>" .$acc[0] . "</span></a>";
                                            }
											else
											{
												echo "<li class='root-level has-sub ".($sel_app == $app->id ? '' : '')."'>";
                                                        echo "<a class='".($sel_app == $app->id ? 'selected' : 'menu_tab')
                                                                ."' href='#'>".$arr[$i]."&nbsp;&nbsp;<span>" .$acc[0] . "</span></a>";
											}
											$i++;
                                    }    
                                        ////////////////////////
                                                 echo "<ul >";
                                                    foreach ($sel_application->modules as $module)
                                                    {

                                                       echo "<li class='has-sub opened'><a href='#'><span>".$module->name."</span></a>";
                                                       echo '<ul>';
                                                        //$first = "";
                                                        $apps = array();
                                                        foreach ($module->lappfunctions as $appfunction)
                                                            $apps[] = $appfunction;
                                                        foreach ($module->rappfunctions as $appfunction)
                                                            $apps[] = $appfunction;
                                                        $application = array();
                                                        foreach ($apps as $application)
                                                        {
                                                            $lnk = access_string($application->label);
                                                            if ($_SESSION["wa_current_user"]->can_access_page($application->access))
                                                            {
                                                                if ($application->label != "")
                                                                {
                                                                    echo "<li><a href='".$path_to_root."/".$application->link."'>".$lnk[0]."</a>";
                                                                    if($arrmenu[$lnk[0]] != null)
                                                                    {
                                                                        echo '<ul><li>';
                                                                        echo "<a href='".$path_to_root."/".$arrmenu[$lnk[0].'link']."'>".$arrmenu[$lnk[0]]."</a>";
                                                                        echo '</li></ul>';
                                                                    }
                                                                    echo "</li>";
                                                                }
                                                            }
                                                        elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items())
                                                            echo "<li><a href='#' class='disabled'>".$lnk[0]."</a></li>";
                                                        }
                                                       echo '</li></ul>'; 
                                                    }
                                                    echo '</ul>'; 
                                       
                                             echo "</li>";
                                }            
                }
        
                echo '</ul>';
               // $first = "id='first'";
                               
                                echo '<ul class="nav navbar-right pull-right">';

                               
                           //preferences
                           echo     '<li  class="dropdown">';
                                           echo " <a title='Preferences' class='shortcut' href='$path_to_root/admin/display_prefs.php?'>" . _(" ") . "<i class='entypo-list'></i></i></a>";
                             echo    '</li>';
                           //change pass
                            // echo  '<li class="sep"></li>';
                                echo     '<li  class="dropdown">';
                                           echo "  <a title='Change password' class='shortcut' href='$path_to_root/admin/change_current_user_password.php?selected_id=" . $_SESSION["wa_current_user"]->username . "'>" . _(" ") . "<i class='entypo-users'></i></a>";
                               echo    '</li>';
                            //logout
                                //echo  '<li class="sep"></li>';
                                echo     '<li  class="dropdown">';
                                           echo "<a class='shortcut' href='$local_path_to_root/access/logout.php?'>" . _("Logout") . "<i class='entypo-logout right'></i></a>";
                               echo    '</li>';
                               echo '<!-- mobile only -->
                                            <li class="visible-xs"> 
                                            
                                                <!-- open/close menu icon (do not remove if you want to enable menu on mobile devices) -->
                                                <div class="horizontal-mobile-menu visible-xs">
                                                    <a href="#" class="with-animation"><!-- add class "with-animation" to support animation -->
                                                        <i class="entypo-menu"></i>
                                                    </a>
                                                </div>
                                                
                                            </li>';
                            echo    '</ul>';
            }

                       // echo "</ul>";
                   echo '</div></header>';
                   //phan chinh
                   echo "<div class='main-content'>";
                     echo "<div class='container'>";  
                        
                        if ($no_menu) {

            }
            elseif ($title && !$is_index)
            {

                            echo "<h2>$title</h2>"

                            .(user_hints() ? "<span id='hints'></span>" : '');
                            echo '<br>';
            }
            
        }



        function menu_footer($no_menu, $is_index)
        {
            global $version, $allow_demo_mode, $app_title, $power_url,
                $power_by, $path_to_root, $Pagehelp, $Ajax;
            include_once($path_to_root . "/includes/date_functions.inc");
            echo "</div>"; // column
            echo "</td></tr><tr><td colspan='2'>";

            
            echo "</td></tr></table></td>\n";
            echo "</table>\n";
            if ($no_menu == false)
            {
                echo "<table align='center' id='web-footer' class='container'>\n";
                                
                                      
                                
                echo "<tr>\n";
                                     
                                        echo "<td align='center' class='footer'><p>";
                                            if ($no_menu == false)
                                            {
                                                if (isset($_SESSION['wa_current_user'])) {
                                                        $phelp = implode('; ', $Pagehelp);
                                                        //echo " " . Today() . " | " . Now() . " - ";
                                                        $Ajax->addUpdate(true, 'hotkeyshelp', $phelp);
                                                        if($phelp != '') echo " ".$phelp." - ";
                                                }
                                            }   
                                           echo "<a target='_blank' href='$power_url'><font color='#ffffff'>Copyright &copy; 2014 by $power_by</font></a>"
                                            . "</p></td>\n";
                echo "</tr>\n";
                //echo "<tr>\n";
                //echo "<td align='center' class='footer'><a target='_blank' href='$power_url'><font color='#666666'>$power_by</font></a></td>\n";
                //echo "</tr>\n";
                if ($allow_demo_mode==true)
                {
                    echo "<tr>\n";
                    echo "</tr>\n";
                }
                echo "</table><br><br>\n";
            }
        }

        function display_applications(&$waapp)
        {
            global $path_to_root, $use_popup_windows;
            include_once("$path_to_root/includes/ui.inc");
            include_once($path_to_root . "/reporting/includes/class.graphic.inc");

            $selected_app = $waapp->get_selected_application();
             //$head_menu = ltrim( $selected_app->name, '&');
            if (!$_SESSION["wa_current_user"]->check_application_access($selected_app))
                return;

            if (method_exists($selected_app, 'render_index'))
            {
                $selected_app->render_index();
                return;
            }
            // first have a look through the directory,
            // and remove old temporary pdfs and pngs
            $dir = company_path(). '/pdf_files';

            if ($d = @opendir($dir)) {
                while (($file = readdir($d)) !== false) {
                    if (!is_file($dir.'/'.$file) || $file == 'index.php') continue;
                // then check to see if this one is too old
                    $ftime = filemtime($dir.'/'.$file);
                 // seems 3 min is enough for any report download, isn't it?
                    if (time()-$ftime > 180){
                        unlink($dir.'/'.$file);
                    }
                }
                closedir($d);
            }
           
    

            $dashboard_app = $waapp->get_application("Dashboard");
            echo '<div id="console" ></div>';

            $userid = $_SESSION["wa_current_user"]->user;
            $sql = "SELECT DISTINCT column_id FROM ".TB_PREF."dashboard_widgets"
                    ." WHERE user_id =".db_escape($userid)
                    ." AND app=".db_escape($selected_app->id)
                    ." ORDER BY column_id";
            $columns=db_query($sql);

            while($column=db_fetch($columns))
              {
                  echo '<div class="column" id="column'.$column['column_id'].'" >';
                  $sql = "SELECT * FROM ".TB_PREF."dashboard_widgets"
                        ." WHERE column_id=".db_escape($column['column_id'])
                        ." AND user_id = ".db_escape($userid)
                        ." AND app=".db_escape($selected_app->id)
                        ." ORDER BY sort_no";
                  $items=db_query($sql);
                  while($item=db_fetch($items))
                  {
                      $widgetData = $dashboard_app->get_widget($item['widget']);
                      echo '
                      <div class="dragbox" id="item'.$item['id'].'">
                          <h2>'.$item['description'].'</h2>
                              <div id="widget_div_'.$item['id'].'" class="dragbox-content" ';
                      if($item['collapsed']==1)
                          echo 'style="display:none;" ';
                      echo '>';
                      if ($widgetData != null) {
                          if ($_SESSION["wa_current_user"]->can_access_page($widgetData->access))
                          {
                              include_once ($path_to_root . $widgetData->path);
                              $className = $widgetData->name;
                              $widgetObject = new $className($item['param']);
                              $widgetObject->render($item['id'],$item['description']);
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
                  //echo '</div></div>';
              }
        }
                
    }

?>