<?php
if( !function_exists('print_document_link') ){
    include_once(ROOT . "/reporting/includes/reporting.inc");
}
function form_group_bootstrap($label, $input, $columns = '3-9', $help = NULL,$attribute=NULL)
{
    $bootstrap = get_instance()->bootstrap;

    if ($bootstrap->input_only) {
        echo $input;
        return TRUE;
    }

    $attribute_group = array(
        'class' => 'input-group-sm row'
    );
    if( isset($bootstrap->label_inline) AND $bootstrap->label_inline ){
        $attribute_group['class'] .= " col-float";
    }
    $attribute = _attributes_str2array($attribute);
    if( !empty($attribute) ){
        $attribute_group = array_merge($attribute_group,$attribute);
    }

    $html = '<div '._parse_attributes($attribute_group).' >';
    $cols = explode('-', $columns);
    $label_col = 0;
    if (is_numeric($columns)) {
        $label_col = $columns;
    } else {
        $label_col = get_instance()->bootstrap->label_column;
    }
    if (is_null($label_col)) {
        get_instance()->bootstrap->label_column = $label_col = 4;
    }

    if ($label_col < 1 and is_string($columns) and count($cols) > 1) {
        $label_col = intval($cols[0]);
    }

    $input_col = 12 - $label_col;

    // if( $bootstrap->label_inline AND strlen($label) > 0){
    // $html .= "<label class=\"col-float control-label\">$label</label><div class=\"col-float\">$input</div>";

    // } else {
    if ($label != null and $label_col > 0) {
        $input_list_fix_mobile = NULL;
        $html .= "<label class=\"col-$label_col control-label\">$label</label>";
    } else {
        $input_col = 12;
        $input_list_fix_mobile = "mobile_fix_top";
    }

    if (strlen($help) > 0) {
        $input .= "<span class=\"help-block clearfix\"> $help</span>";
    }

        $html .= "<div class=\"col-$input_col align-items-center $input_list_fix_mobile \">$input</div>";
        $html .='</div>';

    // }
//     $html .= '</div>';

    if (! $bootstrap->return) {
        echo $html;
    } else {
        return $html;
    }
}

function tabs_bootstrap($name, $tabs, $dft = '')
{
    global $Ajax, $ajax_divs;

    $selname = '_' . $name . '_sel';
    $div = '_' . $name . '_div';

    $sel = find_submit($name . '_', false);
    if ($sel == null)
        $sel = get_post($selname, (string) ($dft === '' ? key($tabs) : $dft));

    if ($sel !== @$_POST[$selname]) {
        $Ajax->activate($name);
        $Ajax->activate('controls');
    }

    $_POST[$selname] = $sel;

    div_start($name, $trigger = null, $non_ajax = false, $attributes = 'style="padding: 15px;"');

    $str = "<ul class='ajaxtabs nav nav-tabs' rel='$div'>\n";
    foreach ($tabs as $tab_no => $tab) {
        $acc = access_string(is_array($tab) ? $tab[0] : $tab);
        $disabled = (is_array($tab) && ! $tab[1]) ? 'disabled ' : '';
        $str .= ("<li class=\"" . ((string) $tab_no === $sel ? 'active' : NULL) . "\">" . "<button type='submit' name='{$name}_" . $tab_no . "' class='" . ((string) $tab_no === $sel ? 'current' : 'ajaxbutton') . "' $acc[1] $disabled>" . "<span>$acc[0]</span>" . "</button>\n" . "</li>\n");
    }

    $str .= "</ul>\n";

    $str_mobile = "<ul class='ajaxtabs nav nav-tabs' rel='$div'>\n";
    if (count($tabs) > 2) {
        $i = 0;
        $mobile_dropdown = NULL;
        foreach ($tabs as $tab_no => $tab) {
            $i ++;
            $acc = access_string(is_array($tab) ? $tab[0] : $tab);
            $disabled = (is_array($tab) && ! $tab[1]) ? 'disabled ' : '';
            $tab_no = $tab_no;
            if ((string) $tab_no === $sel) {
                $str_mobile .= ("<li class=\"" . ((string) $tab_no === $sel ? 'active' : NULL) . "\">" . "<button type='submit' name='{$name}_" . $tab_no . "' class='" . ((string) $tab_no === $sel ? 'current' : 'ajaxbutton') . "' $acc[1] $disabled>" . "<span>$acc[0]</span>" . "</button>\n" . "</li>\n");
            } else {
                $mobile_dropdown .= ("<li class=\"" . ((string) $tab_no === $sel ? 'active' : NULL) . "\">" . "<button type='submit' name='{$name}_" . $tab_no . "' class='" . ((string) $tab_no === $sel ? 'current' : 'ajaxbutton') . "' $acc[1] $disabled>" . "<span>$acc[0]</span>" . "</button>\n" . "</li>\n");
            }
        }

        if (strlen($mobile_dropdown) > 0) {
            $str_mobile .= '<li class="dropdown"><a class="dropdown-toggle" href="javascript:;" data-toggle="dropdown" aria-expanded="false">More <i class="fa fa-angle-down"></i></a> <ul class="dropdown-menu" role="menu">' . $mobile_dropdown . '</ul></li>';
        }
    }
    $str_mobile .= "</ul>\n";

    if (isMobile() and count($tabs) > 2) {
        $str = $str_mobile;
    }

    $str .= "<div class='spaceBox'></div>\n";
    $str .= "<input type='hidden' name='$selname' value='$sel'>\n";
    $str .= '<div class="tab-content clearfix" style="margin-right: 0; margin-left: 0;" >';

    //
    // $str .= "<div class='contentBox' id='$div'>\n";
    echo $str;
}

function box_start($title = NULL, $icon = NULL, $new_row = true, $box_id = NULL)
{
    get_instance()->bootstrap->box_start($title, $icon, $new_row, $box_id);
}
function box_start_col_md_8($title = NULL, $icon = NULL, $new_row = true, $box_id = NULL)
{
    get_instance()->bootstrap->box_start_col_md_8($title, $icon, $new_row, $box_id);
}


function box_end($new_row = true)
{
    get_instance()->bootstrap->box_end($new_row);
}

function box_form_start()
{
    get_instance()->bootstrap->box_form_start($class = NULL, $attributes = NULL);
}

function box_form_end()
{
    get_instance()->bootstrap->box_form_end();
}

/*
 * colums
 */
function col_start($md = 12, $attributes = NULL, $autoClose = true)
{
    $attributes = trim($attributes);
    if( strpos($attributes, "class") === false AND strpos($attributes, "style") === false  ){
        $attributes = array('class'=>$attributes);
    }
    get_instance()->bootstrap->col_start($md, $attributes, $autoClose);
}

function col_end($all = false)
{
    get_instance()->bootstrap->col_end($all);
}

function col_content($str = NULL)
{
    col_start('col-12');
    echo $str;
    col_end();
}

function row_start($class = NULL, $attributes = NULL)
{
    get_instance()->bootstrap->row_start($class, $attributes);
}

function row_end()
{
    get_instance()->bootstrap->row_end();
}

function row_content($str = NULL)
{
    row_start();
    echo $str;
    row_end();
}

function bootstrap_set_label_column($int = 0)
{
    if (intval($int) < 1) {
        $int = 3;
    }
//     if( is_mobile() ){

//     }
    get_instance()->bootstrap->label_column = $int;
}

function bootstrap_set_label_inline($label_inline)
{
    get_instance()->bootstrap->label_inline = $label_inline;
}

function fieldset_start($title = NULL, $attributes = NULL)
{
    get_instance()->bootstrap->fieldset_start($title, $attributes);
}

function fieldset_end()
{
    get_instance()->bootstrap->fieldset_end();
}

function box_footer_start($class = NULL, $attributes = NULL, $show_back = true)
{
    get_instance()->bootstrap->box_footer_start($class, $attributes, $show_back);
}

function box_footer_end()
{
    get_instance()->bootstrap->box_footer_end();
}

function box_footer($show_back = true)
{
    get_instance()->bootstrap->box_footer_start(NULL, NULL, $show_back);
    get_instance()->bootstrap->box_footer_end();
}

/*
 * MT List
 */
function mt_list_start($title = NULL, $class = NULL, $title_bg = 'red')
{
    get_instance()->bootstrap->mt_list_start($title, $class, $title_bg);
}

function mt_list_end($title = NULL, $class = NULL)
{
    get_instance()->bootstrap->mt_list_end();
}

function mt_list($content = NULL, $icon = NULL)
{
    get_instance()->bootstrap->mt_list($content, $icon);
}

function mt_list_print($title, $type, $number, $id = null, $email = 0, $extra = 0){
    $link = print_document_link($number, $title, true, $type, false, 'printlink', $id, $email, $extra);
    get_instance()->bootstrap->mt_list($link);
}
function mt_list_tran_view($title, $type, $number, $id = null)
{
    $link = get_trans_view_str($type, $number, $title, false, 'viewlink', $id);
    get_instance()->bootstrap->mt_list($link);
}
function mt_list_gl_view($title, $type, $number, $id = null)
{
    $link = get_gl_view_str($type, $number, $title,$force=false, $class='', $id='',$icon=false);
    get_instance()->bootstrap->mt_list($link);
}


function mt_list_link($title, $url=NULL, $id = null)
{
    $link = menu_link($url, $title, $id);
    mt_list($link);
}

function mt_list_hyperlink($uri=NULL, $title, $query = null)
{
    $url = $uri. ( strlen($query) > 0 ? "?$query" : NULL ) ;
    $link = menu_link($url, $title);
    mt_list($link);
}


function box_footer_show_active($show_back = false)
{
    $bootstrap = get_instance()->bootstrap;

    box_footer_start(null, null, $show_back);
    echo '<span class="inputs-left flex-column mt-1">';
    $bootstrap->input_only = true;
    echo input_switch('Show also Inactive', 'show_inactive', null, array(
        'Inactive',
        'Active'
    ), true, NULL);
    $bootstrap->input_only = false;
    echo '</span>';
    // checkbox(null, 'show_inactive', null, true) _("Show also Inactive")
    echo submit('Update', _('Update'), false, '', null, 'save', 'float-right');

    box_footer_end();
}

/*
 * $color = []
 * red, white, dark, blue, green, grey
 * blue-madison, blue-chambray, blue-ebonyclay, blue-hoki, blue-steel, blue-soft
 * blue-dark, blue-sharp, blue-oleo
 * 
 * green-meadow, green-seagreen, green-turquoise, green-haze, green-jungle, 
 * green-soft, green-dark, green-sharp, green-steel
 * 
 * grey-steel, grey-cararra
 */
function portlet_start($title= NULL,$color='red',$class= NULL){
    get_instance()->bootstrap->portlet_start($title,$color,$class);
}
function portlet_end(){
    get_instance()->bootstrap->portlet_end();
}

