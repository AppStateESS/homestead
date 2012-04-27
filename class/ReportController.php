<?php

// Location to save the generated report files
// I'd rather this be a private static or class const, but you can't
// calculate the path dynamically that way.
define('HMS_REPORT_PATH', PHPWS_SOURCE_DIR . 'files/hms_reports/');

PHPWS_Core::initModClass('hms', 'ReportInterfaces.php');
PHPWS_Core::initModClass('hms', 'Report.php');

/**
 * ReportController - Central report controller. Provides much of the functionality
 * needed to setup, execute, and save reports.
 *
 * @author jbooker
 * @package HMS
 */
abstract class ReportController {

    // The report object we're controlling/wraping.
    protected $report;

    // The full path name for the output file of this report (without an extension)
    // This is computed at run-time based on the report's completion date so that all output
    // files from the same report have the same timestamp.
    protected $fileName;

    // Local storage of view objects, can be null if not implemented by the report.
    protected $htmlView;
    protected $pdfView;
    protected $csvView;

    /**
     * Constructor
     * If a report is passed in, that report is used. Otherwise,
     * a new instance of the appropriate report (based on this controller's
     * class name) will be instantiated.
     *
     * @param Report $report Optional Report object to use.
     */
    public function __construct(Report $report = null)
    {
        if(isset($report) && !is_null($report)){
            $this->report = $report;
        }else{
            $this->report = $this->getReportInstance();
        }
    }

    /**
     * Returns the class name of the report this object wraps,
     * based on this controller's class name.
     *
     * @return String report's class name
     */
    public function getReportClassName()
    {
        return preg_replace("/Controller$/", '', get_class($this));
    }

    /**
     * Returns a new instance of of the given report name.
     *
     * @return Report New instnace of this controller's report.
     */
    private function getReportInstance()
    {
        $name = $this->getReportClassName();
        ReportFactory::loadReportClass($name);

        return new $name;
    }

    /**
    * Initalizes the report object we're wrapping. Sets creation dates/users.
    *
    * @param int $scheduledExecTime Unix timestamp of the time this report should be executed.
    */
    public function newReport($scheduledExecTime)
    {
        $this->report = $this->getReportInstance();
    
        $this->report->setCreatedBy(UserStatus::getUsername());
        $this->report->setCreatedOn(time());
        $this->report->setScheduledExecTime($scheduledExecTime);
        $this->report->setBeganTimestamp(null);
        $this->report->setCompletedTimestamp(null);
    }
    
    /**
     * Returns the friendly name of the report we're wrapping. It's a shortcut
     * method for $this->report->getFriendlyName().
     *
     * @return String This report's friendly name.
     */
    public function getFriendlyName()
    {
        return $this->report->getFriendlyName();
    }

    /**
     * Responsible for returning a View to be used on the ReportListView. Can be
     * overridden to provide a custom menu item view, but must return an objects
     * that extends the View class.
     *
     * @return View - View object to use as the report's menu item
     */
    public function getMenuItemView()
    {
        $this->loadLastExec();

        PHPWS_Core::initModClass('hms', 'ReportMenuItemView.php');
        $view = new ReportMenuItemView($this->report, $this->getReportClassName());

        return $view;
    }

    /**
     * Returns a string of HTML that describes the report (what it does, etc).
     * 
     * Default implementation looks for a file in hms/templates/admin/reports/<reportName>Desc.tpl
     * 
     * @return String String of HTML for report's description, or null if the default file wasn't found.
     */
    public function getDescription()
    {
        $fileName = PHPWS_SOURCE_DIR . 'mod/hms/templates/admin/reports/' . $this->getReportClassName() . 'Desc.tpl';
        
        if(file_exists($fileName)){
            return file_get_contents($fileName);
        }else{
            return null;
        }
    }
    
    /**
     * 
     */
    public function getSyncSetupView()
    {
        //TODO, and make the details view use this
    }

    /**
     * Returns the ReportSetupView to show the UI for running
     * this report in the background.
     * 
     * The default implementation (for iSyncReport) expects a class named
     * '<reportName>SetupView.php, which extends ReportSetupView to be in
     * the report's class directory. This functionality can be overridden
     * by each report to return a different object, but it must return an
     * object of type ReportSetupView.
     *  
     * 
     * @see iSyncReport
     * @see ReportSetupView
     * @param bool $datePicker Whether or not the interface should show a date picker
     * @return ReportSetupView The ReportSetupView responsible for showing the UI to run this report.
     */
    public function getAsyncSetupView()
    {
        PHPWS_Core::initModClass('hms', 'ReportSetupView.php');
        
        $view = new ReportSetupView($this->report);
        $view->setLinkText('Run in background');
        $view->setDialogId('reportBgDialog');
        $view->setRunNow(true);
        $view->setFormId('report-setup-form-bg');
        
        return $view;
    }

    /**
     * Returns the ReportSetupView to show the UI for scheduling
     * the report.
     * 
     * Default implementation just calls the getAsyncSetupView
     * method with the datePicker = true parameter.
     * 
     * @see getAsyncSetupView
     * @return ReportSetupView - ReportSetupView for scheudling this report.
     */
    public function getSchedSetupView()
    {
        $view = $this->getAsyncSetupView();
        $view->setLinkText('Schedule run');
        $view->setDialogId('reportSchedDialog');
        $view->useDatePicker(true);
        $view->setRunNow(false);
        $view->setFormId('report-setup-form-sched');
        return $view;
    }

    /**
     * Default implementation for the iSyncReport interface.
     * This can be overridden in a sub-class to provide a custom
     * execution command. It must return a valid Command object
     * to run the report synchronously.
     *
     * @see iSyncReport
     * @return Command A Command object to run this report synchronously.
     */
    public function getSyncExecCmd()
    {
        $cmd = CommandFactory::getCommand('ExecReportSync');
        $cmd->setReportClass($this->getReportClassName());

        return $cmd;
    }

    /**
     * Sets the parameters for this report. Must be implemented
     * by each controller. This method must take the values
     * in the passed-in array and assign them to the proper
     * member variables withthin the report.
     * 
     * @param array $params
     */
    public abstract function setParams(Array $params);
    
    /**
     * @return Array An Array of key=>value parameters for this report
     */
    public abstract function getParams();

    /**
     * Shortcut method to save the report object that this controller contains.
     */
    public function saveReport()
    {
        return $this->report->save();
    }

    /**
     * Get's the file name from the Report this controller is wrapping and
     * prepends the configured filesystem path. The file name that's generated
     * will use the current date/time at the time this method is called.
     */
    public function getFileName()
    {
        // Ask the report for it's file name
        $this->fileName = HMS_REPORT_PATH . $this->report->getFileName();
    }

    /**
     * Responsible for starting the execution of this controller's
     * report, then getting and saving each of the implemented views.
     *
     * This is a default implementation, and could be overridden if necessary.
     *
     */
    public function generateReport()
    {
        // Set the start time
        $this->report->setBeganTimestamp(time());
        
        // Save that timestamp
        $this->report->save();

        // Execute the report
        $this->report->execute();

        /*
         * Generate the report's full pathname (with a file extension),
        * so that each output file type will have the same file name.
        */
        $this->getFileName();

        /*
         * For each of the views we have, check to see if this controller
        * implements the necessary interface. If so, call those methods
        * to generate and save the view.
        */
        // HTML
        if($this instanceof iHtmlReportView){
            $this->htmlView = $this->getHtmlView();
            // Save the HTML output
            $this->saveHtmlOutput($this->htmlView);
        }

        // PDF
        if($this instanceof iPdfReportView){
            $this->pdfView = $this->getPdfView();
            // Save the PDF output
            $this->savePdfOutput($this->pdfView);
        }

        // CSV
        if($this instanceof iCsvReportView){
            $this->csvView = $this->getCsvView();
            $this->saveCsvOutput($this->csvView);
        }
        
        // Set the completion time
        $this->report->setCompletedTimestamp(time());
        
        // Save the report to save the completed timestamp
        $this->report->save();
    }

    /**
     * Default implementation of the iHtmlReportView interface. Returns
     * a HTML view based on the report's class name in the form of:
     * report/<reportName>/<reportName>HtmlView.php
     *
     * The generated class name must extend ReportHtmlView.
     *
     * @see iHtmlReportView
     * @return ReportHtmlView
     */
    public function getHtmlView()
    {
        PHPWS_Core::initModClass('hms', 'ReportHtmlView.php');

        $name = $this->getReportClassName();
        $className = $name . "HtmlView";
        PHPWS_Core::initModClass('hms', "report/$name/$className.php");

        return new $className($this->report);
    }

    /**
     * Default implementation for the iHtmlReportView interface. Responsible
     * for appending a file extention to the file name, getting the output
     * from the provided view, saving that output to a file, and storing the
     * finished file name in the Report object.
     *
     * Can be overrriden if custom behavior is necessary.
     *
     * @see iHtmlReportView
     * @param ReportHtmlView $htmlView
     */
    public function saveHtmlOutput(ReportHtmlView $htmlView)
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

    /**
     * Default implementation for the iPdfReportView interface. Responsible
     * for returning an object which extends the ReportPdfView class.
     *
     * This implementation expects a htmlView to exist and attempts to convert
     * it to PDF using the WKPDF class. Override this to provide your own ReportPdfView
     * implementation which generates a custom PDF for this controller's report.
     *
     * @see iHtmlReportView
     * @return ReportPdfView
     */
    public function getPdfView()
    {
        PHPWS_Core::initModClass('hms', 'ReportPdfViewFromHtml.php');

        //TODO Check to make sure a HtmlView actually exists

        $pdfView = new ReportPdfViewFromHtml($this->report, $this->htmlView);

        return $pdfView;
    }

    /**
     * Default implementation for the iPdfReportView interface. Responsible
     * for appending a file extention to the file name, getting the output
     * from the provided view, saving that output to a file, and storing the
     * finished file name in the Report object.
     *
     * Can be overrriden if custom behavior is necessary.
     *
     * @see iHtmlReportView
     * @param ReportPdfView $pdfView
     */
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

    /**
     * Default implementation for the iCsvReportView interface. Responsible for
     * returning an object which extends the ReportCsvView class.
     *
     * By default, it returns an instance of ReportCsvView. This can be overridden
     * to return a custom view (but it must extend ReportCsvView). This view
     * provides a default implementation of the iCsvReport interface to convert
     * report data to the csv format from an array.
     *
     * @see iHtmlCsvView
     * @return ReportCsvView
     */
    public function getCsvView(){
        PHPWS_Core::initModClass('hms', 'ReportCsvView.php');

        $csvView = new ReportCsvView($this->report);

        return $csvView;
    }

    /**
     * Default implementation for the iCsvReportView interface. Responsible
     * for appending a file extention to the file name, getting the output
     * from the provided view, saving that output to a file, and storing the
     * finished file name in the Report object.
     *
     * Can be overrriden if custom behavior is necessary.
     *
     * @see iHtmlCsvView
     * @param ReportCsvView $csvView
     */
    public function saveCsvOutput(ReportCsvView $csvView)
    {
        // Add the proper extension
        $fileName = $this->fileName . '.csv';

        $fileResult = file_put_contents($fileName, $csvView->getOutput());

        // Save the file name to the report
        $this->report->setCsvOutputFilename($fileName);
        $this->report->save();
    }

    /**
     * Shortcut method to return the Command for the default
     * viewing method for this report. This can be overridden
     * to provide custom behavior, or to overrride the report's
     * requested behavior.
     *
     * @return Command Default command for viewing this report's output
     */
    public function getDefaultOutputViewCmd()
    {
        return $this->report->getDefaultOutputViewCmd();
    }

    /**
     * Loads the report instance for the last execution performed.
     * The loaded object will contain a null ID if the report has
     * never been executed. Returns true/false on success/failure.
     *
     * @throws DatabaseException
     * @return boolean
     */
    public function loadLastExec()
    {
        $db = new PHPWS_DB('hms_report');
        $db->addWhere('report', $this->getReportClassName());
        $db->addWhere('completed_timestamp', 'NULL', 'IS NOT');
        $db->addOrder('completed_timestamp DESC');
        $db->setLimit(1);
        $result = $db->loadObject($this->report);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
            return false;
        }

        return true;
    }

    /**
     * Returns the report instance that this controller is managing.
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }
    
    /**
     * Saves the parameters from this report to the database.
     * 
     * @throws DatabaseException
     */
    public function saveParams()
    {
        $params = $this->getParams();

        if(empty($params)){
            return;
        }
        
        $db = new PHPWS_DB('hms_report_param');
        
        foreach($params as $key=>$value){
            $db->reset();
            $db->addValue('report_id', $this->report->getId());
            $db->addValue('param_name', $key);
            $db->addValue('param_value', $value);
            $result = $db->insert();
            
            if(PHPWS_Error::logIfError($result)){
                throw new DatabaseException($result->toString());
            }
        }
    }
    
    /**
     * Loads the parameters for this report from the database.
     * 
     * @throws DatabaseException
     */
    public function loadParams()
    {
        $db = new PHPWS_DB('hms_report_param');
        $db->addWhere('report_id', $this->report->getId());
        
        $results = $db->select();
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException($results->toString());
        }

        if(is_null($results) || empty($results)){
            echo 'empty params!';
            return;
        }
        
        $params = array();
        
        foreach($results as $result){
            $params[$result['param_name']] = $result['param_value'];
        }
        
        $this->setParams($params);
    }
}

?>