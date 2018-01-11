<?php

namespace Homestead;

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
