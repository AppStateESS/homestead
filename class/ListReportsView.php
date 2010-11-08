<?php

/**
 * List Reports View
 *
 * Shows a list of all the available reports and
 * the associated actions for each report.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package hms
 */

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class ListReportsView extends View {

    private $reports;

    public function __construct(Array $reports){
        $this->reports = $reports;
    }

	public function show()
	{
		if(!Current_User::allow('hms', 'reports')){
		    PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
			throw new PermissionException('You do not have permission to run reports.');
		}

		$tpl = array();
        $tpl['REPORTS'] = array();

		foreach($this->reports as $r) {

		    $tags = array();
            $tags['reportName'] = $r->getFriendlyName();
            $lastExec = $r->getLastExec();
            if(is_null($lastExec)){
                $tags['lastRun'] = 'not yet run';
            }else{
                $lastRunTime  = HMS_Util::relativeTime($lastExec->getExecTimestamp());
                $lastUsername =
                $tags['lastRun']    = HMS_Util::relativeTime($lastExec->getExecTimestamp());
                $tags['lastRun']    = $lastExec->getExecUserId();

                // Create the view command
                //TODO
            }

            // Create the command for the 'details' view
            $detailsCmd = CommandFactory::getCommand('ShowReportDetails');
            $detailsCmd->setReportName(get_class($r));
            $tags['detailsView'] = $detailsCmd->getLink('details');

            // Create the command to schedule the report
            $scheduleCmd = CommandFactory::getCommand('ShowScheduleReport');
            $scheduleCmd->setReportName(get_class($r));
            $tags['scheduleView'] = $scheduleCmd->getLink('run');

            $tpl['REPORTS'][] = $tags;
		}

        Layout::addPageTitle("Reports");

		$final = PHPWS_Template::process($tpl, 'hms', 'admin/display_reports.tpl');
		return $final;
	}
}

?>