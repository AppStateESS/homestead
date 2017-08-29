<?php

namespace Homestead\Command;

 

/**
 * ListReportsCommand
 *
 * Shows the interface listing all available reports.
 *
 * @author jbooker
 * @package HMS
 */

class ListReportsCommand extends Command {

    /**
     * Returns the aray of request vars
     *
     * @return Array Array of request vars
     */
    public function getRequestVars()
    {
        return array('action'=>'ListReports');
    }

    /**
     * Exec
     *
     * @param CommandContext $context
     * @throws PermissionException
     */
    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms','reports')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to run/view reports.');
        }

        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        PHPWS_Core::initModClass('hms', 'ListReportsView.php');

        $reports = ReportFactory::getAllReportControllers();
        $reportsList = new ListReportsView($reports);

        $context->setContent($reportsList->show());
    }
}
