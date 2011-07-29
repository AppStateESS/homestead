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
    
    public static function getControllerInstanceByReport(Report $report)
    {
        // Look at the report's class field from the db
        
        // Instanciate the the proper controller and pass in the report
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