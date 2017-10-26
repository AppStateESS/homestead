<?php

namespace Homestead;

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
