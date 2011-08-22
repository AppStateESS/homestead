<?php
/**
 * Report Interfaces
 * 
 * This file aggregates a bunch of interfaces that are used
 * by the reporting system. They're too small to each have
 * their own file, but there's enough of them that they can't
 * all be included anywhere else.
 */

/**
* iHtmlReportView interface - To be implemented by ReportControllers.
* Requires implementation of methods necessary for retreiving and
* saving HTML output.
*
* @author jbooker
* @package HMS
*/
interface iHtmlReportView {
    /**
     * Responsible for creating and initializing the ReportHtmlView
     * @return ReportHtmlView
     */
    public function getHtmlView();

    /**
     * Responsible for saving the output in the ReportHtmlView to a file.
     * @param ReportHtmlView $htmlView
     */
    public function saveHtmlOutput(ReportHtmlView $htmlView);
}

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

/**
 * iSyncReport Interface
 * Interface for report controllers which can be run synchronously.
 *
 * @author jbooker
 * @package HMS
 */
interface iSyncReport {
    /**
     * @see Command
     * @return Command The command to run the implementing report synchronously.
     */
    public function getSyncExecCmd();
    
    /**
     * @see ReportSetupView
     * @return ReportSetupView The ReportSetupView to use for setting up this report.
     */
    public function getSyncSetupView();
}

/**
 * iAsyncReport Interface
 * Interface for report controllers which can be run asynchronously.
 *
 * @author jbooker
 * @package HMS
 */
interface iAsyncReport {
    /**
    * @see Command
    * @return Command The command to run the implementing report synchronously.
    */
    public function getAsyncExecCmd();
    
    /**
     * @see ReportSetupView
     * @return ReportSetupView The ReportSetupView to use for setting up this report.
     */
    public function getAsyncSetupView();
}

/**
 * iSchedReport Interface
 * Interface for report controllers which can be scheduled to execute
 * at a specified time.
 *
 * @author jbooker
 * @package HMS
 */
interface iSchedReport {

}

?>