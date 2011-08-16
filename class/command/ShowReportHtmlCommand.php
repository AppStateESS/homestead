<?php

/**
 * ShowReportHtmlCommand
 * 
 * View controller that shows the HTML output of
 * a particular report exec.
 * 
 * @author jbooker
 * @package HMS
 */
class ShowReportHtmlCommand extends Command {
    
    private $reportId; // ID of the report to be shown
    
    /**
     * Sets the report ID to show
     * 
     * @param int $id ID of the report to show
     */
    public function setReportId($id){
        $this->reportId = $id;
    }
    
    /**
     * Returns the array of request vars for this command.
     * 
     * @throws InvalidArgumentExection
     * @return Array Array of request vars.
     */
    public function getRequestVars()
    {
        if(!isset($this->reportId) || is_null($this->reportId)){
            throw new InvalidArgumentExection('Missing report id.');
        }
        
        return array('action'=>'ShowReportHtml', 'reportId'=>$this->reportId);
    }
    
    /**
     * Shows the requested report's HTML output.
     * 
     * @param CommandContext $context
     * @throws InvalidArgumentExection
     */
    public function execute(CommandContext $context)
    {
        $reportId = $context->get('reportId');
        
        if(!isset($reportId) || is_null($reportId)){
            throw new InvalidArgumentExection('Missing report id.');
        }
        
        // Instantiate the report controller with the requested report id
        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        $report = ReportFactory::getReportById($reportId);

        Layout::addPageTitle($report->getFriendlyName());
        
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