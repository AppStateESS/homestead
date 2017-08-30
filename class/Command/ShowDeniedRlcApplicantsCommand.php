<?php

namespace Homestead\Command;

use \Homestead\DeniedRlcApplicantView;
use \Homestead\UserStatus;
use \Homestead\Exception\PermissionException;

class ShowDeniedRlcApplicantsCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowDeniedRlcApplicants');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'view_rlc_applications')) {
            throw new PermissionException('You do not have permission to view RLC applications.');
        }

        $view = new DeniedRlcApplicantView();

        $context->setContent($view->show());
    }
}
