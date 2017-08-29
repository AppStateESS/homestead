<?php

namespace Homestead\Command;

 

PHPWS_Core::initModClass('hms', 'DeniedRlcApplicantView.php');

class ShowDeniedRlcApplicantsCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowDeniedRlcApplicants');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'view_rlc_applications')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view RLC applications.');
        }

        $view = new DeniedRlcApplicantView();

        $context->setContent($view->show());
    }
}
