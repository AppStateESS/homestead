<?php

// Location to save the generated report files
// I'd rather this be a private static or class const, but you can't
// calculate the path dynamically that way.
define('HMS_REPORT_PATH', PHPWS_SOURCE_DIR . 'files/hms_reports/');

PHPWS_Core::initModClass('hms', 'Report.php');

abstract class ReportController {

    protected $report;
    
    protected $fileName;
    protected $htmlView;
    
    public function __construct(Report $report = null)
    {
        if(isset($report) && !is_null($report)){
            $this->report = $report;
        }else{
            $this->report = $this->getReportInstance();
        }
    }

    public function getReportClassName()
    {
        return preg_replace("/Controller$/", '', get_class($this));
    }

    /**
     * Returns a new instance of of the given report name.
     * @param String $name - Name of report object
     */
    private function getReportInstance()
    {
        $name = $this->getReportClassName();
        ReportFactory::loadReportClass($name);

        return new $name;
    }

    public function getFriendlyName()
    {
        return $this->report->getFriendlyName();
    }

    public function getMenuItemView()
    {
        $this->loadLastExec();

        PHPWS_Core::initModClass('hms', 'ReportMenuItemView.php');
        $view = new ReportMenuItemView($this->report, $this->getReportClassName());

        return $view->show();
    }

    public static function allowSyncExec()
    {
        $c = get_called_class();
        return $c::allowSyncExec;
    }

    public static function allowAsyncExec()
    {
        $c = get_called_class();
        return $c::allowAsyncExec;
    }

    public static function allowScheduledExec()
    {
        $c = get_called_class();
        return $c::allowScheduleExec;
    }

    public function getSyncExecCmd()
    {
        if($this->allowSyncExec()){
            $cmd = CommandFactory::getCommand('ExecReportSync');
            $cmd->setReportClass($this->getReportClassName());
        }else{
            $cmd = null;
        }

        return $cmd;
    }

    /**
     * Returns the form or view necessary to configure this report.
     * The form data (user input) will be made available to the execute function.
     * Returns null if no setup view is necessary.
     */
    public function getSetupView(){
        return null;
    }

    public function newReport($scheduledExecTime)
    {
        $this->report = $this->getReportInstance();

        $this->report->setCreatedBy(UserStatus::getUsername());
        $this->report->setCreatedOn(time());
        $this->report->setScheduledExecTime($scheduledExecTime);
        $this->report->setBeganTimestamp(null);
        $this->report->setCompletedTimestamp(null);
    }

    public abstract function setParamsFromContext(CommandContext $context);
    
    public function saveReport()
    {
        return $this->report->save();
    }

    public function scheduleForLater()
    {

    }

    public function generateReport()
    {
        // Set the start time
        $this->report->setBeganTimestamp(time());
        
        // Execute the report
        $this->report->execute();

        // Set the completion time (Maybe move this to the end of this function?)
        $this->report->setCompletedTimestamp(time());
        
        // Save the report so we're sure to save the timestamps
        // Might remove this if report generation is stable
        $this->report->save();
        
        $this->getFileName();
        
        /*
         * Pass the report to each of the views, save the output
         */
        // HTML
        $this->htmlView = $this->getHtmlView();

        // Save the HTML output
        $this->saveHtmlOutput($this->htmlView);
        
        // PDF
        $this->pdfView = $this->getPdfView();
        $this->savePdfOutput($this->pdfView);
        
        // CSV
        //TODO
    }

    public function getFileName()
    {
        // Ask the report for it's file name
        $this->fileName = HMS_REPORT_PATH . $this->report->getFileName();
    }
    
    public function getHtmlView()
    {
        PHPWS_Core::initModClass('hms', 'ReportView.php');
        
        $name = $this->getReportClassName();
        $className = $name . "HTMLView";
        PHPWS_Core::initModClass('hms', "report/$name/$className.php");
        
        return new $className($this->report);
    }
    
    public function saveHtmlOutput(ReportView $htmlView)
    {
        // Add the proper extension
        $fileName = $this->fileName . '.html';
        
        $fileResult = file_put_contents($fileName, $htmlView->show());
        
        if($fileResult === FALSE){
            //TODO throw exception
        }
        
        // Save the file name to the report
        $this->report->setHtmlOutputFilename($fileName);
        $this->report->save();
    }
    
    public function getPdfView()
    {
        PHPWS_Core::initModClass('hms', 'ReportPdfView.php');
        
        $pdfView = new ReportPdfView($this->report); 
        $pdfView->setHtmlView($this->htmlView);
        
        return $pdfView;
    }
    
    public function savePdfOutput(ReportPdfView $pdfView)
    {
        // Add the proper extension
        $fileName = $this->fileName . '.pdf';

        $pdfView->render();
        
        $fileResult = file_put_contents($fileName, $pdfView->getPdfContent());
        
        // Save the file name to the report
        $this->report->setPdfOutputFilename($fileName);
        $this->report->save();
    }

    public abstract function getCsvView();

    public function getDefaultOutputViewCmd()
    {
        return $this->report->getDefaultOutputViewCmd();
    }

    public function loadLastExec()
    {
        $db = new PHPWS_DB('hms_report');
        $db->addWhere('report', $this->getReportClassName());
        $db->addOrder('completed_timestamp DESC');
        $db->setLimit(1);
        $result = $db->loadObject($this->report);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
            return false;
        }
    }

    public function getReport()
    {
        return $this->report;
    }
}

?>