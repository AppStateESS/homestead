<?php

PHPWS_Core::initModClass('hms', 'WKPDF.php');

class ReportPdfView {

    private $report;
    
    private $pdf;
    private $htmlView;
    
    public function __construct(Report $report)
    {
        $this->report = $report;
        $this->pdf = new WKPDF();
    }
    
    public function setHtmlView(ReportView $htmlView){
        $this->htmlView = $htmlView;
    }
    
    public function render()
    {
        $this->pdf->set_html($this->htmlView->getWrappedHtml());
        $this->pdf->render();
    }
    
    public function getPdfContent()
    {
        return $this->pdf->output(WKPDF::$PDF_ASSTRING,'');
    }
}

?>