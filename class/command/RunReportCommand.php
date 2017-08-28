<?php

namespace Homestead\command;

use \Homestead\Command;

class RunReportCommand extends Command {

    private $report;

    public function setReport($report){
        $this->report = $report;
    }

    public function getRequestVars()
    {
        return array('action'=>'RunReport', 'report'=>$this->report);
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'reports')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do no have permission to run reports.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Reports.php');

        $reportName = $context->get('report');

        if(is_null($reportName)){
            throw new \InvalidArgumentException('Missing report name.');
        }

        //$context->setContent(HMS_Reports::runReport($reportName));

        \Layout::nakedDisplay(HMS_Reports::runReport($reportName), true);
    }
}
