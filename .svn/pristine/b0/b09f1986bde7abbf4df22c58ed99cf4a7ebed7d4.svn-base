<?php
$page_security = 'SA_TAXRATES';
$path_to_root="..";

include($path_to_root . "/includes/session.inc");
// page(_($help_context = "Import Data"));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/admin/db/import_db.php");

$import = new import_data();
$data = $ci->db->query('SELECT * FROM import_process WHERE complate != total')->row();
if( isset($data->file) ){
	$import->readfile($data->file);
	switch ($data->module){
		case 'product':
			$import->importProduct($data->complate,$data->id);
			break;
		default:break;
	}
}
exit();