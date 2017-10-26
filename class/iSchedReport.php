<?php

namespace Homestead;

/**
 * iSchedReport Interface
 * Interface for report controllers which can be scheduled to execute
 * at a specified time.
 *
 * @author jbooker
 * @package HMS
 */
interface iSchedReport {
    /**
     * Returns the ReportSetupView for settings up this report at a scheduled time.
     * The default implemntation just calls the getAsyncSetupView in iAsyncReport.
     *
     * @see ReportSetupView
     * @see iAsyncReort
     * @return ReportSetupView The ReportSEtupView to use for setting up this report.
     */
    public function getSchedSetupView();
}
