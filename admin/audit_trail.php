<?php
$path_to_root="..";
$page_security = 'SA_TAXRATES';
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
global $ci,$Ajax;
$ci->controller_load('audit_trail');
return $ci->audit_trail->index();