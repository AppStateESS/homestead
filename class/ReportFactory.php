<?php

/**
 * ReportManger - Utility class which helps with getting
 * instances of reports
 *
 * @author jbooker
 * @package hms
 */

PHPWS_Core::initModClass('hms', 'ReportController.php');

class ReportFactory {

    public static $dir = 'report';

    public static function getControllerInstance($name)
    {
        $dir = PHPWS_SOURCE_DIR . 'mod/hms/class/' . self::$dir;
        $className = $name . 'Controller';
        PHPWS_Core::initModClass('hms', self::$dir . "/$name/$className.php");
    
        return new $className;
    }
    
    //TODO (if necessary?)
    public static function getControllerInstanceByReport(Report $report)
    {
        // Look at the report's class field from the db
        
        // Instanciate the the proper controller and pass in the report
    }
    
    public static function getReportById($reportId)
    {
        // Get the class of the requested report
        $db = new PHPWS_DB('hms_report');
        $db->addColumn('report');
        $db->addWhere('id', $reportId);
        $result = $db->select('one');

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseExecption($result->toString());
        }
        
        if(is_null($result)){
            throw new InvalidArgumentException('The given report ID does not exist.');
        }
        
        self::loadReportClass($result);
        
        $report = new $result($reportId);
        
        return $report;
    }
    
    public static function loadReportClass($name)
    {
        $dir = PHPWS_SOURCE_DIR . 'mod/hms/class/' . ReportFactory::$dir;
        PHPWS_Core::initModClass('hms', ReportFactory::$dir . "/$name/$name.php");
    }
    
    /*
     * Returns an array of Report objects
     */
    public static function getAllReportControllers()
    {
        $dir = PHPWS_SOURCE_DIR . 'mod/hms/class/' . self::$dir;

        // Get the directory listing and filter out anything that doesn't look right
        $files = scandir("{$dir}/");
        $reportDirs = array();
        foreach($files as $f){
            // Look for directories that don't start with '.'
            if(is_dir($dir . '/' . $f) && substr($f, 0, 1) != '.'){
                $reportDirs[] = $f;
            }
        }

        // For each report directory, instantiate the report class
        $reportControllers = array();
        foreach($reportDirs as $r){
            $reportControllers[] = self::getControllerInstance($r);
        }

        return $reportControllers;
    }
}

?>