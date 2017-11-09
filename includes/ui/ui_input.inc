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
//
// Sets local POST value and adds Value to ajax posting if needed
//
/*function set_post($name, $value, $ajax_trigger=true) {
    global $Ajax;

    $_POST[$name] = $value;
    if ($ajax_trigger) $Ajax->activate($name);
}
*/


//------------------------------------------------------------------------------
//
// Helper function for simple db table editor pages
//
function simple_page_mode($numeric_id = true)
{
	global $Ajax, $Mode, $selected_id;

	$default = $numeric_id ? -1 : '';
	$selected_id = get_post('selected_id', $default);
	foreach (array('ADD_ITEM', 'UPDATE_ITEM', 'RESET', 'CLONE') as $m) {
		if (isset($_POST[$m])) {
			$Ajax->activate('_page_body');
			if ($m == 'RESET'  || $m == 'CLONE')
				$selected_id = $default;
			unset($_POST['_focus']);
			$Mode = $m; return;
		}
	}
	foreach (array('Edit', 'Delete') as $m) {
		foreach ($_POST as $p => $pvar) {
			if (strpos($p, $m) === 0) {
//				$selected_id = strtr(substr($p, strlen($m)), array('%2E'=>'.'));
				unset($_POST['_focus']); // focus on first form entry
				$selected_id = quoted_printable_decode(substr($p, strlen($m)));
				$Ajax->activate('_page_body');
				$Mode = $m;

				return;
			}
		}
	}

	$Mode = '';
}

//------------------------------------------------------------------------------
//
//	Read numeric value from user formatted input
//
function input_num($postname=null, $dflt=0)
{
	if (!isset($_POST[$postname]) || $_POST[$postname] == "")
	  	return $dflt;

    return user_numeric($_POST[$postname]);
}



/*
	For following controls:
	'both' - use both Ctrl-Enter and Escape hotkeys
	'cancel' - apply to 'RESET' button
*/
function submit_add_or_update($add=true, $title=false, $async=false, $clone=false)
{
	$cancel = $async;

	if ($async === 'both') {
		$async = 'default';
		$cancel = 'cancel';
	}
	else if ($async === 'default')
		$cancel = true;
	else if ($async === 'cancel')
		$async = true;

	if ($add)
		submit('ADD_ITEM', _("Add new"), true, $title, $async,'plus-square');
	else {

		submit('UPDATE_ITEM', _("Update"), true, _('Submit changes'), $async,'floppy-o');

		if ($clone){
		    submit('CLONE', _("Clone"), true,_('Edit new record with current data'), $async,'files-o');
		}
		submit('RESET', _("Cancel"), true, _('Cancel edition'), $cancel,'rotate-left');
	}
}

function submit_add_or_update_center($add=true, $title=false, $async=false, $clone=false) {
	submit_add_or_update($add, $title, $async, $clone);
}

function submit_add_or_update_row($add=true, $right=true, $extra="", $title=false, $async=false, $clone = false)
{
	echo "<tr>";
	if ($right)
		echo "<td>&nbsp;</td>\n";
	echo "<td $extra>";
	submit_add_or_update($add, $title, $async, $clone);
	echo "</td></tr>\n";
}



function submit_return($name, $value, $title=false)
{
	if (@$_REQUEST['popup']) {
		submit($name, $value, true, $title, 'selector');
	}
}

function submit_js_confirm($name, $msg, $set = true) {
	global $Ajax;
	$js = "_validate.$name=".($set ? "function(){ return confirm('"
				. strtr($msg, array("\n"=>'\\n')) . "');};"
				: 'null;');
	if (in_ajax()) {
		$Ajax->addScript(true, $js);
	} else
		add_js_source($js);
}
//-----------------------------------------------------------------------------------

// function set_icon($icon, $title=false)
// {
// // 	global $path_to_root;

// // 	if( in_array($icon, array(ICON_EDIT,ICON_DELETE, ICON_DOWN, ICON_VIEW) ) ){
// 	    switch ($icon){
// 	        case ICON_EDIT:
// 	            //$class='icon19_edit';
// 	            return '<i class="fa fa-edit text-info"></i>';
// 	            break;
// 	        case ICON_DELETE:
// 	            return '<i class="fa fa-remove text-danger"></i>';
// 	            break;
// 	        case ICON_DOWN:
// 	            return '<i class="fa fa-get-pocket text-info"></i>';
// 	            break;
// 	        case ICON_VIEW:
// 	            return '<i class="fa fa-eye text-info"></i>';
// 	            break;
//             case ICON_UPDATE:
//                 return '<i class="fa fa-save text-info"></i>';
//                 break;
//             case ICON_CANCEL:
//                 return '<i class="fa fa-rotate-left text-info"></i>';
//                 break;

//             default: $class = 'icon button'; break;
// 	    }
// // 	    return '<span class="'.$class.'" >icon</span>';
// // 	} else {
// // 	    if (basename($icon) === $icon) // standard icons does not contain path separator
// // 	        $icon = site_url("/themes/".user_theme()."/images/$icon");

// // 	    return "<img src='$icon' height='12' border='0'".($title ? " title='$title'" : "")." />\n";
// // 	}

// }






//-----------------------------------------------------------------------------------

function check_value($name)
{
	if (!isset($_POST[$name]))
		return 0;
	return 1;
}

//-----------------------------------------------------------------------------------


function email_cell($label, $params="", $id=null)
{
	label_cell("<a href='mailto:$label'>$label</a>", $params, $id);
}

function amount_decimal_cell($label, $params="", $id=null)
{
	$dec = 0;
	label_cell(price_decimal_format($label, $dec), "nowrap align=right ".$params, $id);
}



function amount_total_cell($label, $bold=false, $params="", $id=null){
    if ($bold)
        label_cell("<b>".number_format2($label,user_amount_dec())."</b>", "nowrap align=right ".$params, $id);
    else
        label_cell(number_format2($label,user_amount_dec()), "nowrap align=right ".$params, $id);
}

//JAM  Allow entered unit prices to be fractional
function unit_amount_cell($label, $bold=false, $params="", $id=null)
{
	if ($bold)
		label_cell("<b>".unit_price_format($label)."</b>", "nowrap align=right ".$params, $id);
	else
		label_cell(unit_price_format($label), "nowrap align=right ".$params, $id);
}


function percent_cell($label, $bold=false, $id=null)
{
	if ($bold)
		label_cell("<b>".percent_format($label)."</b>", "nowrap align=right", $id);
	else
		label_cell(percent_format($label), "nowrap align=right", $id);
}
// 2008-06-15. Changed
function qty_cell($label, $bold=false, $dec=null, $id=null)
{
	if (!isset($dec))
		$dec = get_qty_dec();
	if ($bold)
		label_cell("<b>".number_format2($label, $dec)."</b>", "nowrap align=right", $id);
	else
		label_cell(number_format2($label, $dec), "nowrap align=right", $id);
}

//-----------------------------------------------------------------------------------


//-----------------------------------------------------------------------------------

function text_row_ex($label, $name, $size, $max=null, $title=null, $value=null, $params=null, $post_label=null)
{
	echo "<tr><td class='label'>$label</td>";
	text_cells_ex(null, $name, $size, $max, $value, $title, $params, $post_label);

	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------
function email_row($label, $name, $value, $size, $max, $title=null, $params="", $post_label="")
{
	if (get_post($name))
		$label = "<a href='Mailto:".$_POST[$name]."'>$label</a>";
	text_row($label, $name, $value, $size, $max, $title, $params, $post_label);
}

function email_row_ex($label, $name, $size, $max=null, $title=null, $value=null, $params=null, $post_label=null)
{
	if (get_post($name))
		$label = "<a href='Mailto:".$_POST[$name]."'>$label</a>";
	text_row_ex($label, $name, $size, $max, $title, $value, $params, $post_label);
}

function link_row($label, $name, $value, $size, $max, $title=null, $params="", $post_label="")
{
	$val = get_post($name);
	if ($val) {
		if (strpos($val,'http://')===false)
			$val = 'http://'.$val;
		$label = "<a href='$val' target='_blank'>$label</a>";
	}
	text_row($label, $name, $value, $size, $max, $title, $params, $post_label);
}

function link_row_ex($label, $name, $size, $max=null, $title=null, $value=null, $params=null, $post_label=null)
{
	$val = get_post($name);
	if ($val) {
		if (strpos($val,'http://')===false)
			$val = 'http://'.$val;
		$label = "<a href='$val' target='_blank'>$label</a>";
	}
	text_row_ex($label, $name, $size, $max, $title, $value, $params, $post_label);
}



//-----------------------------------------------------------------------------------
function password_row($label, $name, $value)
{
	echo "<tr><td class='label'>$label</td>";
	label_cell("<input type='password' name='$name' size=20 maxlength=20 value='$value' />");
	echo "</tr>\n";
}




//-----------------------------------------------------------------------------------

function percent_row($label, $name, $init=null)
{

	if (!isset($_POST[$name]) || $_POST[$name]=="")
	{
		$_POST[$name] = $init == null ? '' : $init;
	}

	small_amount_row($label, $name, $_POST[$name], null, "%", user_percent_dec());
}

//JAM  Allow entered unit prices to be fractional
function unit_amount_cells($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
{
	if (!isset($dec))
	  	$dec = user_price_dec()+2;

	amount_cells_ex($label, $name, 15, 15, $init, $params, $post_label, $dec+2);
}



function small_amount_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
{
	echo "<tr>";
	small_amount_cells($label, $name, $init, $params, $post_label, $dec);
	echo "</tr>\n";
}

//-----------------------------------------------------------------------------------





function small_qty_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
{
	if (!isset($dec))
	  	$dec = user_qty_dec();

	echo "<tr>";
	small_amount_cells($label, $name, $init, $params, $post_label, $dec);
	echo "</tr>\n";
}





//-----------------------------------------------------------------------------------


//-----------------------------------------------------------------------------------
/*
function text_row_with_submit($label, $name, $value, $size, $max, $input_name, $input_value)
{
  	global $Ajax;

	default_focus($name);
	echo "<tr><td>$label</td>\n";
	echo "<td>";

	if ($value == null)
		$value = (!isset($_POST[$name]) ? "" : $_POST[$name]);
	echo "<input type=\"text\" name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"$value\">   ";

	submit($input_name, $input_value);

	echo "</td></tr>\n";
	$Ajax->addUpdate($name, $name, $value);
}
*/
//-----------------------------------------------------------------------------------
//
//	When show_inactive page option is set
//  displays value of inactive field as checkbox cell.
//  Also updates database record after status change.
//



function paging_control_row($th,$limit,$current=1,$total=1,$echo=true){
	$cols = ( is_int($th) ) ? $th : count($th);
	$html = "<tr><td colspan=".$cols."><div style='float:right;'>";


	$page = round($total/$limit, 0, PHP_ROUND_HALF_DOWN);

	if( $page*$limit < $total ){
		$page ++;
	}

	$begin = 1;
	$end = 5;
	if( $current >= 3 ){
		$begin = $current-2;
		$end = $current+2;
		if( $end > $page ){
			$end = $page;
			$begin = $end-5;
		}
	}

	if( $end >= $page ) {
		$end = $page;
	}



	if( $current > 1){
		$html.=paging_button('First',1,$current);
	}

	if( $current > 1){
		$html.=paging_button('Prev',$current-1,$current);
	}

	for ($num=$begin;$num <= $end;$num ++){
		$html.=paging_button($num,$num,$current);
	}
	if( $current > 1 && $current != $page){
		$html.=paging_button('Next',$current+1,$current);
	}

	if( $end <= $page && $current != $page ){
		$html.=paging_button('Last',$page,$current);
	}
	$html.="</div></td></tr>";
	if( $echo ){
		echo $html;
	} else {
		return $html;
	}

}

function paging_button($title,$page,$current=1){
	$url = $_SERVER['SCRIPT_NAME'];
	$defaultCurrent = 1;
	if( isset( $_GET['page'] ) ){
		$defaultCurrent = $_GET['page'];
	}
	$url_query = '';
	if( !empty($_GET) ){

		foreach ($_GET AS $key=>$query){
			$url_query .="&$key=$query";
		}
	}
	return '<a href="'.$url.'?page='.$page.$url_query.'" class="paging'.( ($defaultCurrent==$page)?' selected':null ).'" >'.$title.'</a>';
}

//
//	Inserts additional column header when display of inactive records is on.
//
function inactive_control_column(&$th) {
	global $Ajax;

	if (check_value('show_inactive'))
		array_insert($th, count($th)-2 , _("Inactive"));
	if (get_post('_show_inactive_update')) {
		$Ajax->activate('_page_body');
	}
}



function supplier_credit_row($supplier, $credit, $parms='')
{
	global $path_to_root;

	label_row( _("Current Credit:"),
		"<a target='_blank' " . ($credit<0 ? 'class="redfg"' : '')
		."href='".site_url()."/purchasing/inquiry/supplier_inquiry.php?supplier_id=".$supplier."'"
		." onclick=\"javascript:openWindow(this.href,this.target); return false;\" >"
		. price_format($credit)
		."</a>", $parms);
}



?>