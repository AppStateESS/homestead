<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\CommandFactory;
use \Homestead\Exception\PermissionException;

/**
 * RemoveMaskAsSelfCommand
 * Allows RAs to login/logout as the student version of themselves.
 *
 * @author Jeremy Booker
 * @package Hms
 */
class RemoveMaskAsSelfCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'RemoveMaskAsSelf');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms','ra_login_as_self')) {
            throw new PermissionException('You do not have permission to login as the student verison of yourself.');
        }

        UserStatus::removeMaskAsSelf();

        $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
        $cmd->redirect();
    }
}
