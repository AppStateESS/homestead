<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

/**
 * Report class - Abstract parent class for all reports in HMS.
 *
 * @author jbooker
 * @package HMS
 */

// Can't be abstract because the DB class must be able to instanciate it
abstract class Report {

    public $id;

    public $report; // class name of the report
    public $created_by;
    public $created_on;
    public $scheduled_exec_time; // scheduled execution start time, can be in the future for scheduled reports
    public $began_timestamp; // actual execution start time
    public $completed_timestamp; // execution finish time
    
    public $html_output_filename;
    public $pdf_output_filename;
    public $csv_output_filename;

    private $params;

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
    
    public static function getFriendlyName(){
        $c = get_called_class();
        return $c::friendlyName;
    }
    
    public static function getShortName(){
        $c = get_called_class();
        return $c::shortName;
    }

    public function getClass()
    {
        return get_class($this);
    }
    
    /**
     * Loads this report from the database.
     */
    public function load()
    {
        $db = new PHPWS_DB('hms_report');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }

    /**
     * Save a report that has been executed to the database.
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_report');
        $result = $db->saveObject($this);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
            return FALSE;
        }
        return TRUE;
    }

    public function delete() {
        $db = new PHPWS_DB('hms_report');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
            return FALSE;
        }

        return TRUE;
    }
    
    public function getFileName()
    {
        return $this->getShortName() . '-'. date("Ymd-His",time());
    }

    /**
     * Executes the report. Calculated values should be stored in
     * member variables unique to each report.
     */
    public abstract function execute();

    public function historyPagerRowTags()
    {
        $tags = array();
        $tags['COMPLETION_DATE'] = HMS_Util::get_long_date_time($this->getCompletedTimestamp());
        
        // Get the HTML view, if available
        if(!is_null($this->html_output_filename)){
            $htmlCmd = CommandFactory::getCommand('ShowReportHtml');
            $htmlCmd->setReportId($this->getId());
            $tags['HTML'] = $htmlCmd->getLink('html');
        }else{
            $tags['HTML'] = '';
        }
        
        if(!is_null($this->pdf_output_filename)){
            $pdfCmd = CommandFactory::getCommand('ShowReportPdf');
            $pdfCmd->setReportId($this->getId());
            $tags['PDF'] = $pdfCmd->getLink('pdf');
        }else{
            $tags['PDF'] = '';
        }
        
        if(!is_null($this->csv_output_filename)){
            //TODO csv view cmd
            $tags['CSV'] = 'csv exists'; 
        }
        
        $tags['ACTIONS'] = '';

        return $tags;
    }
    
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

?>