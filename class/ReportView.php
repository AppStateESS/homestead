<?php

PHPWS_Core::initModClass('hms', 'View.php');

/**
 * ReportView - Parent class for various ReportView subclasses.
 *
 * @see ReportHtmlView
 * @see ReportPdfView
 * @see ReportCsvView
 * @author jbooker
 * @package HMS
 */
abstract class ReportView {

    // The Report object which we'll generate a view for
    protected $report;

    /**
     * Constructor
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }
}

?>