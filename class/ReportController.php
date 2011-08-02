<?php

abstract class ReportController {

    private $report;

    public function __construct(Report $report = null)
    {
        if(isset($report) && !is_null($report)){
            $this->report = $report;
        }else{
            $this->report = $this->getReportInstance();
        }
    }

    private function getReportClassName()
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
        $dir = PHPWS_SOURCE_DIR . 'mod/hms/class/' . ReportFactory::$dir;
        PHPWS_Core::initModClass('hms', ReportFactory::$dir . "/$name/$name.php");

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
    
    /**
     * Returns the form or view necessary to configure this report.
     * The form data (user input) will be made available to the execute function.
     * Returns null if no setup view is necessary.
     */
    public function getSetupView(){
        return null;
    }

    public function setupRunNowCommand(Command $cmd)
    {
        $cmd->setReportName($this->getReportClassName());
    }

    public function scheduleForLater()
    {

    }

    public function generateReport()
    {
        // Execute the report

        // Pass the report to each of the views, save the output
    }

    public abstract function execute();

    public abstract function getHtmlView();

    public abstract function getPdfView();

    public abstract function getCsvView();

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