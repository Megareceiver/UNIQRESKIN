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
$path_to_root="..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/ui.inc");

if (find_submit('Rep') != -1) {
    include($path_to_root.'/reporting/prn_redirect.php');
    return;
}





//$company = get_company_pref();

$type = 0;
if( isset($_GET['type']) ) {
	$type = $_GET['type'];
}

switch ( $type ){
	case 1:
		$page = 'Bank Payment';
		$table = array(
			'start_date' => array('type'=>'DATEENDM','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
			'end_date' => array('type'=>'DATEENDM','title'=>_('End Date'),'value'=>end_fiscalyear() ),
			'account' => array('type'=>'BANK_ACCOUNTS','title'=>_('From Account')),
			'ref' => array('type'=>'TEXT','title'=>_('Reference')),
			'comment' => array('type'=>'TEXTBOX','title'=>_('Comments')),
			'type'=> array('type'=>'HIDDEN','value'=>$type),
		);
		$_REQUEST['REP_ID'] = 801;
		break;
	case 2:
		$page = 'Bank Deposit';
		$table = array(
			'start_date' => array('type'=>'DATEENDM','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
			'end_date' => array('type'=>'DATEENDM','title'=>_('End Date'),'value'=>end_fiscalyear() ),
			'account' => array('type'=>'BANK_ACCOUNTS','title'=>_('From Account')),
			'ref' => array('type'=>'TEXT','title'=>_('Reference')),
			'comment' => array('type'=>'TEXTBOX','title'=>_('Comments')),
			'type'=> array('type'=>'HIDDEN','value'=>$type),
		);
		$_REQUEST['REP_ID'] = 802;
		break;
	case 4:
		$page = 'Bank Account Transfer Voucher';
		$table = array(
			'start_date' => array('type'=>'DATEENDM','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
			'end_date' => array('type'=>'DATEENDM','title'=>_('End Date'),'value'=>end_fiscalyear() ),
			'account' => array('type'=>'BANK_ACCOUNTS','title'=>_('From Account')),
			'ref' => array('type'=>'TEXT','title'=>_('Reference')),
			'comment' => array('type'=>'TEXTBOX','title'=>_('Comments')),
			'type'=> array('type'=>'HIDDEN','value'=>$type),
		);
		$_REQUEST['REP_ID'] = 803;
		break;
	case ST_JOURNAL:
	default:
		$page = 'GL Journal Vouchers';
		$_REQUEST['REP_ID'] = 0;
		$table = array(
			'start_date' => array('type'=>'DATEENDM','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
			'end_date' => array('type'=>'DATEENDM','title'=>_('End Date'),'value'=>end_fiscalyear() ),
			'ref' => array('type'=>'TEXT','title'=>_('Reference')),
			'comment' => array('type'=>'TEXTBOX','title'=>_('Comments')),
			'type'=> array('type'=>'HIDDEN','value'=>$type),
		);
		break;
}

$js = "";
// $js .= get_js_date_picker();


add_js_file('js/reports.js');
page(_($help_context = "Reports and Analysis : $page"), false, false, "", $js);
if( !$_POST ){
    include_once($path_to_root . "/reporting/includes/reports_classes.inc");
    $reports = new BoxReports;
}
$reports->addReportClass(_('General Ledger'), RC_GL);
$reports->addReport(RC_GL, $_REQUEST['REP_ID'], _('List of '.$page),$table);

add_custom_reports($reports);

$bootstrap = get_instance()->bootstrap;
// echo $reports->ci_display();

// bug($reports->report_current);die;
// $bootstrap->box_start("");
start_form($multi = false, $dummy = false, $action = "", $name = "", 'target="_blank" class="report" ');
// $bootstrap->fieldset_start( access_string($reports->report_current->name,true) );
// $bootstrap->col_start(8,'class="col-md-offset-1"');
// $bootstrap->label_column = 4;
echo $reports->ci_display();

// $bootstrap->col_end();
// $bootstrap->box_footer_start();
// echo submit("Rep".$reports->report_current->id, _("Display: ") . access_string($reports->report_current->name, true), false, '', $pdf_debug ? false : 'default process','file-pdf-o');
// echo hidden('REP_ID', $reports->report_current->id, false);
// $bootstrap->box_footer_end();
// $bootstrap->fieldset_end();
end_form();
// $bootstrap->box_end();

end_page();
?>
