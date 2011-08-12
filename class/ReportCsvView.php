<?php

PHPWS_Core::initModClass('hms', 'ReportView.php');
PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class ReportCsvView extends ReportView {
    
    public function __construct(Report $report)
    {
        parent::__construct($report);
        
        
    }
    
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
    
    private static function sputcsv($row, $delimiter = ',', $enclosure = '"', $eol = "\n")
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

?>