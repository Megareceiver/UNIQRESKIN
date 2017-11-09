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
class accountantarea_app extends application
{
	function accountantarea_app()
	{
// 	    if( defined('COUNTRY') && COUNTRY== 60 ){
// 	        $this->application("AC", _($this->help_context = "Accountant's Area"), true, 'fa fa-desktop');

// 	        $this->add_module(_("Process Journal Entry"), 'fa fa-pencil-square-o', "gl/gl_journal.php?NewJournal=Yes");
// 	        $this->add_module(_("Reports"), 'fa fa-file-text-o');
// 	        $this->add_rapp_function(1, _("Management Reports"), "#", 'SA_SUPPTRANSVIEW', MENU_INQUIRY,'file-text');
// 	        $this->add_rapp_function(1, _("Transaction Reports"), "#", 'SA_SUPPTRANSVIEW', MENU_INQUIRY,'file-text');
// 	        $this->add_rapp_function(1, _("Audit Reports"), "#", 'SA_SUPPTRANSVIEW', MENU_INQUIRY,'file-text');
// 	        $this->add_module(_("Trial Balance Export"), 'fa fa-download');
// 	        $this->add_module(_("Documents Upload"), 'fa fa-upload', "index.php/documents/bookkeepers");
// 	        $this->add_extensions();
// 	    }
        if( config_ci('mobile_document') ){
            $this->application("AC", _($this->help_context = "Accountant's Area"), true, 'fa fa-desktop');
            $this->add_module(_("Mobile Accountant"), 'fa fa-upload', "documents/bookkeepers");
            $this->add_extensions();
        }
	    

	}
}


?>