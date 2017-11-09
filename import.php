<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL,
	as published by the Free Software Foundation, either version 3
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_TAXRATES';
$path_to_root="..";

include($path_to_root . "/includes/session.inc");
page(_($help_context = "Import Data"));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/admin/db/import_db.php");
$import = new import_data();
simple_page_mode(true);
//-----------------------------------------------------------------------------------

function can_process(){
// 	global $selected_id;

// 	if (strlen($_POST['name']) == 0){
// 		display_error(_("The tax type name cannot be empty."));
// 		set_focus('name');
// 		return false;
// 	}
// 	elseif (!check_num('rate', 0))
// 	{
// 		display_error( _("The default tax rate must be numeric and not less than zero."));
// 		set_focus('rate');
// 		return false;
// 	}


	return true;
}

//-----------------------------------------------------------------------------------
// bug($Mode);die('quannh');
if ($Mode=='ADD_ITEM' && can_process()){
	$input_error = 0;

	if (isset($_FILES['excelfile']) && $_FILES['excelfile']['name'] != '') {
// 		$result = $_FILES['excelfile']['error'];
		$filepath = company_path()."/import";
		if (!file_exists($filepath)){
			mkdir($filepath);
		}
		$fileinfo = pathinfo($_FILES['excelfile']['name']);
		$filename = clean_file_name($fileinfo['filename'].'_'.time().'.'.$fileinfo['extension']);

// 		//But check for the worst
		if (!in_array( $fileinfo['extension'], array('xls','XLS','xlsx','XLSX'))){
// 			display_error(_('Only XLS files are supported - a file extension of '.$fileinfo['extension'].' is expected'));
// 			$input_error = 1;
		} elseif ( $_FILES['excelfile']['size'] > ($max_image_size * 1024)) {
// 			display_error(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $max_image_size);
// 			$input_error = 1;
		} elseif (file_exists($filename)) {
// 			$result = unlink($filename);
// 			if (!$result){
// 				display_error(_('The existing file could not be removed'));
// 				$input_error = 1;
// 			}
		}

		if ($input_error != 1){
			$result  =  move_uploaded_file($_FILES['excelfile']['tmp_name'], $filepath.'/'.$filename);
// 			// 			$_POST['file'] = clean_file_name($_FILES['excel']['name']);
			if(!$result)
				display_error(_('Error uploading logo file'));
			else if( $import->readfile($filename) )
				display_notification_centered(_("Success import Data."));


// 				display_notification_centered(_("Success import Data."));
// 				// 				if( isset($_GET['file']) ){



// 				// 				}
// 				// 				$import->addfile();

// 			display_notification_centered(_("Success import Data."));
		}


		// 		$Ajax->activate('_page_body');
	}

	//display_notification(_('New tax type has been added'));
	$Mode = 'RESET';
} else {
	display_note(_("Upload Excel file to import data into system"), 0, 1);
}


if ($Mode == 'RESET'){
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}
//-----------------------------------------------------------------------------------



start_form(TRUE);



//-----------------------------------------------------------------------------------

start_table(TABLESTYLE2);

file_row(_("Data File:"), 'excelfile');


end_table(1);

echo '<center>'.submit('ADD_ITEM', _("Upload"), 'both', _('Submit changes')).'</center>';

end_form();

end_page();

?>
