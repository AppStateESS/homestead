<?php

class ShowReportHtmlCommand extends Command {
    
    private $reportId;
    
    public function setReportId($id){
        $this->reportId = $id;
    }
    
    public function getRequestVars()
    {
        if(!isset($this->reportId) || is_null($this->reportId)){
            throw new InvalidArgumentExection('Missing report id.');
        }
        
        return array('action'=>'ShowReportHtml', 'reportId'=>$this->reportId);
    }
    
    public function execute(CommandContext $context)
    {
        $reportId = $context->get('reportId');
        
        if(!isset($reportId) || is_null($reportId)){
            throw new InvalidArgumentExection('Missing report id.');
        }
        
        // Instantiate the report controller with the requested report id
        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        $report = ReportFactory::getReportById($reportId);
        
        $content = file_get_contents($report->getHtmlOutputFilename());
        
        if($content === FALSE){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not open report file.');
            $reportCmd = CommandFactory::getCommand('ShowReportDetail');
            $reportCmd->setReportClass($report->getClass());
            $reportCmd->redirect();
        }
        
        $context->setContent($content);
    }
}

?>