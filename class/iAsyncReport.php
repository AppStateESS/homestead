<?php

namespace Homestead;

/**
 * iAsyncReport Interface
 * Interface for report controllers which can be run asynchronously.
 *
 * @author jbooker
 * @package HMS
 */
interface iAsyncReport {

    /**
     * @see ReportSetupView
     * @return ReportSetupView The ReportSetupView to use for setting up this report.
     */
    public function getAsyncSetupView();
}
