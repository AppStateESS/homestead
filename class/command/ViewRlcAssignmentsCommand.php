<?php

PHPWS_Core::initModClass('hms', 'RlcAssignmentsView.php');

class ViewRlcAssignmentsCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action' => 'ViewRlcAssignments');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'view_rlc_members')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view RLC members.');
        }

        $view = new RlcAssignmentsView();

        $context->setContent($view->show());
    }
}
?>
