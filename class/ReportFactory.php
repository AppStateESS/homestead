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

    /**
     * Returns the class name for the controller based on the report's class name.
     * 
     * @param String $reportName Class name of a report.
     * @return String Class name of the given report's controller class.
     */
    private static function getControllerClassName($reportName)
    {
        $dir = PHPWS_SOURCE_DIR . 'mod/hms/class/' . self::$dir;
        $className = $reportName . 'Controller';
        
        return $className;
    }
    
    /**
     * Loads the controller class for a given report.
     * 
     * @param String $reportName
     * @param String $controllerName
     */
    private static function loadControllerClass($reportName, $controllerName)
    {
        PHPWS_Core::initModClass('hms', self::$dir . "/$reportName/$controllerName.php");
    }
    
    /**
     * Returns an instance of the corresponding ReportController given a report class name.
     * 
     * @param String $reportName Report class name.
     * @return ReportContoller A ReportController object of the sub-class for the given report class.
     */
    public static function getControllerInstance($reportName)
    {
        $ctrlClassName = self::getControllerClassName($reportName);
        self::loadControllerClass($reportName, $ctrlClassName);
        
        return new $ctrlClassName;
    }
    
    /**
     * Loads the proper ReportController sub-class, creates an instance
     * of that controller class, and initializes the controller with the given report.
     * 
     * @param Report $report
     * @return ReportController An instance of the proper ReportController sub-class, initialized with the given report object.
     */
    public static function getControllerInstanceByReport(Report $report)
    {
        $ctrlClassName = self::getControllerClassName($report->getClass());
        self::loadControllerClass($report->getClass(), $ctrlClassName);
        
        return new $ctrlClassName($report);
    }
    
    /**
     * Returns a ReportController object initialized with the Report object
     * identified by the given id.
     * 
     * @param integer $reportId
     * @return ReportController
     */
    public static function getControllerById($reportId)
    {
        // Get the report object by ID
        $report = self::getReportById($reportId);
        
        // Instanciate the the proper controller
        return self::getControllerInstanceByReport($report);
    }
    
    /**
     * Returns the Report object identified by the given id.
     * 
     * @param integer $reportId
     * @return Report
     * @throws DatabaseExecption
     * @throws InvalidArgumentException
     */
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
    
    /**
     * Loads the class file for the given report class name.
     * 
     * @param String $name Report class name.
     */
    public static function loadReportClass($name)
    {
        $dir = PHPWS_SOURCE_DIR . 'mod/hms/class/' . ReportFactory::$dir;
        PHPWS_Core::initModClass('hms', ReportFactory::$dir . "/$name/$name.php");
    }
    
    /**
     * Returns an array of Report objects
     * 
     * @return array An array of all available ReportController objects.
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

