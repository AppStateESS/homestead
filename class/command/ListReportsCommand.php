<?php

class ListReportsCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ListReports');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms','reports')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to run/view reports.');
        }

        PHPWS_Core::initModClass('hms', 'ReportManager.php');
        PHPWS_Core::initModClass('hms', 'ListReportsView.php');

        $reports = ReportManager::getReports();
        $reportsList = new ListReportsView($reports);

        $context->setContent($reportsList->show());
    }
}