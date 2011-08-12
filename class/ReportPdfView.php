<?php

PHPWS_Core::initModClass('hms', 'WKPDF.php');

abstract class ReportPdfView extends ReportView{

    protected $pdf;
    
    public function __construct(Report $report)
    {
        parent::__construct($report);
    }
    
    abstract function render();
    
    abstract public function getPdfContent();
}

?>