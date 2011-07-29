<?php

/**
 * Report class - Abstract parent class for all reports in HMS.
 *
 * @author jbooker
 * @package HMS
 */

abstract class Report {

    public $id;

    public $class; // class name of the report
    public $from_term;
    public $to_term;
    public $created_by;
    public $created_on;
    public $scheduled_exec_time; // scheduled execution start time, can be in the future for scheduled reports
    public $params;
    public $began_timestamp; // actual execution start time
    public $completed_timestamp; // execution finish time

    /**
     * Constructor
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

    public abstract function getFriendlyName();

    /**
     * Executes the report. Calculated values should be stored in
     * member variables unique to each report.
     */
    public abstract function execute();

    public function getLastExec()
    {
        $className = get_class($this);
        $obj = new $className;

        $db = new PHPWS_DB('hms_report');
        $db->addWhere('report', $obj->report);
        $db->addOrder('completed_timestamp DESC');
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

    public function getId()
    {
        return $this->id;
    }
    
    public function getCompletedTimestamp(){
        return $this->completed_timestamp;
    }

    public function getCreatedBy(){
        return $this->created_by;
    }
}

?>