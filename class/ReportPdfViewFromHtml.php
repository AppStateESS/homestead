<?php

PHPWS_Core::initModClass('hms', 'ReportPdfView.php');
PHPWS_Core::initModClass('hms', 'WKPDF.php');

/**
 * ReportPdfViewFromHtml - Provided as a default implementation of
 * the ReportPdfView class. This class attempts to convert a ReportHtmlView
 * to PDf using the wkhtmltopdf project.
 * 
 * @see ReportPdfView
 * @link http://code.google.com/p/wkhtmltopdf/
 * @author jbooker
 * @package HMS
 */
class ReportPdfViewFromHtml extends ReportPdfView {
    
    // The ReportHtmlView object we're going to convert
    private $htmlView;
    
    /**
     * Constructor
     * 
     * @param Report $report The report instance we're working with.
     * @param ReportHtmlView $htmlView The ReportHtmlView to convert.
     */
    public function __construct(Report $report, ReportHtmlView $htmlView){
        parent::__construct($report);
        
        $this->htmlView = $htmlView;
        $this->pdf = new WKPDF();
    }
    
    /**
     * Renders the PDF.
     */
    public function render()
    {
        $this->pdf->set_html($this->htmlView->getWrappedHtml());
        $this->pdf->render();
    }
    
    /**
     * Returns the content of the PDF file as a (possibly binary formatted) string.
     * 
     * @return String PDF file contents
     */
    public function getPdfContent()
    {
        return $this->pdf->output(WKPDF::$PDF_ASSTRING,'');
    }
}

?>