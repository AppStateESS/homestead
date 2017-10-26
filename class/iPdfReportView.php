<?php

namespace Homestead;

/**
 * iPdfReportView interface - To be implemented by ReportControllers.
 * Requires implementation of methods necessary for retreiving and
 * saving PDF output.
 *
 * @author jbooker
 * @package HMS
 */
interface iPdfReportView {
    /**
     * Responsible for creating and initializing the ReportPdfView
     * @return ReportPdfView
     */
    public function getPdfView();

    /**
     * Responsible for saving the output in the ReportPdfView to a file.
     * @param ReportPdfView $pdfView
     */
    public function savePdfOutput(ReportPdfView $pdfView);
}
