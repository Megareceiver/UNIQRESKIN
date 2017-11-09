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
function defaultCompany()
{
	document.forms[0].company_login_name.options[".$_SESSION["wa_current_user"]->company."].selected = true;
}
</script>";
	add_js_file('login.js');
	// Display demo user name and password within login form if "$allow_demo_mode" is true
	if ($allow_demo_mode == true)
	{
	    //$demo_text = _("Login as user: demouser and password: password");
	}
	else
	{
		//$demo_text = _("Please login here");
    if (@$allow_password_reset) {
      $demo_text .= " "._("or")." <a href='$path_to_root/index.php?reset=1'>"._("request new password")."</a>";
    }
	}

	if (check_faillog())
	{
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

	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n";
	echo "<html dir='$rtl' >\n";
	echo "<head profile=\"http://www.w3.org/2005/10/profile\"><title>$title</title>\n";
   	echo "<meta http-equiv='Content-type' content='text/html; charset=$encoding' />\n";
	echo "<link href='$path_to_root/themes/$def_theme/login.css' rel='stylesheet' type='text/css'> \n";
 	echo "<link href='$path_to_root/themes/default/images/favicon.ico' rel='icon' type='image/x-icon'> \n";
	send_scripts();
	if (!$login_timeout)
	{
		echo $js;
	}
	echo "</head>\n";

	echo "<body id='loginscreen' class='login-page' $onload>\n";

	//echo "<table class='titletext'><tr><td>$title</td></tr></table>\n";
	
	div_start('_page_body');
	br();br();
        
	start_form(false, false, $_SESSION['timeout']['uri'], "loginform");
	start_table(false, "class='login'");
	start_row();
	
// 	bug($db_connections);die('quannh');
	//connection to the database
	$coy_name = null;
	$coy_logo = null;
	$conn = new mysqli($db_connections[0]['host'], $db_connections[0]['dbuser'], $db_connections[0]['dbpassword'], $db_connections[0]['dbname']);
	// Check connection
	if ( !$conn->connect_error) {
		$result = $conn->query("SELECT * FROM ".$db_connections[0]['tbpref']."sys_prefs");
		if ($result->num_rows > 0) {
			// output data of each row
			while($row = $result->fetch_assoc()) {
				if( $row["name"]=='coy_name' ){
					$coy_name = $row["value"];
				}else if($row["name"]=='coy_logo'){
					$coy_logo = $row["value"];
				}
				//echo "id: " . $row["id"]. " - Name: " . $row["name"]. " " . $row["value"]. "<br>";
			}
		} 
		
	}
	$conn->close();
	if( !$coy_logo ){
		$coy_logo = "$path_to_root/themes/$def_theme/images/logo_frontaccounting.png";
	} else {
		$coy_logo = "$path_to_root/company/0/images/$coy_logo";
	}
	//bug($result);die('quannh');
	
	
	
	
	
// 	$dbhandle = mysql_connect($db_connections[0]['host'], $db_connections[0]['dbuser'], $db_connections[0]['dbpassword']) or die("Unable to connect to Database");
// 	$selected = mysql_select_db($db_connections[0]['dbname'],$dbhandle) or die("Unable to connect to Database Name");
	
// 	//execute the SQL query and return records
// 	$result = mysql_query("SELECT * FROM ".$db_connections[0]['tbpref']."sys_prefs");
// 	while ($row = mysql_fetch_array($result)) {
// 		bug($row);
// // 	   echo "ID:".$row['id']."Year: ". //display the results
	   
// 	}
	
	//close the connection
	//mysql_close($dbhandle);
	
	
	
	echo "<td align='center' colspan=2>";
	
	if (!$login_timeout) { // FA logo
    	echo "<a target='_blank' href='$power_url'><img src='$coy_logo' alt='FrontAccounting' height='50' onload='fixPNG(this)' border='0' /></a>";
    	if( $coy_name ){
    		echo '<p class="company-name" >Welcome, '.$coy_name.'!</p>';
    	}
    	
	} else { 
		echo "<font size=5>"._('Authorization timeout')."</font>";
	} 
	echo "</td>\n";
	end_row();
	if (isset($_COOKIE['loginFalse'])) {
		unset($_COOKIE['loginFalse']);
		echo "<tr><td align='center' colspan=2 style='color:red;'>The user and password combination is not valid for the system.</td></tr>";
	}
	echo "<input type='hidden' id=ui_mode name='ui_mode' value='".$_SESSION["wa_current_user"]->ui_mode."' />\n";
	if (!$login_timeout)
	//	table_section_title(_("Version")." $version   Build $build_version - "._("Login"));
	$value = $login_timeout ? $_SESSION['wa_current_user']->loginname : ($allow_demo_mode ? "demouser":"");

	text_row(_("Username"), "user_name_entry_field", $value, 20, 30);

	$password = $allow_demo_mode ? "password":"";

	password_row(_("Password"), 'password', $password);

	
	if ($login_timeout) {
		hidden('company_login_name', $_SESSION["wa_current_user"]->company);
	} else {
		if (isset($_SESSION['wa_current_user']->company))
			$coy =  $_SESSION['wa_current_user']->company;
		else
			$coy = $def_coy;
		if (!@$text_company_selection) {
			echo "<tr style='display:none'><td>"._("Company")."</td><td><select name='company_login_name'>\n";
			for ($i = 0; $i < count($db_connections); $i++)
				echo "<option value=$i ".($i==$coy ? 'selected':'') .">" . $db_connections[$i]["name"] . "</option>";
			echo "</select>\n";
			echo "</td></tr>";
		} else {
//			$coy = $def_coy;
			text_row(_("Company"), "company_login_nickname", "", 20, 50);
		}
		start_row();
		label_cell($demo_text, "colspan=2 align='center' id='log_msg'");
		end_row();
	}; 
	end_table(1);
	echo "<center><input type='submit' class='btnlogin' value='&nbsp;&nbsp;"._("Login")."&nbsp;&nbsp;' name='SubmitUser'"
		.($login_timeout ? '':" onclick='set_fullmode();'").(isset($blocked_msg) ? " disabled" : '')." /></center>\n";

	foreach($_SESSION['timeout']['post'] as $p => $val) {
		// add all request variables to be resend together with login data
		if (!in_array($p, array('ui_mode', 'user_name_entry_field', 
			'password', 'SubmitUser', 'company_login_name'))) 
			if (!is_array($val))
				echo "<input type='hidden' name='$p' value='$val'>";
			else
				foreach($val as $i => $v)
					echo "<input type='hidden' name='{$p}[$i]' value='$v'>";
	}
	end_form(1);
	$Ajax->addScript(true, "document.forms[0].password.focus();");

    echo "<script language='JavaScript' type='text/javascript'>
    //<![CDATA[
            <!--
            document.forms[0].user_name_entry_field.select();
            document.forms[0].user_name_entry_field.focus();
            //-->
    //]]>
    </script>";
    
    div_end();
	//echo "<table class='bottomBar'>\n";
	//echo "<tr>";
	//if (isset($_SESSION['wa_current_user'])) 
	//	$date = Today() . " | " . Now();
	//else	
	//	$date = date("m/d/Y") . " | " . date("h.i am");
	//echo "<td class='bottomBarCell' style='text-align:center'>$date</td>\n";
	//echo "</tr></table>\n";
	echo "<table class='footer'>\n";
	//echo "<tr>\n";
	//echo "<td><a target='_blank' href='$power_url' tabindex='-1'>$app_title</a></td>\n";
	//echo "</tr>\n";
	
	//echo "<td style='text-align:center'><a target='_blank' href='$power_url' tabindex='-1'>$app_title</a> </td>";
	//echo "</tr>";
        echo "<tr>";
	echo "<td style='text-align:center'><a target='_blank' href='$power_url' tabindex='-1'>Copyright &copy; 2014 by $power_by</a></td>\n";
	echo "</tr>";
	echo "</tr></table><br><br>\n";
	echo "</body></html>\n";

?>
