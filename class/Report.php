<?php

/**
 * Report class - Abstract parent class for all reports in HMS.
 *
 * @author jbooker
 *
 */

abstract class Report {

    public $id;

    public $report; // class name of the report
    public $format; // eg. html, csv, pdf
    public $from_term;
    public $to_term;
    public $exec_timestamp; // execution start time, can be in the future for scheduled reports
    public $exec_by_user_id;

    public $content; // content/output of the report

    /**
     *
     */
    public function __construct($id = 0)
    {
        if($id != 0){
            $this->load();
            return;
        }

        $this->report = get_class($this);
    }

    /**
     * Loads this report (and all its outout, if any) from the database.
     */
    public function load()
    {
        $db = new PHPWS_DB('hms_report_exec');
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
        $db = new PHPWS_DB('hms_report_exec');

        $this->stamp();

        $result = $db->saveObject($this);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
            return FALSE;
        }
        return TRUE;
    }

    public function delete() {
        $db = new PHPWS_DB('hms_report_exec');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
            return FALSE;
        }

        return TRUE;
    }

    public abstract function getFriendlyName();

    /**
     * Returns the form or view necessary to configure this report.
     * The form data (user input) will be made available to the execute function.
     * Returns null if no setup view is necessary.
     */
    public abstract function getSetupView();

    /**
     * Executes the report. Calculated values should be stored in
     * member variables unique to each report.
     */
    public abstract function execute();

    /**
     * Returns the view for this report's data.
     */
    public abstract function getReportView();

    /**
     * Finds/calculates and saves to the database any data
     * that this report may need to record periodically in order
     * to be processed/reported on later.
     */
    public abstract function savePeriodicData();

    /**
     * Schedules this report to be run at some point in the future.
     * @param int $timestamp - unix timestamp of when this report should be run
     */
    public function schedule($timestamp = NULL)
    {
        if(is_null($timestamp)){
            $timestamp = mktime();
        }

        //TODO
    }

    public function unSchedule()
    {

    }

    public function getLastExec()
    {
        $className = get_class($this);
        $obj = new $className;

        $db = new PHPWS_DB('hms_report_exec');
        $db->addWhere('report', $obj->report);
        $db->addOrder('exec_timestamp DESC');
        $db->setLimit(1);
        $result = $db->loadObject($obj);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
            return false;
        }

        if(is_null($obj->id)){
            return null;
        }else{
            return $obj;
        }
    }

    /**
     * Row tags function for DB pager
     */
    public function getRowTags()
    {
        $tags = array();
        $tags['TITLE'] = $this->title;
        $tags['EXEC_DATE'] = $this->exec_timestamp;
        $tags['EXEC_BY'] = $this->exec_by_user_id;

        //TODO
        $tags['actions'] = "Actions...";

        return $tags;
    }

    /*********
     * Getters and setters
     */

    public function getExecTimestamp(){
        return $this->exec_timestamp;
    }

    public function getExecUserId(){
        return $this->exec_by_user_id;
    }
}

?>