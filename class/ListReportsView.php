<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_Reports.php');

class ListReportsView extends View {

	public function show()
	{
		if(!Current_User::allow('hms', 'reports')){
			return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
		}

		$tpl = array();

		$reports = HMS_Reports::getReports();

		$tpl['REPORTS'] = array();

		foreach($reports as $code=>$name) {
			 
			$reportCmd = CommandFactory::getCommand('RunReport');
			$reportCmd->setReport($code);
			 
			$cmd = CommandFactory::getCommand('JSPopup');
			$cmd->setViewCommand($reportCmd);

			$cmd->setWidth(800);
			$cmd->setHeight(600);
			$cmd->setLabel($name);
			$cmd->setTitle("Run '$name' Report");
			$cmd->setWindowName('hms_report');

			$tpl['REPORTS'][]['REPORT_LINK'] =  $cmd->getLink($name);
		}

        Layout::addPageTitle("Reports");

		$final = PHPWS_Template::process($tpl, 'hms', 'admin/display_reports.tpl');
		return $final;
	}
}

?>