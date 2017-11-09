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

// ---------------------------------------------------------------------------------
function start_form($multi = false, $dummy = false, $action = "", $name = "", $attributes = "")
{
    // $dummy - leaved for compatibility with 2.0 API
    if ($name != "")
        $name = "name='$name'";

    if ( strlen($action) < 1) {
        $action = get_instance()->uri->uri_string();

    }

    if (strlen($action) < 1) {
        $action = $_SERVER['REQUEST_URI'];
    }
    if (strlen($action) < 1) {
        $action = $_SERVER['PHP_SELF'];
    }
    if( config_item('remove_index_php') == true ){
        $action = str_replace(array('index.php/'), NULL, $action);
    }
    

    if (($query_start = strpos($action, '?')) > 0) {
        $action = substr($action . '/', 0, $query_start);
    }
    // if( !preg_match('#^(\w+:)?//#i', $action) ){
    if (! strpos($action, $_SERVER['HTTP_HOST'])) {
        $action = str_replace(array(
            "//"
        ), NULL, $action);
        if( strpos($action, "/") == 0 ){
            $action = substr($action, 1);
        }

        $action = site_url($action);
    }

    if ($attributes != '') {
        $attributes = _parse_attributes($attributes);
    }

    if ($multi)
        echo "\n<form enctype='multipart/form-data' method='post' action='$action' $name $attributes>\n";
    else
        echo "\n<form method='post' action='$action' $name $attributes>\n";
}

// ---------------------------------------------------------------------------------
function end_form($breaks = 0)
{
    global $Ajax;

    $_SESSION['csrf_token'] = hash('sha256', uniqid(mt_rand(), true));
    if ($breaks)
        br($breaks);
    hidden('_focus');
    hidden('_modified', get_post('_modified', 0));
    hidden('_token', $_SESSION['csrf_token']);
    echo "</form>\n";
    $Ajax->activate('_token');
}

function check_csrf_token()
{
    if ($_SESSION['csrf_token'] != @$_POST['_token']) {
        display_error(_("Request from outside of this page is forbidden."));
        error_log(_("CSRF attack detected from: ") . @$_SERVER['HTTP_HOST'] . ' (' . @$_SERVER['HTTP_REFERER'] . ')');
        return false;
    }
    return true;
}


// -----------------------------------------------------------------------------------
// Find and replace hotkey marker.
// if $clean == true marker is removed and clean label is returned
// (for use in wiki help system), otherwise result is array of label
// with underlined hotkey letter and access property string.
//
function access_string($label, $clean = false)
{
    $access = '';
    $slices = array();

    if (preg_match('/(.*)&([a-zA-Z0-9])(.*)/', $label, $slices)) {
        $label = $clean ? $slices[1] . $slices[2] . $slices[3] : $slices[1] . '<u>' . $slices[2] . '</u>' . $slices[3];
        $access = " accesskey='" . strtoupper($slices[2]) . "'";
    }

    $label = str_replace('&&', '&', $label);

    return $clean ? $label : array(
        $label,
        $access
    );
}

function hyperlink_back($center = true, $no_menu = true, $type_no = 0, $trans_no = 0, $final = false)
{
    global $path_to_root;

    if ($center)
        echo "<center>";
    $id = 0;
    if ($no_menu && $trans_no != 0) {
        include_once ($path_to_root . "/admin/db/attachments_db.inc");
        $id = has_attachment($type_no, $trans_no);
        $attach = get_attachment_string($type_no, $trans_no);
        echo $attach;
    }
    $width = ($id != 0 ? "30%" : "20%");
    start_table(false, "width=$width");
    start_row();
    if ($no_menu) {
        echo "<td align=center><a href='javascript:window.print();'>" . _("Print") . "</a></td>\n";
    }
    echo "<td align=center><a href='javascript:goBack(" . ($final ? '-2' : '') . ");'>" . ($no_menu ? _("Close") : _("Back")) . "</a></td>\n";
    end_row();
    end_table();
    if ($center)
        echo "</center>";
    echo "<br>";
}

function hyperlink_no_params($target, $label, $center = true)
{
    $id = default_focus();
    $pars = access_string($label);
    if ($target == '')
        $target = $_SERVER['PHP_SELF'];
    if ($center)
        echo "<br><center>";
    echo "<a href='$target' id='$id' $pars[1]>$pars[0]</a>\n";
    if ($center)
        echo "</center>";
}

function hyperlink_no_params_td($target, $label)
{
    echo "<td>";
    hyperlink_no_params($target, $label);
    echo "</td>\n";
}

function viewer_link($label, $url = '', $class = '', $id = '', $icon = null)
{
    global $path_to_root;

    if ($class != '')
        $class = " class='$class'";

    if ($id != '')
        $class = " id='$id'";

    if ($url != "") {
        $pars = access_string($label);
        if (user_graphic_links() && $icon)
            $pars[0] = set_icon($icon, $pars[0]);

        $preview_str = "<a target='_blank' $class $id href='" . site_url($url) . "' onclick=\"javascript:openWindow(this.href,this.target); return false;\"$pars[1]>$pars[0]</a>";
    } else
        $preview_str = $label;
    return $preview_str;
}

function menu_link($url, $label, $id = null)
{
    $id = default_focus($id);
    $pars = access_string($label);
    $url = site_url($url);
    return "<a href='$url' class='menu_option' id='$id' $pars[1]>$pars[0]</a>";
}

function submenu_option($title, $url, $id = null)
{
    echo menu_link($url, $title, $id);
//     display_note(menu_link($url, $title, $id), 0, 1);
}

function submenu_view($title, $type, $number, $id = null)
{
    display_note(get_trans_view_str($type, $number, $title, false, 'viewlink', $id), 0, 1);
}

function submenu_print($title, $type, $number, $id = null, $email = 0, $extra = 0)
{
    return print_document_link($number, $title, true, $type, false, 'printlink', $id, $email, $extra);
//     display_note(print_document_link($number, $title, true, $type, false, 'printlink', $id, $email, $extra), 0, 1);
}
// -----------------------------------------------------------------------------------
function hyperlink_params($target, $label, $params, $center = true)
{
    $id = default_focus();
    $pars = access_string($label);
    if ($target == '')
        $target = $_SERVER['PHP_SELF'];

    if ($center)
        echo "<br><center>";

    $target = site_url($target);

    echo "<a id='$id' href='$target?$params'$pars[1]>$pars[0]</a>\n";
    if ($center)
        echo "</center>";
}

function hyperlink_params_td($target, $label, $params)
{
    echo "<td>";
    hyperlink_params($target, $label, $params, false);
    echo "</td>\n";
}

// -----------------------------------------------------------------------------------
function hyperlink_params_separate($target, $label, $params, $center = false)
{
    $id = default_focus();

    $pars = access_string($label);
    if ($center)
        echo "<br><center>";
    echo "<a target='_blank' id='$id' href='$target?$params' $pars[1]>$pars[0]</a>\n";
    if ($center)
        echo "</center>";
}

// -----------------------------------------------------------------------------------
function br($num = 1)
{
    for ($i = 0; $i < $num; $i ++)
        echo "<br>";
}




/*
 * Table editor interfaces. Key is editor type
 * 0 => url of editor page
 * 1 => hotkey code
 * 2 => context help
 */
$popup_editors = array(
    'customer' => array(
        '/sales/manage/customers.php?debtor_no=',
        113,
        _("Customers"),
        900,
        500
    ),
    'branch' => array(
        '/sales/manage/customer_branches.php?SelectedBranch=',
        114,
        _("Branches"),
        900,
        700
    ),
    'supplier' => array(
        '/purchasing/manage/suppliers.php?supplier_id=',
        113,
        _("Suppliers"),
        900,
        700
    ),
    'item' => array(
        '/inventory/manage/items.php?stock_id=',
        115,
        _("Items"),
        800,
        600
    )
);
/*
 * Bind editors for various selectors.
 * $type - type of editor
 * $input - name of related input field
 * $caller - optional function key code (available values F1-F12: 112-123,
 * true: default)
 */
function set_editor($type, $input, $caller = true)
{
    global $Editors, $popup_editors, $Pagehelp;

    $key = $caller === true ? $popup_editors[$type][1] : $caller;

    $Editors[$key] = array(
        site_url() . $popup_editors[$type][0],
        $input,
        $popup_editors[$type][3],
        $popup_editors[$type][4]
    );

    $help = '<kbd class="kbd-light">F' . ($key - 111) . '</kbd> - ';

    $help .= '<span>'.$popup_editors[$type][2]."</span>";
    $Pagehelp[] = $help;
}
// ------------------------------------------------------------------------------
// Procedures below are now obsolete. Preserved for eventual future use.

/*
 * External page call with saving current context.
 * $call - url of external page
 * $ctx - optional. name of SESSION context object or array of names of POST
 * variables saved on call
 */
function context_call($call, $ctx = '')
{
    if (is_array($ctx)) {
        foreach ($ctx as $postname) {
            $context[$postname] = get_post($postname);
        }
    } else
        $context = isset($_SESSION[$ctx]) ? $_SESSION[$ctx] : null;

    array_unshift($_SESSION['Context'], array(
        'name' => $ctx,
        'ctx' => $context,
        'caller' => $_SERVER['PHP_SELF'],
        'ret' => array()
    ));
    meta_forward($call);
}
/*
 * Restores context after external page call and
 * returns array of data passed by external page.
 */
function context_restore()
{
    if (count($_SESSION['Context'])) {
        if ($_SERVER['PHP_SELF'] == $_SESSION['Context'][0]['caller']) {
            $ctx = array_shift($_SESSION['Context']);
            if ($ctx) {
                if (is_array($ctx['ctx'])) {
                    foreach ($ctx['ctx'] as $name => $val) {
                        $_POST[$name] = $val;
                    }
                } else
                    if ($ctx['name'] != '')
                        $_SESSION[$ctx['name']] = $ctx['ctx'];
                return $ctx['ret'];
            }
        }
    }
    return false;
}

/*
 * Return to caller page if the page was called from external context.
 */
function context_return($ret)
{
    if (count($_SESSION['Context'])) {
        $ctx = &$_SESSION['Context'][0];
        $ctx['ret'] = $ret;
        meta_forward($ctx['caller']);
    }
}
/*
 * Clearing context stack after page cancel.
 */
function context_reset()
{
    $_SESSION['Context'] = array();
}
/*
 * Context stack initialization
 */
if (! isset($_SESSION['Context'])) {
    context_reset();
}
/*
 * Redirector for selector F4 calls.
 * $sel_editors is array of selname=>editor_page
 */
function editor_redirect($sel_editors, $save_fun = '')
{
    foreach ($sel_editors as $selname => $editor)
        if (isset($_POST['_' . $selname . '_editor'])) {
            if (function_exists($save_fun))
                $save_fun();
            unset($_POST['_' . $selname . '_editor']);
            context_call($editor, array_keys($_POST));
        }
}
/*
 * Return procedure for selector F4 calls
 */
function editor_return($vars, $restore_fun = '')
{
    if (function_exists($restore_fun))
        $restore_fun();

    if ($ret = context_restore()) {
        foreach ($vars as $postname => $retname)
            if (isset($ret[$retname])) {
                $_POST[$postname] = $ret[$retname];
                set_focus($postname);
            }
    }
}

function confirm_dialog($submit, $msg)
{
    if (find_post($submit)) {
        display_warning($msg);
        br();
        submit_center_first('DialogConfirm', _("Proceed"), '', true);
        submit_center_last('DialogCancel', _("Cancel"), '', 'cancel');
        return 0;
    } else
        return get_post('DialogConfirm', 0);
}

/*
 * Block menu/shortcut links during transaction procesing.
 */
function page_processing($msg = false)
{
    global $Ajax;

    if ($msg === true)
        $msg = _('Entered data has not been saved yet.\nDo you want to abandon changes?');

    $js = "_validate._processing=" . ($msg ? '\'' . strtr($msg, array(
        "\n" => '\\n'
    )) . '\';' : 'null;');
    if (in_ajax()) {
        $Ajax->addScript(true, $js);
    } else
        add_js_source($js);
}

function page_modified($status = true)
{
    global $Ajax;

    $js = "_validate._modified=" . ($status ? 1 : 0) . ';';
    if (in_ajax()) {
        $Ajax->addScript(true, $js);
    } else
        add_js_source($js);
}

?>