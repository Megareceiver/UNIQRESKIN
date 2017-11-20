<?php

// -----------------------------------------------------------------------------
// Creates new db_pager $_SESSION object on first page call.
// Retrieves from $_SESSION var on subsequent $_POST calls
//
// $name - base name for pager controls and $_SESSION object name
// $sql - base sql for data inquiry. Order of fields implies
// pager columns order.
// $coldef - array of column definitions. Example definitions
// Column with title 'User name' and default text format:
// 'User name'
// Skipped field from sql query. Data for the field is not displayed:
// 'dummy' => 'skip'
// Column without title, data retrieved form row data with function func():
// array('fun'=>'func')
// Inserted column with title 'Some', formated with function rowfun().
// formated as date:
// 'Some' => array('type'=>'date, 'insert'=>true, 'fun'=>'rowfun')
// Column with name 'Another', formatted as date,
// sortable with ascending start order (available orders: asc,desc, '').
// 'Another' => array('type'=>'date', 'ord'=>'asc')
//
// All available column format types you will find in db_pager_view.inc file.
// If query result has more fields than count($coldef), rest of data is ignored
// during display, but can be used in format handlers for 'spec' and 'insert'
// type columns.
function &new_db_pager($name, $sql, $coldef, $table = null, $key = null, $page_len = 0)
{

    if ( isset($_SESSION[$name]) && ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SESSION[$name]->sql != $sql)) {
        unset($_SESSION[$name]); // kill pager if sql has changed
    }
    if (! isset($_SESSION[$name])) {
        $_SESSION[$name] = new db_pager($sql, $name, $table, $page_len);
        $_SESSION[$name]->main_tbl = $table;
        $_SESSION[$name]->key = $key;
        $_SESSION[$name]->set_sql($sql);
        $_SESSION[$name]->set_columns($coldef);
    }

    return $_SESSION[$name];
}


function start_table($class = false, $attributes = "", $padding = '2', $spacing = '0')
{
    $attributes = _attributes_str2array($attributes);
    if (! isset($attributes['class'])) {
        $attributes['class'] = NULL;
    }

    if ($class == TABLESTYLE_NOBORDER) {
        $attributes['class'] .= ' tablestyle_noborder';
        // echo " class='tablestyle_noborder'";
    } elseif ($class == TABLESTYLE2) {
        $attributes['class'] .= ' tablestyle2';
        // echo " class=''";
    } elseif ($class == TABLESTYLE) {
        $attributes['class'] .= ' tablestyle';
    }
    $attributes['cellpadding'] = $padding;
    $attributes['cellspacing'] = $spacing;

    if (! strpos($attributes['class'], 'table-striped')) {
        $attributes['class'] .= ' table table-striped table-bordered table-hover';
    }

    $attributes['class'] .= ' table-responsive';
    echo "<table " . _parse_attributes($attributes) . ">\n";
}

function end_table($breaks = 0)
{
    echo "</table>\n";
    if ($breaks)
        br($breaks);
}

function start_outer_table($class = false, $extra = "", $padding = '2', $spacing = '0', $br = false, $column_width = null)
{
    if ($br)
        br();
    start_table($class, $extra, $padding, $spacing);
    echo "<tr valign=top><td width=\"$column_width\" >\n"; // outer table
}

function table_section($number = 1, $width = false, $colum_width = null)
{
    if ($number > 1) {
        echo "</table>\n";
        $width = ($width ? "width=$width" : "");
        echo "</td><td style='border-left:1px solid #cccccc;' $width>\n"; // outer table
    }
    echo "<table class='tablestyle_inner'>\n";
}

function end_outer_table($breaks = 0, $close_table = true)
{
    if ($close_table)
        echo "</table>\n";
    echo "</td></tr>\n";
    end_table($breaks);
}

//
// outer table spacer
//
function vertical_space($params = '')
{
    echo "</td></tr><tr><td valign=center $params>";
}

function table_header($labels, $params = '')
{
    echo '<thead><tr>';
    foreach ($labels as $key => $label) {
        $col_param = NULL;
        if (is_array($label)) {

            $col_param = $label;
            $label = $key;
            if (array_key_exists('label', $col_param)) {
                $label = $col_param['label'];
            }
        }

        labelheader_cell($label, $col_param);
    }

    echo '</tr></thead>';
}

function labelheader_cell($label, $params = "")
{
    if (is_string($params)) {
        $params = _attributes_str2array($params);
    }
    if (! isset($params['class'])) {
        $params['class'] = NULL;
    }
    $params['class'] .= ' tableheader';
    echo "<th " . _parse_attributes($params) . " >".($label)."</th>\n";
}

function hyperlink_params_separate_td($target, $label, $params)
{
    echo "<td>";
    hyperlink_params_separate($target, $label, $params);
    echo "</td>\n";
}

// --------------------------------------------------------------------------------------------------
function alt_table_row_color(&$k, $extra_class = null)
{
    $classes = $extra_class ? array(
        $extra_class
    ) : array();
    if ($k == 1) {
        array_push($classes, 'oddrow');
        $k = 0;
    } else {
        array_push($classes, 'evenrow');
        $k ++;
    }
    echo "<tr class='" . implode(' ', $classes) . "'>\n";
}

function table_section_title($msg, $colspan = 2)
{
    echo "<tr class=\"table-info\" ><td colspan=$colspan class='tableheader'>$msg</td></tr>\n";
}

function start_row($param = "")
{
    $attributes = _attributes_str2array($param);
    echo "<tr " . _parse_attributes($attributes) . " >\n";
}

function end_row()
{
    echo "</tr>\n";
}

// -----------------------------------------------------------------------------
//
// Sql paged table view. Call this function inside form.
//
function display_db_pager(&$pager)
{
    //global $use_popup_windows, $use_date_picker, $path_to_root;

    $pager->select_records();

    div_start("_{$pager->name}_span" , $trigger = null, $non_ajax = false, $attributes = 'class="col-md-12"');
    $headers = array();

    foreach ($pager->columns as $num_col => $col) {
        // record status control column is displayed only when control checkbox is on

        if (isset($col['head']) && ($col['type'] != 'inactive' || get_post('show_inactive'))) {

            $attributes = array();

            if( is_array($col) ){
                if (isset($col['class']) ){
                    $attributes['class'] = $col['class'];
                }
                if (isset($col['align']) ){
                    switch ($col['align']){
                        case 'center':
                            $attributes['class'] = " text-center";
                            break;
                    }
                }

                if (isset($col['width']) ){
                    $attributes['width'] = $col['width'];
                }
            }

            $header_label = strtoupper($col['head']);
            if( isset($col['label']) ){
                $header_label = $col['label'];
            }

            if (! isset($col['ord'])){


            } else {
//                 $icon = (($col['ord'] == 'desc') ? 'sort_desc.gif' : ($col['ord'] == 'asc' ? 'sort_asc.gif' : 'sort_none.gif'));

//                 $header_label = navi_button($pager->name . '_sort_' . $num_col, $col['head'], true, $icon);
                $attributes['class'] = 'sortable';

                switch ($col['ord'] ){
                    case 'desc':
                        $attributes['class'] .= ' desc';
                        break;
                    case 'asc':
                        $attributes['class'] .= ' asc';
                        break;
                    default:
                        $attributes['class'] .= ' both';
                        break;

                }
                $header_label = navi_button($pager->name . '_sort_' . $num_col, strtoupper($header_label));
            }

//             $attributes['label'] = $header_label;

            $headers[$header_label] = $attributes;
        }
    }
    /* show a table of records returned by the sql */
    start_table(TABLESTYLE);
    table_header($headers);
// bug($pager->columns);

    if ($pager->header_fun) { // if set header handler
        start_row("class='{$pager->header_class}'");
        $fun = $pager->header_fun;
        if (method_exists($pager, $fun)) {
            $h = $pager->$fun($pager);
        } elseif (function_exists($fun)) {
            $h = $fun($pager);
        }

        foreach ($h as $c) { // draw header columns
            $pars = isset($c[1]) ? $c[1] : '';
            label_cell($c[0], $pars);
        }
        end_row();
    }

    $cc = 0; // row colour counter
    foreach ($pager->data as $line_no => $row) {

        $marker = $pager->marker;

        if ($marker && $marker($row))
            start_row("class='$pager->marker_class'");
        else
            alt_table_row_color($cc);
        foreach ($pager->columns as $k => $col) {

            $coltype = $col['type'];
//             bug($row);
            $cell = isset($col['name']) ? $row[$col['name']] : '';

            if (isset($col['fun'])) { // use data input function if defined
                $fun = $col['fun'];
                $fun_class = isset($col['class']) ? $col['class'] : NULL;
                if (method_exists($pager, $fun)) {
                    $cell = $pager->$fun($row, $cell);

                } elseif( !is_null($pager->ci_control) AND method_exists($pager->ci_control, $fun) ){
                    $cell = $pager->ci_control->$fun($row);
                } elseif (function_exists($fun)) {
                    $cell = $fun($row, $cell);
                } else
                    $cell = '';
            }
            switch ($coltype) { // format column
                case 'time':
                    label_cell($cell, "width=40");
                    break;
                case 'date':
                    label_cell(sql2date($cell), "align='center' nowrap");
                    break;
                case 'dstamp': // time stamp displayed as date
                    label_cell(sql2date(substr($cell, 0, 10)), "align='center' nowrap");
                    break;
                case 'tstamp': // time stamp - FIX user format
                    label_cell(sql2date(substr($cell, 0, 10)) . ' ' . substr($cell, 10), "align='center'");
                    break;
                case 'percent':
                    percent_cell($cell);
                    break;
                case 'amount':
                    if ($cell == '')
                        label_cell('');
                    else {
                        label_cell(number_total($cell), "nowrap align=right ");
                        // amount_cell($cell, false);
                    }

                    break;

                case 'qty':
                    if ($cell == '')
                        label_cell('');
                    else
                        qty_cell($cell, false, isset($col['dec']) ? $col['dec'] : null);
                    break;
                case 'email':
                    email_cell($cell, isset($col['align']) ? "align='" . $col['align'] . "'" : null);
                    break;
                case 'rate':
                    label_cell(number_format2($cell, user_exrate_dec()), "align=center");
                    break;
                case 'inactive':
                    if (get_post('show_inactive'))
                        $pager->inactive_control_cell($row);
                    break;
                default:

                    // case 'text':
                    if (isset($col['align']))
                        label_cell($cell, "align='" . $col['align'] . "'");
                    else
                        label_cell($cell);
                case 'skip': // column not displayed
            }
        }
        end_row();
    }
    // end of while loop

    if ($pager->footer_fun) { // if set footer handler
        start_row("class='{$pager->footer_class}'");
        $fun = $pager->footer_fun;
        if (method_exists($pager, $fun)) {
            $h = $pager->$fun($pager);
        } elseif (function_exists($fun)) {
            $h = $fun($pager);
        }

        foreach ($h as $c) { // draw footer columns
            $pars = isset($c[1]) ? $c[1] : '';
            label_cell($c[0], $pars);
        }
        end_row();
    }
    end_table();

//     start_row("class='navibar'");
    $colspan = count($pager->columns);
    $inact = @$pager->inactive_ctrl == true ? ' ' . checkbox(null, 'show_inactive', null, true) . _("Show also Inactive") : '';

    if ($pager->rec_count) {
        echo '<div class="fixed-table-pagination" >';
        echo '<div class="pull-left pagination-detail" >';

        $from = ($pager->curr_page - 1) * $pager->page_len + 1;
        $to = $from + $pager->page_len - 1;
        if ($to > $pager->rec_count)
            $to = $pager->rec_count;
        $all = $pager->rec_count;
        echo '<span class="pagination-info" >'.sprintf(_('Show %d-%d of %d'), $from, $to, $all).$inact.' transactions</span>';

        echo '</div>';


//         echo "<td colspan=$colspan class='navibar' style='border:none;padding:3px;'>";
//         echo "<div style='float:right;'>";
        $but_pref = $pager->name . '_page_';
//         start_table();
//         start_row();


        echo '<div class="pull-right pagination" ><ul class="pagination"> ';
        if (@$pager->inactive_ctrl)
            submit('Update', _('Update'), true, '', null); // inactive update

//         echo '<li>'.navi_button_cell($but_pref . 'first', _('First'), $pager->first_page, 'right').'</li>';

        echo '<li class="page-item '. ($pager->first_page ? '':'disabled').'" >'.navi_button($but_pref . 'first',  _('First'), $pager->first_page).'</li>';
        echo '<li class="page-item '. ($pager->prev_page ? '':'disabled').'" >'.navi_button($but_pref . 'prev',  _('Prev'), $pager->prev_page).'</li>';
        echo '<li class="page-item '. ($pager->next_page ? '':'disabled').'" >'.navi_button($but_pref . 'next',  _('Next'), $pager->next_page).'</li>';
        echo '<li class="page-item '. ($pager->last_page ? '':'disabled').'" >'.navi_button($but_pref . 'last',  _('Last'), $pager->last_page).'</li>';


//         echo '<li>'.navi_button_cell($but_pref . 'prev', _('Prev'), $pager->prev_page, 'right').'</li>';
//         echo '<li>'.navi_button_cell($but_pref . 'next', _('Next'), $pager->next_page, 'right').'</li>';
//         echo '<li>'.navi_button_cell($but_pref . 'last', _('Last'), $pager->last_page, 'right').'</li>';

        echo '</ul></div></div>';
//         end_row();
//         end_table();

//         echo "</div>";


//         echo "</td>";
    }
//     else {
//         label_cell(_('No records') . $inact, "colspan=$colspan class='navibar'");
//     }

//     end_row();



    if (isset($pager->marker_txt) AND !in_ajax() ){
        display_notification($pager->marker_txt);
//         display_note($pager->marker_txt, 0, 1, "class='$pager->notice_class'");
    }


    div_end();
    return true;
}

// -----------------------------------------------------------------------------------
function ref_cells($label, $name, $title = null, $init = null, $params = null, $submit_on_change = false)
{
    text_cells_ex($label, $name, 16, 18, $init, $title, $params, null, $submit_on_change);
}

// -----------------------------------------------------------------------------------
function ref_row($label, $name, $title = null, $init = null, $submit_on_change = false)
{
    echo "<tr><td class='label'>$label</td>";
    ref_cells(null, $name, $title, $init, null, $submit_on_change);
    echo "</tr>\n";
}