<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\AdminMaintenanceMenuView;
use \Homestead\Exception\PermissionException;

class ShowAdminMaintenanceMenuCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowAdminMaintenanceMenu');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::isUnrestricted('hms')){
            throw new PermissionException('You do not have permission to view the admin maintenance menu.');
        }

        $adminMenu = new AdminMaintenanceMenuView();

        $context->setContent($adminMenu->show());
    }
}
