<?php
function elem_p($content=NULL){
    echo '<p>'.$content.'</p>';
}

function elem_div_begin($class=NULL){
    echo '<div class="'.$class.'">';
}

function elem_div_end(){
    echo '</div>';
}