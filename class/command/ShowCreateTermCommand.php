<?php
namespace Homestead\command;

use \Homestead\Command;

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
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit terms.');
        }

        $view = new CreateTermView();
        $context->setContent($view->show());
    }
}
