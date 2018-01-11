<?php
namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\CreateTermView;
use \Homestead\Exception\PermissionException;

/**
 * @author Jeremy Booker
 * @package hms
 */
class ShowCreateTermCommand extends Command {

    public function getRequestVars() {
        $vars = array('action' => 'ShowCreateTerm');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'edit_terms')) {
            throw new PermissionException('You do not have permission to edit terms.');
        }

        $view = new CreateTermView();
        $context->setContent($view->show());
    }
}
