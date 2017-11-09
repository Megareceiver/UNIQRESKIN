<?php
$page_security = 'SA_SALESCREDITINV';
$path_to_root = "..";
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/admin/db/voiding_db.inc");
global $ci;
$ci->controller_load('wrongposting');
$ci->wrongposting->sale_credit_invoice();