<?php

class ExecReportSyncCommand extends Command {

    private $reportClass;

    public function setReportClass($class){
        $this->reportClass = $class;
    }

    public function getRequestVars()
    {
        if(!isset($this->reportClass) || is_null($this->reportClass)){
            throw new InvalidArgumentException('Missing report class.');
        }

        return array('action'=>'ExecReportSync', 'reportClass'=>$this->reportClass);
    }

    public function execute(CommandContext $context)
    {
        //TODO check permissions
        
        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        
        // Determine which report we're running
        $reportClass = $context->get('reportClass');

        if(!isset($reportClass) || is_null($reportClass)){
            throw new InvalidArgumentException('Missing report class.');
        }
        
        // Get the proper report controller
        $reportCtrl = ReportFactory::getControllerInstance($reportClass);
        
        // Initalize a new report
        $reportCtrl->newReport(time());

        // Get the params from the context
        $reportCtrl->setParamsFromContext($context);
        
        // Save this report so it'll have an ID
        $reportCtrl->saveReport();
        
        // Generate the report
        $reportCtrl->generateReport();
        
        // Get the default view command
        $viewCmd = $reportCtrl->getDefaultOutputViewCmd();

        // Rediect to the view command
        $viewCmd->redirect();
    }
}
?>