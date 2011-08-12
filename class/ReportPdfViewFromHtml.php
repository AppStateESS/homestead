<?php

PHPWS_Core::initModClass('hms', 'ReportPdfView.php');

class ReportPdfViewFromHtml extends ReportPdfView {
    
    private $htmlView;
    
    public function __construct(Report $report, ReportHtmlView $htmlView){
        parent::__construct($report);
        
        $this->htmlView = $htmlView;
        $this->pdf = new WKPDF();
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