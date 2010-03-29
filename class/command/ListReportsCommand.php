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
         
        PHPWS_Core::initModClass('hms', 'ListReportsView.php');

        $reportsList = new ListReportsView();

        $context->setContent($reportsList->show());
    }
}