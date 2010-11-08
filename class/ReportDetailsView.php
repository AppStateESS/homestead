<?php

PHPWS_Core::initModClass('hms', 'View.php');

class ReportDetailsView extends View {

    private $reportName;

    public function __construct($reportName)
    {
        $this->reportName = $reportName;
    }

    public function show()
    {

    }
}

?>