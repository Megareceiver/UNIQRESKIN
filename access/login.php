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

if (!isset($path_to_root) || isset($_GET['path_to_root']) || isset($_POST['path_to_root']))
	die(_("Restricted access"));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/page/header.inc");

$js = "<script language='JavaScript' type='text/javascript'>
function defaultCompany(){
	document.forms[0].company_login_name.options[".$_SESSION["wa_current_user"]->company."].selected = true;
}
</script>";
add_js_file('login.js');
// Display demo user name and password within login form if "$allow_demo_mode" is true
if ($allow_demo_mode == true){
	    //$demo_text = _("Login as user: demouser and password: password");
} else {
	//$demo_text = _("Please login here");
	if (@$allow_password_reset) {
      $demo_text .= " "._("or")." <a href='$path_to_root/index.php?reset=1'>"._("request new password")."</a>";
    }
}

if (check_faillog()) {
		$blocked_msg = '<span class=redfg>'._('Too many failed login attempts.<br>Please wait a while or try later.').'</span>';

	    $js .= "<script>setTimeout(function() {
	    	document.getElementsByName('SubmitUser')[0].disabled=0;
	    	document.getElementById('log_msg').innerHTML='$demo_text'}, 1000*$login_delay);</script>";
	    $demo_text = $blocked_msg;
}
if (!isset($def_coy))
	$def_coy = 0;

$def_theme = "default";

$login_timeout = $_SESSION["wa_current_user"]->last_act;

$title = $login_timeout ? _('Authorization timeout') : $app_title." - "._("Login");
$encoding = isset($_SESSION['language']->encoding) ? $_SESSION['language']->encoding : "iso-8859-1";
$rtl = isset($_SESSION['language']->dir) ? $_SESSION['language']->dir : "ltr";
$onload = !$login_timeout ? "onload='defaultCompany()'" : "";

$coy_name = null;
$company_info = $ci->db->where_in('name',array('coy_name','coy_logo'))->get('sys_prefs')->result();
if( $company_info ){
    foreach ($company_info AS $info){
        if( $info->name=='coy_logo' ){
            $coy_logo = $info->value;
        } else if ($info->name=='coy_name') {
            $coy_name = $info->value;
        }
    }
}

$coy_logo = company_logo();
include_once($path_to_root . "/themes/$theme/renderer.php");
$rend = new renderer();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo 'Login - '.$power_by?></title>
        <meta content="text/html;charset=iso-8859-1" http-equiv="content-type">
        <meta content="width=device-width,initial-scale=1" name="viewport">
        <link href="login.html" rel="canonical">
        
        <!--link href="<?php echo $rend->theme_uri."/assets/bootstrap/css/bootstrap.min.css";?>" rel="stylesheet" type="text/css">
        <link href="<?php echo $rend->theme_uri."/assets/metronic/css/components.min.css";?>" rel="stylesheet" type="text/css"-->
        <link href="<?php echo $rend->theme_uri."/assets/plugins/font-awesome/css/font-awesome.min.css";?>" rel="stylesheet" type="text/css">
        <link href="<?php echo $rend->theme_uri."/assets/metronic/css/login.css";?>" rel="stylesheet" type="text/css">
        
        <script language="javascript" type="text/javascript" src="<?php echo $rend->theme_uri."/assets/js/jquery-min.1.9.1.js";?>"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
        <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>
    <body class="login">
        <div class="uniq-login-bag"></div>
        <div class="uniq-login-place">
            <div class="uniq-logo">
                <img src="<?php echo $rend->theme_uri."assets/images/uniq-logo-login.png";?>"/>
                <br><br><br>
                <p lang="login-0">- Welcome To -</p>
                <strong><?php echo $coy_name;?></strong>
                <?php if( isset($db_connections[0]['license']) ):?>
                    <p class="company-license" >
                        Your License ID: <span style="font-weight: bold; text-transform: uppercase;"><?php echo $db_connections[0]['license'];?></span>
                    </p>
                <?php endif;?>
            </div>
            <?php start_form(false, false, $_SESSION['timeout']['uri'], "loginform");?>
                     <strong lang="login-1">Login Form</strong>
                    <br><br><br>
                    <div class="uniq-field">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <input type="text" placeholder="Username" class="touch" name="user_name_entry_field"/>
                        <input type='hidden' id=ui_mode name='ui_mode' value="<?php echo $_SESSION["wa_current_user"]->ui_mode;?>" />
                    </div>
                    <div class="uniq-field">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                        <input type="password" placeholder="Password" class="touch" name="password"/>
                    </div>
                    <!-- <div class="form-group">
                        <div class="col-md-6">
                          <div class="g-recaptcha" data-sitekey="6LfjHDYUAAAAACB4XaK_8o5hcTYVdRREw83UXwEu"></div>
                    </div> -->
                    <div class="uniq-field button">
                        <input type="submit" value="Login Now" lang="btnlog-1">
                    </div>
                    <br>
                    <p lang="login-2">Forgot Password | Help ?</p>

                    <?php if (isset($_COOKIE['loginFalse'])):
                        unset($_COOKIE['loginFalse']);
                        setcookie('loginFalse', '', time() - 3600);
                    ?>
                        <div class="alert alert-danger">
                            <button class="close" data-close="alert"></button>
                            <span> The user and password combination is not valid</span>
                        </div>
                    <?php endif;?>

                    <input type="hidden" name="company_login_name" value="0" />
                <?php end_form(1)?>
           </div>
           
           <script type="text/javascript">
           window.onload = function () { form_handle() }
           function form_handle(){
               console.log('active');
                $('.touch').on('focus', function( ){
                   $('.uniq-login-bag').addClass('grey');
                });
                $('.touch').on('blur', function( ){
                    $('.uniq-login-bag').removeClass('grey');;
                });
            };

            

           
            $('[lang="btnlog-1"]').val('Login');

            var lang = [{ 
                head : [
                "Welcome to",
                "LOGIN PAGE",
                "Forget password | help?"
                ]}
            ];
            console.log(lang[0].head.length);
            for (var a = 0; a < lang[0].head.length; a++){
                $('[lang="login-'+a+'"]').html(lang[0].head[a]);
            }
        </script>
    </body>
</html>
