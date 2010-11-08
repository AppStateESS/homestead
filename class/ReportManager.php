<?php

/**
 * ReportManger - Utility class which helps with getting
 * instances of reports
 *
 * @author jbooker
 * @package hms
 */

class ReportManager {

    private static $dir = 'report';

    /*
     * Returns an array of Report objects
     */
    public static function getReports()
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
        $reports = array();
        foreach($reportDirs as $r){
            $reports[] = self::getReportInstance($r);
        }

        return $reports;
    }

    /**
     * Returns an instance of of the given report name.
     * @param String $name - Name of report object
     */
    public static function getReportInstance($name)
    {
        $dir = PHPWS_SOURCE_DIR . 'mod/hms/class/' . self::$dir;
        PHPWS_Core::initModClass('hms', self::$dir . "/$name/$name.php");

        return new $name;
    }

}

?>