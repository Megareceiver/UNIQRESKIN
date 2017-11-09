<?php
$path_to_root="..";

include($path_to_root . "/includes/session.inc");
global $ci,$Ajax, $security_areas;
// bug($security_areas);die;
$page_security = 'SA_PRINTERS';

$ci->load_library('reporting');

$rep_type = $ci->input->get('REP_ID');
$ci->reporting->get_items($rep_type);

if( $ci->input->get('save')==true ){
    $ci->reporting->do_report(true,true);
// 	$Ajax->popup($ci->reporting->do_report());
} else {

	$ci->reporting->do_report(false);
}


?>
