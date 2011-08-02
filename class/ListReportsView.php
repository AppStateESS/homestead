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

    private $reportControllers;

    public function __construct(Array $reportControllers){
        $this->reportControllers = $reportControllers;
    }

	public function show()
	{
	    $this->setTitle("Reports");
	    
		if(!Current_User::allow('hms', 'reports')){
		    PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
			throw new PermissionException('You do not have permission to run reports.');
		}

		$tpl = array();
        $tpl['REPORTS'] = array();

		foreach($this->reportControllers as $rc) {

		    $tags = array();
		    
		    /*
		    $rc->loadLastExec();
            $lastExec = $rc->getReport();
            if(is_null($lastExec->getId())){
                $tags['reportName'] = $rc->getFriendlyName();
                $tags['lastRunTime'] = 'not yet run';
            }else{
                $tags['lastRunTime']    = HMS_Util::relativeTime($lastExec->getCompletedTimestamp());

                // Create the view command
                //TODO
                //$tags['reportName'] = $viewCmd->getLink($r->getFriendlyName());
            }
			*/
            /*
            // Create the command for the 'details' view
            $detailsCmd = CommandFactory::getCommand('ShowReportDetails');
            //$detailsCmd->setReportName(get_class($r));
            $tags['detailsView'] = $detailsCmd->getLink('details');
			*/
            
            // Schedule a report
            //$scheduleCmd = CommandFactory::getCommand('ShowScheduleReport');
            
            // Create the command to run the report now
            //$scheduleCmd = CommandFactory::getCommand('ShowScheduleReport');
            //$rc->setupRunNowCommand($runNowCmd);
            //$tags['scheduleView'] = $scheduleCmd->getLink('run now');

            $tpl['REPORTS'][]['ITEM'] = $rc->getMenuItemView();
		}

		$final = PHPWS_Template::process($tpl, 'hms', 'admin/display_reports.tpl');
		return $final;
	}
}

?>