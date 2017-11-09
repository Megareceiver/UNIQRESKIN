<?php
/**********************************************************************
// Creator: Alastair Robertson
// date_:   2013-01-30
// Title:   Dashboard theme renderer
// Free software under GNU GPL
***********************************************************************/
class renderer {
	function __construct(){
		if( !class_exists('model') ){
			include ROOT.'/includes/model.php';
		}
		$this->model = new model();

	}
	function get_icon($category){
		global  $path_to_root, $show_menu_category_icons;
		if ($show_menu_category_icons)
			$img = $category == '' ? 'right.gif' : $category.'.png';
		else
        	$img = 'right.gif';
		return "<img src='$path_to_root/themes/".user_theme()."/images/$img' style='vertical-align:middle;' border='0'>&nbsp;&nbsp;";
	}

    function wa_header(){
    	page(_($help_context = "Main Menu"), false, true);
	}

	function wa_footer(){
		end_page(false, true);
	}

    function menu_header($title, $no_menu, $is_index,$button_reload=false){
    	global $path_to_root, $help_base_url, $db_connections,$power_by,$session;
    	//$logo = '<img src="'.$path_to_root.'/themes/'.user_theme().'/images/logo.png" >';
    	$system_config = $this->model->get_row("name='coy_logo'",'sys_prefs');
    	$coy_logo = company_logo();
    	if( $system_config && isset($system_config['value']) && $coy_logo ){

    		$logo = '<img src="'.$coy_logo.'" style="height:64px;" >';
    	}
//     	bug($system_config);

    	if (!$no_menu) {
                //css
                      $cur = get_company_Pref('coy_name');
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/style.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/at.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/bootstrap.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/themes/".user_theme()."/css/ball.css'>";
                echo "<link rel='stylesheet' href='$path_to_root/js/chosen/chosen.css'>";


                echo "<script type='text/javascript' src='$path_to_root/js/bootstrap/bootstrap.min.js'></script>";

                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/at.js'></script>\n";
                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/listview.js'></script>";
                echo "<script type='text/javascript' src='$path_to_root/js/chosen/chosen.jquery.js'></script>";
                echo "<script type='text/javascript' src='$path_to_root/js/opening.js'></script>";

                echo "<script type='text/javascript' src='$path_to_root/js/bootstrap/select/bootstrap-select.js'></script>";
//                 echo ' <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">';
                echo "<link rel='stylesheet' href='$path_to_root/js/bootstrap/select/bootstrap-select.css'>";


                 //echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/jquery.js'></script>\n";
                 echo "<div class='wrapper'>";
                        echo "<div class='menu-header'>
                                        <!-- logo -->
                                    <div class='menu-header-auto'>
                                        <ul>
                                            <li> <a  class='active' href='$path_to_root/admin/change_current_user_password.php?selected_id=" . $_SESSION["wa_current_user"]->username . "'> Account  </a> </li>

                                            <li> <a href='$path_to_root/admin/display_prefs.php?'> Settings </a></li>
                                            <li> <a href='#'>Help </a> </li>
                                        </ul>
                                    </div>
                                </div>";
                   add_access_extensions();

                   echo "<div class='header'>
                           <a href='$path_to_root'>$logo</a>
                            <div class='search-info'>
                                <p class='company-name'>$cur</p>
                                <div class='search'>
                                    <input type='text' name='search' value='' placeholder='Search transaction'/>
                                    <div class='btn search-btn' style='margin-top: -10px; position: relative; padding-right: 0;' >
                                        <a href='$path_to_root/access/logout.php?'>Log out</a>
                                    </div>
                                </div>
                                    <div class='clear'></div>
                                <div class='help'>";

                        echo   "</div>
                            </div>
                        </div>";
                    echo '<div class="clear"></div>';
                    echo '<div class="container">';
            }


            if (!$no_menu) {
                $applications = $_SESSION['App']->applications;
                $local_path_to_root = $path_to_root;
                //$img = "<img src='$local_path_to_root/themes/dashboard/images/login.gif' width='14' height='14' border='0' alt='"._('Logout')."'>&nbsp;&nbsp;";
                $himg = "<img src='$local_path_to_root/themes/".user_theme()."/images/help.gif' width='14' height='14' border='0' alt='"._('Help')."'>&nbsp;&nbsp;";
                $sel_app = $_SESSION['sel_app'];


                            echo '<div class="menu">
                                    <div id="nav">';
                            echo    "<ul>";

                               $arr = array();
                               $arr[1] = "<img src='$path_to_root/themes/".user_theme()."/images/menuicon1.png' >";
                               $arr[2] = "<img src='$path_to_root/themes/".user_theme()."/images/menuicon_06.png' >";
                               $arr[3] = "<img src='$path_to_root/themes/".user_theme()."/images/menuicon_08.png' >";
                               $arr[4] = "<img src='$path_to_root/themes/".user_theme()."/images/menuicon_10.png' >";
                               $arr[5] = "<img src='$path_to_root/themes/".user_theme()."/images/menuicon_12.png' >";
                               $arr[6] = "<img src='$path_to_root/themes/".user_theme()."/images/menuicon_03.png' >";
                               $arr[7] = "<img src='$path_to_root/themes/".user_theme()."/images/menuicon_15.png' >";
                                $mt = "<img src='$path_to_root/themes/".user_theme()."/images/mt.png' style='position: absolute; right: 10px; top: 0;'>";
                               $i = 0;
                foreach($applications as $app){
                	if($app->id != 'Dashboard'){
                		if ($_SESSION["wa_current_user"]->check_application_access($app)) {
                			if ($app->id == $app->id) $sel_application = $app;
                			$acc = access_string($app->name);
                			if(isset($arrmenuroot) && $arrmenuroot[$acc[0]] == null){
                				echo "<li class='root-level has-sub ".($sel_app == $app->id ? 'active' : '')."'>";
                                echo "<a class='dropdownWithHref a".($sel_app == $app->id ? 'selected' : 'menu_tab')
                                	."' href='$local_path_to_root/index.php?application=".$app->id
                                    ."'$acc[1]>".$arr[$i]."<span>" .$acc[0] . "</span></a>";
							} else {
								echo "<li class='root-level has-sub ".($sel_app == $app->id ? '' : '')."'>";
                                echo "<a class='dropdownWithHref a".($sel_app == $app->id ? 'selected' : 'menu_tab')
                                	."' href='#'>".( (isset($arr[$i]))?$arr[$i]:null)."<span>" .$acc[0] . "</span></a>";
							}
							$i++;
						}
                                        ////////////////////////
                        echo "<ul class='sub-level' style='width: 170px;'>";
                        foreach ($sel_application->modules as $module)
                                                    {
                                                       $apps = array();
                                                        foreach ($module->lappfunctions as $appfunction)
                                                            $apps[] = $appfunction;
                                                        foreach ($module->rappfunctions as $appfunction)
                                                            $apps[] = $appfunction;
                                                        $application = array();

                                                      if($acc[0] == "Accountant's Area" && $module->name == 'Process Journal Entries')  echo "<li class='seperatorBottom'><a  href='".$path_to_root."/gl/gl_journal.php?NewJournal=Yes'><span>".$module->name."</span></a>";
                                                      else
                                                      {
                                                        if($apps == null) echo "<li class='seperatorBottom'><a  href='#'><span>".$module->name."</span></a>";
                                                        else echo "<li class='seperatorBottom'><a  href='#'><span>".$module->name."</span>".$mt."</a>";
                                                      }
                                                       echo '<ul class="sub-level1">';
                                                        //$first = "";

                                                        foreach ($apps as $application)
                                                        {
                                                            $lnk = access_string($application->label);
                                                            if ($_SESSION["wa_current_user"]->can_access_page($application->access))
                                                            {
                                                                if ($application->label != "")
                                                                {
                                                                   echo "<li class='seperatorBottom'><a style='cursor: pointer;' href='".$path_to_root."/".$application->link."'>".$lnk[0]."</a>";
                                                                    // if($arrmenu[$lnk[0]] != null)
                                                                    // {
                                                                    //     echo '<ul><li>';
                                                                    //     echo "<a href='".$path_to_root."/".$arrmenu[$lnk[0].'link']."'>".$arrmenu[$lnk[0]]."</a>";
                                                                    //     echo '</li></ul>';
                                                                    // }
                                                                   echo "</li>";
                                                                }
                                                            }
                                                        elseif (!$_SESSION["wa_current_user"]->hide_inaccessible_menu_items())
                                                           echo "<li class='seperatorBottom'><a href='#' style='cursor: pointer;' class='disabled'>".$lnk[0]."</a></li>";
                                                        }
                                                         echo '</ul>';
                                                       echo '</li>';

                                                    }
                                                    echo '</ul>';

                                             echo "</li>";
                                }
                }

                            echo '</ul>';
                    echo '</div></div>';
            }

                       // echo "</ul>";
                   echo '</div>';
                   //phan chinh
                   echo "<div class='main-content'>";
                     echo "<div class='container'>";
                         ////////////////////////HOME///////////////////////////////////

              if( isset($_GET['application']) && $_GET['application'] == 'H'){

              	$home_shoft = array(
              			array('1','#','Dash Board','An overview of how your business is perfoming'),
              			array('2','/sales/inquiry/sales_orders_view.php?type=32',
              					'Create a Quote','Send a quotation to your customer'),
              			array('3','/sales/sales_order_entry.php?NewInvoice=0',
              					'Create an Invoice','Sell items or service to your customer'),
              			array('4','/sales/customer_payments.php',
              					'Customer Payment','Receipt money from your customer'),
              			array('5','/sales/credit_note_entry.php?NewCredit=Yes',
              					'Create a Credit Note','Credit your customer for goods returned'),
              			array('6','/purchasing/po_entry_items.php?NewInvoice=Yes',
              					'Create a Supplier Invoice','Purchase items from supplier'),
              			array('7','/purchasing/supplier_payment.php',
              					'Supplier Payment','Pay you supplier'),
              			array('8','/gl/bank_account_reconcile.php',
              					'Bank Reconcilliation','Import and view bank statement transaction'),
              			array('9','/gl/gl_bank.php?NewPayment=Yes',
              					'Pay Expenses','Manually capture expenses into your bank'),
              			array('10','/gl/inquiry/bank_inquiry.php',
              					'View Bank Transaction','View payments, receipts and bank tranfer'),
              			array('11','/reporting/reports_main.php?Class=0&REP_ID=108',
              					'Send Customer Statements','Email ( print) statements to all your customer'),
              			array('12','/gl/inquiry/profit_loss.php',
              					'Profit and Loss','View your Profit and Loss report'),
              			array('13','/gl/inquiry/balance_sheet.php',
              					'Balance Sheet','Statement of Assets and Liabilities report'),
              			array('14','/gstform/gstform.php',
              					'Prepare GST Return','Tax returns and Tax reporting'),
              			array('15','/admin/company_preferences.php',
              					'Setting Company','Manage branding, financial years, Tax and so on'),

              			);
//               	<div class="block-1 block-end">
//               	<a class="block-img" href="'.$path_to_root.'/gl/inquiry/profit_loss.php">
//               	<img src="'.$path_to_root.'/themes/'.user_theme().'/images/12_hover.png"/>
//               	<img src="'.$path_to_root.'/themes/'.user_theme().'/images/12.png"/>
//               	</a>
//               	<p><b>Profit and Loss</b></p>
//               	<div class="block-content"><p>View your Profit and Loss report</p></div>
//               	</div>
                $menu = '<div class="add-new"> <div class="h2"> <p>Application Shortcut</p> </div></div>
                  <div class="clear"></div>
                  <div class="table1">';

                foreach ($home_shoft AS $k=>$menite){

                	$menu.='<a href="'.$path_to_root.$menite[1].'" class="block-1 '.( ($k> 1 && ($k+1)%6==0 ) ? 'block-end':null ).'">

			              	<span class="block-img" >
			              	<img src="'.$path_to_root.'/themes/'.user_theme().'/images/'.$menite[0].'_hover.png"/>
			              	<img src="'.$path_to_root.'/themes/'.user_theme().'/images/'.$menite[0].'.png"/>
			              	</span>
			              	<p><b>'.$menite[2].'</b></p>
			              	<div class="block-content"><p>'.$menite[3].'</p></div>
			              	</a>
			              	';
                }

				$menu.='<div class="clear"></div>
                  <div class="margin100"></div>
                  </div>';
				echo $menu;
              }
            /////////////////////end home///////////////////////////////////
            if ($no_menu) {

            } elseif ($title && !$is_index) {

                if( $button_reload ){
                    echo '<h2 class="add-new" >'.$title.' - <button title="Refresh" name="page_reload" type="button" class="buttontitle" >Reload data</button></h2>';
                } else {
                    echo "<h2 class='add-new'>$title</h2>";
                }

                if (user_hints())
                    echo  "<span id='hints'></span>";
                echo '<br><div class="clear"></div>';
            }



        }



        function menu_footer($no_menu, $is_index){

            global $version, $allow_demo_mode, $app_title, $power_url, $power_by, $path_to_root, $Pagehelp, $Ajax, $copyright;
            include_once($path_to_root . "/includes/date_functions.inc");
            echo "</div>"; // column

            if ($no_menu == false)
            {
                echo "<div class='footer'>";
                if ($no_menu == false)
                                {
                                    if (isset($_SESSION['wa_current_user'])) {
                                            $phelp = implode('; ', $Pagehelp);
                                            //echo " " . Today() . " | " . Now() . " - ";
                                            $Ajax->addUpdate(true, 'hotkeyshelp', $phelp);
                                            if($phelp != '') echo " ".$phelp;
                                    }
                                }
                       echo "<p><a target='_blank' href='$power_url'><font >".$copyright."</font></a></p>\n";

                echo "</div>";
                                echo "<script type='text/javascript' src='$path_to_root/themes/".user_theme()."/js/Navigation_0003.js'></script>\n";
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
              echo '<div class="clear"></div>';
        }

    }

?>