<?php

/**
 * CancelReportCommand
 * 
 * Controller for canceling a pending report.
 * 
 * @author jbooker
 * @package HMS
 */
class CancelReportCommand extends Command {
    
    private $reportId;
    
    public function setReportId($id){
        $this->reportId = $id;
    }
    
    public function getRequestVars()
    {
        if(!isset($this->reportId)){
            throw new InvalidArgumentException('Missing report id.');
        }
        
        return array('action'=>'CancelReport', 'reportId'=>$this->reportId);
    }
    
    public function execute(CommandContext $context)
    {
        // Check for report ID
        $reportId = $context->get('reportId');
        
        if(!isset($reportId) || is_null($reportId)){
            throw new InvalidArgumentException('Missing report id.');
        }
        
        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        
        // Load the report to get its class
        $report = ReportFactory::getReportById($reportId);
        
        $db = new PHPWS_DB('hms_report');
        $db->addWhere('id', $reportId);
        $result = $db->delete();
        
        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }
        
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Report canceled.');
        
        $cmd = CommandFactory::getCommand('ShowReportDetail');
        $cmd->setReportClass($report->getClass());
        $cmd->redirect();
    }
}

?>