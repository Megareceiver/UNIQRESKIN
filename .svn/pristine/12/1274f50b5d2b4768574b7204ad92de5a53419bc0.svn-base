<?php


function db_table_responsive(&$pager)
{
    global $use_popup_windows, $use_date_picker, $path_to_root;

    $pager->select_records();
    div_start("_{$pager->name}_span",null, false, $attributes = 'class="dataTables_wrapper" style="width:100%; margin-left:0;" ');

    $headers = array();

    //div_start(NULL,null, false, 'class="table-responsive" ');
    /* show a table of records returned by the sql */
    echo '<div class="table-responsive" >';
    start_table(TABLESTYLE, array('width'=>$pager->width,'class'=>'table table-striped table-bordered table-hover','data-count-fixed-columns'=>"2"));
    echo '<thead><tr>';

    foreach ($pager->columns as $num_col => $col) {

        if (isset($col['head']) && ($col['type'] != 'inactive' || get_post('show_inactive'))) {
            if (! isset($col['ord'])){
                label_cell($col['head'],NULL,null,$type='th');
            } else {
                $header_class = 'sorting';
                label_cell($col['head'],array('class'=>$header_class),null,$type='th');
            }
        }
    }
    echo '</tr></thead>';

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
            $cell = isset($col['name']) ? $row[$col['name']] : '';
            if (isset($col['fun'])) { // use data input function if defined
                $fun = $col['fun'];

                if (method_exists($pager, $fun)) {
                    $cell = $pager->$fun($row, $cell);
                } elseif( is_object($pager->ci_control) AND method_exists($pager->ci_control, $fun) ){
                    $cell = $pager->ci_control->$fun($row, $cell);
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
                    if ( isset($col['align']) )
                        label_cell($cell, "align='" . $col['align'] . "'");
                    else
                        label_cell($cell,' class="" ');
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
    echo '</div>';
    //div_end(); //table-scrollable


    start_row("class='navibar'");
    $colspan = count($pager->columns);
    $inact = @$pager->inactive_ctrl == true ? ' ' . checkbox(null, 'show_inactive', null, true) . _("Show also Inactive") : '';
    end_row();

    if ($pager->rec_count) {
        echo '<div class="fixed-table-pagination" >';

        $from = ($pager->curr_page - 1) * $pager->page_len + 1;
        $to = $from + $pager->page_len - 1;
        if ($to > $pager->rec_count){
            $to = $pager->rec_count;
        }
        $all = $pager->rec_count;
        $but_pref = $pager->name . '_page_';

        echo '<div class="pull-left pagination-detail" >';
        echo '<span class="pagination-info" >'.sprintf(_('Records %d-%d of %d'), $from, $to, $all).'</span>';

        echo '</div>';
//         echo '<div class="row" >'
//                 .'<div class="col-md-5 col-sm-5" >'
//                     .'<div class="dataTables_info" role="status" aria-live="polite" >'.sprintf(_('Records %d-%d of %d'), $from, $to, $all).'</div>'
//                 .'</div>'
//             .'<div class="col-md-7 col-sm-7">'
//                     .'<div class="dataTables_paginate paging_bootstrap_full_number"  >'
//                         .'<ul class="pagination" style="visibility: visible;">'
//                         .'<li class="prev">'.navi_button($but_pref . 'first', _('First'), $pager->first_page).'</li>'
//                         .'<li class="prev">'.navi_button($but_pref . 'prev', _('Prev'), $pager->prev_page).'</li>'
//                         .'<li class="next">'.navi_button($but_pref . 'next', _('Next'), $pager->next_page).'</li>'
//                         .'<li class="next">'.navi_button($but_pref . 'last', _('Last'), $pager->last_page).'</li>'
//                             .'</ul>'
//                     .'</div>'
//             .'</div>'
//             .'</div>';

        echo '<div class="pull-right pagination" ><ul class="pagination"> ';
        echo '<li class="page-item '. ($pager->first_page ? '':'disabled').'" >'.navi_button($but_pref . 'first',  _('First'), $pager->first_page).'</li>';
        echo '<li class="page-item '. ($pager->prev_page ? '':'disabled').'" >'.navi_button($but_pref . 'prev',  _('Prev'), $pager->prev_page).'</li>';
        echo '<li class="page-item '. ($pager->next_page ? '':'disabled').'" >'.navi_button($but_pref . 'next',  _('Next'), $pager->next_page).'</li>';
//         echo '<li class="page-item '. ($pager->next_page ? '':'disabled').'" >'.navi_button($but_pref ,  _('next'), $pager->next_page).'</li>';
        echo '<li class="page-item '. ($pager->last_page ? '':'disabled').'" >'.navi_button($but_pref . 'last',  _('Last'), $pager->last_page).'</li>';
        echo '</ul></div></div>';

    } else {
        label_cell(_('No records') . $inact, "colspan=$colspan class='navibar'");
    }
    div_end();
}
function ci_table_view($tableFields = array(), $query, $view='table_items',$view_module='html'){
    $data = array('table'=>$tableFields,'items'=>NULL);

    if( is_object($query) && get_class($query)=="CI_DB_mysql_result" ){

        //             $db = $this->db->query($this->db->last_query()." LIMIT $offset,".$this->limit);
        $db = $this->db->query($this->db->last_query());

        $data['items'] = $db->result();
    } elseif ( is_array($query)){
        $data['items'] = $query;
    }else {
        display_db_error("Database have errors!", $this->db->last_query(),false);
        return FALSE;
    }
    module_view($view,$data,true, false,$view_module);
}