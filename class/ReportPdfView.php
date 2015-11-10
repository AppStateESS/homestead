<?php

/**
 * ReportPdfView
 *
 * Abstract class representing the PDF view of a given report.
 * Extend this class to provide individual PDF views for each report,
 * or just use the RepotPdfViewFromHtml sub-class to provide a default
 * conversion from HTML.
 *
 * @see ReportPdfViewFromHtml
 * @author jbooker
 * @package HMS
 */

PHPWS_Core::initModClass('hms', 'ReportView.php');

abstract class ReportPdfView extends ReportView{

    // The PDF object this view will construct.
    protected $pdf;

    /**
     * Constructor
     *
     * @param Report $report The report this view is based on.
     */
    public function __construct(Report $report)
    {
        parent::__construct($report);
    }

    /**
     * Renders the PDF.
     */
    public abstract function render();

    /**
     * Returns the content of the PDF file as a (possibly binary formatted) string.
     *
     * @return String PDF file contents
     */
    public abstract function getPdfContent();
}
