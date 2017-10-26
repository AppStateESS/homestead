<?php

namespace Homestead\Command;

use \Homestead\RlcAssignmentsView;
use \Homestead\UserStatus;
use \Homestead\Exception\PermissionException;

class ViewRlcAssignmentsCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action' => 'ViewRlcAssignments');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'view_rlc_members')){
            throw new PermissionException('You do not have permission to view RLC members.');
        }

        $view = new RlcAssignmentsView();

        $context->setContent($view->show());
    }
}
