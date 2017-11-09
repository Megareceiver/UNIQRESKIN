<?php
$path_to_root="..";
$page_security = 'SA_OPEN';
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/reporting/includes/reports_classes.inc");

global $ci;



// $reports = new BoxReports;

$report_type = $ci->input->get('REP_ID');

switch ( $report_type ){
	case 1:
		$page = 'Bank Payment';
		$table = array(
			'start_date' => array('type'=>'DATEENDM','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
			'end_date' => array('type'=>'DATEENDM','title'=>_('End Date'),'value'=>end_fiscalyear() ),
			'account' => array('type'=>'BANK_ACCOUNTS','title'=>_('From Account')),
			'ref' => array('type'=>'TEXT','title'=>_('Reference')),
			'comment' => array('type'=>'TEXTBOX','title'=>_('Comments')),
			'type'=> array('type'=>'HIDDEN','value'=>$report_type),
		);
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
		break;
	case 4:
		$page = 'Bank Account Transfer Voucher';
		$table = array(
			'start_date' => array('type'=>'DATEENDM','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
			'end_date' => array('type'=>'DATEENDM','title'=>_('End Date'),'value'=>end_fiscalyear() ),
			'account' => array('type'=>'BANK_ACCOUNTS','title'=>_('From Account')),
			'ref' => array('type'=>'TEXT','title'=>_('Reference')),
			'comment' => array('type'=>'TEXTBOX','title'=>_('Comments')),
			'type'=> array('type'=>'HIDDEN','value'=>$report_type),
		);
		break;
	case ST_PURCHORDER:
		$page = 'Print Purchase Orders';
		$table = array(
				'from'=>array('type'=>'supplier','title'=>_('From Supplier')),
				'to'=>array('type'=>'supplier','title'=>_('To Supplier')),
				'curr'=>array('type'=>'currency','title'=>_('Currency Filter')),
				'email'=>array('type'=>'true','title'=>_('Email Suppliers')),
				
				'start_date' => array('type'=>'DATEENDM','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
				'end_date' => array('type'=>'DATEENDM','title'=>_('End Date'),'value'=>end_fiscalyear() ),
// 				'account' => array('type'=>'BANK_ACCOUNTS','title'=>_('From Account')),
// 				'ref' => array('type'=>'TEXT','title'=>_('Reference')),
				'comment' => array('type'=>'TEXTBOX','title'=>_('Comments')),
				'type'=> array('type'=>'HIDDEN','value'=>$report_type),
		);
		break;
	case ST_JOURNAL:
	default:
		$page = 'GL Journal Vouchers';

		$table = array(
			'start_date' => array('type'=>'DATEENDM','title'=>_('Start Date'),'value'=>begin_fiscalyear()),
			'end_date' => array('type'=>'DATEENDM','title'=>_('End Date'),'value'=>end_fiscalyear() ),
			'ref' => array('type'=>'TEXT','title'=>_('Reference')),
			'comment' => array('type'=>'TEXTBOX','title'=>_('Comments')),
			'type'=> array('type'=>'HIDDEN','value'=>$report_type),
		);
		break;
}

$js = "";
$js .= get_js_date_picker();

add_js_file('reports.js');
page( $page, false, false, "", $js);

// die('iam here');


// $reports->addReportClass(_('General Ledger'), RC_GL);
// $reports->addReport(RC_GL, 803, _('List of '.$page),$table);

// add_custom_reports($reports);

// echo $reports->ci_display();
echo $ci->view('cash_gl/vouchers_pdf',array('options'=>$table),true);

end_page();
?>
