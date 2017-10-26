<?php

namespace Homestead;

/**
 * ReportManger - Utility class which helps with getting
 * instances of reports
 *
 * @author jbooker
 * @package hms
 */

class ReportFactory {

    public static $dir = 'Report';

    /**
     * Returns an instance of the corresponding ReportController given a report class name.
     *
     * @param String $reportName Report class name.
     * @return ReportContoller A ReportController object of the sub-class for the given report class.
     */
    public static function getControllerInstance($reportName)
    {
        $ctrlClassName = '\\Homestead\\Report\\' . $reportName . '\\' . $reportName . 'Controller';
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
        $ctrlClassName = '\\Homestead\\Report\\' . $report->getClass() . '\\' . $report->getClass() . 'Controller';
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
     * @throws DatabaseException
     * @throws \InvalidArgumentException
     */
    public static function getReportById($reportId)
    {
        // Get the class of the requested report
        $db = new \PHPWS_DB('hms_report');
        $db->addColumn('report');
        $db->addWhere('id', $reportId);
        $result = $db->select('one');

        if(\PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if(is_null($result)){
            throw new \InvalidArgumentException('The given report ID does not exist.');
        }

        $classResult = '\\Homestead\\Report\\' . $result . '\\' . $result;
        $report = new $classResult($reportId);

        return $report;
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
            $ctrlClassName = '\\Homestead\\Report\\' . $r . '\\' . $r . 'Controller';
            $reportControllers[] = new $ctrlClassName;
        }

        return $reportControllers;
    }
}
