<?php
$page_security = 'SA_TAXRATES';
$path_to_root = "..";
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
global $ci;
$ci->controller_load('gst_from5');
$ci->gst_from5->index();
?>
