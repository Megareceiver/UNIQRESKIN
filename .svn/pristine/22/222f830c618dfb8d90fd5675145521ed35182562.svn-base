<?php

function meta_forward($forward_to, $params = "")
{
    if( substr($params, 0, 1) !='?' ){
        $params = '?' . $params;
    }

    global $Ajax;
    if( in_ajax() ){
        echo "<meta http-equiv='Refresh' content='0; url=$forward_to$params'>\n";
        echo "<center><br>" . _("You should automatically be forwarded.");
        echo " " . _("If this does not happen") . " " . "<a href='$forward_to?$params'>" . _("click here") . "</a> " . _("to continue") . ".<br><br></center>\n";


        $Ajax->redirect("$forward_to$params");
    } else {
        redirect("$forward_to$params");
    }

    exit();
}

function redirect_query($query=NULL){

    meta_forward($_SERVER['PHP_SELF'], $query);
}