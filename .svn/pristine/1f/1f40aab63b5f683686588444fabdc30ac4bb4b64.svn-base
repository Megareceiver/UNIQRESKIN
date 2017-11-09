<?php
$page_security = 'SA_TAXRATES';
$path_to_root="..";
include($path_to_root . "/includes/session.inc");

$file = null;
if( isset($_GET['file']) ){
	$file = $_GET['file'];
}

switch ($file){
	case 'data_migration_template';
		$file_data = read_file(ROOT.'company/Data_Migration_Template.xls');
// 		bug(ROOT.'company/0/Data_Migration_Template.xls');die;
		force_download($file.'.xls',$file_data);
// 		redirect('admin/import.php');
		break;
	default: redirect('admin/import.php');
}