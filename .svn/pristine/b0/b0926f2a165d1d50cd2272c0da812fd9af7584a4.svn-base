<?php

class HtmlBootstrapLib
{

    function __construct()
    {
        $this->static_value();
    }

    private function static_value(){
        $this->label_column = 3;
        $this->return = false;
        $this->input_only = false;
        $this->input_class = 'form-control';

        $this->label_inline = false;

        $this->col_started = false;
        $this->colums_level = array();
        $this->colums_level_running = 0;

        $this->row_level = array();
        $this->row_level_running = 0;

        $this->row_started = false;

    }

    /*
     * column func
     */


    function col_start($md = 12, $attributes = NULL, $autoClose=true)
    {
        if( isset($this->mt_list_started) AND $this->mt_list_started ){
            $this->mt_list_end();
        }
        
        if( isset($this->portlet_started) AND $this->portlet_started ){
            $this->portlet_end();
        }

        if ( array_key_exists("level_".$this->colums_level_running, $this->colums_level) AND $this->colums_level["level_".$this->colums_level_running] ) {
            $this->col_end();
        }

        if (is_string($attributes)) {
            $attributes = _attributes_str2array($attributes);
        }

        if ( !isset($attributes['class'])) {
            $attributes['class'] = NULL;
        }
        if( is_numeric($md) ){
            $attributes['class'] .= " col-$md";
        } else {
            $attributes['class'] .= " $md";
        }


//         $level = count($this->colums_level) +1;


        if( $this->colums_level_running > 0 AND $this->colums_level["level_".$this->colums_level_running] != true){
            $this->colums_level_running ++;
        } else if( $this->colums_level_running < 1 ){
            $this->colums_level_running = 1;
        }

        $level = "level_".$this->colums_level_running;

//         if( array_key_exists("level_".$this->colums_level_running, $this->colums_level) AND $this->colums_level["level_".$this->colums_level_running] ){
//             $this->col_end();
//         }

        $this->colums_level["level_".$this->colums_level_running] = $autoClose;

        $attributes['level'] = $level;


        echo "<div " . _parse_attributes($attributes) . " >\n";
        $this->col_started = true;
    }

    function col_end($all=false)
    {
        if( $this->colums_level_running > 0 ){
            echo "</div>\n";
            //$this->col_started = false;
            $this->colums_level_running --;
        }

        if( $all AND $this->colums_level_running > 0 ){
            $this->col_end($all);
        }

    }

    /*
     * Row
     */
    function row_start($class = NULL, $attributes = NULL, $autoClose=true)
    {

        if ( array_key_exists("level_".$this->row_level_running, $this->row_level) AND $this->row_level["level_".$this->row_level_running] ) {
            $this->row_end();
        }
        if ( is_string($attributes) ) {
            $attributes = _attributes_str2array($attributes);
        }

        if ( !isset($attributes['class'])) {
            $attributes['class'] = NULL;
        }
        $attributes['class'] .= " row $class";


        /*
         * add Level
         */
        if( $this->row_level_running > 0 AND $this->row_level["level_".$this->row_level_running] != true){
            $this->row_level_running ++;
        } else if( $this->row_level_running < 1 ){
            $this->row_level_running = 1;
        }

        $level = "level_".$this->row_level_running;


        $this->row_level["level_".$this->row_level_running] = $autoClose;

        $attributes['level'] = $level;

        /*
         * out put HTML
         */


        if ($this->row_started) {
            $this->row_end();
        }
//         var_dump($attributes);
        echo "<div " . _parse_attributes($attributes) . " >\n";


    }
    function row_end($all=false)
    {
        if( isset($this->mt_list_started) AND $this->mt_list_started ){
            $this->mt_list_end();
        }

        if( $this->colums_level_running > 0 ){
            $this->col_end(TRUE);
        }

        if( $this->row_level_running > 0 ){
            echo "</div>\n";
            $this->row_level_running --;
        }

        if( $all AND $this->row_level_running > 0 ){
            $this->row_end($all);
        }
        $this->row_started = false;

    }

    /*
     * Box func
     */
    var $box_started = false;

    function box_start($title = NULL, $icon = NULL, $new_row=false, $box_id=NULL)
    {
        $this->col_started = false;

        if ($this->box_started) {
            $this->box_end();
        }
        // echo '<div class="clearfix portlet box"><div class="portlet-body form-body form-horizontal clearfix">';

        $html = NULL;
        if( $new_row ){
            $html.= '<div class="row"><div class="col-md-12">';
        }

        $html.= '<div class="clearfix portlet light portlet-fit portlet-form" '.(strlen($box_id) > 0 ? 'id="'.$box_id.'"' : NULL).' >';

        if (strlen($title) > 0) {
            if( !$icon ){
                $icon = 'icon-settings';
            }
            $box_title = '<div class="caption font-green">';
            $box_title .= '';
            $box_title .= '<span class="caption-subject bold uppercase"><i class="fa '.$icon.'"></i> ' . $title . '</span>';
            $box_title .= '</div>';

            $html.= '<div class="portlet-title">' . $box_title . '</div>';
        }

        echo $html;
        $this->box_started = true;
        $this->box_form_start();
    }
    function box_end($new_row=true)
    {
        if ($this->box_form_started) {
            $this->box_form_end();
        }

        if ($this->box_footer_started ) {
            $this->box_footer_end();
        }

        $html = "</div>";

        if( $new_row ){
            $html.= "</div></div>";
        }
        echo $html;
        $this->box_started = false;
    }

/*
 * portlet func
 */

    var $box_form_started = false;
    function box_form_start($class = NULL, $attributes = NULL)
    {
        if ($this->box_form_started) {
            $this->box_form_end();
        }
        echo '<div class="form-body form-horizontal justify-content-center">';
        $this->box_form_started = true;
    }
    function box_form_end(){
        echo "</div>";
        $this->box_form_started = false;
    }


    /*
     * box footer
     */
    var $box_footer_started = false;
    function box_footer_start($class = NULL, $attributes = NULL,$show_back=true)
    {

        if ($this->box_form_started) {
            $this->box_form_end();
        }

        if ($this->box_footer_started) {
            $this->tb_footer_end();
        }

        $attributes['class'] = 'form-actions clearfix';
        if (is_string($attributes)) {
            $attributes = _attributes_str2array($attributes);
        }
        echo "<div " . _parse_attributes($attributes) . " >";

        if( $show_back ){
            echo anchor(get_instance()->url_back,'<i class="fa fa-rotate-left"></i>  Back','class="btn green btn_left" ');
        }

        $this->tb_footer_started = true;
    }
    function box_footer_end()
    {
        echo "</div>";
        $this->box_footer_started = false;
    }


    /*
     * fieldset
     */
    var $fieldset_started = false;
    function fieldset_start($title=NULL,$attributes=NULL){
        if ($this->fieldset_started) {
            $this->fieldset_end();
        }
        if (is_string($attributes)) {
            $attributes = _attributes_str2array($attributes);
        }
        $this->fieldset_started = true;
        echo '<fieldset ' . _parse_attributes($attributes) . '><legend>'.$title.'</legend>';
    }
    function fieldset_end()
    {
        echo "</fieldset>";
        $this->fieldset_started = false;
    }

    /*
     * table row
     */
    var $tb_row_started = false;

    function tb_row_start($class = NULL, $attributes = NULL)
    {
        if (is_string($attributes)) {
            $attributes = _attributes_str2array($attributes);
        }

        if ($this->tb_row_started) {
            $this->tb_row_end();
        }
        echo "<tr " . _parse_attributes($attributes) . " >";
        $this->tb_row_started = true;
    }
    function tb_row_end()
    {
        echo "</tr>";
        $this->tb_row_started = false;
    }
    /*
     * Table footer
     */
    var $tb_footer_started = false;

    function tb_footer_start($class = NULL, $attributes = NULL)
    {
        if (is_string($attributes)) {
            $attributes = _attributes_str2array($attributes);
        }

        if ($this->tb_footer_started) {
            $this->tb_footer_end();
        }
        echo "<tfoot " . _parse_attributes($attributes) . " >";
        $this->tb_footer_started = true;
    }
    function tb_footer_end()
    {
        echo "</tfoot>";
        $this->tb_row_started = false;
    }

    /*
     * MT List
     */
    var $mt_list_started= false;
    function mt_list_start($title= NULL,$class= NULL,$title_bg='red'){
        if( $this->mt_list_started ){
            $this->mt_list_end();
        }

        $html = '<div class="mt-element-list">';
        $html.= '<div class="mt-list-head list-simple font-white bg-'.$title_bg.' '.$class.' ">';
        if( strlen($title) > 0 ){
            $html.= '<div class="list-head-title-container"><h4 class="list-title">'.$title.'</h4></div>';
        }
        $html.= '</div>';

        $html.= '<div class="mt-list-container list-simple"><ul>';
        echo $html;
        $this->mt_list_started = true;
    }
    function mt_list_end(){
        echo '</ul></div></div>';
        $this->mt_list_started = false;
    }
    function mt_list($content=NULL,$icon = NULL){
        $html = '<li class="mt-list-item">';
        $html.= '<div class="list-icon-container done"><i class="icon-check"></i></div>';
        $html.= '<div class="list-item-content">'.$content.'</div>';
        $html.= ' </li>';
        if( $this->mt_list_started ){
            echo $html;
        }
    }

    
    /*
     * portlet
     */
    var $portlet_started = false;
    function portlet_start($title= NULL,$corlor='yellow-crusta',$class= NULL){
        $html = '<div class="portlet box '.$corlor.'">';
        $html.= '<div class="portlet-title"><div class="caption bold">'.$title.'</div></div>';
        $html.= '<div class="portlet-body">';
        
        if( $this->portlet_started ){
            $this->portlet_end();
        }
        
        
        $this->portlet_started = true;
        echo $html;
    }
    
    function portlet_end(){
        if( $this->portlet_started ){
            echo "</div></div>\n";
            $this->portlet_started = false;
        }
      
    }
}
