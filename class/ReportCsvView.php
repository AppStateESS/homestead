<?php

namespace Homestead;
PHPWS_Core::initModClass('hms', 'HMS_Util.php');

/**
 * ReportCsvView class
 *
 * Provides a default implementation of CSV output.
 * Uses the iCsvReport interface to ensure it can call
 * the necessary methods to get the CSV data from the report.
 *
 * @see iCsvReport
 * @author jbooker
 * @package HMS
 */
class ReportCsvView extends ReportView {

    /**
     * Constructor
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        parent::__construct($report);


    }

    /**
     * Returns the full CSV output as one long string.
     *
     * @return String CSV output
     */
    public function getOutput()
    {
        // Get the columns from the report
        $columns = $this->report->getCsvColumnsArray();

        // Add a copule of meta-data fields
        $columns[] = HMS_Util::get_long_date_time($this->report->getCompletedTimestamp());
        $columns[] = $this->report->getCreatedBy();
        $columns[] = Term::toString($this->report->getTerm());

        $output = self::sputcsv($columns);

        $rows = $this->report->getCsvRowsArray();
        if(isset($rows) && !empty($rows)){
            foreach($rows as $cols){
                $output .= self::sputcsv($cols);
            }
        }

        return $output;
    }

    /**
     * Handles writing an array to a comma-separated string
     *
     * @param Array $row Array of values to write
     * @param char $delimiter
     * @param char $enclosure
     * @param char $eol
     */
    private static function sputcsv(Array $row, $delimiter = ',', $enclosure = '"', $eol = "\n")
    {
        static $fp = false;
        if ($fp === false)
        {
            $fp = fopen('php://temp', 'r+'); // see http://php.net/manual/en/wrappers.php.php - yes there are 2 '.php's on the end.
            // NB: anything you read/write to/from 'php://temp' is specific to this filehandle
        }
        else
        {
            rewind($fp);
        }

        if (fputcsv($fp, $row, $delimiter, $enclosure) === false)
        {
            return false;
        }

        rewind($fp);
        $csv = fgets($fp);

        if ($eol != PHP_EOL)
        {
            $csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
        }

        return $csv;
    }
}
