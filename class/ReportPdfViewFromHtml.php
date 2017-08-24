<?php

namespace Homestead;

if (!defined('WKPDF_PATH')) {
    define('WKPDF_PATH', PHPWS_SOURCE_DIR . 'mod/hms/vendor/ioki/wkhtmltopdf-amd64-centos6/bin/wkhtmltopdf-amd64-centos6');
}
if (!defined('USE_XVFB')) {
    define('USE_XVFB', false);
    define('XVFB_PATH', '');
}

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
class ReportPdfViewFromHtml extends ReportPdfView
{
    // The ReportHtmlView object we're going to convert
    private $htmlView;

    /**
     * Constructor
     *
     * @param Report $report The report instance we're working with.
     * @param ReportHtmlView $htmlView The ReportHtmlView to convert.
     */
    public function __construct(Report $report, ReportHtmlView $htmlView)
    {
        parent::__construct($report);

        $this->htmlView = $htmlView;
        $this->pdf = new WKPDF(WKPDF_PATH);
        if (USE_XVFB) {
            $this->pdf->setXVFB(XVFB_PATH);
        }
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
        return $this->pdf->output(WKPDF::$PDF_ASSTRING, '');
    }

}
