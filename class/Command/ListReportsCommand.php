<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\ReportFactory;
use \Homestead\ListReportsView;
use \Homestead\Exception\PermissionException;

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
            throw new PermissionException('You do not have permission to run/view reports.');
        }

        $reports = ReportFactory::getAllReportControllers();
        $reportsList = new ListReportsView($reports);

        $context->setContent($reportsList->show());
    }
}
