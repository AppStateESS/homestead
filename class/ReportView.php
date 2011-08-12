<?php

PHPWS_Core::initModClass('hms', 'View.php');

abstract class ReportView {
    
    protected $report;
    
    public function __construct(Report $report)
    {
        $this->report = $report;
    }
}

?>