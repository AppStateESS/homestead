<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

/**
 * iCsvReport Interface
 * Enforces the methods necessary for the ReportCsvView to retrieve the CSV data from the implementing report class.
 *
 * @author jbooker
 * @package HMS
 */
interface iCsvReport {
    /**
     * Returns an array of column names, used to make the csv file header line
     */
    public function getCsvColumnsArray();

    /**
     * Returns a two-dimensional array of data rows, each containing the columns values for that row
     */
    public function getCsvRowsArray();
}

/**
 * Report class - Abstract parent class for all reports in HMS.
 *
 * @author jbooker
 * @package HMS
 */
abstract class Report {

    const category = 'Uncategorized';

    public $id;

    public $report; // class name of the report
    public $created_by; // string user name
    public $created_on; // unix timestamp of creation time
    public $scheduled_exec_time; // scheduled execution start time, can be in the future for scheduled reports
    public $began_timestamp; // actual execution start time
    public $completed_timestamp; // execution finish time

    /*
     * Full path file names for the generated output,
     * can be null if a format isn't used.
     */
    public $html_output_filename;
    public $pdf_output_filename;
    public $csv_output_filename;

    /**
     * Constructor
     */
    public function __construct($id = 0)
    {
        if($id != 0){
            $this->id = $id;
            $this->load();
            return;
        }

        // Initalize values
        $this->report = get_class($this);
    }

    /**
     * Returns the "friendly" (long) name of the report.
     * The constant 'const friendlyName = "name"' must be
     * declared in the implementing class. This is shown to
     * the user in lots of places.
     *
     * @return String friendly name
     */
    public static function getFriendlyName(){
        $c = get_called_class();
        return $c::friendlyName;
    }

    /**
     * Returns the category of the report.
     * The constant category must be
     * declared in the implementing class.
     *
     * @return String category
     */
    public static function getCategory(){
        $c = get_called_class();
        return $c::category;
    }

    /**
    * Returns the "short" name of the report.
    * The constant 'const friendlyName = "name"' must be
    * declared in the implementing class. This is stored
    * in the database and used filtering/selecting later.
    *
    * @return String short name
    */
    public static function getShortName(){
        $c = get_called_class();
        return $c::shortName;
    }

    /**
     * Returns the class name of this report.
     * @return String class name of this report.
     */
    public function getClass()
    {
        return get_class($this);
    }

    /**
     * Loads this report from the database.
     *
     * @throws DatabaseException
     */
    public function load()
    {
        $db = new PHPWS_DB('hms_report');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
    }

    /**
     * Save a report to the database.
     *
     * @throws DatabaseException
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_report');
        $result = $db->saveObject($this);
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Deletes a record.
     *
     * @throws DatabaseException
     */
    public function delete() {
        $db = new PHPWS_DB('hms_report');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Returns the filename for this report based on the 'shortName'
     * field and the current date/time.
     */
    public function getFileName()
    {
        return $this->getShortName() . '-'. date("Ymd-His",time());
    }

    /**
     * Executes the report. Calculated values should be stored in
     * member variables unique to each report. Must be implemented
     * by each report.
     */
    public abstract function execute();


    public function getRelativeLastRun()
    {
        return HMS_Util::relativeTime($this->getCompletedTimestamp());
    }

    public function getLastRunUser(){
        return $this->getCreatedBy();
    }

    /**
     * Returns the DBPager tags used for showing each record on the ReportDetailView.
     *
     * @return Array DBPager tags for this report
     */
    public function historyPagerRowTags()
    {
        $tags = array();
        $tags['COMPLETION_DATE'] = HMS_Util::get_long_date_time($this->getCompletedTimestamp());

        // Get the HTML view, if available
        if(!is_null($this->html_output_filename)){
            $htmlCmd = CommandFactory::getCommand('ShowReportHtml');
            $htmlCmd->setReportId($this->getId());
            $tags['HTML'] = $htmlCmd->getURI();
        }

        if(!is_null($this->pdf_output_filename)){
            $pdfCmd = CommandFactory::getCommand('ShowReportPdf');
            $pdfCmd->setReportId($this->getId());
            $tags['PDF'] = $pdfCmd->getURI();
        }

        if(!is_null($this->csv_output_filename)){
            $csvCmd = CommandFactory::getCommand('ShowReportCsv');
            $csvCmd->setReportId($this->id);
            $tags['CSV'] = $csvCmd->getURI();
        }

        $tags['ACTIONS'] = '';

        return $tags;
    }


    /**
     * Returns the DBPager tags used for showing scheduled execution records
     * on the ReportDetailView.
     *
     * @return Array DBPager tags for this report
     */
    public function schedulePagerRowTags()
    {
        $tags = array();
        $tags['SCHEDULE_DATE'] = HMS_Util::get_long_date_time($this->getScheduledExecTime());


        $actions = array();

        $cancelCmd = CommandFactory::getCommand('CancelReport');
        $cancelCmd->setReportId($this->getId());

        $actions[] = $cancelCmd->getLink('cancel');

        $tags['ACTIONS'] = implode(' ', $actions);

        return $tags;
    }

    /**
     * Returns the Command object to use for the default viewing method
     * for the generated output, setup with the appropriate params for
     * this report instance. Can be overwridden by individual reports
     * to change this behavior.
     *
     * @return Command Default command for viewing this report's output.
     */
    public function getDefaultOutputViewCmd()
    {
        $cmd = CommandFactory::getCommand('ShowReportHtml');
        $cmd->setReportId($this->id);

        return $cmd;
    }

    /*********
     * Getters and setters
     */

    public function getId(){
        return $this->id;
    }

    public function getCreatedBy(){
        return $this->created_by;
    }

    public function setCreatedby($username){
        $this->created_by = $username;
    }

    public function getCreatedOn(){
        return $this->created_on;
    }

    public function setCreatedOn($timestamp){
        $this->created_on = $timestamp;
    }

    public function getScheduledExecTime(){
        return $this->scheduled_exec_time;
    }

    public function setScheduledExecTime($timestamp){
        $this->scheduled_exec_time = $timestamp;
    }

    public function getBeganTimestamp(){
        return $this->began_timestamp;
    }

    public function setBeganTimestamp($timestamp){
        $this->began_timestamp = $timestamp;
    }

    public function getCompletedTimestamp(){
        return $this->completed_timestamp;
    }

    public function setCompletedTimestamp($timestamp){
        $this->completed_timestamp = $timestamp;
    }

    public function getHtmlOutputFilename(){
        return $this->html_output_filename;
    }

    public function setHtmlOutputFilename($fileName){
        $this->html_output_filename = $fileName;
    }

    public function getPdfOutputFilename(){
        return $this->pdf_output_filename;
    }

    public function setPdfOutputFilename($fileName){
        $this->pdf_output_filename = $fileName;
    }

    public function getCsvOutputFilename(){
        return $this->csv_output_filename;
    }

    public function setCsvOutputFilename($fileName){
        $this->csv_output_filename = $fileName;
    }
}
