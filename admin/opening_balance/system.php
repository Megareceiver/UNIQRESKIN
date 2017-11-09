<?php
$page_security = 'SA_GLSETUP';
$path_to_root="../..";
include($path_to_root . "/includes/session.inc");
include ROOT.'/includes/view.php';
include ROOT.'/admin/controller/opening.php';

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

global $ci;

$js = $total_area = null;
$js .= get_js_date_picker();


page(_("System GL Accounts Opening Balance"), false, false, "", $js,false,null,$total_area);
$action = new opening();


start_form();
$action->system_gl();

submit_center('submit', _("Submit"), true, '', 'default');
end_form();



end_page();