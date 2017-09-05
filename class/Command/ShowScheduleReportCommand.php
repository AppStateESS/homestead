<?php

namespace Homestead\Command;

use \Homestead\ReportManager;
use \Homestead\Exception\PermissionException;

class ShowScheduleReportCommand extends Command {

    private $reportName;

    public function setReportName($name){
        $this->reportName = $name;
    }

    public function getRequestVars()
    {
        return array('action'=>'ShowScheduleReport', 'reportName'=>$this->reportName);
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'reports')){
            throw new PermissionException('You do not have permission to run reports.');
        }

        $this->reportName = $context->get('reportName');

        $report = ReportManager::getReportInstance($this->reportName);

        /* TODO
         $view = $report->getSetupView();

         if(is_null($view)){

         }else{

         }
         $context->setContent($view->show());
         */

        $report->schedule();
    }
}
