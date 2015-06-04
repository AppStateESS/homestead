<?php

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
        if(!Current_User::allow('hms', 'reports')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to run reports.');
        }

        $this->reportName = $context->get('reportName');

        PHPWS_Core::initModClass('hms', 'Report.php');

        $report = ReportManager::getReportInstance($this->reportName);

        /** TODO
         $view = $report->getSetupView();

         if(is_null($view)){

         }else{

         }
         $context->setContent($view->show());
         */

        $report->schedule();
    }
}

