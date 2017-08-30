<?php

namespace Homestead;

/**
 * iCsvReportView interface - To be implemented by ReportControllers.
 * Requires implementation of methods necessary for retreiving and
 * saving CSV output.
 *
 * @author jbooker
 * @package HMS
 */
interface iCsvReportView {
    /**
     * Responsible for creating and initializing the ReportCsvView
     * @return ReportCsvView
     */
    public function getCsvView();

    /**
     * Responsible for saving the output in the ReportCsvView to a file.
     * @param ReportCsvView $csvView
     */
    public function saveCsvOutput(ReportCsvView $csvView);
}
