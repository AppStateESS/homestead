<?php

/**
 * Show Report Details Command
 *
 * Shows the ReportDetailsView which gives a list of executions
 * and allows the user to view the output of each execution.
 *
 * @author jbooker
 * @package hms
 */

class ShowReportDetailsCommand extends Command {

    private $reportName;

    public function setReportName($name){
        $this->reportName = $name;
    }

    public function getRequestVars()
    {
        return array('action'=>'ShowReportDetails', 'reportName'=>$this->reportName);
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'reports')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to run reports.');
        }

        $this->reportName = $context->get('reportName');

        PHPWS_Core::initModClass('hms', 'Report.php');
        PHPWS_Core::initModClass('hms', 'ReportDetailsView.php');

        $view = new ReportDetailsView($this->reportName);
        $context->setContent($view->show());
    }
}

?>