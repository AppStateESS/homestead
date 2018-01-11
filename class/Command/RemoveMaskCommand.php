<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\CommandFactory;
use \Homestead\Exception\PermissionException;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class RemoveMaskCommand extends Command
{
    public function getRequestVars()
    {
        $vars = array('action' => 'RemoveMask');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'login_as_student')){
            throw new PermissionException('You do not have permission to login as a student.');
        }

        if(!UserStatus::isMasquerading()) {
            throw new PermissionException('You are not currently masquerading as another user.');
        }

        $user = UserStatus::getUsername();

        UserStatus::removeMask();

        $cmd = CommandFactory::getCommand('ShowStudentProfile');
        $cmd->setUsername($user);
        $cmd->redirect();
    }
}
